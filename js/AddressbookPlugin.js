Ext.ns('Tine.Donator');

Tine.Donator.AddressbookPlugin = Ext.extend(Tine.Tinebase.AppPlugin, {
	pluginName: 'DonatorAddressbookPlugin',
	contactEditDialog: null,
	donatorEditDialog: null,
	
	getEditDialogMainTabs: function(contactEditDialog, navigate){
		this.navigate = navigate;
		this.registerContactEventListeners(contactEditDialog);
		this.contactEditDialog = contactEditDialog;
		this.donatorEditDialog = Tine.Donator.getFundMasterEditRecordAsTab(true);
		return [this.donatorEditDialog];
	},
	
	registerContactEventListeners: function(contactEditDialog){
		contactEditDialog.on('loadcontact',this.onLoadContact,this);
	},
	
	onLoadContact: function(contact){
		if(contact.id != 0){
			this.donatorEditDialog.enable();
			this.donatorEditDialog.onLoadParent(contact);
		}
		return true;
	},
	
	onUpdateContact: function(contact){
		this.donatorEditDialog.save(contact);
		return true;
	},
	
	onCancelContactEditDialog: function(){
		this.unsetMemberEditDialog();
		return true;
	},
	onSaveAndCloseContactDialog: function(){
		this.onSaveContact();
		this.unsetMemberEditDialog();
		return true;
	},
	unsetMemberEditDialog: function(){
		this.donatorEditDialog = null;
	}
});