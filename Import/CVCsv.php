<?php
class Donator_Import_CVCsv extends Tinebase_Import_Csv_Abstract
{
    /**
     * add some more values (container id)
     *
     * @return array
     */
    protected function _addData($recordData)
    {        
    	Tinebase_Core::setExecutionLifeTime(36000);
    	$result = array();

    	$fmController = Donator_Controller_FundMaster::getInstance();
    	$campaignController = Donator_Controller_Campaign::getInstance();
    	
    	$fm = $fmController->getByContactId((int)$recordData['contact_id']-852);
    	$fmId = $fm->getId();
    	unset($recordData['contact_id']);
    	$result['fundmaster_id'] = $fmId;
    	
    	$result['donation_amount'] = str_replace(' €', '', $recordData['amount']);
    	$result['donation_amount'] = trim(str_replace(',', '.', $result['donation_amount']));
    	//$result['donation_amount'] = (float)str_replace(',','.',$result['donation_amount']);
    	
    	if(!is_numeric($result['donation_amount'])){
    		throw new Exception('Spendenbetrag nicht numerisch');
    	}
    	
    	if(self::validate_Date($recordData['date'], $dt)){
    		$result['donation_date'] = $dt;
    	}else{
    		$result['donation_date'] = new Zend_Date('2009-12-31','yyyy-M-dd');
    	}
    	$result['confirmation_date'] = new Zend_Date('2009-12-31','yyyy-M-dd');
    	$result['thanks_date'] = new Zend_Date('2009-12-31','yyyy-M-dd');
    	$result['confirmation_kind'] = 'CONFIRMATION_NO';
    	$result['gratuation_kind'] = 'THANK_NO';
		
    	$campaign = null;
    	try{
    		$campaignNr = trim($recordData['campaign']);
//    		if($campaignNr == 185){
//    			$campaignNr = 184;
//    		}
	    	$campaign = $campaignController->getByCampaignNumber($campaignNr);
	    	$campaignId = $campaign->getId();
    	}catch(Exception $e){
    		$campaign = $campaignController->getByCampaignNumber(0);
	    	$campaignId = $campaign->getId();
    	}
		$result['campaign_id'] = $campaignId;
    	
    	$baController = Donator_Controller_DonationAccount::getInstance();
    	$result['donation_account_id'] = $baController->getByBankAccountNumber($recordData['bank_account'])->getId();

        return $result;	
    }  
    
public static function validate_Date($date, &$dt = null){
	if(strlen($date)>10 || !strpos($date,'.') || (substr_count($date, '.')!=2) ){
		return false;
	}
	try{
		$dt = new Zend_Date($date,'d.M.Y');
		return true;
	}catch(Exception $e){
		return false;
	}
}
    
//	protected function _importRecord($_recordData, &$_result)
//    {
//        $record = new $this->_modelName($_recordData, TRUE);
//        print_r($record);
//        exit;
////        if ($record->isValid()) {
////            if (! $this->_options['dryrun']) {
////			print_r($recordData);
////			print_r($_result);
////				//$this->_controller->createWithBrevetationMaster($record, $_recordData['contact_id'], $_recordData);
////                //$record = call_user_func(array($this->_controller, $this->_createMethod), $record);
////            } else {
////                $_result['results']->addRecord($record);
////            }
////            
////            $_result['totalcount']++;
////            
////        } else {
////            if (Tinebase_Core::isLogLevel(Zend_Log::DEBUG)) Tinebase_Core::getLogger()->debug(__METHOD__ . '::' . __LINE__ . ' ' . print_r($record->toArray(), true));
////            throw new Tinebase_Exception_Record_Validation('Imported record is invalid.');
////        }
//    }
}
?>