<?php
/*
 * This file is part of the Sulu CMS.
 *
 * (c) MASSIVE ART WebServices GmbH
 *
 * This source file is subject to the MIT license that is bundled
 * with this source code in the file LICENSE.
 */

namespace Sulu\Bundle\WebsiteBundle\Navigation;

use ReflectionMethod;
use Sulu\Bundle\TestBundle\Testing\PhpcrTestCase;
use Sulu\Component\Content\Query\ContentQueryExecutor;
use Sulu\Component\Content\Property;
use Sulu\Component\Content\PropertyTag;
use Sulu\Component\Content\StructureInterface;
use Sulu\Component\Webspace\Localization;
use Sulu\Component\Webspace\Navigation;
use Sulu\Component\Webspace\NavigationContext;
use Sulu\Component\Webspace\Webspace;

class NavigationTest extends PhpcrTestCase
{
    /**
     * @var StructureInterface[]
     */
    private $data;

    /**
     * @var Webspace
     */
    private $webspace;

    /**
     * @var NavigationMapperInterface
     */
    private $navigation;

    protected function setUp()
    {
        $this->prepareMapper();
        $this->data = $this->prepareTestData();

        $this->structureManager->expects($this->any())
            ->method('getStructures')
            ->will($this->returnCallback(array($this, 'structuresCallback')));

        $contentQuery = new ContentQueryExecutor($this->sessionManager, $this->mapper);

        $this->navigation = new NavigationMapper(
            $this->mapper,
            $contentQuery,
            new NavigationQueryBuilder($this->structureManager, $this->languageNamespace),
            $this->sessionManager
        );
    }

    protected function prepareWebspaceManager()
    {
        if ($this->webspaceManager === null) {
            $this->webspace = new Webspace();
            $this->webspace->setKey('default');

            $local = new Localization();
            $local->setLanguage('en');

            $this->webspace->setLocalizations(array($local));
            $this->webspace->setName('Default');

            $this->webspace->setNavigation(
                new Navigation(
                    array(
                        new NavigationContext('main', array()),
                        new NavigationContext('footer', array())
                    )
                )
            );

            $this->webspaceManager = $this->getMock('Sulu\Component\Webspace\Manager\WebspaceManagerInterface');
            $this->webspaceManager
                ->expects($this->any())
                ->method('findWebspaceByKey')
                ->will($this->returnValue($this->webspace));
        }
    }

    public function structureCallback()
    {
        $args = func_get_args();
        $structureKey = $args[0];

        if ($structureKey == 'default_template') {
            return $this->getStructureMock($structureKey);
        } elseif ($structureKey == 'excerpt') {
            return $this->getStructureMock($structureKey, false);
        } elseif ($structureKey == 'simple') {
            return $this->getStructureMock($structureKey);
        } elseif ($structureKey == 'overview') {
            return $this->getStructureMock($structureKey);
        } elseif ($structureKey == 'norlp') {
            return $this->getStructureMock($structureKey, false);
        }

        return null;
    }

    public function structuresCallback()
    {
        return array(
            $this->getStructureMock('default_template'),
            $this->getStructureMock('excerpt'),
            $this->getStructureMock('simple'),
            $this->getStructureMock('overview'),
            $this->getStructureMock('norlp')
        );
    }

    public function getExtensionCallback()
    {
        return new ExcerptStructureExtension($this->structureManager, $this->contentTypeManager);
    }

    public function getExtensionsCallback()
    {
        return array($this->getExtensionCallback());
    }

