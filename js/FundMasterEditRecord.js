Ext.ns('Tine.Donator');

Tine.Donator.FundMasterEditDialog = Ext.extend(Tine.widgets.dialog.EditDialog, {
	
	/**
	 * @private
	 */
	windowNamePrefix: 'FundMastertEditWindow_',
	appName: 'Donator',
	recordClass: Tine.Donator.Model.FundMaster,
	recordProxy: Tine.Donator.fundMasterBackend,
	loadRecord: false,
	evalGrants: false,
	initComponent: function(){
		this.initWidgets();
		this.initDependentGrids();
		this.on('load',this.onLoadFundMaster, this);
		this.on('afterrender',this.onAfterRender,this);
        this.action_addDonation = new Ext.Action({
            actionType: 'edit',
            handler: this.onAddDonation,
            iconCls: 'action_edit',
            scope: this
        });
        this.action_editContact = new Ext.Action({
            actionType: 'edit',
            handler: this.onEditContact,
            iconCls: 'action_edit',
            scope: this
        });
        this.action_addContact = new Ext.Action({
            actionType: 'edit',
            handler: this.onAddContact,
            iconCls: 'actionAdd',
            scope: this
        });
        this.action_addFundMaster = new Ext.Action({
            actionType: 'edit',
            handler: this.onAddFundMaster,
            text: 'Kontakt vorhanden',
            iconCls: 'actionAdd',
            scope: this
        });
        this.action_addFMWithContact = new Ext.Action({
            actionType: 'edit',
            handler: this.onAddFMWithContact,
            iconCls: 'actionAdd',
            text: 'Kontakt vorher anlegen',
            scope: this
        });
        this.actions_newFundMaster = new Ext.Action({
         	allowMultiple: false,
         	text: 'Neuen Spender anlegen',
            menu:{
             	items:[
             	       this.action_addFundMaster,
 					   this.action_addFMWithContact
             	]
             }
         });
        //this.items = this.getFormItems();
		Tine.Donator.FundMasterEditDialog.superclass.initComponent.call(this);
	},
    initButtons: function(){
    	Tine.Donator.FundMasterEditDialog.superclass.initButtons.call(this);
    	this.tbar = [
    	   '->',
    	   Ext.apply(new Ext.Button(this.actions_newFundMaster), {
				 scale: 'small',
	             rowspan: 2,
	             iconAlign: 'left'
	        }),
    	   /*Ext.apply(new Ext.Button(this.action_addContact), {
				 text: 'Neuen Kontakt anlegen',
	             scale: 'small',
	             rowspan: 2,
	             iconAlign: 'left'
	        }),*/
    	   Ext.apply(new Ext.Button(this.action_editContact), {
				 text: 'gehe zu Kontaktdatensatz',
	             scale: 'small',
	             rowspan: 2,
	             iconAlign: 'left'
	        })
	 	];
        this.fbar = [
             '->',
             this.action_applyChanges,
             this.action_cancel,
             this.action_saveAndClose
        ];
    },
	onLoadFundMaster: function(){
		if(this.fundMasterWidget){
			this.fundMasterWidget.onLoadFundMaster(this.record);
		}
		if(this.record.id !== 0){
			this.donationGrid.enable();
		}
		// set window name, to avoid multiply opening (twice) the same record
		this.window.name = Tine.Donator.FundMasterEditDialog.prototype.windowNamePrefix + this.record.id;
		this.donationGrid.loadFundMaster(this.record);
		this.regularDonationGrid.loadFundMaster(this.record);
	},
	onAddContact: function(){
		this.contactWin = Tine.Addressbook.ContactEditDialog.openWindow({});
		this.contactWin.on('beforeclose',this.onReloadSelectionGrid,this);
	},
	onReloadSelectionGrid: function(){
		this.getContactSelectionGrid().grid.getStore().reload();
	},
	onEditContact: function(){
	   	this.contactWin = Tine.Addressbook.ContactEditDialog.openWindow({
			record: new Tine.Addressbook.Model.Contact(this.record.data.contact_id,this.record.data.contact_id.id)
		});
		//this.contactWin.on('beforeclose',this.onReloadFundMaster,this);
	},
	onAddFundMaster: function(){
		this.record = null;
		this.initRecord();
		this.getForm().reset();
		this.donationGrid.getStore().removeAll();
		this.donationGrid.disable();
		this.regularDonationGrid.getStore().removeAll();
		this.regularDonationGrid.disable();
	},
	onAddFMWithContact: function(){
		this.onAddFundMaster();
		this.contactWin = Tine.Addressbook.ContactEditDialog.openWindow({
			listeners:{
				update:{
					scope:this,
					fn: this.onUpdateContact
				}
			}
		});
		this.contactWin.on('close',this.onReloadSelectionGrid,this);
	},
	onUpdateContact: function(contact){
		var contactData = Ext.util.JSON.decode(contact);
		var contactRecord = new Tine.Addressbook.Model.Contact(contactData, contactData.id);
		this.record.data.contact_id = contactRecord;
		Ext.getCmp('fundmaster_contact_id').setValue(contactRecord);
	},
	
	initWidgets: function(){
		this.getFundMasterWidget();
	},
	/**
	 *  initialize dependent gridpanels
	 */
	initDependentGrids: function(){
		this.donationGrid = new Tine.Donator.FundMasterDonationGridPanel({
			title:'Spenden',
			layout:'fit',
			disabled:true,
			frame: true,
			app: Tine.Tinebase.appMgr.get('Donator')
		});
		this.regularDonationGrid = new Tine.Donator.RegularDonationGridPanel({
			title:'Spendenaufträge',
			layout:'border',
			perspective: 'FUNDMASTER',
			useImplicitForeignRecordFilter: true,
			disabled:false,
			frame: true,
			app: Tine.Tinebase.appMgr.get('Donator')
		});
	},
	getFormItems: function(){
		//return Tine.Donator.getFundMasterEditPanel();
		return new Ext.Panel({
			//xtype:'panel',
			region:'center',
			layout:'border',
			items:[
				{
				    xtype: 'panel',
				    region:'center',
				    border: false,
				    //autoScroll:true,
				   // height:500,
				    frame:true,
				    //layout:'fit',
				    items:
				    	    Tine.Donator.getFundMasterEditPanel() 
				    	
				    	
				},
				{
					xtype:'panel',
					region:'south',
					header:false,
					height: 300,
					collapsible:true,
					collapseMode:'mini',
					layout:'fit',
					split:true,
					items:[
					       {
					    	   xtype:'tabpanel',
					    	   activeItem:0,
					    	   layoutOnTabChange:true,
					    	   items:[
					    	        this.donationGrid,
					    	        this.regularDonationGrid
					    	   ]
					       }
					       
					]
				}
				
		]});
	},
	 onAfterRender: function(){
	    	this.initDropZone();
	    },
	    
	    initDropZone: function(){
	    	if(!this.ddConfig){
	    		return;
	    	}
			this.dd = new Ext.dd.DropTarget(this.el, {
				scope: this,
				ddGroup     : this.ddConfig.ddGroupContact,
				notifyEnter : function(ddSource, e, data) {
					this.scope.el.stopFx();
					this.scope.el.highlight();
				},
				notifyDrop  : function(ddSource, e, data){
					return this.scope.onDrop(ddSource, e, data);
				}
			});
			this.dd.addToGroup(this.ddConfig.ddGroupGetContact);
		},
		
		extractRecordFromDrop: function(ddSource, e, data){
			var source = data.selections[0];
			var record = null;
			switch(ddSource.ddGroup){
			case 'ddGroupFundMaster':
				var source = data.selections[0];
				record = source;
				break;
				
			case 'ddGroupGetFundMaster':
				if(source.getFundMaster !== undefined && typeof(source.getFundMaster)==='function'){
					record = source.getFundMaster();
				}
				break;
			}
			return record;
		},
		
		onDrop: function(ddSource, e, data){
			var record = this.extractRecordFromDrop(ddSource, e, data);
			if(!record){
				return false;
			}
			this.record = record;
			this.initRecord();
			return true;
		},
		getFundMasterWidget: function(){
			if(!this.fundMasterWidget){
				this.fundMasterWidget = new Tine.Donator.FundMasterWidget({
						region: 'north',
						layout:'fit',
						height:40,
						editDialog: this
				});
			}
			return this.fundMasterWidget;
		},
		getContactSelectionGrid: function(){
			return Ext.getCmp('fundmasterContactSelectionGrid');
		}
});

