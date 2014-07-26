Ext.namespace('Tine.Donator');

Tine.Donator.PrintDonationDialog = Ext.extend(Ext.form.FormPanel, {
	windowNamePrefix: 'PrintDonationWindow_',
	title: 'Spenden: Druckausgaben',

	appName: 'Donator',
	layout:'fit',

	outputType: null,
	serveType: 'DOWNLOAD',
	docType: 'ORIGINAL',
	mainGrid: null,
	isPreview: false,
	predefinedDonationFilter: [],
	predefinedFundMasterFilter: [],
	/**
	 * initialize component
	 */
	initComponent: function(){
		this.initActions();
		this.initToolbar();
		this.items = this.getFormItems();
		Tine.Donator.PrintDonationDialog.superclass.initComponent.call(this);
		this.on('afterrender', this.onAfterRender, this);
	},
	onAfterRender: function(){
		Ext.getCmp('donation_list').on('select', this.onSelectExtras, this);
		
	},
	onSelectExtras: function(){
		var value = Ext.getCmp('donation_list').getValue();
		
		//this.filterPanel.deleteAllFilters();
		
		switch(value){
		case 'SINGLE_CONFIRM':
			var filters = [
				{field:'donation_date', operator:'within',value:'yearThis'},
				{field:'confirmation_kind', operator:'equals',value:'CONFIRMATION_SINGLE'},
				{field:'confirmation_date', operator:'isnull',value:''},
				{field:'cancelled', operator:'equals',value:'0'},
				{field:'donation_amount', operator:'greater',value:'0'}
			];
			
			this.filterPanel.setValue(filters);
			break;
			
		case 'COLLECT_CONFIRM':
			var filters = [
				{field:'donation_date', operator:'within',value:'yearThis'},
				{field:'confirmation_kind', operator:'equals',value:'CONFIRMATION_COLLECT'},
				{field:'confirmation_date', operator:'isnull',value:''},
				{field:'cancelled', operator:'equals',value:'0'},
				{field:'donation_amount', operator:'greater',value:'0'}
			];
						
			this.filterPanel.setValue(filters);
			
			break;
			
		case 'STANDARD_THANK':
			var filters = [
				{field:'donation_date', operator:'within',value:'yearThis'},
				{field:'gratuation_kind', operator:'equals',value:'THANK_STANDARD'},
				{field:'thanks_date', operator:'isnull',value:''},
				{field:'cancelled', operator:'equals',value:'0'},
				{field:'donation_amount', operator:'greater',value:'0'}
			];
		
			this.filterPanel.setValue(filters);
			break;
			
		case 'PERSONALIZED_THANK':
			var filters = [
				{field:'donation_date', operator:'within',value:'yearThis'},
				{field:'gratuation_kind', operator:'equals',value:'THANK_INDIVIDUAL'},
				{field:'thanks_date', operator:'isnull',value:''},
				{field:'cancelled', operator:'equals',value:'0'},
				{field:'donation_amount', operator:'greater',value:'0'}
			];
									
			this.filterPanel.setValue(filters);
			
			break;
		}
		
		
	},
	initActions: function(){
        this.actions_print = new Ext.Action({
            text: 'Ok',
            disabled: false,
            iconCls: 'action_applyChanges',
            handler: this.printDonation,
            scale:'small',
            iconAlign:'left',
            scope: this
        });
        this.actions_cancel = new Ext.Action({
            text: 'Abbrechen',
            disabled: false,
            iconCls: 'action_cancel',
            handler: this.cancel,
            scale:'small',
            iconAlign:'left',
            scope: this
        });   
    },
    
	/**
	 * init bottom toolbar
	 */
	initToolbar: function(){
		this.bbar = new Ext.Toolbar({
			height:48,
        	items: [
        	        '->',
                    Ext.apply(new Ext.Button(this.actions_cancel), {
                        scale: 'medium',
                        rowspan: 2,
                        iconAlign: 'left',
                        arrowAlign:'right'
                    }),
                    Ext.apply(new Ext.Button(this.actions_print), {
                        scale: 'medium',
                        rowspan: 2,
                        iconAlign: 'left',
                        arrowAlign:'right'
                    })
                ]
        });
	},
	getPrintAction: function(){
		return Ext.getCmp('donation_print_action').getValue();
	},
	getDonationFilterData: function(){
		return Ext.util.JSON.encode(this.filterPanel.getValue());
	},
	getFundMasterFilterData: function(){
		return Ext.util.JSON.encode(this.fundMasterFilterPanel.getValue());
	},
	getServeType: function(){
		return this.serveType;
	},
	getExportType: function(){
		return Ext.getCmp('donation_export_type').getValue();
	},
	getDocKind: function(){
		return Ext.getCmp('donation_doc_kind').getValue();
	},
	/**
	 * save the order including positions
	 */
	printDonation: function(){
		var data = Ext.util.JSON.encode(
			{
				printAction: this.getPrintAction(),
				exportType: this.getExportType(),
				docKind: this.getDocKind(),
				donationFilters: this.getDonationFilterData(),
				fundMasterFilters: this.getFundMasterFilterData(),
				serveType: this.getServeType(),
				sort1: Ext.getCmp('donation_sort1').getValue(),
				sort2: Ext.getCmp('donation_sort2').getValue(),
				sortDirection: Ext.getCmp('donation_sort_dir').getValue(),
			}
		);
		
		var params = {
            method: 'Donator.expDonations',
            requestType: 'HTTP',
            data: data
        };
        
		var downloader = new Ext.ux.file.Download({
			timeout:3600000,
			params: params
        }).start();

	},
	/**
	 * Cancel and close window
	 */
	cancel: function(){
		this.purgeListeners();
        this.window.close();
	},
	setCurrentFilter: function(){
		if(this.mainGrid){
			this.filterBuffer = this.filterPanel.getValue();
			this.filterPanel.setValue(this.mainGrid.getGridFilterToolbar().getValue());
		}
	},
	unsetCurrentFilter: function(){
		this.filterPanel.setValue(this.filterBuffer);
	},
	/**
	 * returns dialog
	 * 
	 * NOTE: when this method gets called, all initalisation is done.
	 */
	getFormItems: function() {
		
		var store = new Tine.Tinebase.data.RecordStore(Ext.copyTo({readOnly: true}, {
			recordClass: Tine.widgets.persistentfilter.model.PersistentFilter,
			proxy: Tine.widgets.persistentfilter.model.persistentFilterProxy
		}, 'totalProperty,root,recordClass'));
		// use some fields from brevetation edit dialog
		
		var panel = {
	        xtype: 'panel',
	        region:'north',
	        anchor:'100%',
	        border: false,
	        frame:true,
	        height:160,
	        items:[{xtype:'columnform',items:[
	              [
					{
					    fieldLabel: 'Druckaktion',
					    disabledClass: 'x-item-disabled-view',
					    allowEdit:false,
					    id:'donation_print_action',
					    name:'print_action',
					    width: 140,
					    xtype:'combo',
					    store:[['CONFIRMATION_SINGLE','Einzelbestätigungen'],['CONFIRMATION_COLLECT','Sammelbestätigungen'],['GRATUATION','Dankschreiben'],['DONATION_LIST','Spendenliste']],
					    value: 'CONFIRMATION_SINGLE',
						mode: 'local',
						displayField: 'name',
					    valueField: 'id',
					    triggerAction: 'all'
					},{
					    fieldLabel: 'Exporttyp',
					    disabledClass: 'x-item-disabled-view',
					    allowEdit:false,
					    id:'donation_export_type',
					    name:'export_type',
					    width: 80,
					    xtype:'combo',
					    store:[['PDF','Pdf'],['CSV','Csv']],
					    disabled:true,
					    value: 'PDF',
						mode: 'local',
						displayField: 'name',
					    valueField: 'id',
					    triggerAction: 'all'
					},{
					    fieldLabel: 'Original/Vorschau',
					    disabledClass: 'x-item-disabled-view',
					    allowEdit:false,
					    id:'donation_doc_kind',
					    name:'doc_kind',
					    width: 180,
					    xtype:'combo',
					    store:[['ORIGINAL','Originaldokumente'],['PREVIEW','Vorschau']],
					    value: 'PREVIEW',
						mode: 'local',
						displayField: 'name',
					    valueField: 'id',
					    triggerAction: 'all'
					},{
					    fieldLabel: 'Extras Spendenliste',
					    disabledClass: 'x-item-disabled-view',
					    allowEdit:false,
					    id:'donation_list',
					    name:'list',
					    width: 220,
					    xtype:'combo',
					    store:[['NONE','---'],['SINGLE_CONFIRM','einzeln zu bestätigen'],['COLLECT_CONFIRM','gesammelt zu bestätigen'],['STANDARD_THANK','standardmässig zu bedanken'],['PERSONALIZED_THANK','individuell zu bedanken']],
					    value: 'NONE',
						mode: 'local',
						displayField: 'name',
					    valueField: 'id',
					    triggerAction: 'all'
					}
				],[
					{
					    fieldLabel: 'Sortierung1',
					    disabledClass: 'x-item-disabled-view',
					    allowEdit:false,
					    id:'donation_sort1',
					    name:'sort1',
					    width: 150,
					    xtype:'combo',
					    store:[
				           ['donation_nr','Spenden-Nr'],
				           ['donation_date','Spenden-Datum'],
				           ['donation_amount','Spenden-Betrag'],
				           ['contact_id', 'Spender-Adress-Nr'],
				           ['n_family', 'Spender-Nachname'],
				           ['adr_one_postalcode', 'Spender-PLZ'],
				           ['adr_one_locality', 'Spender-Ort']
					    ],
					    value: 'donation_date',
						mode: 'local',
						displayField: 'name',
					    valueField: 'id',
					    triggerAction: 'all'
					},{
					    fieldLabel: 'Sortierung2',
					    disabledClass: 'x-item-disabled-view',
					    allowEdit:false,
					    id:'donation_sort2',
					    name:'sort2',
					    width: 150,
					    xtype:'combo',
					    store:[
				           ['donation_nr','Spenden-Nr'],
				           ['donation_date','Spenden-Datum'],
				           ['donation_amount','Spenden-Betrag'],
				           ['contact_id', 'Spender-Adress-Nr'],
				           ['n_family', 'Spender-Nachname'],
				           ['adr_one_postalcode', 'Spender-PLZ'],
				           ['adr_one_locality', 'Spender-Ort']
					    ],
					    value: 'donation_date',
						mode: 'local',
						displayField: 'name',
					    valueField: 'id',
					    triggerAction: 'all'
					},{
					    fieldLabel: 'Richtung',
					    disabledClass: 'x-item-disabled-view',
					    allowEdit:false,
					    id:'donation_sort_dir',
					    name:'sort_dir',
					    width: 140,
					    xtype:'combo',
					    store:[['ASC','aufsteigend'],['DESC','absteigend']],
					    value: 'ASC',
						mode: 'local',
						displayField: 'name',
					    valueField: 'id',
					    triggerAction: 'all'
					}
				],[
				   {xtype: 'checkbox', checked: true, name:'use_current_filter',hideLabel: true, boxLabel:'aktuellen Filter der Hauptansicht verwenden',
					   listeners:{
						   check:{
							   scope: this,
							   fn: function(field){
								   switch(field.getValue()){
								   case true:
									   this.setCurrentFilter();
									   break;
								   case false:
									   this.unsetCurrentFilter();
									   break;
								   }
							   }
						   }
					   }
				   }
				],[
				 {xtype:'hidden',id:'filters', name:'filters', width:1}
				]
	        ]}]
	    };

		if(this.predefinedFilter == null){
			this.predefinedFilter = [];
		}
		this.filterPanel = new Tine.widgets.form.FilterFormField({
				id:'fp',
				filterModels: Tine.Donator.Model.Donation.getFilterModel(),
				defaultFilter: 'query',
				filters:this.predefinedDonationFilter
		});
		 
		this.filterPanel.on('afterrender', this.onAfterRender, this);
		
		this.fundMasterFilterPanel = new Tine.widgets.form.FilterFormField({
			id:'afp',
			filterModels: Tine.Donator.Model.FundMaster.getFilterModel(),
			defaultFilter: 'query',
			filters:this.predefinedFundMasterFilter
		});
		
		var wrapper = {
			xtype: 'panel',
			layout: 'border',
			frame: true,
			items: [
			   panel,
			   {
					xtype: 'panel',
					title: 'Selektion der Spender',
					height:200,
					id:'fundMasterFilterPanel',
					region:'center',
					autoScroll:true,
					items: 	[this.fundMasterFilterPanel]
				},
			   {
					xtype: 'panel',
					title: 'Selektion der Spenden',
					height:200,
					id:'donationFilterPanel',
					region:'south',
					autoScroll:true,
					items: 	[this.filterPanel]
				}    
			]
		
		};
		return wrapper;
	}
});

/**
 * Donation Edit Popup
 */
Tine.Donator.PrintDonationDialog.openWindow = function (config) {
    // TODO: this does not work here, because of missing record
	record = {};
	var id = (config.record && config.record.id) ? config.record.id : 0;
    var window = Tine.WindowFactory.getWindow({
        width: 800,
        height: 500,
        name: Tine.Donator.PrintDonationDialog.prototype.windowNamePrefix + id,
        contentPanelConstructor: 'Tine.Donator.PrintDonationDialog',
        contentPanelConstructorConfig: config
    });
    return window;
};