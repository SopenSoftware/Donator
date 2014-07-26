Ext.namespace('Tine.Donator');

Tine.Donator.CampaignEditDialog = Ext.extend(Tine.widgets.dialog.EditDialog, {
	
	/**
	 * @private
	 */
	windowNamePrefix: 'CampaignEditWindow_',
	appName: 'Donator',
	recordClass: Tine.Donator.Model.Campaign,
	recordProxy: Tine.Donator.campaignBackend,
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
						xtype: 'checkbox',
						disabledClass: 'x-item-disabled-view',
						id: 'donation_is_closed',
						name: 'is_closed',
						hideLabel:true,
					    boxLabel: 'Kampagne abgeschlossen',
					    width: 200
					}
				],[
					new Tine.Tinebase.widgets.form.RecordPickerComboBox({
                        fieldLabel: this.app.i18n._('Projekt'),
                        name: 'project_id',
                        blurOnSelect: true,
                        allowBlank:false,
                        recordClass: Tine.Donator.Model.Project,
                        width: 400
                    }),
                    {
					    fieldLabel: 'Kampagnen-Nr.',
					    emptyText:'<automatisch>',
					    disabledClass: 'x-item-disabled-view',
					    id:'campaign_nr',
					    name:'campaign_nr',
					    value:null,
					    disabled:true,
					    width: 150
					}
				],[
					{
					    fieldLabel: 'Bezeichnung',
					    id:'name',
					    name:'name',
					    value:null,
					    allowBlank:false,
					    width: 330
					},
					new Tine.Tinebase.widgets.form.RecordPickerComboBox({
				    	fieldLabel: 'Betrieb',
				    	disabledClass: 'x-item-disabled-view',
				    	id:'donation_unit_id',
				    	name: 'donation_unit_id',
				        blurOnSelect: true,
				        recordClass: Tine.Donator.Model.DonationUnit,
				        width: 220,
				        allowBlank:false
				    })
				 ],[
	
					new Tine.Tinebase.widgets.form.RecordPickerComboBox({
				    	fieldLabel: 'Bankkonto Eingang',
				    	disabledClass: 'x-item-disabled-view',
				    	id:'donation_donation_account_id',
				    	name: 'donation_account_id',
				        blurOnSelect: true,
				        recordClass: Tine.Donator.Model.DonationAccount,
				        width: 250,
				        allowBlank:true
				    }),
				    
			        Tine.Billing.Custom.getRecordPicker('AccountSystem', 'campaign_erp_proceed_account_id',
	        		{
	        		    fieldLabel: 'Erlöskonto',
	        		    name:'erp_proceed_account_id',
	        		    width: 150
	        		}),
	        		Tine.Billing.Custom.getRecordPicker('AccountSystem', 'account_system_account_class',
	        		{
	        		    fieldLabel: 'Aufwandskonto',
	        		    name:'erp_activity_account_id',
	        		    width: 150
	        		}),
	        		{
					    xtype:'hidden',
					    id:'cost_unit',
					    name:'cost_unit',
					    value:null
					}
				],[
				   {
					   xtype:'monetarynumfield',
					   fieldLabel: 'Budget',
					   id:'budget',
					   name:'budget',
					   value:0,
					   width: 150
				   },{
       		        	xtype: 'datefield',
    		            fieldLabel: 'Beginn', 
    		            id:'begin',
    		            name:'begin',
    		            width: 100
    		        },{
       		        	xtype: 'datefield',
    		            fieldLabel: 'Ende', 
    		            id:'end',
    		            name:'end',
    		            width: 100
    		        },{
    				    fieldLabel: 'Art Bedankung',
    				    disabledClass: 'x-item-disabled-view',
    				    id:'fundmaster_gratuation_kind',
    				    name:'gratuation_kind',
    				    width: 200,
    				    xtype:'combo',
    				    store:[['NO_VALUE','...keine Auswahl...'],['THANK_NO','Keine'],['THANK_STANDARD','Standard'],['THANK_INDIVIDUAL','Individuell']],
    				    value: 'NO_VALUE',
    					mode: 'local',
    					displayField: 'name',
    				    valueField: 'id',
    				    triggerAction: 'all'
    				}
    		    ],[     		        
					new Tine.Tinebase.widgets.form.RecordPickerComboBox({
					    fieldLabel: this.app.i18n._('Vorlage spezfisches Dankschreiben'),
					    id: 'gratuation_template_id',
					    name: 'gratuation_template_id',
					    blurOnSelect: true,
					    allowBlank:false,
					    recordClass: Tine.DocManager.Model.Template,
					    width: 300
					}),
					{
						xtype: 'fundcontactselect',
						width: 250,
						fieldLabel: 'Verantwortlicher Kontakt',
					    id:'responsible_contact_id',
					    name:'responsible_contact_id'
					}
				],[
					Tine.Billing.Custom.getRecordPicker('AccountSystem', 'donation_bank_account_system_id',
					{
					    fieldLabel: 'Bankkonto Fibu',
					    name:'bank_account_system_id',
					    width: 275,
					    displayFunc:'getTitle'
					}),
					Tine.Billing.Custom.getRecordPicker('AccountSystem', 'donation_debit_account_system_id',
					{
					    fieldLabel: 'Forderungskonto Fibu',
					    name:'debit_account_system_id',
					    width: 275,
					    displayFunc:'getTitle',
					    hasDefault:true,
					    defaultIndicatorField: 'number',
					    defaultComparisonValue: '14000',
					    autoSelectDefault: true
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
Tine.Donator.CampaignEditDialog.openWindow = function (config) {
    var id = (config.record && config.record.id) ? config.record.id : 0;
    var window = Tine.WindowFactory.getWindow({
        width: 600,
        height: 450,
        name: Tine.Donator.CampaignEditDialog.prototype.windowNamePrefix + id,
        contentPanelConstructor: 'Tine.Donator.CampaignEditDialog',
        contentPanelConstructorConfig: config
    });
    return window;
};