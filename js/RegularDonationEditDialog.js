Ext.ns('Tine.Donator');

Tine.Donator.RegularDonationEditHandler = function(config){
	config = config || {};
    Ext.apply(this, config);

    Tine.Donator.RegularDonationEditHandler.superclass.constructor.call(this);
};

Ext.extend(Tine.Donator.RegularDonationEditHandler, Ext.util.Observable, {
	parentPanel:null,
	initialize: function(parentPanel){
		// disable some fields? necessary here?
		//this.disableFields();
		this.parentPanel = parentPanel;
		this.getFundMasterSelect().addListener('select',this.onFundMasterSelect, this);
		this.getFundMasterSelect().addListener('change',this.onFundMasterSelect, this);
		this.getCampaignSelect().addListener('select',this.onCampaignSelect, this);
		this.getCampaignSelect().addListener('change',this.onCampaignSelect, this);
		
	},
	focusFirst: function(){
		this.getCampaignSelect().focus();
	},
	onFundMasterSelect: function( fundMasterRecord ){
		var fundMaster = fundMasterRecord.selectedRecord;
		Ext.getCmp('regular_donation_confirmation_kind').setValue(fundMaster.get('confirmation_kind'));
		Ext.getCmp('regular_donation_gratuation_kind').setValue(fundMaster.get('gratuation_kind'));
		Ext.getCmp('regular_donation_bank_account_nr').setValue(fundMaster.get('bank_account_nr'));
		Ext.getCmp('regular_donation_bank_code').setValue(fundMaster.get('bank_code'));
		Ext.getCmp('regular_donation_bank_name').setValue(fundMaster.get('bank_name'));
		Ext.getCmp('regular_donation_account_name').setValue(fundMaster.get('account_name'));
	},
	onCampaignSelect: function(campaignRecord){
		var campaign = campaignRecord.selectedRecord;
		if(campaign){
			var gratuationKind = campaign.get('gratuation_kind');
		
			if(gratuationKind !== 'NO_VALUE'){
				Ext.getCmp('regular_donation_gratuation_kind').setValue(gratuationKind);
			}
			var donationAccount = campaign.getForeignRecord(Tine.Donator.Model.DonationAccount, 'donation_account_id');
			if(donationAccount){
				Ext.getCmp('regular_donation_donation_account_id').setValue(donationAccount);
			}
			var erpProceedAccount = campaign.getForeignRecord(Tine.Billing.Model.AccountSystem, 'erp_proceed_account_id');
			if(erpProceedAccount){
				Ext.getCmp('regular_donation_erp_proceed_account_id').setValue(erpProceedAccount);
			}
		}
	},
	getFundMasterSelect: function(){
		return Ext.getCmp('regular_donation_fundmaster_id');
	},
	getCampaignSelect: function(){
		return Ext.getCmp('regular_donation_campaign_id');
	}
});

