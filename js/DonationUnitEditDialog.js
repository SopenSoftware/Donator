Ext.namespace('Tine.Donator');

Tine.Donator.DonationUnitEditDialog = Ext.extend(Tine.widgets.dialog.EditDialog, {
	
	/**
	 * @private
	 */
	windowNamePrefix: 'DonationUnitEditWindow_',
	appName: 'Donator',
	recordClass: Tine.Donator.Model.DonationUnit,
	recordProxy: Tine.Donator.donationUnitBackend,
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
					new Tine.Tinebase.widgets.form.RecordPickerComboBox({
						disabledClass: 'x-item-disabled-view',
						width: 250,
						fieldLabel: 'Kontakt Betrieb',
					    id:'contact_id',
					    name:'contact_id',
					    disabled: false,
					    onAddEditable: false,
					    onEditEditable: false,
					    blurOnSelect: true,
					    recordClass: Tine.Addressbook.Model.Contact,
					    width: 500,
					    allowBlank:false,
					    ddConfig:{
				        	ddGroup: 'ddGroupContact'
				        }
					})
				],[	
					{
					    fieldLabel: 'Betriebs-Nr',
					    id:'unit_nr',
					    name:'unit_nr',
					    value:null,
					    width: 500
					}
				 ],[
					{
						fieldLabel: 'Betrieb Bezeichnung',
					    id:'unit_name',
					    name:'unit_name',
					    value:null,
					    width: 500
					} 
	             ]
	        ]}]
	    };
	}
});

/**
 * Donator Edit Popup
 */
Tine.Donator.DonationUnitEditDialog.openWindow = function (config) {
    var id = (config.record && config.record.id) ? config.record.id : 0;
    var window = Tine.WindowFactory.getWindow({
        width: 600,
        height: 450,
        name: Tine.Donator.DonationUnitEditDialog.prototype.windowNamePrefix + id,
        contentPanelConstructor: 'Tine.Donator.DonationUnitEditDialog',
        contentPanelConstructorConfig: config
    });
    return window;
};