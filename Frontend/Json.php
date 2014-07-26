<?php
class Donator_Frontend_Json extends Tinebase_Frontend_Json_Abstract{
    protected $_donatorController = NULL;
	protected $_donatorFundMasterController = NULL;
    protected $_campaignController = NULL;
	protected $_projectController = NULL;
    
    protected $_config = NULL;
    protected $_userTimezone = null;
    protected $_serverTimezone = null;
    
    /**
     * the constructor
     *
     */
    public function __construct()
    {
        $this->_applicationName = 'Donator';
        $this->_donatorController = Donator_Controller_Donation::getInstance();
        $this->_donatorFundMasterController = Donator_Controller_FundMaster::getInstance();
        $this->_campaignController = Donator_Controller_Campaign::getInstance();
        $this->_projectController = Donator_Controller_Project::getInstance();
        $this->_donationUnitController = Donator_Controller_DonationUnit::getInstance();
        $this->_donationAccountController = Donator_Controller_DonationAccount::getInstance();
        $this->_regularDonationController = Donator_Controller_RegularDonation::getInstance();
    }
    
    public function getDonation($id){
    	if(!$id ) {
            $member = $this->_donatorController->getEmptyDonation();
        } else {
            $member = $this->_donatorController->get($id);
        }
        $memberData = $member->toArray();
        
        return $memberData;
    }
    
    public function searchDonations($filter,$paging){
    	$result = $this->_search($filter,$paging,$this->_donatorController,'Donator_Model_DonationFilter');
    	$result['sum'] = $result['totalcount']['sum'];
        $result['totalcount'] = $result['totalcount']['count'];
        
        return $result;
    }
    
    public function deleteDonations($ids){
    	 return $this->_delete($ids, $this->_donatorController);
    }

    public function saveDonation($recordData){
    	$member = new Donator_Model_Donation();
        $member->setFromArray($recordData);
        
        if (!$member['id']) {
            $member = $this->_donatorController->create($member);
        } else {
            $member = $this->_donatorController->update($member);
        }
        
        $result =  $this->getDonation($member->getId());
        return $result;
    }
    
    public function reverseDonation($donationId){
    	return $this->_donatorController->reverseDonation($donationId)->toArray();
    }
    
 	public function getRegularDonation($id){
    	if(!$id ) {
            $member = $this->_regularDonationController->getEmptyRegularDonation();
        } else {
            $member = $this->_regularDonationController->get($id);
        }
        $memberData = $member->toArray();
        
        return $memberData;
    }
    
    public function searchRegularDonations($filter,$paging){
    	return $this->_search($filter,$paging,$this->_regularDonationController,'Donator_Model_RegularDonationFilter');
    }
    
    public function deleteRegularDonations($ids){
    	 return $this->_delete($ids, $this->_regularDonationController);
    }
    
    public function saveRegularDonation($recordData){
    	$member = new Donator_Model_RegularDonation();
        $member->setFromArray($recordData);
        
        if (!$member['id']) {
            $member = $this->_regularDonationController->create($member);
        } else {
            $member = $this->_regularDonationController->update($member);
        }
        
        $result =  $this->getRegularDonation($member->getId());
        return $result;
    }
    
    public function getCampaign($id){
    	if(!$id ) {
            $member = $this->_campaignController->getEmptyCampaign();
        } else {
            $member = $this->_campaignController->get($id);
        }
        $memberData = $member->toArray();
        
        return $memberData;
    }
    
    public function searchCampaigns($filter,$paging){
    	return $this->_search($filter,$paging,$this->_campaignController,'Donator_Model_CampaignFilter');
    }
    
    public function deleteCampaigns($ids){
    	 return $this->_delete($ids, $this->_campaignController);
    }
    
    public function saveCampaign($recordData){
    	$member = new Donator_Model_Campaign();
        $member->setFromArray($recordData);
        
        if (!$member['id']) {
            $member = $this->_campaignController->create($member);
        } else {
            $member = $this->_campaignController->update($member);
        }
        
        $result =  $this->getCampaign($member->getId());
        return $result;
    }
    
    
    public function getFundMaster($id){
    	if(!$id ) {
            $memberFundMaster = $this->_donatorFundMasterController->getEmptyFundMaster();
        } else {
            $memberFundMaster = $this->_donatorFundMasterController->get($id);
        }
        $memberFundMasterData = $memberFundMaster->toArray();
        
        return $memberFundMasterData;
    }
    
    public function searchFundMasters($filter,$paging){
    	return $this->_search($filter,$paging,$this->_donatorFundMasterController,'Donator_Model_FundMasterFilter');
    }
    
    public function deleteFundMasters($ids){
    	 return $this->_delete($ids, $this->_donatorFundMasterController);
    }
    
