Ext.ns('Tine.Donator','Tine.Donator.Model');

Tine.Donator.Model.CampaignArray = [
	{name: 'id'},
	{name: 'project_id'},
	{name: 'gratuation_template_id'},
	{name: 'campaign_nr'},
	{name: 'name'},
	{name: 'description'},
	{name: 'responsible_contact_id'},
	{name: 'donation_account_id'},
	{name: 'donation_unit_id'},
	{name: 'cost_unit'},
	{name: 'budget'},
	{name: 'begin'},
	{name: 'end'},
	{name: 'gratuation_kind'},
	{name: 'is_closed'},
	{name: 'erp_proceed_account_id'},
	{name: 'erp_activity_account_id'},
	{name: 'debit_account_system_id'},
	{name: 'bank_account_system_id'}
];

Tine.Donator.Model.Campaign = Tine.Tinebase.data.Record.create(Tine.Donator.Model.CampaignArray, {
   appName: 'Donator',
   modelName: 'Campaign',
   idProperty: 'id',
   titleProperty: 'name',
   recordName: 'Spendenkampagne',
   recordsName: 'Spendenkampagnen',
   containerProperty: null
});

Tine.Donator.Model.Campaign.getDefaultData = function(){
	return {
	};
};

Tine.Donator.Model.Campaign.getFilterModel = function() {
    var app = Tine.Tinebase.appMgr.get('Donator');
    return [
        {label: _('Quick search'),          field: 'query',       operators: ['contains']},
        {app: app, filtertype: 'foreignrecord', label: 'Kampagne', field: 'project_id', foreignRecordClass: Tine.Donator.Model.Project, ownField:'project_id'}
    ];
}

Tine.Donator.Model.ProjectArray = [
	{name: 'id'},
	{name: 'project_nr'},
	{name: 'name'},
	{name: 'description'}
];

Tine.Donator.Model.Project = Tine.Tinebase.data.Record.create(Tine.Donator.Model.ProjectArray, {
   appName: 'Donator',
   modelName: 'Project',
   idProperty: 'id',
   titleProperty: 'name',
   recordName: 'Spendenprojekt',
   recordsName: 'Spendenprojekte',
   containerProperty: null
});

Tine.Donator.Model.Project.getDefaultData = function(){
	return {
	};
};

Tine.Donator.Model.Project.getFilterModel = function() {
    var app = Tine.Tinebase.appMgr.get('Donator');
    return [
        {label: _('Quick search'),          field: 'query',       operators: ['contains']}
    ];
}
/**
* sopen Donator model
*/
Tine.Donator.Model.FundMasterArray = 
[
  {name: 'id'},
  {name: 'contact_id'},
  {name: 'adr_one_street'},
  {name: 'adr_one_postalcode'},
  {name: 'adr_one_locality'},
  {name: 'donation_affinity_seasonal'},
  {name: 'donation_affinity_thematic'},
  {name: 'donation_affinity_regional'},
  {name: 'donation_affinity_spec_events'},
  {name: 'donator_affiliate'},
  {name: 'first_contact' },
  {name: 'first_contact_campaign_id'},
  {name: 'reg_donation_amount'},
  {name: 'reg_donation_account_nr'},
  {name: 'gratuation_kind'},
  {name: 'confirmation_kind'},
  {name: 'donation_payment_interval'},
  {name: 'donation_payment_method'},
  {name: 'is_fm_hidden'},
  {name: 'bank_account_nr'},
  {name: 'bank_code'},
  {name: 'bank_name'},
  {name: 'account_name'}
];

