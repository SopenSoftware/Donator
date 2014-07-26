<?php

/**
 * class to hold Donation data
 *
 * @package     Donator
 */
class Donator_Model_Donation extends Tinebase_Record_Abstract
{
	const CYCLE = 'CYCLE';
	const SINGLE = 'SINGLE';
	
	const CONFIRMATION_NO = 'CONFIMRATION_NO';
	const CONFIRMATION_SINGLE = 'CONFIRMATION_SINGLE';
	const CONFIRMATION_COLLECT = 'CONFIRMATION_COLLECT';
	
	const THANK_NO = 'THANK_NO';
	const THANK_STANDARD = 'THANK_STANDARD';
	const THANK_INDIVIDUAL = 'THANK_INDIVIDUAL';
	
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
        'fundmaster_id'                  => array(Zend_Filter_Input::ALLOW_EMPTY => true),
    	'related_donation_id'                  => array(Zend_Filter_Input::ALLOW_EMPTY => true),
    	'campaign_id'                  => array(Zend_Filter_Input::ALLOW_EMPTY => true),
    	'donation_nr'                  => array(Zend_Filter_Input::ALLOW_EMPTY => true),
        'member_nr'                  => array(Zend_Filter_Input::ALLOW_EMPTY => true),
    	'donation_date'                  => array(Zend_Filter_Input::ALLOW_EMPTY => true, Zend_Filter_Input::DEFAULT_VALUE => NULL),
    	'thanks_date'                  => array(Zend_Filter_Input::ALLOW_EMPTY => true, Zend_Filter_Input::DEFAULT_VALUE => NULL),
    	'confirmation_date'				=> array(Zend_Filter_Input::ALLOW_EMPTY => true, Zend_Filter_Input::DEFAULT_VALUE => NULL),
    	'gratuation_kind'                  => array(Zend_Filter_Input::ALLOW_EMPTY => true),
    	'donation_account_id'                  => array(Zend_Filter_Input::ALLOW_EMPTY => true),
		'erp_proceed_account_id'                  => array(Zend_Filter_Input::ALLOW_EMPTY => true),
    	'donation_amount'                  => array(Zend_Filter_Input::ALLOW_EMPTY => true),
    	'donation_usage'                  => array(Zend_Filter_Input::ALLOW_EMPTY => true),
    	'confirmation_kind'                  => array(Zend_Filter_Input::ALLOW_EMPTY => true),
		'non_monetary'                  => array(Zend_Filter_Input::ALLOW_EMPTY => true),
		'non_monetary_source'                  => array(Zend_Filter_Input::ALLOW_EMPTY => true),
		'non_monetary_rating'                  => array(Zend_Filter_Input::ALLOW_EMPTY => true),
		'refund_quitclaim'                  => array(Zend_Filter_Input::ALLOW_EMPTY => true),
		'confirm_nr' 				=> array(Zend_Filter_Input::ALLOW_EMPTY => true),
		
