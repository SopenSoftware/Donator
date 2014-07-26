Ext.ns('Tine.Donator');

Tine.Donator.getRegularDonationGridConfig = function(app){
	return {
	    recordClass: Tine.Donator.Model.RegularDonation,
	    recordProxy: Tine.Donator.Model.regularDonationBackend,
		columns:[
		   { id: 'regular_donation_nr', header: app.i18n._('Spendenauftrag-Nr.'), dataIndex: 'regular_donation_nr', sortable:true },		               
		   { id: 'donation_fundmaster_contact', header: app.i18n._('Spender'), dataIndex: 'donation_fundmaster_contact', sortable:false},
	       { id: 'campaign_id', header: app.i18n._('Kampagne'), dataIndex: 'campaign_id',renderer:Tine.Donator.renderer.campaignRenderer, sortable:true},
	       { id: 'campaign_project', header: app.i18n._('Projekt'), dataIndex: 'campaign_project', sortable:false},
	       { id: 'donation_account_id', header: app.i18n._('Spendenkonto'), dataIndex: 'donation_account_id',renderer:Tine.Donator.renderer.donationAccountRenderer, sortable:true },
	       { id: 'reg_donation_amount', header: app.i18n._('Spendenbetrag'), dataIndex: 'reg_donation_amount',renderer: Sopen.Renderer.MonetaryNumFieldRenderer, sortable:true,summaryType:'sum',
		        summaryRenderer:Sopen.Renderer.MonetaryNumFieldRenderer },
	       { id: 'begin_date', header: app.i18n._('Beginn'), dataIndex: 'begin_date', renderer: Tine.Tinebase.common.dateRenderer,sortable:true, sortable:true},
	       { id: 'next_date', header: app.i18n._('Nächste Fälligkeit'), dataIndex: 'next_date', renderer: Tine.Tinebase.common.dateRenderer,sortable:true, sortable:true},
	       { id: 'end_date', header: app.i18n._('Ende'), dataIndex: 'end_date', renderer: Tine.Tinebase.common.dateRenderer,sortable:true, sortable:true},
	       
	       { id: 'confirmation_kind', header: app.i18n._('Art Bestätigung'), dataIndex: 'confirmation_kind',renderer:Tine.Donator.renderer.confirmationKind, hidden:true, sortable:true },
	       { id: 'gratuation_kind', header: app.i18n._('Art Bedankung'), dataIndex: 'gratuation_kind', renderer:Tine.Donator.renderer.gratuationKind, hidden:true, sortable:true },
	       { id: 'donation_usage', header: app.i18n._('Vwzw'), dataIndex: 'donation_usage',hidden:true, sortable:true },
	       { id: 'bank_account_nr', header: app.i18n._('Kto.Nr.'), dataIndex: 'bank_account_nr',hidden:true, sortable:true },
	       { id: 'bank_code', header: app.i18n._('BLZ'), dataIndex: 'bank_code',hidden:true, sortable:true },
	       { id: 'bank_name', header: app.i18n._('Bank'), dataIndex: 'bank_name',hidden:true, sortable:true },
	       { id: 'account_name', header: app.i18n._('Kontoinhaber'), dataIndex: 'account_name',hidden:true, sortable:true },
	       { header: app.i18n._('Ausgesetzt'), dataIndex: 'on_hold',hidden:true, sortable:true },
	       { header: app.i18n._('Beendet'), dataIndex: 'terminated',hidden:true, sortable:true },
	       { header: app.i18n._('Kontrollbetrag'), dataIndex: 'control_sum',renderer: Sopen.Renderer.MonetaryNumFieldRenderer, sortable:true,
		        summaryRenderer:Sopen.Renderer.MonetaryNumFieldRenderer },
		   { header: app.i18n._('Kontr.zähler'), dataIndex: 'control_count',sortable:true },
		   { header: app.i18n._('Mitgl.ausetr.'), dataIndex: 'terminated_membership',sortable:true }
	   ],
	   actionTexts: {
			addRecord:{
				buttonText: 'Spendenauftrag hinzufügen',
				buttonTooltip: 'Fügt einen neuen Spendenauftrag hinzu'
			},
			editRecord:{
				buttonText: 'Spendenauftrag bearbeiten',
				buttonTooltip: 'Öffnet das Formular "Spendenauftrag" zum Bearbeiten'
			},
			deleteRecord:{
				buttonText: 'Spendenauftrag löschen',
				buttonTooltip: 'Löscht ausgewählte Spendenaufträge)'
			}
	}};
};

