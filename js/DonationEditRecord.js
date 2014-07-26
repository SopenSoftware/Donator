Ext.ns('Tine.Donator');

Tine.Donator.DonationEditHandler = function(config){
	config = config || {};
    Ext.apply(this, config);

    Tine.Donator.DonationEditHandler.superclass.constructor.call(this);
};

Ext.extend(Tine.Donator.DonationEditHandler, Ext.util.Observable, {
	parentPanel:null,
	initialize: function(parentPanel){
		// disable some fields? necessary here?
		//this.disableFields();
		this.parentPanel = parentPanel;
		this.getFundMasterSelect().addListener('select',this.onFundMasterSelect, this);
		this.getFundMasterSelect().addListener('change',this.onFundMasterSelect, this);
		this.getCampaignSelect().addListener('select',this.onCampaignSelect, this);
		this.getCampaignSelect().addListener('change',this.onCampaignSelect, this);
		
		this.getNonMonetarySelect().addListener('check',this.onNonMonetarySelect, this);

		this.setDefaultDate();
		if(this.parentPanel.record.get('confirmation_date')){
			if(!Tine.Tinebase.common.hasRight('edit_afterprint', 'Donator')){
				this.parentPanel.setDisabled(true);
			}
			if(Tine.Tinebase.common.hasRight('reset_donation', 'Donator')){
				this.getConfirmationDate().enable();
				this.getGratuationDate().enable();
			}
		}
		
	},
	focusFirst: function(){
		this.getCampaignSelect().focus();
	},
	onFundMasterSelect: function( fundMasterRecord ){
		var fundMaster = fundMasterRecord.selectedRecord;
		Ext.getCmp('donation_confirmation_kind').setValue(fundMaster.get('confirmation_kind'));
		Ext.getCmp('donation_gratuation_kind').setValue(fundMaster.get('gratuation_kind'));
	},
	onCampaignSelect: function(campaignRecord){
		var campaign = campaignRecord.selectedRecord;
		if(campaign){
			var gratuationKind = campaign.get('gratuation_kind');
		
			if(gratuationKind !== 'NO_VALUE'){
				Ext.getCmp('donation_gratuation_kind').setValue(gratuationKind);
			}
			var donationAccount = campaign.getForeignRecord(Tine.Donator.Model.DonationAccount, 'donation_account_id');
			if(donationAccount){
				Ext.getCmp('donation_donation_account_id').setValue(donationAccount);
			}
			
			var erpProceedAccSystem = campaign.getForeignRecord(Tine.Billing.Model.AccountSystem, 'erp_proceed_account_id');
			if(erpProceedAccSystem){
				Ext.getCmp('donation_erp_proceed_account_id').setValue(erpProceedAccSystem);
			}
		}
	},
	onNonMonetarySelect: function( select ){
		var value = select.getValue();
		switch(value){
		case true:
				this.getNonMonetaryExpander().expand();
				this.getNonMonetarySource().enable();
				this.getNonMonetaryRating().enable();
			break;
			
		case false:
			this.getNonMonetaryExpander().collapse();
			this.getNonMonetarySource().disable();
			this.getNonMonetaryRating().disable();
			break;
		}
	},
	getNonMonetaryExpander: function(){
		return Ext.getCmp('nonMonetaryAddition');
	},
	getFundMasterSelect: function(){
		return Ext.getCmp('donation_fundmaster_id');
	},
	getCampaignSelect: function(){
		return Ext.getCmp('donation_campaign_id');
	},
	getNonMonetarySelect: function(){
		return Ext.getCmp('donation_non_monetary');
	},
	getNonMonetarySource: function(){
		return Ext.getCmp('donation_non_monetary_source');
	},
	getNonMonetaryRating: function(){
		return Ext.getCmp('donation_non_monetary_rating');
	},
	getRefundQuitclaim: function(){
		return Ext.getCmp('donation_refund_quitclaim');
	},
	getConfirmationDate: function(){
		return Ext.getCmp('donation_confirmation_date');
	},
	getGratuationDate: function(){
		return Ext.getCmp('donation_thanks_date');
	},
	setDefaultDate: function(){
		if(!Ext.getCmp('donation_donation_date').getValue()){
			Ext.getCmp('donation_donation_date').setValue(new Date());
		}
	}
});

