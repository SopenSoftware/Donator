<?php
class Donator_Model_RegularDonationFilter extends Tinebase_Model_Filter_FilterGroup implements Tinebase_Model_Filter_AclFilter
{
    /**
     * @var string application of this filter group
     */
    protected $_applicationName = 'Donator';
    
    protected $_className = 'Donator_Model_RegularDonationFilter';
    
    /**
     * @var array filter model fieldName => definition
     */
    protected $_filterModel = array(
    	'id'          => array('filter' => 'Tinebase_Model_Filter_Id'),
    	'fundmaster_id' => array('filter' => 'Tinebase_Model_Filter_ForeignId', 
	        'options' => array(
	                'filtergroup'       => 'Donator_Model_FundMasterFilter', 
	                'controller'        => 'Donator_Controller_FundMaster'
	         )
        ),
        'query'          => array('filter' => 'Tinebase_Model_Filter_Query', 'options' => array('fields' => array('bank_account_nr','regular_donation_nr'))),
    	'reg_donation_amount' => array('filter' => 'Tinebase_Model_Filter_Int'),
        'next_date' => array('filter' => 'Tinebase_Model_Filter_Date'),
        'on_hold' => array('filter' => 'Tinebase_Model_Filter_Bool'),
        'terminated' => array('filter' => 'Tinebase_Model_Filter_Bool'),
        'control_count' => array('filter' => 'Tinebase_Model_Filter_Int'),
        'control_sum' => array('filter' => 'Tinebase_Model_Filter_Int'),
        'terminated_membership' => array('filter' => 'Tinebase_Model_Filter_Int')
    );
}
?>