/**
* @type {Tine.Tinebase.data.Record}
* Contact record definition
*/
Tine.Donator.Model.FundMaster = Tine.Tinebase.data.Record.create(Tine.Donator.Model.FundMasterArray, {
	appName: 'Donator',
	modelName: 'FundMaster',
	idProperty: 'id',
	recordName: 'Spender',
	recordsName: 'Spender',
	containerProperty: null,
	containerName: 'Kontakt',
	containersName: 'Kontakte',
	titleProperty: 'contact_n_fileas',
    getContact: function(){
    	var assocContact = this.get('contact_id');
    	var assocContactId = assocContact.id;
    	assocContact.jpegphoto = null;
    	return new Tine.Addressbook.Model.Contact(assocContact,assocContactId);
    },
	relations:[
   		{
   			name: 'contact',
   			model: Tine.Addressbook.Model.Contact,
   			fkey: 'contact_id',
   			embedded:true,
   			emissions:[
   			    {dest: {name: 'contact_n_fileas'}, source: function(contact){return contact.getTitle();}},
   			    {dest: {name: 'contact_org_name'}, source: function(contact){return contact.get('org_name');}},
   			    {dest: {name: 'contact_adr_one_street'}, source: function(contact){return contact.get('adr_one_street');}},
   			    {dest: {name: 'contact_adr_one_location'}, source: function(contact){return contact.get('adr_one_street') + ' ' +contact.get('adr_one_locality');}},
   			    {dest: {name: 'contact_adr_one_locality'}, source: function(contact){return contact.get('adr_one_locality');}}
   			]
   		}
   	]
});

Tine.Donator.Model.FundMaster.getDefaultData = function(){
	return {
		contact_id: new Tine.Addressbook.Model.Contact({},0),
		donation_affinity_seasonal:0,
		donation_affinity_thematic:0,
		donation_affinity_regional:0,
		donation_affinity_spec_events:0,
		donator_affiliate:0,
		reg_donation_amount: 0,
		reg_donation_account_nr: '',
		confirmation_kind:'CONFIRMATION_NO',
		gratuation_kind:'THANK_NO',
		donation_payment_interval:'NOVALUE',
		donation_payment_method:'NOVALUE'
	};
};

Tine.Donator.Model.FundMaster.getFilterModel = function() {
    var app = Tine.Tinebase.appMgr.get('Donator');
    return [
        {label: _('Quick search'),          field: 'query',       operators: ['contains']},
        {app: app, filtertype: 'foreignrecord', label: 'Kontakt', field: 'contact_id', foreignRecordClass: Tine.Addressbook.Model.Contact, ownField:'contact_id'}
    ];
}

Tine.Donator.Model.DonationArray = [
	{name: 'id'},
	{name: 'fundmaster_id'},
	// contact_id not really contained: just a phantom for level 2 contact-foreign-id filter
	{name: 'contact_id'},
	{name: 'related_donation_id'},
	{name: 'donation_nr'},
	{name: 'campaign_id'},
	{name: 'thanks_date'},
	{name: 'confirmation_date'},
	{name: 'gratuation_kind'},
	{name: 'donation_account_id'},
	{name: 'donation_amount'},
	{name: 'donation_date', type: 'date', dateFormat: Date.patterns.ISO8601Long },
	{name: 'donation_usage'},
	{name: 'confirmation_kind'},
	{name: 'non_monetary'},
	{name: 'non_monetary_source'},
	{name: 'non_monetary_rating'},
	{name: 'refund_quitclaim'},
	{name: 'non_monetary_description'},
	{name: 'confirm_nr'},
	{name: 'is_hidden'},
	{name: 'is_cancelled'},
	{name: 'is_cancellation'},
	{name: 'erp_proceed_account_id'},
	{name: 'donation_type'},
	{name: 'booking_id'},
	{name: 'payment_id'},
	{name: 'allow_booking'},
    {name: 'period'},
	{name: 'is_member'},
	{name: 'fee_group_id'}
];

Tine.Donator.Model.Donation = Tine.Tinebase.data.Record.create(Tine.Donator.Model.DonationArray, {
   appName: 'Donator',
   modelName: 'Donation',
   idProperty: 'id',
   recordName: 'Spende',
   recordsName: 'Spenden',
	relations:[
   		{
   			name: 'donation_campaign_project',
   			model: Tine.Donator.Model.Campaign,
   			fkey: 'campaign_id',
   			embedded:true,
   			emissions:[
   			    {dest: {
   			    	name: 'campaign_project'}, 
   			    	source: function(campaign){
   			    		var project = campaign.get('project_id');
   			    		if(typeof(project) === 'object'){
   			    			project = new Tine.Donator.Model.Project(project);
   			    			return project.getTitle() + ' [# '+project.get('project_nr')+' ]';
   			    		}else{
   			    			return '';
   			    		}
   			    	}
   			    }
   			]
   		},{
   			name: 'donation_fundmaster_contact',
   			model: Tine.Donator.Model.FundMaster,
   			fkey: 'fundmaster_id',
   			embedded:true,
   			emissions:[
   			    {dest: {
   			    	name: 'donation_fundmaster_contact'}, 
   			    	source: function(fundmaster){
   			    		var contact = fundmaster.get('contact_id');
   			    		if(typeof(contact) === 'object'){
   			    			contact = new Tine.Addressbook.Model.Contact(contact);
   			    			return contact.getTitle() + ' [# '+contact.get('id')+' ]';
   			    		}else{
   			    			return '';
   			    		}
   			    	}
   			    }
   			]
   		}
   	]
});

