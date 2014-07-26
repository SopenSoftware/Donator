<?php

class Donator_Custom_Template{
	/**
	 * 
	 * Check whether current donation has to be printed
	 * @param Donator_Model_Donation $donation
	 * @param string $templateId
	 */
	public static function isToPrint($donation, $type, &$templateId, $collected){
	
		if($donation->__get('is_cancelled')){
			return false;
		}
		
		if($donation->__get('is_cancellation')){
			return false;
		}
		
		switch($type){
			case Donator_Controller_Print::TYPE_CONFIRMATION:
				
				if($donation->__get('confirmation_date')){
					return false;
				}
				
				if($donation->__get('confirmation_kind') == 'CONFIRMATION_NO'){
					return false;
				}
				
				if($donation->__get('confirmation_kind') == 'CONFIRMATION_COLLECT' && $collected){
					$templateId = null;
					throw Exception('Not yet implemented: Druck Sammelquittung');
					//return true;
				}else{
					
					if($donation->__get('non_monetary') == 1){
						$templateId = Tinebase_Core::getPreference('Donator')->getValue(Donator_Preference::TEMPLATE_CONFIRMATION_NON_MON);
						return true;
					}
					$templateId = Tinebase_Core::getPreference('Donator')->getValue(Donator_Preference::TEMPLATE_CONFIRMATION);
					return true;
				}
				return false;
			case Donator_Controller_Print::TYPE_GRATUATION:
				if($donation->__get('thanks_date')){
					return false;
				}
				
				if($donation->__get('gratuation_kind') == 'THANK_NO'){
					return false;
				}
				if($donation->__get('gratuation_kind') == 'THANK_STANDARD'){
					$campaignId = $donation->__get('campaign_id');
					if($campaignId){
						$campaign = Donator_Controller_Campaign::getInstance()->get($campaignId);
						if($campaign){
							$templateId = $campaign->__get('gratuation_template_id');
							$templateId = $templateId->id;
							if($templateId){
								return true;
							}
						}
					}
				}
				$templateId = Tinebase_Core::getPreference('Donator')->getValue(Donator_Preference::TEMPLATE_GRATUATION);
				return true;
		}
	}
	
	public static function getConfirmationData(array $dataObjects, &$textBlocks){
		
		// Datenobject Spende
		$donation = $dataObjects['donation'];
		$project = $dataObjects['project'];
		$campaign = $dataObjects['campaign'];
		
		// Datenobject Spender-Kontakt
		$contact = $dataObjects['contact'];
		
		// Textblöcke umordnen, assoziativ nach Namen
		/*$textBlocksVar = array();
		foreach($textBlocks as $textBlock){
			$textBlocksVar[$textBlock['name']] = $textBlock['data'];
		}*/
		// Spendenbetrag
		$donationAmount = $donation->__get('donation_amount');

		// Betrag formatiert
		$BETRAG = \org\sopen\app\util\format\Currency::formatCurrency($donationAmount);
		
		// Betrag in Worten
		$BETRAG_WORTE = \org\sopen\app\util\format\Currency::speakCurrency($donationAmount);
		
		// Datum formatiert von ISO nach dd.mm.yyyy
		$DATUM = \org\sopen\app\util\format\Date::format($donation->__get('donation_date'));
		
		$nonMonetaryDescription = $donation->__get('non_monetary_description');
		// Geld/Sachzuwendung
		/*if((bool)$donation->__get('non_monetary')==true){
			$map = array(
				'Betriebsvermögen' => true,
				'Privatvermögen' => true,
				'keinAngabe' => true
			);
			$source = $donation->__get('non_monetary_source');
			switch($source){
				
				case 'COMMERCIAL':
						unset($map['Betriebsvermögen']);
					break;
					
				case 'PRIVATE':
						unset($map['Privatvermögen']);
					break;
					
				case 'NOSTATEMENT':
						unset($map['keinAngabe']);
					break;
					
			}
			foreach($map as $key => $value){
				$textBlocks[$key] = '';
			}
			
			$rating = (bool)$donation->__get('non_monetary_rating');
			if($rating){
				$textBlocks['ULliegenNichtVor'] = '';
			}else{
				$textBlocks['UlliegenVor'] = '';
			}
		}
		
		
		// Verzichtserklärung:
		if((bool)$donation->__get('refund_quitclaim')==true){
			$textBlocks['VerzichtNein'] = '';
		}else{
			$textBlocks['VerzichtJa'] = '';
		}
		*/
		
		
		$kind = 'Geldzuwendung ($)';
		if($donation->__get('non_monetary')){
			$kind = 'Sachzuwendung ($)';
		}
		
		if(!$donation->__get('refund_quitclaim')){
			$kind = str_replace('$', 'a', $kind);
		}else{
			$kind = str_replace('$', 'b', $kind);
		}
		
		return array(
			'SP' => array(
				'OBJECT' => $donation,
				'BETRAG' => $BETRAG,
				'BETR_WORTE' => $BETRAG_WORTE,
				'DATUM' => $DATUM,
				'ART' => $kind,
				//'SACH_BESCHREIBUNG' => $nonMonetaryDescription,
				'BELEG_DATUM' => strftime('%d.%m.%Y'),
				'CONFIRMATION_NR' => $donation->__get('confirm_nr')
			),
			'DATUM' => \org\sopen\app\util\format\Date::format(new Zend_Date()),
			'VWZW' => $campaign->__get('name'),
			'KONTAKT' => $contact,
			'BRIEFANREDE' => $contact->__get('letter_salutation'),
			'ANSCHRIFT' => array(
				'BRIEF' => $contact->getLetterDrawee()->toText()
			)
		);
	}
	
	public static function getGratuationData(array $dataObjects, &$textBlocks){
		// Datenobject Spender-Kontakt
		$contact = $dataObjects['contact'];
		$userContact = $dataObjects['userContact'];
	
		return array(
			'BRIEFANREDE' => $contact->__get('letter_salutation'),
			'ANSCHRIFT' => array(
				'BRIEF' => $contact->getLetterDrawee()->toText()
			),
			'ADR_NR' => $contact->__get('id'),
			'DATUM' => strftime('%d.%m.%Y'),
			'USER' => array(
				'PHONE' => $userContact->__get('tel_work'),
				'FAX' =>  $userContact->__get('tel_fax'),
				'MAIL' =>  $userContact->__get('email')
			)
		);
	}
}

?>