    /**
     * @return StructureInterface[]
     */
    private function prepareTestData()
    {
        $data = array(
            'news' => array(
                'name' => 'News',
                'rl' => '/news',
                'ext' => array('excerpt' => array('name' => 'Excerpt News')),
                'navContexts' => array('footer')
            ),
            'products' => array(
                'name' => 'Products',
                'rl' => '/products',
                'ext' => array('excerpt' => array('name' => 'Excerpt Products')),
                'navContexts' => array('main')
            ),
            'news/news-1' => array(
                'name' => 'News-1',
                'rl' => '/news/news-1',
                'ext' => array('excerpt' => array('name' => 'Excerpt News 1')),
                'navContexts' => array('main', 'footer')
            ),
            'news/news-2' => array(
                'name' => 'News-2',
                'rl' => '/news/news-2',
                'ext' => array('excerpt' => array('name' => 'Excerpt News 2')),
                'navContexts' => array('main')
            ),
            'products/products-1' => array(
                'name' => 'Products-1',
                'rl' => '/products/products-1',
                'ext' => array('excerpt' => array('name' => 'Excerpt Products 1')),
                'navContexts' => array('main', 'footer')
            ),
            'products/products-2' => array(
                'name' => 'Products-2',
                'rl' => '/products/products-2',
                'ext' => array('excerpt' => array('name' => 'Excerpt Products 2')),
                'navContexts' => array('main')
            )
        );

        $this->mapper->saveStartPage(array('name' => 'Startpage', 'url' => '/'), 'simple', 'default', 'en', 1);

        $data['news'] = $this->mapper->save(
            $data['news'],
            'simple',
            'default',
            'en',
            1,
            true,
            null,
            null,
            StructureInterface::STATE_PUBLISHED
        );
        $data['news/news-1'] = $this->mapper->save(
            $data['news/news-1'],
            'simple',
            'default',
            'en',
            1,
            true,
            null,
            $data['news']->getUuid(),
            StructureInterface::STATE_PUBLISHED
        );
        $data['news/news-2'] = $this->mapper->save(
            $data['news/news-2'],
            'simple',
            'default',
            'en',
            1,
            true,
            null,
            $data['news']->getUuid(),
            StructureInterface::STATE_PUBLISHED
        );

        $data['products'] = $this->mapper->save(
            $data['products'],
            'simple',
            'default',
            'en',
            1,
            true,
            null,
            null,
            StructureInterface::STATE_PUBLISHED
        );
        $data['products/products-1'] = $this->mapper->save(
            $data['products/products-1'],
            'simple',
            'default',
            'en',
            1,
            true,
            null,
            $data['products']->getUuid(),
            StructureInterface::STATE_PUBLISHED
        );
        $data['products/products-2'] = $this->mapper->save(
            $data['products/products-2'],
            'simple',
            'default',
            'en',
            1,
            true,
            null,
            $data['products']->getUuid(),
            StructureInterface::STATE_PUBLISHED
        );

        return $data;
    }

    private function getStructureMock($name, $rlp = true)
    {
        $structureMock = $this->getMockForAbstractClass(
            '\Sulu\Component\Content\Structure',
            array($name, 'asdf', 'asdf', 2400)
        );

        $method = new ReflectionMethod(
            get_class($structureMock), 'addChild'
        );

        $method->setAccessible(true);
        $method->invokeArgs(
            $structureMock,
            array(
                new Property(
                    'name',
                    '',
                    'text_line',
                    true,
                    true,
                    1,
                    1,
                    array(),
                    array(new PropertyTag('sulu.node.name', 1))
                )
            )
        );

        if ($rlp) {
            $method->invokeArgs(
                $structureMock,
                array(
                    new Property(
                        'rl',
                        '',
                        'resource_locator',
                        true,
                        true,
                        1,
                        1,
                        array(),
                        array(new PropertyTag('sulu.rlp', 1))
                    )
                )
            );
        }

        return $structureMock;
    }

    public function testMainNavigation()
    {
        $main = $this->navigation->getRootNavigation('default', 'en', 2);
        $this->assertEquals(2, sizeof($main));
        $this->assertEquals(2, sizeof($main[0]['children']));
        $this->assertEquals(2, sizeof($main[1]['children']));

        $this->assertEquals('/news', $main[0]['url']);
        $this->assertEquals('/news/news-1', $main[0]['children'][0]['url']);
        $this->assertEquals('/news/news-2', $main[0]['children'][1]['url']);
        $this->assertEquals('/products', $main[1]['url']);
        $this->assertEquals('/products/products-1', $main[1]['children'][0]['url']);
        $this->assertEquals('/products/products-2', $main[1]['children'][1]['url']);

        $main = $this->navigation->getRootNavigation('default', 'en', 1);
        $this->assertEquals(2, sizeof($main));
        $this->assertEquals(0, sizeof($main[0]['children']));
        $this->assertEquals(0, sizeof($main[1]['children']));

        $main = $this->navigation->getRootNavigation('default', 'en', null);
        $this->assertEquals(2, sizeof($main));
        $this->assertEquals(2, sizeof($main[0]['children']));
        $this->assertEquals(2, sizeof($main[1]['children']));
        $this->assertEquals(0, sizeof($main[0]['children'][0]['children']));
        $this->assertEquals(0, sizeof($main[0]['children'][1]['children']));
        $this->assertEquals(0, sizeof($main[1]['children'][0]['children']));
        $this->assertEquals(0, sizeof($main[1]['children'][1]['children']));
    }

