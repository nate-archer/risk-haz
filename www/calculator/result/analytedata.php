<?php
    //this file contains functions to pull analyte specific values from the DB
	
	function getSfo($regulator, $aid) {
		//regulator must be either 'california' or 'federal'
		
		//create connection to DB
		include "/opt/bitnami/apache2/htdocs/calculator/connection.php"; 
		
		$sfo_mg = "";
		
		if ($regulator == "california") {
			do {
				//Check for values from CA table first. SFO ID = 1. 
				$query = "SELECT ToxVal FROM TOXICITY_CA WHERE Aid_fk = ".$aid." AND Toxid_fk = 1;";
				$result = mysqli_query($link, $query);
		        $rowResult = mysqli_fetch_array($result, MYSQLI_NUM);
				if ($rowResult[0] != "") { //means we found an SFO value in the CA table
				    $sfo_mg = $rowResult[0];
					break;
				}
					
				//If no SFO value found, check Fed table
				$query = "SELECT ToxVal FROM TOXICITY_FED WHERE Aid_fk = ".$aid." AND Toxid_fk = 1;";
				$result = mysqli_query($link, $query);
		        $rowResult = mysqli_fetch_array($result, MYSQLI_NUM);
				if ($rowResult[0] != "") { //means we found an SFO value in the Fed table
					$sfo_mg = $rowResult[0];
					break;
				}
					    
				//If no SFO value in either table, set SFO = 0
				$sfo_mg = 0;
				break;
			} while (0);
			
		//Otherwise, regulator is federal
		}else {
			do {
				//Check Fed table for SFO. SFO ID = 1
				$query = "SELECT ToxVal FROM TOXICITY_FED WHERE Aid_fk = ".$aid." AND Toxid_fk = 1;";
				$result = mysqli_query($link, $query);
		        $rowResult = mysqli_fetch_array($result, MYSQLI_NUM);
				if ($rowResult[0] != "") { //means we found an SFO value in the Fed table
					$sfo_mg = $rowResult[0];
					break;
				}
				    
				//If no SFO value in table, set SFO = 0
				$sfo_mg = 0;
				break;
			} while (0);
		}
		
		mysqli_free_result($result);
		mysqli_close($link);
		return $sfo_mg;
	}
	
	function getRfdo($regulator, $aid) {
		//regulator must be either 'california' or 'federal'
		
		//create connection to DB
		include "/opt/bitnami/apache2/htdocs/calculator/connection.php"; 
		
		$rfdo_mg = "";
		
		if ($regulator == "california") {
			do {
				//Check for values from CA table first. RfDo ID = 3.
				$query = "SELECT ToxVal FROM TOXICITY_CA WHERE Aid_fk = ".$aid." AND Toxid_fk = 3;";
				$result = mysqli_query($link, $query);
		        $rowResult = mysqli_fetch_array($result, MYSQLI_NUM);
				if ($rowResult[0] != "") { //means we found a RfDo value in the CA table
				    $rfdo_mg = $rowResult[0];
					break;
				}
				
				//If no RfDo value found, check Fed table
				$query = "SELECT ToxVal FROM TOXICITY_FED WHERE Aid_fk = ".$aid." AND Toxid_fk = 3;";
				$result = mysqli_query($link, $query);
		        $rowResult = mysqli_fetch_array($result, MYSQLI_NUM);
				if ($rowResult[0] != "") { //means we found a RfDo value in the Fed table
					$rfdo_mg = $rowResult[0];
					break;
				}
				    
				//If no RfDo value in either table, set RfDo = 0
				$rfdo_mg = 0;
				break;
			} while (0);
			
		//Otherwise, regulator is federal	
		}else {
			do {
				//Check Fed table for RfDo. RfDo ID = 3.
				$query = "SELECT ToxVal FROM TOXICITY_FED WHERE Aid_fk = ".$aid." AND Toxid_fk = 3;";
				$result = mysqli_query($link, $query);
		        $rowResult = mysqli_fetch_array($result, MYSQLI_NUM);
				if ($rowResult[0] != "") { //means we found a RfDo value in the Fed table
					$rfdo_mg = $rowResult[0];
					break;
				}
				    
				//If no RfDo value found, set RfDo = 0
				$rfdo_mg = 0;
				break;
			} while (0);
		}
		
		mysqli_free_result($result);
		mysqli_close($link);
		return $rfdo_mg;
	}
	
	function getAbs($regulator, $aid) {
		//regulator must be either 'california' or 'federal'
		
		//create connection to DB
		include "/opt/bitnami/apache2/htdocs/calculator/connection.php"; 
		
		$abs = "";
		
		if ($regulator == "california") {
			//need to get the ABS ID for the analyte
			$query = "SELECT absid_fk FROM analyte WHERE aid = ".$aid.";";
			$result = mysqli_query($link, $query);
		    $rowResult = mysqli_fetch_array($result, MYSQLI_NUM);
			$absId = $rowResult[0];
			
			//look up absID in ABS_CA table to get ABS value
			$query = "SELECT absval FROM abs_ca WHERE absid = ".$absId.";";
			$result = mysqli_query($link, $query);
		    $rowResult = mysqli_fetch_array($result, MYSQLI_NUM);
			$abs = $rowResult[0];
			
		//Otherwise, regulator is federal	
		}else {
			//look up ABS value in TOXICITY_FED table. ABS toxid = 5.
			do {
				$query = "SELECT ToxVal FROM TOXICITY_FED WHERE Aid_fk = ".$aid." AND Toxid_fk = 5;";
				$result = mysqli_query($link, $query);
		        $rowResult = mysqli_fetch_array($result, MYSQLI_NUM);
				if ($rowResult[0] != "") { //means we found an ABS value in the Fed table
					$abs = $rowResult[0];
					break;
				}
				    
				//If no ABS value found, set $abs = 0
				$abs = 0;
				break;
			} while (0);
		}
	
		mysqli_free_result($result);
		mysqli_close($link);
		return $abs;
	}
?>