<?php
class Donator_Controller_FundMaster extends Tinebase_Controller_Record_Abstract
{
	/**
	 * config of courses
	 *
	 * @var Zend_Config
	 */
	protected $_config = NULL;

	/**
	 * the constructor
	 *
	 * don't use the constructor. use the singleton
	 */
	private function __construct() {
		$this->_applicationName = 'Donator';
		$this->_backend = new Donator_Backend_FundMaster();
		$this->_modelName = 'Donator_Model_FundMaster';
		$this->_currentAccount = Tinebase_Core::getUser();
		$this->_purgeRecords = FALSE;
		$this->_doContainerACLChecks = FALSE;
		$this->_config = isset(Tinebase_Core::getConfig()->sofundmaster) ? Tinebase_Core::getConfig()->sofundmaster : new Zend_Config(array());
	}

	private static $_instance = NULL;

	/**
	 * the singleton pattern
	 *
	 * @return SoEventManager_Controller_SoEvent
	 */
	public static function getInstance()
	{
		if (self::$_instance === NULL) {
			self::$_instance = new self();
		}

		return self::$_instance;
	}

	public function getEmptyFundMaster(){
		$emptyOrder = new Donator_Model_FundMaster(null,true);
		$emptyOrder->setFromArray(array('bank_account_nr' => ' '));
		return $emptyOrder;
	}
	
	public function getByContactOrCreate($contactId){

		try{
			return $this->getByContactId($contactId);
		}catch(Exception $e){
			return $this->createFromContact($contactId);
		}
	}
	
	public function getIdsAsArrayByContactId($contactId){
		
		$filter = new Donator_Model_FundMasterFilter(array(), 'AND');
		$filter->addFilter(new Tinebase_Model_Filter_Id('contact_id', 'equals',$contactId));
		
		$paging = new Tinebase_Model_Pagination(array('sort'=>'n_family', 'dir' => 'ASC'));
		return $this->search($filter, $paging, false, true);
		
		
	}
	
	public function createFromContact($contactId){
		$fm = $this->getEmptyFundMaster();
		$contact = Addressbook_Controller_Contact::getInstance()->get($contactId);
		$fm->__set('contact_id', $contactId);
		$fm->__set('adr_one_street', $contact->__get('adr_one_street'));
		$fm->__set('adr_one_postalcode', $contact->__get('adr_one_postalcode'));
		$fm->__set('adr_one_locality', $contact->__get('adr_one_locality'));
		$fm->__set('confirmation_kind','CONFIRMATION_SINGLE');
		$fm->__set('gratuation_kind','THANK_NO');
		$fm->__set('donation_payment_interval','NOVALUE');
		$fm->__set('donation_payment_method','NOVALUE');
		return $this->create($fm);
	}

	/**
	 * (non-PHPdoc)
	 * @see release/sopen 1.1/main/app/core/vendor/tine/v/2/base/Tinebase/Controller/Record/Tinebase_Controller_Record_Abstract::_inspectCreate()
	 */
	protected function _inspectCreate(Tinebase_Record_Interface $_record)
	{
		$contactId = $_record->__get('contact_id');
		
		if(is_array($contactId)){
			$_record->__set('contact_id',$contactId['id']);
		}
		
		$campaignId = $_record->__get('first_contact_campaign_id');
		 
		if(is_array($campaignId)){
			$_record->__set('first_contact_campaign_id',$campaignId['id']);
		}
	}

	/**
	 * (non-PHPdoc)
	 * @see release/sopen 1.1/main/app/core/vendor/tine/v/2/base/Tinebase/Controller/Record/Tinebase_Controller_Record_Abstract::_inspectUpdate()
	 */
	protected function _inspectUpdate($_record, $_oldRecord)
	{
		$contactId = $_record->__get('contact_id');
		 
		if(is_array($contactId)){
			$_record->__set('contact_id',$contactId['id']);
		}
		
		$campaignId = $_record->__get('first_contact_campaign_id');
		 
		if(is_array($campaignId)){
			$_record->__set('first_contact_campaign_id',$campaignId['id']);
		}
	}
	