Tine.Donator.DonationEditDialog = Ext.extend(Tine.widgets.dialog.EditDialog, {
	/**
	 * @private
	 */
	windowNamePrefix: 'DonationtEditWindow_',
	appName: 'Donator',
	recordClass: Tine.Donator.Model.Donation,
	recordProxy: Tine.Donator.donationBackend,
	fundMaster: null,
	memberRecord: null,
	loadRecord: false,
	evalGrants: false,
	 disabledClass: 'x-item-dissi',
	
	initComponent: function(){
		this.action_editFundMaster = new Ext.Action({
            actionType: 'edit',
            handler: this.onEditFundMaster,
            iconCls: 'action_edit',
            scope: this
        });
		this.action_reverseDonation = new Ext.Action({
            id: 'reverseButton',
            text: 'Spende stornieren',
            handler: this.reverseDonation,
            disabled: true,
            scope: this
        });
		this.on('afterrender',this.onAfterRender, this);
		this.on('load',this.onLoadDonation, this);
		Tine.Donator.DonationEditDialog.superclass.initComponent.call(this);
		
	},
	initButtons: function(){
		Tine.Donator.DonationEditDialog.superclass.initButtons.call(this);
		this.tbar = [
    	   '->',
    	   Ext.apply(new Ext.Button(this.action_editFundMaster), {
				 text: 'Gehe zu Spender',
	             scale: 'small',
	             rowspan: 2,
	             iconAlign: 'left'
	        }),
    	   Ext.apply(new Ext.Button(this.action_reverseDonation), {
				 text: 'Spende stornieren',
	             scale: 'small',
	             rowspan: 2,
	             iconAlign: 'left'
	        })
	 	];		
	},
	onEditFundMaster: function(){
	   	this.fundMasterWin = Tine.Donator.FundMasterEditDialog.openWindow({
			record: new Tine.Donator.Model.FundMaster(this.record.data.fundmaster_id,this.record.data.fundmaster_id.id)
		});
	},
	onLoadDonation: function(){
		if(this.record.id == 0){
			if(this.fundMaster){
				var fundMasterRecord = new Tine.Donator.Model.FundMaster(this.fundMaster.data, this.fundMaster.id);
				this.record.data.fundmaster_id = fundMasterRecord;
				this.record.data.gratuation_kind = fundMasterRecord.get('gratuation_kind');
				this.record.data.confirmation_kind = fundMasterRecord.get('confirmation_kind');
				
			}
			
			if(this.memberRecord){
				this.record.data.bank_account_nr = this.memberRecord.get('bank_account_nr');
				this.record.data.bank_code = this.memberRecord.get('bank_code');
				this.record.data.bank_name = this.memberRecord.get('bank_name');
				this.record.data.account_name = this.memberRecord.get('account_holder');
			}
		}else{
			if(!this.isReverse()){
				this.action_reverseDonation.enable();
			}else{
				this.action_reverseDonation.disable();
			}
		}
	},
	isReverse: function(){
    	if(this.record){
    		var isCancellation = this.record.get('is_cancellation');
    		var isCancelled = this.record.get('is_cancelled');
    		
    		return (isCancellation | isCancelled);
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
	askReverseDonation: function(){
		Ext.MessageBox.show({
            title: 'Frage', 
            msg: 'Möchten Sie die Spende tatsächlich stornieren?',
            buttons: Ext.Msg.YESNO,
            scope:this,
            fn: this.doReverseDonation,
            icon: Ext.MessageBox.QUESTION
        });
	},
	doReverseDonation: function(btn, text){
		if(btn!='yes'){
			return;
		}
		this.reverseDonation(btn, null, true);
	},
	reverseDonation: function(btn, event, proceed){
		if(proceed){
			Ext.Ajax.request({
	            scope: this,
	            success: this.onReverseDonation,
	            params: {
	                method: 'Donator.reverseDonation',
	               	donationId:  this.record.get('id')
	            },
	            failure: this.onReverseDonationFailed
	        });
		}else{
			this.askReverseDonation();
		}
	},
	onReverseDonation: function(response){
		this.reverseDonationResponse = response;
		if(this.invertWin){
			this.invertWin.hide();
			this.invertWin = null;
		}
		Ext.MessageBox.show({
            title: 'Erfolg', 
            msg: 'Die Spende wurde erfolgreich storniert.</br>Möchten Sie den Gutschriftsbeleg öffnen?',
            buttons: Ext.Msg.YESNO,
            scope:this,
            fn: this.showCreditDialog,
            icon: Ext.MessageBox.INFO
        });
	},
	onReverseDonationFailed: function(){
		Ext.MessageBox.show({
            title: 'Fehler', 
            msg: 'Das Stornieren der Spende ist fehlgeschlagen.',
            buttons: Ext.Msg.OK,
            icon: Ext.MessageBox.WARNING
        });
	},
	onAfterRender: function(){
		if(!Tine.Tinebase.common.hasRight('__edit', 'Donator')){
			 this.action_saveAndClose.disable();
			 this.action_applyChanges.disable();
		}
		
		this.editHandler = new Tine.Donator.DonationEditHandler();
		this.editHandler.initialize(this);
		this.editHandler.focusFirst();
	},
	
	getFormItems: function(){
		return Tine.Donator.getDonationEditPanel();
	}
});

Tine.Donator.DonationEditDialog.openWindow = function (config) {
    var id = (config.record && config.record.id) ? config.record.id : 0;
    var window = Tine.WindowFactory.getWindow({
        width: 520,
        height: 600,
        name: Tine.Donator.DonationEditDialog.prototype.windowNamePrefix + id,
        contentPanelConstructor: 'Tine.Donator.DonationEditDialog',
        contentPanelConstructorConfig: config
    });
    return window;
};

Tine.Donator.DonationEditRecord =  Ext.extend(Tine.widgets.dialog.DependentEditForm, {
	id: 'sopen-Donator-donation-edit-record-form',
	className: 'Tine.Donator.DonationEditRecord',
	key: 'DonationEditRecord',
	recordArray: Tine.Donator.Model.DonationArray,
	recordCollection: null,
	recordClass: Tine.Donator.Model.Donation,
    recordProxy: Tine.Donator.donationBackend,
    parentRecordClass: Tine.Donator.Model.FundMaster,
    disabledClass: 'x-item-dissi',
    parentRelation: {
		fkey: 'fundmaster_id',
		references: 'id',
		type: Tine.widgets.dialog.parentRelationTypes.ONE_TO_MANY
	},
    gridPanelClass: Tine.Donator.DonationGridPanelNested,
	formFieldPrefix: 'donation_',
	useButtons:true,
	// TODO: dirty, id bound to form definition
	formPanelToolbarId: 'donator-donation-edit-dialog-panel-toolbar',
	initComponent: function(){
		this.app = Tine.Tinebase.appMgr.get('Donator');
		this.recordArray = Tine.Donator.Model.Donation.getFieldDefinitions();
		this.gridPanelClass = Tine.Donator.DonationGridPanelNested;
		//Ext.apply(this);
		Tine.Donator.DonationEditRecord.superclass.initComponent.call(this);
		this.on('addrecord', this.onAddRecord, this);
	},
	onAddRecord: function(record){
		record.data.donation_date = new Date();
		Tine.Donator.DonationEditRecord.superclass.onAddRecord.call(this, record);
		this.editHandler.setDefaultDate();
		
		return true;
	},
	onAfterRender: function(){
		Tine.Donator.DonationEditRecord.superclass.onAfterRender.call(this);
		this.editHandler = new Tine.Donator.DonationEditHandler();
		this.editHandler.initialize(this);
		this.editHandler.focusFirst();
	},
	exchangeEvents: function(observable){
		this.checkObservableBreak(observable);
		switch(observable.className){
		case 'Tine.Donator.FundMasterEditRecord':
			observable.on('applychanges',this.handlerApplyChanges,this);
			observable.on('loadform', this.onLoadParent, this);
			observable.on('initeditmode', this.onDependentEditing, this);
			observable.on('initviewmode', this.onDependentEndEditing, this);
			observable.on('cancel',this.handlerCancel, this);
			observable.exchangeEvents(this);
			return true;
		}
		return false;
	},
	getFormContents: function(){
		return Tine.Donator.getDonationEditDialogPanelNested(this.getComponents());
	}
});

Tine.Donator.getDonationEditRecordAsTab = function(){
	return new Tine.Donator.DonationEditRecord (
		{
			title: 'Spenden',
			withFilterToolbar: true,
			useGrid:true
		}
	);
};

Tine.Donator.getDonationEditRecordPanel = function(){
	return new Tine.Donator.DonationEditRecord({
		title: null,
		header: false,
		bodyStyle:'padding:0'
	});
};

Tine.Donator.getDonationEditDialogPanelNested = function(components){
	return [{
		xtype:'panel',
		layout:'fit',
		id: 'donator-donation-main-content-panel',
		//frame:true,
		border:false,
		items: [{
    	   xtype:'panel',
    	   header:false,
    	   border:false,
    	   //frame:true,
    	   layout:'border',
    	   items:[{
	    	   xtype:'panel',
	    	   region:'center',
	    	   header:false,
	    	   border:false,
	    	   frame:true,
	    	   layout:'fit',
	    	   items:[Tine.Donator.getDonationEditPanelEmbedded()]
	       },{
	    	   xtype:'panel',
	    	   region:'north',
	    	   height:220,
	    	   collapsible:true,
	    	   collapseMode:'mini',
	    	   split:true,
	    	   layout:'fit',
	    	   items:[ components.grid.grid ]
	       }]
		}]
	}];
};

Tine.Donator.getDonationEditPanelEmbedded = function(){
	return {
		xtype: 'panel',
		id: 'Donator-donation-edit-dialog-panel',
		border: false,
		frame: true,
		cls: 'tw-editdialog',
		layout:'border',
		defferedRender:true,
		items:[
			/*{
				xtype: 'panel',
				id: 'donator-donation-edit-dialog-panel-toolbar',
				height: 26,
				layout:'fit',
				region:'north',
				tbar: new Ext.Toolbar({id:'donator-donation-edit-dialog-panel-toolbar-tb'})
			},*/{
				xtype:'panel',
				region:'center',
				layout:'fit',
				autoScroll: true,
				border:false,
				tbar: new Ext.Toolbar({id:'donator-donation-edit-dialog-panel-toolbar-tb',height:26}),
				/*width: 400,
				defaults: {
			        xtype: 'fieldset',
			        // -> never: IE killer
			        //autoHeight: 'auto',
			        layout:'fit',
			        defaultType: 'textfield'
			    },*/
			    items: Tine.Donator.getDonationFormItems()
		}]};
}

Tine.Donator.getDonationEditPanel = function(){
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
		    items: Tine.Donator.getDonationFormItems()
		}]};
}

