Ext.namespace('Tine.Donator');

/**
 * Timeaccount grid panel
 */
Tine.Donator.ProjectGridPanel = Ext.extend(Tine.widgets.grid.GridPanel, {
    recordClass: Tine.Donator.Model.Project,
    evalGrants: false,
    // grid specific
    defaultSortInfo: {field: 'project_nr', direction: 'DESC'},
    gridConfig: {
        loadMask: true,
        autoExpandColumn: 'title'
    },
    initComponent: function() {
        this.recordProxy = Tine.Donator.projectBackend;
        
        //this.actionToolbarItems = this.getToolbarItems();
        this.gridConfig.columns = this.getColumns();
        this.initFilterToolbar();
        
        this.plugins = this.plugins || [];
        this.plugins.push(this.filterToolbar);        
        
        Tine.Donator.ProjectGridPanel.superclass.initComponent.call(this);
    },
    initFilterToolbar: function() {
		var quickFilter = [new Tine.widgets.grid.FilterToolbarQuickFilterPlugin()];	
		this.filterToolbar = new Tine.widgets.grid.FilterToolbar({
            app: this.app,
            filterModels: Tine.Donator.Model.Project.getFilterModel(),
            defaultFilter: 'query',
            filters: [{field:'query',operator:'contains',value:''}],
            plugins: quickFilter
        });
    },  
    
	getColumns: function() {
		return [
		   { id: 'project_nr', header: this.app.i18n._('Projekt-Nr'), dataIndex: 'project_nr', sortable:true },		               
		   { id: 'name', header: this.app.i18n._('Bezeichnung'), dataIndex: 'name', sortable:true },
		   { id: 'description', header: this.app.i18n._('Beschreibung'), dataIndex: 'description', sortable:true }
        ];
	}
});