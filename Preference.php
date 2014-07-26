<?php
/**
 * Tine 2.0
 * 
 * @package     DocManager
 * @license     http://www.gnu.org/licenses/agpl.html AGPL Version 3
 * @author      Cornelius Weiss <c.weiss@metaways.de>
 * @copyright   Copyright (c) 2009 Metaways Infosystems GmbH (http://www.metaways.de)
 * @version     $Id: Preference.php 14258 2010-05-07 14:46:00Z g.ciyiltepe@metaways.de $
 */


/**
 * backend for DocManager preferences
 *
 * @package     DocManager
 */
class Donator_Preference extends Tinebase_Preference_Abstract
{
    /**************************** application preferences/settings *****************/
    
    /**
     * default DocManager all newly created contacts are placed in
     */
    const TEMPLATE_CONFIRMATION = 'templateConfirmation';
    const TEMPLATE_CONFIRMATION_COLLECTED = 'templateConfirmationCollected';
    const TEMPLATE_CONFIRMATION_NON_MON = 'templateConfirmationNonMonetary';
 	const TEMPLATE_CONFIRMATION_LIST = 'templateConfirmationList';
    const TEMPLATE_GROUPED_DONATION_LIST = 'templateGroupedDonationList';
    
    const TEMPLATE_REGDON_INVOICE = 'templateRegDonInvoice';
    const REGDON_ORDERTEMPLATE = 'templateRegDonOrderTemplate';
    
    
    /**
     * default persistent filter
     */
    const TEMPLATE_GRATUATION = 'templateGratuation';
    
    
    /**
     * @var string application
     */
    protected $_application = 'Donator';    
        
    /**************************** public functions *********************************/
    
    /**
     * get all possible application prefs
     *
     * @return  array   all application prefs
     */
    public function getAllApplicationPreferences()
    {
        $allPrefs = array(
            self::TEMPLATE_CONFIRMATION,
            self::TEMPLATE_CONFIRMATION_COLLECTED,
            self::TEMPLATE_GRATUATION,
            self::TEMPLATE_CONFIRMATION_NON_MON,
            self::TEMPLATE_CONFIRMATION_LIST,
           // self::TEMPLATE_GROUPED_DONATION_LIST,
            self::TEMPLATE_REGDON_INVOICE,
            self::REGDON_ORDERTEMPLATE
        );
            
        return $allPrefs;
    }
    
    /**
     * get translated right descriptions
     * 
     * @return  array with translated descriptions for this applications preferences
     */
    public function getTranslatedPreferences()
    {
        //$translate = Tinebase_Translation::getTranslation($this->_application);

        $prefDescriptions = array(
            self::TEMPLATE_CONFIRMATION  => array(
                'label'         => 'Vorlage Einzelquittung Geldzuwendung',
                'description'   => '',
            ),
            self::TEMPLATE_CONFIRMATION_COLLECTED  => array(
                'label'         => 'Vorlage Sammelquittung Geldzuwendung',
                'description'   => '',
            ),
            self::TEMPLATE_CONFIRMATION_NON_MON  => array(
                'label'         => 'Vorlage Spendenquittung Sachzuwendung',
                'description'   => '',
            ),            
            self::TEMPLATE_GRATUATION  => array(
                'label'         => 'Template für Bedankung',
                'description'   => '',
            ),
             self::TEMPLATE_CONFIRMATION_LIST  => array(
                'label'         => 'Template Spendenliste/Einzelquittung',
                'description'   => '',
            ),
            /* self::TEMPLATE_GROUPED_DONATION_LIST  => array(
                'label'         => 'Template Spendenliste gruppiert nach Spender',
                'description'   => '',
             ),*/
             self::TEMPLATE_REGDON_INVOICE=> array(
                'label'         => 'Template Rechnung Dauerspende',
                'description'   => '',
            ),
            self::REGDON_ORDERTEMPLATE => array(
                'label'         => 'Auftragsvorlage Dauerspende',
                'description'   => '',
            )
        );
        
        return $prefDescriptions;
    }
    
    /**
     * get preference defaults if no default is found in the database
     *
     * @param string $_preferenceName
     * @return Tinebase_Model_Preference
     */
    public function getPreferenceDefaults($_preferenceName, $_accountId=NULL, $_accountType=Tinebase_Acl_Rights::ACCOUNT_TYPE_USER)
    {
        $preference = $this->_getDefaultBasePreference($_preferenceName);
        
        switch($_preferenceName) {
            case self::TEMPLATE_CONFIRMATION:
           	case self::TEMPLATE_CONFIRMATION_COLLECTED:
            case self::TEMPLATE_CONFIRMATION_NON_MON:
            case self::TEMPLATE_CONFIRMATION_LIST:
                /*$accountId          = $_accountId ? $_accountId : Tinebase_Core::getUser()->getId();
                $DocManagers       = Tinebase_Container::getInstance()->getPersonalContainer($accountId, 'DocManager', $accountId, 0, true);
                $preference->value  = $DocManagers->getFirstRecord()->getId();
                $preference->personal_only = TRUE;
                */
                break;
            case self::TEMPLATE_GRATUATION:
            case self::TEMPLATE_REGDON_INVOICE:
            case self::REGDON_ORDERTEMPLATE:
                /*$accountId          = $_accountId ? $_accountId : Tinebase_Core::getUser()->getId();
                $DocManagers       = Tinebase_Container::getInstance()->getPersonalContainer($accountId, 'DocManager', $accountId, 0, true);
                $preference->value  = $DocManagers->getFirstRecord()->getId();
                $preference->personal_only = TRUE;
                */
                break;                
            default:
                throw new Tinebase_Exception_NotFound('Default preference with name ' . $_preferenceName . ' not found.');
        }
        
        return $preference;
    }
    
    /**
     * get special options
     *
     * @param string $_value
     * @return array
     */
    protected function _getSpecialOptions($_value)
    {
        $result = array();
        switch($_value) {
            case self::TEMPLATE_CONFIRMATION:
            case self::TEMPLATE_CONFIRMATION_COLLECTED:
          	case self::TEMPLATE_CONFIRMATION_NON_MON:
          	case self::TEMPLATE_CONFIRMATION_LIST:
          	case self::TEMPLATE_REGDON_INVOICE:
            	$templates = DocManager_Controller_Template::getInstance()->getAll();
            	foreach ($templates as $template) {
                    $result[] = array($template->getId(), $template->__get('name'));
                }
                break;
            case self::TEMPLATE_GRATUATION:
        		$templates = DocManager_Controller_Template::getInstance()->getAll();
         		foreach ($templates as $template) {
                    $result[] = array($template->getId(), $template->__get('name'));
                }
                break;
                
            case self::REGDON_ORDERTEMPLATE :
            	$templates = Billing_Controller_OrderTemplate::getInstance()->getAll();
         		foreach ($templates as $template) {
                    $result[] = array($template->getId(), $template->__get('name'));
                }
                break;
            	break;
            default:
                $result = parent::_getSpecialOptions($_value);
        }
        
        return $result;
    }
}
