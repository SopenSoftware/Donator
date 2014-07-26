Ext.ns('Tine.Donator');

Tine.Donator.getDonationGridConfig = function(app){
	return {
	    recordClass: Tine.Donator.Model.Donation,
	    recordProxy: Tine.Donator.Model.donationBackend,
		columns:[
		   { id: 'donation_nr', header: app.i18n._('Spenden-Nr.'), dataIndex: 'donation_nr', sortable:true },		               
		   { id: 'donation_fundmaster_contact', header: app.i18n._('Spender'), dataIndex: 'donation_fundmaster_contact', sortable:false},
		   { id: 'campaign_id', header: app.i18n._('Kampagne'), dataIndex: 'campaign_id',renderer:Tine.Donator.renderer.campaignRenderer, sortable:true},
	       { id: 'campaign_project', header: app.i18n._('Projekt'), dataIndex: 'campaign_project', sortable:false},
	       { id: 'donation_account_id', header: app.i18n._('Spendenkonto'), dataIndex: 'donation_account_id',renderer:Tine.Donator.renderer.donationAccountRenderer, sortable:true },
	       { id: 'donation_amount', header: app.i18n._('Spendenbetrag'), dataIndex: 'donation_amount',renderer: Sopen.Renderer.MonetaryNumFieldRenderer, sortable:true,summaryType:'sum',
		        summaryRenderer:Sopen.Renderer.MonetaryNumFieldRenderer },
	       { id: 'donation_date', header: app.i18n._('Datum Spende'), dataIndex: 'donation_date', renderer: Tine.Tinebase.common.dateRenderer,sortable:true, sortable:true},
	       { id: 'donation_usage', header: app.i18n._('Bemerkung'), dataIndex: 'donation_usage',hidden:true, sortable:true },
	       { id: 'confirmation_kind', header: app.i18n._('Art Bestätigung'), dataIndex: 'confirmation_kind',renderer:Tine.Donator.renderer.confirmationKind, hidden:true, sortable:true },
	       { id: 'confirmation_date', header: app.i18n._('Datum Bestätigung'), dataIndex: 'confirmation_date',renderer: Tine.Tinebase.common.dateRenderer,hidden:true, sortable:true },
	       { id: 'thanks_kind', header: app.i18n._('Art Bedankung'), dataIndex: 'thanks_kind', renderer:Tine.Donator.renderer.gratuationKind, hidden:true, sortable:true },
	       { id: 'thanks_date', header: app.i18n._('Datum Bedankung'), dataIndex: 'thanks_date',hidden:true, renderer: Tine.Tinebase.common.dateRenderer,sortable:true },
	       { id: 'donation_type', header: app.i18n._('Art Spende'), dataIndex: 'donation_type', renderer:Tine.Donator.renderer.donationType, sortable:true },
	       { header: app.i18n._('Storniert'), dataIndex: 'is_cancelled', sortable:true },
	       { header: app.i18n._('Ist Storno'), dataIndex: 'is_cancellation', sortable:true },
	       { header: app.i18n._('Buchung'), dataIndex: 'booking_id',renderer: Tine.Billing.renderer.bookingRenderer, sortable:true},
	       { header:app.i18n._('Zahlung'), dataIndex: 'payment_id', sortable:false, renderer:Tine.Billing.renderer.paymentRenderer  },
			{ header: app.i18n._('Ist Mitglied'), dataIndex: 'is_member', sortable:true },
			{ header: app.i18n._('Periode'), dataIndex: 'period', sortable:true },
			{ header: app.i18n._('Beitr.gruppe'), dataIndex: 'fee_group_id', renderer: Tine.Membership.renderer.feeGroupRenderer, sortable:true }
	   ],
	   actionTexts: {
			addRecord:{
				buttonText: 'Spende hinzufügen',
				buttonTooltip: 'Fügt einen neuen Spendendatensatz hinzu'
			},
			editRecord:{
				buttonText: 'Spende bearbeiten',
				buttonTooltip: 'Öffnet das Formular "Spendenstammdaten" zum Bearbeiten'
			},
			deleteRecord:{
				buttonText: 'Spende löschen',
				buttonTooltip: 'Löscht ausgewählte Spende(n)'
			}
	}};
};