Tine.Donator.getDonationFormItems = function(){
	var nonMonetaryForm = 
	{
		xtype:'columnform',border:false,items:
		[[
		  	{
				xtype: 'checkbox',
				disabledClass: 'x-item-disabled-view',
				id: 'donation_non_monetary_rating',
				name: 'non_monetary_rating',
				hideLabel:true,
				labelPos:'top',
			    boxLabel: 'Unterlagen Wertermittlung liegen vor',
			    width: 250,
			    disabled:true
			},{
			    fieldLabel: 'Quelle Sachzuwendung',
			    disabledClass: 'x-item-disabled-view',
			    id:'donation_non_monetary_source',
			    name:'non_monetary_source',
			    width: 225,
			    xtype:'combo',
			    store:[['COMMERCIAL','Geschäftsvermögen'],['PRIVATE','Privatvermögen'],['NOSTATEMENT','Keine Angabe']],
			    value: 'NOSTATEMENT',
			    mode: 'local',
			    displayField: 'name',
			    valueField: 'id',
			    triggerAction: 'all',
			    disabled:true
			}
		],[  		
			{
				xtype: 'textarea',
				fieldLabel: 'Beschreibung Sachzuwendung (mit Alter, Zustand, Kaufpreis etc.)',
				disabledClass: 'x-item-disabled-view',
				id:'donation_non_monetary_description',
				name:'non_monetary_description',
				width: 450,
				height: 30
			} 
		]]
	};
	
	return [
	   {xtype:'columnform',border:false,items:[[
	   {xtype: 'hidden',id:'donation_id',name:'id'},	
	   {xtype:'hidden', name:'booking_id'},
	   {xtype: 'hidden',id:'related_donation_id',name:'id'},
	   
	   {
			xtype: 'checkbox',
			disabledClass: 'x-item-disabled-view',
			id: 'donation_is_cancelled',
			name: 'is_cancelled',
			hideLabel:true,
			disabled:false,
		    boxLabel: 'storniert',
		    width: 200
		},{
			xtype: 'checkbox',
			disabledClass: 'x-item-disabled-view',
			id: 'donation_is_cancellation',
			name: 'is_cancellation',
			hideLabel:true,
			disabled:false,
		    boxLabel: 'ist Storno',
		    width: 200
		}
	   ],[
	      new Tine.Tinebase.widgets.form.RecordPickerComboBox({
	        fieldLabel: 'Spender',
	        disabledClass: 'x-item-disabled-view',
	        id:'donation_fundmaster_id',
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
	        width: 450,
	        allowBlank:false
	    }),
	  ],[  
	    {
		   fieldLabel: 'Spendennummer',
			disabledClass: 'x-item-disabled-view',
			id:'donation_donation_nr',
			name:'donation_nr',
			disabled:true,
			onAddEditable:false,
			onEditEditable:false,
		    value:null,
		    //allowBlank:false,
		    width: 150
	    },
	    Tine.Donator.Custom.getRecordPicker('Campaign','donation_campaign_id',{
	    	fieldLabel: 'Kampagne/Verwendungszweck',
	    	disabledClass: 'x-item-disabled-view',
	    	name: 'campaign_id',
		    appendFilters: [{field: 'is_closed', operator: 'equals', value: false }],
		    disabled: false,
		    width:300,
		    onAddEditable: true,
		    onEditEditable: false,
		    blurOnSelect: true,
		    allowBlank:false
		})
	 ],[
		{
			fieldLabel: 'Projekt',
			disabledClass: 'x-item-disabled-view',
			id:'donation_campaign_project',
			name:'campaign_project',
			disabled:true,
			infoField:true,
		    width: 250
		},{
		    fieldLabel: 'Art',
		    disabledClass: 'x-item-disabled-view',
		    id:'donation_donation_type',
		    name:'donation_type',
		    width: 200,
		    xtype:'combo',
		    store:[['CYCLE','Dauerspende'],['SINGLE','Einzelspende']],
		    value: 'SINGLE',
		    disabled:true,
			mode: 'local',
			displayField: 'name',
		    valueField: 'id',
		    triggerAction: 'all'
		}
	 ],[  
		{
			xtype: 'checkbox',
			disabledClass: 'x-item-disabled-view',
			id: 'donation_non_monetary',
			name: 'non_monetary',
			hideLabel:true,
		    boxLabel: 'ist Sachzuwendung',
		    width: 200
		},{
			xtype: 'checkbox',
			disabledClass: 'x-item-disabled-view',
			id: 'donation_refund_quitclaim',
			name: 'refund_quitclaim',
			hideLabel:true,
		    boxLabel: 'Verzicht auf Erstattung von Aufwendungen',
		    width: 250
		}
	 ],[  
	    {
	    	xtype: 'panel',
	    	id:'nonMonetaryAddition',
	    	layout:'fit',
	    	header:false,
	    	width:450,
	    	collapsible:true,
	    	collapsed:true,
	    	items:[
	    	       nonMonetaryForm
	    	]
	    }
	],[  
		    		
	{
		   	xtype: 'datefield',
		   	disabledClass: 'x-item-disabled-view',
		   	fieldLabel: 'Datum Spende', 
		   	id:'donation_donation_date',
		   	name:'donation_date',
		   	width: 150,
	        tabIndex:2
	},{
			xtype:'extuxclearabledatefield',
			disabledClass: 'x-item-disabled-view',
			fieldLabel: 'Datum Bestätigung',
			id:'donation_confirmation_date',
			name:'confirmation_date',
			disabled: true,
			width: 150
	},{
			xtype:'extuxclearabledatefield',
			disabledClass: 'x-item-disabled-view',
			fieldLabel: 'Datum Bedankung',
			id:'donation_thanks_date',
			name:'thanks_date',
			disabled:true,
		    width: 150
		}
	],[
		{
		    fieldLabel: 'Art Bedankung',
		    disabledClass: 'x-item-disabled-view',
		    id:'donation_gratuation_kind',
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
		},{
			fieldLabel: 'Art Bestätigung',
			disabledClass: 'x-item-disabled-view',
			id:'donation_confirmation_kind',
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
		}
	],[
		new Tine.Tinebase.widgets.form.RecordPickerComboBox({
	    	fieldLabel: 'Bankkonto Eingang',
	    	disabledClass: 'x-item-disabled-view',
	    	id:'donation_donation_account_id',
	    	name: 'donation_account_id',
	        blurOnSelect: true,
	        recordClass: Tine.Donator.Model.DonationAccount,
	        width: 180,
	        //disabled:true,
	        allowBlank:false,
	        tabIndex:6
	    }),{
			xtype:'monetarynumfield',
			disabledClass: 'x-item-disabled-view',
			fieldLabel: 'Spendenbetrag',
			id:'donation_donation_amount',
			name:'donation_amount',
			width: 140,
	        tabIndex:5
		},{
	    	fieldLabel: 'Bestätigungs-Nr', 
		    id:'donation_confirm_nr',
		    name:'confirm_nr',
	    	disabledClass: 'x-item-disabled-view',
	    	blurOnSelect: true,
	    	disabled:true,
	 	    width:130
	 	}
	],[		
		Tine.Billing.Custom.getRecordPicker('AccountSystem', 'donation_erp_proceed_account_id',
	    {
			fieldLabel: 'Erlöskonto',
	        name:'erp_proceed_account_id',
	        width: 450
	    })
	],[
		{
			xtype: 'textarea',
			fieldLabel: 'Bemerkung',
			disabledClass: 'x-item-disabled-view',
			id:'donation_donation_usage',
			name:'donation_usage',
			width: 450,
			height: 80
		} 
	]]}/*]}*/  
	];
}