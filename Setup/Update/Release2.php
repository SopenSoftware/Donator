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

class Donator_Setup_Update_Release2 extends Setup_Update_Abstract{
	/**
	 * Add new columns
	 */
	public function update_0(){
		 $declaration = new Setup_Backend_Schema_Field_Xml('
				<field>
	                <name>donation_type</name>
					<type>enum</type>
					<value>CYCLE</value>
					<value>SINGLE</value>
					<default>SINGLE</default>
	            </field>'
         );
         $this->_backend->addCol('fund_donation', $declaration);
         
         $declaration = new Setup_Backend_Schema_Field_Xml('
				<field>
                    <name>erp_proceed_account_id</name>
                    <type>integer</type>
					<notnull>true</notnull>
					<default>null</default>
                </field>'
         );
         $this->_backend->addCol('fund_donation', $declaration);
         
         
         $declaration = new Setup_Backend_Schema_Field_Xml('
				<field>
                    <name>erp_proceed_account_id</name>
                    <type>integer</type>
					<notnull>true</notnull>
					<default>null</default>
                </field>'
         );
         $this->_backend->addCol('fund_campaign', $declaration);
    
         $declaration = new Setup_Backend_Schema_Field_Xml('
				<field>
                    <name>erp_activity_account_id</name>
                    <type>integer</type>
					<notnull>false</notnull>
					<default>null</default>
                </field>'
         );
         $this->_backend->addCol('fund_campaign', $declaration);
         
         $declaration = new Setup_Backend_Schema_Field_Xml('
				<field>
					<name>bank_account_nr</name>
					<type>text</type>
					<length>32</length>
					<notnull>false</notnull>
					<default>null</default>
				</field>'
         );
         $this->_backend->addCol('fund_master', $declaration);
         
          $declaration = new Setup_Backend_Schema_Field_Xml('
				<field>
					<name>bank_code</name>
					<type>text</type>
					<length>16</length>
					<default>null</default>
					<notnull>false</notnull>
				</field>'
         );
         $this->_backend->addCol('fund_master', $declaration);

          $declaration = new Setup_Backend_Schema_Field_Xml('
				<field>
					<name>bank_name</name>
					<type>text</type>
					<length>48</length>
					<default>null</default>
					<notnull>false</notnull>
				</field>'
         );
         $this->_backend->addCol('fund_master', $declaration);

          $declaration = new Setup_Backend_Schema_Field_Xml('
				<field>
					<name>account_name</name>
					<type>text</type>
					<length>48</length>
					<default>null</default>
					<notnull>false</notnull>
				</field>'
         );
         $this->_backend->addCol('fund_master', $declaration);

         $declaration = new Setup_Backend_Schema_Table_Xml('
          <table>
			<name>fund_regular_donation</name>	
			<version>3</version>
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
					<name>fundmaster_id</name>
                    <type>text</type>
					<length>40</length>
                    <notnull>true</notnull>
				</field>			
				<field>
					<name>campaign_id</name>
                    <type>text</type>
					<length>40</length>
                    <notnull>false</notnull>
				</field>
				<field>
	                <name>donation_account_id</name>
                    <type>text</type>
					<length>40</length>
                    <notnull>false</notnull>
	            </field>
				<field>
                    <name>erp_proceed_account_id</name>
                    <type>integer</type>
					<notnull>true</notnull>
					<default>null</default>
                </field>
				<field>
					<name>begin_date</name>
                    <type>date</type>
					<notnull>true</notnull>
				</field>
				<field>
					<name>next_date</name>
                    <type>date</type>
					<notnull>true</notnull>
				</field>
				<field>
					<name>end_date</name>
                    <type>date</type>
					<notnull>false</notnull>
					<default>null</default>
				</field>
				<field>
	                <name>reg_donation_amount</name>
	                <type>float</type>
	                <notnull>false</notnull>
					<default>0</default>
	            </field>
				<field>
	                <name>gratuation_kind</name>
					<type>enum</type>
					<value>THANK_NO</value>
					<value>THANK_STANDARD</value>
					<value>THANK_INDIVIDUAL</value>
					<default>THANK_NO</default>
	            </field>
				<field>
	                <name>confirmation_kind</name>
					<type>enum</type>
					<value>CONFIRMATION_COLLECT</value>
					<value>CONFIRMATION_SINGLE</value>
					<value>CONFIRMATION_NO</value>
					<default>CONFIRMATION_COLLECT</default>
	            </field>				
				<field>
					<name>donation_payment_interval</name>
					<type>enum</type>
					<value>NOVALUE</value>
					<value>YEAR</value>
					<value>QUARTER</value>
					<value>MONTH</value>
					<default>NOVALUE</default>
				</field>	
				<field>
					<name>donation_payment_method</name>
					<type>text</type>
					<length>40</length>
                    <notnull>true</notnull>
				</field>
				<field>
					<name>bank_account_nr</name>
					<type>text</type>
					<length>32</length>
					<notnull>false</notnull>
					<default>null</default>
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
	                <name>on_hold</name>
	                <type>boolean</type>
	                <notnull>true</notnull>
					<default>false</default>
	            </field>
				<field>
	                <name>donation_usage</name>
	                <type>text</type>
	                <notnull>false</notnull>
	            </field>
				<index>
                    <name>id</name>
                    <primary>true</primary>
                    <field>
                        <name>id</name>
                    </field>
                </index>
			</declaration>
		</table>
        ');
        
        $this->_backend->createTable($declaration);
         
        $this->setApplicationVersion('Donator', '2.01');
	}
	
	public function update_01(){
		
          $declaration = new Setup_Backend_Schema_Field_Xml('
				<field>
                    <name>regular_donation_nr</name>
                    <type>text</type>
					<length>24</length>
                    <notnull>true</notnull>
                </field>'
         );
         $this->_backend->addCol('fund_regular_donation', $declaration);
	
		$this->_backend->execInsertStatement(new SimpleXMLElement('
			<record>
				<table>
					<name>number_base</name>
				</table>
				<field>
					<name>key</name>
					<value>donator_regular_donation_nr</value>
				</field>
				<field>
					<name>formula</name>
					<value>N1</value>
				</field>
				<field>
					<name>number1</name>
					<value>0</value>
				</field>
				<field>
					<name>number2</name>
					<value>0</value>
				</field>
				<field>
					<name>number3</name>
					<value>0</value>
				</field>
				<field>
					<name>last_generated</name>
					<value>0</value>
				</field>			
			</record>'));
	
	    $this->setApplicationVersion('Donator', '2.02');
	    
	}
	
	public function update_02(){
		 $declaration = new Setup_Backend_Schema_Field_Xml('
				<field>
	                <name>donation_amount</name>
	                <type>float</type>
					<unsigned>false</unsigned>
	                <notnull>false</notnull>
					<default>0</default>
	            </field>'
         );
         $this->_backend->alterCol('fund_donation', $declaration);
	
	
		$this->setApplicationVersion('Donator', '2.03');
	    
	}
	
	public function update_03(){
		$declaration = new Setup_Backend_Schema_Field_Xml('
				<field>
                    <name>debit_account_system_id</name>
                    <type>integer</type>
                    <notnull>false</notnull>
                    <default>null</default>
                </field>'
       );
       $this->_backend->addCol('fund_campaign', $declaration);
	
	   $declaration = new Setup_Backend_Schema_Field_Xml('
				<field>
                    <name>bank_account_system_id</name>
                    <type>integer</type>
                    <notnull>false</notnull>
                    <default>null</default>
                </field>'
       );
       $this->_backend->addCol('fund_campaign', $declaration);
		
       $declaration = new Setup_Backend_Schema_Field_Xml('
				<field>
                    <name>booking_id</name>
                    <type>integer</type>
                    <notnull>false</notnull>
                    <default>null</default>
                </field>'
       );
       $this->_backend->addCol('fund_donation', $declaration);
		$this->setApplicationVersion('Donator', '2.04');
	    
	}
	
	public function update_04(){
		$declaration = new Setup_Backend_Schema_Field_Xml('
			<field>
                    <name>bank_account_system_id</name>
                    <type>integer</type>
                    <notnull>false</notnull>
                    <default>null</default>
                </field>'
       	);
       	$this->_backend->addCol('fund_donation_account', $declaration);
       	
       	
		$this->setApplicationVersion('Donator', '2.05');
	    
	}
	
	public function update_05(){
		$this->_backend->execInsertStatement(new SimpleXMLElement('
			<record>
				<table>
					<name>number_base</name>
				</table>
				<field>
					<name>key</name>
					<value>donator_collected_confirmation_nr</value>
				</field>
				<field>
					<name>formula</name>
					<value>N1</value>
				</field>
				<field>
					<name>number1</name>
					<value>0</value>
				</field>
				<field>
					<name>number2</name>
					<value>0</value>
				</field>
				<field>
					<name>number3</name>
					<value>0</value>
				</field>
				<field>
					<name>last_generated</name>
					<value>0</value>
				</field>			
			</record>'));
	
	    $this->setApplicationVersion('Donator', '2.06');
	    
	}
	
	public function update_06(){
		$declaration = new Setup_Backend_Schema_Field_Xml('
				<field>
	                <name>is_cancelled</name>
	                <type>boolean</type>
	                <notnull>true</notnull>
					<default>false</default>
	            </field>'
       );
       $this->_backend->addCol('fund_donation', $declaration);
	
	   $declaration = new Setup_Backend_Schema_Field_Xml('
				<field>
	                <name>is_cancellation</name>
	                <type>boolean</type>
	                <notnull>true</notnull>
					<default>false</default>
	            </field>'
       );
       	$this->_backend->addCol('fund_donation', $declaration);
		
       	$this->setApplicationVersion('Donator', '2.07');
	    
	}
	
	public function update_07(){
		$declaration = new Setup_Backend_Schema_Field_Xml('
				<field>
					<name>related_donation_id</name>
                    <type>text</type>
					<length>40</length>
                    <notnull>false</notnull>
					<default>null</default>
				</field>
		    ');
       $this->_backend->addCol('fund_donation', $declaration);
	
	   
       $this->setApplicationVersion('Donator', '2.08');
	    
	}
	
	public function update_08(){
		$declaration = new Setup_Backend_Schema_Field_Xml('
				<field>
	                <name>is_fm_hidden</name>
	                <type>boolean</type>
	                <notnull>true</notnull>
					<default>false</default>
	            </field>
		    ');
       $this->_backend->alterCol('fund_master', $declaration, 'is_hidden');
	
	   
       $this->setApplicationVersion('Donator', '2.09');
	    
	}
	
				
	public function update_09(){
		$declaration = new Setup_Backend_Schema_Field_Xml('
				<field>
					<name>donation_payment_interval</name>
					<type>enum</type>
					<value>NOVALUE</value>
					<value>YEAR</value>
					<value>HALF</value>
					<value>QUARTER</value>
					<value>MONTH</value>
					<default>NOVALUE</default>
				</field>	
		    ');
       $this->_backend->alterCol('fund_master', $declaration);
       
       $declaration = new Setup_Backend_Schema_Field_Xml('
				<field>
					<name>donation_payment_interval</name>
					<type>enum</type>
					<value>NOVALUE</value>
					<value>YEAR</value>
					<value>HALF</value>
					<value>QUARTER</value>
					<value>MONTH</value>
					<default>NOVALUE</default>
				</field>	
		    ');
       $this->_backend->alterCol('fund_regular_donation', $declaration);
	   
       $this->setApplicationVersion('Donator', '2.10');
	    
	}
	
	public function update_10(){
		$declaration = new Setup_Backend_Schema_Field_Xml('
				<field>
	                <name>terminated</name>
	                <type>boolean</type>
	                <notnull>true</notnull>
					<default>false</default>
	            </field>
		    ');
       $this->_backend->addCol('fund_regular_donation', $declaration);
       
       $this->setApplicationVersion('Donator', '2.11');
	    
	}
	
	public function update_11(){
		$declaration = new Setup_Backend_Schema_Field_Xml('
				<field>
	                <name>control_sum</name>
	                <type>float</type>
	                <notnull>false</notnull>
					<default>0</default>
	            </field>
		    ');
       $this->_backend->addCol('fund_regular_donation', $declaration);

       $declaration = new Setup_Backend_Schema_Field_Xml('
				<field>
	                <name>control_count</name>
	                <type>integer</type>
	                <notnull>false</notnull>
					<default>0</default>
	            </field>
		    ');
       $this->_backend->addCol('fund_regular_donation', $declaration);
       
       $this->setApplicationVersion('Donator', '2.12');
	    
	}			
         
	public function update_12(){
		$declaration = new Setup_Backend_Schema_Field_Xml('
				<field>
	                <name>terminated_membership</name>
	                <type>integer</type>
	                <notnull>false</notnull>
					<default>0</default>
	            </field>
		    ');
       $this->_backend->addCol('fund_regular_donation', $declaration);

       $this->setApplicationVersion('Donator', '2.13');
	    
	}	

	public function update_13(){
		$declaration = new Setup_Backend_Schema_Field_Xml('
				<field>
					<name>last_receipt_id</name>
                    <type>integer</type>
					<notnull>false</notnull>
					<default>null</default>
				</field>
		    ');
       $this->_backend->addCol('fund_regular_donation', $declaration);
       
       $declaration = new Setup_Backend_Schema_Field_Xml('
				<field>
					<name>last_donation_id</name>
                    <type>text</type>
					<length>40</length>
                    <notnull>false</notnull>
					<default>null</default>
				</field>
		    ');
       $this->_backend->addCol('fund_regular_donation', $declaration);

       $this->setApplicationVersion('Donator', '2.14');
	    
	}
	
	public function update_14(){
		$declaration = new Setup_Backend_Schema_Field_Xml('
				<field>
                    <name>payment_id</name>
                   	<type>text</type>
					<length>40</length>
                    <notnull>false</notnull>
                </field>
		    ');
       $this->_backend->addCol('fund_donation', $declaration);
       
       $this->setApplicationVersion('Donator', '2.15');
	    
	}
	
	public function update_15(){
		$declaration = new Setup_Backend_Schema_Field_Xml('
				<field>
                    <name>allow_booking</name>
                    <type>boolean</type>
                    <default>true</default>
                </field>
		    ');
       $this->_backend->addCol('fund_donation', $declaration);
       
       $this->setApplicationVersion('Donator', '2.16');
	    
	}
	
	public function update_16(){

		$declaration = new Setup_Backend_Schema_Field_Xml('
				<field>
                    <name>is_member</name>
                    <type>boolean</type>
					<notnull>true</notnull>
					<default>false</default>
			    </field>	');
		$this->_backend->addCol('fund_donation', $declaration);
		
		$declaration = new Setup_Backend_Schema_Field_Xml('
				<field>
                    <name>period</name>
                    <type>integer</type>
					<notnull>false</notnull>
					<default>0</default>
			    </field>	');
		$this->_backend->addCol('fund_donation', $declaration);
		
		$declaration = new Setup_Backend_Schema_Field_Xml('
				<field>
                    <name>fee_group_id</name>
                    <type>text</type>
					<length>40</length>
                    <notnull>false</notnull>
					<default>null</default>
                </field>	');
		$this->_backend->addCol('fund_donation', $declaration);
		$this->setApplicationVersion('Donator', '2.17');
	}
	
	public function update_17(){
		$declaration = new Setup_Backend_Schema_Field_Xml('
				<field>
					<name>bank_account_id</name>
					<type>integer</type>
					<notnull>false</notnull>
				</field>	
		    ');
       $this->_backend->addCol('fund_regular_donation', $declaration);
       
       $declaration = new Setup_Backend_Schema_Field_Xml('
				<field>
					<name>bank_account_usage_id</name>
					<type>integer</type>
					<notnull>false</notnull>
				</field>	
		    ');
       $this->_backend->addCol('fund_regular_donation', $declaration);
       
       $this->setApplicationVersion('Donator', '2.18');
	}
	
	/*public function update_18(){
		$declaration = new Setup_Backend_Schema_Field_Xml('
				<field>
					<name>bank_account_id</name>
					<type>integer</type>
					<notnull>false</notnull>
				</field>	
		    ');
       $this->_backend->addCol('fund_master', $declaration);
       
       $declaration = new Setup_Backend_Schema_Field_Xml('
				<field>
					<name>bank_account_usage_id</name>
					<type>integer</type>
					<notnull>false</notnull>
				</field>	
		    ');
       $this->_backend->addCol('fund_master', $declaration);
       
       $this->setApplicationVersion('Donator', '2.19');
	}*/
	
}
?>