//extended content panel constructor
Tine.Donator.FundMasterEditDialogPanel = Ext.extend(Ext.Panel, {
	panelManager:null,
	windowNamePrefix: 'FundMasterEditWindow_',
	appName: 'Donator',
	layout:'fit',
	bodyStyle:'padding:0px;padding-top:5px',
	forceLayout:true,
	initComponent: function(){
		this.initSelectionGrids();
		
		Ext.apply(this.initialConfig,{region:'center'});
		
		var regularDialog = new Tine.Donator.FundMasterEditDialog(this.initialConfig);
		//regularDialog.setTitle('Artikel Stammdaten');
		regularDialog.doLayout();
		this.items = this.getItems(regularDialog);
		Tine.Donator.FundMasterEditDialogPanel.superclass.initComponent.call(this);
	},
	initSelectionGrids: function(){
		this.fundMasterSelectionGrid = new Tine.Donator.FundMasterSelectionGrid({
			title:'Spender',
			layout:'border',
			app: Tine.Tinebase.appMgr.get('Donator')
		});
	},
	getItems: function(regularDialog){
		var recordChoosers = [
			this.fundMasterSelectionGrid,
			{
				xtype:'contactselectiongrid',
				id: 'fundmasterContactSelectionGrid',
				title:'Kontakte',
				layout:'border',
				app: Tine.Tinebase.appMgr.get('Addressbook')
			}                    
		];
		
		// use some fields from brevetation edit dialog
		 var recordChooserPanel = {
				 xtype:'panel',
				 layout:'accordion',
				 region:'east',
				 title: 'Auswahlübersicht',
				 width:600,
				 collapsible:true,
				 bodyStyle:'padding:8px;',
				 split:true,
				 items: recordChoosers
		 };
		return [{
			xtype:'panel',
			layout:'border',
			items:[
			       // display creditor widget north
			       regularDialog.getFundMasterWidget(),
			       // tab panel containing creditor master data
			       // + dependent panels
			       regularDialog,
			       // place record chooser east
			       recordChooserPanel
			]
		}];
	}
});

