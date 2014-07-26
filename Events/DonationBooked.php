<?php 
/**
 * 
 * OpenItemPayed event
 * @author hhartl
 *
 */
class Donator_Events_DonationBooked extends Tinebase_Event_Abstract
{
	/**
	 * 
	 * Donation given by Donator_Controller_Donation
	 * @var Donator_Model_Donation
	 */
    public $donation;
    public $booking;
    
    /**
	 * 
	 * Donation given by Donator_Controller_Donation
	 * @var Donator_Model_Donation
	 */
    public $openItem;
    
    public function __construct($donation, $booking)
    {
        $this->donation = $donation;
        $this->booking = $booking;
    }
}


?>