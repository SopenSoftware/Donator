<?php

/**
 * class to hold Campaign data
 *
 * @package     Donator
 */
class Donator_Model_Campaign extends Tinebase_Record_Abstract
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
        'project_id'                  => array(Zend_Filter_Input::ALLOW_EMPTY => true),
    	'erp_proceed_account_id'                  => array(Zend_Filter_Input::ALLOW_EMPTY => true),
    	'erp_activity_account_id'                  => array(Zend_Filter_Input::ALLOW_EMPTY => true),
    	'gratuation_template_id'                  => array(Zend_Filter_Input::ALLOW_EMPTY => true),
    	'campaign_nr'                  => array(Zend_Filter_Input::ALLOW_EMPTY => true),
    	'name'                  => array(Zend_Filter_Input::ALLOW_EMPTY => true),
    	'description'                  => array(Zend_Filter_Input::ALLOW_EMPTY => true),
    	'responsible_contact_id'                  => array(Zend_Filter_Input::ALLOW_EMPTY => true),
    	'donation_account_id'                  => array(Zend_Filter_Input::ALLOW_EMPTY => true),
    	'donation_unit_id'                  => array(Zend_Filter_Input::ALLOW_EMPTY => true),
    	'cost_unit'                  => array(Zend_Filter_Input::ALLOW_EMPTY => true),
    	'budget'				=> array(Zend_Filter_Input::ALLOW_EMPTY => true),
    	'begin'                  => array(Zend_Filter_Input::ALLOW_EMPTY => true, Zend_Filter_Input::DEFAULT_VALUE => NULL),
    	'end'                  => array(Zend_Filter_Input::ALLOW_EMPTY => true, Zend_Filter_Input::DEFAULT_VALUE => NULL),
    	'gratuation_kind'                  => array(Zend_Filter_Input::ALLOW_EMPTY => true, Zend_Filter_Input::DEFAULT_VALUE => NULL),
		'debit_account_system_id' 				=> array(Zend_Filter_Input::ALLOW_EMPTY => true),
		'bank_account_system_id' 				=> array(Zend_Filter_Input::ALLOW_EMPTY => true),
		'is_closed'	=> array(Zend_Filter_Input::ALLOW_EMPTY => true)
    );
    protected $_dateFields = array(
    // modlog
    );
    
	public function setFromArray(array $_data)
	{

		if(empty($_data['begin']) || $_data['begin']==""){
			unset($_data['begin']);
		}
		if(empty($_data['end']) || $_data['end']==""){
			unset($_data['end']);
		}
		if(empty($_data['campaign_nr']) || $_data['campaign_nr']==""){
			unset($_data['campaign_nr']);
		}
		if(empty($_data['donation_account_id']) || $_data['donation_account_id']==""){
			unset($_data['donation_account_id']);
		}				
		parent::setFromArray($_data);
	}

	protected function _setFromJson(array &$_data)
	{
		if(empty($_data['begin']) || $_data['begin']==""){
			unset($_data['begin']);
		}
		if(empty($_data['end']) || $_data['end']==""){
			unset($_data['end']);
		}
			if(empty($_data['campaign_nr']) || $_data['campaign_nr']==""){
			unset($_data['campaign_nr']);
		}
		if(empty($_data['donation_account_id']) || $_data['donation_account_id']==""){
			unset($_data['donation_account_id']);
		}	
	}
}