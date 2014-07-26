<?php
class Donator_Model_DonationFilter extends Tinebase_Model_Filter_FilterGroup// implements Tinebase_Model_Filter_AclFilter
{
    /**
     * @var string application of this filter group
     */
    protected $_applicationName = 'Donator';
    
    protected $_className = 'Donator_Model_DonationFilter';
    
    /**
     * @var array filter model fieldName => definition
     */
    protected $_filterModel = array(
     	'id' => array('filter' => 'Tinebase_Model_Filter_Id'),
   		'query'          => array('filter' => 'Tinebase_Model_Filter_Query', 'options' => array('fields' => array('donation_nr'))),
    	'donation_amount' => array('filter' => 'Tinebase_Model_Filter_Int'),
    	'donation_date' => array('filter' => 'Tinebase_Model_Filter_Date'),
        'fundmaster_id' => array('filter' => 'Tinebase_Model_Filter_ForeignId', 
    	'options' => array(
                'filtergroup'       => 'Donator_Model_FundMasterFilter', 
                'controller'        => 'Donator_Controller_FundMaster'
            )
        ),
        'campaign_id' => array('filter' => 'Tinebase_Model_Filter_ForeignId', 
        'options' => array(
                'filtergroup'       => 'Donator_Model_CampaignFilter', 
                'controller'        => 'Donator_Controller_Campaign'
            )
        ),
        'contact_id' => array('filter' => 'Donator_Model_DonationContactFilter',
        'options' => array(
                'filtergroup_p1'       => 'Addressbook_Model_ContactFilter', 
        		'filtergroup'       => 'Donator_Model_FundMasterFilter', 
        	    'controller_p1'     => 'Addressbook_Controller_Contact',
        		'controller'		=> 'Donator_Controller_FundMaster'
            )
        ),
        'booking_id' => array('filter' => 'Tinebase_Model_Filter_ForeignId',
            'options' => array(
                'filtergroup'       => 'Billing_Model_BookingFilter', 
                'controller'        => 'Billing_Controller_Booking'
            )
        ),
        'payment_id' => array('filter' => 'Tinebase_Model_Filter_ForeignId',
            'options' => array(
                'filtergroup'       => 'Billing_Model_PaymentFilter', 
                'controller'        => 'Billing_Controller_Payment'
            )
        ),
        'gratuation_kind' => array('filter'=>'Tinebase_Model_Filter_Text'),
        'confirmation_kind' => array('filter'=>'Tinebase_Model_Filter_Text'),
        'showHidden' => array('custom' => true),
        'donation_type' => array('filter'=>'Tinebase_Model_Filter_Text'),
        'is_cancelled' => array('filter'=>'Tinebase_Model_Filter_Bool'),
        'is_cancellation' => array('filter'=>'Tinebase_Model_Filter_Bool'),
		'confirmation_date' => array('filter' => 'Tinebase_Model_Filter_Date'),
		'thanks_date' => array('filter' => 'Tinebase_Model_Filter_Date'),
        'is_member'		  => array('filter' => 'Tinebase_Model_Filter_Bool'),
		'period'		  => array('filter' => 'Tinebase_Model_Filter_Int'),
		'fee_group_id'		  => array('filter' => 'Tinebase_Model_Filter_Text')
    );
    
    /**
     * set options
     *
     * @param array $_options
     */
    protected function _setOptions(array $_options)
    {
        $_options['showHidden']         = array_key_exists('showHidden', $_options)      ? $_options['showHidden']      : FALSE;
        parent::_setOptions($_options);
    }
    
/**
     * appends custom filters to a given select object
     * 
     * @param  Zend_Db_Select                    $_select
     * @param  Tinebase_Backend_Sql_Abstract     $_backend
     * @return void
     */
    public function appendFilterSql($_select, $_backend)
    {
        // manage show hidden
        $this->_appendShowHiddenSql($_select);
        
    }
    
    /**
     * append show hidden filter
     *
     * @param Zend_Db_Select $_select
     */
    protected function _appendShowHiddenSql($_select)
    {
        $showHidden = false;
        foreach ($this->_customData as $customData) {
            if ($customData['field'] == 'showHidden' && $customData['value'] == true) {
                $showHidden = true;
            }
        }
        if($showHidden || $this->_options['showHidden']){
            // nothing to filter
        } else {
            $_select->where(Tinebase_Core::getDb()->quoteIdentifier('is_hidden') . ' = 0');
        }
    }
}
?>