Tine.Donator.RegularDonationEditDialog = Ext.extend(Tine.widgets.dialog.EditDialog, {
	/**
	 * @private
	 */
	windowNamePrefix: 'RegularDonationtEditWindow_',
	appName: 'Donator',
	recordClass: Tine.Donator.Model.RegularDonation,
	recordProxy: Tine.Donator.regularDonationBackend,
	fundMaster: null,
	loadRecord: false,
	evalGrants: false,
	disabledClass: 'x-item-dissi',
	bankAccountWidget: null,
	
	initComponent: function(){
		
		this.action_editFundMaster = new Ext.Action({
            actionType: 'edit',
            handler: this.onEditFundMaster,
            iconCls: 'action_edit',
            scope: this
        });
		
		this.action_editLastDonation = new Ext.Action({
            actionType: 'edit',
            handler: this.onEditLastDonation,
            iconCls: 'action_edit',
            scope: this
        });
		
		this.action_editLastReceipt = new Ext.Action({
            actionType: 'edit',
            handler: this.onEditLastReceipt,
            iconCls: 'action_edit',
            scope: this
        });
		this.action_reverseLastExecution = new Ext.Action({
            id: 'reverseLastExecButton',
            text: 'Letzte Ausführung stornieren',
            handler: this.askReverseLastExecution,
            //disabled: true,
            scope: this
        });
		this.action_executeNow = new Ext.Action({
            id: 'execNowButton',
            text: 'Jetzt ausführen',
            handler: this.askExecuteNow,
            //disabled: true,
            scope: this
        });
		
		this.actions_action = new Ext.Action({
        	allowMultiple: false,
            text: 'Aktionen',
            menu:{
            	items:[
            	       this.action_executeNow,
					   this.action_reverseLastExecution
            	]
            }
        });
		
		this.initWidgets();
		
		Tine.Donator.RegularDonationEditDialog.superclass.initComponent.call(this);
		this.on('afterrender',this.onAfterRender, this);
		this.on('load',this.onLoadRegularDonation, this);
	},
	initButtons: function(){
		Tine.Donator.RegularDonationEditDialog.superclass.initButtons.call(this);
		this.tbar = [
    	   '->',
    	   Ext.apply(new Ext.Button(this.action_editFundMaster), {
				 text: 'Gehe zu Spender',
	             scale: 'small',
	             rowspan: 2,
	             iconAlign: 'left'
	        }),
	        Ext.apply(new Ext.Button(this.action_editLastDonation), {
				 text: 'Öffne letzte Spende',
	             scale: 'small',
	             rowspan: 2,
	             iconAlign: 'left'
	        }),
	        Ext.apply(new Ext.Button(this.action_editLastReceipt), {
				 text: 'Öffne letzte Rechnung',
	             scale: 'small',
	             rowspan: 2,
	             iconAlign: 'left'
	        }),
	        
	        Ext.apply(new Ext.Button(this.actions_action), {
				 text: 'Aktionen',
	             scale: 'small',
	             rowspan: 2,
	             iconAlign: 'left'
	        })
	        
	 	];		
	},
	initWidgets: function(){
		var fields = Tine.Donator.RegularDonationFormFields.get();
		
		this.bankAccountWidget = new Tine.Billing.BankAccountWidget();
		this.bankAccountWidget.init('Donator');
		this.bankAccountWidget.setFormComponent({
		   xtype: 'fieldset',
		   collapsible: true,
		   title: 'Bank-/Zahlungsdaten',
		   width:500,
		   layout:'fit',
		   items:[
          	{xtype:'columnform', 
          		items:[[
          		       fields.donation_payment_interval,
          		       this.bankAccountWidget.registerPaymentMethodField(
      		    		 fields.donation_payment_method
          		       )
          		],[
          		   this.bankAccountWidget.registerBankAccountSelector({
          			   name:'bank_account_id'
          		   }),
          		   this.bankAccountWidget.getButtonOpenBankAccount(),
          		   this.bankAccountWidget.getButtonUnlinkBankAccount(),
          		   this.bankAccountWidget.getButtonToggleBankAccountLock()
          		],[
      		       this.bankAccountWidget.registerIbanField({
          			   name:'iban'
          		   }),
          		   this.bankAccountWidget.registerNameField({
          			   name:'bank_account_name'
          		   }),
          		   this.bankAccountWidget.getButtonUpdateBankAccount(),
      	        ],[
					this.bankAccountWidget.registerSepaMandateIdentField({
						name:'sepa_mandate_ident'
				  }),
				  	this.bankAccountWidget.registerSepaMandateSignatureDateField({
					   name:'sepa_signature_date'
				  	}),
				  	this.bankAccountWidget.getButtonOpenSepaMandate()
      	        ],[
      		       this.bankAccountWidget.registerBicField({
          			   name:'bic'
          		   }),
      		       this.bankAccountWidget.registerBankCodeField({
          			   name:'bank_account_bank_code'
          		   }),
          		   this.bankAccountWidget.registerNumberField({
          			   name:'bank_account_number'
          		   })
      		    ],[
      		       this.bankAccountWidget.registerBankNameField({
          			   name:'bank_account_bank_name'
          		   })
      		    ],[
      		       this.bankAccountWidget.registerBankSelector({hidden:true, hideLabel:true}),this.bankAccountWidget.registerSepaMandateSelector({hidden:true, hideLabel:true})
          	    ]]                                                            
          	}
		   ]
	   });
	},
	onEditFundMaster: function(){
	   	this.fundMasterWin = Tine.Donator.FundMasterEditDialog.openWindow({
			record: new Tine.Donator.Model.FundMaster(this.record.data.fundmaster_id,this.record.data.fundmaster_id.id)
		});
	},
	onEditLastDonation: function(){
		if(this.record.getForeignId('last_donation_id')){
		   	this.lastDonWin = Tine.Donator.DonationEditDialog.openWindow({
				record: new Tine.Donator.Model.Donation({id:this.record.data.last_donation_id},this.record.data.last_donation_id)
			});
		}
	},
	onEditLastReceipt: function(){
		if(this.record.getForeignId('last_receipt_id')){
		   	this.lastReceiptWin = Tine.Billing.InvoiceEditDialog.openWindow({
				record: new Tine.Billing.Model.Receipt({id:this.record.data.last_receipt_id},this.record.data.last_receipt_id)
			});
		}
	},
	askExecuteNow: function(){
		Ext.MessageBox.show({
            title: 'Frage', 
            msg: 'Möchten Sie die Sollstellung jetzt durchführen?',
            buttons: Ext.Msg.YESNO,
            scope:this,
            fn: this.doExecuteNow,
            icon: Ext.MessageBox.QUESTION
        });
	},
	doExecuteNow: function(btn, text){
		if(btn!='yes'){
			return;
		}
		this.executeNow(btn, null, true);
	},
	executeNow: function(btn, event, proceed){
		if(proceed){
			Ext.Ajax.request({
	            scope: this,
	            success: this.onExecuteNow,
	            params: {
	                method: 'Donator.executeRegularDonationNow',
	               	regularDonationId:  this.record.get('id')
	            },
	            failure: this.onExecuteNowFailed
	        });
		}else{
			this.askExecuteNow();
		}
	},
	onExecuteNow: function(response){
		this.executeNowResponse = response;
		if(this.invertWin){
			this.invertWin.hide();
			this.invertWin = null;
		}
		Ext.MessageBox.show({
            title: 'Erfolg', 
            msg: 'Die Sollstellung wurde erfolgreich durchgeführt?',
            buttons: Ext.Msg.YESNO,
            scope:this,
            fn: this.showCreditDialog,
            icon: Ext.MessageBox.INFO
        });
	},
	onExecuteNowFailed: function(){
		Ext.MessageBox.show({
            title: 'Fehler', 
            msg: 'Das Sollstellung ist fehlgeschlagen.',
            buttons: Ext.Msg.OK,
            icon: Ext.MessageBox.WARNING
        });
	},
	hasLastExecution: function(){
    	if(this.record){
    		var hasLastReceipt = this.record.getForeignId('last_receipt_id');
    		var hasLastDonation = this.record.getForeignId('last_donation_id');
    		
    		if(hasLastReceipt && hasLastDonation){
    			return true;
    		}
    		return false;
    	}else{
    		Ext.Msg({
    			title: 'Hinweis', 
	            msg: 'Es ist noch kein Datensatz geladen.',
	            buttons: Ext.Msg.OK,
	            icon: Ext.MessageBox.INFO
    		});
    		return false;
    	}
    },
	askReverseLastExecution: function(){
		Ext.MessageBox.show({
            title: 'Frage', 
            msg: 'Möchten Sie die letzte Sollstellung tatsächlich stornieren?',
            buttons: Ext.Msg.YESNO,
            scope:this,
            fn: this.doReverseLastExecution,
            icon: Ext.MessageBox.QUESTION
        });
	},
	doReverseLastExecution: function(btn, text){
		if(btn!='yes'){
			return;
		}
		this.reverseLastExecution(btn, null, true);
	},
	reverseLastExecution: function(btn, event, proceed){
		if(proceed){
			Ext.Ajax.request({
	            scope: this,
	            success: this.onReverseLastExecution,
	            params: {
	                method: 'Donator.reverseLastRegularDonationExecution',
	               	regularDonationId:  this.record.get('id')
	            },
	            failure: this.onReverseLastExecutionFailed
	        });
		}else{
			this.askReverseLastExecution();
		}
	},
	onReverseLastExecution: function(response){
		this.reverseLastExecutionResponse = response;
		if(this.invertWin){
			this.invertWin.hide();
			this.invertWin = null;
		}
		Ext.MessageBox.show({
            title: 'Erfolg', 
            msg: 'Die letzte Sollstellung wurde erfolgreich storniert.</br>Möchten Sie den Gutschriftsbeleg öffnen?',
            buttons: Ext.Msg.YESNO,
            scope:this,
            fn: this.showCreditDialog,
            icon: Ext.MessageBox.INFO
        });
	},
	onReverseLastExecutionFailed: function(){
		Ext.MessageBox.show({
            title: 'Fehler', 
            msg: 'Das Stornieren der letzten Ausführung ist fehlgeschlagen.',
            buttons: Ext.Msg.OK,
            icon: Ext.MessageBox.WARNING
        });
	},
	onLoadRegularDonation: function(){
		if(this.record.id == 0){
			if(this.fundMaster){
				var fundMasterRecord = new Tine.Donator.Model.FundMaster(this.fundMaster.data, this.fundMaster.id);
				this.record.data.fundmaster_id = fundMasterRecord;
				this.record.data.gratuation_kind = fundMasterRecord.get('gratuation_kind');
				this.record.data.confirmation_kind = fundMasterRecord.get('confirmation_kind');
				this.record.data.bank_account_nr = fundMasterRecord.get('bank_account_nr');
				this.record.data.bank_code = fundMasterRecord.get('bank_code');
				this.record.data.bank_name = fundMasterRecord.get('bank_name');
				this.record.data.account_name = fundMasterRecord.get('account_name');
				
				if(this.bankAccountWidget){
					this.bankAccountWidget.triggerUpdate();
					this.bankAccountWidget.setContactId(fundMasterRecord.getForeignId('contact_id'));
				}
			}
			
			if(this.memberRecord){
				this.record.data.bank_account_nr = this.memberRecord.get('bank_account_nr');
				this.record.data.bank_code = this.memberRecord.get('bank_code');
				this.record.data.bank_name = this.memberRecord.get('bank_name');
				this.record.data.account_name = this.memberRecord.get('account_holder');
				this.record.data.donation_payment_interval = this.memberRecord.get('fee_payment_interval');
				this.record.data.donation_payment_method = this.memberRecord.getForeignId('fee_payment_method');
			}
		}else{
			if(this.bankAccountWidget){
				this.bankAccountWidget.triggerUpdate();
				var fundMasterRecord = this.record.getForeignRecord(Tine.Donator.Model.FundMaster, 'fundmaster_id');
				this.bankAccountWidget.setContactId(fundMasterRecord.getForeignId('contact_id'));
			}
			
			if(this.hasLastExecution()){
				this.action_reverseLastExecution.enable();
			}else{
				this.action_reverseLastExecution.disable();
			}
		}
	},
	onAfterRender: function(){
		this.editHandler = new Tine.Donator.RegularDonationEditHandler();
		this.editHandler.initialize(this);
		this.editHandler.focusFirst();
		
		this.bankAccountWidget.finalize();
	},
	
	getFormItems: function(){
		return Tine.Donator.getRegularDonationEditPanel(this.bankAccountWidget);
	}
});

