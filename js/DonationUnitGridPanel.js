Ext.namespace('Tine.Donator');

/**
 * Timeaccount grid panel
 */
Tine.Donator.DonationUnitGridPanel = Ext.extend(Tine.widgets.grid.GridPanel, {
    recordClass: Tine.Donator.Model.DonationUnit,
    evalGrants: false,
    // grid specific
    defaultSortInfo: {field: 'unit_nr', direction: 'DESC'},
    gridConfig: {
        loadMask: true,
        autoExpandColumn: 'title'
    },
    initComponent: function() {
        this.recordProxy = Tine.Donator.donationUnitBackend;
        
        //this.actionToolbarItems = this.getToolbarItems();
        this.gridConfig.columns = this.getColumns();
        this.initFilterToolbar();
        
        this.plugins = this.plugins || [];
        this.plugins.push(this.filterToolbar);        
        
        Tine.Donator.DonationUnitGridPanel.superclass.initComponent.call(this);
    },
    initFilterToolbar: function() {
		var quickFilter = [new Tine.widgets.grid.FilterToolbarQuickFilterPlugin()];	
		this.filterToolbar = new Tine.widgets.grid.FilterToolbar({
            app: this.app,
            filterModels: Tine.Donator.Model.DonationUnit.getFilterModel(),
            defaultFilter: 'query',
            filters: [{field:'query',operator:'contains',value:''}],
            plugins: quickFilter
        });
    },  
    
	getColumns: function() {
		return [
		   { id: 'contact_id', header: this.app.i18n._('Kontakt'), dataIndex: 'contact_id',renderer:Tine.Donator.renderer.contactRenderer, sortable:true  },
		   { id: 'unit_nr', header: this.app.i18n._('Betriebs-Nr'), dataIndex: 'unit_nr', sortable:true },		               
		   { id: 'unit_name', header: this.app.i18n._('Bezeichnung'), dataIndex: 'unit_name', sortable:true }
        ];
	}
});