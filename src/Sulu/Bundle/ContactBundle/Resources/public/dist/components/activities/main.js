define(["text!sulucontact/components/activities/activity.form.html"],function(a){"use strict";var b={overlayId:"activitiesOverlay",activityListSelector:"#activities-list",activityFormSelector:"#acitivity-form",activitiesURL:"/admin/api/activities/"},c=function(){return[{id:"add",icon:"plus-circle","class":"highlight-white",title:"add",position:10,callback:this.addOrEditActivity.bind(this)},{id:"settings",icon:"gear",items:[{title:this.sandbox.translate("contact.activities.remove"),callback:this.removeActivities.bind(this)}]}]};return{view:!0,layout:{sidebar:{width:"fixed",cssClasses:"sidebar-padding-50"}},templates:["/admin/contact/template/contact/activities"],initialize:function(){this.activityDefaults=null,this.contact=this.options.contact,this.account=this.options.account,this.instanceName=this.options.instanceName,this.responsiblePersons=this.options.responsiblePersons,this.render(),this.bindCustomEvents(),this.sandbox.emit("sulu.contacts.activities.get.defaults"),this.contact&&this.contact.id?this.initSidebar(this.options.widgetUrl,this.contact.id):this.account&&this.account.id&&this.initSidebar(this.options.widgetUrl,this.account.id)},initSidebar:function(a,b){this.sandbox.emit("sulu.sidebar.set-widget",a+b)},bindCustomEvents:function(){this.sandbox.on("sulu.contacts.activities.set.defaults",function(a){this.activityDefaults=a},this),this.sandbox.on("sulu.contacts."+this.instanceName+".activity.loaded",function(a){this.startOverlay(a)},this),this.sandbox.on("husky.datagrid.item.click",function(a){this.sandbox.emit("sulu.contacts."+this.instanceName+".activity.load",a)},this),this.sandbox.on("sulu.header.back",function(){this.sandbox.emit("sulu.contacts.contacts.list")},this),this.sandbox.on("sulu.contacts."+this.instanceName+".activity.added",function(a){this.sandbox.emit("husky.datagrid.record.add",a)},this),this.sandbox.on("sulu.contacts."+this.instanceName+".activity.updated",function(a){this.sandbox.emit("husky.datagrid.records.change",a)},this),this.sandbox.on("sulu.contacts."+this.instanceName+".activity.removed",function(a){this.sandbox.emit("husky.datagrid.record.remove",a)},this),this.sandbox.on("husky.overlay.activity-add-edit.opened",function(){this.sandbox.start(b.activityFormSelector);var a=this.sandbox.form.create(b.activityFormSelector);a.initialized.then(function(){this.sandbox.form.setData(b.activityFormSelector,this.overlayData)}.bind(this))}.bind(this))},setTitle:function(){var a=this.sandbox.translate("contact.contacts.title"),b=[{title:"navigation.contacts"},{title:"contact.contacts.title",event:"sulu.contacts.contacts.list"}];this.options.contact&&this.options.contact.id&&(a=this.options.contact.fullName,b.push({title:"#"+this.options.contact.id})),this.sandbox.emit("sulu.header.set-title",a),this.sandbox.emit("sulu.header.set-breadcrumb",b)},addOrEditActivity:function(a){a?this.sandbox.emit("sulu.contacts.contact.activity.load",a):this.startOverlay(null)},startOverlay:function(c){var d,e,f,g;this.sandbox.dom.remove("#"+b.overlayId),f=this.sandbox.dom.createElement('<div id="'+b.overlayId+'"></div>'),this.sandbox.dom.append(b.activityListSelector,f),this.overlayData=c,d=this.sandbox.translate(c&&c.id?"contact.contacts.activities.edit":"contact.contacts.activities.add"),g={activityType:c&&c.activityType?c.activityType.id:"",activityStatus:c&&c.activityStatus?c.activityStatus.id:"",activityPriority:c&&c.activityPriority?c.activityPriority.id:"",assignedContact:c&&c.assignedContact?c.assignedContact.id:"",activityTypes:this.activityDefaults.activityTypes,activityPriorities:this.activityDefaults.activityPriorities,activityStatuses:this.activityDefaults.activityStatuses,responsiblePersons:this.responsiblePersons,contact:this.contact?this.contact.id:"",account:this.account?this.account.id:"",translate:this.sandbox.translate},e=this.sandbox.util.template(a,g),this.sandbox.start([{name:"overlay@husky",options:{el:f,title:d,openOnStart:!0,removeOnClose:!0,instanceName:"activity-add-edit",data:e,skin:"wide",okCallback:this.editAddOkClicked.bind(this),closeCallback:this.stopOverlayComponents.bind(this)}}])},stopOverlayComponents:function(){this.sandbox.stop(b.activityFormSelector)},editAddOkClicked:function(){if(!this.sandbox.form.validate(b.activityFormSelector,!0))return!1;var a=this.sandbox.form.getData(b.activityFormSelector);this.contact&&!a.contact&&(a.contact=this.contact.id),this.account&&!a.account&&(a.account=this.account.id),a.id||delete a.id,this.sandbox.emit("sulu.contacts."+this.instanceName+".activity.save",a),this.stopOverlayComponents()},render:function(){var a;this.sandbox.dom.html(this.$el,this.renderTemplate("/admin/contact/template/contact/activities")),this.contact?(this.setTitle(),a="/admin/api/activities?flat=true&contact="+this.contact.id):a="/admin/api/activities?flat=true&account="+this.account.id,this.sandbox.sulu.initListToolbarAndList.call(this,"activitiesContactsFields","/admin/api/activities/fields",{el:this.$find("#list-toolbar-container"),instanceName:"activities",inHeader:!0,template:c.call(this)},{el:this.sandbox.dom.find("#activities-list",this.$el),url:a,searchInstanceName:"activities",searchFields:["subject"],resultKey:"activities",viewOptions:{table:{selectItem:{type:"checkbox"},removeRow:!1}}})},removeActivities:function(){this.sandbox.emit("husky.datagrid.items.get-selected",function(a){a.length>0&&this.sandbox.emit("sulu.contacts."+this.instanceName+".activities.delete",a)}.bind(this))}}});