Tine.Donator.RegularDonationEditDialog.openWindow = function (config) {
    var id = (config.record && config.record.id) ? config.record.id : 0;
    var window = Tine.WindowFactory.getWindow({
        width: 520,
        height: 600,
        name: Tine.Donator.RegularDonationEditDialog.prototype.windowNamePrefix + id,
        contentPanelConstructor: 'Tine.Donator.RegularDonationEditDialog',
        contentPanelConstructorConfig: config
    });
    return window;
};

Tine.Donator.getRegularDonationEditPanel = function(bankAccountWidget){
	
	return {
		xtype: 'panel',
		id: 'Donator-donation-edit-dialog-panel',
		border: false,
		frame: true,
		cls: 'tw-editdialog',
		layout:'border',
		defferedRender:true,
		items:[{
			xtype:'panel',
			region:'center',
			layout:'fit',
			autoScroll: true,
			/*border:false,
			width: 400,
			defaults: {
		        xtype: 'fieldset',
		        // -> never: IE killer
		        //autoHeight: 'auto',
		        layout:'fit',
		        defaultType: 'textfield'
		    },*/
		    items: Tine.Donator.getRegularDonationFormItems(bankAccountWidget)
		}]};
}
Ext.ns('Tine.Donator.RegularDonationFormFields');