Tine.Donator.Model.Donation.getDefaultData = function(){
	return {
		donation_amount:0,
		confirmation_kind:'CONFIRMATION_NO',
		gratuation_kind:'THANK_NO'//,
		//donation_date: new Date()
	};
};

Tine.Donator.Model.Donation.getFilterModel = function() {
    var app = Tine.Tinebase.appMgr.get('Donator');
	var feeGroupStore = Tine.Membership.getStore('FeeGroup');
	feeGroupStore.push(['','NICHTMITGLIED']);
	
	
    return [
        {label: _('Quick search'),          field: 'query',       operators: ['contains']},
        {app: app, filtertype: 'foreignrecord', label: 'Spender', field: 'contact_id', foreignRecordClass: Tine.Addressbook.Model.Contact, ownField:'contact_id'},
        {app: app, filtertype: 'foreignrecord', label: 'Kampagne', field: 'campaign_id', foreignRecordClass: Tine.Donator.Model.Campaign, ownField:'campaign_id'},
        {label: _('Spendenbetrag'),          field: 'donation_amount',       operators: ['greater','less','equals']},
        {label: _('Spendendatum'),         field: 'donation_date', valueType: 'date', pastOnly: true},
        {label: _('Datum Bestätigung'),         field: 'confirmation_date', valueType: 'date', pastOnly: true},
        {label: _('Datum Bedankung'),         field: 'thanks_date', valueType: 'date', pastOnly: true},
         {label: app.i18n._('Periode/Wirtschaftsjahr'),  field: 'period', valueType: 'number'},    	
			{label: app.i18n._('Beitragsgruppe Mitglied'),  field: 'fee_group_id',  valueType: 'combo', valueField:'id', displayField:'name',operators: ['equals','not', 'isnull', 'notnull'],
				store:feeGroupStore},
        {label: app.i18n._('Art Bestätigung'),  field: 'confirmation_kind',  valueType: 'combo', valueField:'id', displayField:'name', 
        	store:[['CONFIRMATION_NO', 'Keine'],['CONFIRMATION_SINGLE','Einzeln'],['CONFIRMATION_COLLECT', 'Gesammelt']]},
    	{label: app.i18n._('Art Bedankung'),  field: 'gratuation_kind',  valueType: 'combo', valueField:'id', displayField:'name', 
        	store:[['THANK_NO', 'Keine'],['THANK_STANDARD','Standard'],['THANK_INDIVIDUAL', 'Individuell']]},
        
        {label: app.i18n._('Art Spende'),  field: 'donation_type',  valueType: 'combo', valueField:'id', displayField:'name', 
        	store:[['SINGLE', 'Einzelspende'],['CYCLE','Dauerspende']]},
        {label: app.i18n._('Storniert'),   field: 'is_cancelled',  valueType: 'bool'},
        {label: app.i18n._('IstStorno'),   field: 'is_cancellation',  valueType: 'bool'}
    ];
};

Tine.Donator.Model.Donation.getFilterModelForPlugin = function() {
    var app = Tine.Tinebase.appMgr.get('Donator');
    return [
        {label: _('Quick search'),          field: 'query',       operators: ['contains']},
        {app: app, filtertype: 'foreignrecord', label: 'Kampagne', field: 'campaign_id', foreignRecordClass: Tine.Donator.Model.Campaign, ownField:'campaign_id'},
        {label: _('Spendenbetrag'),          field: 'donation_amount',       operators: ['greater','less','equals']},
        {label: _('Spendendatum'),         field: 'donation_date', valueType: 'date', pastOnly: true}
        
    ];
};