Tine.Donator.FundMasterEditDialog.openWindow = function (config) {
    var id = (config.record && config.record.id) ? config.record.id : 0;
    var window = Tine.WindowFactory.getWindow({
        width: 1200,
        height: 850,
        name: Tine.Donator.FundMasterEditDialog.prototype.windowNamePrefix + id,
        contentPanelConstructor: 'Tine.Donator.FundMasterEditDialogPanel',
        contentPanelConstructorConfig: config
    });
    return window;
};

Tine.Donator.FundMasterEditRecord = Ext.extend(Tine.widgets.dialog.DependentEditForm, {
	id: 'sopen-fundmaster-edit-record-form',
	className: 'Tine.Donator.FundMasterEditRecord',
	key: 'FundMasterEditRecord',
	recordArray: Tine.Donator.Model.FundMasterArray,
	recordClass: Tine.Donator.Model.FundMaster,
    recordProxy: Tine.Donator.fundMasterBackend,
    
    parentRecordClass: Tine.Addressbook.Model.Contact,
    parentRelation: {
		fkey: 'contact_id',
		references: 'id',
		type: Tine.widgets.dialog.parentRelationTypes.ONE_TO_MANY
	},
    useGrid: true,
    useChildPanels:true,
    splitViewToggle: true,
    gridPanelClass: Tine.Donator.FundMasterGridPanelNested,
	formFieldPrefix: 'fundmaster_',
	formPanelToolbarId: 'donator-fundmaster-edit-dialog-panel-toolbar',
	initComponent: function(){
		this.app = Tine.Tinebase.appMgr.get('Donator');
		this.gridPanelClass = Tine.Donator.FundMasterGridPanelNested;
		this.recordProxy = Tine.Donator.fundMasterBackend;
		this.parentRecordClass = Tine.Addressbook.Model.Contact;
		this.parentRelation = {
			fkey: 'contact_id',
			references: 'id',
			type: Tine.widgets.dialog.parentRelationTypes.ONE_TO_MANY
		};
		Tine.Donator.FundMasterEditRecord.superclass.initComponent.call(this);
		// register parent action events
		// this record events are handled by parent class
    	this.registerGridEvent('addparentrecord',this.onAddDonator, this);
    	this.registerGridEvent('editparentrecord',this.onEditDonator, this);
    	//this.on('beforeaddrecord', this.onBeforeAddRecord, this);
    	//this.on('addrecord', this.onAddRecord, this);
	},
	initChildPanels: function(){
		var donationPanel = Tine.Donator.getDonationEditRecordAsTab();
		donationPanel.disable();
		this.registerChildPanel('DonationEditRecord', donationPanel);
		//Tine.Donator.FundMasterEditRecord.superclass.initChildPanels.call(this);
	},
	exchangeEvents: function(observable){
		this.checkObservableBreak(observable);
		switch(observable.className){
		case 'Tine.Donator.DonationEditRecord':
			observable.disable();
			observable.on('aftersavesuccess',this.onAfterSaveDonation, this);
			
			// don't call observable.exchangeEvents again here in parent
			// -> recursion
			return true;
		}
		return false;
	},
	onAfterSaveDonation: function(){
		try{
			this.getGrid().reload();
		}catch(e){
			// IE craziness
		}
	},
	onAddDonator: function(){
	    var record = new Tine.Addressbook.Model.Contact(Tine.Addressbook.Model.Contact.getDefaultData(), 0);
	    var popupWindow = Tine.Addressbook.ContactEditDialog.openWindow({
	        record: record,
	        listeners: {
	            scope: this,
	            'update': function(record) {
	                this.load(true, true, true);
	            }
	        }
	    });
	},
	onEditDonator: function(record){
        record = new Tine.Addressbook.Model.Contact(record.data.contact_id,record.data.contact_id.id);
        var popupWindow = Tine.Addressbook.ContactEditDialog.openWindow({
	        record: record,
	        listeners: {
	            scope: this,
	            'update': function(record) {
	                this.load(true, true, true);
	            }
	        }
	    });
	},	

	getFormContents: function(){
		return Tine.Donator.getFundMasterEditDialogPanel(this.getComponents());
	}
});

