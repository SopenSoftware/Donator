<?php
class Donator_Backend_Donation extends Tinebase_Backend_Sql_Abstract
{
    /**
     * Table name without prefix
     *
     * @var string
     */
    protected $_tableName = 'fund_donation';
    
    /**
     * Model name
     *
     * @var string
     */
    protected $_modelName = 'Donator_Model_Donation';

    /**
     * if modlog is active, we add 'is_deleted = 0' to select object in _getSelect()
     *
     * @var boolean
     */
    protected $_modlogActive = false;
    
    public function search(Tinebase_Model_Filter_FilterGroup $_filter = NULL, Tinebase_Model_Pagination $_pagination = NULL, $_onlyIds = FALSE){
    	// no ids searchable
    	// check if needed anywhere and modify if so
    	$recordSet = parent::search($_filter,$_pagination,$_onlyIds);
    	
    	if( ($recordSet instanceof Tinebase_Record_RecordSet) && ($recordSet->count()>0)){
    		$it = $recordSet->getIterator();
    		foreach($it as $key => $record){
				$this->appendDependentRecords($record);				
    		}
    	}
    	return $recordSet;
    }
    
    /**
     * Append contacts by foreign key (record embedding)
     * 
     * @param Tinebase_Record_Abstract $record
     * @return void
     */
    protected function appendDependentRecords($record){
        if($record->__get('fundmaster_id')){
    		$this->appendForeignRecordToRecord($record, 'fundmaster_id', 'fundmaster_id', 'id', new Donator_Backend_FundMaster());
			// TODO HH: kind of strange way of embedding 2. level foreign record
    		$fundMaster = $record->__get('fundmaster_id');
    		try{
    			if(is_object($fundMaster)){
    				$contactId = $fundMaster->__get('contact_id');
    			}else{
    				$contactId = $fundMaster->contact_id;
    			}
    			$contact = Addressbook_Controller_Contact::getInstance()->get($contactId);
    			if(is_object($fundMaster)){
    				$fundMaster->__set('contact_id',$contact->toArray());
    			}else{
    				$fundMaster->contact_id = $contact->toArray();
    			}
    		}catch(Exception $e){
    		}
			$record->__set('fundmaster_id',$fundMaster);
        }
      	if($record->__get('campaign_id')){
    		$this->appendForeignRecordToRecord($record, 'campaign_id', 'campaign_id', 'id', new Donator_Backend_Campaign());
    		// TODO HH: kind of strange way of embedding 2. level foreign record
    		$campaign = $record->__get('campaign_id');
    		try{
    			if(is_object($campaign)){
    				$projectId = $campaign->__get('project_id');
    			}else{
    				$projectId = $campaign->project_id;
    			}
    			$project = Donator_Controller_Project::getInstance()->get($projectId);
    			if(is_object($campaign)){
    				$campaign->__set('project_id',$project->toArray());
    			}else{
    				$campaign->project_id = $project->toArray();
    			}
    		}catch(Exception $e){
    		}
			$record->__set('campaign_id',$campaign);
    	}
        if($record->__get('donation_account_id')){
    		$this->appendForeignRecordToRecord($record, 'donation_account_id', 'donation_account_id', 'id', new Donator_Backend_DonationAccount());
    	}
   		 if($record->__get('erp_proceed_account_id')){
    		$this->appendForeignRecordToRecord($record, 'erp_proceed_account_id', 'erp_proceed_account_id', 'id', new Billing_Backend_AccountSystem());
    	}
    	if($record->__get('payment_id')){
    		$this->appendForeignRecordToRecord($record, 'payment_id', 'payment_id', 'id', new Billing_Backend_Payment());
        }
    	if($record->__get('booking_id')){
    		$this->appendForeignRecordToRecord($record, 'booking_id', 'booking_id', 'id', new Billing_Backend_Booking());
        }
		if($record->__get('fee_group_id')){
    		$this->appendForeignRecordToRecord($record, 'fee_group_id', 'fee_group_id', 'id', new Membership_Backend_FeeGroup());
    	} 
    	
    }
    
