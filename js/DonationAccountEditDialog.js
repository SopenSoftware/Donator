Ext.namespace('Tine.Donator');

Tine.Donator.DonationAccountEditDialog = Ext.extend(Tine.widgets.dialog.EditDialog, {
	
	/**
	 * @private
	 */
	windowNamePrefix: 'DonationAccountEditWindow_',
	appName: 'Donator',
	recordClass: Tine.Donator.Model.DonationAccount,
	recordProxy: Tine.Donator.donationAccountBackend,
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
					    fieldLabel: 'Konto-Nr',
					    disabledClass: 'x-item-disabled-view',
					    id:'bank_account_nr',
					    name:'bank_account_nr',
					    value:null,
					    width: 150
					},{
					    fieldLabel: 'BLZ',
					    id:'bank_code',
					    name:'bank_code',
					    value:null,
					    width: 150
					}
				 ],[
	     			{
					    fieldLabel: 'Bank Bezeichnung',
					    disabledClass: 'x-item-disabled-view',
					    id:'bank_name',
					    name:'bank_name',
					    value:null,
					    width: 150
					},{
					    fieldLabel: 'Kontoinhaber',
					    id:'account_name',
					    name:'account_name',
					    value:null,
					    width: 150
					}
				],[	
				   Tine.Billing.Custom.getRecordPicker('AccountSystem', 'dacc_bank_account_system_id',
					{
					    fieldLabel: 'Bankkonto Fibu',
					    name:'bank_account_system_id',
					    width: 275,
					    displayFunc:'getTitle'
					})
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
Tine.Donator.DonationAccountEditDialog.openWindow = function (config) {
    var id = (config.record && config.record.id) ? config.record.id : 0;
    var window = Tine.WindowFactory.getWindow({
        width: 600,
        height: 450,
        name: Tine.Donator.DonationAccountEditDialog.prototype.windowNamePrefix + id,
        contentPanelConstructor: 'Tine.Donator.DonationAccountEditDialog',
        contentPanelConstructorConfig: config
    });
    return window;
};