Tine.Donator.getFundMasterEditRecordAsTab = function(){
	return new Tine.Donator.FundMasterEditRecord(
		{
			title: 'Spenderdaten',
			withFilterToolbar: false,
			useGrid:true,
			disabled: true,
			closable:true,
			getRecordChooserItems: function(){
				return [ {
		        	xtype: 'fundmasterselectiongrid',
		        	title:'Spender',
		        	layout:'border',
		        	app: Tine.Tinebase.appMgr.get('Donator')
		        }];
			}
		}
	);
};

Tine.Donator.getFundMasterEditRecordPanel = function(){
	return new Tine.Donator.FundMasterEditRecord(
		{
			title: ' ',
			header: true,
			bodyStyle:'padding:0',
			withFilterToolbar:true,
			useGrid:true
		}
	);
};

Tine.Donator.getFundMasterEditDialogPanel = function(components){
	var editPanel = Tine.Donator.getFundMasterEditPanelEmbedded();
	var tabPanelItems = [
	    editPanel
	];

	if(components.childPanels.DonationEditRecord){
		tabPanelItems.push(components.childPanels.DonationEditRecord);
	}
	
	var editDialogPanel = {
		xtype:'panel',
		layout:'fit',
		id: 'fundmaster-edit-dialog-panel',
		items: [
		{
		    xtype:'panel',
			layout:'fit',
			cls: 'tw-editdialog',
			border:false,
			items:[{
			    xtype: 'tabpanel',
			    id: 'fundmaster-edit-dialog-childpanel-container',
			    border: false,
			    plain:true,
			    layoutOnTabChange: true,
			    border:false,
			    activeTab: 0,
			    items: tabPanelItems
			}]
		}]}; 
	
	
	var contentPanelItems = [{
 	   xtype:'panel',
	   region:'center',
	   header:false,
	   border:false,
	   frame:true,
	   layout:'fit',
	   items:[editDialogPanel]
   }];
	
	if(components.grid.useGrid){
		var gridWrapperItem = {
    	   xtype:'panel',
    	   region:'north',
    	   height:180,
    	   header:false,
    	   border:false,
    	   split:true,
    	   collapsible:true,
    	   collapseMode:'mini',
    	   collapsed:true,
    	   layout:'fit',
    	   items:[components.grid.grid]
		};
		contentPanelItems.push(gridWrapperItem);
	}
	
	return [{
		xtype:'panel',
		layout:'fit',
		id: 'fundmaster-main-content-panel',
		items: [{
	  	   xtype:'panel',
	  	   header: false,
	  	   border:false,
	 	   layout:'border',
	 	   items: contentPanelItems
	    }]
	}];
};