Tine.Donator.RegularDonationGridPanel = Ext.extend(Tine.widgets.grid.GridPanel, {
	id: 'tine-donator-regular-donation-gridpanel',
	stateId: 'tine-donator-reglar-donation-gridpanel',
	region:'center',
    recordClass: Tine.Donator.Model.RegularDonation,
    evalGrants: false,
    defaultSortInfo: {field: 'regular_donation_nr', direction: 'DESC'},
    gridConfig: {
        loadMask: true,
        autoExpandColumn: 'title'
    },
    fundMasterRecord: null,
    memberRecord: null,
    perspective: 'COMMON',
    initComponent: function() {
        this.recordProxy = Tine.Donator.regularDonationBackend;
        this.fundMasterRecord = new Tine.Donator.Model.FundMaster({},0);
        
        //this.actionToolbarItems = this.getToolbarItems();
        this.gridConfig.columns = this.getColumns();
        this.initFilterToolbar();
        
        this.plugins = this.plugins || [];
        this.plugins.push(this.filterToolbar);
        
        this.action_addRegularDonation = new Ext.Action({
            actionType: 'edit',
            handler: this.addRegularDonation,
            iconCls: 'actionAdd',
            scope: this
        });
      
        this.on('afterrender', this.onAfterRender, this);
        Tine.Donator.RegularDonationGridPanel.superclass.initComponent.call(this);
        this.pagingToolbar.add(
				 '->'
		);
		this.pagingToolbar.add(
			 Ext.apply(new Ext.Button(this.action_addRegularDonation), {
				 text: 'Spendenauftrag hinzufügen',
		         scale: 'small',
		         rowspan: 2,
		         iconAlign: 'left'
		     }
		));
    },
    initFilterToolbar: function() {
    	var plugins = [];
    	if(this.perspective == 'COMMON'){
    		plugins = [new Tine.widgets.grid.FilterToolbarQuickFilterPlugin()];	
    	}
    	this.filterToolbar = new Tine.widgets.grid.FilterToolbar({
            app: this.app,
            filterModels: Tine.Donator.Model.RegularDonation.getFilterModel(),
            defaultFilter: 'query',
            filters: [{field:'query',operator:'contains',value:''}],
            plugins: plugins
        });
        
    },
    getColumns: function() {
    	return Tine.Donator.getRegularDonationGridConfig(this.app).columns;
	},
	initActions: function(){
    	// was moved: does not generate DTA but create invoices
        this.actions_generateDTA = new Ext.Action({
            text: 'Spendenaufträge sollstellen',
			disabled: false,
            handler: this.generateDTA,
            iconCls: 'action_edit',
            scope: this
        });
        
        this.actions_improveRegularDonations = new Ext.Action({
            text: 'Spendenaufträge prüfen',
			disabled: false,
            handler: this.improveRegularDonations,
            iconCls: 'action_edit',
            scope: this
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
            Ext.apply(new Ext.Button(this.actions_improveRegularDonations), {
                scale: 'medium',
                rowspan: 2,
                iconAlign: 'top',
                iconCls: 'action_edit'
            }),
            Ext.apply(new Ext.Button(this.actions_generateDTA), {
                scale: 'medium',
                rowspan: 2,
                iconAlign: 'top',
                iconCls: 'action_edit'
            })
        ];
    },
    generateDTA: function(){
    	Ext.Ajax.request({
			scope: this,
			success: this.onExecRegDon,
			timeout:3600000,
			params: {
				method: 'Donator.executeRegularDonations'
			},
			failure: this.onExecRegDonFailure
		});
    },
    onExecRegDon: function(response){
    	Ext.MessageBox.show({
            title: 'Erfolg', 
            msg: 'Die Sollstellung der Spendenaufträge ist fertiggestellt.',
            buttons: Ext.Msg.OK,
            icon: Ext.MessageBox.INFO
        });
    },
    onExecRegDonFailure: function(response){
    	Ext.MessageBox.show({
            title: 'Fehler', 
            msg: 'Die Sollstellung der Spendenaufträge ist fehlgeschlagen.',
            buttons: Ext.Msg.OK,
            icon: Ext.MessageBox.WARNING
        });
    },
    improveRegularDonations: function(){
    	Ext.Ajax.request({
			scope: this,
			success: this.onImproveRegularDonations,
			timeout:3600000,
			params: {
				method: 'Donator.improveRegularDonations'
			},
			failure: this.onImproveRegularDonationsFailure
		});
    },
    onImproveRegularDonations: function(response){
    	Ext.MessageBox.show({
            title: 'Erfolg', 
            msg: 'Die Überprüfung der Spendenaufträge ist fertiggestellt.',
            buttons: Ext.Msg.OK,
            icon: Ext.MessageBox.INFO
        });
    },
    onImproveRegularDonationsFailure: function(response){
    	Ext.MessageBox.show({
            title: 'Fehler', 
            msg: 'Die Überprüfung der Spendenaufträge ist fehlgeschlagen.',
            buttons: Ext.Msg.OK,
            icon: Ext.MessageBox.WARNING
        });
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
	onEditDonator: function(record){
		alert('edit donator');
	},
	getSelectedIds: function(){
		 var selectedRows = this.grid.getSelectionModel().getSelections();
		 var result = [];
		 for(var i in selectedRows){
			 result.push(selectedRows[i].id);
		 }
		 return result;
	},
    loadFundMaster: function( fundMasterRecord ){
		if(fundMasterRecord.data.result !== undefined){
			this.fundMasterRecord = new Tine.Donator.Model.FundMaster(fundMasterRecord.data.result, fundMasterRecord.id);
		}else{
		this.fundMasterRecord = new Tine.Donator.Model.FundMaster(fundMasterRecord.data, fundMasterRecord.id);
		}
    	
    	this.store.reload();
    },
    loadMember: function( memberRecord ){
    	this.memberRecord = memberRecord;
    	Ext.Ajax
		.request({
			scope : this,
			params : {
				method : 'Donator.getFundMasterByContactId',
				contactId :  memberRecord.getForeignId('contact_id')
			},
			success : this.onLoadMember,
			failure : function(response) {
				//
			}
		});
    },
    onLoadMember: function(response) {
		var result = Ext.util.JSON
				.decode(response.responseText);
		
		console.log(result);
		
		//var data = result.result;
		
		if (result) {
			this.loadFundMaster(new Tine.Donator.Model.FundMaster(result,result.id));
		} 
	},
    onStoreBeforeload: function(store, options) {
    	Tine.Donator.RegularDonationGridPanel.superclass.onStoreBeforeload.call(this, store, options);
    	if(!this.useImplicitForeignRecordFilter == true){
    		return;
    	}
    	
    	if(!this.fundMasterRecord){
    		return;
    	}
    	delete options.params.filter;
    	options.params.filter = [];
    	if(this.perspective == 'FUNDMASTER' && this.fundMasterRecord && this.fundMasterRecord.id == 0){
    		this.store.removeAll();
    		return false;
    	}
    	
    	if(this.perspective == 'FUNDMASTER' && this.fundMasterRecord){
    		this.addForeignFilter('fundmaster_id', this.fundMasterRecord, options);
    	}
    },
    addForeignFilter: function(field, record, options){
    	var filter = {	
			field:field,
			operator:'AND',
			value:[{
				field:'id',
				operator:'equals',
				value: record.get('id')}]
		};
        options.params.filter.push(filter);
    },
    addRegularDonation: function(){
    	this.donationWin = Tine.Donator.RegularDonationEditDialog.openWindow({
			fundMaster: this.fundMasterRecord,
			memberRecord: this.memberRecord
		});
		this.donationWin.on('beforeclose',this.store.reload(),this.store);
    },
    getDetailsPanel: function(){
    	return null;
    },
    onAfterRender: function(){
    	
    }
});