		'booking_id' 				=> array(Zend_Filter_Input::ALLOW_EMPTY => true),
		'payment_id' 				=> array(Zend_Filter_Input::ALLOW_EMPTY => true),
		'non_monetary_description'	=> array(Zend_Filter_Input::ALLOW_EMPTY => true),
		'donation_type'	=> array(Zend_Filter_Input::ALLOW_EMPTY => true),
		'is_hidden'	=> array(Zend_Filter_Input::ALLOW_EMPTY => true),
		'is_cancelled'	=> array(Zend_Filter_Input::ALLOW_EMPTY => true),
		'is_cancellation'	=> array(Zend_Filter_Input::ALLOW_EMPTY => true),
		'contact_id'	=> array(Zend_Filter_Input::ALLOW_EMPTY => true),
		'n_family'	=> array(Zend_Filter_Input::ALLOW_EMPTY => true),
    	'org_name'	=> array(Zend_Filter_Input::ALLOW_EMPTY => true),
		'adr_one_postalcode'	=> array(Zend_Filter_Input::ALLOW_EMPTY => true),
    	'adr_one_locality'	=> array(Zend_Filter_Input::ALLOW_EMPTY => true),
    	'adr_one_street'	=> array(Zend_Filter_Input::ALLOW_EMPTY => true),
    	'allow_booking'          => array(Zend_Filter_Input::ALLOW_EMPTY => true),
		'is_member'	=> array(Zend_Filter_Input::ALLOW_EMPTY => true),
		'period'	=> array(Zend_Filter_Input::ALLOW_EMPTY => true),
		'fee_group_id'	=> array(Zend_Filter_Input::ALLOW_EMPTY => true)
	);
	protected $_datetimeFields = array(
	// modlog
	//'donation_date',
	//'confirmation_date'
	);
	
		public function setFromArray(array $_data)
	{
		if(empty($_data['donation_nr']) || $_data['donation_nr']==""){
			unset($_data['donation_nr']);
		}
		if(empty($_data['thanks_date']) || $_data['thanks_date']==""){
			unset($_data['thanks_date']);
		}
		if(empty($_data['donation_date']) || $_data['donation_date']==""){
			//unset($_data['donation_date']);
			$_data['donation_date'] = null;
		}
		if(empty($_data['confirmation_date']) || $_data['confirmation_date']==""){
			$_data['confirmation_date'] = null;
		}
		
		if(empty($_data['thanks_date']) || $_data['thanks_date']==""){
			$_data['thanks_date'] = null;
		}
		
		if(empty($_data['donation_account']) || $_data['donation_account']==""){
			unset($_data['donation_account']);
		}	
		if(empty($_data['booking_id']) || $_data['booking_id']==""){
			unset($_data['booking_id']);
		}		
		parent::setFromArray($_data);


	}

	protected function _setFromJson(array &$_data)
	{
		if(empty($_data['donation_nr']) || $_data['donation_nr']==""){
			unset($_data['donation_nr']);
		}		
		if(empty($_data['thanks_date']) || $_data['thanks_date']==""){
			unset($_data['thanks_date']);
		}
		if(empty($_data['donation_date']) || $_data['donation_date']==""){
			$_data['donation_date'] = null;
			//unset($_data['donation_date']);
		}
		if(empty($_data['confirmation_date']) || $_data['confirmation_date']==""){
			$_data['confirmation_date'] = null;
		}
		
		if(empty($_data['thanks_date']) || $_data['thanks_date']==""){
			$_data['thanks_date'] = null;
		}
		
		if(empty($_data['donation_account']) || $_data['donation_account']==""){
			unset($_data['donation_account']);
		}		
		if(empty($_data['booking_id']) || $_data['booking_id']==""){
			unset($_data['booking_id']);
		}
	}
	
	public function allowBooking(){
    	$this->__set('allow_booking', 1);
    	return $this;
    }
    
	public function prohibitBooking(){
    	$this->__set('allow_booking', 0);
    	return $this;
    }
    
    public function isBookingAllowed(){
    	return $this->__get('allow_booking') == 1;
    }
    
	public function isCancelled(){
    	return $this->__get('is_cancelled') == 1;
    }
    
	public function isCancellation(){
    	return $this->__get('is_cancellation') == 1;
    }
	
	public function setFromCampaign(Donator_Model_Campaign $campaign){
		$aCampaign = $campaign->toArray();
		unset($aCampaign['id']);
		$this->setFromArray($aCampaign);
	}
	
	public function isSingle(){
		return $this->__get('donation_type') == 'SINGLE';
	}
	
	public function isCycle(){
		return $this->__get('donation_type') == 'CYCLE';
	}
	
	public function getDebitorId(){
		$fundMaster = $this->getForeignRecord('fundmaster_id', Donator_Controller_FundMaster::getInstance());
    	$contactId = $fundMaster->getForeignId('contact_id');
    	$debitor = Billing_Controller_Debitor::getInstance()->getByContactOrCreate($contactId);
    	$debitorId = $debitor->getId();
    	return $debitorId;
	}
	
	public function getBookingData($fibuKto=null, $debitorId = null){
		
    	$value = abs($this->__get('donation_amount'));
    	if(is_null($debitorId)){
	    	$debitorId = $this->getDebitorId();
    	}
    	if(is_null($fibuKto)){
    		$fibuKto = Tinebase_Core::getPreference('Billing')->getValue(Billing_Preference::FIBU_KTO_DEBITOR);
    	}
    	
    	$erloesKto = $this->getForeignId('erp_proceed_account_id'); 
    	
    	if(!$this->__get('is_cancellation')){
    		return array(
				'credits' => array(
    				array( 'kto' => $erloesKto, 'value' => $value, 'debitor' => $debitorId)
    			),
				'debits' => array(
					array( 'kto' => $fibuKto,  'value' => $value, 'debitor' => $debitorId)
				)
			);
    	}else{
    		return array(
				'debits' => array(
    				array( 'kto' => $erloesKto, 'value' => $value, 'debitor' => $debitorId)
    			),
				'credits' => array(
					array( 'kto' => $fibuKto,  'value' => $value, 'debitor' => $debitorId)
				)
			);
    	}
     	
    }
    
    public static function createFromPayment($payment, $donationData){
    	
    }
    
	public function tellGratuationKind(){
		switch($this->__get('gratuation_kind')){
			case self::THANK_NO:
				return 'keine';
			case self::THANK_STANDARD:
				return 'stand.';
			case self::THANK_INDIVIDUAL:
				return 'indiv.';
		}
	}
	
	public function tellConfirmationKind(){
		switch($this->__get('confirmation_kind')){
			case self::CONFIRMATION_NO:
				return 'keine';
			case self::CONFIRMATION_SINGLE:
				return 'einzeln';
			case self::CONFIRMATION_COLLECT:
				return 'gesammelt';
		}
	}
	
	public function tellType(){
		switch($this->__get('donation_type')){
			case self::CYCLE:
				return 'Dauerspende';
			case self::SINGLE:
				return 'Einzelspende';
		}
	}
}