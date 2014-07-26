 <?php
/**
 * Tine 2.0
 * @package     Donator
 * @license     http://www.gnu.org/licenses/agpl.html AGPL Version 3
 * @author      Hans-Jürgen Hartl <hhartl@sopen.de>
 * @copyright   Copyright (c) 2010 sopen GmbH (http://www.sopen.de)
 * @version     $Id: Cli.php  $
 * 
 */

/**
 * cli server for Donator
 *
 * This class handles cli requests for the Donator
 *
 * @package     Donator
 */
class Donator_Frontend_Cli extends Tinebase_Frontend_Cli_Abstract
{
    /**
     * the internal name of the application
     *
     * @var string
     */
    protected $_applicationName = 'Donator';
    
    /**
     * import config filename
     *
     * @var string
     */
    protected $_configFilename = 'importconfig.inc.php';

    /**
     * help array with function names and param descriptions
     */

    /**
     * import Donations
     *
     * @param Zend_Console_Getopt $_opts
     */
    public function importFundMasters($_opts)
    {
    	set_time_limit(0);      
        parent::_import($_opts, Donator_Controller_FundMaster::getInstance());        
    }
    
    /**
     * import Donations
     *
     * @param Zend_Console_Getopt $_opts
     */
    public function importDonations($_opts)
    {
    	set_time_limit(0);      
        parent::_import($_opts, Donator_Controller_Donation::getInstance());        
    }
    
 	public function importRegularDonations($_opts)
    {
    	set_time_limit(0);      
        parent::_import($_opts, Donator_Controller_RegularDonation::getInstance());        
    }
    
    public function importProjects($_opts)
    {
    	set_time_limit(0);      
        parent::_import($_opts, Donator_Controller_Project::getInstance());        
    }
    
    public function importCampaigns($_opts)
    {
    	set_time_limit(0);      
        parent::_import($_opts, Donator_Controller_Campaign::getInstance());        
    }
}
