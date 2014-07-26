Ext.ns('Tine.Donator');

Tine.Donator.getFundMasterGridConfig = function(app){
	return {
	    recordClass: Tine.Donator.Model.FundMaster,
		recordProxy: Tine.Donator.Model.fundMasterBackend,
		columns: [
		   { id: 'contact_id', header: app.i18n._('Kontakt'), dataIndex: 'contact_id',renderer:Tine.Donator.renderer.contactRenderer, sortable:true  },
		   { id: 'adr_one_street', header: app.i18n._('Strasse'), dataIndex: 'adr_one_street' },
           { id: 'adr_one_locality', header: app.i18n._('Ort'), dataIndex: 'adr_one_locality', width: 150, hidden: false },
           { id: 'adr_one_postalcode', header: app.i18n._('PLZ'), dataIndex: 'adr_one_postalcode' },
           { id: 'donation_affinity_seasonal', header: app.i18n._('Affinität saisonal'), dataIndex: 'donation_affinity_seasonal', renderer: Sopen.GenericAttribute.Renderer,hidden:true, sortable:true },		               
           { id: 'donation_affinity_thematic', header: app.i18n._('Affinität thematisch'), dataIndex: 'donation_affinity_thematic, renderer: Sopen.GenericAttribute.Renderer',hidden:true, sortable:true },
           { id: 'donation_affinity_regional', header: app.i18n._('Affinität regional'), dataIndex: 'donation_affinity_regional', renderer: Sopen.GenericAttribute.Renderer,hidden:true, sortable:true },
           { id: 'donation_affinity_spec_events', header: app.i18n._('Affinität spez. Ereign.'), dataIndex: 'donation_affinity_spec_events',hidden:true, sortable:true },
           { id: 'donator_affiliate', header: app.i18n._('Spenderwerbung'), dataIndex: 'donator_affiliate',hidden:true, sortable:true },
           { id: 'first_contact', header: app.i18n._('Erstkontakt'), dataIndex: 'first_contact', hidden:true, sortable:true },
           { id: 'first_contact_campaign_id', header: app.i18n._('Kampagne Erstkontakt'), dataIndex: 'first_contact_campaign_id',renderer:Tine.Donator.renderer.campaignRenderer,hidden:true, sortable:true },
           { id: 'reg_donation_amount', header: app.i18n._('Regelm. Spende'), dataIndex: 'reg_donation_amount',renderer: Sopen.Renderer.MonetaryNumFieldRenderer,hidden:true, sortable:true },
           { id: 'reg_donation_account_nr', header: app.i18n._('Kto. Regelm. Spende'), dataIndex: 'reg_donation_account_nr',hidden:true, sortable:true },
           { id: 'gratuation_kind', header: app.i18n._('Art Bedankung'), dataIndex: 'gratuation_kind',renderer:Tine.Donator.renderer.gratuationKind,hidden:true, sortable:true },
           { id: 'confirmation_kind', header: app.i18n._('Art Bestätigung'), dataIndex: 'confirmation_kind',renderer:Tine.Donator.renderer.confirmationKind,hidden:true },
           { id: 'donation_payment_interval', header: app.i18n._('Zahlungsweise'), dataIndex: 'donation_payment_interval',renderer:Tine.Donator.renderer.paymentInterval,hidden:true, sortable:true },
           { id: 'donation_payment_method', header: app.i18n._('Zahlungsart'), dataIndex: 'donation_payment_method',renderer:Tine.Donator.renderer.paymentMethod,hidden:true, sortable:true }
		],
		actionTexts: {
			addRecord:{
				buttonText: 'Spender Stammdaten hinzufügen',
				buttonTooltip: 'Fügt einen neuen Spender-Stammdatensatz hinzu'
			},
			editRecord:{
				buttonText: 'Spender Stammdaten bearbeiten',
				buttonTooltip: 'Öffnet das Formular "Spenderstammdaten" zum Bearbeiten'
			},
			deleteRecord:{
				buttonText: 'Spender Stammdaten löschen',
				buttonTooltip: 'Löscht ausgewählte(n) Spender'
			}
	   }};
};

/**
 * dependent edit form grid panel, to be shown in a dependent edit form
 */