    public function testNavigation()
    {
        $main = $this->navigation->getNavigation($this->data['news']->getUuid(), 'default', 'en', 1);
        $this->assertEquals(2, sizeof($main));
        $this->assertEquals(0, sizeof($main[0]['children']));
        $this->assertEquals(0, sizeof($main[1]['children']));

        $this->assertEquals($this->data['news/news-1']->getUuid(), $main[0]['uuid']);
        $this->assertEquals('News-1', $main[0]['title']);
        $this->assertEquals('/news/news-1', $main[0]['url']);

        $this->assertEquals($this->data['news/news-2']->getUuid(), $main[1]['uuid']);
        $this->assertEquals('News-2', $main[1]['title']);
        $this->assertEquals('/news/news-2', $main[1]['url']);
    }

    public function testMainNavigationFlat()
    {
        $result = $this->navigation->getRootNavigation('default', 'en', 1, true);
        $this->assertEquals(2, sizeof($result));
        $this->assertEquals('News', $result[0]['title']);
        $this->assertEquals('Products', $result[1]['title']);

        $result = $this->navigation->getRootNavigation('default', 'en', 2, true);
        $this->assertEquals(6, sizeof($result));
        $this->assertEquals('News', $result[0]['title']);
        $this->assertEquals('News-1', $result[1]['title']);
        $this->assertEquals('News-2', $result[2]['title']);
        $this->assertEquals('Products', $result[3]['title']);
        $this->assertEquals('Products-1', $result[4]['title']);
        $this->assertEquals('Products-2', $result[5]['title']);
    }

    public function testNavigationFlat()
    {
        $data['news'] = $this->mapper->save(
            array(
                'name' => 'SubNews',
                'rl' => '/asdf',
                'navContexts' => array('footer')
            ),
            'simple',
            'default',
            'en',
            1,
            true,
            null,
            $this->data['news/news-1']->getUuid(),
            StructureInterface::STATE_PUBLISHED
        );

        $result = $this->navigation->getNavigation($this->data['news']->getUuid(), 'default', 'en', 2, true);
        $this->assertEquals(3, sizeof($result));
        $this->assertEquals('News-1', $result[0]['title']);
        $this->assertEquals('SubNews', $result[1]['title']);
        $this->assertEquals('News-2', $result[2]['title']);
    }

    public function testNavigationExcerpt()
    {
        $data['news'] = $this->mapper->save(
            array(
                'name' => 'SubNews',
                'rl' => '/asdf',
                'navContexts' => array('footer')
            ),
            'simple',
            'default',
            'en',
            1,
            true,
            null,
            $this->data['news/news-1']->getUuid(),
            StructureInterface::STATE_PUBLISHED
        );

        $result = $this->navigation->getNavigation(
            $this->data['news']->getUuid(),
            'default',
            'en',
            2,
            true,
            null,
            true
        );
        $this->assertEquals(3, sizeof($result));
        $this->assertEquals('News-1', $result[0]['title']);
        $this->assertEquals('Excerpt News 1', $result[0]['excerpt']['name']);

        $this->assertEquals('SubNews', $result[1]['title']);
        $this->assertEquals('', $result[1]['excerpt']['name']);

        $this->assertEquals('News-2', $result[2]['title']);
        $this->assertEquals('Excerpt News 2', $result[2]['excerpt']['name']);
    }

    public function testBreadcrumb()
    {
        $breadcrumb = $this->navigation->getBreadcrumb($this->data['news/news-2']->getUuid(), 'default', 'en', 1);
        $this->assertEquals(3, sizeof($breadcrumb));

        // startpage has no title
        $this->assertEquals('Startpage', $breadcrumb[0]->getTitle());
        $this->assertEquals('/', $breadcrumb[0]->getUrl());
        $this->assertEquals('News', $breadcrumb[1]->getTitle());
        $this->assertEquals('/news', $breadcrumb[1]->getUrl());
        $this->assertEquals('News-2', $breadcrumb[2]->getTitle());
        $this->assertEquals('/news/news-2', $breadcrumb[2]->getUrl());
    }

