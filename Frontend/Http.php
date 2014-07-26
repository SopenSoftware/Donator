<?php


/**
 * This class handles all Http requests for the Donator application
 *
 * @package     Donator
 * @subpackage  Frontend
 */
class Donator_Frontend_Http extends Tinebase_Frontend_Http_Abstract
{
    protected $_applicationName = 'Donator';
    
    /**
     * Returns all JS files which must be included for this app
     *
     * @return array Array of filenames
     */
    public function getJsFilesToInclude()
    {
        return array(
        	'Donator/js/Models.js',
            'Donator/js/Backend.js',
        	'Donator/js/Custom.js',
        	'Donator/js/MainScreen.js',
            'Donator/js/AddressbookPlugin.js',
        	'Donator/js/FundMasterEditRecord.js',
        	'Donator/js/FundMasterGridPanel.js',
        	'Donator/js/FundMasterWidget.js',
            'Donator/js/DonationEditRecord.js',
            'Donator/js/DonationGridPanel.js',
        	'Donator/js/DonationAccountEditDialog.js',
            'Donator/js/DonationAccountGridPanel.js',
            'Donator/js/DonationUnitEditDialog.js',
            'Donator/js/DonationUnitGridPanel.js',
        	'Donator/js/CampaignEditDialog.js',
        	'Donator/js/CampaignGridPanel.js',
            'Donator/js/ProjectEditDialog.js',
            'Donator/js/ProjectGridPanel.js',
        	'Donator/js/ContactSelect.js',
        	'Donator/js/ProjectSelect.js',
        	'Donator/js/CampaignSelect.js',
        	'Donator/js/Renderer.js',
        	'Donator/js/RegularDonationEditDialog.js',
        	'Donator/js/RegularDonationGridPanel.js',
        	'Donator/js/PrintDonationDialog.js'
        );
    }
    
    public function getCssFilesToInclude()
    {
        return array(
            'Donator/css/Donator.css'
        );
    }
    
    public function printConfirmations(){
    	error_reporting(E_ALL);
    	ini_set('display_errors','on');
    	Donator_Controller_Print::getInstance()->printConfirmations();
    }
    
    public function printGratuations(){
    	error_reporting(E_ALL);
    	ini_set('display_errors','on');
    	Donator_Controller_Print::getInstance()->printGratuations();
    }
    
    public function printDueConfirmations(){
    	error_reporting(E_ALL);
    	ini_set('display_errors','on');
    	Donator_Controller_Print::getInstance()->printDueConfirmations();
    }
    
 	public function printDueCollectConfirmations(){
    	error_reporting(E_ALL);
    	ini_set('display_errors','on');
    	Donator_Controller_Print::getInstance()->printDueCollectConfirmations();
    }
    
    public function printDueGratuations(){
    	error_reporting(E_ALL);
    	ini_set('display_errors','on');
    	Donator_Controller_Print::getInstance()->printDueGratuations();
    }
    
    public function printDueAll(){
    	error_reporting(E_ALL);
    	ini_set('display_errors','on');
    	Donator_Controller_Print::getInstance()->printAll();
    }
    
    public function expDonations(){
    	Donator_Controller_Donation::getInstance()->expDonations();
    }
    
	/*public function generateDTA(){
		error_reporting(E_ALL);
    	ini_set('display_errors','on');
		Donator_Controller_RegularDonation::getInstance()->generateDTA();
	}
	*/
	
 	public function printConfirmationPrepareList(){
    	error_reporting(E_ALL);
    	ini_set('display_errors','on');
    	Donator_Controller_PrintConfirmationList::getInstance()->printDonations(
    		Donator_Controller_Donation::getInstance()->getDonationsToBeConfirmed(null, 'CONFIRMATION_SINGLE', true)
    	);
    }
}
