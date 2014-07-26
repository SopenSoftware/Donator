/**
 * Sopen
 * 
 * @package     Donator
 * @license     http://www.gnu.org/licenses/agpl.html AGPL Version 3
 * @author     
 * @copyright   
 * @version     $Id:  $
 *
 */
Ext.ns('Tine.Donator');

Tine.Donator.Application = Ext.extend(Tine.Addressbook.Application, {
    addressbookPlugin: null,
    
	init: function(){
		Tine.Tinebase.appMgr.on('initall',this.onInitAll,this);
	},
	
	onInitAll: function(){
		this.addressbookPlugin = new Tine.Donator.AddressbookPlugin();
		Tine.Tinebase.appMgr.get('Addressbook').registerPlugin(new Tine.Donator.AddressbookPlugin());
		this.registerPlugin(this.addressbookPlugin);
	},
    /**
     * Get translated application title of the calendar application
     * 
     * @return {String}
     */
    getTitle: function() {
        return this.i18n.ngettext('Spenden', 'Spenden', 1);
    }
});

Tine.Donator.MainScreen = Ext.extend(Tine.widgets.MainScreen, {
    activeContentType: 'FundMaster',
    westPanelXType: 'tine.donator.treepanel',
    mainPanel: null,
    fundMasterPanel: null,
    donationPanel: null,
    campaignPanel: null,
    projectPanel: null,
    fundMasterEmbedded: true,
    
    initComponent: function(){
		Tine.Donator.MainScreen.superclass.initComponent.call(this);
	},
    show: function() {
	    if(this.fireEvent("beforeshow", this) !== false){
	    	this.showWestPanel();
	        this.showCenterPanel();
	        this.showNorthPanel();
	        this.fireEvent('show', this);
	    }
	    return this;
	},
	getCenterPanel: function(activeContentType){
		switch(activeContentType){
		case 'Campaign':
			if(!this.campaignPanel){
				this.campaignPanel = new Tine.Donator.CampaignGridPanel({
					app: this.app,
					plugins:[]
				});
			}
			this.mainPanel = this.campaignPanel;
			break;
		case 'Project':
			if(!this.projectPanel){
				this.projectPanel = new Tine.Donator.ProjectGridPanel({
					app: this.app,
					plugins:[]
				});
			}
			this.mainPanel = this.projectPanel;
			break;
		case 'Donation':
			if(!this.donationPanel){
				this.donationPanel = new Tine.Donator.DonationGridPanel({
					app: this.app,
					plugins:[]
				});
			}
			this.mainPanel = this.donationPanel;
			break;
		case 'RegularDonation':
			if(!this.regularDonationPanel){
				this.regularDonationPanel = new Tine.Donator.RegularDonationGridPanel({
					app: this.app,
					plugins:[]
				});
			}
			this.mainPanel = this.regularDonationPanel;
			break;
		case 'DonationUnit':
			if(!this.donationUnitPanel){
				this.donationUnitPanel = new Tine.Donator.DonationUnitGridPanel({
					app: this.app,
					plugins:[]
				});
			}
			this.mainPanel = this.donationUnitPanel;
			break;
		case 'DonationAccount':
			if(!this.donationAccountPanel){
				this.donationAccountPanel = new Tine.Donator.DonationAccountGridPanel({
					app: this.app,
					plugins:[]
				});
			}
			this.mainPanel = this.donationAccountPanel;
			break;			
		case 'FundMaster':
//			if(this.fundMasterEmbedded){
//				if(!this.fundMasterPanel){
//					this.fundMasterPanel = Tine.Donator.getFundMasterEditRecordPanel();
//			    	this.fundMasterPanel.registerGridEvent('splitview', this.onSplitViewToggle, this);
//				}
//			}else{
				if(!this.fundMasterPanel){
					this.fundMasterPanel = new Tine.Donator.FundMasterGridPanel({
						app: this.app,
						plugins:[]
					});
				}
//			}

			this.mainPanel = this.fundMasterPanel;
			break;
		}
		return this.mainPanel;
	},
	setFundMasterEmbedded: function(fundMasterEmbedded){
		/*if( this.fundMasterEmbedded !== fundMasterEmbedded){
			delete this.fundMasterPanel;
		}
		this.fundMasterEmbedded = fundMasterEmbedded;*/
	},
	getNorthPanel: function(){
		if(this.activeContentType == 'FundMaster'){
			try{
				return this.mainPanel.getGrid().getActionToolbar();	
			}catch(e){
				
			}
			
		}
		return this.mainPanel.getActionToolbar();
	}
});

Tine.Donator.FilterPanel = function(config) {
    Ext.apply(this, config);
    Tine.Donator.FilterPanel.superclass.constructor.call(this);
};

Ext.extend(Tine.Donator.FilterPanel, Tine.widgets.persistentfilter.PickerPanel, {
	suppressEvents:false,
    filter: [{field: 'model', operator: 'equals', value: 'Donator_Model_FundMasterFilter'}],
    onFilterChange: function(){
	}
});

Tine.Donator.TreePanel = Ext.extend(Ext.tree.TreePanel, {
	rootVisible:false,
	useArrows:true,
	activeContentType: 'FundMaster',
    initComponent: function() {
        this.root = {
            id: 'root',
            leaf: false,
            expanded: true,
            children: [{
                text: this.app.i18n._('Spender'),
                id : 'fundMasterContainer',
                contentType: 'FundMaster',
                leaf:true
                /*,
                children: [
					{
					    text: this.app.i18n._('Spenden'),
					    id : 'fundMasterDonationContainer',
					    contentType: 'FundMaster',
					    leaf:true
					}         
                ]*/
            },{
                text: this.app.i18n._('Spenden'),
                id : 'donationContainer',
                contentType: 'Donation',
                leaf:true
            },{
                text: this.app.i18n._('Spendenaufträge'),
                id : 'regularDonationContainer',
                contentType: 'RegularDonation',
                leaf:true
            },{
                text: this.app.i18n._('Kampagnen'),
                id: 'campaignContainer',
                contentType: 'Campaign',
                leaf: true
            },{
                text: this.app.i18n._('Projekte'),
                id: 'projectContainer',
                contentType: 'Project',
                leaf: true
            },{
                text: this.app.i18n._('Betriebe'),
                id: 'donationUnitContainer',
                contentType: 'DonationUnit',
                leaf: true
            },{
                text: this.app.i18n._('Spendenkonten'),
                id: 'donationAccountContainer',
                contentType: 'DonationAccount',
                leaf: true
            }]
        };
        
    	Tine.Donator.TreePanel.superclass.initComponent.call(this);
        this.on('click', function(node) {
            if (node.attributes.contentType !== undefined) {
                this.app.getMainScreen().activeContentType = node.attributes.contentType;
               /* if(node.attributes.id == 'fundMasterDonationContainer'){
                	 this.app.getMainScreen().setFundMasterEmbedded(true);
                }else{
                	this.app.getMainScreen().setFundMasterEmbedded(false);
                }*/
                this.app.getMainScreen().show();
            }
        }, this);
	},
	splitViewToggle: function(){
		alert('split it');
	}
});
Ext.reg('tine.donator.treepanel',Tine.Donator.TreePanel);


