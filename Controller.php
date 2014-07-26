<?php
/**
 * Tine 2.0
 * 
 * MAIN controller for Billing, does event and container handling
 *
 * @package     Billing
 * @subpackage  Controller
 * @license     http://www.gnu.org/licenses/agpl.html AGPL Version 3
 * @author      Lars Kneschke <l.kneschke@metaways.de>
 * @copyright   Copyright (c) 2010-2010 Metaways Infosystems GmbH (http://www.metaways.de)
 * @version     $Id: Controller.php 18044 2010-12-22 23:05:24Z l.kneschke@metaways.de $
 * 
 */

/**
 * main controller for Donator
 *
 * @package     Billing
 * @subpackage  Controller
 */
class Donator_Controller extends Tinebase_Controller_Abstract implements Tinebase_Event_Interface, Tinebase_Container_Interface
{
    /**
     * holds the instance of the singleton
     *
     * @var Filemamager_Controller
     */
    private static $_instance = NULL;
    
    /**
     * constructor (get current user)
     */
    private function __construct() {
        $this->_currentAccount = Tinebase_Core::getUser();
    }
    
    /**
     * don't clone. Use the singleton.
     *
     */
    private function __clone() 
    {        
    }
    
    /**
     * the singleton pattern
     *
     * @return Addressbook_Controller
     */
    public static function getInstance() 
    {
        if (self::$_instance === NULL) {
            self::$_instance = new Donator_Controller;
        }
        
        return self::$_instance;
    }

    /**
     * event handler function
     * 
     * all events get routed through this function
     *
     * @param Tinebase_Event_Abstract $_eventObject the eventObject
     * 
     * @todo    write test
     */
    public function handleEvents(Tinebase_Event_Abstract $_eventObject)
    {
    	
    	
    	if($_eventObject instanceof Billing_Events_BillableReceiptCreated){
			// create open item out of invoice or credit
			try{
						$receipt = Billing_Controller_Receipt::getInstance()->get($_eventObject->receipt->getId());
						
						// -> create donation as parts from article (controlled by bill_article:creates_donation)
						 Donator_Controller_Donation::getInstance()->createFromReceipt($receipt);
			}catch(Exception $e){
				echo $e->__toString();
			}
		}
		
   	 	if($_eventObject instanceof Donator_Events_DonationCreated){
    		try{
    			// TODO: NRW
    			$donation = $_eventObject->donation;
	    		if($donation->isSingle() && $donation->isBookingAllowed()){
	    			// do booking
	    			$booking = Donator_Controller_Donation::getInstance()
	    						->bookDonation($donation);
					// do payment
	    			$payment = Donator_Controller_Donation::getInstance()
	    						->payDonation($donation);	    						
	    			// do payment: fires event Donation_Booked
	    			
	    			// if not generated from payment: no payment_id inside!!
	    			//$payment = Billing_Controller_Donation::getInstance()->payDonation($donation);
	    		
	    			
	    		}else{
	    			// booking chain is started by open_item which
	    			// is generated for a regular donation
	    			// also payment is handled by open item payment mechanism
	    		}
    		
    		}catch(Exception $e){
    			echo $e->__toString();
    		}
    	}
    	
    	if($_eventObject instanceof Donator_Events_DonationBooked){
    		try{
    			// TODO: NRW
    			$donation = $_eventObject->donation;
    			$booking = $_eventObject->booking;
	    		if($donation->isSingle()){
	    			Billing_Controller_DebitorAccount::getInstance()
	    				->onDonationBooked( $donation, $booking);
	    			
	    		}else{
	    			// booking chain is started by open_item which
	    			// is generated for a regular donation
	    			// also payment is handled by open item payment mechanism
	    		}
    		
    		}catch(Exception $e){
    			echo $e->__toString();
    		}
    	}
    	
    	// --> for regular donations which are handled by standard ERP booking
    	if($_eventObject instanceof Billing_Events_BillableReceiptBooked){
    		try{
    			// TODO: NRW
    			$receipt = $_eventObject->receipt;
	    		if($receipt->isDonation()){
		    		$donation = $receipt->getDonation();
		    		$openItem = $_eventObject->openItem;
	    			$booking = $_eventObject->booking;
	    			
	    			$donation->__set('booking_id', $booking->getId());
	    			Donator_Controller_Donation::getInstance()->update($donation);
		    	}
    		
    		}catch(Exception $e){
    			echo $e->__toString();
    		}
    	}
    	
   	 	if($_eventObject instanceof Billing_Events_PaymentBooked){
    		// TODO: NRW
    		$payment = $_eventObject->payment;
    		$booking = $_eventObject->booking;
		   	 try{
	    			// TODO: NRW
	    			$receipt = $payment->getForeignRecordBreakNull('receipt_id', Billing_Controller_Receipt::getInstance());
		    		if($receipt && $receipt->isDonation()){
			    		$donation = $receipt->getDonation();
			    		
		    			$donation->__set('payment_id', $payment->getId());
		    			Donator_Controller_Donation::getInstance()->update($donation);
		    			
			    	}
	    		
	    		}catch(Exception $e){
	    			echo $e->__toString();
	    		}
    	}
    	
   		if($_eventObject instanceof Billing_Events_SetAccountsBankTransferDetected){
    		try{
    			Donator_Controller_FundMaster::getInstance()->onSetAccountBankTransferDetected($_eventObject);
    		}catch(Exception $e){
				echo $e->__toString();
    		}
    	}
    }
        
    /**
     * creates the initial folder for new accounts
     *
     * @param mixed[int|Tinebase_Model_User] $_account   the accountd object
     * @return Tinebase_Record_RecordSet of subtype Tinebase_Model_Container
     */
    public function createPersonalFolder($_account)
    {
    }
    
    /**
     * delete all personal user folders and the contacts associated with these folders
     *
     * @param Tinebase_Model_User $_account the accountd object
     * @todo implement and write test
     */
    public function deletePersonalFolder($_account)
    {
    }
}
