<?php
/**
 *
 * 
 * @author hhartl
 *
 */

/**
 * Migration of Brevetation app
 */

class Donator_Setup_Update_Release1 extends Setup_Update_Abstract{
	/**
	 * Add new columns
	 */
	public function update_0(){
		$declaration = new Setup_Backend_Schema_Field_Xml('
				<field>
	                <name>non_monetary</name>
	                <type>boolean</type>
					<default>false</default>
					<notnull>false</notnull>
	            </field>');
        $this->_backend->addCol('fund_donation', $declaration);
        
        $declaration = new Setup_Backend_Schema_Field_Xml('
				<field>
	                <name>refund_quitclaim</name>
	                <type>boolean</type>
					<default>false</default>
					<notnull>false</notnull>
	            </field>');
        $this->_backend->addCol('fund_donation', $declaration);
        
        $this->setApplicationVersion('Donator', '1.1');
	}
	
	/**
	 * Add new columns
	 */
	public function update_1(){
		$declaration = new Setup_Backend_Schema_Field_Xml('
				<field>
                    <name>gratuation_template_id</name>
                    <type>text</type>
					<length>40</length>
                    <notnull>true</notnull>
                </field>');
        $this->_backend->addCol('fund_campaign', $declaration);
        
        $this->setApplicationVersion('Donator', '1.2');
	}
	
	public function update_2(){
		$declaration = new Setup_Backend_Schema_Field_Xml('
			<field>
                <name>non_monetary_source</name>
				<type>enum</type>
				<value>COMMERCIAL</value>
				<value>PRIVATE</value>
				<value>NOSTATEMENT</value>
				<default>NOSTATEMENT</default>
            </field>');
 		$this->_backend->addCol('fund_donation', $declaration);
 				
		$declaration = new Setup_Backend_Schema_Field_Xml('
			<field>
                <name>non_monetary_rating</name>
                <type>boolean</type>
				<default>false</default>
				<notnull>false</notnull>
            </field>');		
		
 		$this->_backend->addCol('fund_donation', $declaration);
        
        $this->setApplicationVersion('Donator', '1.3');	            
	}
	
	public function update_3(){
		$this->_backend->dropCol(
			'fund_donation',
			'donation_account');
			
		$declaration = new Setup_Backend_Schema_Field_Xml('
				<field>
	                <name>donation_account_id</name>
                    <type>text</type>
					<length>40</length>
					<notnull>false</notnull>
	            </field>');
 		$this->_backend->addCol('fund_donation', $declaration);
 		 $this->setTableVersion('fund_donation', '2');
		
		 $declaration = new Setup_Backend_Schema_Table_Xml('
         <table>
			<name>fund_donation_account</name>	
			<version>1</version>
			<engine>InnoDB</engine>
	     	<charset>utf8</charset>
			<declaration>
                <field>
                    <name>id</name>
                    <type>text</type>
					<length>40</length>
                    <notnull>true</notnull>
                </field>
				<field>
					<name>bank_account_nr</name>
					<type>text</type>
					<length>32</length>
					<notnull>true</notnull>
				</field>
				<field>
					<name>bank_code</name>
					<type>text</type>
					<length>16</length>
					<default>null</default>
					<notnull>false</notnull>
				</field>
				<field>
					<name>bank_name</name>
					<type>text</type>
					<length>48</length>
					<default>null</default>
					<notnull>false</notnull>
				</field>
				<field>
					<name>account_name</name>
					<type>text</type>
					<length>48</length>
					<default>null</default>
					<notnull>false</notnull>
				</field>
				<field>
					<name>description</name>
					<type>text</type>
					<default>null</default>
					<notnull>false</notnull>
				</field>
				<index>
                    <name>id</name>
                    <primary>true</primary>
                    <field>
                        <name>id</name>
                    </field>
                </index>
				<index>
                    <name>unique_donation_account_nr</name>
                    <unique>true</unique>
                    <field>
                        <name>bank_account_nr</name>
                    </field>
                </index>			
			</declaration>
		</table>');
        
        $this->_backend->createTable($declaration);
        $this->setTableVersion('fund_donation_account', '1');
        
        $declaration = new Setup_Backend_Schema_Table_Xml('
        <table>
			<name>fund_donation_unit</name>
			<version>1</version>
			<engine>InnoDB</engine>
	     	<charset>utf8</charset>
			<declaration>
                <field>
                    <name>id</name>
                    <type>text</type>
					<length>40</length>
                    <notnull>true</notnull>
                </field>
				<field>
	                <name>contact_id</name>
	                <type>integer</type>
	                <notnull>true</notnull>
	            </field>
				<field>
					<name>unit_nr</name>
					<type>text</type>
					<length>16</length>
					<default>null</default>
					<notnull>false</notnull>
				</field>
				<field>
					<name>unit_name</name>
					<type>text</type>
					<length>16</length>
					<default>null</default>
					<notnull>false</notnull>
				</field>
			</declaration>
		</table>');
        
        $this->_backend->createTable($declaration);
        $this->setTableVersion('fund_donation_unit', '1');
        
        $this->setApplicationVersion('Donator', '1.4');	 
	}
	
	public function update_4(){
		$this->_backend->dropCol(
			'fund_campaign',
			'donation_account_nr');
			
		$declaration = new Setup_Backend_Schema_Field_Xml('
				<field>
	                <name>donation_account_id</name>
                    <type>text</type>
					<length>40</length>
					<notnull>false</notnull>
	            </field>');
 		$this->_backend->addCol('fund_campaign', $declaration);
 		$this->setTableVersion('fund_campaign', '2');
		
	    $this->setApplicationVersion('Donator', '1.5');	 
	}
	
	
	public function update_5(){
		$declaration = new Setup_Backend_Schema_Field_Xml('
		         <field>
                    <name>donation_unit_id</name>
                    <type>text</type>
					<length>40</length>
                    <notnull>true</notnull>
                </field>');
 		$this->_backend->addCol('fund_campaign', $declaration);
 		$this->setTableVersion('fund_campaign', '3');
		
	    $this->setApplicationVersion('Donator', '1.6');	 
	}
	
	public function update_6(){


		$declaration = new Setup_Backend_Schema_Field_Xml('
				<field>
	                <name>non_monetary_description</name>
	                <type>text</type>
					<length>1024</length>
					<default>null</default>
	                <notnull>false</notnull>
	            </field>');
 		$this->_backend->addCol('fund_donation', $declaration);
 		
 		$declaration = new Setup_Backend_Schema_Field_Xml('
				<field>
	                <name>confirm_nr</name>
	                <type>text</type>
					<length>24</length>
					<default>null</default>
	                <notnull>false</notnull>
	            </field>');
 		$this->_backend->addCol('fund_donation', $declaration);
	    $this->setTableVersion('fund_donation', '2');
	    
	     // add default records
        $xml = '<?xml version="1.0" encoding="utf-8"?>
        <defaultRecords>
		<record>
			<table>
				<name>number_base</name>
			</table>
			<field>
				<name>key</name>
				<value>donation_confirm_nr</value>
			</field>
			<field>
				<name>description</name>
				<value>Basis Spenden-Bestätigungs-Nummer</value>
			</field>
			<field>
				<name>formula</name>
				<value>N1</value>
			</field>
			<field>
				<name>number1</name>
				<value>0</value>
			</field>
		</record>	
	</defaultRecords>';
        
	$xml = new SimpleXMLElement($xml);
        
	// insert default records
	if (isset($xml->defaultRecords)) {
		foreach ($xml->defaultRecords[0] as $record) {
			$this->_backend->execInsertStatement($record);
		}
	}	    
	    
		$this->setApplicationVersion('Donator', '1.7');	 
	}
	
	public function update_7(){
		$declaration = new Setup_Backend_Schema_Field_Xml('
	        <field>
                <name>is_hidden</name>
                <type>boolean</type>
                <notnull>true</notnull>
				<default>false</default>
            </field>');
 		$this->_backend->addCol('fund_master', $declaration);
 		$this->setTableVersion('fund_master', '2');
		
 		$declaration = new Setup_Backend_Schema_Field_Xml('
	        <field>
                <name>is_hidden</name>
                <type>boolean</type>
                <notnull>true</notnull>
				<default>false</default>
            </field>');
 		$this->_backend->addCol('fund_donation', $declaration);
 		$this->setTableVersion('fund_donation', '3');
 		
	    $this->setApplicationVersion('Donator', '1.8');
	}
	
	public function update_8(){
 		$declaration = new Setup_Backend_Schema_Field_Xml('
			<field>
                <name>gratuation_kind</name>
				<type>enum</type>
				<value>THANK_NO</value>
				<value>THANK_STANDARD</value>
				<value>THANK_INDIVIDUAL</value>
				<value>NO_VALUE</value>
				<default>NO_VALUE</default>
            </field>');
 		$this->_backend->addCol('fund_campaign', $declaration);
 		
 		$this->setTableVersion('fund_campaign', '4');
 		
 		$declaration = new Setup_Backend_Schema_Field_Xml('
			<field>
                <name>donation_date</name>
                <type>datetime</type>
                <notnull>false</notnull>
            </field>');
 		$this->_backend->alterCol('fund_donation', $declaration);
 		
 		$declaration = new Setup_Backend_Schema_Field_Xml('
			<field>
                <name>confirmation_date</name>
                <type>datetime</type>
                <notnull>false</notnull>
            </field>');
 		$this->_backend->alterCol('fund_donation', $declaration);
 		
	    $this->setApplicationVersion('Donator', '1.9');
	}
	
	public function update_9(){
 		$declaration = new Setup_Backend_Schema_Field_Xml('
				<field>
	                <name>is_closed</name>
	                <type>boolean</type>
	                <notnull>true</notnull>
					<default>false</default>
	            </field>');
 		$this->_backend->addCol('fund_campaign', $declaration);
 		
	    $this->setApplicationVersion('Donator', '2.0');
	}
}
?>