Tine.Donator.RegularDonationFormFields.get = function(){
	return {
		// hidden fields
		id: 
			{xtype: 'hidden',id:'regular_donation_id',name:'id'},
		fund_master_id:
			new Tine.Tinebase.widgets.form.RecordPickerComboBox({
		        fieldLabel: 'Spender',
		        disabledClass: 'x-item-disabled-view',
		        id:'regular_donation_fundmaster_id',
		        name: 'fundmaster_id',
		        onAddEditable: false,
		        onEditEditable:false,
		        blurOnSelect: true,
		        recordClass: Tine.Donator.Model.FundMaster,
		        itemSelector: 'div.search-fund-master',
		        tpl:  new Ext.XTemplate(
	                '<tpl for="."><div class="search-fund-master">',
	                    '<table cellspacing="0" cellpadding="2" border="0" style="font-size: 11px;" width="100%">',
	                        '<tr>',
	                            '<td width="30%"><b>{[this.encode(values.contact_id.n_fileas)]}</b><br/>{[this.encode(values.contact_id.org_name)]}</td>',
	                            '<td width="25%">{[this.encode(values.contact_id.adr_one_street)]}<br/>',
	                                '{[this.encode(values.contact_id.adr_one_postalcode)]} {[this.encode(values.contact_id.adr_one_locality)]}</td>',
	                            '<td width="25%">{[this.encode(values.contact_id.tel_work)]}<br/>{[this.encode(values.contact_id.tel_cell)]}</td>',
	                        '</tr>',
	                    '</table>',
	                '</div></tpl>',
	                {
	                    encode: function(value) {
	                         if (value) {
	                            return Ext.util.Format.htmlEncode(value);
	                        } else {
	                            return '';
	                        }
	                    }
	                }
	            ),
		        width: 300,
		        allowBlank:false
		    }),
		 campaign_id:
			 Tine.Donator.Custom.getRecordPicker('Campaign','regular_donation_campaign_id',{
		    	fieldLabel: 'Kampagne/Verwendungszweck',
		    	disabledClass: 'x-item-disabled-view',
		    	name: 'campaign_id',
			    appendFilters: [{field: 'is_closed', operator: 'equals', value: false }],
			    disabled: false,
			    width:250,
			    onAddEditable: true,
			    onEditEditable: false,
			    blurOnSelect: true,
			    allowBlank:true
			}),
		donation_account_id:
			new Tine.Tinebase.widgets.form.RecordPickerComboBox({
		    	fieldLabel: 'Bankkonto Spendeneingang',
		    	disabledClass: 'x-item-disabled-view',
		    	id:'regular_donation_donation_account_id',
		    	name: 'donation_account_id',
		        blurOnSelect: true,
		        recordClass: Tine.Donator.Model.DonationAccount,
		        width: 200,
		        allowBlank:false,
		        tabIndex:6
		    }),
		 erp_proceed_account_id:
			Tine.Billing.Custom.getRecordPicker('AccountSystem', 'regular_donation_erp_proceed_account_id',
    		{
    		    fieldLabel: 'Erlöskonto',
    		    name:'erp_proceed_account_id',
    		    width: 150
    		}),
    	 begin_date:
	    	{
	 		   	xtype: 'datefield',
	 		   	disabledClass: 'x-item-disabled-view',
	 		   	fieldLabel: 'gültig ab', 
	 		   	id:'regular_donation_begin_date',
	 		   	name:'begin_date',
	 		   	width: 150,
	 		   	allowBlank:false
		 	},
		 next_date:
	    	{
	 		   	xtype: 'datefield',
	 		   	disabledClass: 'x-item-disabled-view',
	 		   	fieldLabel: 'nächste Fälligkeit', 
	 		   	id:'regular_donation_next_date',
	 		   	name:'next_date',
	 		   	width: 150
		 	},
		 last_date:
	    	{
	 		   	xtype: 'datefield',
	 		   	disabledClass: 'x-item-disabled-view',
	 		   	fieldLabel: 'zuletzt ausgeführt', 
	 		   	id:'regular_donation_last_date',
	 		   	name:'last_date',
	 		   	width: 150
		 	},
		 end_date:
	    	{
	 		   	xtype: 'extuxclearabledatefield',
	 		   	disabledClass: 'x-item-disabled-view',
	 		   	fieldLabel: 'gültig bis', 
	 		   	id:'regular_donation_end_date',
	 		   	name:'end_date',
	 		   	width: 150,
	 	        tabIndex:2
		 	},
		 reg_donation_amount:
		 	{
				xtype:'monetarynumfield',
				disabledClass: 'x-item-disabled-view',
				fieldLabel: 'Spendenbetrag',
				id:'regular_donation_reg_donation_amount',
				name:'reg_donation_amount',
				width: 150,
		        tabIndex:5
			},
		gratuation_kind:
			{
			    fieldLabel: 'Art Bedankung',
			    disabledClass: 'x-item-disabled-view',
			    id:'regular_donation_gratuation_kind',
			    name:'gratuation_kind',
			    width: 225,
			    xtype:'combo',
			    store:[['THANK_NO','Keine'],['THANK_STANDARD','Standard'],['THANK_INDIVIDUAL','Individuell']],
			    value: 'THANK_NO',
			    mode: 'local',
			    displayField: 'name',
			    valueField: 'id',
			    triggerAction: 'all',
		        tabIndex:3
			},
		confirmation_kind:
			{
				fieldLabel: 'Art Bestätigung',
				disabledClass: 'x-item-disabled-view',
				id:'regular_donation_confirmation_kind',
				name:'confirmation_kind',
				width: 225,
				xtype:'combo',
				store:[['CONFIRMATION_COLLECT','Sammelquittung'],['CONFIRMATION_SINGLE','Einzelquittung'],['CONFIRMATION_NO','Keine Quittung']],
				value: 'CONFIRMATION_SINGLE',
				mode: 'local',
				displayField: 'name',
				valueField: 'id',
				triggerAction: 'all',
		        tabIndex:4
			},
		donation_payment_interval:
			{
				width:200,
			    fieldLabel: 'Zahlungsweise', 
			    disabledClass: 'x-item-disabled-view',
			    id:'regular_donation_donation_payment_interval',
			    xtype:'combo',
			    store:[['NOVALUE','...keine Auswahl...'],['YEAR','jährlich'],['HALF','halbjährlich'],['QUARTER','quartalsweise'],['MONTH','monatlich']],
			    value: 'NOVALUE',
			    name:'donation_payment_interval',
			    mode: 'local',
				displayField: 'name',
			    valueField: 'id',
			    triggerAction: 'all'
			},
		donation_payment_method:
			{
			    fieldLabel: 'Zahlungsart',
			    disabledClass: 'x-item-disabled-view',
			    id:'regular_donation_donation_payment_method',
			    name:'donation_payment_method',
			    width:200,
			    xtype:'combo',
			    store:[['NOVALUE','...keine Auswahl...'],['DEBIT','Lastschrift'],['BANKTRANSFER','Überweisung']],
				value: 'NOVALUE',
			    mode: 'local',
				displayField: 'name',
			    valueField: 'id',
			    triggerAction: 'all'
			 },
		bank_code:
			 {
		        	xtype:'textfield',
		        	disabledClass: 'x-item-disabled-view',
		        	width:200,
		            fieldLabel: 'BLZ', 
		            id:'regular_donation_bank_code',
		            name:'bank_code'
		        },
		     bank_name:   
		        {
		        	xtype:'textfield',
		        	disabledClass: 'x-item-disabled-view',
		        	width:200,
		            fieldLabel: 'Bank-Name', 
		            id:'regular_donation_bank_name',
		            name:'bank_name'
		        },
		    bank_account_nr:   
		        {
		        	xtype:'textfield',
		        	disabledClass: 'x-item-disabled-view',
		        	width:200,
		            fieldLabel: 'Kontonummer', 
		            id:'regular_donation_bank_account_nr',
		            name:'bank_account_nr'
		        },
		    account_name:    
		        {
		        	xtype:'textfield',
		        	disabledClass: 'x-item-disabled-view',
		        	width:200,
		            fieldLabel: 'Kontoinhaber', 
		            id:'regular_donation_account_name',
		            name:'account_name'
		        },
		   donation_usage:
		    {
				xtype: 'textarea',
				fieldLabel: 'Verwendungszweck',
				disabledClass: 'x-item-disabled-view',
				id:'regular_donation_donation_usage',
				name:'donation_usage',
				width: 450,
				height: 80
			},
		regular_donation_nr:
			{
			   fieldLabel: 'Spendenauftr. Nr',
				disabledClass: 'x-item-disabled-view',
				id:'regular_donation_regular_donation_nr',
				name:'regular_donation_nr',
				disabled:true,
				onAddEditable:false,
				onEditEditable:false,
			    value:null,
			    //allowBlank:false,
			    width: 150
		    },
		on_hold:
		{
			xtype: 'checkbox',
			disabledClass: 'x-item-disabled-view',
			id: 'regular_donation_on_hold',
			name: 'on_hold',
			hideLabel:true,
			disabled:false,
		    boxLabel: 'ausgesetzt',
		    width: 200
		},
		terminated:
		{
			xtype: 'checkbox',
			disabledClass: 'x-item-disabled-view',
			id: 'regular_donation_terminated',
			name: 'terminated',
			hideLabel:true,
			disabled:false,
		    boxLabel: 'beendet',
		    width: 200
		}
	};
};

Tine.Donator.getRegularDonationFormItems = function(bankAccountWidget){
	
	var fields = Tine.Donator.RegularDonationFormFields.get();
	
	return [
	   {xtype:'columnform',border:false,items:[[
	   {xtype: 'hidden',id:'regular_donation_id',name:'id'}
		 ],[
		    	fields.on_hold,
		    	fields.terminated
		 ],[
		      fields.fund_master_id,
		      fields.regular_donation_nr
		 ],[  
		       fields.campaign_id,
			   fields.donation_account_id
		 ],[
		    	fields.erp_proceed_account_id,
				fields.begin_date,
				fields.end_date
		 ],[
				fields.next_date,
				fields.last_date,
				fields.reg_donation_amount
		 ],[  
				fields.gratuation_kind,
				fields.confirmation_kind
		 ],[ 
		    bankAccountWidget.getFormComponent()

		    /*
				fields.donation_payment_interval,
				fields.donation_payment_method
		 ],[  
		    	fields.bank_code,
				fields.bank_name
		 ],[
				fields.bank_account_nr,
				fields.account_name*/
		],[
		   		fields.donation_usage
	
	]]}  
	];
}