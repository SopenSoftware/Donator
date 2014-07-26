/**
 * Sopen 1.0
 * campaigns combo box and store
 * 
 * @package     Donator
 * @license     http://www.gnu.org/licenses/gpl.html GPL Version 3
 * @author      Hans-J�rgen Hartl
 * @copyright   Copyright (c) 2009,2010 sopen GmbH
 *
 */


Ext.namespace('Tine.Donator');

Tine.Donator.CampaignSelect = Ext.extend(Tine.Tinebase.widgets.form.RecordPickerComboBox, 
{
	initComponent: function(){
		this.recordClass = Tine.Donator.Model.Campaign;
		Tine.Donator.CampaignSelect.superclass.initComponent.call(this);
	}
});
Ext.reg('fundcampaignselect', Tine.Donator.CampaignSelect);