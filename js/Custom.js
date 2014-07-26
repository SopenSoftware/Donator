Ext.ns('Tine.Donator.Custom');

Tine.Donator.Custom.getCampaignRecordPicker = function(id, config){
	if(!id){
		id = 'campaignEditorField';
	}
	return new Tine.Tinebase.widgets.form.RecordPickerComboBox(Ext.apply({
		id:id,
		disabledClass: 'x-item-disabled-view',
		recordClass: Tine.Donator.Model.Campaign,
		fieldLabel: 'Artikel',
	    allowBlank:false,
	    autoExpand: true,
	    triggerAction: 'all',
	    selectOnFocus: true,
	    itemSelector: 'div.search-item',
	    appendFilters: [],
	    onBeforeQuery: function(qevent){
	    	this.store.baseParams.filter = [
	            {field: 'query', operator: 'contains', value: qevent.query }
	        ];
	    	this.store.baseParams.filter = this.store.baseParams.filter.concat(this.appendFilters);
	    	this.store.baseParams.sort = 'campaign_nr';
	    	this.store.baseParams.dir = 'ASC';
	    },
	    onBeforeLoad: function(store, options) {
	        options.params.paging = {
                start: options.params.start,
                limit: options.params.limit
            };
	        options.params.sort = 'campaign_nr';
	        options.params.dir = 'ASC';
	        options.params.paging.sort = 'campaign_nr';
		    options.params.paging.dir = 'ASC';
	    },
	    tpl:new Ext.XTemplate(
	            '<tpl for="."><div class="search-item">',
	                '<table cellspacing="0" cellpadding="2" border="0" style="font-size: 12px;" width="100%">',
	                    '<tr  style="font-size: 12px;border-bottom:1px solid #000000;">',
	                        '<td width="30%"><b>{[this.encode(values.campaign_nr)]}</b></td>',
	                        '<td width="70%">{[this.encode(values.name)]}<br/></td>',
	                    '</tr>',
	                '</table>',
	            '</div></tpl>',
	            {
	                encode: function(value) {
	                     if (value) {
	                        return Ext.util.Format.htmlEncode(value);
	                    } else {
	                        return '';
	                    }
	                }
	            }
	        )
	},config));
};

Tine.Donator.Custom.getRecordPicker = function(modelName, id, config){
	switch(modelName){
	case 'Campaign':
		return Tine.Donator.Custom.getCampaignRecordPicker(id, config);
	default: 
		throw 'Unknown model type for record picker';
	}
};