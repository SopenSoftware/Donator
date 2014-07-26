<?php 
use org\sopen\app\api\filesystem\storage\StorageException;

class Donator_Controller_Print extends Tinebase_Controller_Abstract{
	const TYPE_CONFIRMATION = 'confirmation';
	const TYPE_GRATUATION = 'gratuation';
	
	const PROCESS_ALL = 'all';
	const PROCESS_CONFIRMATIONS = 'confirmations';
	const PROCESS_COLLECTED_CONFIRMATIONS = 'collectedConfirmations';
	const PROCESS_GRATUATIONS = 'gratuations';
	
	const PROCESS_SEL_CONFIRMATIONS = 'selConfirmations';
	const PROCESS_SEL_GRATUATIONS = 'selGratuations';
	
	
	/**
	 * config of courses
	 *
	 * @var Zend_Config
	 */
	protected $_config = NULL;
	private $pdfServer = null;
	private $printJobStorage = null;
	private $map = array();
	private $count = 0;
	private $params = array();
	private $donationFilters = array();
	private $fundMasterFilters = array();
	protected $serveType = 'INLINE';
	
	/**
	 * the constructor
	 *
	 * don't use the constructor. use the singleton
	 */
	private function __construct() {
		$this->_applicationName = 'Donator';
		$this->_currentAccount = Tinebase_Core::getUser();
		$this->_donationController = Donator_Controller_Donation::getInstance();
		$this->_doContainerACLChecks = FALSE;
		$this->params = $this->getParams();
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
	
	public function setIsPreview(){
		$this->params['preview'] = true;
	}
	
	/**
	 * 
	 * Analyze request params
	 */
	public function getParams(){
		$params = array(
			'preview' => false,
			'ids' => array()
		);
		if(isset($_REQUEST['preview'])){
			$params['preview'] = true;
		}
		if(isset($_REQUEST['ids'])){
			$params['ids'] = Zend_Json::decode($_REQUEST['ids']);
		}
		return $params;
	}
	
	public function setDonationFilters($filters){
		$this->donationFilters = $filters;	
	}
	
	public function setFundMasterFilters($filters){
		$this->fundMasterFilters = $filters;
	}
	
	public function setServeType($type){
		if(!in_array($type, array('DOWNLAD','INLINE'))){
			$this->serveType = $type;
		}
	}
	
	/**
	 * 
	 * determine whether it's a preview
	 */
	public function isPreview(){
		if(array_key_exists('preview',$this->params)){
			return $this->params['preview'];
		}
		return false;
	}
	/**
	 * 
	 * get ids from request
	 */
	public function getIds(){
		if(array_key_exists('ids',$this->params)){
			return $this->params['ids'];
		}
		return null;
	}
	
	public function printAll(){
		$this->runTransaction(self::PROCESS_ALL);
	}
	
	public function printDueGratuations(){
		$this->runTransaction(self::PROCESS_GRATUATIONS);
	}
	
	public function printDueConfirmations(){
		$this->runTransaction(self::PROCESS_CONFIRMATIONS);
	}
	
	public function printDueCollectConfirmations(){
		$this->runTransaction(self::PROCESS_COLLECTED_CONFIRMATIONS);
	}
	
	public function printGratuations(){
		$this->runTransaction(self::PROCESS_SEL_GRATUATIONS);
	}
	
	public function printConfirmations(){
		$this->runTransaction(self::PROCESS_SEL_CONFIRMATIONS);
	}
	
	private function createDueConfirmations($collected = false){
		if(!$collected){
			$donations = $this->_donationController->getDonationsToBeConfirmed();
			$this->createConfirmations($donations, $collected);
		}else{
			//$donationIds = $this->_donationController->getCollectedDonationsToBeConfirmed(true);
			$this->createCollectedConfirmations();
		}
	}
	
	private function createSelConfirmations($collected = false){
		$filter = new Tinebase_Model_Filter_Id('id','in', $this->getIds());
		$filterGroup = new Tinebase_Model_Filter_FilterGroup(array());
		$filterGroup->addFilter($filter);
		$donationIds = $this->_donationController->search($filterGroup, null, false, true);
		$this->createConfirmations($donationIds, $collected);
	}
	
	private function createCollectedConfirmations(){
		$templateId = Tinebase_Core::getPreference('Donator')->getValue(Donator_Preference::TEMPLATE_CONFIRMATION_COLLECTED);
				
		// search fundmaster to get collected confirmations
		$filters = $this->fundMasterFilters;
		
		$filters[] = array(
			'field' =>'confirmation_kind',
			'operator' => 'equals',
			'value' => 'CONFIRMATION_COLLECT'
		);
		
		$filter = new Donator_Model_FundMasterFilter($filters, 'AND');
		$paging = new Tinebase_Model_Pagination(array(
			'sort' => 'adr_one_postalcode',
			'dir' => 'ASC'
		));
		
		$fundMasterIds = Donator_Controller_FundMaster::getInstance()->search(
			$filter, 
			$paging,
			null,
			true
		);
		
		$this->count += count($fundMasterIds);
		
		foreach($fundMasterIds as $fundMasterId){
			$fundMaster = Donator_Controller_FundMaster::getInstance()->get($fundMasterId);
			$donationIds = $this->_donationController->getCollectedDonationsToBeConfirmed($fundMasterId, true, $this->donationFilters);
			$posTable = array();
			$totalSum = 0;
			$first = true;
			// for final document generate unique number
			if(!$this->isPreview()){
				$number = Tinebase_NumberBase_Controller::getInstance()->getNextNumber(
					'donator_collected_confirmation_nr',
					new Donator_Custom_NumberBase_Calculator_DonationConfirmNr(),
					array(
							'fundmaster' => $fundMaster
					)
				);
			}else{
				$number = Tinebase_NumberBase_Controller::getInstance()->simulateGetNextNumber(
					'donator_collected_confirmation_nr',
					new Donator_Custom_NumberBase_Calculator_DonationConfirmNr(),
					array(
							'fundmaster' => $fundMaster
					)
				);
			}
			
			if(count($donationIds)>0){
				foreach($donationIds as $donationId){
					$fullRecordDonation = $this->_donationController->get($donationId);
					$contact = $fundMaster->getForeignRecord('contact_id',Addressbook_Controller_Contact::getInstance());
					$contactId = $contact->getId();
					// get data for template from custom template
					$replaceTextBlocks = $this->templateController->getTextBlocks($templateId);
					
					$campaign = $fullRecordDonation->__get('campaign_id');
					$campaign = Donator_Controller_Campaign::getInstance()->get($campaign->__get('id'));
					$project = $campaign->__get('project_id');
					$project = Donator_Controller_Project::getInstance()->get($project->__get('id'));
					
					
					$donationAmount = abs((float)$fullRecordDonation->__get('donation_amount'));
					$totalSum += $donationAmount;
					
					// Betrag formatiert
					$BETRAG = \org\sopen\app\util\format\Currency::formatCurrency($donationAmount);
					
					// Betrag in Worten
					$BETRAG_WORTE = \org\sopen\app\util\format\Currency::speakCurrency($donationAmount);
					
					// Datum formatiert von ISO nach dd.mm.yyyy
					$DATUM = \org\sopen\app\util\format\Date::format($fullRecordDonation->__get('donation_date'));
					
					if($first){
						$first = false;
						$firstDate = $DATUM;
					}
					
					$lastDate = $DATUM;
					
					$kind = 'Geldzuwendung ($)';
					if($fullRecordDonation->__get('non_monetary')){
						$kind = 'Sachzuwendung ($)';
					}
					
					if(!$fullRecordDonation->__get('refund_quitclaim')){
						$kind = str_replace('$', 'a', $kind);
					}else{
						$kind = str_replace('$', 'b', $kind);
					}
					
					$posTable[] = array(
						'amount' => $BETRAG,
						'amount_words' => $BETRAG_WORTE,
						'date' => $DATUM,
						'usage' => $campaign->__get('name'),
						'kind' => $kind,
						'ADR_NR' => $contact->__get('id'),
						'BRIEFANREDE' => $contact->__get('letter_salutation'),
						'ANSCHRIFT' => array(
							'BRIEF' => $contact->getLetterDrawee()->toText()
						)
					);
							
					if(!$this->isPreview()){
						$fullRecordDonation->__set('confirmation_date', strftime('%Y-%m-%d'));
						$fullRecordDonation->__set('confirm_nr', $number);
						$fullRecordDonation->flatten();
						Donator_Controller_Donation::getInstance()->update($fullRecordDonation);
					}
				}
				$totalSum = \org\sopen\app\util\format\Currency::formatCurrency($totalSum);
					
				// Betrag in Worten
				$totalSumWords = \org\sopen\app\util\format\Currency::speakCurrency($totalSum);
				
				$data = array(
					'sum' => $totalSum,
					'sum_words' => $totalSumWords,
					'CONFIRMATION_NR' => $number,
					'first' => $firstDate,
					'last' => $lastDate,
					'POS_TABLE' => $posTable,
					'DATUM' => \org\sopen\app\util\format\Date::format(new Zend_Date()),
					'ADR_NR' => $contact->__get('id'),
					'BRIEFANREDE' => $contact->__get('letter_salutation'),
					'ANSCHRIFT' => array(
						'BRIEF' => $contact->getLetterDrawee()->toText()
					)
				);
				$this->map[$contactId][] = $fundMasterId;
					
				$tempInFile = $this->tempFilePath . md5(serialize($donation).microtime()) . '_in.odt';
				$tempOutFile = $this->tempFilePath . md5(serialize($donation).microtime()) . '_out.odt';
	
				$this->templateController->renderTemplateToFile($templateId, $data, $tempInFile, $tempOutFile, $replaceTextBlocks);
				
				// move file into storage: cleans up tempfile at once
				$this->printJobStorage->moveIn( $tempOutFile,"//in/$contactId/$fundMasterId/odt/confirmation");
			}
		}
		
		foreach($this->map as $contactId=>$cDonation){
			foreach($cDonation as $cDonationId){
				if($this->printJobStorage->fileExists("//in/$contactId/$cDonationId/odt/confirmation")){
					$inFile = $this->printJobStorage->resolvePath( "//in/$contactId/$cDonationId/odt/confirmation" );
					$outFile = $this->printJobStorage->getCreateIfNotExist( "//convert/$contactId/$cDonationId/pdf/confirmation" );
					$this->pdfServer->convertDocumentToPdf($inFile, $outFile);
				}
			}
		}
	}
	
	private function createConfirmations($donationIds, $collected = false){
		$this->count += count($donationIds);

		foreach($donationIds as $donationId){
			$fullRecordDonation = $this->_donationController->get($donationId);
			
			if(!Donator_Custom_Template::isToPrint($fullRecordDonation, self::TYPE_CONFIRMATION, &$templateId, $collected)){
				--$this->count;
				continue;	
			}
			
			/*if(!$donation->__get('confirm_nr')){
				$donation->__set('confirm_nr', Tinebase_NumberBase_Controller::getInstance()->getNextNumber('donation_confirm_number'));
			}*/
			
			$contact = $fullRecordDonation->__get('fundmaster_id')->__get('contact_id');
			$contactId = $contact['id'];
			$contact = Addressbook_Controller_Contact::getInstance()->get($contactId);
			// get data for template from custom template
			$replaceTextBlocks = $this->templateController->getTextBlocks($templateId);
			
			$campaign = $fullRecordDonation->getForeignRecord('campaign_id',Donator_Controller_Campaign::getInstance());
			//$campaign = Donator_Controller_Campaign::getInstance()->get($campaign->__get('id'));
			$project = $campaign->getForeignRecord('project_id',Donator_Controller_Project::getInstance());
			//$project = Donator_Controller_Project::getInstance()->get($project->__get('id'));
			
			if(!$this->isPreview()){
				$number = Tinebase_NumberBase_Controller::getInstance()->getNextNumber(
					'donator_collected_confirmation_nr',
					new Donator_Custom_NumberBase_Calculator_DonationConfirmNr(),
					array(
							'donation' => $fullRecordDonation
					)
				);
			}else{
				$number = Tinebase_NumberBase_Controller::getInstance()->simulateGetNextNumber(
					'donator_collected_confirmation_nr',
					new Donator_Custom_NumberBase_Calculator_DonationConfirmNr(),
					array(
							'donation' => $fullRecordDonation
					)
				);
			}
			
			$fullRecordDonation->__set('confirm_nr', $number);
			
			$data = Donator_Custom_Template::getConfirmationData(
				array(
					'contact' => $contact,
					'donation' => $fullRecordDonation,
					'campaign' => $campaign,
					'project' => $project
				),
				$replaceTextBlocks
			);
			
			$donationId = $fullRecordDonation->__get('id');
			$this->map[$contactId][] = $donationId;
			
			$tempInFile = $this->tempFilePath . md5(serialize($donation).microtime()) . '_in.odt';
			$tempOutFile = $this->tempFilePath . md5(serialize($donation).microtime()) . '_out.odt';

			$this->templateController->renderTemplateToFile($templateId, $data, $tempInFile, $tempOutFile, $replaceTextBlocks);
			
			// move file into storage: cleans up tempfile at once
			$this->printJobStorage->moveIn( $tempOutFile,"//in/$contactId/$donationId/odt/confirmation");

			if(!$this->isPreview()){
				$fullRecordDonation->__set('confirmation_date', strftime('%Y-%m-%d'));
				$fullRecordDonation->__set('confirm_nr', $number);
				$fullRecordDonation->flatten();
				Donator_Controller_Donation::getInstance()->update($fullRecordDonation);
			}
		}
		
		foreach($this->map as $contactId=>$cDonation){
			foreach($cDonation as $cDonationId){
				if($this->printJobStorage->fileExists("//in/$contactId/$cDonationId/odt/confirmation")){
					$inFile = $this->printJobStorage->resolvePath( "//in/$contactId/$cDonationId/odt/confirmation" );
					$outFile = $this->printJobStorage->getCreateIfNotExist( "//convert/$contactId/$cDonationId/pdf/confirmation" );
					$this->pdfServer->convertDocumentToPdf($inFile, $outFile);
				}
			}
		}
	}
	
	private function createDueGratuations($collected = false){
		$donationIds = $this->_donationController->getDonationsToBeGratuated(null, 'THANK_STANDARD', $this->donationFilters);
		$this->createGratuations($donationIds, $collected);
	}
	
	private function createSelGratuations($collected = false){
		$filter = new Tinebase_Model_Filter_Id('id','in', $this->getIds());
		$filterGroup = new Tinebase_Model_Filter_FilterGroup(array());
		$filterGroup->addFilter($filter);
		$donationIds = $this->_donationController->search($filterGroup, null, false, true);
		$this->createGratuations($donationIds, $collected);
	}
	
	private function createGratuations($donationIds){
		$templateId = Tinebase_Core::getPreference('Donator')->getValue(Donator_Preference::TEMPLATE_GRATUATION);
		$this->count += count($donationIds);
		foreach($donationIds as $donationId){
			$fullRecordDonation = $this->_donationController->get($donationId);
			
			if(!Donator_Custom_Template::isToPrint($fullRecordDonation, self::TYPE_GRATUATION, &$templateId, false)){
				--$this->count;
				continue;
			}
			
			$contact = $fullRecordDonation->__get('fundmaster_id')->__get('contact_id');
			$contactId = $contact['id'];
			$contact = Addressbook_Controller_Contact::getInstance()->get($contactId);
			// get data for template from custom template
			$replaceTextBlocks = $this->templateController->getTextBlocks($templateId);
			$user = Tinebase_Core::get(Tinebase_Core::USER);
			$userContact =  Addressbook_Controller_Contact::getInstance()->getContactByUserId($user->getId());
			$data = Donator_Custom_Template::getGratuationData(
				array(
					'contact' => $contact,
					'donation' => $donation,
					'user' => Tinebase_Core::get(Tinebase_Core::USER),
					'userContact' => $userContact
				),
				$replaceTextBlocks
			);
			
			$donationId = $fullRecordDonation->__get('id');
			$this->map[$contactId][] = $donationId;
			
			$tempInFile = $this->tempFilePath . md5(serialize($donation).microtime()) . '_in.odt';
			$tempOutFile = $this->tempFilePath . md5(serialize($donation).microtime()) . '_out.odt';

			$this->templateController->renderTemplateToFile($templateId, $data, $tempInFile, $tempOutFile, $replaceTextBlocks);
			
			// move file into storage: cleans up tempfile at once
			$this->printJobStorage->moveIn( $tempOutFile,"//in/$contactId/$donationId/odt/gratuation");
			
			if(!$this->isPreview()){
				$fullRecordDonation->__set('thanks_date', strftime('%Y-%m-%d'));
				$fullRecordDonation->flatten();
				Donator_Controller_Donation::getInstance()->update($fullRecordDonation);
			}
		}
		
		foreach($this->map as $contactId=>$cDonation){
			foreach($cDonation as $cDonationId){
				if($this->printJobStorage->fileExists("//in/$contactId/$cDonationId/odt/gratuation")){
					$inFile = $this->printJobStorage->resolvePath( "//in/$contactId/$cDonationId/odt/gratuation" );
					$outFile = $this->printJobStorage->getCreateIfNotExist( "//convert/$contactId/$cDonationId/pdf/gratuation" );
					$this->pdfServer->convertDocumentToPdf($inFile, $outFile);
				}
			}
		}
	}
	
	private function createResult(){
		$inputFiles = array();
		$pathMap = array();
		foreach($this->map as $contactId=>$cDonation){
			foreach($cDonation as $cDonationId){
				
				if($this->printJobStorage->fileExists("//convert/$contactId/$cDonationId/pdf/confirmation")){
					$path = $this->printJobStorage->resolvePath( "//convert/$contactId/$cDonationId/pdf/confirmation" );
					if(!array_key_exists($path,$pathMap)){
						$inputFiles[] = $path;
						$pathMap[$path] = null;
					}
				}
				
				if($this->printJobStorage->fileExists("//convert/$contactId/$cDonationId/pdf/gratuation")){
					$path = $this->printJobStorage->resolvePath( "//convert/$contactId/$cDonationId/pdf/gratuation" );
					if(!array_key_exists($path,$pathMap)){
						$inputFiles[] = $path;
						$pathMap[$path] = null;
					}
				}
				
			}
		}	

		// give the final output file a name in the storage
		$outputFile = $this->printJobStorage->getCreateIfNotExist( "//out/result/DueConfirmations/pdf/final" );

		// merge the sorted input files to a multipage pdf
		$this->pdfServer->mergePdfFiles($inputFiles, $outputFile);
	}
	
	private function outputResult(){
		header("Pragma: public");
	    header("Cache-Control: max-age=0");
	        
		
		if($this->serveType == 'DOWNLOAD'){
			header('Content-Disposition: attachment; filename=print.pdf');
		}
		
		header("Content-Description: Pdf Datei");  
	    header('Content-Type: application/pdf');
		
	    $this->printJobStorage->readFileClose("//out/result/DueConfirmations/pdf/final");
	    // get content from storage and close it (temporary storage gets deleted by this operation)
		//echo $this->printJobStorage->getFileContent("//out/result/DueConfirmations/pdf/final");
		
		//$this->printJobStorage->close();
	}
	
	private function outputNone(){
		$this->printJobStorage->close();
		echo 'Keine Dokumente fällig zum Druck!';
	}
	
	private function runTransaction($process){
		try{
			$config = \Tinebase_Config::getInstance()->getConfig('pdfserver', NULL, TRUE)->value;
			$storageConf = \Tinebase_Config::getInstance()->getConfig('printjobs', NULL, TRUE)->value;
			
    		$this->tempFilePath = CSopen::instance()->getCustomerPath().'/customize/data/documents/temp/';
			$this->templateController = DocManager_Controller_Template::getInstance();
			$db = Tinebase_Core::getDb();
			$tm = Tinebase_TransactionManager::getInstance();
			
			$this->pdfServer = org\sopen\app\api\pdf\server\PdfServer::getInstance($config)->
				setDocumentsTempPath(CSopen::instance()->getDocumentsTempPath());
			$this->printJobStorage =  org\sopen\app\api\filesystem\storage\TempFileProcessStorage::createNew(
				'printjobs', 
				$storageConf['storagepath']
			);

			$this->printJobStorage->addProcessLines(array('in','convert','out'));
			
			$tId = $tm->startTransaction($db);
			
			switch($process){
				
				case self::PROCESS_ALL:
					
					$this->createDueConfirmations();
					$this->createDueGratuations();
					break;
					
				case self::PROCESS_CONFIRMATIONS:
					
					$this->createDueConfirmations();
					break;
					
				case self::PROCESS_COLLECTED_CONFIRMATIONS:
					$this->createDueConfirmations(true);
					break;
					
				case self::PROCESS_GRATUATIONS:
					
					$this->createDueGratuations();
					break;
					
				case self::PROCESS_SEL_CONFIRMATIONS:
					$this->createSelConfirmations();
					break;
					
				case self::PROCESS_SEL_GRATUATIONS:
					$this->createSelGratuations();
					break;
					
					
			}
			
			// create the multipage output from single page input files
			if($this->count>0){
				$this->createResult();
			}
			// make db changes final
			$tm->commitTransaction($tId);
			
			// output the result
			if($this->count>0){
				$this->outputResult();
			}else{
				$this->outputNone();
			}
		}catch(Exception $e){
			echo $e->__toString();
			$tm->rollback($tId);
			
			if($this->printJobStorage){
				$this->printJobStorage->close();
			}
		}
	}
}
?>