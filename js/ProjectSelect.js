/**
 * Sopen 1.0
 * projects combo box and store
 * 
 * @package     Donator
 * @license     http://www.gnu.org/licenses/gpl.html GPL Version 3
 * @author      Hans-J�rgen Hartl
 * @copyright   Copyright (c) 2009,2010 sopen GmbH
 *
 */


Ext.namespace('Tine.Donator');

Tine.Donator.ProjectSelect = Ext.extend(Tine.Tinebase.widgets.form.RecordPickerComboBox, 
{
	recordClass: Tine.Donator.Model.Project
});
Ext.reg('fundprojectselect', Tine.Donator.ProjectSelect);