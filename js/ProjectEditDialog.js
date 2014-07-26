Ext.namespace('Tine.Donator');

Tine.Donator.ProjectEditDialog = Ext.extend(Tine.widgets.dialog.EditDialog, {
	
	/**
	 * @private
	 */
	windowNamePrefix: 'ProjectEditWindow_',
	appName: 'Donator',
	recordClass: Tine.Donator.Model.Project,
	recordProxy: Tine.Donator.projectBackend,
	loadRecord: false,
	evalGrants: false,
	
	/**
	 * returns dialog
	 * 
	 * NOTE: when this method gets called, all initalisation is done.
	 */
	getFormItems: function() {
	    return {
	        xtype: 'panel',
	        border: false,
	        frame:true,
	        items:[{xtype:'columnform',items:[
	             [
					{
					    fieldLabel: 'Projekt-Nr',
					    emptyText: '<automatisch>',
					    disabledClass: 'x-item-disabled-view',
					    id:'project_nr',
					    name:'project_nr',
					    value:null,
					    disabled:true,
					    width: 150
					},{
					    fieldLabel: 'Bezeichnung',
					    id:'name',
					    name:'name',
					    value:null,
					    width: 400
					}
				 ],[
					{
						xtype:'textarea',
					    fieldLabel: 'Beschreibung',
					    id:'description',
					    name:'description',
					    value:null,
					    width: 550,
					    height:200
					} 
	             ]
	        ]}]
	    };
	}
});

/**
 * Donator Edit Popup
 */
Tine.Donator.ProjectEditDialog.openWindow = function (config) {
    var id = (config.record && config.record.id) ? config.record.id : 0;
    var window = Tine.WindowFactory.getWindow({
        width: 600,
        height: 450,
        name: Tine.Donator.ProjectEditDialog.prototype.windowNamePrefix + id,
        contentPanelConstructor: 'Tine.Donator.ProjectEditDialog',
        contentPanelConstructorConfig: config
    });
    return window;
};