    public function reverseDonation($donationId){
    	
    }
    
    /**
     * Get Donator record by id (with embedded dependent contacts)
     * 
     * @param int $id
     */
    public function get($id, $_getDeleted = FALSE){
    	$record = parent::get($id, $_getDeleted);
    	$this->appendDependentRecords($record);
    	return $record;
    }
    
    public function getCollectedDonationsToBeConfirmed($fundMasterId = null, $idsOnly = false, $additionalFilters = array() ){
    	return $this->getDonationsToBeConfirmed($fundMasterId , 'CONFIRMATION_COLLECT', $idsOnly, $additionalFilters);
    }
    
    /**
     * 
     * Get the donations to be confirmed
     * Includes any donation which has an empty confirmation_date
     * 
     * 
     * @param string $fundMasterId	Optional: fundmasterId -> filter for certain donator
     */
    public function getDonationsToBeConfirmed($fundMasterId = null, $type = 'CONFIRMATION_SINGLE', $idsOnly = false, $additionalFilters = array() ){
		if(!$fundMasterId){
			$pagination = new Tinebase_Model_Pagination(array(
				'sort' => array('adr_one_postalcode', 'n_family')
			));
		}else{
			$pagination = new Tinebase_Model_Pagination(array(
				'sort' => array('donation_date', 'org_name'),
				'order' => 'DESC'
			));
		}
		$filter = new Tinebase_Model_Filter_FilterGroup($additionalFilters);
    	$filter->addFilter(new Tinebase_Model_Filter_Date(
    		'confirmation_date',
    		'isnull'
    	));
    	/*$filter->addFilter(new Tinebase_Model_Filter_Date(
    		'donation_date',
    		'afterAtOrNull',
    		'2012-01-01'
    	));
    	$filter->addFilter(new Tinebase_Model_Filter_Date(
    		'donation_date',
    		'beforeAtOrNull',
    		'2012-12-31'
    	));*/
    	$filter->addFilter(new Tinebase_Model_Filter_Text(
    		'confirmation_kind',
    		'equals',
    		$type
    	));
    	$filter->addFilter(new Tinebase_Model_Filter_Bool(
    		'is_cancelled',
    		'equals',
    		0
    	));
    	$filter->addFilter(new Tinebase_Model_Filter_Bool(
    		'is_cancellation',
    		'equals',
    		0
    	));
    	$filter->addFilter(new Tinebase_Model_Filter_Int(
    		'donation_amount',
    		'greater',
    		4.99
    	));
    	if(!is_null($fundMasterId)){
    		$filter->addFilter(new Tinebase_Model_Filter_Id('fundmaster_id', 'equals', $fundMasterId));
    	}
    	return parent::search($filter,$pagination);
    }
    
    public function countDonationsWithinTimeframe(Zend_Date $beginDate, Zend_Date $endDate = null, $fundMasterId = null){
    	
    	$beginDate = $beginDate->toString('yyyy-MM-dd');
    	
    	$filter = new Tinebase_Model_Filter_FilterGroup(array());
    	
   		$filter->addFilter(new Tinebase_Model_Filter_Date(
    		'donation_date',
    		'afterOrAt',
    		$beginDate
    	));
    	if($endDate){
    		$endDate = $endDate->toString('yyyy-MM-dd');
	    	$filter->addFilter(new Tinebase_Model_Filter_Date(
	    		'donation_date',
	    		'beforeOrAt',
	    		$endDate
	    	));
    	}
    	
    	$filter->addFilter(new Tinebase_Model_Filter_Bool(
    		'is_cancelled',
    		'equals',
    		0
    	));
    	$filter->addFilter(new Tinebase_Model_Filter_Bool(
    		'is_cancellation',
    		'equals',
    		0
    	));
    	$filter->addFilter(new Tinebase_Model_Filter_Int(
    		'donation_amount',
    		'greater',
    		0
    	));
    	if(!is_null($fundMasterId)){
    		$filter->addFilter(new Tinebase_Model_Filter_ForeignId('fundmaster_id', 'equals', $fundMasterId));
    	}
    	
    	return $this->searchCount($filter);
    }
    
