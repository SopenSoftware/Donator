<?php
class Donator_Model_ProjectFilter extends Tinebase_Model_Filter_FilterGroup// implements Tinebase_Model_Filter_AclFilter
{
    /**
     * @var string application of this filter group
     */
    protected $_applicationName = 'Donator';
    
    protected $_className = 'Donator_Model_ProjectFilter';
    
    /**
     * @var array filter model fieldName => definition
     */
    protected $_filterModel = array(
        'id' => array('filter' => 'Tinebase_Model_Filter_Id'),
    	'query'          => array('filter' => 'Tinebase_Model_Filter_Query', 'options' => array('fields' => array('name', 'description')))
    );
    
    public function toArray($_valueToJson = false)
    {
        $result = parent::toArray($_valueToJson);
        
        foreach ($result as &$filterData) {
            if ($filterData['field'] == 'id' && $_valueToJson == true && ! empty($filterData['value'])) {
                //if (Tinebase_Core::isLogLevel(Zend_Log::DEBUG)) Tinebase_Core::getLogger()->debug(__METHOD__ . '::' . __LINE__ . ' value:' . print_r($filterData['value'], true));
                try {
                    $filterData['value'] = Donator_Controller_Project::getInstance()->get($filterData['value'])->toArray();
                } catch (Tinebase_Exception_NotFound $nfe) {
                    if (Tinebase_Core::isLogLevel(Zend_Log::INFO)) Tinebase_Core::getLogger()->INFO(__METHOD__ . '::' . __LINE__ . " could not find and resolve timeaccount {$filterData['value']}");
                }
            }
        }
        
        return $result;
    }
}
?>