/**
* sopen donation unit (ge: Betrieb) model
*/
Tine.Donator.Model.DonationUnitArray = [
  {name: 'id'},
  {name: 'contact_id'},
  {name: 'unit_nr'},
  {name: 'unit_name'}

];

/**
* @type {Tine.Tinebase.data.Record}
* Contact record definition
*/
Tine.Donator.Model.DonationUnit = Tine.Tinebase.data.Record.create(Tine.Donator.Model.DonationUnitArray, {
	appName: 'Donator',
	modelName: 'DonationUnit',
	idProperty: 'id',
	recordName: 'Betrieb',
	recordsName: 'Betriebe',
	containerProperty: null,
	containerName: 'Kontakt',
	containersName: 'Kontakte',
	titleProperty: 'unit_nr',
    getContact: function(){
    	var assocContact = this.get('contact_id');
    	var assocContactId = assocContact.id;
    	assocContact.jpegphoto = null;
    	return new Tine.Addressbook.Model.Contact(assocContact,assocContactId);
    },
    getTitle: function(){
    	return this.get('unit_nr') + ' ' + this.get('unit_name');
    },
	relations:[
   		{
   			name: 'contact',
   			model: Tine.Addressbook.Model.Contact,
   			fkey: 'contact_id',
   			embedded:true,
   			emissions:[
   			    {dest: {name: 'contact_n_fileas'}, source: function(contact){return contact.getTitle();}},
   			    {dest: {name: 'contact_org_name'}, source: function(contact){return contact.get('org_name');}},
   			    {dest: {name: 'contact_adr_one_street'}, source: function(contact){return contact.get('adr_one_street');}},
   			    {dest: {name: 'contact_adr_one_location'}, source: function(contact){return contact.get('adr_one_street') + ' ' +contact.get('adr_one_locality');}},
   			    {dest: {name: 'contact_adr_one_locality'}, source: function(contact){return contact.get('adr_one_locality');}}
   			]
   		}
   	]
});

Tine.Donator.Model.DonationUnit.getDefaultData = function(){
	return {
		contact_id: new Tine.Addressbook.Model.Contact({},0)
	};
};

Tine.Donator.Model.DonationUnit.getFilterModel = function() {
    var app = Tine.Tinebase.appMgr.get('Donator');
    return [
        {label: _('Quick search'),          field: 'query',       operators: ['contains']},
        {app: app, filtertype: 'foreignrecord', label: 'Kontakt', field: 'contact_id', foreignRecordClass: Tine.Addressbook.Model.Contact, ownField:'contact_id'}
    ];
};



Tine.Donator.Model.DonationAccountArray = [
	{name: 'id'},
	{name: 'bank_account_nr'},
	{name: 'bank_code'},
	{name: 'bank_name'},
	{name: 'account_name'},
	{name: 'description'},
	{name: 'bank_account_system_id'}
];

Tine.Donator.Model.DonationAccount = Tine.Tinebase.data.Record.create(Tine.Donator.Model.DonationAccountArray, {
   appName: 'Donator',
   modelName: 'DonationAccount',
   idProperty: 'id',
   titleProperty: 'bank_account_nr',
   recordName: 'Spendenkonto',
   recordsName: 'Spendenkonten',
   containerProperty: null,
   getTitle: function(){
	   return this.get('bank_account_nr') + ' ' + this.get('bank_name');
   }
});

Tine.Donator.Model.DonationAccount.getDefaultData = function(){
	return {
	};
};

Tine.Donator.Model.DonationAccount.getFilterModel = function() {
    var app = Tine.Tinebase.appMgr.get('Donator');
    return [
        {label: _('Quick search'),          field: 'query',       operators: ['contains']}
    ];
};

