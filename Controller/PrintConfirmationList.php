<?php 
use org\sopen\app\api\filesystem\storage\StorageException;
/**
 * 
 * Class for printing memberships (certificates, cards)
 * 
 * @author hhartl
 *
 */
class Donator_Controller_PrintConfirmationList extends Tinebase_Controller_Abstract{
	
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
	protected $serveType = 'DOWNLOAD';
	
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
	}

	private static $_instance = NULL;
	//private $jobId = null;
	
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
	
	public function getPrintJobStorage(){
		return $this->printJobStorage;
	}
	
	/*public function setJobId($jobId){
		$this->jobId = $jobId;	
	}*/
	
	public function printDonations($processArray){
		$this->processArray = $processArray;
		
		$this->runTransaction();
	}
	
	private function createConfirmationList(){
		$this->templateId = Tinebase_Core::getPreference('Donator')->getValue(Donator_Preference::TEMPLATE_CONFIRMATION_LIST);
		$this->createDoc();
		$this->finalizeDocs();
	}
	
	private function createDoc(){

		$aMem = array();
		$count = 0;
		$total = 0;
		
		$count = count($this->processArray);
		$this->count = $count;

		foreach($this->processArray as $donationId){
			$donation = Donator_Controller_Donation::getInstance()->get($donationId);
			$fundMaster = $donation->getForeignRecord('fundmaster_id', Donator_Controller_FundMaster::getInstance());
			$contact = $fundMaster->getForeignRecord('contact_id', Addressbook_Controller_Contact::getInstance());
			$campaign = $donation->getForeignRecord('campaign_id', Donator_Controller_Campaign::getInstance());
			$donationAmount = $donation->__get('donation_amount');
			$total += $donationAmount;
			
			$company2 = $company3 = '';
			if($contact->__get('company2')){
				$company2 = $contact->__get('company2');
			}
			/*$sortName = $contact->__get('n_family');
			if(!$contact->__get('n_family')){
				$sortName = $contact->__get('org_name');
			}*/
			$aDon[] = array(
				'NR' => $donation->__get('donation_nr'),
				'CNR' => $contact->__get('id'),
				'NAME' => $contact->__get('n_fileas'),
				'FORENAME' => $contact->__get('n_given'),
				'LASTNAME' => $contact->__get('n_family'),
				'THANK'		=> $donation->tellGratuationKind(),
				'CONFIRM'		=> $donation->tellConfirmationKind(),
				'TYPE' => $donation->tellType(),
				'COMPANY' => $contact->__get('org_name') . $company2 . $company3,
				'STREET' => $contact->__get('adr_one_street'),
				'LOCATION' => $contact->__get('adr_one_postalcode'). ' ' .$contact->__get('adr_one_locality'),
				'AMOUNT' => \org\sopen\app\util\format\Currency::formatCurrency($donationAmount),
				'DONATION' => \org\sopen\app\util\format\Currency::formatCurrency($donationAmount),
				'DDATE' =>  \org\sopen\app\util\format\Date::format($donation->__get('donation_date')),
				'USAGE' => $campaign->__get('name'),
				'DEFAULTUSAGE' => 'gemeinnützige Zwecke'
			);
        }
		
		//ksort($aDon);

		$aData = array(
			'DATE' => strftime('%d.%m.%Y %H:%M:%S'),
			'LISTNAME' => 'Spendenliste gemäß Filter',
			'POS_TABLE' => $aDon,
			'count' => $count,
			'total' => \org\sopen\app\util\format\Currency::formatCurrency($total)
		);
		$tempInFile = $this->tempFilePath . md5(serialize($parentMember).microtime()) . '_in.odt';
		$tempOutFile = $this->tempFilePath . md5(serialize($parentMember).microtime()) . '_out.odt';
		
		$this->templateController->renderTemplateToFile($this->templateId, $aData, $tempInFile, $tempOutFile, array());
		
		// move file into storage: cleans up tempfile at once
		$this->printJobStorage->moveIn( $tempOutFile,"//in/single/preparation/odt/vlist");
	}
	
	private function finalizeDocs(){
		if($this->printJobStorage->fileExists("//in/single/preparation/odt/vlist")){
			$inFile = $this->printJobStorage->resolvePath( "//in/single/preparation/odt/vlist" );
			$bufferAssocId = $assocId;
			$outFile = $this->printJobStorage->getCreateIfNotExist( "//convert/single/preparation/pdf/vlist" );
			$inputFiles[] = $outFile;
			$this->pdfServer->convertDocumentToPdf($inFile, $outFile);
		}

		$outputFile = $this->printJobStorage->getCreateIfNotExist( "//out/result/merge/pdf/final" );
		$this->printJobStorage->copy("//convert/single/preparation/pdf/vlist", "//out/result/merge/pdf/final" );
	}
	
	private function outputResult(){
		header("Pragma: public");
	    header("Cache-Control: max-age=0");
	        
		
		if($this->serveType == 'DOWNLOAD'){
			header('Content-Disposition: attachment; filename=print.pdf');
		}
		
		header("Content-Description: Pdf Datei");  
	    header('Content-Type: application/pdf');
		
		// get content from storage and close it (temporary storage gets deleted by this operation)
		$content = $this->printJobStorage->readFileClose("//out/result/merge/pdf/final");
		
	}
	
	private function outputNone(){
		//$this->printJobStorage->close();
		echo 'Keine Dokumente fällig zum Druck!';
	}
	
	private function runTransaction(){
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
			
			$this->createConfirmationList();
			
			
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
		}
	}
}
?>