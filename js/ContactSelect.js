/**
 * Sopen 1.0
 * contacts combo box and store
 * 
 * @package     Donator
 * @license     http://www.gnu.org/licenses/gpl.html GPL Version 3
 * @author      Hans-J�rgen Hartl
 * @copyright   Copyright (c) 2009,2010 sopen GmbH
 *
 */


Ext.namespace('Tine.Donator');

Tine.Donator.ContactSelect = Ext.extend(Tine.Addressbook.SearchCombo, 
{
	contactFields: Tine.Addressbook.Model.ContactArray.concat({name: 'displaytitle'}),
	displayField: 'displaytitle',
	valueField: 'id',
	getValue: function() {
    	return this.selectedRecord ? this.selectedRecord.get(this.valueField) : null;
	},
	setValue: function(obj){
		if(obj==null){
			Tine.Donator.ContactSelect.superclass.setValue.call(this, null);
			return;
		}
		var varType = typeof(obj);
		switch(varType){
		case 'object':
			obj.displaytitle = null;
			var contact = new Tine.Addressbook.Model.Contact(obj,obj.id);
			contact.set('displaytitle',contact.getTitle());
			this.selectedRecord = contact;
			Tine.Donator.ContactSelect.superclass.setValue.call(this, contact.getTitle());
			break;
		case 'string':
			Tine.Donator.ContactSelect.superclass.setValue.call(this, obj);
			break;
		}
	}
});
Ext.reg('fundcontactselect', Tine.Donator.ContactSelect);