Tine.Donator.getFundMasterEditPanel = function(){
	return {
		xtype: 'panel',
		id: 'Donator-edit-dialog-panel',
		title: 'Spender Stammdaten',
		border: false,
		frame: true,
		cls: 'tw-editdialog',
		layout:'fit',
		autoScroll: true,
		//defferedRender:true,
		/*defaults: {
		    xtype: 'fieldset',
		    // -> never do this: kills IE
		    //autoHeight: 'auto',
		    layout:'box',
		    disabledClass: 'x-item-disabled-view',
		    defaultType: 'textfield'
		},*/
		items:[
		       Tine.Donator.getFundMasterFormItems()     
		]};
}

Tine.Donator.getFundMasterEditPanelEmbedded = function(){
	return {
		xtype: 'panel',
		id: 'donator-edit-dialog-panel',
		title: 'Spender Stammdaten',
		border: false,
		//frame: true,
		layout:'border',
		items:[ 
			/*{
				xtype: 'panel',
				id: 'donator-fundmaster-edit-dialog-panel-toolbar',
				height: 26,
				layout:'fit',
				region:'north',
				tbar: new Ext.Toolbar({id:'donator-fundmaster-edit-dialog-panel-toolbar-tb'})
			},*/{
				xtype: 'panel',
				border:false,
				frame:true,
				region:'center',
				layout:'fit',
				autoScroll: true,
				tbar: new Ext.Toolbar({id:'donator-fundmaster-edit-dialog-panel-toolbar-tb',height:26}),
				defaults: {
					xtype: 'fieldset',
					// -> never: kills IE
					//autoHeight: 'auto',
					layout:'fit',
					disabledClass: 'x-item-disabled-view',
					defaultType: 'textfield'
				},
				items: Tine.Donator.getFundMasterFormItems()
			}
		]
	};
}

