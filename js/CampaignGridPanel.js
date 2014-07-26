Ext.namespace('Tine.Donator');

/**
 * Timeaccount grid panel
 */
Tine.Donator.CampaignGridPanel = Ext.extend(Tine.widgets.grid.GridPanel, {
    recordClass: Tine.Donator.Model.Campaign,
    evalGrants: false,
    // grid specific
    defaultSortInfo: {field: 'campaign_nr', direction: 'DESC'},
    gridConfig: {
        loadMask: true,
        autoExpandColumn: 'title'
    },
    initComponent: function() {
        this.recordProxy = Tine.Donator.campaignBackend;
        
        //this.actionToolbarItems = this.getToolbarItems();
        this.gridConfig.columns = this.getColumns();
        this.initFilterToolbar();
        
        this.plugins = this.plugins || [];
        this.plugins.push(this.filterToolbar);        
        
        Tine.Donator.CampaignGridPanel.superclass.initComponent.call(this);
        //this.selectionModel.on('selectionchange',this.onSelectionChange);
        
        //this.action_addInNewWindow.setDisabled(! Tine.Tinebase.common.hasRight('manage', 'SoVendorManager', 'timeaccounts'));
        //this.action_editInNewWindow.requiredGrant = 'readGrant';
    },
    initFilterToolbar: function() {
		var quickFilter = [new Tine.widgets.grid.FilterToolbarQuickFilterPlugin()];	
		this.filterToolbar = new Tine.widgets.grid.FilterToolbar({
            app: this.app,
            filterModels: Tine.Donator.Model.Campaign.getFilterModel(),
            defaultFilter: 'query',
            filters: [{field:'query',operator:'contains',value:''}],
            plugins: quickFilter
        });
    },  
    
	getColumns: function() {
		return [
		   { id: 'campaign_nr', header: this.app.i18n._('Kampagnen-Nr'), dataIndex: 'campaign_nr', sortable:true },		
		   { id: 'donation_unit_id', header: this.app.i18n._('Betrieb'), dataIndex: 'donation_unit_id',renderer:Tine.Donator.renderer.donationUnitRenderer, sortable:true  },
		   { id: 'project_id', header: this.app.i18n._('Projekt'), dataIndex: 'project_id',renderer:Tine.Donator.renderer.projectRenderer, sortable:true  },
		   { id: 'name', header: this.app.i18n._('Bezeichnung'), dataIndex: 'name', sortable:true },
		   { id: 'description', header: this.app.i18n._('Beschreibung'), dataIndex: 'description', sortable:true },
		   { id: 'responsible_contact_id', header: this.app.i18n._('Verantwortlicher'), dataIndex: 'responsible_contact_id',renderer:Tine.Donator.renderer.contactRenderer, sortable:true },
           { id: 'donation_account_id', header: this.app.i18n._('Spendenkonto'), dataIndex: 'donation_account_id',renderer:Tine.Donator.renderer.donationAccountRenderer, sortable:true },
           { id: 'cost_unit', header: this.app.i18n._('Kostenstelle'), dataIndex: 'cost_unit', sortable:true},
           { id: 'budget', header: this.app.i18n._('Budget'), dataIndex: 'budget',renderer:Sopen.Renderer.MonetaryNumFieldRenderer, sortable:true },
           { id: 'begin', header: this.app.i18n._('Beginn'), dataIndex: 'begin', renderer: Tine.Tinebase.common.dateRenderer, sortable:true },
           { id: 'end', header: this.app.i18n._('Ende'), dataIndex: 'end', renderer: Tine.Tinebase.common.dateRenderer, sortable:true }
        ];
	}

});