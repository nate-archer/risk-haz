<?php
    //this file contains formulas to calculate risk and hazard values

    function oralRiskResident($sfo_mg, $cs_mg) {
		
		global $irsAdult_mg, $irsChild_mg, $efResident_days, $edAdult_years, $edChild_years, $bwAdult_kg, $bwChild_kg, $atAllRisk_days;
		
		$risk = ( $sfo_mg * $cs_mg * ( ($irsAdult_mg * $efResident_days * $edAdult_years * 0.000001) / ($bwAdult_kg * $atAllRisk_days) ) ) +
		        ( $sfo_mg * $cs_mg * ( ($irsChild_mg * $efResident_days * $edChild_years * 0.000001) / ($bwChild_kg * $atAllRisk_days) ) );
		
		return $risk;
	}
	
	function oralRiskWorker($sfo_mg, $cs_mg) {
		
		global $irsAdult_mg, $efWorker_days, $edWorker_years, $bwAdult_kg, $atAllRisk_days;
		
		$risk = ( $sfo_mg * $cs_mg * ( ($irsAdult_mg * $efWorker_days * $edWorker_years * 0.000001) / ($bwAdult_kg * $atAllRisk_days) ) );
		
		return $risk;
	}
	
	function oralHazResident ($rfdo_mg, $cs_mg) {
		
		//check if rfdo is 0 to avoid division by zero error in hazard calculation formula
		if ($rfdo_mg == 0){
			return 0;
		}
		
		global $irsChild_mg, $efResident_days, $edChild_years, $bwChild_kg, $atChild_days;
		
		$haz = ( (1 / $rfdo_mg) * $cs_mg * ( ($irsChild_mg * $efResident_days * $edChild_years * 0.000001) / ($bwChild_kg * $atChild_days) ) );
		
		return $haz;
		
	}
	
	function oralHazWorker ($rfdo_mg, $cs_mg) {
		
		//check if rfdo is 0 to avoid division by zero error in hazard calculation formula
		if ($rfdo_mg == 0){
			return 0;
		}
		
		global $irsAdult_mg, $efWorker_days, $edWorker_years, $bwAdult_kg, $atWorker_days;
		
		$haz = ( (1 / $rfdo_mg) * $cs_mg * ( ($irsAdult_mg * $efWorker_days * $edWorker_years * 0.000001) / ($bwAdult_kg * $atWorker_days) ) );
		
		return $haz;
		
	}
	
	function dermalRiskResident ($sfo_mg, $cs_mg, $abs) {
		
		global $saAdult_cm, $saChild_cm, $afAdult_mg, $afChild_mg, $efResident_days, $edAdult_years, $edChild_years, $bwAdult_kg, $bwChild_kg, $atAllRisk_days;
		
		$risk = ( $sfo_mg * $cs_mg * ( ($saAdult_cm * $afAdult_mg * $abs * $efResident_days * $edAdult_years * 0.000001) / ($bwAdult_kg * $atAllRisk_days) ) ) +
		        ( $sfo_mg * $cs_mg * ( ($saChild_cm * $afChild_mg * $abs * $efResident_days * $edChild_years * 0.000001) / ($bwChild_kg * $atAllRisk_days) ) );
		
		return $risk;
		
	}
	
	function dermalRiskWorker ($sfo_mg, $cs_mg, $abs) {
		
		global $saAdult_cm, $afAdult_mg, $efWorker_days, $edWorker_years, $bwAdult_kg, $atAllRisk_days;
		
		$risk = ( $sfo_mg * $cs_mg * ( ($saAdult_cm * $afAdult_mg * $abs * $efWorker_days * $edWorker_years * 0.000001) / ($bwAdult_kg * $atAllRisk_days) ) );
		
		return $risk;
		
	}
	
	function dermalHazResident ($rfdo_mg, $cs_mg, $abs) {
		
		//check if rfdo is 0 to avoid division by zero error in hazard calculation formula
		if ($rfdo_mg == 0){
			return 0;
		}
		
		global $saChild_cm, $afChild_mg, $efResident_days, $edChild_years, $bwChild_kg, $atChild_days;
		
		$haz = ( (1 / $rfdo_mg) * $cs_mg * ( ($saChild_cm * $afChild_mg * $abs * $efResident_days * $edChild_years * 0.000001) / ($bwChild_kg * $atChild_days) ) );
		
		return $haz;
		
	}
	
	function dermalHazWorker ($rfdo_mg, $cs_mg, $abs) {
		
		//check if rfdo is 0 to avoid division by zero error in hazard calculation formula
		if ($rfdo_mg == 0){
			return 0;
		}
		
		global $saAdult_cm, $afAdult_mg, $efWorker_days, $edWorker_years, $bwAdult_kg, $atWorker_days;
		
		$haz = ( (1 / $rfdo_mg) * $cs_mg * ( ($saAdult_cm * $afAdult_mg * $abs * $efWorker_days * $edWorker_years * 0.000001) / ($bwAdult_kg * $atWorker_days) ) );
		
		return $haz;
		
	}

?>