Tine.Donator.getFundMasterFormItems = function(){
	return [
		{title:'',layout:'fit',checkboxToggle:false,border:false,items:[{xtype:'columnform',items:[[
		{xtype: 'hidden',id:'fundmaster_id',name:'id'},
		],[
		new Tine.Tinebase.widgets.form.RecordPickerComboBox({
			disabledClass: 'x-item-disabled-view',
			width: 250,
			fieldLabel: 'Kontakt Spender',
		    id:'fundmaster_contact_id',
		    name:'contact_id',
		    disabled: false,
		    onAddEditable: false,
		    onEditEditable: false,
		    blurOnSelect: true,
		    recordClass: Tine.Addressbook.Model.Contact,
		    width: 200,
		    allowBlank:false,
		    ddConfig:{
	        	ddGroup: 'ddGroupContact'
	        }
		})
		],[
		{
		    fieldLabel: 'Affinität saisonal',
		    disabledClass: 'x-item-disabled-view',
		    context: 'donation_affinity_seasonal',
			xtype: 'sogenericstatefield',
			width:200,
			id:'fundmaster_donation_affinity_seasonal',
		    name:'donation_affinity_seasonal',
			allowBlank:false
		},{
		    fieldLabel: 'Affinität thematisch',
		    disabledClass: 'x-item-disabled-view',
		    context: 'donation_affinity_thematic',
			xtype: 'sogenericstatefield',
			width:200,
			id:'fundmaster_donation_affinity_thematic',
		    name:'donation_affinity_thematic',
			allowBlank:false
		},{
		    fieldLabel: 'Affinität regional',
		    disabledClass: 'x-item-disabled-view',
		    context: 'donation_affinity_regional',
			xtype: 'sogenericstatefield',
			width:200,
			id:'fundmaster_donation_affinity_regional',
		    name:'donation_affinity_regional',
			allowBlank:false
		}
		],[
		{
		    fieldLabel: 'Affinität spez. Ereign.',
		    disabledClass: 'x-item-disabled-view',
		    context: 'donation_affinity_spec_events',
			xtype: 'sogenericstatefield',
			width:200,
			id:'fundmaster_donation_affinity_spec_events',
		    name:'donation_affinity_spec_events',
			allowBlank:false
		},{
		    fieldLabel: 'Spenderwerbung',
		    disabledClass: 'x-item-disabled-view',
		    context: 'donator_affiliate',
			xtype: 'sogenericstatefield',
			width:200,
			id:'fundmaster_donator_affiliate',
		    name:'donator_affiliate',
			allowBlank:false
		},{
			xtype: 'datefield',
			disabledClass: 'x-item-disabled-view',
		    width: 200,
		    fieldLabel: 'Datum Erstkontakt', 
		    id:'fundmaster_first_contact',
		    name:'first_contact'
		}
		],[
		new Tine.Tinebase.widgets.form.RecordPickerComboBox({
			id:'fundmaster_first_contact_campaign_id',
			name: 'first_contact_campaign_id',
		    fieldLabel: 'Kampagne Erstkontakt',
		    disabledClass: 'x-item-disabled-view',
		    width: 300,
		    blurOnSelect: true,
		    recordClass: Tine.Donator.Model.Campaign
		}),
		{
		    xtype: 'monetarynumfield',
		    disabledClass: 'x-item-disabled-view',
			fieldLabel: 'Betrag regelm. Spende',
			width: 200,
			id: 'fundmaster_reg_donation_amount',
			name: 'reg_donation_amount',
			value:0
		},{
			fieldLabel: 'Konto regelm. Spende',
		  	disabledClass: 'x-item-disabled-view',
			width: 200,
			id: 'fundmaster_reg_donation_account_nr',
			name: 'reg_donation_account_nr'
		}
		],[
		{
		    fieldLabel: 'Art Bedankung',
		    disabledClass: 'x-item-disabled-view',
		    id:'fundmaster_gratuation_kind',
		    name:'gratuation_kind',
		    width: 200,
		    xtype:'combo',
		    store:[['THANK_NO','Keine'],['THANK_STANDARD','Standard'],['THANK_INDIVIDUAL','Individuell']],
		    value: 'THANK_NO',
			mode: 'local',
			displayField: 'name',
		    valueField: 'id',
		    triggerAction: 'all'
		},			{
		    fieldLabel: 'Art Bestätigung',
		    disabledClass: 'x-item-disabled-view',
		    id:'fundmaster_confirmation_kind',
		    name:'confirmation_kind',
		    width: 200,
		    xtype:'combo',
		    store:[['CONFIRMATION_COLLECT','Sammelquittung'],['CONFIRMATION_SINGLE','Einzelquittung'],['CONFIRMATION_NO','Keine Quittung']],
		    value: 'CONFIRMATION_SINGLE',
			mode: 'local',
			displayField: 'name',
		    valueField: 'id',
		    triggerAction: 'all'
			}
		],[
			{
				width:200,
			    fieldLabel: 'Zahlungsweise', 
			    disabledClass: 'x-item-disabled-view',
			    id:'fundmaster_donation_payment_interval',
			    xtype:'combo',
			    store:[['NOVALUE','...keine Auswahl...'],['YEAR','jährlich'],['HALF','halbjährlich'],['QUARTER','quartalsweise'],['MONTH','monatlich']],
			    value: 'NOVALUE',
			    name:'donation_payment_interval',
			    mode: 'local',
				displayField: 'name',
			    valueField: 'id',
			    triggerAction: 'all'
			},{
			    fieldLabel: 'Zahlungsart',
			    disabledClass: 'x-item-disabled-view',
			    id:'fundmaster_donation_payment_method',
			    name:'donation_payment_method',
			    width:200,
			    xtype:'combo',
			    store:[['NOVALUE','...keine Auswahl...'],['DEBIT','Lastschrift'],['BANKTRANSFER','Überweisung']],
				value: 'NOVALUE',
			    mode: 'local',
				displayField: 'name',
			    valueField: 'id',
			    triggerAction: 'all'
			 }
		   ],[
		  		{
		        	xtype:'textfield',
		        	disabledClass: 'x-item-disabled-view',
		        	width:200,
		            fieldLabel: 'BLZ', 
		            id:'fund_master_bank_code',
		            name:'bank_code'
		        },
		        {
		        	xtype:'textfield',
		        	disabledClass: 'x-item-disabled-view',
		        	width:200,
		            fieldLabel: 'Bank-Name', 
		            id:'fund_master_bank_name',
		            name:'bank_name'
		        }
		    ],[
		        {
		        	xtype:'textfield',
		        	disabledClass: 'x-item-disabled-view',
		        	width:200,
		            fieldLabel: 'Kontonummer', 
		            id:'fund_master_bank_account_nr',
		            name:'bank_account_nr'
		        },
		        {
		        	xtype:'textfield',
		        	disabledClass: 'x-item-disabled-view',
		        	width:200,
		            fieldLabel: 'Kontoinhaber', 
		            id:'fund_master_account_name',
		            name:'account_name'
		        }
		      
		  ]
		
		
		]}]}
	];
}

