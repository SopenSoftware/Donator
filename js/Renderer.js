Ext.namespace('Tine.Donator');
Ext.namespace('Tine.Donator.renderer');

Tine.Donator.renderer.contactRenderer =  function(_recordData) {
	if(!_recordData){
		return null;
	}
	var _record = new Tine.Addressbook.Model.Contact(_recordData,_recordData.id);
	if(typeof(_record) === 'object' && !Ext.isEmpty(_record)){
		try{
			return _record.getTitle();
		}catch(e){
			return "";
		}
	}
};

Tine.Donator.renderer.projectRenderer =  function(_recordData) {
	if(!_recordData){
		return null;
	}
	var _record = new Tine.Donator.Model.Project(_recordData,_recordData.id);
	if(typeof(_record) === 'object' && !Ext.isEmpty(_record)){
		try{
			return _record.getTitle();
		}catch(e){
			return "";
		}
	}
};


Tine.Donator.renderer.campaignRenderer =  function(_recordData) {
	if(!_recordData){
		return null;
	}
	var _record = new Tine.Donator.Model.Campaign(_recordData,_recordData.id);
	if(typeof(_record) === 'object' && !Ext.isEmpty(_record)){
		try{
			return _record.getTitle();
		}catch(e){
			return "";
		}
	}
};

Tine.Donator.renderer.donationAccountRenderer =  function(_recordData) {
	if(!_recordData){
		return null;
	}
	var _record = new Tine.Donator.Model.DonationAccount(_recordData,_recordData.id);
	if(typeof(_record) === 'object' && !Ext.isEmpty(_record)){
		try{
			return _record.getTitle();
		}catch(e){
			return "";
		}
	}
};

Tine.Donator.renderer.donationUnitRenderer =  function(_recordData) {
	if(!_recordData){
		return null;
	}
	var _record = new Tine.Donator.Model.DonationUnit(_recordData,_recordData.id);
	if(typeof(_record) === 'object' && !Ext.isEmpty(_record)){
		try{
			return _record.getTitle();
		}catch(e){
			return "";
		}
	}
};

Tine.Donator.renderer.gratuationKind =  function(v) {
	switch(v){
	case 'THANK_NO':
		return 'keine';
	case 'THANK_STANDARD':
		return 'standard';
	case 'THANK_INDIVIDUAL':
		return 'individuell';
	}
};


Tine.Donator.renderer.confirmationKind =  function(v) {
	switch(v){
	case 'CONFIRMATION_NO':
		return 'keine';
	case 'CONFIRMATION_SINGLE':
		return 'einzeln';
	case 'CONFIRMATION_COLLECT':
		return 'gesammelt';
	}
};


Tine.Donator.renderer.donationType =  function(v) {
	switch(v){
	case 'SINGLE':
		return 'Einzelspende';
	case 'CYCLE':
		return 'Dauerspende';
	}
};

Tine.Donator.renderer.paymentInterval =  function(v) {
	switch(v){
	case 'NOVALUE':
		return 'keine';
	case 'YEAR':
		return 'jährlich';
	case 'HALF':
		return 'halbjährlich';
	case 'QUARTER':
		return 'quartalsweise';
	case 'MONTH':
		return 'monatlich';		
	}
};

Tine.Donator.renderer.paymentMethod =  function(v) {
	switch(v){
	case 'NOVALUE':
		return 'keine';
	case 'BANKTRANSFER':
		return 'Überweisung';
	case 'DEBIT':
		return 'Lastschrift';
	}
};