Tine.Donator.FundMasterGridPanelNested = Ext.extend(Tine.widgets.grid.DependentEditFormGridPanel, {
	id: 'tine-donator-fundmaster-nested-gridpanel',
	stateId: 'tine-donator-fundmaster-nested-gridpanel',
	title: 'Spender',
	titlePrefix: 'Spender Stammdaten ',
    grouping: false,
    withFilterToolbar: true,
    parentRelation:{
		fKeyColumn: 'contact_id',
		refColumn: 'id'
	},
	// never, really never forget grid config (gridID especially), 
	// otherwise in nested panels the grid view of the parent grid get's overriden by child
	// -> causes strange effects of course
	gridConfig: {
		gridID: 'tine-donation-fundmaster-nest-gp',
        loadMask: true
    },		
    recordClass: Tine.Donator.Model.FundMaster,
    recordProxy: Tine.Donator.Model.fundMasterBackend,
	initComponent : function() {
		this.actionTexts = Tine.Donator.getFundMasterGridConfig(this.app).actionTexts,
		this.filterModels = Tine.Donator.Model.FundMaster.getFilterModel();
		
		Tine.Donator.FundMasterGridPanelNested.superclass.initComponent.call(this);
	},

	getColumns: function() {
		return Tine.Donator.getFundMasterGridConfig(this.app).columns;
	}
});
Ext.reg('fundmasternestedgrid', Tine.Donator.FundMasterGridPanelNested);

/**
 * regular grid panel
 */
Tine.Donator.FundMasterGridPanel = Ext.extend(Tine.widgets.grid.GridPanel, {
	id: 'tine-donator-fundmaster-gridpanel',
	stateId: 'tine-donator-fundmaster-gridpanel',
    recordClass: Tine.Donator.Model.FundMaster,
    evalGrants: false,
    // grid specific
    defaultSortInfo: {field: 'contact_id', direction: 'DESC'},
    gridConfig: {
        loadMask: true,
        autoExpandColumn: 'title'
    },
    initComponent: function() {
        this.recordProxy = Tine.Donator.fundMasterBackend;
        
        //this.actionToolbarItems = this.getToolbarItems();
        this.gridConfig.columns = this.getColumns();
        this.initFilterToolbar();
        
        this.plugins = this.plugins || [];
        this.plugins.push(this.filterToolbar);        
        
        Tine.Donator.FundMasterGridPanel.superclass.initComponent.call(this);
        this.plugins.push( this.action_showHiddenToggle);
    },
    
    initActions: function(){
        this.action_showHiddenToggle = new Tine.widgets.grid.FilterButton({
            text: this.app.i18n._('Archivierte anzeigen'),
            iconCls: 'action_showArchived',
            field: 'showHidden',
            scale: 'medium',
            rowspan: 2,
            iconAlign: 'top',
            stateId: 'sopen-donator-fundmaster-show-archived-toggle',
        	stateful: true,
        	stateEvents: ['toggle'],
        	getState: function(){
        		return {
        			pressed: this.pressed
        		};
        	}
        });
        
       this.supr().initActions.call(this);
    },
    /**
     * add custom items to action toolbar
     * 
     * @return {Object}
     */
    getActionToolbarItems: function() {
        return [
            this.action_showHiddenToggle
        ];
    },
    /**
     * add custom items to context menu
     * 
     * @return {Array}
     */
    getContextMenuItems: function() {
        var items = [
        ];
        
        return items;
    },
    
    initFilterToolbar: function() {
		var quickFilter = [new Tine.widgets.grid.FilterToolbarQuickFilterPlugin()];	
		this.filterToolbar = new Tine.widgets.grid.FilterToolbar({
            app: this.app,
            filterModels: Tine.Donator.Model.FundMaster.getFilterModel(),
            defaultFilter: 'query',
            filters: [{field:'query',operator:'contains',value:''}],
            plugins: quickFilter
        });
    },  
    
	getColumns: function() {
    	return Tine.Donator.getFundMasterGridConfig(this.app).columns;
	}
});
Ext.reg('fundMastergrid', Tine.Donator.FundMasterGridPanel);

Tine.Donator.FundMasterSelectionGrid = Ext.extend(Tine.widgets.grid.GridPanel, {
	id: 'tine-fund-fundmaster-selection-grid',
	stateId: 'tine-fund-fundmaster-selection-grid',
    recordClass: Tine.Donator.Model.FundMaster,
    evalGrants: false,
    // grid specific
    defaultSortInfo: {field: 'contact_id', direction: 'DESC'},
    useQuickSearchPlugin: false,
    
    gridConfig: {
        loadMask: true,
        autoExpandColumn: 'title',
        // drag n drop
        enableDragDrop: true,
        ddGroup: 'ddGroupFundMaster',
        ddGroupContact: 'ddGroupGetContact'
    },
    initComponent: function() {
        this.recordProxy = Tine.Donator.fundMasterBackend;
        
        this.gridConfig.columns = this.getColumns();
        this.filterToolbar = this.getFilterToolbar();
        
        this.plugins = this.plugins || [];
        this.plugins.push(this.filterToolbar);        
        
        Tine.Donator.FundMasterSelectionGrid.superclass.initComponent.call(this);
    },
    
	getColumns: function() {
    	return Tine.Donator.getFundMasterGridConfig(this.app).columns;
	}
});
Ext.reg('fundmasterselectiongrid', Tine.Donator.FundMasterSelectionGrid);
