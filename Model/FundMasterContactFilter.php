<?php
class Donator_Model_FundMasterContactFilter extends Tinebase_Model_Filter_Abstract
{
    protected $_operators = array(
        'contains','equals'
    );
    
    public function appendFilterSql($_select, $_backend){
    	if($this->_value){
	    	$filter = new Donator_Model_FundMasterFilter(array(
	    		
	    	), 'AND');
	    	$contactFilter = new Addressbook_Model_ContactFilter(array(
	            array('field' => 'query',   'operator' => 'contains', 'value' => $this->_value),
	        ));
	        $contactIds = Addressbook_Controller_Contact::getInstance()->search($contactFilter, NULL, FALSE, TRUE);
	        $filter->addFilter(new Tinebase_Model_Filter_Id('contact_id', 'in', $contactIds));
	       	Tinebase_Backend_Sql_Filter_FilterGroup::appendFilters($_select, $filter, $_backend);
    	}
    }
}
?>