    /**
     * 
     * Get the donations to be gratuated
     * Includes any donation which has an empty gratuation_date
     * 
     * 
     * @param string $fundMasterId	Optional: fundmasterId -> filter for certain donator
     */
    public function getDonationsToBeGratuated($fundMasterId = null, $type = 'THANK_STANDARD', $additionalFilters = array() ){
		$pagination = new Tinebase_Model_Pagination(array(
			'sort' => array('adr_one_postalcode', 'n_family')
		));
    	$filter = new Tinebase_Model_Filter_FilterGroup($additionalFilters);
    	$filter->addFilter(new Tinebase_Model_Filter_Date(
    		'thanks_date',
    		'isnull'
    	));
    	$filter->addFilter(new Tinebase_Model_Filter_Text(
    		'gratuation_kind',
    		'equals',
    		$type
    	));
    /*	$filter->addFilter(new Tinebase_Model_Filter_Date(
    		'donation_date',
    		'afterAtOrNull',
    		'2012-01-01'
    	));
    	$filter->addFilter(new Tinebase_Model_Filter_Date(
    		'donation_date',
    		'beforeAtOrNull',
    		'2012-12-31'
    	));*/
    	$filter->addFilter(new Tinebase_Model_Filter_Bool(
    		'is_cancelled',
    		'equals',
    		0
    	));
    	$filter->addFilter(new Tinebase_Model_Filter_Bool(
    		'is_cancellation',
    		'equals',
    		0
    	));
    	$filter->addFilter(new Tinebase_Model_Filter_Int(
    		'donation_amount',
    		'greater',
    		0
    	));
    	if(!is_null($fundMasterId)){
    		$filter->addFilter(new Tinebase_Model_Filter_ForeignId('fundmaster_id', 'equals', $fundMasterId));
    	}
    	return parent::search($filter,$pagination);
    }
    
    public function searchCount(Tinebase_Model_Filter_FilterGroup $_filter)
    {        
        $select = $this->_getSelect(array('count' => 'COUNT(*)'));
        $this->_addFilter($select, $_filter);
        
        // fetch complete row here
        $result = $this->_db->fetchRow($select);
        return $result;        
    } 
    
	protected function _getSelect($_cols = '*', $_getDeleted = FALSE)
    {     
        $select = $this->_db->select();    
        
        if (is_array($_cols) && isset($_cols['count'])) {
            $cols = array(
            	'count'             => 'COUNT(*)',
                'sum'               => 'SUM(donation_amount)'
            );
            
        }else{
        	$cols = (array)$_cols;
        	$cols = array_merge(
                (array)$_cols, 
                array(
					'contact_id' => 'co.id',
                	'n_family' => 'co.n_family',
                	'org_name' => 'co.org_name',
                	'adr_one_postalcode'    	=> 'co.adr_one_postalcode',
                	'adr_one_street'  => 'co.adr_one_street',
                	'adr_one_locality'  => 'co.adr_one_locality'
                )
            );
        }

        $select->from(array($this->_tableName => $this->_tablePrefix . $this->_tableName), $cols);
        
        $select->joinLeft(array('fm' => $this->_tablePrefix . 'fund_master'),
        	$this->_db->quoteIdentifier('fm.id') . ' = ' . $this->_db->quoteIdentifier($this->_tableName . '.fundmaster_id'),
        array()); 
        
       	$select->joinLeft(array('co' => $this->_tablePrefix . 'addressbook'),
       		$this->_db->quoteIdentifier('co.id') . ' = ' . $this->_db->quoteIdentifier('fm.contact_id'),
       	array()); 
       /* if (!$_getDeleted && $this->_modlogActive) {
            // don't fetch deleted objects
            $select->where($this->_db->quoteIdentifier($this->_tableName . '.is_deleted') . ' = 0');                        
        }        */
        
        //if (Tinebase_Core::isLogLevel(Zend_Log::DEBUG)) Tinebase_Core::getLogger()->debug(__METHOD__ . '::' . __LINE__ . ' ' . $select->__toString());
        
        return $select; 
    }
}
?>