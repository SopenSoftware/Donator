<?php

/**
 * class to hold SoEvent data
 *
 * @package     SoEventManager
 */
class Donator_Model_FundMaster extends Tinebase_Record_Abstract
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
        'contact_id'                  => array(Zend_Filter_Input::ALLOW_EMPTY => true),
		'donation_affinity_seasonal'                  => array(Zend_Filter_Input::ALLOW_EMPTY => true),
		'donation_affinity_thematic'                  => array(Zend_Filter_Input::ALLOW_EMPTY => true),
		'donation_affinity_regional'                  => array(Zend_Filter_Input::ALLOW_EMPTY => true),
		'donation_affinity_spec_events'                  => array(Zend_Filter_Input::ALLOW_EMPTY => true),
		'donator_affiliate'                  => array(Zend_Filter_Input::ALLOW_EMPTY => true),
		'first_contact'                  => array(Zend_Filter_Input::ALLOW_EMPTY => true, Zend_Filter_Input::DEFAULT_VALUE => NULL),
		'first_contact_campaign_id'                  => array(Zend_Filter_Input::ALLOW_EMPTY => true),
		'reg_donation_amount'                  => array(Zend_Filter_Input::ALLOW_EMPTY => true),
		'reg_donation_account_nr'                  => array(Zend_Filter_Input::ALLOW_EMPTY => true),
		'gratuation_kind'                  => array(Zend_Filter_Input::ALLOW_EMPTY => true),
		'confirmation_kind'                  => array(Zend_Filter_Input::ALLOW_EMPTY => true),
		'donation_payment_interval'                  => array(Zend_Filter_Input::ALLOW_EMPTY => true),
		'donation_payment_method'                  => array(Zend_Filter_Input::ALLOW_EMPTY => true),
		'is_fm_hidden'	=> array(Zend_Filter_Input::ALLOW_EMPTY => true),
    	'adr_one_postalcode'	=> array(Zend_Filter_Input::ALLOW_EMPTY => true),
    	'adr_one_locality'	=> array(Zend_Filter_Input::ALLOW_EMPTY => true),
    	'adr_one_street'	=> array(Zend_Filter_Input::ALLOW_EMPTY => true),
    	'bank_account_nr'	=> array(Zend_Filter_Input::ALLOW_EMPTY => true),
    	'bank_code'	=> array(Zend_Filter_Input::ALLOW_EMPTY => true),
       	'bank_name'	=> array(Zend_Filter_Input::ALLOW_EMPTY => true),
       	'account_name'	=> array(Zend_Filter_Input::ALLOW_EMPTY => true)/*,
    	'sepa_mandate_id'                  => array(Zend_Filter_Input::ALLOW_EMPTY => true),
       	'bank_account_id'                  => array(Zend_Filter_Input::ALLOW_EMPTY => true),
    	'bank_account_usage_id'                  => array(Zend_Filter_Input::ALLOW_EMPTY => true),
    	'bic'                  => array(Zend_Filter_Input::ALLOW_EMPTY => true),
    	'iban'                  => array(Zend_Filter_Input::ALLOW_EMPTY => true),
    	'bank_account_number'                  => array(Zend_Filter_Input::ALLOW_EMPTY => true),
    	'bank_account_name'                  => array(Zend_Filter_Input::ALLOW_EMPTY => true),
       	'bank_account_bank_name'                  => array(Zend_Filter_Input::ALLOW_EMPTY => true),
    	'sepa_mandate_ident'                  => array(Zend_Filter_Input::ALLOW_EMPTY => true),
    	'sepa_signature_date'                  => array(Zend_Filter_Input::ALLOW_EMPTY => true)*/
    );
    protected $_dateFields = array(
    // modlog
    );
    
    public function setFromArray(array $_data)
    {	

    	if(empty($_data['first_contact']) || $_data['first_contact']==""){
    		unset($_data['first_contact']);
    	}
    	
    	if(empty($_data['bank_account_nr']) || $_data['bank_account_nr']==""){
    		$_data['bank_account_nr'] = ' ';
    	}
parent::setFromArray($_data);
        

    }
    
     protected function _setFromJson(array &$_data)
    {
        if(empty($_data['first_contact']) || $_data['first_contact']==""){
    		unset($_data['first_contact']);
    	}
    if(empty($_data['bank_account_nr']) || $_data['bank_account_nr']==""){
    		$_data['bank_account_nr'] = ' ';
    	}
    }
}