Ext.namespace('Tine.Donator');

/**
 * Timeaccount grid panel
 */
Tine.Donator.DonationAccountGridPanel = Ext.extend(Tine.widgets.grid.GridPanel, {
    recordClass: Tine.Donator.Model.DonationAccount,
    evalGrants: false,
    // grid specific
    defaultSortInfo: {field: 'bank_account_nr', direction: 'DESC'},
    gridConfig: {
        loadMask: true,
        autoExpandColumn: 'title'
    },
    initComponent: function() {
        this.recordProxy = Tine.Donator.donationAccountBackend;
        
        //this.actionToolbarItems = this.getToolbarItems();
        this.gridConfig.columns = this.getColumns();
        this.initFilterToolbar();
        
        this.plugins = this.plugins || [];
        this.plugins.push(this.filterToolbar);        
        
        Tine.Donator.DonationAccountGridPanel.superclass.initComponent.call(this);
    },
    initFilterToolbar: function() {
		var quickFilter = [new Tine.widgets.grid.FilterToolbarQuickFilterPlugin()];	
		this.filterToolbar = new Tine.widgets.grid.FilterToolbar({
            app: this.app,
            filterModels: Tine.Donator.Model.DonationAccount.getFilterModel(),
            defaultFilter: 'query',
            filters: [{field:'query',operator:'contains',value:''}],
            plugins: quickFilter
        });
    },  
    
	getColumns: function() {
		return [
		   { id: 'bank_account_nr', header: this.app.i18n._('Konto-Nr'), dataIndex: 'bank_account_nr', sortable:true },		               
		   { id: 'bank_code', header: this.app.i18n._('BLZ'), dataIndex: 'bank_code', sortable:true },
		   { id: 'bank_name', header: this.app.i18n._('Bank Bezeichnung'), dataIndex: 'bank_name', sortable:true },
		   { id: 'account_name', header: this.app.i18n._('Kontoinhaber'), dataIndex: 'account_name', sortable:true },
		   { header: this.app.i18n._('Konto FIBU'), dataIndex: 'bank_account_system_id',renderer: Tine.Billing.renderer.accountSystemRenderer, sortable:true },
		   { id: 'description', header: this.app.i18n._('Beschreibung'), dataIndex: 'description', sortable:true },
        ];
	}
});