	public function onSetAccountBankTransferDetected($objEvent){

		$bankAccount = $objEvent->getBankAccount();
		$contactId = $objEvent->getDebitor()->getForeignId('contact_id');
		
		$fundMasters = $this->_backend->getMultipleByProperty($contactId, 'contact_id');
		
		foreach($fundMasters as $fundMaster){
			$fBankAccount = Billing_Api_BankAccount::getFromFundMaster($fundMaster);
			if($fBankAccount->equals($bankAccount)){
				$fundMaster->__set('donation_payment_method', 'BANKTRANSFER');
				Donator_Controller_FundMaster::getInstance()->update($regDon);
			}
			
			$fId = $fundMaster->getId();
			$regDons = Donator_Controller_RegularDonation::getInstance()->getByFundMasterId($fId);
			foreach($regDons as $regDon){
				$rBankAccount = Billing_Api_BankAccount::getFromRegularDonation($regDon);
				if($rBankAccount->equals($bankAccount)){
					$regDon->__set('donation_payment_method', 'BANKTRANSFER');
					Donator_Controller_RegularDonation::getInstance()->update($regDon);
				}
			}
		}
		
	}
	
	/**
	 * 
	 * Get the fund master record by it's contact id
	 * @param int $contactId
	 */
	public function getByContactId($contactId){
		//return $this->_backend->getByProperty($contactId, 'contact_id');
		return $this->_backend->getByContactId($contactId);
	}
	
	public function getAllByContactIdBreakNull($contactId){
		try{
			return $this->_backend->getByPropertySet(
	    		array(
	    			'contact_id' => $contactId
	    		),
	    		false,		// no deleted records
	    		false		// recordset not just single record
	    	);
		}catch(Exception $e){
			return null;
		}
	}
	
	/**
	 * 
	 * Enter description here ...
	 * @param Tinebase_Record_Interface $_record
	 */
    public function inspectDeleteRecord(Tinebase_Record_Interface $_record){
    	
    }

    /**
     * 
     * helper for decision: is record deletable?
     * tobe solved in customizing (alternative algorithm of allow deleting when having foreign records)
     * @param Tinebase_Record_Interface $_record
     */
    public function isDeletable(Tinebase_Record_Interface $_record){
    	$recordSet = Donator_Controller_Donation::getInstance()->getByFundMasterId($_record->getId());
    	if($recordSet->count()>0){
    		return false;
    	}
    	return true;
    }
    
    public function donateByContactId($contactId, $amount, $campaignId, $date, $usage, $payment = null, $bookingAllowed = true){
    	
    	$fundMaster = $this->getByContactOrCreate($contactId);
    	$campaign = Donator_Controller_Campaign::getInstance()->get($campaignId);
    	$donation = Donator_Controller_Donation::getInstance()->getEmptyDonation();
    	$units = Donator_Controller_DonationUnit::getInstance()->getAllUnits();
    	$unit = $units->getFirstRecord();
    	$donation->setFromCampaign($campaign);
    	$donation->__set('fundmaster_id', $fundMaster->getId());
    	$donation->__set('campaign_id', $campaignId);
    	
    	// if payment is created first
    	if($payment){
    		$donation->__set('payment_id', $payment->getId());
    	}
    	$donation->__set('donation_date', new Zend_Date($date));
    	//$donation->__set('unit_id', $unit->getId());
    	$donation->__set('campaign_id', $campaignId);
    	$donation->__set('donation_amount', $amount);
    	$donation->__set('donation_usage', $usage);
    	$donation->__set('confirmation_kind', $fundMaster->__get('confirmation_kind'));
    	
    	if(!$bookingAllowed){
    		$donation->prohibitBooking();
    	}
    	
    	return Donator_Controller_Donation::getInstance()->create($donation);
    }
}
?>