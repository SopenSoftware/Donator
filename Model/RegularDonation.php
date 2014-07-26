<?php

/**
 * class to hold SoEvent data
 *
 * @package     SoEventManager
 */
class Donator_Model_RegularDonation extends Tinebase_Record_Abstract
{
    /**
     * key in $_validators/$_properties array for the filed which
     * represents the identifier
     *
     * @var string
     */
    protected $_identifier = 'id';
    
    /**
     * application the record belongs to
     *
     * @var string
     */
    protected $_application = 'Donator';
    
    /**
     * list of zend validator
     *
     * this validators get used when validating user generated content with Zend_Input_Filter
     *
     * @var array
     *
     */
    protected $_validators = array(
        'id'                    => array(Zend_Filter_Input::ALLOW_EMPTY => true, Zend_Filter_Input::DEFAULT_VALUE => NULL),
        'regular_donation_nr'                  => array(Zend_Filter_Input::ALLOW_EMPTY => true),
		'fundmaster_id'                  => array(Zend_Filter_Input::ALLOW_EMPTY => true),
		'campaign_id'                  => array(Zend_Filter_Input::ALLOW_EMPTY => true),
		'donation_account_id'                  => array(Zend_Filter_Input::ALLOW_EMPTY => true),
		'erp_proceed_account_id'                  => array(Zend_Filter_Input::ALLOW_EMPTY => true),
    	'last_receipt_id'                  => array(Zend_Filter_Input::ALLOW_EMPTY => true),
    	'last_donation_id'                  => array(Zend_Filter_Input::ALLOW_EMPTY => true),
		'begin_date'                  => array(Zend_Filter_Input::ALLOW_EMPTY => true),
		'last_date'                  => array(Zend_Filter_Input::ALLOW_EMPTY => true),
		'next_date'                  => array(Zend_Filter_Input::ALLOW_EMPTY => true),
		'end_date'                  => array(Zend_Filter_Input::ALLOW_EMPTY => true),
		'reg_donation_amount'                  => array(Zend_Filter_Input::ALLOW_EMPTY => true),
		'gratuation_kind'	=> array(Zend_Filter_Input::ALLOW_EMPTY => true),
    	'confirmation_kind'	=> array(Zend_Filter_Input::ALLOW_EMPTY => true),
    	'donation_payment_interval'	=> array(Zend_Filter_Input::ALLOW_EMPTY => true),
    	'donation_payment_method'	=> array(Zend_Filter_Input::ALLOW_EMPTY => true),
    	'bank_account_nr'	=> array(Zend_Filter_Input::ALLOW_EMPTY => true),
    	'bank_code'	=> array(Zend_Filter_Input::ALLOW_EMPTY => true),
       	'bank_name'	=> array(Zend_Filter_Input::ALLOW_EMPTY => true),
       	'account_name'	=> array(Zend_Filter_Input::ALLOW_EMPTY => true),
       	'on_hold'	=> array(Zend_Filter_Input::ALLOW_EMPTY => true),
       	'terminated'	=> array(Zend_Filter_Input::ALLOW_EMPTY => true),
       	'control_sum'	=> array(Zend_Filter_Input::ALLOW_EMPTY => true),
       	'control_count'	=> array(Zend_Filter_Input::ALLOW_EMPTY => true),
    	'terminated_membership'	=> array(Zend_Filter_Input::ALLOW_EMPTY => true),
    	'sepa_mandate_id'                  => array(Zend_Filter_Input::ALLOW_EMPTY => true),
       	'bank_account_id'                  => array(Zend_Filter_Input::ALLOW_EMPTY => true),
    	'bank_account_usage_id'                  => array(Zend_Filter_Input::ALLOW_EMPTY => true),
    	'bic'                  => array(Zend_Filter_Input::ALLOW_EMPTY => true),
    	'iban'                  => array(Zend_Filter_Input::ALLOW_EMPTY => true),
    	'bank_account_number'                  => array(Zend_Filter_Input::ALLOW_EMPTY => true),
    	'bank_account_name'                  => array(Zend_Filter_Input::ALLOW_EMPTY => true),
       	'bank_account_bank_name'                  => array(Zend_Filter_Input::ALLOW_EMPTY => true),
    	'sepa_mandate_ident'                  => array(Zend_Filter_Input::ALLOW_EMPTY => true),
    	'sepa_signature_date'                  => array(Zend_Filter_Input::ALLOW_EMPTY => true)
    );
    protected $_dateFields = array(
    'begin_date',
    'next_date',
    'end_date'
    );
    
    public function execute(){
    	
    	$beginDate = $this->__get('begin_date');
    	$nextDate = $this->__get('next_date');
    	if(is_null($nextDate)){
    		$nextDate = $beginDate;
    	}
    	$nextDate = new Zend_Date($nextDate);
    	
    	$this->__set('last_date', new Zend_Date());
    	
    	$paymentInterval = $this->__get('donation_payment_interval');
    	
    	switch($paymentInterval){
    		case 'MONTH':
    			$nextDate->add(1, Zend_Date::MONTH);
    			break;
    		case 'QUARTER':
    			$nextDate->add(3, Zend_Date::MONTH);
    			break;
    		case 'HALF':
    			$nextDate->add(6, Zend_Date::MONTH);
    			break;
    		case 'YEAR':
    		default:
    			$nextDate->add(1, Zend_Date::YEAR);
    			break;
    	}
    	
    	$this->__set('next_date', $nextDate);
    	
    	$donation = $this->createDonation();
    	$this->__set('last_donation_id', $donation->getId());
    	Donator_Controller_RegularDonation::getInstance()->update($this);
    	
    	return $donation;
    		
    }
    
    public function createDonation(){
    	$donation = Donator_Controller_Donation::getInstance()->getEmptyDonation();
    	$regDon = clone $this;
    	$regDon->flatten();
    	$aRegDon = $regDon->toArray();
    	
    	$donation->setFromArray($aRegDon);
    	$donation->__set('id', null);
    	$donation->__set('donation_type', 'CYCLE');
    	$donation->__set('donation_amount', $this->__get('reg_donation_amount'));
    	$donation->__set('donation_date', new Zend_Date());
    	
    	$donation = Donator_Controller_Donation::getInstance()->create($donation);
    	
    	return $donation;
    }
    
    public function setFromArray(array $_data)
    {	
		if(empty($_data['next_date']) || $_data['next_date']==""){
			$_data['next_date'] = $_data['begin_date'];
		}
    	if(empty($_data['end_date']) || $_data['end_date']==""){
			$_data['end_date'] = null;
		}
		parent::setFromArray($_data);
    }
    
    protected function _setFromJson(array &$_data)
    {
    	if(empty($_data['next_date']) || $_data['next_date']==""){
			$_data['next_date'] = $_data['begin_date'];
		}
    	if(empty($_data['end_date']) || $_data['end_date']==""){
			$_data['end_date'] = null;
		}
    }
}