    public function testNavigationNoRlp()
    {
        // this node should not be visible in navigation
        $this->mapper->save(
            array('name' => 'Hikaru Sulu'),
            'norlp',
            'default',
            'en',
            1,
            true,
            null,
            null,
            StructureInterface::STATE_PUBLISHED
        );

        $main = $this->navigation->getNavigation($this->data['news']->getUuid(), 'default', 'en', 1);
        $this->assertEquals(2, sizeof($main));
        $this->assertEquals(0, sizeof($main[0]['children']));
        $this->assertEquals(0, sizeof($main[1]['children']));

        $this->assertEquals($this->data['news/news-1']->getUuid(), $main[0]['uuid']);
        $this->assertEquals('News-1', $main[0]['title']);
        $this->assertEquals('/news/news-1', $main[0]['url']);

        $this->assertEquals($this->data['news/news-2']->getUuid(), $main[1]['uuid']);
        $this->assertEquals('News-2', $main[1]['title']);
        $this->assertEquals('/news/news-2', $main[1]['url']);
    }

    public function testNavContexts()
    {
        // context footer (only news and one sub page news-1)
        $result = $this->navigation->getRootNavigation('default', 'en', 2, false, 'footer');

        $this->assertEquals(2, sizeof($result));
        $layer1 = $result;

        $this->assertEquals(1, sizeof($layer1[0]['children']));
        $layer2 = $layer1[0]['children'][0];

        $this->assertEquals('News', $layer1[0]['title']);
        $this->assertEquals('News-1', $layer2['title']);

        $this->assertEquals(0, sizeof($layer1[1]['children']));
        $this->assertEquals('Products-1', $layer1[1]['title']);

        // context main (only products and two sub pages
        $result = $this->navigation->getRootNavigation('default', 'en', 2, false, 'main');

        $this->assertEquals(3, sizeof($result));
        $layer1 = $result;

        $this->assertEquals(0, sizeof($layer1[0]['children']));
        $this->assertEquals(0, sizeof($layer1[1]['children']));

        $this->assertEquals('News-1', $layer1[0]['title']);
        $this->assertEquals('News-2', $layer1[1]['title']);

        $this->assertEquals(2, sizeof($layer1[2]['children']));
        $layer2 = $layer1[2]['children'];

        $this->assertEquals('Products', $layer1[2]['title']);
        $this->assertEquals('Products-1', $layer2[0]['title']);
        $this->assertEquals('Products-2', $layer2[1]['title']);
    }

    public function testNavContextsFlat()
    {
        // context footer (only news and one sub page news-1)
        $result = $this->navigation->getRootNavigation('default', 'en', 2, true, 'footer');

        $this->assertEquals(3, sizeof($result));

        // check children
        $this->assertEquals(0, sizeof($result[0]['children']));
        $this->assertEquals(0, sizeof($result[1]['children']));
        $this->assertEquals(0, sizeof($result[2]['children']));

        // check title
        $this->assertEquals('News', $result[0]['title']);
        $this->assertEquals('News-1', $result[1]['title']);
        $this->assertEquals('Products-1', $result[2]['title']);

        // context main (only products and two sub pages
        $result = $this->navigation->getRootNavigation('default', 'en', 2, true, 'main');

        $this->assertEquals(5, sizeof($result));

        // check children
        $this->assertEquals(0, sizeof($result[0]['children']));
        $this->assertEquals(0, sizeof($result[1]['children']));
        $this->assertEquals(0, sizeof($result[2]['children']));
        $this->assertEquals(0, sizeof($result[3]['children']));
        $this->assertEquals(0, sizeof($result[4]['children']));

        // check title
        $this->assertEquals('News-1', $result[0]['title']);
        $this->assertEquals('News-2', $result[1]['title']);
        $this->assertEquals('Products', $result[2]['title']);
        $this->assertEquals('Products-1', $result[3]['title']);
        $this->assertEquals('Products-2', $result[4]['title']);
    }

