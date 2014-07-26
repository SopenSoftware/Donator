<?php
class Donator_Model_FundMasterFilter extends Tinebase_Model_Filter_FilterGroup implements Tinebase_Model_Filter_AclFilter
{
    /**
     * @var string application of this filter group
     */
    protected $_applicationName = 'Donator';
    
    protected $_className = 'Donator_Model_FundMasterFilter';
    
    /**
     * @var array filter model fieldName => definition
     */
    protected $_filterModel = array(
    	'id'          => array('filter' => 'Tinebase_Model_Filter_Id'),
    	'contact_id' => array('filter' => 'Tinebase_Model_Filter_ForeignId', 
            'options' => array(
                'filtergroup'       => 'Addressbook_Model_ContactFilter', 
                'controller'        => 'Addressbook_Controller_Contact'
            )
        ),
       'query' 		=> array('filter' => 'Donator_Model_FundMasterContactFilter'),
        'confirmation_kind' 		=> array('filter' => 'Tinebase_Model_Filter_Text'),
        'created_by'    => array('filter' => 'Tinebase_Model_Filter_User'),
        'showHidden' => array('custom' => true)
    );
    
    /**
     * set options
     *
     * @param array $_options
     */
    protected function _setOptions(array $_options)
    {
        $_options['showHidden']         = array_key_exists('showHidden', $_options)      ? $_options['showHidden']      : FALSE;
        parent::_setOptions($_options);
    }
    
/**
     * appends custom filters to a given select object
     * 
     * @param  Zend_Db_Select                    $_select
     * @param  Tinebase_Backend_Sql_Abstract     $_backend
     * @return void
     */
    public function appendFilterSql($_select, $_backend)
    {
        // manage show hidden
        $this->_appendShowHiddenSql($_select);
        
    }
    
    /**
     * append show hidden filter
     *
     * @param Zend_Db_Select $_select
     */
    protected function _appendShowHiddenSql($_select)
    {
        $showHidden = false;
        foreach ($this->_customData as $customData) {
            if ($customData['field'] == 'showHidden' && $customData['value'] == true) {
                $showHidden = true;
            }
        }
        if($showHidden || $this->_options['showHidden']){
            // nothing to filter
        } else {
            $_select->where(Tinebase_Core::getDb()->quoteIdentifier('is_fm_hidden') . ' = 0');
        }
    }
}
?>