Tine.Donator.DonationGridPanelNested = Ext.extend(Tine.widgets.grid.DependentEditFormGridPanel, {
	id: 'tine-donator-donation-nested-gridpanel',
	stateId: 'tine-donator-donation-nested-gridpanel',
	title: 'Spenden',
    grouping: false,
    withFilterToolbar: true,
    withQuickFilter: false,
    parentRelation:{
		fKeyColumn: 'fundmaster_id',
		refColumn: 'id'
	},
	// never, really never forget grid config (gridID especially), 
	// otherwise in nested panels the grid view of the parent grid get's overriden by child
	// -> causes strange effects of course	
	gridConfig: {
		gridID: 'tine-donation-donation-nest-gp',
        loadMask: true
    },	
    recordClass: Tine.Donator.Model.Donation,
    recordProxy: Tine.Donator.Model.donationBackend,
    initComponent : function() {
		this.actionTexts = Tine.Donator.getDonationGridConfig(this.app).actionTexts,
		this.filterModels = Tine.Donator.Model.Donation.getFilterModelForPlugin();
		//this.gridColumns = this.getColumns();
		Tine.Donator.DonationGridPanelNested.superclass.initComponent.call(this);
	},

	getColumns: function() {
		return Tine.Donator.getDonationGridConfig(this.app).columns;
	}
});
Ext.reg('donationnestedgrid', Tine.Donator.DonationGridPanelNested);

