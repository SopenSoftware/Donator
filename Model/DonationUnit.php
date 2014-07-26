<?php

/**
 * class to hold DonationUnit data
 *
 * @package     Donator
 */
class Donator_Model_DonationUnit extends Tinebase_Record_Abstract
{
	/**
	 * key in $_validators/$_properties array for the filed which
	 * represents the identifier
	 *
	 * @var string
	 */
	protected $_identifier = 'id';

	/**
	 * application the record belongs to
	 *
	 * @var string
	 */
	protected $_application = 'Donator';

	/**
	 * list of zend validator
	 *
	 * this validators get used when validating user generated content with Zend_Input_Filter
	 *
	 * @var array
	 *
	 */
	protected $_validators = array(
        'id'                    => array(Zend_Filter_Input::ALLOW_EMPTY => true, Zend_Filter_Input::DEFAULT_VALUE => NULL),
        'contact_id'                  => array(Zend_Filter_Input::ALLOW_EMPTY => false),
    	'unit_nr'                  => array(Zend_Filter_Input::ALLOW_EMPTY => true),
    	'unit_name'                  => array(Zend_Filter_Input::ALLOW_EMPTY => true)
	);
	protected $_dateFields = array(
	);
}