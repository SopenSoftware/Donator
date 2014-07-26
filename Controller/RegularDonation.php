<?php
class Donator_Controller_RegularDonation extends Tinebase_Controller_Record_Abstract
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
		$this->_backend = new Donator_Backend_RegularDonation();
		$this->_modelName = 'Donator_Model_RegularDonation';
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

	public function getEmptyRegularDonation(){
		$emptyOrder = new Donator_Model_RegularDonation(null,true);
		return $emptyOrder;
	}
	
	public function getByFundMasterId($fundMasterId){
		return $this->_backend->getMultipleByProperty($fundMasterId, 'fundmaster_id');
	}

	public function createFromContact($contactId){
		$fm = $this->getEmptyRegularDonation();
		$contact = Addressbook_Controller_Contact::getInstance()->get($contactId);
		$fm->__set('contact_id', $contactId);
		$fm->__set('adr_one_street', $contact->__get('adr_one_street'));
		$fm->__set('adr_one_postalcode', $contact->__get('adr_one_postalcode'));
		$fm->__set('adr_one_locality', $contact->__get('adr_one_locality'));
		$fm->__set('confirmation_kind','CONFIRMATION_NO');
		$fm->__set('gratuation_kind','THANK_NO');
		$fm->__set('donation_payment_interval','NOVALUE');
		$fm->__set('donation_payment_method','NOVALUE');
		return $this->create($fm);
	}

	/**
	 * (non-PHPdoc)
	 * @see release/sopen 1.1/main/app/core/vendor/tine/v/2/base/Tinebase/Controller/Record/Tinebase_Controller_Record_Abstract::_inspectCreate()
	 */
	protected function _inspectCreate(Tinebase_Record_Interface $_record){
		$_record->__set('regular_donation_nr', Tinebase_NumberBase_Controller::getInstance()->getNextNumber('donator_regular_donation_nr'));
		if(!$_record->__get('bank_account_id')){
			if($_record->__get('iban')){
				$bankAccount = Billing_Api_BankAccount::createForContactAndIBAN(
						$_record->getForeignId('contact_id'),
						$_record->__get('iban'),
						$_record->__get('bank_account_name'),
						$_record->getForeignIdBreakNull('bank_account_id')
				);
					
				$_record->__set('bank_account_id', $bankAccount->getId());
			}
		}
	}
	
	protected function _afterCreate(Tinebase_Record_Interface $_record){
		
		
		$bankAccount = $_record->getForeignRecordBreakNull('bank_account_id', Billing_Controller_BankAccount::getInstance());
    	if($bankAccount){
	    	$usage = $bankAccount->addUsageRegularDonation($_record);
	    	$paymentMethod = $_record->__get('donation_payment_method');
	    	
	    	if($paymentMethod == 'DEBIT' || $paymentMethod == 'DEBIT_GM'){
				Billing_Controller_SepaMandate::getInstance()->generateSepaMandateForBankAccountUsage($usage);
			}
    	}
	}

	/**
	 * (non-PHPdoc)
	 * @see release/sopen 1.1/main/app/core/vendor/tine/v/2/base/Tinebase/Controller/Record/Tinebase_Controller_Record_Abstract::_inspectUpdate()
	 */
	protected function _inspectUpdate($_record, $_oldRecord)
	{
		if(!$_record->__get('bank_account_id')){
			if($_record->__get('iban')){
				$bankAccount = Billing_Api_BankAccount::createForContactAndIBAN(
						$_record->getForeignId('contact_id'),
						$_record->__get('iban'),
						$_record->__get('bank_account_name'),
						$_record->getForeignIdBreakNull('bank_account_id')
				);
					
				$_record->__set('bank_account_id', $bankAccount->getId());
			}
		}
		$bankAccount = $_record->getForeignRecordBreakNull('bank_account_id', Billing_Controller_BankAccount::getInstance());
		$usage = $bankAccount->addUsageRegularDonation($_record);
			
		$paymentMethod = $_record->__get('donation_payment_method');
    	if($paymentMethod == 'DEBIT' || $paymentMethod == 'DEBIT_GM'){
			Billing_Controller_SepaMandate::getInstance()->generateSepaMandateForBankAccountUsage($usage);
		}
		
		if($_record->__get('sepa_signature_date') && !$_oldRecord->__get('sepa_signature_date')){
			$sepaMandate = $usage->getForeignRecordBreakNull('sepa_mandate_id', Billing_Controller_SepaMandate::getInstance());
			if($sepaMandate){
				$sepaMandate->__set('signature_date', new Zend_Date($_record->__get('sepa_signature_date')));
				$sepaMandate->__set('mandate_state', 'CONFIRMED');
				Billing_Controller_SepaMandate::getInstance()->update($sepaMandate);
			}
		}elseif(!$_record->__get('sepa_signature_date')  && $_oldRecord->__get('sepa_signature_date') ){
			$sepaMandate = $usage->getForeignRecordBreakNull('sepa_mandate_id', Billing_Controller_SepaMandate::getInstance());
			if($sepaMandate){
				$sepaMandate->__set('signature_date', null);
				$sepaMandate->__set('mandate_state', 'GENERATED');
				Billing_Controller_SepaMandate::getInstance()->update($sepaMandate);
			}
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
		$recordSet = Donator_Controller_Donation::getInstance()->getByRegularDonationId($_record->getId());
		if($recordSet->count()>0){
			return false;
		}
		return true;
	}

	public function improveRegularDonations(){
		set_time_limit(0);
		try{

			$filters = array();
				
			$filters[] = array(
	    			'field' => 'on_hold',
	    			'operator' => 'equals',
	    			'value' => 0
			);
			$filters[] = array(
	    			'field' => 'terminated',
	    			'operator' => 'equals',
	    			'value' => 0
			);
			$filter = new Donator_Model_RegularDonationFilter($filters, 'AND');
				
				
				
			$regDonationIds =  $this->search(
			$filter,
			new Tinebase_Model_Pagination(array('sort' => 'next_date', 'dir' => 'ASC')),
			null,
			true
			);

			foreach($regDonationIds as $regDonId){
				$dirty = false;
				$regDon = $this->get($regDonId);

				$fundMasterId = $regDon->getForeignId('fundmaster_id');
				$fundMaster = $regDon->getForeignRecord('fundmaster_id', Donator_Controller_FundMaster::getInstance());
				$contactId = $fundMaster->getForeignId('contact_id');

				$beginDate = new Zend_Date($regDon->__get('begin_date'));
				$nextDate = null;
				if($regDon->__get('next_date')){
					$beginDate = new Zend_Date($regDon->__get('next_date'));
				}
				$counts = Donator_Controller_Donation::getInstance()->countDonationsWithinTimeframe($beginDate, $nextDate, $fundMasterId);

				$regDon->__set('control_sum', 0);
				$regDon->__set('control_count', 0);

				if($counts['count']>0){
					$regDon->__set('control_sum', $counts['sum']);
					$regDon->__set('control_count', $counts['count']);
					$dirty = true;
				}

				$memberships = Membership_Controller_SoMember::getInstance()->getByContactId($contactId);
				 
				// check whether there are terminated memberships and mark if custom code makes necessary
				if(Membership_Custom_SoMember::regularDonationMemberTerminationAlert($memberships)){
					$regDon->__set('terminated_membership',1);
					$dirty = true;
				}else{
					$regDon->__set('terminated_membership',0);
					$dirty = true;
				}

				$debitor = Billing_Controller_Debitor::getInstance()->getByContactId($contactId);
				 
				$orderIds = Billing_Controller_Order::getInstance()->getOrdersByDebitorId($debitor->getId(), 'DONATOR', true);
				 
				foreach($orderIds as $orderId){
					$receipts = Billing_Controller_Order::getInstance()->getReceiptsByOrderId($orderId);
					foreach($receipts as $receipt){
						if(
						(float)$regDon->__get('reg_donation_amount') == (float) $receipt->__get('total_brutto') &&
						($receipt->__get('erp_context_id') == 'DONATOR')
						){
							$regDon->__set('last_receipt_id', $receipt->getId());
							$dirty = true;
							break;
						}
					}
				}
				 
				$donations = Donator_Controller_Donation::getInstance()->getByFundMasterId($fundMasterId);
				foreach($donations as $donation){
					if((float)$donation->__get('donation_amount') == (float)$regDon->__get('reg_donation_amount') ){
						if($donation->__get('donation_type') == 'CYCLE'){
							$regDon->__set('last_donation_id', $donation->getId());
							$dirty = true;
							break;
						}
					}
				}
				 
				if($dirty){
					$this->update($regDon);
				}
				 
			}
				
			return array(
				'state' => 'success',
				'result' => null	
			);
				
		}catch(Exception $e){
				
			return array(
				'state' => 'failure',
				'result' => null,
				'errorInfo' => array(
					'message' => $e->getMessage(),
					'trace' => $e->getTrace()
			)
			);
		}
	}

	public function executeRegularDonationNow($regularDonationId){
		return $this->generateDTA($regularDonationId);
		/*if($result['state'] == 'success'){
			$regularDonation = $this->get($regularDonationId);
		}*/
	}

	public function reverseLastRegularDonationExecution($regularDonationId){
		$regularDonation = $this->get($regularDonationId);
		$lastInvoice = $regularDonation->__get('last_receipt_id');
		$lastDonation = $regularDonation->getForeignId('last_donation_id');
		 
		if($lastInvoice && $lastDonation){
			$ld = $regularDonation->getForeignRecord('last_donation_id', Donator_Controller_Donation::getInstance());
			if(!$ld->__get('is_cancelled')){
				 
				Donator_Controller_Donation::getInstance()->reverseDonation($lastDonation);
				Billing_Controller_Order::getInstance()->reverseInvoice($lastInvoice,null);
			}

		}
	}

	public function generateDTA($regularDonationId = null){
		set_time_limit(0);
		try{
			// membership controllers
			$mController = Membership_Controller_SoMember::getInstance();

			// order controllers
			$orderController = Billing_Controller_Order::getInstance();
			$orderTemplateController = Billing_Controller_OrderTemplate::getInstance();
			$orderTemplatePosController = Billing_Controller_OrderTemplatePosition::getInstance();
			$debitorController = Billing_Controller_Debitor::getInstance();
			$orderPosController = Billing_Controller_OrderPosition::getInstance();
				
			$filters = array();
			$filters[] = array(
	    			'field' => 'next_date',
	    			'operator' => 'beforeOrAt',
	    			'value' => strftime('%Y-%m-%d')
			);
			$filters[] = array(
	    			'field' => 'last_date',
	    			'operator' => 'beforeAtOrNull',
	    			'value' => strftime('%Y-%m-%d')
			);
			$filters[] = array(
	    			'field' => 'begin_date',
	    			'operator' => 'beforeOrAt',
	    			'value' => strftime('%Y-%m-%d')
			);
			$filters[] = array(
	    			'field' => 'on_hold',
	    			'operator' => 'equals',
	    			'value' => 0
			);
			$filters[] = array(
	    			'field' => 'terminated',
	    			'operator' => 'equals',
	    			'value' => 0
			);
			if(!is_null($regularDonationId)){
				$filters[] = array(
	    			'field' => 'id',
	    			'operator' => 'equals',
	    			'value' => $regularDonationId
				);
			}
				
			$filter = new Donator_Model_RegularDonationFilter($filters, 'AND');
				
				
			$db = Tinebase_Core::getDb();
			$tm = Tinebase_TransactionManager::getInstance();
				
				
			if(!is_null($regularDonationId)){
				$regDonationIds = array($regularDonationId);
			}else{
				$regDonationIds =  $this->search(
				$filter,
				new Tinebase_Model_Pagination(array('sort' => 'next_date', 'dir' => 'ASC')),
				null,
				true
				);
			}
			
			foreach($regDonationIds as $regDonId){
				$tId = $tm->startTransaction($db);
					
				$regDon = $this->get($regDonId);
				$campaign = $regDon->getForeignRecord('campaign_id', Donator_Controller_Campaign::getInstance());
				$fundMaster = $regDon->getForeignRecord('fundmaster_id', Donator_Controller_FundMaster::getInstance());

				$contactId = $fundMaster->getForeignId('contact_id');

				if(is_null($contactId)){
					throw new Exception('Contact is null for member_id '. $fundMaster->getId());
				}

				$debitor = $debitorController->getByContactOrCreate($contactId);
				if(!$debitor instanceof Billing_Model_Debitor){
					throw new Exception('No instance Billing_Model_Debitor');
				}
				// create order based on order template
				$order = $orderController->createOrderForDebitor($debitor->getId(), null, 'DONATOR');
				if(!$order instanceof Billing_Model_Order){
					throw new Exception('No instance Billing_Model_Order');
				}

				$templateId = Tinebase_Core::getPreference('Donator')->getValue(Donator_Preference::TEMPLATE_REGDON_INVOICE);
				$orderTemplateId = Tinebase_Core::getPreference('Donator')->getValue(Donator_Preference::REGDON_ORDERTEMPLATE);

				$orderTemplate = $orderTemplateController->get($orderTemplateId);

				$op = $orderTemplatePosController->getByOrderTemplateId($orderTemplateId);
				$count = 0;
				// execute reg don and give back single donation record
				$donation = $regDon->execute();

				foreach($op as $pos){
					// get fee progress var associations
					$posArray = array(
							'amount' => $pos->__get('amount'),
							'name' => $pos->__get('name'),
							'article_id' => $pos->getForeignId('article_id'),
							'price_group_id' => $pos->getForeignId('price_group_id'),
							'unit_id' => $pos->getForeignId('unit_id'),
							'vat_id' => $pos->getForeignId('vat_id'),
							'price_netto' => $donation->__get('donation_amount'),
							'price_brutto' => $donation->__get('donation_amount'),
							'weight' => $pos->__get('weight'),
							'factor' => 1
					);
					$orderPosition = $orderPosController->getEmptyOrderPosition(null,true);
					$orderPosition->setFromArray(
					$posArray
					);
					$orderPosition->__set('order_id', $order->getId());
					$orderPosition->__set('position_nr', ++$count);
					$orderPosController->calculate($orderPosition);
					$orderPosition = $orderPosController->create($orderPosition);
				}
					
				$additionalData =  self::getAdditionalInvoiceData($donation);
				$paymentMethodId =
				Billing_Controller_PaymentMethod::getInstance()
				->getPaymentMethodFromRecordOrDefault($regDon, 'donation_payment_method')
				->getId();
					
				$additionalData['CNR'] = $contactId;


				$additionalData['USAGE'] = 'Angekündigte Spende('.$contactId.') - '.$campaign->__get('name');
				$additionalData['CAMPAIGN'] = $campaign->__get('name');

				$params = array(
						'process' => array(
							'billing' => array(
								'active' => true,
								'data' => array(
									'template_id' => $templateId,
									'payment_method_id' => $paymentMethodId,
									'erp_context_id' => 'DONATOR',
									'usage' => 'Angekündigte Spende('.$contactId.') - '.$campaign->__get('name'),
									'donation_id' => $donation->getId()
				),
								'additionalTemplateData' => $additionalData
				)
				)
				);

				$result = $orderController->processOrder($order->getId(), $params);

				$regDon->__set('last_receipt_id', $result['receipts']['invoice']['data']['id']);
				$this->update($regDon);

				$tm->commitTransaction($tId);
				//$feeProgress->__set('fee_calc_datetime', new Zend_Date());
				//$feeProgress->__set('invoice_receipt_id', $result['receipts']['invoice']['data']['id']);
				//$feeProgress->__set('order_id', $result['result']['id']);

			}

				
			return array(
				'state' => 'success',
				'result' => $regDon->toArray()	
			);
				
		}catch(Exception $e){
			$tm->rollback($tId);
			return array(
				'state' => 'failure',
				'result' => null,
				'errorInfo' => array(
					'message' => $e->getMessage(),
					'trace' => $e->getTrace()
			)
			);
		}
	}

	public function getAdditionalInvoiceData($donation){
		return array(
    		'DNR' => $donation->__get('donation_nr'),
    		'FMASTER' => $donation->getForeignId('fundmaster_id'),
    		'DONDATE_RAW' => $donation->__get('donation_date'),
    		'DONDATE' => \org\sopen\app\util\format\Date::format($donation->__get('donation_date'))
		);
	}

	public function generateDTA1(){
		$db = Tinebase_Core::getDb();
		$tm = Tinebase_TransactionManager::getInstance();
		try{
			require_once 'Payment/DTA.php';

			$filters = array();
			$filters[] = array(
	    			'field' => 'next_date',
	    			'operator' => 'beforeOrAt',
	    			'value' => strftime('%Y-%m-%d')
			);
			$filters[] = array(
	    			'field' => 'last_date',
	    			'operator' => 'beforeAtOrNull',
	    			'value' => strftime('%Y-%m-%d')
			);
			$filters[] = array(
	    			'field' => 'begin_date',
	    			'operator' => 'beforeOrAt',
	    			'value' => strftime('%Y-%m-%d')
			);
			$filter = new Donator_Model_RegularDonationFilter($filters, 'AND');

			$aCampaigns = array();

			// start transaction
			$tId = $tm->startTransaction($db);
				
			$regDonations =  $this->search(
			$filter,
			new Tinebase_Model_Pagination(array('sort' => 'next_date', 'dir' => 'ASC'))
			);
				
			$campaigns = $regDonations->__getFlattened('campaign_id');
			$campaigns = array_unique($campaigns);
				
			$tempFilePath = CSopen::instance()->getCustomerPath().'/customize/data/documents/temp/';
				
			foreach($campaigns as $campaignId){
				$campaign = Donator_Controller_Campaign::getInstance()->get($campaignId);
				$bankAccount = $campaign->getForeignRecordBreakNull('donation_account_id', Donator_Controller_DonationAccount::getInstance());
				$bankAccountId = $bankAccount->getId();
				if(!array_key_exists($bankAccountId, $aCampaigns)){
						
					$hash = md5(serialize($bankAccountId).microtime());
					$dtaFile = new DTA(DTA_DEBIT);
					$dtaFile->setAccountFileSender(
					array(
						        "name"           => $bankAccount->__get('account_name'),
						        "bank_code"      => $bankAccount->__get('bank_code'),
						        "account_number" => $bankAccount->__get('bank_account_nr')
					)
					);
						
					$aCampaigns[$bankAccountId] = array(
						'hash' => $hash,
						'dtaFile' => $dtaFile,
						'bankAccount' => $campaign->getForeignRecordBreakNull('donation_account_id', Donator_Controller_DonationAccount::getInstance()),
						'campaign' => $campaign
					);
				}

			}
				
			$aCollectData = array();
				
			// 		create DTA file
			foreach($regDonations as $regDonation){
				$aCollect = array();
				$val = (float) $regDonation->__get('reg_donation_amount');
				$aCollect['value'] = $val;
				if(!$val || $val<=0){
					continue;
				}

				$bankAccount = $regDonation->getForeignRecordBreakNull('donation_account_id', Donator_Controller_DonationAccount::getInstance());
				$bankAccountId = $bankAccount->getId();

				$campaign = $regDonation->getForeignRecordBreakNull('campaign_id', Donator_Controller_Campaign::getInstance());
				$aCollect['bankaccount_id'] = $bankAccountId;
				if(!array_key_exists($bankAccountId, $aCampaigns)){
						
					$hash = md5(serialize($bankAccountId).microtime());
					$dtaFile = new DTA(DTA_DEBIT);
					$dtaFile->setAccountFileSender(
					array(
						       "name"           => $bankAccount->__get('account_name'),
						        "bank_code"      => $bankAccount->__get('bank_code'),
						        "account_number" => $bankAccount->__get('bank_account_nr')
					)
					);
						
					$aCampaigns[$bankAccountId] = array(
						'hash' => $hash,
						'dtaFile' => $dtaFile,
						'bankAccount' => $regDonation->getForeignRecordBreakNull('donation_account_id', Donator_Controller_DonationAccount::getInstance()),
						'campaign' => $campaign
					);
				}



				$dtaFile = $aCampaigns[$bankAccountId]['dtaFile'];


				$fundMaster = $regDonation->getForeignRecordBreakNull('fundmaster_id', Donator_Controller_FundMaster::getInstance());

				$contact = $fundMaster->getForeignRecordBreakNull('contact_id', Addressbook_Controller_Contact::getInstance());

				$debitor = Billing_Controller_Debitor::getInstance()->getByContactOrCreate($contact->getId());

				$dtaFile->addExchange(
				array(
				        "name"          	=> $regDonation->__get('account_name'),
			        	"bank_code"      	=> $regDonation->__get('bank_code'),
			        	"account_number" 	=> $regDonation->__get('bank_account_nr')
				),
				(string)$val,                 	// Amount of money.
				array(                  		// Description of the transaction ("Verwendungszweck").
				        "Spende ".$campaign->__get('name'),
				$val. ' EUR -'.$bankAccount->__get('account_name')
				)
				);
				$donation = $regDonation->execute();


				$this->update($regDonation);
				$donation = Donator_Controller_Donation::getInstance()->create($donation);

				$aCollect['dta'] = $dtaFile;
				$aCollect['donation'] = $donation->toArray();
				$aCollect['regdonation'] = $regDonation->toArray();
				$aCollectData[] = $aCollect;
				//
				// not necessary anymore (donation gets payed after its creation automatically)
				//Donator_Controller_Donation::getInstance()->payDonation($donation);
				//
			}
				
			Tinebase_Core::getLogger()->warn(__METHOD__ . '::' . __LINE__ . ' REGDON EXECUTE: '.print_r($aCollectData, true));
				

			$p1 = $tempFilePath.'DTAUS0-'.md5(microtime());
			mkdir($p1);

			$zipMap = array();
				
			foreach($aCampaigns as $cmp){
				$dtaFile = $cmp['dtaFile'];

				$hash = $cmp['hash'];
				$bankAccount = $cmp['bankAccount'];
				$curFileNameRaw = $bankAccount->__get('bank_code').'-'.$bankAccount->__get('bank_account_nr');
				$curFilename = $p1.'/'.$curFileNameRaw.'-DTAUS0';
				$handoutFileName = $p1.'/'.$curFileNameRaw.'-begleitzettel.txt';
				mkdir($curPath);


				$meta = $dtaFile->getMetaData();
					
				$date	= strftime("%d.%m.%y", $meta["date"]);
				$execDate	=strftime("%d.%m.%y", $meta["exec_date"]);
				$count	=$meta["count"];
				$sumEUR	= $meta["sum_amounts"];
				$sumKto	=$meta["sum_accounts"];
				$sumBankCodes	= $meta["sum_bankcodes"];

				$sender	= $bankAccount->__get('account_name');
				$senderBank	= $bankAccount->__get('bank_name');
				$senderBankCode	= $bankAccount->__get('bank_code');
				$senderAccount	= $bankAccount->__get('bank_account_nr');
					
				$handoutContent = "Datenträger-Begleitzettel
Erstellungsdatum: $date 
Ausführungsdatum: $execDate
Anzahl der Lastschriften: $count
Summe der Beträge in EUR: $sumEUR
Kontrollsumme Kontonummern: $sumKto
Kontrollsumme Bankleitzahlen: $sumBankCodes
Auftraggeber: $sender
Beauftragtes Bankinstitut: $senderBank
Bankleitzahl: $senderBankCode
Kontonummer: $senderAccount";

				// save dta file and handout file
				$dtaFile->saveFile($curFilename);
				file_put_contents($handoutFileName, $handoutContent);

				$zipMap[] = array(
					'raw' => $curFileNameRaw,
					'dtaus' => $curFilename,
					'handout' => $handoutFileName,
					'hash' => $hash
				);


			}
				
			$zipMap1 = array();
				
			foreach($zipMap as $zFile){
				$zip = new ZipArchive();
				$filename = "$tempFilePath/DTAUS0-$hash.zip";

				if ($zip->open($filename, ZIPARCHIVE::CREATE)!==TRUE) {
					exit("cannot open <$filename>\n");
				}

				$zip->addFile($curFilename, $curFileNameRaw.'-DTAUS0');
				$zip->addFile($handoutFileName, $curFileNameRaw.'-begleitzettel.txt');

				$zip->close();

				$zipMap1[] = array('raw' => $filename, 'local' => 'DTAUS0-'.$hash.'.zip');
			}
				
			$zip = new ZipArchive();
			$filename = "$tempFilePath/DTAUS0.zip";
				
			if ($zip->open($filename, ZIPARCHIVE::CREATE)!==TRUE) {
				exit("cannot open <$filename>\n");
			}
				
			foreach($zipMap1 as $zFile){


				$zip->addFile($zFile['raw'], $zFile['local']);

			}

			$zip->close();
				
			header("Content-type: application/zip;\n");
			header("Content-Transfer-Encoding: binary");
			$len = filesize($filename);
			header("Content-Length: $len;\n");
			$date = strftime('%Y-%m-%d');
			$outname="DTAUS0-$date.zip";
			header("Content-Disposition: attachment; filename=\"$outname\";\n\n");

			readfile($filename);
				
			unlink($filename);
				
			// TEST : rollback transaction in order to execute it multiple
				
			$tm->commitTransaction($tId);
		}catch(Exception $e){
			$tm->rollback($tId);
		}
	}
}
?>