<?php
    //this file gets all the constant values from the DB needed to calculate the risk and hazard formulas
	
	//create connection to DB
    include "/opt/bitnami/apache2/htdocs/calculator/connection.php"; 

    $query = "SELECT value FROM CONSTANT WHERE shortname = 'AF' AND receptor = 'Adult';";
	$afAdult_mg = getConstant($link, $query);
	
	$query = "SELECT value FROM CONSTANT WHERE shortname = 'AF' AND receptor = 'Child';";
	$afChild_mg  = getConstant($link, $query);
	
	$query = "SELECT value FROM CONSTANT WHERE shortname = 'AT' AND receptor = 'All (Risk)';";
	$atAllRisk_days  = getConstant($link, $query);
	
	$query = "SELECT value FROM CONSTANT WHERE shortname = 'AT' AND receptor = 'Child (Haz)';";
	$atChild_days  = getConstant($link, $query);
	
	$query = "SELECT value FROM CONSTANT WHERE shortname = 'AT' AND receptor = 'Worker (Haz)';";
	$atWorker_days  = getConstant($link, $query);
	
	$query = "SELECT value FROM CONSTANT WHERE shortname = 'BW' AND receptor = 'Adult';";
	$bwAdult_kg  = getConstant($link, $query);
	
	$query = "SELECT value FROM CONSTANT WHERE shortname = 'BW' AND receptor = 'Child';";
	$bwChild_kg  = getConstant($link, $query);
	
	$query = "SELECT value FROM CONSTANT WHERE shortname = 'ED' AND receptor = 'Adult';";
	$edAdult_years  = getConstant($link, $query);
	
	$query = "SELECT value FROM CONSTANT WHERE shortname = 'ED' AND receptor = 'Child';";
	$edChild_years  = getConstant($link, $query);
	
	$query = "SELECT value FROM CONSTANT WHERE shortname = 'ED' AND receptor = 'Worker';";
	$edWorker_years  = getConstant($link, $query);
	
	$query = "SELECT value FROM CONSTANT WHERE shortname = 'EF' AND receptor = 'Resident';";
	$efResident_days  = getConstant($link, $query);
	
	$query = "SELECT value FROM CONSTANT WHERE shortname = 'EF' AND receptor = 'Worker';";
	$efWorker_days  = getConstant($link, $query);
	
	$query = "SELECT value FROM CONSTANT WHERE shortname = 'ET' AND receptor = 'Resident';";
	$etResident_hours  = getConstant($link, $query);
	
	$query = "SELECT value FROM CONSTANT WHERE shortname = 'ET' AND receptor = 'Worker';";
	$etWorker_hours  = getConstant($link, $query);
	
	$query = "SELECT value FROM CONSTANT WHERE shortname = 'IRs' AND receptor = 'Adult';";
	$irsAdult_mg  = getConstant($link, $query);
	
	$query = "SELECT value FROM CONSTANT WHERE shortname = 'IRs' AND receptor = 'Child';";
	$irsChild_mg  = getConstant($link, $query);
	
	$query = "SELECT value FROM CONSTANT WHERE shortname = 'PEF';";
	$pef_m  = getConstant($link, $query);
	
	$query = "SELECT value FROM CONSTANT WHERE shortname = 'SA' AND receptor = 'Adult';";
	$saAdult_cm  = getConstant($link, $query);
	
	$query = "SELECT value FROM CONSTANT WHERE shortname = 'SA' AND receptor = 'Child';";
	$saChild_cm  = getConstant($link, $query);
	
	mysqli_close($link);
	
	function getConstant($connection, $request) {
		$result = mysqli_query($connection, $request);
		$row = mysqli_fetch_array($result, MYSQLI_NUM);
		mysqli_free_result($result);
		return $row[0];
	}
	
?>