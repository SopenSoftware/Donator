<?php
class Donator_Controller_Campaign extends Tinebase_Controller_Record_Abstract
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
		$this->_backend = new Donator_Backend_Campaign();
		$this->_modelName = 'Donator_Model_Campaign';
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

	public function getEmptyCampaign(){
		$emptyOrder = new Donator_Model_Campaign(null,true);
		return $emptyOrder;
	}
	
	public function getByCampaignNumber($campaignNumber){
		return $this->_backend->getByProperty($campaignNumber, 'campaign_nr');
	}
	
	protected function _inspectCreate(Tinebase_Record_Interface $_record){
		$_record->__set('campaign_nr', Tinebase_NumberBase_Controller::getInstance()->getNextNumber('donator_campaign_nr'));
	}
	
	public function getDefaultCampaign(){
    	return $this->_backend->getByProperty(1,'is_default');
    }
	
	
}
?>