    public function testNavigationTestPage()
    {
        $data = array(
            'name' => 'Products-3',
            'rl' => '/products/products-3'
        );

        $this->data['products/products-3'] = $this->mapper->save(
            $data,
            'simple',
            'default',
            'en',
            1,
            true,
            null,
            $this->data['products']->getUuid(),
            StructureInterface::STATE_TEST
        );

        $main = $this->navigation->getNavigation($this->data['products']->getUuid(), 'default', 'en', 1);
        $this->assertEquals(2, sizeof($main));
        $this->assertEquals('/products/products-1', $main[0]['url']);
        $this->assertEquals('/products/products-2', $main[1]['url']);

        $this->data['products/products-3'] = $this->mapper->save(
            $data,
            'simple',
            'default',
            'en',
            1,
            true,
            $this->data['products/products-3']->getUuid(),
            null,
            StructureInterface::STATE_PUBLISHED
        );

        $main = $this->navigation->getNavigation($this->data['products']->getUuid(), 'default', 'en', 1);
        $this->assertEquals(3, sizeof($main));
        $this->assertEquals('/products/products-1', $main[0]['url']);
        $this->assertEquals('/products/products-2', $main[1]['url']);
        $this->assertEquals('/products/products-3', $main[2]['url']);

        $main = $this->navigation->getNavigation($this->data['products']->getUuid(), 'default', 'en', 1, false, 'main');
        $this->assertEquals(2, sizeof($main));
        $this->assertEquals('/products/products-1', $main[0]['url']);
        $this->assertEquals('/products/products-2', $main[1]['url']);

        $data = array(
            'name' => 'Products-3',
            'rl' => '/products/products-3',
            'navContexts' => array('main')
        );
        $this->data['products/products-3'] = $this->mapper->save(
            $data,
            'simple',
            'default',
            'en',
            1,
            true,
            $this->data['products/products-3']->getUuid()
        );

        $main = $this->navigation->getNavigation($this->data['products']->getUuid(), 'default', 'en', 1);
        $this->assertEquals(3, sizeof($main));
        $this->assertEquals('/products/products-1', $main[0]['url']);
        $this->assertEquals('/products/products-2', $main[1]['url']);
        $this->assertEquals('/products/products-3', $main[2]['url']);

        $main = $this->navigation->getNavigation($this->data['products']->getUuid(), 'default', 'en', 1, false, 'main');
        $this->assertEquals(3, sizeof($main));
        $this->assertEquals('/products/products-1', $main[0]['url']);
        $this->assertEquals('/products/products-2', $main[1]['url']);
        $this->assertEquals('/products/products-3', $main[2]['url']);
    }

    public function testNavigationOrder()
    {
        $main = $this->navigation->getNavigation($this->data['news']->getUuid(), 'default', 'en', 1);
        $this->assertEquals(2, sizeof($main));
        $this->assertEquals(0, sizeof($main[0]['children']));
        $this->assertEquals(0, sizeof($main[1]['children']));

        $this->assertEquals($this->data['news/news-1']->getUuid(), $main[0]['uuid']);
        $this->assertEquals('News-1', $main[0]['title']);
        $this->assertEquals('/news/news-1', $main[0]['url']);

        $this->assertEquals($this->data['news/news-2']->getUuid(), $main[1]['uuid']);
        $this->assertEquals('News-2', $main[1]['title']);
        $this->assertEquals('/news/news-2', $main[1]['url']);

        $session = $this->sessionManager->getSession();
        $session->getNodeByIdentifier($this->data['news/news-1']->getUuid())->setProperty('sulu:order', 100);
        $session->save();
        $session->refresh(false);

        $main = $this->navigation->getNavigation($this->data['news']->getUuid(), 'default', 'en', 1);
        $this->assertEquals(2, sizeof($main));
        $this->assertEquals(0, sizeof($main[0]['children']));
        $this->assertEquals(0, sizeof($main[1]['children']));

        $this->assertEquals($this->data['news/news-2']->getUuid(), $main[0]['uuid']);
        $this->assertEquals('News-2', $main[0]['title']);
        $this->assertEquals('/news/news-2', $main[0]['url']);

        $this->assertEquals($this->data['news/news-1']->getUuid(), $main[1]['uuid']);
        $this->assertEquals('News-1', $main[1]['title']);
        $this->assertEquals('/news/news-1', $main[1]['url']);
    }
}
