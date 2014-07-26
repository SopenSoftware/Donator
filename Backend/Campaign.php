<?php
class Donator_Backend_Campaign extends Tinebase_Backend_Sql_Abstract
{
    /**
     * Table name without prefix
     *
     * @var string
     */
    protected $_tableName = 'fund_campaign';
    
    /**
     * Model name
     *
     * @var string
     */
    protected $_modelName = 'Donator_Model_Campaign';

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
      	if($record->__get('project_id')){
    		$this->appendForeignRecordToRecord($record, 'project_id', 'project_id', 'id', new Donator_Backend_Project());
    	}
        if($record->__get('donation_account_id')){
    		$this->appendForeignRecordToRecord($record, 'donation_account_id', 'donation_account_id', 'id', new Donator_Backend_DonationAccount());
    	}
            if($record->__get('donation_unit_id')){
    		$this->appendForeignRecordToRecord($record, 'donation_unit_id', 'donation_unit_id', 'id', new Donator_Backend_DonationUnit());
    	}    	
       	if($record->__get('responsible_contact_id')){
    		$this->appendForeignRecordToRecord($record, 'responsible_contact_id', 'responsible_contact_id', 'id', Addressbook_Backend_Factory::factory(Addressbook_Backend_Factory::SQL));
    	}
        if($record->__get('gratuation_template_id')){
    		$this->appendForeignRecordToRecord($record, 'gratuation_template_id', 'gratuation_template_id', 'id', new DocManager_Backend_Template());
    	}    	
     	if($record->__get('erp_proceed_account_id')){
    		$this->appendForeignRecordToRecord($record, 'erp_proceed_account_id', 'erp_proceed_account_id', 'id', new Billing_Backend_AccountSystem());
    	}
    	
      	if($record->__get('bank_account_system_id')){
    		$this->appendForeignRecordToRecord($record, 'bank_account_system_id', 'bank_account_system_id', 'id', new Billing_Backend_AccountSystem());
         }
         
      	if($record->__get('debit_account_system_id')){
    		$this->appendForeignRecordToRecord($record, 'debit_account_system_id', 'debit_account_system_id', 'id', new Billing_Backend_AccountSystem());
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
}
?>