Tine.Donator.DonationGridPanel = Ext.extend(Tine.widgets.grid.GridPanel, {
	id: 'tine-donator-donation-gridpanel',
	stateId: 'tine-donator-donation-gridpanel',
	region:'center',
    recordClass: Tine.Donator.Model.Donation,
    evalGrants: false,
    // grid specific
    defaultSortInfo: {field: 'donation_nr', direction: 'DESC'},
    gridConfig: {
        loadMask: true,
        autoExpandColumn: 'title'
    },
    inDialog:false,
    initComponent: function() {
        this.recordProxy = Tine.Donator.donationBackend;
        //this.actionToolbarItems = this.getToolbarItems();
        this.initDetailsPanel();
        this.gridConfig.columns = this.getColumns();
        this.initFilterToolbar();
        
        this.plugins = this.plugins || [];
        this.plugins.push(this.filterToolbar);  

        Tine.Donator.DonationGridPanel.superclass.initComponent.call(this);
       this.plugins.push( this.action_showHiddenToggle);
    },
    
    initActions: function(){
    	
        this.actions_printDueAll = new Ext.Action({
            text: 'Alle fälligen Schreiben als Stapel',
			disabled: false,
            handler: this.printDueAll,
            iconCls: 'action_exportAsPdf',
            scope: this
        });
    	
        this.actions_printDueConfirmations = new Ext.Action({
            text: 'Fällige Einzelbestätigungen drucken',
			disabled: false,
            handler: this.printDueConfirmations,
            iconCls: 'action_exportAsPdf',
            scope: this
        });
        
        this.actions_printDueCollectConfirmations = new Ext.Action({
            text: 'Fällige Sammelbestätigungen drucken',
			disabled: false,
            handler: this.printDueCollectConfirmations,
            iconCls: 'action_exportAsPdf',
            scope: this
        });
        
        this.actions_printConfirmationPrepareList = new Ext.Action({
            text: 'Variable Druckausgabe',
			disabled: false,
            handler: this.expDonations,
            iconCls: 'action_exportAsPdf',
            scope: this
        });
        
        this.actions_printConfirmations = new Ext.Action({
            text: 'Einzelbestätigung(en) drucken',
			disabled: true,
            handler: this.printConfirmations,
            iconCls: 'action_exportAsPdf',
            scope: this,
            actionUpdater: this.updateSinglePrintActions,
            printActionType: 'confirmation'
        });
        
        this.actions_printCollectConfirmations = new Ext.Action({
            text: 'Sammelbestätigung(en) drucken',
			disabled: true,
            handler: this.printCollectConfirmations,
            iconCls: 'action_exportAsPdf',
            scope: this,
            actionUpdater: this.updateSinglePrintActions,
            printActionType: 'confirmation'
        });
        
        this.actions_printConfirmationsPreview = new Ext.Action({
            text: 'Vorschau Einzelbestätigung(en)',
			disabled: true,
            handler: this.printConfirmationsPreview,
            iconCls: 'action_exportAsPdf',
            scope: this,
            actionUpdater: this.updateSinglePrintActions,
            printActionType: 'confirmation'
        });
        
        this.actions_printCollectConfirmationsPreview = new Ext.Action({
            text: 'Vorschau Sammelbestätigung(en)',
			disabled: true,
            handler: this.printCollectConfirmationsPreview,
            iconCls: 'action_exportAsPdf',
            scope: this,
            actionUpdater: this.updateSinglePrintActions,
            printActionType: 'confirmation'
        });
        
        this.actions_printDueGratuations = new Ext.Action({
            text: 'Fällige Dankschreiben drucken',
			disabled: false,
            handler: this.printDueGratuations,
            iconCls: 'action_exportAsPdf',
            scope: this
        });
        
        this.actions_printGratuations = new Ext.Action({
            text: 'Dankschreiben drucken',
			disabled: true,
            handler: this.printGratuations,
            iconCls: 'action_exportAsPdf',
            scope: this,
            actionUpdater: this.updateSinglePrintActions,
            printActionType: 'gratuation'
        });
        
        this.actions_printGratuationsPreview = new Ext.Action({
            text: 'Vorschau Dankschreiben',
			disabled: true,
            handler: this.printGratuationsPreview,
            iconCls: 'action_exportAsPdf',
            scope: this,
            actionUpdater: this.updateSinglePrintActions,
            printActionType: 'gratuation'
        });
        
        this.actions_printDueAllPreview = new Ext.Action({
            text: 'Vorschau: Alle fälligen Schreiben als Stapel',
			disabled: false,
            handler: this.printDueAllPreview,
            iconCls: 'action_exportAsPdf',
            scope: this
        });
    	
        this.actions_printDueConfirmationsPreview = new Ext.Action({
            text: 'Vorschau: Fällige Einzelbestätigungen drucken',
			disabled: false,
            handler: this.printDueConfirmationsPreview,
            iconCls: 'action_exportAsPdf',
            scope: this
        });
        
        this.actions_printDueCollectConfirmationsPreview = new Ext.Action({
            text: 'Vorschau: Fällige Sammelbestätigungen drucken',
			disabled: false,
            handler: this.printDueCollectConfirmationsPreview,
            iconCls: 'action_exportAsPdf',
            scope: this
        });
        
        this.actions_printDueGratuationsPreview = new Ext.Action({
            text: 'Vorschau: Fällige Dankschreiben drucken',
			disabled: false,
            handler: this.printDueGratuationsPreview,
            iconCls: 'action_exportAsPdf',
            scope: this
        });
        
        this.action_editPayment = new Ext.Action({
            text: 'Zahlung öffnen',
            //disabled: true,
            actionType: 'edit',
            handler: this.onEditPayment,
            //actionUpdater: this.updatePayInvoiceAction,
            iconCls: 'action_edit',
            scope: this
        });
        this.action_editBooking = new Ext.Action({
            text: 'FIBU-Buchung öffnen',
            //disabled: true,
            actionType: 'edit',
            handler: this.onEditBooking,
            //actionUpdater: this.updatePayInvoiceAction,
            iconCls: 'action_edit',
            scope: this
        });
        
        this.action_editCompound = new Ext.Action({
            text: 'Verbund öffnen',
            //disabled: true,
            actionType: 'edit',
            handler: this.onEditCompound,
            //actionUpdater: this.updatePayInvoiceAction,
            iconCls: 'action_edit',
            scope: this
        });
        
        this.action_showHiddenToggle = new Tine.widgets.grid.FilterButton({
            text: this.app.i18n._('Archivierte anzeigen'),
            iconCls: 'action_showArchived',
            field: 'showHidden',
            scale: 'medium',
            rowspan: 2,
            iconAlign: 'top',
            stateId: 'sopen-donator-dontation-show-archived-toggle',
        	stateful: true,
        	stateEvents: ['toggle'],
        	getState: function(){
        		return {
        			pressed: this.pressed
        		};
        	}
        });
        
        this.actions_print = new Ext.Action({
        	allowMultiple: false,
            text: 'Druckaufträge',
            menu:{
            	items:[
            	       this.actions_printConfirmationPrepareList,
					   this.actions_printDueAll,
					   '-',
            	       this.actions_printDueConfirmations,
            	       this.actions_printDueCollectConfirmations,
            	       this.actions_printDueGratuations,
            	       '-',
            	       this.actions_printConfirmations,
            	       this.actions_printCollectConfirmations,
            	       this.actions_printGratuations
            	]
            }
        });
        
        this.actions_printPreview = new Ext.Action({
        	allowMultiple: false,
            text: 'Druckvorschau',
            menu:{
            	items:[
					   this.actions_printDueAllPreview,
					   '-',
            	       this.actions_printDueConfirmationsPreview,
            	       this.actions_printDueCollectConfirmationsPreview,
            	       this.actions_printDueGratuationsPreview,
            	       '-',
            	       this.actions_printConfirmationsPreview,
            	       this.actions_printCollectConfirmationsPreview,
            	       this.actions_printGratuationsPreview
            	]
            }
        });
        
        this.actionUpdater.addActions([
           this.actions_printConfirmations,
           this.actions_printCollectConfirmations,
           this.actions_printGratuations,
           this.actions_printConfirmationsPreview,
           this.actions_printCollectConfirmationsPreview,
           this.actions_printGratuationsPreview
       ]);
               
       this.supr().initActions.call(this);
    },
    /**
     * add custom items to action toolbar
     * 
     * @return {Object}
     */
    getActionToolbarItems: function() {
        return [
            this.action_showHiddenToggle,
            Ext.apply(new Ext.Button(this.actions_print), {
                scale: 'medium',
                rowspan: 2,
                iconAlign: 'top',
                iconCls: 'action_exportAsPdf'
            }),
            Ext.apply(new Ext.Button(this.actions_printPreview), {
                scale: 'medium',
                rowspan: 2,
                iconAlign: 'top',
                iconCls: 'action_exportAsPdf'
            })
        ];
    },
    /**
     * add custom items to context menu
     * 
     * @return {Array}
     */
    getContextMenuItems: function() {
        var items = [
            '-',
            this.actions_printConfirmations,
            this.actions_printGratuations,
            '-',
            this.actions_printConfirmationsPreview,
            this.actions_printGratuationsPreview,
            '-',
            this.action_editBooking,
            this.action_editPayment,
            this.action_editCompound
        ];
        
        return items;
    },
    onEditPayment: function(){
		var selectedRecord = this.getSelectedRecord();
		if(selectedRecord.getForeignId('payment_id')){
		   	this.lastReceiptWin = Tine.Billing.PaymentEditDialog.openWindow({
				record: new Tine.Billing.Model.Payment({id:selectedRecord.getForeignId('payment_id')},selectedRecord.getForeignId('payment_id'))
			});
		}
	},
	onEditBooking: function(){
		var selectedRecord = this.getSelectedRecord();
		if(selectedRecord.getForeignId('booking_id')){
		   	this.lastReceiptWin = Tine.Billing.BookingEditDialog.openWindow({
				record: new Tine.Billing.Model.Booking({id:selectedRecord.getForeignId('booking_id')},selectedRecord.getForeignId('booking_id'))
			});
		}
	},
	onEditCompound: function(){
		var selectedRecord = this.getSelectedRecord();
		if(selectedRecord){
		   	this.compoundWin = Tine.Billing.CompoundWorkPanel.openWindow({
				donation: selectedRecord
			});
		}
	},
	expDonations: function(){
    	//this.openPrintWindow('printConfirmationPrepareList');
		
		var win = Tine.Donator.PrintDonationDialog.openWindow({
			mainGrid: this
		});
		
    },
    printConfirmationsPreview: function(){
    	this.printConfirmations(true);
    },
    printConfirmations: function(preview){
    	this.openPrintWindow('printConfirmations', preview, this.getSelectedIds());
    },
    printCollectConfirmationsPreview: function(){
    	this.printCollectConfirmations(true);
    },
    printCollectConfirmations: function(preview){
    	this.openPrintWindow('printCollectConfirmations', preview, this.getSelectedIds());
    },
    /**
     * print preview for gratuations
     */
    printGratuationsPreview: function(){
    	this.printGratuations(true);
    },
    printGratuations: function(preview){
    	this.openPrintWindow('printGratuations', preview, this.getSelectedIds());
    },
    printDueConfirmationsPreview: function(){
    	this.printDueConfirmations(true);
    },
    printDueConfirmations: function(preview){
    	this.openPrintWindow('printDueConfirmations', preview, null);
    },
    printDueCollectConfirmationsPreview: function(){
    	this.printDueCollectConfirmations(true);
    },
    printDueCollectConfirmations: function(preview){
    	this.openPrintWindow('printDueCollectConfirmations', preview, null);
    },
    printDueGratuationsPreview: function(){
    	this.printDueGratuations(true);
    },
    printDueGratuations: function(preview){
    	this.openPrintWindow('printDueGratuations', preview, null);
    },
    printDueAllPreview: function(){
    	this.printDueAll(true);
    },
    printDueAll: function(preview){
    	this.openPrintWindow('printDueAll', preview, null);
    },
    updateSinglePrintActions: function(action, grants, records) {
    	action.setDisabled(false);
    	/*action.setDisabled(true);
    	if (records.length == 1) {
            var donation = records[0];
            if (! donation) {
                return false;
            }
            var printConfirmation = (donation.get('confirmation_kind') == 'CONFIRMATION_SINGLE');
            var printGratuation = (donation.get('gratuation_kind')=='THANK_STANDARD');
            var confirmationDate = donation.get('confirmation_date');
            var gratuationDate = donation.get('thanks_date');
            
            if(action.initialConfig.printActionType == 'confirmation' && printConfirmation==true && !confirmationDate){
            	action.setDisabled(false);
            }
            if(action.initialConfig.printActionType == 'gratuation' && printGratuation==true && !gratuationDate){
            	action.setDisabled(false);
            }
        }*/
    },
    initFilterToolbar: function() {
    	var plugins = [];
    	if(!this.inDialog){
    		plugins = [new Tine.widgets.grid.FilterToolbarQuickFilterPlugin()];	
    	}
		
		this.filterToolbar = new Tine.widgets.grid.FilterToolbar({
            app: this.app,
            filterModels: Tine.Donator.Model.Donation.getFilterModel(),
            defaultFilter: 'query',
            filters: [{field:'query',operator:'contains',value:''}],
            plugins: plugins
        });
    },
    openPrintWindow: function(method, preview, ids){
    	if(typeof(preview)==='object'){
    		preview = false;
    	}
    	var requestStr = '?method=Donator.'+method;
    	var idsStr = '';
    	if(ids){
    		idsStr = '&ids=' + Ext.util.JSON.encode(ids);
    	}
    	if(preview){
    		requestStr += '&preview=1';
    	}
    	
    	requestStr += idsStr;

    	return window.open(
				Sopen.Config.runtime.requestURI + requestStr,
				requestStr,
				"menubar=no,location=no,resizable=yes,scrollbars=yes,status=yes"
		);
    },
	getColumns: function() {
    	return Tine.Donator.getDonationGridConfig(this.app).columns;
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
	 initDetailsPanel: function() {
	        this.detailsPanel = new Tine.widgets.grid.DetailsPanel({
	            gridpanel: this,
	            
	            // use default Tpl for default and multi view
	            defaultTpl: new Ext.XTemplate(
	                '<div class="preview-panel-timesheet-nobreak">',
	                    '<!-- Preview timeframe -->',           
	                    '<div class="preview-panel preview-panel-timesheet-left">',
	                        '<div class="bordercorner_1"></div>',
	                        '<div class="bordercorner_2"></div>',
	                        '<div class="bordercorner_3"></div>',
	                        '<div class="bordercorner_4"></div>',
	                        '<div class="preview-panel-declaration">' /*+ this.app.i18n._('timeframe')*/ + '</div>',
	                        '<div class="preview-panel-timesheet-leftside preview-panel-left">',
	                            '<span class="preview-panel-bold">',
	                            /*'First Entry'*/'<br/>',
	                            /*'Last Entry*/'<br/>',
	                            /*'Duration*/'<br/>',
	                            '<br/>',
	                            '</span>',
	                        '</div>',
	                        '<div class="preview-panel-timesheet-rightside preview-panel-left">',
	                            '<span class="preview-panel-nonbold">',
	                            '<br/>',
	                            '<br/>',
	                            '<br/>',
	                            '<br/>',
	                            '</span>',
	                        '</div>',
	                    '</div>',
	                    '<!-- Preview summary -->',
	                    '<div class="preview-panel-timesheet-right">',
	                        '<div class="bordercorner_gray_1"></div>',
	                        '<div class="bordercorner_gray_2"></div>',
	                        '<div class="bordercorner_gray_3"></div>',
	                        '<div class="bordercorner_gray_4"></div>',
	                        '<div class="preview-panel-declaration">'/* + this.app.i18n._('summary')*/ + '</div>',
	                        '<div class="preview-panel-timesheet-leftside preview-panel-left">',
	                            '<span class="preview-panel-bold">',
	                            this.app.i18n._('Anzahl Spendeneingänge') + '<br/>',
	                            this.app.i18n._('Spendenbetrag gesamt') + '<br/>',
	                            '</span>',
	                        '</div>',
	                        '<div class="preview-panel-timesheet-rightside preview-panel-left">',
	                            '<span class="preview-panel-nonbold">',
	                            '{count}<br/>',
	                            '{sum}<br/>',
	                            '</span>',
	                        '</div>',
	                    '</div>',
	                '</div>'            
	            ),
	            
	            showDefault: function(body) {
	            	
					var data = {
					    count: this.gridpanel.store.proxy.jsonReader.jsonData.totalcount,
					    sum:  Sopen.Renderer.MonetaryNumFieldRenderer(this.gridpanel.store.proxy.jsonReader.jsonData.sum)
				    };
	                
	                this.defaultTpl.overwrite(body, data);
	            },
	            
	            showMulti: function(sm, body) {
	            	
	                var data = {
	                    count: sm.getCount(),
	                    sum: 0
	                };
	                sm.each(function(record){
	                    data.sum = data.sum + parseFloat(record.data.donation_amount);
	                });
	                data.sum =  Sopen.Renderer.MonetaryNumFieldRenderer(data.sum);
	                
	                this.defaultTpl.overwrite(body, data);
	            },
	            
	            tpl: new Ext.XTemplate(
	        		'<div class="preview-panel-timesheet-nobreak">',	
	        			'<!-- Preview beschreibung -->',
	        			'<div class="preview-panel preview-panel-timesheet-left">',
	        				'<div class="bordercorner_1"></div>',
	        				'<div class="bordercorner_2"></div>',
	        				'<div class="bordercorner_3"></div>',
	        				'<div class="bordercorner_4"></div>',
	        				'<div class="preview-panel-declaration">' /* + this.app.i18n._('Description') */ + '</div>',
	        				'<div class="preview-panel-timesheet-description preview-panel-left" ext:qtip="{[this.encode(values.description)]}">',
	        					'<span class="preview-panel-nonbold">',
	        					 '{[this.encode(values.description, "longtext")]}',
	        					'<br/>',
	        					'</span>',
	        				'</div>',
	        			'</div>',
	        			'<!-- Preview detail-->',
	        			'<div class="preview-panel-timesheet-right">',
	        				'<div class="bordercorner_gray_1"></div>',
	        				'<div class="bordercorner_gray_2"></div>',
	        				'<div class="bordercorner_gray_3"></div>',
	        				'<div class="bordercorner_gray_4"></div>',
	        				'<div class="preview-panel-declaration">' /* + this.app.i18n._('Detail') */ + '</div>',
	        				'<div class="preview-panel-timesheet-leftside preview-panel-left">',
	        				// @todo add custom fields here
	        				/*
	        					'<span class="preview-panel-bold">',
	        					'Ansprechpartner<br/>',
	        					'Newsletter<br/>',
	        					'Ticketnummer<br/>',
	        					'Ticketsubjekt<br/>',
	        					'</span>',
	        			    */
	        				'</div>',
	        				'<div class="preview-panel-timesheet-rightside preview-panel-left">',
	        					'<span class="preview-panel-nonbold">',
	        					'<br/>',
	        					'<br/>',
	        					'<br/>',
	        					'<br/>',
	        					'</span>',
	        				'</div>',
	        			'</div>',
	        		'</div>',{
	                encode: function(value, type, prefix) {
	                    if (value) {
	                        if (type) {
	                            switch (type) {
	                                case 'longtext':
	                                    value = Ext.util.Format.ellipsis(value, 150);
	                                    break;
	                                default:
	                                    value += type;
	                            }                           
	                        }
	                    	
	                        var encoded = Ext.util.Format.htmlEncode(value);
	                        encoded = Ext.util.Format.nl2br(encoded);
	                        
	                        return encoded;
	                    } else {
	                        return '';
	                    }
	                }
	            })
	        });
	    }
});
Ext.reg('donationgrid', Tine.Donator.DonationGridPanel);




























Tine.Donator.FundMasterDonationGridPanel = Ext.extend(Tine.widgets.grid.GridPanel, {
	id: 'tine-donator-fundmaster-donation-gridpanel',
	stateId: 'tine-donator-fundmaster-donation-gridpanel',
	region:'center',
    recordClass: Tine.Donator.Model.Donation,
    evalGrants: false,
    fundMasterRecord: null,
    memberRecord: null,
    // grid specific
    defaultSortInfo: {field: 'donation_nr', direction: 'DESC'},
    gridConfig: {
        loadMask: true,
        autoExpandColumn: 'title'
    },
    initComponent: function() {
        this.recordProxy = Tine.Donator.donationBackend;
        //this.actionToolbarItems = this.getToolbarItems();
        this.fundMasterRecord = new Tine.Donator.Model.FundMaster({},0);
        this.gridConfig.columns = this.getColumns();
        var summary = new Ext.ux.grid.GridSummary();
        this.gridConfig.plugins = [summary];
        //this.initFilterToolbar();
        
        this.plugins = this.plugins || [];
        //this.plugins.push(this.filterToolbar);        
        this.action_addDonation = new Ext.Action({
            actionType: 'edit',
            handler: this.onAddDonation,
            iconCls: 'actionAdd',
            scope: this
        });
        Tine.Donator.FundMasterDonationGridPanel.superclass.initComponent.call(this);
		 this.pagingToolbar.add(
				 '->'
		 );
		 this.pagingToolbar.add(
				 Ext.apply(new Ext.Button(this.action_addDonation), {
					 text: 'Spende hinzufügen',
		             scale: 'small',
		             rowspan: 2,
		             iconAlign: 'left'
		        }
		 ));
    },
    onAddDonation: function(){
    	this.donationWin = Tine.Donator.DonationEditDialog.openWindow({
			fundMaster: this.fundMasterRecord,
			memberRecord: this.memberRecord
		});
		this.donationWin.on('beforeclose',this.onReloadFundMaster,this);
    },
    onReloadFundMaster: function(){
    	this.store.reload();
    },
    initActions: function(){
    	
        this.actions_printDueAll = new Ext.Action({
            text: 'Alle fälligen Schreiben als Stapel',
			disabled: false,
            handler: this.printDueAll,
            iconCls: 'action_exportAsPdf',
            scope: this
        });
    	
        this.actions_printDueConfirmations = new Ext.Action({
            text: 'Fällige Spendenbestätigungen drucken',
			disabled: false,
            handler: this.printDueConfirmations,
            iconCls: 'action_exportAsPdf',
            scope: this
        });
        
        this.actions_printDueGratuations = new Ext.Action({
            text: 'Fällige Dankschreiben drucken',
			disabled: false,
            handler: this.printDueGratuations,
            iconCls: 'action_exportAsPdf',
            scope: this
        });
        
        this.actions_print = new Ext.Action({
        	allowMultiple: false,
            text: 'Druckaufträge',
            menu:{
            	items:[
					   this.actions_printDueAll,
					   '-',
            	       this.actions_printDueConfirmations,
            	       this.actions_printDueGratuations
            	]
            }
        });
        
        this.actionUpdater.addActions([
           this.actions_printDueConfirmations
       ]);
               
       this.supr().initActions.call(this);
    },
    /**
     * add custom items to action toolbar
     * 
     * @return {Object}
     */
    getActionToolbarItems: function() {
        return [
            Ext.apply(new Ext.Button(this.actions_print), {
                scale: 'medium',
                rowspan: 2,
                iconAlign: 'top',
                iconCls: 'action_exportAsPdf'
            })
        ];
    },
    /**
     * add custom items to context menu
     * 
     * @return {Array}
     */
    getContextMenuItems: function() {
        var items = [
            '-',
            this.actions_printDueConfirmations
        ];
        
        return items;
    },
    
    initFilterToolbar: function() {
		var quickFilter = [new Tine.widgets.grid.FilterToolbarQuickFilterPlugin()];	
		this.filterToolbar = new Tine.widgets.grid.FilterToolbar({
            app: this.app,
            filterModels: Tine.Donator.Model.Donation.getFilterModel(),
            defaultFilter: 'query',
            filters: [{field:'query',operator:'contains',value:''}],
            plugins: []
        });
    },
	getColumns: function() {
    	return Tine.Donator.getDonationGridConfig(this.app).columns;
	},
	    loadFundMaster: function( fundMasterRecord ){
	    	this.fundMasterRecord = fundMasterRecord;
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
			
			if (result.success) {
				this.loadFundMaster(new Tine.Donator.Model.FundMaster(result.result,result.result.id));
			} 
		},
	    onStoreBeforeload: function(store, options) {
	    	Tine.Donator.FundMasterDonationGridPanel.superclass.onStoreBeforeload.call(this, store, options);
	    	delete options.params.filter;
	    	options.params.filter = [];
	    	if(!this.fundMasterRecord || this.fundMasterRecord.id == 0){
	    		this.store.removeAll();
	    		return false;
	    	}
	    	var filter = {	
				field:'fundmaster_id',
				operator:'AND',
				value:[{
					field:'id',
					operator:'equals',
					value: this.fundMasterRecord.get('id')}]
			};
	        options.params.filter.push(filter);
	    }
});
Ext.reg('fundmasterdonationgrid', Tine.Donator.FundMasterDonationGridPanel);

