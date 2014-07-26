<?php
/**
 * Tine 2.0
 * 
 * @package     Addressbook
 * @subpackage  Acl
 * @license     http://www.gnu.org/licenses/agpl.html AGPL Version 3
 * @copyright   Copyright (c) 2009 Metaways Infosystems GmbH (http://www.metaways.de)
 * @author      Philipp Schuele <p.schuele@metaways.de>
 * @version     $Id: Rights.php 17856 2010-12-14 16:08:21Z l.kneschke@metaways.de $
 * 
 */

/**
 * this class handles the rights for the Addressbook application
 * 
 * a right is always specific to an application and not to a record
 * examples for rights are: admin, run
 * 
 * to add a new right you have to do these 3 steps:
 * - add a constant for the right
 * - add the constant to the $addRights in getAllApplicationRights() function
 * . add getText identifier in getTranslatedRightDescriptions() function
 * 
 * @package     Addressbook
 * @subpackage  Acl
 */
class Donator_Acl_Rights extends Tinebase_Acl_Rights_Abstract
{
	const RESET_DONATION = 'reset_donation';
	const EDIT_AFTERPRINT = 'edit_afterprint';
	
	const GRANT_VIEW = '__view';
	const GRANT_EDIT = '__edit';
	const GRANT_DELETE = '__delete';
	
	
    /**
     * holds the instance of the singleton
     *
     * @var Donator_Acl_Rights
     */
    private static $_instance = NULL;
    
    /**
     * the clone function
     *
     * disabled. use the singleton
     */
    private function __clone() 
    {        
    }
    
    /**
     * the constructor
     *
     */
    private function __construct()
    {
        
    }    
    
    /**
     * the singleton pattern
     *
     * @return Addressbook_Acl_Rights
     */
    public static function getInstance() 
    {
        if (self::$_instance === NULL) {
            self::$_instance = new Donator_Acl_Rights;
        }
        
        return self::$_instance;
    }
    
    /**
     * get all possible application rights
     *
     * @return  array   all application rights
     */
    public function getAllApplicationRights()
    {
        
        $allRights = parent::getAllApplicationRights();
        
        $addRights = array(
            self::RESET_DONATION,
            self::EDIT_AFTERPRINT,
            self::GRANT_VIEW,
            self::GRANT_EDIT,
            self::GRANT_DELETE
        );
        $allRights = array_merge($allRights, $addRights);
        
        return $allRights;
    }

    /**
     * get translated right descriptions
     * 
     * @return  array with translated descriptions for this applications rights
     */
    private function getTranslatedRightDescriptions()
    {
        $translate = Tinebase_Translation::getTranslation('Addressbook');
        
        $rightDescriptions = array(
            self::RESET_DONATION => array(
                'text'          => $translate->_('Spende nach Druck zurücksetzen'),
                'description'   => $translate->_('Nach dem Druck einer Spende ist das jeweilige Druckdatum gefüllt.')
            ),
            self::EDIT_AFTERPRINT => array(
                'text'          => $translate->_('Spende nach Druck bearbeiten'),
                'description'   => $translate->_('Nach dem Druck einer Spende kann diese normalerweise nicht mehr bearbeitet werden.')
            ),
            self::GRANT_VIEW => array(
                'text'          => $translate->_('Spenden anzeigen'),
                'description'   => $translate->_('')
            ),
            self::GRANT_EDIT => array(
                'text'          => $translate->_('Spenden bearbeiten'),
                'description'   => $translate->_('')
            ),
            self::GRANT_DELETE => array(
                'text'          => $translate->_('Spenden löschen'),
                'description'   => $translate->_('')
            )                                                                                              
        );
        
        return $rightDescriptions;
    }

    /**
     * get right description
     * 
     * @param   string right
     * @return  array with text + description
     */
    public function getRightDescription($_right)
    {        
        $result = parent::getRightDescription($_right);
        
        $rightDescriptions = self::getTranslatedRightDescriptions();
        
        if ( isset($rightDescriptions[$_right]) ) {
            $result = $rightDescriptions[$_right];
        }

        return $result;
    }
}