    public function saveFundMaster($recordData){
    	$memberFundMaster = new Donator_Model_FundMaster();
        $memberFundMaster->setFromArray($recordData);
        
        if (empty($memberFundMaster->id)) {
            $memberFundMaster = $this->_donatorFundMasterController->create($memberFundMaster);
        } else {
            $memberFundMaster = $this->_donatorFundMasterController->update($memberFundMaster);
        }
        
        $result =  $this->getFundMaster($memberFundMaster->getId());
        return $result;
    }
    
    public function getFundMasterByContactId($contactId){
    	try{
    		$memberFundMaster = $this->_donatorFundMasterController->getByContactId($contactId);
    		return array(
    			'success' => true,
    			'result' => $memberFundMaster->toArray()
    		);
    	}catch(Exception $e){
    		return array(
    			'success' => false,
    			'result' => null
    		);
    	}
    	
    }
    
    public function getProject($id){
    	if(!$id ) {
            $member = $this->_projectController->getEmptyProject();
        } else {
            $member = $this->_projectController->get($id);
        }
        $memberData = $member->toArray();
        
        return $memberData;
    }
    
    public function searchProjects($filter,$paging){
    	return $this->_search($filter,$paging,$this->_projectController,'Donator_Model_ProjectFilter');
    }
    
    public function deleteProjects($ids){
    	 return $this->_delete($ids, $this->_projectController);
    }
    
    public function saveProject($recordData){
    	$member = new Donator_Model_Project();
        $member->setFromArray($recordData);
        
        if (!$member['id']) {
            $member = $this->_projectController->create($member);
        } else {
            $member = $this->_projectController->update($member);
        }
        
        $result =  $this->getProject($member->getId());
        return $result;
    }
    
    public function getDonationUnit($id){
    	if(!$id ) {
            $member = $this->_donationUnitController->getEmptyDonationUnit();
        } else {
            $member = $this->_donationUnitController->get($id);
        }
        $memberData = $member->toArray();
        
        return $memberData;
    }
    
    public function searchDonationUnits($filter,$paging){
    	return $this->_search($filter,$paging,$this->_donationUnitController,'Donator_Model_DonationUnitFilter');
    }
    
    public function deleteDonationUnits($ids){
    	 return $this->_delete($ids, $this->_donationUnitController);
    }
    
    public function saveDonationUnit($recordData){
    	$member = new Donator_Model_DonationUnit();
        $member->setFromArray($recordData);
        
        if (!$member['id']) {
            $member = $this->_donationUnitController->create($member);
        } else {
            $member = $this->_donationUnitController->update($member);
        }
        
        $result =  $this->getDonationUnit($member->getId());
        return $result;
    }
    
    public function getDonationAccount($id){
    	if(!$id ) {
            $member = $this->_donationAccountController->getEmptyDonationAccount();
        } else {
            $member = $this->_donationAccountController->get($id);
        }
        $memberData = $member->toArray();
        
        return $memberData;
    }
    
    public function searchDonationAccounts($filter,$paging){
    	return $this->_search($filter,$paging,$this->_donationAccountController,'Donator_Model_DonationAccountFilter');
    }
    
    public function deleteDonationAccounts($ids){
    	 return $this->_delete($ids, $this->_donationAccountController);
    }
    
    public function saveDonationAccount($recordData){
    	$member = new Donator_Model_DonationAccount();
        $member->setFromArray($recordData);
        
        if (!$member['id']) {
            $member = $this->_donationAccountController->create($member);
        } else {
            $member = $this->_donationAccountController->update($member);
        }
        
        $result =  $this->getDonationAccount($member->getId());
        return $result;
    }    
    
    public function executeRegularDonations(){
    	return Donator_Controller_RegularDonation::getInstance()->generateDTA();
    }
    
	public function improveRegularDonations(){
    	return Donator_Controller_RegularDonation::getInstance()->improveRegularDonations();
    }
    
    public function executeRegularDonationNow($regularDonationId){
    	try{
    		return Donator_Controller_RegularDonation::getInstance()->executeRegularDonationNow($regularDonationId);
    		
    	}catch(Exception $e){
    		return array(
    			'state' => 'failure',
    			'result' => null
    		);
    	}
    }
    
    public function reverseLastRegularDonationExecution($regularDonationId){
    	try{
    		Donator_Controller_RegularDonation::getInstance()->reverseLastRegularDonationExecution($regularDonationId);
    		return array(
    			'state' => 'success',
    			'result' => null
    		);
    	}catch(Exception $e){
    		return array(
    			'state' => 'failure',
    			'errorMessage' => $e->getMessage(),
    			'errorInfo' => $e->getTrace(),
    			'result' => null
    		);
    	}
    }
}

?>