<?php

/*
 * This file is part of Sulu.
 *
 * (c) Sulu GmbH
 *
 * This source file is subject to the MIT license that is bundled
 * with this source code in the file LICENSE.
 */

namespace Sulu\Component\Security\Tests\Unit\Authorization\AccessControl;

use PHPUnit\Framework\TestCase;
use Prophecy\PhpUnit\ProphecyTrait;
use Sulu\Bundle\PageBundle\Document\BasePageDocument;
use Sulu\Component\Content\Document\Behavior\SecurityBehavior;
use Sulu\Component\Content\Document\Behavior\WebspaceBehavior;
use Sulu\Component\DocumentManager\DocumentManagerInterface;
use Sulu\Component\DocumentManager\Exception\DocumentNotFoundException;
use Sulu\Component\Security\Authentication\RoleRepositoryInterface;
use Sulu\Component\Security\Authorization\AccessControl\PhpcrAccessControlProvider;

class PhpcrAccessControlProviderTest extends TestCase
{
    use ProphecyTrait;

    /**
     * @var PhpcrAccessControlProvider
     */
    private $phpcrAccessControlProvider;

    /**
     * @var RoleRepositoryInterface
     */
    private $roleRepository;

    /**
     * @var DocumentManagerInterface
     */
    private $documentManager;

    public function setUp(): void
    {
        $this->documentManager = $this->prophesize(DocumentManagerInterface::class);
        $this->roleRepository = $this->prophesize(RoleRepositoryInterface::class);
        $this->phpcrAccessControlProvider = new PhpcrAccessControlProvider(
            $this->documentManager->reveal(),
            $this->roleRepository->reveal(),
            ['view' => 64, 'edit' => 32, 'delete' => 16]
        );
    }

    public function testSetPermissions()
    {
        $document = $this->prophesize(WebspaceBehavior::class);
        $document->willImplement(SecurityBehavior::class);

        $this->documentManager->find('1', null, ['rehydrate' => false])->willReturn($document);
        $document->setPermissions(['role' => ['view' => true, 'edit' => false]])->shouldBeCalled();
        $this->documentManager->persist($document)->shouldBeCalled();
        $this->documentManager->flush()->shouldBeCalled();

        $this->phpcrAccessControlProvider->setPermissions(
            \get_class($document),
            '1',
            ['role' => ['view' => true, 'edit' => false]]
        );
    }

    public function testGetPermissions()
    {
        $document = $this->prophesize(WebspaceBehavior::class);
        $document->willImplement(SecurityBehavior::class);

        $this->documentManager->find('1', null, ['rehydrate' => false])->willReturn($document);
        $document->getPermissions()->willReturn(['1' => ['view' => true, 'edit' => true, 'delete' => false]]);

        $this->roleRepository->findRoleIdsBySystem(null)->willReturn([]);

        $this->assertEquals([
            1 => ['view' => true, 'edit' => true, 'delete' => false],
        ], $this->phpcrAccessControlProvider->getPermissions(\get_class($document), '1'));
    }

    public function testGetEmptyPermissions()
    {
        $document = $this->prophesize(WebspaceBehavior::class);
        $document->willImplement(SecurityBehavior::class);

        $this->documentManager->find('1', null, ['rehydrate' => false])->willReturn($document);
        $document->getPermissions()->willReturn(null);

        $this->roleRepository->findRoleIdsBySystem(null)->willReturn([]);

        $this->assertEquals([], $this->phpcrAccessControlProvider->getPermissions(\get_class($document), '1'));
    }

    public function testGetPermissionsForSystem()
    {
        $document = $this->prophesize(WebspaceBehavior::class);
        $document->willImplement(SecurityBehavior::class);

        $this->documentManager->find('1', null, ['rehydrate' => false])->willReturn($document);
        $document->getPermissions()->willReturn([
            '1' => ['view' => true, 'edit' => true, 'delete' => false],
            '2' => ['view' => true, 'edit' => false, 'delete' => true],
            '4' => ['view' => true, 'edit' => false, 'delete' => false],
        ]);

        $this->roleRepository->findRoleIdsBySystem('Sulu')->willReturn([1, 4]);
        $this->roleRepository->findRoleIdsBySystem('Website')->willReturn([2]);

        $this->assertEquals(
            [
                1 => ['view' => true, 'edit' => true, 'delete' => false],
                4 => ['view' => true, 'edit' => false, 'delete' => false],
            ],
            $this->phpcrAccessControlProvider->getPermissions(\get_class($document), '1', 'Sulu')
        );

        $this->assertEquals(
            [
                2 => ['view' => true, 'edit' => false, 'delete' => true],
            ],
            $this->phpcrAccessControlProvider->getPermissions(\get_class($document), '1', 'Website')
        );
    }

    public function testGetPermissionsForNotExistingDocument()
    {
        $this->documentManager->find('1', null, ['rehydrate' => false])->willThrow(DocumentNotFoundException::class);

        $this->assertEquals([], $this->phpcrAccessControlProvider->getPermissions('Acme\Document', '1'));
    }

    public function testGetPermissionsForUnsecuredDocument()
    {
        $document = $this->prophesize(WebspaceBehavior::class);

        $this->documentManager->find('1', null, ['rehydrate' => false])->willReturn($document);

        $this->assertEquals([], $this->phpcrAccessControlProvider->getPermissions('Acme\Document', '1'));
    }

    /**
     * @dataProvider provideSupport
     */
    public function testSupport($type, $supported)
    {
        $this->assertEquals($this->phpcrAccessControlProvider->supports($type), $supported);
    }

    public function provideSupport()
    {
        return [
            [BasePageDocument::class, true],
            [\stdClass::class, false],
        ];
    }
}
