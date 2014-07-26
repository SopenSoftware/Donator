<?php
/**
 * 
 * Standard calculator for number base
 * @author hhartl
 *
 */
class Donator_Custom_NumberBase_Calculator_DonationConfirmNr extends Tinebase_NumberBase_Calculator_Standard{
	
	 public function calculateNumber(Tinebase_NumberBase_Model &$numberBase, array $params = null){
	 	// use standard calculator
	 	$newNumber = parent::calculateNumber(&$numberBase, $params);
	 	
	 	if(!is_array($params)){
	 		throw new Tinebase_NumberBase_Exception('No params given for calculator: '.__CLASS__);
	 	}
	 	
	 	/*if(!array_key_exists('fundmaster', $params)){
	 		throw new Tinebase_NumberBase_Exception('Donation not contained in params: '.__CLASS__);
	 	}*/
	 	
	  	$confirmKind = null;
	 	
	 	if(array_key_exists('fundmaster', $params)){
	 		$fundMaster = $params['fundmaster'];
	 		$confirmKind = $fundMaster->__get('confirmation_kind');
	 	}elseif(array_key_exists('donation', $params)){
	 		$donation = $params['donation'];
	 		$confirmKind = $donation->__get('confirmation_kind');
	 	}
	 	if( $confirmKind == 'CONFIRMATION_COLLECT'){
	 		$newNumber = 'S2012'.$newNumber;
	 	}else{
	 		$newNumber = 'E2012'.$newNumber;
	 	}
	 	
	 	return $newNumber;
	 }
}