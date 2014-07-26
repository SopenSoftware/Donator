<?php
class Donator_Backend_FundMaster extends Tinebase_Backend_Sql_Abstract
{
    /**
     * Table name without prefix
     *
     * @var string
     */
    protected $_tableName = 'fund_master';
    
    /**
     * Model name
     *
     * @var string
     */
    protected $_modelName = 'Donator_Model_FundMaster';

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
      	if($record->__get('contact_id')){
      		try{
    		$this->appendForeignRecordToRecord($record, 'contact_id', 'contact_id', 'id', Addressbook_Backend_Factory::factory(Addressbook_Backend_Factory::SQL));
    		//$contact = $record->getForeignRecord('contact_id', Addressbook_Controller_Contact::getInstance());
    		//$record->__set('adr_one_street', $contact->__get('adr_one_street'));
    		//$record->__set('adr_one_postalcode', $contact->__get('adr_one_postalcode'));
    		//$record->__set('adr_one_locality', $contact->__get('adr_one_locality'));
      		}catch(Exception $e){
      			// silent failure: ok
      		}
    		
    	}
        if($record->__get('first_contact_campaign_id')){
    		$this->appendForeignRecordToRecord($record, 'first_contact_campaign_id', 'first_contact_campaign_id', 'id', new Donator_Backend_Campaign());
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
    
  	public function getByContactId($contactId){
    	$record = $this->getByProperty($contactId, 'contact_id');
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
                	'adr_one_postalcode'    	=> 'co.adr_one_postalcode',
                	'adr_one_street'  => 'co.adr_one_street',
                	'adr_one_locality'  => 'co.adr_one_locality'
                )
            );
        }
        
        $select->from(array($this->_tableName => $this->_tablePrefix . $this->_tableName), $cols);
        
    	$select->joinLeft(array('co' => $this->_tablePrefix . 'addressbook'),
                    $this->_db->quoteIdentifier('co.id') . ' = ' . $this->_db->quoteIdentifier($this->_tableName . '.contact_id'),
                    array()); 
                    
        if (!$_getDeleted && $this->_modlogActive) {
            // don't fetch deleted objects
            $select->where($this->_db->quoteIdentifier($this->_tableName . '.is_deleted') . ' = 0');                        
        }
        
        return $select;
    }
}
?>