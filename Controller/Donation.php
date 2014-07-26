<?php
//use org\sopen\app\util\arrays;

class Donator_Controller_Donation extends Tinebase_Controller_Record_Abstract
{
	/**
	 * config of courses
	 *
	 * @var Zend_Config
	 */
	protected $_config = NULL;
	private $autoPay = true;
	private $autoBook = true;
	/**
	 * the constructor
	 *
	 * don't use the constructor. use the singleton
	 */
	private function __construct() {
		$this->_applicationName = 'Donator';
		$this->_backend = new Donator_Backend_Donation();
		$this->_modelName = 'Donator_Model_Donation';
		$this->_currentAccount = Tinebase_Core::getUser();
		$this->_purgeRecords = FALSE;
		$this->_doContainerACLChecks = FALSE;
		$this->_config = isset(Tinebase_Core::getConfig()->somembers) ? Tinebase_Core::getConfig()->somembers : new Zend_Config(array());
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

	public function countDonationsWithinTimeframe(Zend_Date $beginDate, Zend_Date $endDate = null, $fundMasterId = null){
    	//return $this->_backend->countDonationsWithinTimeframe($beginDate, $endDate, $fundMasterId);
    	$beginDate = $beginDate->toString('yyyy-MM-dd');
    	
    	$filters = array();
    	$filters[] = array(
    		'field' => 'donation_date',
    		'operator' => 'afterOrAt',
    		'value' => $beginDate
    	);
    	
    	
   		/*$filter->addFilter(new Tinebase_Model_Filter_Date(
    		'donation_date',
    		'afterOrAt',
    		$beginDate
    	));*/
    	if($endDate){
    		$endDate = $endDate->toString('yyyy-MM-dd');
    		
    		$filters[] = array(
	    		'field' => 'donation_date',
	    		'operator' => 'beforeOrAt',
	    		'value' => $endDate
	    	);
    		
	    	/*$filter->addFilter(new Tinebase_Model_Filter_Date(
	    		'donation_date',
	    		'beforeOrAt',
	    		$endDate
	    	));*/
    	}
    	
    	$filters[] = array(
	    		'field' => 'is_cancelled',
	    		'operator' => 'equals',
	    		'value' => 0
	    	);
	    $filters[] = array(
	    		'field' => 'is_cancellation',
	    		'operator' => 'equals',
	    		'value' => 0
	    	);
	    
    	if(!is_null($fundMasterId)){
    		//$filter->addFilter(new Tinebase_Model_Filter_ForeignId('fundmaster_id', 'equals', $fundMasterId));
    		$filters[] = array(
	    		'field' => 'fundmaster_id',
	    		'operator' => 'AND',
	    		'value' => array(array(
    				'field' => 'id',
    				'operator' => 'equals',
    				'value' => $fundMasterId
    			))
	    	);
    		
    	}
    	$filter = new Donator_Model_DonationFilter($filters,'AND');
    	return $this->searchCount($filter);
	}
	
	public function getEmptyDonation(){
		$emptyOrder = new Donator_Model_Donation(null,true);
		return $emptyOrder;
	}
	
	public function setAutoPay($flag){
		$this->autoPay = $flag;
	}
	
	public function setAutoBook($flag){
		$this->autoBook = $flag;
	}
	
	public function createFromReceipt(Billing_Model_Receipt $receipt){
		
		$donationData = $receipt->getCreateDonationData();
		
		if(count($donationData)>0){
			
			$debitor = $receipt->getDebitor();
			$contactId = $debitor->getForeignId('contact_id');
				
			foreach($donationData as $campaignId => $donationAmount){
				
				Donator_Controller_FundMaster::getInstance()->donateByContactId($contactId, $donationAmount, $campaignId, $receipt->__get('invoice_date'), 'Spende zu Rechnung ' .$receipt->__get('invoice_nr'),  null, false);
						
			}
		}
		
	}
	
	public function payDonation($donation, $payment = null, $reverse = false, $context = 'DONATOR'){
		
		if($donation->isCycle() || !$donation->isBookingAllowed()){
			return ;
		}
		$fundMaster = $donation->getForeignRecordBreakNull('fundmaster_id', Donator_Controller_FundMaster::getInstance());
		$contact = $fundMaster->getForeignRecordBreakNull('contact_id', Addressbook_Controller_Contact::getInstance());
		$debitor = Billing_Controller_Debitor::getInstance()->getByContactOrCreate($contact->getId());
		
		$payment = $donation->getForeignRecordBreakNull('payment_id', Billing_Controller_Payment::getInstance());
		$create = false;
		if(is_null($payment)){
			$create = true;
			$payment = Billing_Controller_Payment::getInstance()->getEmptyPayment();
		}
		$payment->__set('erp_context_id', $context);
		$payment->__set('debitor_id', $debitor->getId());
		
		if(!$payment->__get('account_system_id')){
			$donationBankAccount = $donation->getForeignRecordBreakNull('donation_account_id');
			if(!$donationBankAccount){
				$defaultBankAccount = Billing_Controller_AccountSystem::getInstance()->getDefaultBankAccount();
				$bankAccountId = $defaultBankAccount->getId();
			}else{
				$bankAccountId = $donationBankAccount->getForeignId('bank_account_system_id');
			}
			$payment->__set('account_system_id', $bankAccountId);
		}
		$campaign = $donation->getForeignRecord('campaign_id', Donator_Controller_Campaign::getInstance());
		$usage = 'Spende Adr.nr '.$contactId.' '.$campaign->__get('name');
		
		$fordKto = Tinebase_Core::getPreference('Billing')->getValue(Billing_Preference::FIBU_KTO_DEBITOR);
		$payment->__set('account_system_id_haben', $fordKto);
		
		$payment->__set('payment_date', $donation->__get('donation_date'));
		$payment->__set('payment_type', 'PAYMENT');
		
		$payment->__set('usage', $usage.' '.$donation->__get('donation_usage'));
		$payment->__set('object_id', $donation->getId());
		$payment->__set('donation_id', $donation->getId());
		$payment->__set('is_cancelled', $donation->__get('is_cancelled'));
		$payment->__set('is_cancellation', $donation->__get('is_cancellation'));
		$payment->__set('type','DEBIT');
		$payment->__set('amount', abs($donation->__get('donation_amount')));
		if($payment->__get('is_cancellation')){
			$payment->swapAccounts();
			$payment->__set('type','CREDIT');
			$payment->__set('amount', abs($donation->__get('donation_amount')));
		}
			
		if($create){
			$payment = Billing_Controller_Payment::getInstance()->create($payment);
		}else{
			Billing_Controller_Payment::getInstance()->update($payment);
		}
		$donation->__set('payment_id', $payment->getId());
		$this->update($donation);
	}
	
	public function reverseDonation($donationId){
		$donation = $this->get($donationId);
		
		$revDon = clone $donation;
		$revDon->__set('id',null);
		$revDon->__set('payment_id', null);
		$revDon->__set('booking_id', null);
		$revDon->__set('donation_amount', (-1) * $revDon->__get('donation_amount'));
		$revDon->__set('is_cancellation',true);
		$revDon->__set('related_donation_id',$donation->getId());
		$revDon->__set('donation_usage', 'STORNO #Spenden-Nr:'.$donation->__get('donation_nr'). " ". $donation->__get('donation_usage'));
		
		$revDon = $this->create($revDon);
		
		$donation->__set('is_cancelled',true);
		$donation->__set('related_donation_id',$revDon->getId());
		$donation->__set('donation_usage', 'STORNIERT '.$donation->__get('donation_usage'));
		
		$donation = $this->update($donation);
		
		Tinebase_Event::fireEvent(new Donator_Events_DonationReverted($donation, $revDon));
		return $donation;
	}
	
	/*public function addHistoryDebitorAccountItem($donation, $reverse = false, $context = 'DONATOR'){
		$fundMaster = $donation->getForeignRecordBreakNull('fundmaster_id', Donator_Controller_FundMaster::getInstance());
		$contact = $fundMaster->getForeignRecordBreakNull('contact_id', Addressbook_Controller_Contact::getInstance());
		$debitor = Billing_Controller_Debitor::getInstance()->getByContactOrCreate($contact->getId());
		$debitorId = $debitor->getId();		
		
		$debitorAccount = new Billing_Model_DebitorAccount(null,true);
        $debitorAccount->__set('debitor_id', $debitorId);
        $debitorAccount->__set('item_type', 'HISTORY');
        $debitorAccount->__set('usage', $donation->__get('donation_usage').' Datentransfer VEWA Spenden: Sp.nr '. $donation->__get('donation_nr') .' Adress-Nr '. $contact->getId());
        
        $value = abs((float)$donation->__get('donation_amount'));
        
        $debitorAccount->__set('s_brutto', $value);
        $debitorAccount->__set('h_brutto', $value);
        $debitorAccount->__set('debitor_id', $debitorId);
         $debitorAccount->__set('erp_context_id', $context);
        
        $date = new Zend_Date($donation->__get('donation_date'));
      	$date = $date->toString('yyyy-MM-dd');
        $debitorAccount->__set('create_date', $date);
        $debitorAccount->__set('value_date', $date);
        $debitorAccount->__set('debitor_id', $debitorId);
        
        Billing_Controller_DebitorAccount::getInstance()->create($debitorAccount);
	}*/
	
	public function addDebitorDebitAccountItem($donation, $context = 'DONATOR'){
		$fundMaster = $donation->getForeignRecordBreakNull('fundmaster_id', Donator_Controller_FundMaster::getInstance());
		$contact = $fundMaster->getForeignRecordBreakNull('contact_id', Addressbook_Controller_Contact::getInstance());
		$debitor = Billing_Controller_Debitor::getInstance()->getByContactOrCreate($contact->getId());
		$debitorId = $debitor->getId();		
		
		$debitorAccount = new Billing_Model_DebitorAccount(null,true);
		 $value = abs((float)$donation->__get('donation_amount'));
		$debitorAccount->__set('item_type', 'DEBIT');
		if($donation->__get('is_cancellation')){
			$debitorAccount->__set('item_type', 'CREDIT');
	 		  $debitorAccount->__set('h_brutto', $value);
		}else{
	        $debitorAccount->__set('s_brutto', $value);
		}
		
        $debitorAccount->__set('debitor_id', $debitorId);
        
        $debitorAccount->__set('usage', $donation->__get('donation_usage').' Sollstellung Spende: Sp.nr '. $donation->__get('donation_nr') .' Adress-Nr '. $contact->getId());
        
       
        //$debitorAccount->__set('h_brutto', $value);
        $debitorAccount->__set('debitor_id', $debitorId);
        $debitorAccount->__set('erp_context_id', $context);
        
        $debitorAccount->__set('object_id', $donation->getId());
        
        $date = new Zend_Date($donation->__get('donation_date'));
      	//$date = $date->toString('yyyy-MM-dd');
        $debitorAccount->__set('create_date', $date);
        $debitorAccount->__set('value_date', $date);
        $debitorAccount->__set('debitor_id', $debitorId);
        
        Billing_Controller_DebitorAccount::getInstance()->create($debitorAccount);
	}
	
	public function bookDonation($donation){
		if($donation->isSingle() && $donation->isBookingAllowed()){
			$fundMaster = $donation->getForeignRecord('fundmaster_id', Donator_Controller_FundMaster::getInstance());
			$contactId = $fundMaster->getForeignId('contact_id');
			$debitor = Billing_Controller_Debitor::getInstance()->getByContactOrCreate($contactId);
			
			$campaign = $donation->getForeignRecord('campaign_id', Donator_Controller_Campaign::getInstance());
			
			$usage = 'Spende Adr.nr '.$contactId.' '.$campaign->__get('name');
			
			$booking = new Billing_Model_Booking(null, true);
			$booking->__set('booking_date', new Zend_Date($donation->__get('donation_date')));
			$booking->__set('booking_text', $usage.' '.$donation->__get('donation_usage'));
			$booking->__set('erp_context_id', 'DONATOR');
			$booking->__set('receipt_unique_nr', ' ');
			$booking->__set('is_cancelled', $donation->__get('is_cancelled'));
			$booking->__set('is_cancellation', $donation->__get('is_cancellation'));
			
			// set reference to source object: in this case the donation
			$booking->__set('object_id', $donation->getId());
			$booking->__set('donation_id', $donation->getId());
			
			$data = $donation->getBookingData();
						
			$booking = Billing_Controller_Booking::getInstance()->create($booking);
			Billing_Controller_AccountBooking::getInstance()->multiCreateAccountBookings($booking->getId(), $data);
			$donation->__set('booking_id', $booking->getId());
			$donation = $this->update($donation);
			Tinebase_Event::fireEvent(new Donator_Events_DonationBooked($donation, $booking));
		}
	}
	
 	/*public function create(Tinebase_Record_Interface $_record){
    	parent::create($_record);
    	// @todo: remove this -> as this is called by parent::create already!!
    	//$this->_afterCreate($_record);
    	return $this->get($_record->getId());
    }*/
	
	protected function _inspectCreate(Tinebase_Record_Interface $_record)
	{
		$_record->__set('donation_nr', Tinebase_NumberBase_Controller::getInstance()->getNextNumber('donator_donation_nr'));
		
		$fundmasterId = $_record->__get('fundmaster_id');
		
		if(is_array($fundmasterId)){
			$_record->__set('fundmaster_id',$fundmasterId['id']);
		}
		
		$campaign = $_record->getForeignRecord('campaign_id',Donator_Controller_Campaign::getInstance());
		
		if(!$_record->__get('erp_proceed_account_id')){
			$_record->__set('erp_proceed_account_id', $campaign->getForeignId('erp_proceed_account_id'));
		}
		
		if(!$_record->__get('donation_account_id')){
			$_record->__set('donation_account_id', $campaign->getForeignId('donation_account_id'));
		}
		
		$donationDate = new Zend_Date($_record->__get('donation_date'));
		$period = $donationDate->get(Zend_Date::YEAR);
		$_record->__set('period', $period);
		
		
	}

	/**
	 * (non-PHPdoc)
	 * @see release/sopen 1.1/main/app/core/vendor/tine/v/2/base/Tinebase/Controller/Record/Tinebase_Controller_Record_Abstract::_inspectUpdate()
	 */
	protected function _inspectUpdate($_record, $_oldRecord)
	{
		$fundmasterId = $_record->__get('fundmaster_id');
		 
		if(is_array($fundmasterId)){
			$_record->__set('fundmaster_id',$fundmasterId['id']);
		}
		
		$campaign = $_record->getForeignRecord('campaign_id',Donator_Controller_Campaign::getInstance());
		
		if(!$_record->__get('erp_proceed_account_id')){
			$_record->__set('erp_proceed_account_id', $campaign->getForeignId('erp_proceed_account_id'));
		}
		
		if(!$_record->__get('donation_account_id')){
			$_record->__set('donation_account_id', $campaign->getForeignId('donation_account_id'));
		}
		$donationDate = new Zend_Date($_record->__get('donation_date'));
			$period = $donationDate->get(Zend_Date::YEAR);
		$_record->__set('period', $period);
	}
	
	
	protected function _afterCreate(Tinebase_Record_Interface $_record){
		
		Tinebase_Event::fireEvent(new Donator_Events_DonationCreated($_record));

	}
	
	
	/**
	 * 
	 * Get donations to be confirmed
	 * @return Tinebase_Record_RecordSet
	 */
	public function getDonationsToBeConfirmed($fundMasterId = null, $type = 'CONFIRMATION_SINGLE', $idsOnly = false, $additionalFilters = array()){
		return $this->_backend->getDonationsToBeConfirmed($fundMasterId, $type, $idsOnly, $additionalFilters);
	}
	
	public function getCollectedDonationsToBeConfirmed( $fundMasterId = null, $idsOnly = false, $additionalFilters = array()){
		return $this->_backend->getCollectedDonationsToBeConfirmed($fundMasterId, $idsOnly, $additionalFilters);
	}

	/**
	 * 
	 * Get a donator's donations to be confirmed
	 * @param unknown_type $fundMasterId
	 * @return Tinebase_Record_RecordSet
	 */
	public function getDonatorsDonationsToBeConfirmed($fundMasterId, $additionalFilters = array()){
		return $this->_backend->getDonationsToBeConfirmed($fundMasterId, $additionalFilters);
	}
	
	/**
	 * 
	 * Get donations to be gratuated
	 * @return Tinebase_Record_RecordSet
	 */
	public function getDonationsToBeGratuated(){
		return $this->_backend->getDonationsToBeGratuated();
	}

	/**
	 * 
	 * Get a donator's donations to be gratuated
	 * @param unknown_type $fundMasterId
	 * @return Tinebase_Record_RecordSet
	 */
	public function getDonatorsDonationsToBeGratuated($fundMasterId){
		return $this->_backend->getDonationsToBeGratuated($fundMasterId);
	}
	
	/**
	 * 
	 * Get donations by fundmaster_id
	 * @param string $fundMasterId
	 * @return	Tinebase_Record_RecordSet
	 */
	public function getByFundMasterId($fundMasterId){
		return $this->_backend->getMultipleByProperty($fundMasterId, 'fundmaster_id');
	}
	/**
	 * 
	 * 
	 * input data:
	 * {
				printAction: this.getPrintAction(),
				exportType: this.getExportType(),
				docKind: this.getDocKind(),
				donationFilters: this.getDonationFilterData(),
				fundMasterFilters: this.getFundmasterFilterData(),
				serveType: this.getServeType(),
				sort1: Ext.getCmp('donation_sort1').getValue(),
				sort2: Ext.getCmp('donation_sort2').getValue(),
				sortDirection: Ext.getCmp('donation_sort_dir').getValue(),
		}
	 */
	public function expDonations(){
		try{
			$data = org\sopen\app\util\arrays\ArrayHelper::getKeyPathBreak('data', $_REQUEST);
			$data = Zend_Json::decode($data);
			
			$printAction = org\sopen\app\util\arrays\ArrayHelper::getKeyPathBreak('printAction',$data);
			$exportType = org\sopen\app\util\arrays\ArrayHelper::getKeyPathBreak('exportType',$data);
			
			$docKind = org\sopen\app\util\arrays\ArrayHelper::getKeyPathBreak('docKind',$data);
			$serveType = org\sopen\app\util\arrays\ArrayHelper::getKeyPathBreak('serveType',$data);
			$sort1 = org\sopen\app\util\arrays\ArrayHelper::getKeyPathBreak('sort1',$data);
			$sort2 = org\sopen\app\util\arrays\ArrayHelper::getKeyPathBreak('sort2',$data);
			$sortDirection = org\sopen\app\util\arrays\ArrayHelper::getKeyPathBreak('sortDirection',$data);
			
			$donationFilters = Zend_Json::decode(org\sopen\app\util\arrays\ArrayHelper::getKeyPathBreak('donationFilters',$data));
			$fundMasterFilters = Zend_Json::decode(org\sopen\app\util\arrays\ArrayHelper::getKeyPathBreak('fundMasterFilters',$data));
			
			$printController = null;
			
			switch($printAction){
				case 'CONFIRMATION_SINGLE':
				case 'CONFIRMATION_COLLECT':
				case 'GRATUATION':
					$printController = Donator_Controller_Print::getInstance();
					break;
					
				case 'DONATION_LIST':
					$printController = Donator_Controller_PrintConfirmationList::getInstance();
					break;
					
				default:
					break;
			}
			
			if($printController instanceof Donator_Controller_Print){
				if($docKind == 'PREVIEW'){
					$printController->setIsPreview();
				}
				
				$printController->setDonationFilters($donationFilters);
				$printController->setFundMasterFilters($fundMasterFilters);
				$printController->setServeType($serveType);
			}
			
			$aSort = array('adr_one_postalcode', 'n_family');
			if($sort1){
				$aSort = array($sort1);
			}
			
			if($sort2){
				$aSort[] = $sort2;
			}
			
			$pagination = new Tinebase_Model_Pagination(array(
				'sort' => $aSort
			));
			
			switch($printAction){
				case 'CONFIRMATION_SINGLE':
						$printController->printDueConfirmations();
					break;
					
				case 'CONFIRMATION_COLLECT':
						$printController->printDueCollectConfirmations();
					break;
					
				case 'GRATUATION':
						$printController->printDueGratuations();
					break;
					
				case 'DONATION_LIST':
						//$fundMasterFilters = new Donator_Model_FundMasterFilter($fundMasterFilters, 'AND');
						$donationFilters = new Donator_Model_DonationFilter($donationFilters, 'AND');
						
						foreach($fundMasterFilters as $fFilter){
							$donationFilters->addFilter($donationFilters->createFilter($fFilter['field'],$fFilter['operator'], $fFilter['value']));
						}
						//$donationFilters->addFilterGroup($fundMasterFilters);
						
						$printController->printDonations($this->search($donationFilters, $pagination, false, true ));
					
					break;
				
					default: 
						throw Zend_Exception('No or unknown action provided');
					
					
			}
			
			
			
		}catch(org\sopen\app\util\arrays\ArrayKeyPathException $e){
			echo $e->__toString();
			exit;
			throw new Zend_Exception('No or wrong input parameter provided');
		}catch(Exception $e){
			echo $e->__toString();
			exit;
		}
	}
}
?>