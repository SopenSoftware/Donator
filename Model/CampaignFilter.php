<?php
class Donator_Model_CampaignFilter extends Tinebase_Model_Filter_FilterGroup// implements Tinebase_Model_Filter_AclFilter
{
    /**
     * @var string application of this filter group
     */
    protected $_applicationName = 'Donator';
    
    protected $_className = 'Donator_Model_CampaignFilter';
    
    /**
     * @var array filter model fieldName => definition
     */
    protected $_filterModel = array(
        'id' => array('filter' => 'Tinebase_Model_Filter_Id'),
    	'query'          => array('filter' => 'Tinebase_Model_Filter_Query', 'options' => array('fields' => array('name', 'description','campaign_nr'))),
	    'project_id' => array('filter' => 'Tinebase_Model_Filter_ForeignId', 
	        'options' => array(
	                'filtergroup'       => 'Donator_Model_ProjectFilter', 
	                'controller'        => 'Donator_Controller_Project'
	            )
	    ),
	    'is_closed'          => array('filter' => 'Tinebase_Model_Filter_Bool')
    );
}
?>