Tine.Donator.Model.RegularDonationArray = 
[
  {name: 'id'},
  {name: 'regular_donation_nr'},
  {name: 'fundmaster_id'},
  {name: 'campaign_id'},
  {name: 'donation_account_id'},
  {name: 'erp_proceed_account_id'},
  {name: 'last_receipt_id'},
  {name: 'last_donation_id'},
  {name: 'begin_date'},
  {name: 'next_date'},
  {name: 'last_date'},
  {name: 'end_date'},
  {name: 'reg_donation_amount'},
  {name: 'gratuation_kind'},
  {name: 'confirmation_kind' },
  {name: 'donation_payment_interval'},
  {name: 'donation_payment_method'},
  /*{name: 'bank_account_nr'},
  {name: 'bank_code'},
  {name: 'bank_name'},
  {name: 'account_name'},*/
  {name: 'on_hold'},
  {name: 'terminated'},
  {name: 'donation_usage'},
  {name: 'control_count'},
  {name: 'control_sum'},
  {name: 'terminated_membership'},
  {name: 'sepa_mandate_id'},
  {name: 'bank_account_id'},
  {name: 'bic'},
  {name: 'iban'},
  {name: 'bank_account_number'},
  {name: 'bank_account_bank_code'},
  {name: 'bank_account_name'},
  {name: 'bank_account_bank_name'},
  {name: 'sepa_mandate_ident'},
  {name: 'sepa_signature_date'}
  
];

/**
* @type {Tine.Tinebase.data.Record}
* Contact record definition
*/
Tine.Donator.Model.RegularDonation = Tine.Tinebase.data.Record.create(Tine.Donator.Model.RegularDonationArray, {
	appName: 'Donator',
	modelName: 'RegularDonation',
	idProperty: 'id',
	recordName: 'Spendenauftrag',
	recordsName: 'Spendenaufträge',
	containerProperty: null,
	titleProperty: 'donation_fundmaster_contact',
    getContact: function(){
    	
    },
	relations:[
		{
			name: 'donation_fundmaster_contact',
			model: Tine.Donator.Model.FundMaster,
			fkey: 'fundmaster_id',
			embedded:true,
			emissions:[
			    {dest: {
			    	name: 'donation_fundmaster_contact'}, 
			    	source: function(fundmaster){
			    		var contact = fundmaster.get('contact_id');
			    		if(typeof(contact) === 'object'){
			    			contact = new Tine.Addressbook.Model.Contact(contact);
			    			return contact.getTitle() + ' [# '+contact.get('id')+' ]';
			    		}else{
			    			return '';
			    		}
			    	}
			    }
			]
		}
   	]
});

Tine.Donator.Model.RegularDonation.getDefaultData = function(){
	return {
		reg_donation_amount: 0,
		confirmation_kind:'CONFIRMATION_NO',
		gratuation_kind:'THANK_NO',
		donation_payment_interval:'NOVALUE',
		donation_payment_method:'NOVALUE'
	};
};

Tine.Donator.Model.RegularDonation.getFilterModel = function() {
    var app = Tine.Tinebase.appMgr.get('Donator');
    return [
			{label: _('Quick search'),          field: 'query',       operators: ['contains']},
			{label: app.i18n._('Ausgesetzt'),   field: 'on_hold',  valueType: 'bool'},
	        {label: app.i18n._('Beendet'),   field: 'terminated',  valueType: 'bool'},
	        {label: _('Spendenbetrag'),          field: 'reg_donation_amount',       operators: ['greater','less','equals']},
	        {label: _('Kontrollbetrag'),          field: 'control_sum',       operators: ['greater','less','equals']},
	        {label: _('Kontr.zähler'),          field: 'control_count',       operators: ['greater','less','equals']},
	        {label: _('Ausgetr.Mgl.'),          field: 'terminated_membership',       operators: ['greater','less','equals']}
	        
	        
			//{app: app, filtertype: 'foreignrecord', label: 'Spender', field: 'fundmaster_id', foreignRecordClass: Tine.Donator.Model.FundMaster, ownField:'fundmaster_id'},
			//{app: app, filtertype: 'foreignrecord', label: 'Kampagne', field: 'campaign_id', foreignRecordClass: Tine.Donator.Model.Campaign, ownField:'campaign_id'},
			//{label: _('Spendenbetrag'),          field: 'reg_donation_amount',       operators: ['greater','less','equals']}

        //{label: _('Quick search'),          field: 'query',       operators: ['contains']},
    ];
}


