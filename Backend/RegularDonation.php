<?php
class Donator_Backend_RegularDonation extends Tinebase_Backend_Sql_Abstract
{
    /**
     * Table name without prefix
     *
     * @var string
     */
    protected $_tableName = 'fund_regular_donation';
    
    /**
     * Model name
     *
     * @var string
     */
    protected $_modelName = 'Donator_Model_RegularDonation';

    /**
     * if modlog is active, we add 'is_deleted = 0' to select object in _getSelect()
     *
     * @var boolean
     */
    protected $_modlogActive = false;
    
    public function search(Tinebase_Model_Filter_FilterGroup $_filter = NULL, Tinebase_Model_Pagination $_pagination = NULL, $_onlyIds = FALSE){
    	// TODO HH: no ids searchable
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
     
     /*if(!$record->__get('next_date')){
     	 $begin = new Zend_Date($record->__get('begin_date'));
     }else{
     	 $begin = new Zend_Date($record->__get('next_date'));
     }
     
     $count = Donator_Controller_Donation::getInstance()->countDonationsWithinTimeframe($begin, null, $record->getForeignId('fundmaster_id'));
     $record->__set('control_count', $count['count']);
     $record->__set('control_sum', $count['sum']);
     */
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
    
	protected function _getSelect($_cols = '*', $_getDeleted = FALSE)
    {        
        $select = $this->_db->select();

        if (is_array($_cols) && isset($_cols['count'])) {
            $cols = array(
                'count'                => 'COUNT(*)'
            );
            
        } else {
            $cols = array_merge(
                (array)$_cols, 
                array(
                	'bic' => 'ba.bic',
		        	'iban' => 'bact.iban',
		        	'bank_account_number' => 'bact.number',
		        	'bank_account_bank_code' => 'ba.code',
		        	'bank_account_name' => 'bact.name',
		        	'bank_account_bank_name' => 'ba.name',
		        	'sepa_mandate_id'	=> 'sepa.id',
		        	'sepa_mandate_ident'	=> 'sepa.mandate_ident',
		        	'sepa_signature_date'   => 'sepa.signature_date'
                )
            );
        }
        
        $select->from(array($this->_tableName => $this->_tablePrefix . $this->_tableName), $cols);
        
		$select->joinLeft(array('bact' => $this->_tablePrefix . 'bill_bank_account'),
			$this->_db->quoteIdentifier( 'bank_account_id') . ' = ' . $this->_db->quoteIdentifier('bact.id'),
			array()
		);
		
		$select->joinLeft(array('bactusage' => $this->_tablePrefix . 'bill_bank_account_usage'),
			$this->_db->quoteIdentifier( 'bactusage.bank_account_id') . ' = ' . $this->_db->quoteIdentifier('bact.id').' AND '.
			$this->_db->quoteIdentifier('bactusage.id') . ' = ' . $this->_db->quoteIdentifier('bactusage.regular_donation_id'),
			array()
		);
		
		$select->joinLeft(array('sepa' => $this->_tablePrefix . 'bill_sepa_mandate'),
			$this->_db->quoteIdentifier('bactusage.sepa_mandate_id') . ' = ' . $this->_db->quoteIdentifier('sepa.id'),
			array()
		);
        
        $select->joinLeft(array('ba' => $this->_tablePrefix . 'bill_bank'),
			$this->_db->quoteIdentifier('bact.bank_id') . ' = ' . $this->_db->quoteIdentifier('ba.id'),
			array()
		);      
        
        if (!$_getDeleted && $this->_modlogActive) {
            // don't fetch deleted objects
            $select->where($this->_db->quoteIdentifier($this->_tableName . '.is_deleted') . ' = 0');                        
        }
        
        return $select;
    }
}
?>