<?php 
/**
 * 
 * OpenItemPayed event
 * @author hhartl
 *
 */
class Donator_Events_DonationCreated extends Tinebase_Event_Abstract
{
	/**
	 * 
	 * Donation given by Donator_Controller_Donation
	 * @var Donator_Model_Donation
	 */
	public static $donationMap = array();
    public $donation;
    public $fundMaster;
    
    /**
	 * 
	 * Donation given by Donator_Controller_Donation
	 * @var Donator_Model_Donation
	 */
    public $openItem;
    
    public function __construct($donation, $fundMaster)
    {
    	if(!array_key_exists($donation->getId(), self::$donationMap)){
    		self::$donationMap[$donation->getId()] = true;
    	}else{
    		throw new Exception('donation '.$donation->getId().' threw Created event more then once');
    	}
        $this->donation = $donation;
        $this->fundMaster = $fundMaster;
    }
}


?>