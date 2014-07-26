<?php 
/**
 * 
 * OpenItemPayed event
 * @author hhartl
 *
 */
class Donator_Events_DonationReverted extends Tinebase_Event_Abstract
{
	/**
	 * 
	 * Donation given by Donator_Controller_Donation
	 * @var Donator_Model_Donation
	 */
    public $donation;
    public $reverseDonation;
    
    /**
	 * 
	 * Donation given by Donator_Controller_Donation
	 * @var Donator_Model_Donation
	 */
    public $openItem;
    
    public function __construct($donation, $reverseDonation)
    {
        $this->donation = $donation;
        $this->reverseDonation = $reverseDonation;
    }
}


?>