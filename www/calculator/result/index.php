<?php

    session_start();
	
	//check if initial form values have been entered. If values are empty, redirect to start of form
	if ( !isset ($_SESSION['regulator']) || !isset ($_SESSION['receptor']) || 
	(!isset ($_SESSION['oral']) && !isset ($_SESSION['inhalation']) && !isset ($_SESSION['dermal'])) || 
	!isset ($_SESSION['selectedAnalytes'])) {
		
		header("Location: http://www.riskhaz.com/calculator/");
		
	}
	
	$riskTotal = $hazTotal = 0;
	$oralRisk = $inhalationRisk = $dermalRisk = $oralHaz = $inhalationHaz = $dermalHaz = 0;
	
	$afAdult_mg = $afChild_mg = $atAllRisk_days = $atChild_days = $atWorker_days = $bwAdult_kg = $bwChild_kg = 
	$edAdult_years = $edChild_years = $edWorker_years = $efResident_days = $efWorker_days = $etResident_hours = $etWorker_hours = 
	$irsAdult_mg = $irsChild_mg = $pef_m = $saAdult_cm = $saChild_cm = "";
	
	include "/opt/bitnami/apache2/htdocs/calculator/result/constants.php"; //used to populate constants above
	include "/opt/bitnami/apache2/htdocs/calculator/result/analytedata.php"; //functions to pull analyte specific data from DB
	include "/opt/bitnami/apache2/htdocs/calculator/result/formulas.php"; //formulas to calculate risk and hazard values
	include "/opt/bitnami/apache2/htdocs/calculator/result/scino.php"; //functions to convert a number to scientific notation
	
	//copy selected analytes data from session to local array. We'll need to add values from the DB to this array after they are calculated below.
	$analyteData = array();
	
	for ($row = 0; $row < count($_SESSION['selectedAnalytes']); $row++) {
		
		$currentAnalyte = "analyte" .$row; //used to make a unique array name; example "analyte0"
		$$currentAnalyte = array("aid"=>$_SESSION['selectedAnalytes'][$row][0], "name"=>$_SESSION['selectedAnalytes'][$row][1], 
		    "concentration"=>$_SESSION['selectedAnalytes'][$row][2], "unit"=>$_SESSION['selectedAnalytes'][$row][3]);
		
		array_push($analyteData,$$currentAnalyte);
	}
	
	//loop through analyte data
	for ($row = 0; $row < count($analyteData); $row++) {
		
		$cs_mg = $sfo_mg = $rfdo_mg = $abs = "";
		
        //concentration provided by user. if in micrograms, convert to mg, save the new value in the array and update the units to mg		
		if ($analyteData[$row]["unit"] == "ug") {
			$cs_mg = ($analyteData[$row]["concentration"]) / 1000;
			$analyteData[$row]["concentration"] = $cs_mg;
			$analyteData[$row]["unit"] = "mg";
				
		}else {
			$cs_mg = $analyteData[$row]["concentration"];
		}
		
		//check which pathways we need to calculate values for
		if ($_SESSION['oral']) {	
			
			//Need to get SFO and RfDo values from DB. Check if values previously obtained first
			if (!isset ($analyteData[$row]["sfo"])) {
				
				$sfo_mg = getSfo($_SESSION['regulator'],$analyteData[$row]["aid"]);
			    $analyteData[$row]["sfo"] = $sfo_mg;
				
			}
			
			if (!isset ($analyteData[$row]["rfdo"])) {
				
				$rfdo_mg = getRfdo($_SESSION['regulator'],$analyteData[$row]["aid"]);
			    $analyteData[$row]["rfdo"] = $rfdo_mg;
				
			}
			
			//check if we are calculating for resident or worker
			if ($_SESSION['receptor'] == "resident") {
				
				//calculate risk value
				$analyteOralRisk = oralRiskResident($sfo_mg, $cs_mg);
				$oralRisk = $oralRisk + $analyteOralRisk;
				
				//save analyte risk value to analyeData array to display later
				$analyteData[$row]["oralRisk"] = $analyteOralRisk;
				$analyteData[$row]["oralRiskCo"] = getCoefficient($analyteOralRisk);
				$analyteData[$row]["oralRiskEx"] = getExponent($analyteOralRisk);
				
				//calculate haz value
				$analyteOralHaz = oralHazResident($rfdo_mg, $cs_mg);
				$oralHaz = $oralHaz + $analyteOralHaz;
				
				//save analyte haz value to analyteData array to display later
				$analyteData[$row]["oralHaz"] = $analyteOralHaz;
				$analyteData[$row]["oralHazCo"] = getCoefficient($analyteOralHaz);
				$analyteData[$row]["oralHazEx"] = getExponent($analyteOralHaz);
			
			//otherwise, we need to calculate for worker
			}else {
				
				//calculate risk value
				$analyteOralRisk = oralRiskWorker($sfo_mg, $cs_mg);
				$oralRisk = $oralRisk + $analyteOralRisk;
				
				//save analyte risk value to analyeData array to display later
				$analyteData[$row]["oralRisk"] = $analyteOralRisk;
				$analyteData[$row]["oralRiskCo"] = getCoefficient($analyteOralRisk);
				$analyteData[$row]["oralRiskEx"] = getExponent($analyteOralRisk);
				
				//calculate haz value
				$analyteOralHaz = oralHazWorker($rfdo_mg, $cs_mg);
				$oralHaz = $oralHaz + $analyteOralHaz;
				
				//save analyte haz value to analyteData array to display later
				$analyteData[$row]["oralHaz"] = $analyteOralHaz;
				$analyteData[$row]["oralHazCo"] = getCoefficient($analyteOralHaz);
				$analyteData[$row]["oralHazEx"] = getExponent($analyteOralHaz);
				
			}
		}
		
		if ($_SESSION['inhalation']) {
			//inhalation will be done in a later phase
			
		}
		
		if ($_SESSION['dermal']) {
			
			//Need to get SFO, RfDo, and ABS values from DB. Check if values previously obtained first
			if (!isset ($analyteData[$row]["sfo"])) {
				
				$sfo_mg = getSfo($_SESSION['regulator'],$analyteData[$row]["aid"]);
			    $analyteData[$row]["sfo"] = $sfo_mg;
				
			}
			
			if (!isset ($analyteData[$row]["rfdo"])) {
				
				$rfdo_mg = getRfdo($_SESSION['regulator'],$analyteData[$row]["aid"]);
			    $analyteData[$row]["rfdo"] = $rfdo_mg;
				
			}
			
			if (!isset ($analyteData[$row]["abs"])) {
				
				$abs = getAbs($_SESSION['regulator'],$analyteData[$row]["aid"]);
			    $analyteData[$row]["abs"] = $abs;
				
			}
			
			//check if we are calculating for resident or worker
			if ($_SESSION['receptor'] == "resident") {
				
				//calculate risk value
				$analyteDermalRisk = dermalRiskResident($sfo_mg, $cs_mg, $abs);
				$dermalRisk = $dermalRisk + $analyteDermalRisk;
				
				//save analyte risk value to analyeData array to display later
				$analyteData[$row]["dermalRisk"] = $analyteDermalRisk;
				$analyteData[$row]["dermalRiskCo"] = getCoefficient($analyteDermalRisk);
				$analyteData[$row]["dermalRiskEx"] = getExponent($analyteDermalRisk);
				
				//calculate haz value
				$analyteDermalHaz = dermalHazResident($rfdo_mg, $cs_mg, $abs);
				$dermalHaz = $dermalHaz + $analyteDermalHaz;
				
				//save analyte haz value to analyteData array to display later
				$analyteData[$row]["dermalHaz"] = $analyteDermalHaz;
				$analyteData[$row]["dermalHazCo"] = getCoefficient($analyteDermalHaz);
				$analyteData[$row]["dermalHazEx"] = getExponent($analyteDermalHaz);
			
			//otherwise, we need to calculate for worker
			}else {
				
				//calculate risk value
				$analyteDermalRisk = dermalRiskWorker($sfo_mg, $cs_mg, $abs);
				$dermalRisk = $dermalRisk + $analyteDermalRisk;
				
				//save analyte risk value to analyeData array to display later
				$analyteData[$row]["dermalRisk"] = $analyteDermalRisk;
				$analyteData[$row]["dermalRiskCo"] = getCoefficient($analyteDermalRisk);
				$analyteData[$row]["dermalRiskEx"] = getExponent($analyteDermalRisk);
				
				//calculate haz value
				$analyteDermalHaz = dermalHazWorker($rfdo_mg, $cs_mg, $abs);
				$dermalHaz = $dermalHaz + $analyteDermalHaz;
				
				//save analyte haz value to analyteData array to display later
				$analyteData[$row]["dermalHaz"] = $analyteDermalHaz;
				$analyteData[$row]["dermalHazCo"] = getCoefficient($analyteDermalHaz);
				$analyteData[$row]["dermalHazEx"] = getExponent($analyteDermalHaz);
				
			}
		}
	}
	
	$riskTotal = $oralRisk + $inhalationRisk + $dermalRisk;
	
	$oralRiskCo = getCoefficient($oralRisk);
	$oralRiskEx = getExponent($oralRisk);
	$inhalationRiskCo = getCoefficient($inhalationRisk);
	$inhalationRiskEx = getExponent($inhalationRisk);
	$dermalRiskCo = getCoefficient($dermalRisk);
	$dermalRiskEx = getExponent($dermalRisk);
	$riskTotalCo = getCoefficient($riskTotal);
	$riskTotalEx = getExponent($riskTotal);
	
	$hazTotal = $oralHaz + $inhalationHaz + $dermalHaz;
	
	$oralHazCo = getCoefficient($oralHaz);
	$oralHazEx = getExponent($oralHaz);
	$inhalationHazCo = getCoefficient($inhalationHaz);
	$inhalationHazEx = getExponent($inhalationHaz);
	$dermalHazCo = getCoefficient($dermalHaz);
	$dermalHazEx = getExponent($dermalHaz);
	$hazTotalCo = getCoefficient($hazTotal);
	$hazTotalEx = getExponent($hazTotal);
	
?>

<!DOCTYPE html>
<html lang="en">
    <head>
        <!-- Required meta tags -->
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">

        <!-- Bootstrap CSS -->
        <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/4.0.0-alpha.6/css/bootstrap.min.css" integrity="sha384-rwoIResjU2yc3z8GV/NPeZWAv56rSmLldC3R/AZzGRnGxQQKnKkoFVhFQhNUwEyJ" crossorigin="anonymous">
		
		<link rel="stylesheet" href="http://www.riskhaz.com/css/styles.css">
		
		<title>Risk & Hazard Calculator</title>
	</head>
    <body>
	    <div class="container nav-container">
			<nav class="navbar navbar-toggleable navbar-light nav-element">
				<button class="navbar-toggler navbar-toggler-right" type="button" data-toggle="collapse" data-target="#navbarSupportedContent" aria-controls="navbarSupportedContent" aria-expanded="false" aria-label="Toggle navigation">
					<span class="navbar-toggler-icon"></span>
				</button>
				<a class="navbar-brand" id="site-name" href="http://www.riskhaz.com/calculator">RiskHaz</a>

				<div class="collapse navbar-collapse" id="navbarSupportedContent">
					<ul class="navbar-nav ml-auto justify-content-end">
						<li class="nav-item active">
							<a class="nav-link" href="http://www.riskhaz.com/calculator">Calculator <span class="sr-only">(current)</span></a>
						</li>
						<li class="nav-item">
							<a class="nav-link" href="http://www.riskhaz.com/about">About</a>
						</li>
						<li class="nav-item">
							<a class="nav-link" href="http://www.riskhaz.com/contact">Contact</a>
						</li>
					</ul>
				</div>
			</nav>
		</div>
		
		<div class="container-fluid top-border">
		</div>
		
		<div class="container">
		    <div class="row content-label">
			    <p class="h5">Results</p>
			</div>
			
			<div class="row content">
				<div class="col-4 no-padding">
				    <span class="results-label">Risk</span><br>
					<span><?php echo $riskTotalCo." x 10<sup>".$riskTotalEx."</sup>";?></span>
				</div>		
				<div class="col-4 no-padding">
				    <span class="results-label">Hazard</span><br>
					<span><?php echo $hazTotalCo." x 10<sup>".$hazTotalEx."</sup>";?></span>
				</div>	
			</div>
			
			<div class="row buttons-submit">
				<a href="http://www.riskhaz.com/calculator/"><button type="button" class="btn btn-primary">Home</button></a>
			</div>
		</div>
		
		<div class="spacer-40px"></div>
		
		<div class="container">
		    <div class="row content" id="calculations-header">
			    <p class="h5">Calculations</p>
			</div>
			
			<div class="row content grey-border no-margin-top-bottom padding-regulator">
				<span class="regulator-receptor-labels">
				    <?php 
					    if ($_SESSION['regulator']=="california"){
							echo 'Regulator: California';
						}else {
							echo 'Regulator: Federal';
						}
					?>
				</span>
				<span class="regulator-receptor-labels margin-left-20px">
				    <?php 
					    if ($_SESSION['receptor']=="resident"){
							echo 'Receptor: Resident';
						}else {
							echo 'Receptor: Worker';
						}
					?>
				</span>
			</div>
			
			<!--Oral Risk, Resident-->
			<div id="oralRiskResident">
			    <div class="row content grey-border no-margin-top-bottom padding-subheader">
			        <span class="calculations-subheader">Oral Risk</span>
			    </div>
			
			    <div class="row content grey-border no-margin-top-bottom padding-formula">
			        <!--Mobile Display -->
			        <div class="col-lg hidden-sm-up">
				        <span class="formula-small">SF<sub>o</sub> x C<sub>s</sub> x </span>
				        <span class="fraction-small">
					        <span class="numerator">IR<sub>s, adult</sub> x EF<sub>resident</sub> x ED<sub>adult</sub> x 10<sup>-6</sup> kg/mg</span>
					        <span class="denominator">BW<sub>adult</sub> x AT</span>
				        </span>
				    </div>	
				    <div class="col-1 hidden-sm-up">	
				        <span class="plus-small"> + </span>
				    </div>
				    <div class="col-lg hidden-sm-up">
				        <span class="formula-small">SF<sub>o</sub> x C<sub>s</sub> x </span>
				        <span class="fraction-small">
					        <span class="numerator">IR<sub>s, child</sub> x EF<sub>resident</sub> x ED<sub>child</sub> x 10<sup>-6</sup> kg/mg</span>
					        <span class="denominator">BW<sub>child</sub> x AT</span>
				        </span>
				    </div>
			    
				    <!-- Standard Display -->
			        <div class="col-lg hidden-xs-down hidden-xl-up">
				        <span class="formula">SF<sub>o</sub> x C<sub>s</sub> x </span>
				        <span class="fraction">
					        <span class="numerator">IR<sub>s, adult</sub> x EF<sub>resident</sub> x ED<sub>adult</sub> x 10<sup>-6</sup> kg/mg</span>
					        <span class="denominator">BW<sub>adult</sub> x AT</span>
				        </span>
				    </div>	
				    <div class="col-1 hidden-xs-down hidden-xl-up">	
				        <span class="plus"> + </span>
				    </div>
				    <div class="col-lg hidden-xs-down hidden-xl-up">
				        <span class="formula">SF<sub>o</sub> x C<sub>s</sub> x </span>
				        <span class="fraction">
					        <span class="numerator">IR<sub>s, child</sub> x EF<sub>resident</sub> x ED<sub>child</sub> x 10<sup>-6</sup> kg/mg</span>
					        <span class="denominator">BW<sub>child</sub> x AT</span>
				        </span>
				    </div>
				
				    <!-- Extra Large Display -->
				    <div class="col-lg hidden-lg-down">
				        <span class="formula">SF<sub>o</sub> x C<sub>s</sub> x </span>
				        <span class="fraction">
					        <span class="numerator">IR<sub>s, adult</sub> x EF<sub>resident</sub> x ED<sub>adult</sub> x 10<sup>-6</sup> kg/mg</span>
					        <span class="denominator">BW<sub>adult</sub> x AT</span>
				        </span>
					    <span class="plus padding-plus-xl"> + </span>
				        <span class="formula">SF<sub>o</sub> x C<sub>s</sub> x </span>
				        <span class="fraction">
					        <span class="numerator">IR<sub>s, child</sub> x EF<sub>resident</sub> x ED<sub>child</sub> x 10<sup>-6</sup> kg/mg</span>
					        <span class="denominator">BW<sub>child</sub> x AT</span>
				        </span>
				    </div>
			    </div>
			
			    <div class="row content grey-border no-margin-top-bottom padding-variables-header">
			        <span class="variables-header">Constants</span>
			    </div>
			
			    <div class="row content grey-border no-margin-top-bottom padding-variables">
			        <!--Mobile Display -->
			        <div class="col-lg-6 hidden-sm-up">
				        <span class="variables-small">IR<sub>s, adult</sub> = incidental soil ingestion rate (<?php echo $irsAdult_mg; ?> mg/day)</span><br>
					    <span class="variables-small">IR<sub>s, child</sub> = incidental soil ingestion rate (<?php echo $irsChild_mg; ?> mg/day)</span><br>
						<span class="variables-small">EF<sub>resident</sub> = exposure frequency (<?php echo $efResident_days; ?> days/year)</span><br>
					    <span class="variables-small">ED<sub>adult</sub> = exposure duration (<?php echo $edAdult_years; ?> years)</span><br>
				    </div>
				    <div class="col-lg-6 hidden-sm-up">
					    <span class="variables-small">ED<sub>child</sub> = exposure duration (<?php echo $edChild_years; ?> years)</span><br>
				        <span class="variables-small">BW<sub>adult</sub> = body weight (<?php echo $bwAdult_kg; ?> kg)</span><br>
					    <span class="variables-small">BW<sub>child</sub> = body weight (<?php echo $bwChild_kg; ?> kg)</span><br>
					    <span class="variables-small">AT = averaging time (<?php echo number_format($atAllRisk_days); ?> days)</span><br>
				    </div>
				
				    <!--Standard - XL Display -->
				    <div class="col-lg-6 hidden-xs-down">
				        <span class="variables">IR<sub>s, adult</sub> = incidental soil ingestion rate (<?php echo $irsAdult_mg; ?> mg/day)</span><br>
					    <span class="variables">IR<sub>s, child</sub> = incidental soil ingestion rate (<?php echo $irsChild_mg; ?> mg/day)</span><br>
						<span class="variables">EF<sub>resident</sub> = exposure frequency (<?php echo $efResident_days; ?> days/year)</span><br>
					    <span class="variables">ED<sub>adult</sub> = exposure duration (<?php echo $edAdult_years; ?> years)</span><br>
					    
				    </div>
				    <div class="col-lg-6 hidden-xs-down">
					    <span class="variables">ED<sub>child</sub> = exposure duration (<?php echo $edChild_years; ?> years)</span><br>
				        <span class="variables">BW<sub>adult</sub> = body weight (<?php echo $bwAdult_kg; ?> kg)</span><br>
					    <span class="variables">BW<sub>child</sub> = body weight (<?php echo $bwChild_kg; ?> kg)</span><br>
					    <span class="variables">AT = averaging time (<?php echo number_format($atAllRisk_days); ?> days)</span><br>
				    </div>
			    </div>
				
				<!--Loop through analyte data array to populate-->
				<?php
				    for ($row = 0; $row < count($analyteData); $row++) {
						echo "\n";
					    echo "\t\t\t\t".'<div class="row content grey-border no-margin-top-bottom padding-variables-header">'."\n";
						    echo "\t\t\t\t\t".'<span class="variables-header">'.$analyteData[$row]["name"].'</span>'."\n";
						echo "\t\t\t\t".'</div>'."\n";
						echo "\n";
						echo "\t\t\t\t".'<div class="row content grey-border no-margin-top-bottom padding-variables analyte-values-padding">'."\n";
						echo "\t\t\t\t\t".'<!--Mobile Display -->'."\n";
						echo "\t\t\t\t\t".'<div class="col-lg-6 hidden-sm-up">'."\n";
						echo "\t\t\t\t\t\t".'<span class="variables-small">SF<sub>o</sub> = slope factor (['.$analyteData[$row]["sfo"].' mg/kg]<sup>-1</sup>)</span><br>'."\n";
						echo "\t\t\t\t\t".'</div>'."\n";
						echo "\t\t\t\t\t".'<div class="col-lg-6 hidden-sm-up">'."\n";
						echo "\t\t\t\t\t\t".'<span class="variables-small">C<sub>s</sub> = concentration in soil ('.$analyteData[$row]["concentration"].' mg/kg)</span><br>'."\n";
						echo "\t\t\t\t\t".'</div>'."\n";
						echo "\n";
						echo "\t\t\t\t\t".'<!--Standard - XL Display -->'."\n";
						echo "\t\t\t\t\t".'<div class="col-lg-6 hidden-xs-down">'."\n";
						echo "\t\t\t\t\t\t".'<span class="variables">SF<sub>o</sub> = slope factor (['.$analyteData[$row]["sfo"].' mg/kg]<sup>-1</sup>)</span><br>'."\n";
						echo "\t\t\t\t\t".'</div>'."\n";
						echo "\t\t\t\t\t".'<div class="col-lg-6 hidden-xs-down">'."\n";
						echo "\t\t\t\t\t\t".'<span class="variables">C<sub>s</sub> = concentration in soil ('.$analyteData[$row]["concentration"].' mg/kg)</span><br>'."\n";
						echo "\t\t\t\t\t".'</div>'."\n";
						echo "\t\t\t\t".'</div>'."\n";
						echo "\n";
						echo "\t\t\t\t".'<!--Analyte risk value -->'."\n";
						echo "\t\t\t\t".'<div class="row grey-border no-margin-top-bottom analyte-risk">'."\n";
						echo "\t\t\t\t\t".'<span class="variables-small hidden-sm-up">Risk = '.$analyteData[$row]["oralRiskCo"].' x 10<sup>'.$analyteData[$row]["oralRiskEx"].'</sup></span>'."\n";
						echo "\t\t\t\t\t".'<span class="variables hidden-xs-down">Risk = '.$analyteData[$row]["oralRiskCo"].' x 10<sup>'.$analyteData[$row]["oralRiskEx"].'</sup></span>'."\n";
						echo "\t\t\t\t".'</div>'."\n";
					}
				?>
			
			    <div class="row content grey-border no-margin-top-bottom padding-variables-header">
			        <span class="variables-header">Total Oral Risk</span>
			    </div>
			
			    <div class="row content grey-border no-margin-top-bottom padding-variables">
			        <!--Mobile Display -->
			        <div class="col-lg hidden-sm-up">
				        <span class="variables-small"><?php echo $oralRiskCo." x 10<sup>".$oralRiskEx."</sup>";?></span><br>
				    </div>
				
				    <!--Standard - XL Display -->
				    <div class="col-lg hidden-xs-down">
				        <span class="variables"><?php echo $oralRiskCo." x 10<sup>".$oralRiskEx."</sup>";?></span><br>
				    </div>
			    </div>
				
			</div>
			
			<!--Oral Risk, Worker-->
			<div id="oralRiskWorker">
			    <div class="row content grey-border no-margin-top-bottom padding-subheader">
			        <span class="calculations-subheader">Oral Risk</span>
			    </div>
			
			    <div class="row content grey-border no-margin-top-bottom padding-formula">
			        <!--Mobile Display -->
			        <div class="col-lg hidden-sm-up">
				        <span class="formula-small">SF<sub>o</sub> x C<sub>s</sub> x </span>
				        <span class="fraction-small">
					        <span class="numerator">IR<sub>s, adult</sub> x EF<sub>worker</sub> x ED<sub>worker</sub> x 10<sup>-6</sup> kg/mg</span>
					        <span class="denominator">BW<sub>adult</sub> x AT</span>
				        </span>
				    </div>	
			    
				    <!-- Standard Display -->
			        <div class="col-lg hidden-xs-down hidden-xl-up">
				        <span class="formula">SF<sub>o</sub> x C<sub>s</sub> x </span>
				        <span class="fraction">
					        <span class="numerator">IR<sub>s, adult</sub> x EF<sub>worker</sub> x ED<sub>worker</sub> x 10<sup>-6</sup> kg/mg</span>
					        <span class="denominator">BW<sub>adult</sub> x AT</span>
				        </span>
				    </div>	
				
				    <!-- Extra Large Display -->
				    <div class="col-lg hidden-lg-down">
				        <span class="formula">SF<sub>o</sub> x C<sub>s</sub> x </span>
				        <span class="fraction">
					        <span class="numerator">IR<sub>s, adult</sub> x EF<sub>worker</sub> x ED<sub>worker</sub> x 10<sup>-6</sup> kg/mg</span>
					        <span class="denominator">BW<sub>adult</sub> x AT</span>
				        </span>
				    </div>
			    </div>
			
			    <div class="row content grey-border no-margin-top-bottom padding-variables-header">
			        <span class="variables-header">Constants</span>
			    </div>
			
			    <div class="row content grey-border no-margin-top-bottom padding-variables">
			        <!--Mobile Display -->
			        <div class="col-lg-6 hidden-sm-up">
				        <span class="variables-small">IR<sub>s, adult</sub> = incidental soil ingestion rate (<?php echo $irsAdult_mg; ?> mg/day)</span><br>
						<span class="variables-small">EF<sub>worker</sub> = exposure frequency (<?php echo $efWorker_days; ?> days/year)</span><br>
					    <span class="variables-small">ED<sub>worker</sub> = exposure duration (<?php echo $edWorker_years; ?> years)</span><br>
				    </div>
				    <div class="col-lg-6 hidden-sm-up">
				        <span class="variables-small">BW<sub>adult</sub> = body weight (<?php echo $bwAdult_kg; ?> kg)</span><br>
					    <span class="variables-small">AT = averaging time (<?php echo number_format($atAllRisk_days); ?> days)</span><br>
				    </div>
				
				    <!--Standard - XL Display -->
				    <div class="col-lg-6 hidden-xs-down">
				        <span class="variables">IR<sub>s, adult</sub> = incidental soil ingestion rate (<?php echo $irsAdult_mg; ?> mg/day)</span><br>
						<span class="variables">EF<sub>worker</sub> = exposure frequency (<?php echo $efWorker_days; ?> days/year)</span><br>
					    <span class="variables">ED<sub>worker</sub> = exposure duration (<?php echo $edWorker_years; ?> years)</span><br>
				    </div>
				    <div class="col-lg-6 hidden-xs-down">
				        <span class="variables">BW<sub>adult</sub> = body weight (<?php echo $bwAdult_kg; ?> kg)</span><br>
					    <span class="variables">AT = averaging time (<?php echo number_format($atAllRisk_days); ?> days)</span><br>
				    </div>
			    </div>
				
				<!--Loop through analyte data array to populate-->
				<?php
				    for ($row = 0; $row < count($analyteData); $row++) {
						echo "\n";
					    echo "\t\t\t\t".'<div class="row content grey-border no-margin-top-bottom padding-variables-header">'."\n";
						    echo "\t\t\t\t\t".'<span class="variables-header">'.$analyteData[$row]["name"].'</span>'."\n";
						echo "\t\t\t\t".'</div>'."\n";
						echo "\n";
						echo "\t\t\t\t".'<div class="row content grey-border no-margin-top-bottom padding-variables analyte-values-padding">'."\n";
						echo "\t\t\t\t\t".'<!--Mobile Display -->'."\n";
						echo "\t\t\t\t\t".'<div class="col-lg-6 hidden-sm-up">'."\n";
						echo "\t\t\t\t\t\t".'<span class="variables-small">SF<sub>o</sub> = slope factor (['.$analyteData[$row]["sfo"].' mg/kg]<sup>-1</sup>)</span><br>'."\n";
						echo "\t\t\t\t\t".'</div>'."\n";
						echo "\t\t\t\t\t".'<div class="col-lg-6 hidden-sm-up">'."\n";
						echo "\t\t\t\t\t\t".'<span class="variables-small">C<sub>s</sub> = concentration in soil ('.$analyteData[$row]["concentration"].' mg/kg)</span><br>'."\n";
						echo "\t\t\t\t\t".'</div>'."\n";
						echo "\n";
						echo "\t\t\t\t\t".'<!--Standard - XL Display -->'."\n";
						echo "\t\t\t\t\t".'<div class="col-lg-6 hidden-xs-down">'."\n";
						echo "\t\t\t\t\t\t".'<span class="variables">SF<sub>o</sub> = slope factor (['.$analyteData[$row]["sfo"].' mg/kg]<sup>-1</sup>)</span><br>'."\n";
						echo "\t\t\t\t\t".'</div>'."\n";
						echo "\t\t\t\t\t".'<div class="col-lg-6 hidden-xs-down">'."\n";
						echo "\t\t\t\t\t\t".'<span class="variables">C<sub>s</sub> = concentration in soil ('.$analyteData[$row]["concentration"].' mg/kg)</span><br>'."\n";
						echo "\t\t\t\t\t".'</div>'."\n";
						echo "\t\t\t\t".'</div>'."\n";
						echo "\n";
						echo "\t\t\t\t".'<!--Analyte risk value -->'."\n";
						echo "\t\t\t\t".'<div class="row grey-border no-margin-top-bottom analyte-risk">'."\n";
						echo "\t\t\t\t\t".'<span class="variables-small hidden-sm-up">Risk = '.$analyteData[$row]["oralRiskCo"].' x 10<sup>'.$analyteData[$row]["oralRiskEx"].'</sup></span>'."\n";
						echo "\t\t\t\t\t".'<span class="variables hidden-xs-down">Risk = '.$analyteData[$row]["oralRiskCo"].' x 10<sup>'.$analyteData[$row]["oralRiskEx"].'</sup></span>'."\n";
						echo "\t\t\t\t".'</div>'."\n";
					}
				?>
			
			    <div class="row content grey-border no-margin-top-bottom padding-variables-header">
			        <span class="variables-header">Total Oral Risk</span>
			    </div>
			
			    <div class="row content grey-border no-margin-top-bottom padding-variables">
			        <!--Mobile Display -->
			        <div class="col-lg hidden-sm-up">
				        <span class="variables-small"><?php echo $oralRiskCo." x 10<sup>".$oralRiskEx."</sup>";?></span><br>
				    </div>
				
				    <!--Standard - XL Display -->
				    <div class="col-lg hidden-xs-down">
				        <span class="variables"><?php echo $oralRiskCo." x 10<sup>".$oralRiskEx."</sup>";?></span><br>
				    </div>
			    </div>
				
			</div>
			
			<!--Oral Hazard, Resident-->
			<div id="oralHazResident">
			    <div class="row content grey-border no-margin-top-bottom padding-subheader">
			        <span class="calculations-subheader">Oral Hazard</span>
			    </div>
			
			    <div class="row content grey-border no-margin-top-bottom padding-formula">
			        <!--Mobile Display -->
			        <div class="col-lg hidden-sm-up">
					     <span class="fraction-small">
						    <span class="numerator">&nbsp;&nbsp;&nbsp;1&nbsp;&nbsp;&nbsp;</span>
						    <span class="denominator">RfD<sub>o</sub></span>
						</span>
						<span class="formula-small">x C<sub>s</sub> x</span>
				        <span class="fraction-small">
					        <span class="numerator">IR<sub>s, child</sub> x EF<sub>resident</sub> x ED<sub>child</sub> x 10<sup>-6</sup> kg/mg</span>
					        <span class="denominator">BW<sub>child</sub> x AT<sub>child</sub></span>
				        </span>
				    </div>
			    
				    <!-- Standard Display -->
			        <div class="col-lg hidden-xs-down hidden-xl-up">
					    <span class="fraction">
						    <span class="numerator">&nbsp;&nbsp;&nbsp;1&nbsp;&nbsp;&nbsp;</span>
						    <span class="denominator">RfD<sub>o</sub></span>
						</span>
				        <span class="formula">x C<sub>s</sub> x </span>
				        <span class="fraction">
					        <span class="numerator">IR<sub>s, child</sub> x EF<sub>resident</sub> x ED<sub>child</sub> x 10<sup>-6</sup> kg/mg</span>
					        <span class="denominator">BW<sub>child</sub> x AT<sub>child</sub></span>
				        </span>
				    </div>	
				
				    <!-- Extra Large Display -->
				    <div class="col-lg hidden-lg-down">
					    <span class="fraction">
						    <span class="numerator">&nbsp;&nbsp;&nbsp;1&nbsp;&nbsp;&nbsp;</span>
						    <span class="denominator">RfD<sub>o</sub></span>
						</span>
				        <span class="formula"> x C<sub>s</sub> x </span>
				        <span class="fraction">
					        <span class="numerator">IR<sub>s, child</sub> x EF<sub>resident</sub> x ED<sub>child</sub> x 10<sup>-6</sup> kg/mg</span>
					        <span class="denominator">BW<sub>child</sub> x AT<sub>child</sub></span>
				        </span>
				    </div>
			    </div>
			
			    <div class="row content grey-border no-margin-top-bottom padding-variables-header">
			        <span class="variables-header">Constants</span>
			    </div>
			
			    <div class="row content grey-border no-margin-top-bottom padding-variables">
			        <!--Mobile Display -->
			        <div class="col-lg-6 hidden-sm-up">
					    <span class="variables-small">IR<sub>s, child</sub> = incidental soil ingestion rate (<?php echo $irsChild_mg; ?> mg/day)</span><br>
						<span class="variables-small">EF<sub>resident</sub> = exposure frequency (<?php echo $efResident_days; ?> days/year)</span><br>
						<span class="variables-small">ED<sub>child</sub> = exposure duration (<?php echo $edChild_years; ?> years)</span><br>
				    </div>
				    <div class="col-lg-6 hidden-sm-up">
					    <span class="variables-small">BW<sub>child</sub> = body weight (<?php echo $bwChild_kg; ?> kg)</span><br>
					    <span class="variables-small">AT<sub>child</sub> = averaging time (<?php echo number_format($atChild_days); ?> days)</span><br>
				    </div>
				
				    <!--Standard - XL Display -->
				    <div class="col-lg-6 hidden-xs-down">
					    <span class="variables">IR<sub>s, child</sub> = incidental soil ingestion rate (<?php echo $irsChild_mg; ?> mg/day)</span><br>
						<span class="variables">EF<sub>resident</sub> = exposure frequency (<?php echo $efResident_days; ?> days/year)</span><br>
					    <span class="variables">ED<sub>child</sub> = exposure duration (<?php echo $edChild_years; ?> years)</span><br>
					    
				    </div>
				    <div class="col-lg-6 hidden-xs-down">
					    <span class="variables">BW<sub>child</sub> = body weight (<?php echo $bwChild_kg; ?> kg)</span><br>
					    <span class="variables">AT<sub>child</sub> = averaging time (<?php echo number_format($atChild_days); ?> days)</span><br>
				    </div>
			    </div>
				
				<!--Loop through analyte data array to populate-->
				<?php
				    for ($row = 0; $row < count($analyteData); $row++) {
						echo "\n";
					    echo "\t\t\t\t".'<div class="row content grey-border no-margin-top-bottom padding-variables-header">'."\n";
						    echo "\t\t\t\t\t".'<span class="variables-header">'.$analyteData[$row]["name"].'</span>'."\n";
						echo "\t\t\t\t".'</div>'."\n";
						echo "\n";
						echo "\t\t\t\t".'<div class="row content grey-border no-margin-top-bottom padding-variables analyte-values-padding">'."\n";
						echo "\t\t\t\t\t".'<!--Mobile Display -->'."\n";
						echo "\t\t\t\t\t".'<div class="col-lg-6 hidden-sm-up">'."\n";
						echo "\t\t\t\t\t\t".'<span class="variables-small">RfD<sub>o</sub> = oral reference dose ('.$analyteData[$row]["rfdo"].' mg/kg)</span><br>'."\n";
						echo "\t\t\t\t\t".'</div>'."\n";
						echo "\t\t\t\t\t".'<div class="col-lg-6 hidden-sm-up">'."\n";
						echo "\t\t\t\t\t\t".'<span class="variables-small">C<sub>s</sub> = concentration in soil ('.$analyteData[$row]["concentration"].' mg/kg)</span><br>'."\n";
						echo "\t\t\t\t\t".'</div>'."\n";
						echo "\n";
						echo "\t\t\t\t\t".'<!--Standard - XL Display -->'."\n";
						echo "\t\t\t\t\t".'<div class="col-lg-6 hidden-xs-down">'."\n";
						echo "\t\t\t\t\t\t".'<span class="variables">RfD<sub>o</sub> = oral reference dose ('.$analyteData[$row]["rfdo"].' mg/kg)</span><br>'."\n";
						echo "\t\t\t\t\t".'</div>'."\n";
						echo "\t\t\t\t\t".'<div class="col-lg-6 hidden-xs-down">'."\n";
						echo "\t\t\t\t\t\t".'<span class="variables">C<sub>s</sub> = concentration in soil ('.$analyteData[$row]["concentration"].' mg/kg)</span><br>'."\n";
						echo "\t\t\t\t\t".'</div>'."\n";
						echo "\t\t\t\t".'</div>'."\n";
						echo "\n";
						echo "\t\t\t\t".'<!--Analyte hazard value -->'."\n";
						echo "\t\t\t\t".'<div class="row grey-border no-margin-top-bottom analyte-risk">'."\n";
						echo "\t\t\t\t\t".'<span class="variables-small hidden-sm-up">Hazard = '.$analyteData[$row]["oralHazCo"].' x 10<sup>'.$analyteData[$row]["oralHazEx"].'</sup></span>'."\n";
						echo "\t\t\t\t\t".'<span class="variables hidden-xs-down">Hazard = '.$analyteData[$row]["oralHazCo"].' x 10<sup>'.$analyteData[$row]["oralHazEx"].'</sup></span>'."\n";
						echo "\t\t\t\t".'</div>'."\n";
					}
				?>
			
			    <div class="row content grey-border no-margin-top-bottom padding-variables-header">
			        <span class="variables-header">Total Oral Hazard</span>
			    </div>
			
			    <div class="row content grey-border no-margin-top-bottom padding-variables">
			        <!--Mobile Display -->
			        <div class="col-lg hidden-sm-up">
				        <span class="variables-small"><?php echo $oralHazCo." x 10<sup>".$oralHazEx."</sup>";?></span><br>
				    </div>
				
				    <!--Standard - XL Display -->
				    <div class="col-lg hidden-xs-down">
				        <span class="variables"><?php echo $oralHazCo." x 10<sup>".$oralHazEx."</sup>";?></span><br>
				    </div>
			    </div>
				
			</div>
			
			<!--Oral Hazard, Worker-->
			<div id="oralHazWorker">
			    <div class="row content grey-border no-margin-top-bottom padding-subheader">
			        <span class="calculations-subheader">Oral Hazard</span>
			    </div>
			
			    <div class="row content grey-border no-margin-top-bottom padding-formula">
			        <!--Mobile Display -->
			        <div class="col-lg hidden-sm-up">
					    <span class="fraction-small">
						    <span class="numerator">&nbsp;&nbsp;&nbsp;1&nbsp;&nbsp;&nbsp;</span>
						    <span class="denominator">RfD<sub>o</sub></span>
						</span>
						<span class="formula-small">x C<sub>s</sub> x</span>
				        <span class="fraction-small">
					        <span class="numerator">IR<sub>s, adult</sub> x EF<sub>worker</sub> x ED<sub>worker</sub> x 10<sup>-6</sup> kg/mg</span>
					        <span class="denominator">BW<sub>adult</sub> x AT<sub>worker</sub></span>
				        </span>
				    </div>
			    
				    <!-- Standard Display -->
			        <div class="col-lg hidden-xs-down hidden-xl-up">
					    <span class="fraction">
						    <span class="numerator">&nbsp;&nbsp;&nbsp;1&nbsp;&nbsp;&nbsp;</span>
						    <span class="denominator">RfD<sub>o</sub></span>
						</span>
				        <span class="formula">x C<sub>s</sub> x </span>
				        <span class="fraction">
					        <span class="numerator">IR<sub>s, adult</sub> x EF<sub>worker</sub> x ED<sub>worker</sub> x 10<sup>-6</sup> kg/mg</span>
					        <span class="denominator">BW<sub>adult</sub> x AT<sub>worker</sub></span>
				        </span>
				    </div>	
				
				    <!-- Extra Large Display -->
				    <div class="col-lg hidden-lg-down">
					    <span class="fraction">
						    <span class="numerator">&nbsp;&nbsp;&nbsp;1&nbsp;&nbsp;&nbsp;</span>
						    <span class="denominator">RfD<sub>o</sub></span>
						</span>
				        <span class="formula"> x C<sub>s</sub> x </span>
				        <span class="fraction">
					        <span class="numerator">IR<sub>s, adult</sub> x EF<sub>worker</sub> x ED<sub>worker</sub> x 10<sup>-6</sup> kg/mg</span>
					        <span class="denominator">BW<sub>adult</sub> x AT<sub>worker</sub></span>
				        </span>
				    </div>
			    </div>
			
			    <div class="row content grey-border no-margin-top-bottom padding-variables-header">
			        <span class="variables-header">Constants</span>
			    </div>
			
			    <div class="row content grey-border no-margin-top-bottom padding-variables">
			        <!--Mobile Display -->
			        <div class="col-lg-6 hidden-sm-up">
					    <span class="variables-small">IR<sub>s, adult</sub> = incidental soil ingestion rate (<?php echo $irsAdult_mg; ?> mg/day)</span><br>
						<span class="variables-small">EF<sub>worker</sub> = exposure frequency (<?php echo $efWorker_days; ?> days/year)</span><br>
						<span class="variables-small">ED<sub>worker</sub> = exposure duration (<?php echo $edWorker_years; ?> years)</span><br>
				    </div>
				    <div class="col-lg-6 hidden-sm-up">
					    <span class="variables-small">BW<sub>adult</sub> = body weight (<?php echo $bwAdult_kg; ?> kg)</span><br>
					    <span class="variables-small">AT<sub>worker</sub> = averaging time (<?php echo number_format($atWorker_days); ?> days)</span><br>
				    </div>
				
				    <!--Standard - XL Display -->
				    <div class="col-lg-6 hidden-xs-down">
					    <span class="variables">IR<sub>s, adult</sub> = incidental soil ingestion rate (<?php echo $irsAdult_mg; ?> mg/day)</span><br>
						<span class="variables">EF<sub>worker</sub> = exposure frequency (<?php echo $efWorker_days; ?> days/year)</span><br>
					    <span class="variables">ED<sub>worker</sub> = exposure duration (<?php echo $edWorker_years; ?> years)</span><br>
					    
				    </div>
				    <div class="col-lg-6 hidden-xs-down">
					    <span class="variables">BW<sub>adult</sub> = body weight (<?php echo $bwAdult_kg; ?> kg)</span><br>
					    <span class="variables">AT<sub>worker</sub> = averaging time (<?php echo number_format($atWorker_days); ?> days)</span><br>
				    </div>
			    </div>
				
				<!--Loop through analyte data array to populate-->
				<?php
				    for ($row = 0; $row < count($analyteData); $row++) {
						echo "\n";
					    echo "\t\t\t\t".'<div class="row content grey-border no-margin-top-bottom padding-variables-header">'."\n";
						    echo "\t\t\t\t\t".'<span class="variables-header">'.$analyteData[$row]["name"].'</span>'."\n";
						echo "\t\t\t\t".'</div>'."\n";
						echo "\n";
						echo "\t\t\t\t".'<div class="row content grey-border no-margin-top-bottom padding-variables analyte-values-padding">'."\n";
						echo "\t\t\t\t\t".'<!--Mobile Display -->'."\n";
						echo "\t\t\t\t\t".'<div class="col-lg-6 hidden-sm-up">'."\n";
						echo "\t\t\t\t\t\t".'<span class="variables-small">RfD<sub>o</sub> = oral reference dose ('.$analyteData[$row]["rfdo"].' mg/kg)</span><br>'."\n";
						echo "\t\t\t\t\t".'</div>'."\n";
						echo "\t\t\t\t\t".'<div class="col-lg-6 hidden-sm-up">'."\n";
						echo "\t\t\t\t\t\t".'<span class="variables-small">C<sub>s</sub> = concentration in soil ('.$analyteData[$row]["concentration"].' mg/kg)</span><br>'."\n";
						echo "\t\t\t\t\t".'</div>'."\n";
						echo "\n";
						echo "\t\t\t\t\t".'<!--Standard - XL Display -->'."\n";
						echo "\t\t\t\t\t".'<div class="col-lg-6 hidden-xs-down">'."\n";
						echo "\t\t\t\t\t\t".'<span class="variables">RfD<sub>o</sub> = oral reference dose ('.$analyteData[$row]["rfdo"].' mg/kg)</span><br>'."\n";
						echo "\t\t\t\t\t".'</div>'."\n";
						echo "\t\t\t\t\t".'<div class="col-lg-6 hidden-xs-down">'."\n";
						echo "\t\t\t\t\t\t".'<span class="variables">C<sub>s</sub> = concentration in soil ('.$analyteData[$row]["concentration"].' mg/kg)</span><br>'."\n";
						echo "\t\t\t\t\t".'</div>'."\n";
						echo "\t\t\t\t".'</div>'."\n";
						echo "\n";
						echo "\t\t\t\t".'<!--Analyte hazard value -->'."\n";
						echo "\t\t\t\t".'<div class="row grey-border no-margin-top-bottom analyte-risk">'."\n";
						echo "\t\t\t\t\t".'<span class="variables-small hidden-sm-up">Hazard = '.$analyteData[$row]["oralHazCo"].' x 10<sup>'.$analyteData[$row]["oralHazEx"].'</sup></span>'."\n";
						echo "\t\t\t\t\t".'<span class="variables hidden-xs-down">Hazard = '.$analyteData[$row]["oralHazCo"].' x 10<sup>'.$analyteData[$row]["oralHazEx"].'</sup></span>'."\n";
						echo "\t\t\t\t".'</div>'."\n";
					}
				?>
			
			    <div class="row content grey-border no-margin-top-bottom padding-variables-header">
			        <span class="variables-header">Total Oral Hazard</span>
			    </div>
			
			    <div class="row content grey-border no-margin-top-bottom padding-variables">
			        <!--Mobile Display -->
			        <div class="col-lg hidden-sm-up">
				        <span class="variables-small"><?php echo $oralHazCo." x 10<sup>".$oralHazEx."</sup>";?></span><br>
				    </div>
				
				    <!--Standard - XL Display -->
				    <div class="col-lg hidden-xs-down">
				        <span class="variables"><?php echo $oralHazCo." x 10<sup>".$oralHazEx."</sup>";?></span><br>
				    </div>
			    </div>
				
			</div>
			
			<!--Dermal Risk, Resident-->
			<div id="dermalRiskResident">
			    <div class="row content grey-border no-margin-top-bottom padding-subheader">
			        <span class="calculations-subheader">Dermal Risk</span>
			    </div>
			
			    <div class="row content grey-border no-margin-top-bottom padding-formula">
			        <!--Mobile Display -->
			        <div class="col-lg hidden-sm-up">
				        <p class="formula-small">SF<sub>o</sub> x C<sub>s</sub> x </p>
				        <p class="fraction-small">
					        <span class="numerator">SA<sub>adult</sub> x AF<sub>adult</sub> x ABS x EF<sub>resident</sub> x ED<sub>adult</sub> x 10<sup>-6</sup> kg/mg</span>
					        <span class="denominator">BW<sub>adult</sub> x AT</span>
				        </p>
				    </div>	
				    <div class="col-1 hidden-sm-up">	
				        <span class="plus-small no-margin-top-bottom"> + </span>
				    </div>
				    <div class="col-lg hidden-sm-up">
				        <p class="formula-small">SF<sub>o</sub> x C<sub>s</sub> x </p>
				        <p class="fraction-small">
					        <span class="numerator">SA<sub>child</sub> x AF<sub>child</sub> x ABS x EF<sub>resident</sub> x ED<sub>child</sub> x 10<sup>-6</sup> kg/mg</span>
					        <span class="denominator">BW<sub>child</sub> x AT</span>
				        </p>
				    </div>
			    
				    <!-- Standard Display -->
			        <div class="col-xl hidden-xs-down hidden-xl-up">
				        <span class="formula">SF<sub>o</sub> x C<sub>s</sub> x </span>
				        <span class="fraction">
					        <span class="numerator">SA<sub>adult</sub> x AF<sub>adult</sub> x ABS x EF<sub>resident</sub> x ED<sub>adult</sub> x 10<sup>-6</sup> kg/mg</span>
					        <span class="denominator">BW<sub>adult</sub> x AT</span>
				        </span>
				    </div>	
				    <div class="col-1 hidden-xs-down hidden-xl-up vertical-center-parent">	
				        <p class="plus no-margin-top-bottom vertical-center-child"> + </p>
				    </div>
				    <div class="col-xl hidden-xs-down hidden-xl-up">
				        <span class="formula">SF<sub>o</sub> x C<sub>s</sub> x </span>
				        <span class="fraction">
					        <span class="numerator">SA<sub>child</sub> x AF<sub>child</sub> x ABS x EF<sub>resident</sub> x ED<sub>child</sub> x 10<sup>-6</sup> kg/mg</span>
					        <span class="denominator">BW<sub>child</sub> x AT</span>
				        </span>
				    </div>
				
				    <!-- Extra Large Display -->
				    <div class="col-lg hidden-lg-down">
				        <span class="formula">SF<sub>o</sub> x C<sub>s</sub> x </span>
				        <span class="fraction">
					        <span class="numerator">SA<sub>adult</sub> x AF<sub>adult</sub> x ABS x EF<sub>resident</sub> x ED<sub>adult</sub> x 10<sup>-6</sup> kg/mg</span>
					        <span class="denominator">BW<sub>adult</sub> x AT</span>
				        </span>
					    <span class="plus padding-plus-xl"> + </span>
				        <span class="formula">SF<sub>o</sub> x C<sub>s</sub> x </span>
				        <span class="fraction">
					        <span class="numerator">SA<sub>child</sub> x AF<sub>child</sub> x ABS x EF<sub>resident</sub> x ED<sub>child</sub> x 10<sup>-6</sup> kg/mg</span>
					        <span class="denominator">BW<sub>child</sub> x AT</span>
				        </span>
				    </div>
			    </div>
			
			    <div class="row content grey-border no-margin-top-bottom padding-variables-header">
			        <span class="variables-header">Constants</span>
			    </div>
			
			    <div class="row content grey-border no-margin-top-bottom padding-variables">
			        <!--Mobile Display -->
			        <div class="col-lg-6 hidden-sm-up">
				        <span class="variables-small">SA<sub>adult</sub> = exposed skin surface area (<?php echo number_format($saAdult_cm); ?> cm<sup>2</sup>)</span><br>
					    <span class="variables-small">SA<sub>child</sub> = exposed skin surface area (<?php echo number_format($saChild_cm); ?> cm<sup>2</sup>)</span><br>
						<span class="variables-small">AF<sub>adult</sub> = soil to skin adherence factor (<?php echo $afAdult_mg; ?> mg/cm<sup>2</sup>)</span><br>
					    <span class="variables-small">AF<sub>child</sub> = soil to skin adherence factor (<?php echo $afChild_mg; ?> mg/cm<sup>2</sup>)</span><br>
						<span class="variables-small">EF<sub>resident</sub> = exposure frequency (<?php echo $efResident_days; ?> days/year)</span><br>
				    </div>
				    <div class="col-lg-6 hidden-sm-up">
					    <span class="variables-small">ED<sub>adult</sub> = exposure duration (<?php echo $edAdult_years; ?> years)</span><br>
				        <span class="variables-small">ED<sub>child</sub> = exposure duration (<?php echo $edChild_years; ?> years)</span><br>
					    <span class="variables-small">BW<sub>adult</sub> = body weight (<?php echo $bwAdult_kg; ?> kg)</span><br>
					    <span class="variables-small">BW<sub>child</sub> = body weight (<?php echo $bwChild_kg; ?> kg)</span><br>
						<span class="variables-small">AT = averaging time (<?php echo number_format($atAllRisk_days); ?> days)</span><br>
				    </div>
				
				    <!--Standard - XL Display -->
				    <div class="col-lg-6 hidden-xs-down">
				        <span class="variables">SA<sub>adult</sub> = exposed skin surface area (<?php echo number_format($saAdult_cm); ?> cm<sup>2</sup>)</span><br>
					    <span class="variables">SA<sub>child</sub> = exposed skin surface area (<?php echo number_format($saChild_cm); ?> cm<sup>2</sup>)</span><br>
						<span class="variables">AF<sub>adult</sub> = soil to skin adherence factor (<?php echo $afAdult_mg; ?> mg/cm<sup>2</sup>)</span><br>
					    <span class="variables">AF<sub>child</sub> = soil to skin adherence factor (<?php echo $afChild_mg; ?> mg/cm<sup>2</sup>)</span><br>
						<span class="variables">EF<sub>resident</sub> = exposure frequency (<?php echo $efResident_days; ?> days/year)</span><br>
					    
				    </div>
				    <div class="col-lg-6 hidden-xs-down">
					    <span class="variables">ED<sub>adult</sub> = exposure duration (<?php echo $edAdult_years; ?> years)</span><br>
				        <span class="variables">ED<sub>child</sub> = exposure duration (<?php echo $edChild_years; ?> years)</span><br>
					    <span class="variables">BW<sub>adult</sub> = body weight (<?php echo $bwAdult_kg; ?> kg)</span><br>
					    <span class="variables">BW<sub>child</sub> = body weight (<?php echo $bwChild_kg; ?> kg)</span><br>
						<span class="variables">AT = averaging time (<?php echo number_format($atAllRisk_days); ?> days)</span><br>
				    </div>
			    </div>
				
				<!--Loop through analyte data array to populate-->
				<?php
				    for ($row = 0; $row < count($analyteData); $row++) {
						echo "\n";
					    echo "\t\t\t\t".'<div class="row content grey-border no-margin-top-bottom padding-variables-header">'."\n";
						    echo "\t\t\t\t\t".'<span class="variables-header">'.$analyteData[$row]["name"].'</span>'."\n";
						echo "\t\t\t\t".'</div>'."\n";
						echo "\n";
						echo "\t\t\t\t".'<div class="row content grey-border no-margin-top-bottom padding-variables analyte-values-padding">'."\n";
						echo "\t\t\t\t\t".'<!--Mobile Display -->'."\n";
						echo "\t\t\t\t\t".'<div class="col-lg-6 hidden-sm-up">'."\n";
						echo "\t\t\t\t\t\t".'<span class="variables-small">SF<sub>o</sub> = slope factor (['.$analyteData[$row]["sfo"].' mg/kg]<sup>-1</sup>)</span><br>'."\n";
						echo "\t\t\t\t\t\t".'<span class="variables-small">ABS = fraction of chemical absorbed from soil ('.$analyteData[$row]["abs"].')</span><br>'."\n";
						echo "\t\t\t\t\t".'</div>'."\n";
						echo "\t\t\t\t\t".'<div class="col-lg-6 hidden-sm-up">'."\n";
						echo "\t\t\t\t\t\t".'<span class="variables-small">C<sub>s</sub> = concentration in soil ('.$analyteData[$row]["concentration"].' mg/kg)</span><br>'."\n";
						echo "\t\t\t\t\t".'</div>'."\n";
						echo "\n";
						echo "\t\t\t\t\t".'<!--Standard - XL Display -->'."\n";
						echo "\t\t\t\t\t".'<div class="col-lg-6 hidden-xs-down">'."\n";
						echo "\t\t\t\t\t\t".'<span class="variables">SF<sub>o</sub> = slope factor (['.$analyteData[$row]["sfo"].' mg/kg]<sup>-1</sup>)</span><br>'."\n";
						echo "\t\t\t\t\t\t".'<span class="variables">ABS = fraction of chemical absorbed from soil ('.$analyteData[$row]["abs"].')</span><br>'."\n";
						echo "\t\t\t\t\t".'</div>'."\n";
						echo "\t\t\t\t\t".'<div class="col-lg-6 hidden-xs-down">'."\n";
						echo "\t\t\t\t\t\t".'<span class="variables">C<sub>s</sub> = concentration in soil ('.$analyteData[$row]["concentration"].' mg/kg)</span><br>'."\n";
						echo "\t\t\t\t\t".'</div>'."\n";
						echo "\t\t\t\t".'</div>'."\n";
						echo "\n";
						echo "\t\t\t\t".'<!--Analyte risk value -->'."\n";
						echo "\t\t\t\t".'<div class="row grey-border no-margin-top-bottom analyte-risk">'."\n";
						echo "\t\t\t\t\t".'<span class="variables-small hidden-sm-up">Risk = '.$analyteData[$row]["dermalRiskCo"].' x 10<sup>'.$analyteData[$row]["dermalRiskEx"].'</sup></span>'."\n";
						echo "\t\t\t\t\t".'<span class="variables hidden-xs-down">Risk = '.$analyteData[$row]["dermalRiskCo"].' x 10<sup>'.$analyteData[$row]["dermalRiskEx"].'</sup></span>'."\n";
						echo "\t\t\t\t".'</div>'."\n";
					}
				?>
			
			    <div class="row content grey-border no-margin-top-bottom padding-variables-header">
			        <span class="variables-header">Total Dermal Risk</span>
			    </div>
			
			    <div class="row content grey-border no-margin-top-bottom padding-variables">
			        <!--Mobile Display -->
			        <div class="col-lg hidden-sm-up">
				        <span class="variables-small"><?php echo $dermalRiskCo." x 10<sup>".$dermalRiskEx."</sup>";?></span><br>
				    </div>
				
				    <!--Standard - XL Display -->
				    <div class="col-lg hidden-xs-down">
				        <span class="variables"><?php echo $dermalRiskCo." x 10<sup>".$dermalRiskEx."</sup>";?></span><br>
				    </div>
			    </div>
				
			</div>
			
			<!--Dermal Risk, Worker-->
			<div id="dermalRiskWorker">
			    <div class="row content grey-border no-margin-top-bottom padding-subheader">
			        <span class="calculations-subheader">Dermal Risk</span>
			    </div>
			
			    <div class="row content grey-border no-margin-top-bottom padding-formula">
			        <!--Mobile Display -->
			        <div class="col-lg hidden-sm-up">
				        <p class="formula-small">SF<sub>o</sub> x C<sub>s</sub> x </p>
				        <p class="fraction-small">
					        <span class="numerator">SA<sub>adult</sub> x AF<sub>adult</sub> x ABS x EF<sub>worker</sub> x ED<sub>worker</sub> x 10<sup>-6</sup> kg/mg</span>
					        <span class="denominator">BW<sub>adult</sub> x AT</span>
				        </p>
				    </div>
			    
				    <!-- Standard Display -->
			        <div class="col-xl hidden-xs-down hidden-xl-up">
				        <span class="formula">SF<sub>o</sub> x C<sub>s</sub> x </span>
				        <span class="fraction">
					        <span class="numerator">SA<sub>adult</sub> x AF<sub>adult</sub> x ABS x EF<sub>worker</sub> x ED<sub>worker</sub> x 10<sup>-6</sup> kg/mg</span>
					        <span class="denominator">BW<sub>adult</sub> x AT</span>
				        </span>
				    </div>	
				
				    <!-- Extra Large Display -->
				    <div class="col-lg hidden-lg-down">
				        <span class="formula">SF<sub>o</sub> x C<sub>s</sub> x </span>
				        <span class="fraction">
					        <span class="numerator">SA<sub>adult</sub> x AF<sub>adult</sub> x ABS x EF<sub>worker</sub> x ED<sub>worker</sub> x 10<sup>-6</sup> kg/mg</span>
					        <span class="denominator">BW<sub>adult</sub> x AT</span>
				        </span>
				    </div>
			    </div>
			
			    <div class="row content grey-border no-margin-top-bottom padding-variables-header">
			        <span class="variables-header">Constants</span>
			    </div>
			
			    <div class="row content grey-border no-margin-top-bottom padding-variables">
			        <!--Mobile Display -->
			        <div class="col-lg-6 hidden-sm-up">
				        <span class="variables-small">SA<sub>adult</sub> = exposed skin surface area (<?php echo number_format($saAdult_cm); ?> cm<sup>2</sup>)</span><br>
						<span class="variables-small">AF<sub>adult</sub> = soil to skin adherence factor (<?php echo $afAdult_mg; ?> mg/cm<sup>2</sup>)</span><br>
						<span class="variables-small">EF<sub>worker</sub> = exposure frequency (<?php echo $efWorker_days; ?> days/year)</span><br>
				    </div>
				    <div class="col-lg-6 hidden-sm-up">
					    <span class="variables-small">ED<sub>worker</sub> = exposure duration (<?php echo $edWorker_years; ?> years)</span><br>
					    <span class="variables-small">BW<sub>adult</sub> = body weight (<?php echo $bwAdult_kg; ?> kg)</span><br>
						<span class="variables-small">AT = averaging time (<?php echo number_format($atAllRisk_days); ?> days)</span><br>
				    </div>
				
				    <!--Standard - XL Display -->
				    <div class="col-lg-6 hidden-xs-down">
				        <span class="variables">SA<sub>adult</sub> = exposed skin surface area (<?php echo number_format($saAdult_cm); ?> cm<sup>2</sup>)</span><br>
						<span class="variables">AF<sub>adult</sub> = soil to skin adherence factor (<?php echo $afAdult_mg; ?> mg/cm<sup>2</sup>)</span><br>
						<span class="variables">EF<sub>worker</sub> = exposure frequency (<?php echo $efWorker_days; ?> days/year)</span><br>
					    
				    </div>
				    <div class="col-lg-6 hidden-xs-down">
					    <span class="variables">ED<sub>worker</sub> = exposure duration (<?php echo $edWorker_years; ?> years)</span><br>
					    <span class="variables">BW<sub>adult</sub> = body weight (<?php echo $bwAdult_kg; ?> kg)</span><br>
						<span class="variables">AT = averaging time (<?php echo number_format($atAllRisk_days); ?> days)</span><br>
				    </div>
			    </div>
				
				<!--Loop through analyte data array to populate-->
				<?php
				    for ($row = 0; $row < count($analyteData); $row++) {
						echo "\n";
					    echo "\t\t\t\t".'<div class="row content grey-border no-margin-top-bottom padding-variables-header">'."\n";
						    echo "\t\t\t\t\t".'<span class="variables-header">'.$analyteData[$row]["name"].'</span>'."\n";
						echo "\t\t\t\t".'</div>'."\n";
						echo "\n";
						echo "\t\t\t\t".'<div class="row content grey-border no-margin-top-bottom padding-variables analyte-values-padding">'."\n";
						echo "\t\t\t\t\t".'<!--Mobile Display -->'."\n";
						echo "\t\t\t\t\t".'<div class="col-lg-6 hidden-sm-up">'."\n";
						echo "\t\t\t\t\t\t".'<span class="variables-small">SF<sub>o</sub> = slope factor (['.$analyteData[$row]["sfo"].' mg/kg]<sup>-1</sup>)</span><br>'."\n";
						echo "\t\t\t\t\t\t".'<span class="variables-small">ABS = fraction of chemical absorbed from soil ('.$analyteData[$row]["abs"].')</span><br>'."\n";
						echo "\t\t\t\t\t".'</div>'."\n";
						echo "\t\t\t\t\t".'<div class="col-lg-6 hidden-sm-up">'."\n";
						echo "\t\t\t\t\t\t".'<span class="variables-small">C<sub>s</sub> = concentration in soil ('.$analyteData[$row]["concentration"].' mg/kg)</span><br>'."\n";
						echo "\t\t\t\t\t".'</div>'."\n";
						echo "\n";
						echo "\t\t\t\t\t".'<!--Standard - XL Display -->'."\n";
						echo "\t\t\t\t\t".'<div class="col-lg-6 hidden-xs-down">'."\n";
						echo "\t\t\t\t\t\t".'<span class="variables">SF<sub>o</sub> = slope factor (['.$analyteData[$row]["sfo"].' mg/kg]<sup>-1</sup>)</span><br>'."\n";
						echo "\t\t\t\t\t\t".'<span class="variables">ABS = fraction of chemical absorbed from soil ('.$analyteData[$row]["abs"].')</span><br>'."\n";
						echo "\t\t\t\t\t".'</div>'."\n";
						echo "\t\t\t\t\t".'<div class="col-lg-6 hidden-xs-down">'."\n";
						echo "\t\t\t\t\t\t".'<span class="variables">C<sub>s</sub> = concentration in soil ('.$analyteData[$row]["concentration"].' mg/kg)</span><br>'."\n";
						echo "\t\t\t\t\t".'</div>'."\n";
						echo "\t\t\t\t".'</div>'."\n";
						echo "\n";
						echo "\t\t\t\t".'<!--Analyte risk value -->'."\n";
						echo "\t\t\t\t".'<div class="row grey-border no-margin-top-bottom analyte-risk">'."\n";
						echo "\t\t\t\t\t".'<span class="variables-small hidden-sm-up">Risk = '.$analyteData[$row]["dermalRiskCo"].' x 10<sup>'.$analyteData[$row]["dermalRiskEx"].'</sup></span>'."\n";
						echo "\t\t\t\t\t".'<span class="variables hidden-xs-down">Risk = '.$analyteData[$row]["dermalRiskCo"].' x 10<sup>'.$analyteData[$row]["dermalRiskEx"].'</sup></span>'."\n";
						echo "\t\t\t\t".'</div>'."\n";
					}
				?>
			
			    <div class="row content grey-border no-margin-top-bottom padding-variables-header">
			        <span class="variables-header">Total Dermal Risk</span>
			    </div>
			
			    <div class="row content grey-border no-margin-top-bottom padding-variables">
			        <!--Mobile Display -->
			        <div class="col-lg hidden-sm-up">
				        <span class="variables-small"><?php echo $dermalRiskCo." x 10<sup>".$dermalRiskEx."</sup>";?></span><br>
				    </div>
				
				    <!--Standard - XL Display -->
				    <div class="col-lg hidden-xs-down">
				        <span class="variables"><?php echo $dermalRiskCo." x 10<sup>".$dermalRiskEx."</sup>";?></span><br>
				    </div>
			    </div>
				
			</div>
			
			<!--Dermal Hazard, Resident-->
			<div id="dermalHazResident">
			    <div class="row content grey-border no-margin-top-bottom padding-subheader">
			        <span class="calculations-subheader">Dermal Hazard</span>
			    </div>
			
			    <div class="row content grey-border no-margin-top-bottom padding-formula">
			        <!--Mobile Display -->
			        <div class="col-lg hidden-sm-up">
				        <span class="fraction-small">
						    <span class="numerator">&nbsp;&nbsp;&nbsp;1&nbsp;&nbsp;&nbsp;</span>
						    <span class="denominator">RfD<sub>o</sub></span>
						</span>
						<span class="formula-small">x C<sub>s</sub> x</span>
				        <span class="fraction-small">
					        <span class="numerator">SA<sub>child</sub> x AF<sub>child</sub> x ABS x EF<sub>resident</sub> x ED<sub>child</sub> x 10<sup>-6</sup> kg/mg</span>
					        <span class="denominator">BW<sub>child</sub> x AT<sub>child</sub></span>
				        </span>
				    </div>
			    
				    <!-- Standard Display -->
			        <div class="col-lg hidden-xs-down hidden-xl-up">
					    <span class="fraction">
						    <span class="numerator">&nbsp;&nbsp;&nbsp;1&nbsp;&nbsp;&nbsp;</span>
						    <span class="denominator">RfD<sub>o</sub></span>
						</span>
				        <span class="formula">x C<sub>s</sub> x </span>
				        <span class="fraction">
					        <span class="numerator">SA<sub>child</sub> x AF<sub>child</sub> x ABS x EF<sub>resident</sub> x ED<sub>child</sub> x 10<sup>-6</sup> kg/mg</span>
					        <span class="denominator">BW<sub>child</sub> x AT<sub>child</sub></span>
				        </span>
				    </div>	
				
				    <!-- Extra Large Display -->
				    <div class="col-lg hidden-lg-down">
					    <span class="fraction">
						    <span class="numerator">&nbsp;&nbsp;&nbsp;1&nbsp;&nbsp;&nbsp;</span>
						    <span class="denominator">RfD<sub>o</sub></span>
						</span>
				        <span class="formula"> x C<sub>s</sub> x </span>
				        <span class="fraction">
					        <span class="numerator">SA<sub>child</sub> x AF<sub>child</sub> x ABS x EF<sub>resident</sub> x ED<sub>child</sub> x 10<sup>-6</sup> kg/mg</span>
					        <span class="denominator">BW<sub>child</sub> x AT<sub>child</sub></span>
				        </span>
				    </div>
			    </div>
			
			    <div class="row content grey-border no-margin-top-bottom padding-variables-header">
			        <span class="variables-header">Constants</span>
			    </div>
			
			    <div class="row content grey-border no-margin-top-bottom padding-variables">
			        <!--Mobile Display -->
			        <div class="col-lg-6 hidden-sm-up">
					    <span class="variables-small">SA<sub>child</sub> = exposed skin surface area (<?php echo number_format($saChild_cm); ?> cm<sup>2</sup>)</span><br>
						<span class="variables-small">AF<sub>child</sub> = soil to skin adherence factor (<?php echo $afChild_mg; ?> mg/cm<sup>2</sup>)</span><br>
						<span class="variables-small">EF<sub>resident</sub> = exposure frequency (<?php echo $efResident_days; ?> days/year)</span><br>
				    </div>
				    <div class="col-lg-6 hidden-sm-up">
					    <span class="variables-small">ED<sub>child</sub> = exposure duration (<?php echo $edChild_years; ?> years)</span><br>
					    <span class="variables-small">BW<sub>child</sub> = body weight (<?php echo $bwChild_kg; ?> kg)</span><br>
					    <span class="variables-small">AT<sub>child</sub> = averaging time (<?php echo number_format($atChild_days); ?> days)</span><br>
				    </div>
				
				    <!--Standard - XL Display -->
				    <div class="col-lg-6 hidden-xs-down">
					    <span class="variables">SA<sub>child</sub> = exposed skin surface area (<?php echo number_format($saChild_cm); ?> cm<sup>2</sup>)</span><br>
						<span class="variables">AF<sub>child</sub> = soil to skin adherence factor (<?php echo $afChild_mg; ?> mg/cm<sup>2</sup>)</span><br>
					    <span class="variables">EF<sub>resident</sub> = exposure frequency (<?php echo $efResident_days; ?> days/year)</span><br>
					    
				    </div>
				    <div class="col-lg-6 hidden-xs-down">
					    <span class="variables">ED<sub>child</sub> = exposure duration (<?php echo $edChild_years; ?> years)</span><br>
					    <span class="variables">BW<sub>child</sub> = body weight (<?php echo $bwChild_kg; ?> kg)</span><br>
					    <span class="variables">AT<sub>child</sub> = averaging time (<?php echo number_format($atChild_days); ?> days)</span><br>
				    </div>
			    </div>
				
				<!--Loop through analyte data array to populate-->
				<?php
				    for ($row = 0; $row < count($analyteData); $row++) {
						echo "\n";
					    echo "\t\t\t\t".'<div class="row content grey-border no-margin-top-bottom padding-variables-header">'."\n";
						    echo "\t\t\t\t\t".'<span class="variables-header">'.$analyteData[$row]["name"].'</span>'."\n";
						echo "\t\t\t\t".'</div>'."\n";
						echo "\n";
						echo "\t\t\t\t".'<div class="row content grey-border no-margin-top-bottom padding-variables analyte-values-padding">'."\n";
						echo "\t\t\t\t\t".'<!--Mobile Display -->'."\n";
						echo "\t\t\t\t\t".'<div class="col-lg-6 hidden-sm-up">'."\n";
						echo "\t\t\t\t\t\t".'<span class="variables-small">RfD<sub>o</sub> = oral reference dose ('.$analyteData[$row]["rfdo"].' mg/kg)</span><br>'."\n";
						echo "\t\t\t\t\t\t".'<span class="variables-small">ABS = fraction of chemical absorbed from soil ('.$analyteData[$row]["abs"].')</span><br>'."\n";
						echo "\t\t\t\t\t".'</div>'."\n";
						echo "\t\t\t\t\t".'<div class="col-lg-6 hidden-sm-up">'."\n";
						echo "\t\t\t\t\t\t".'<span class="variables-small">C<sub>s</sub> = concentration in soil ('.$analyteData[$row]["concentration"].' mg/kg)</span><br>'."\n";
						echo "\t\t\t\t\t".'</div>'."\n";
						echo "\n";
						echo "\t\t\t\t\t".'<!--Standard - XL Display -->'."\n";
						echo "\t\t\t\t\t".'<div class="col-lg-6 hidden-xs-down">'."\n";
						echo "\t\t\t\t\t\t".'<span class="variables">RfD<sub>o</sub> = oral reference dose ('.$analyteData[$row]["rfdo"].' mg/kg)</span><br>'."\n";
						echo "\t\t\t\t\t\t".'<span class="variables">ABS = fraction of chemical absorbed from soil ('.$analyteData[$row]["abs"].')</span><br>'."\n";
						echo "\t\t\t\t\t".'</div>'."\n";
						echo "\t\t\t\t\t".'<div class="col-lg-6 hidden-xs-down">'."\n";
						echo "\t\t\t\t\t\t".'<span class="variables">C<sub>s</sub> = concentration in soil ('.$analyteData[$row]["concentration"].' mg/kg)</span><br>'."\n";
						echo "\t\t\t\t\t".'</div>'."\n";
						echo "\t\t\t\t".'</div>'."\n";
						echo "\n";
						echo "\t\t\t\t".'<!--Analyte hazard value -->'."\n";
						echo "\t\t\t\t".'<div class="row grey-border no-margin-top-bottom analyte-risk">'."\n";
						echo "\t\t\t\t\t".'<span class="variables-small hidden-sm-up">Hazard = '.$analyteData[$row]["dermalHazCo"].' x 10<sup>'.$analyteData[$row]["dermalHazEx"].'</sup></span>'."\n";
						echo "\t\t\t\t\t".'<span class="variables hidden-xs-down">Hazard = '.$analyteData[$row]["dermalHazCo"].' x 10<sup>'.$analyteData[$row]["dermalHazEx"].'</sup></span>'."\n";
						echo "\t\t\t\t".'</div>'."\n";
					}
				?>
				
			    <div class="row content grey-border no-margin-top-bottom padding-variables-header">
			        <span class="variables-header">Total Dermal Hazard</span>
			    </div>
			
			    <div class="row content grey-border no-margin-top-bottom padding-variables">
			        <!--Mobile Display -->
			        <div class="col-lg hidden-sm-up">
						<span class="variables-small"><?php echo $dermalHazCo." x 10<sup>".$dermalHazEx."</sup>";?></span><br>
				    </div>
				
				    <!--Standard - XL Display -->
				    <div class="col-lg hidden-xs-down">
						<span class="variables"><?php echo $dermalHazCo." x 10<sup>".$dermalHazEx."</sup>";?></span><br>
				    </div>
			    </div>
				
			</div>
			
			<!--Dermal Hazard, Worker-->
			<div id="dermalHazWorker">
			    <div class="row content grey-border no-margin-top-bottom padding-subheader">
			        <span class="calculations-subheader">Dermal Hazard</span>
			    </div>
			
			    <div class="row content grey-border no-margin-top-bottom padding-formula">
			        <!--Mobile Display -->
			        <div class="col-lg hidden-sm-up">
				        <span class="fraction-small">
						    <span class="numerator">&nbsp;&nbsp;&nbsp;1&nbsp;&nbsp;&nbsp;</span>
						    <span class="denominator">RfD<sub>o</sub></span>
						</span>
						<span class="formula-small">x C<sub>s</sub> x</span>
				        <span class="fraction-small">
					        <span class="numerator">SA<sub>adult</sub> x AF<sub>adult</sub> x ABS x EF<sub>worker</sub> x ED<sub>worker</sub> x 10<sup>-6</sup> kg/mg</span>
					        <span class="denominator">BW<sub>adult</sub> x AT<sub>worker</sub></span>
				        </span>
				    </div>
			    
				    <!-- Standard Display -->
			        <div class="col-lg hidden-xs-down hidden-xl-up">
					    <span class="fraction">
						    <span class="numerator">&nbsp;&nbsp;&nbsp;1&nbsp;&nbsp;&nbsp;</span>
						    <span class="denominator">RfD<sub>o</sub></span>
						</span>
				        <span class="formula">x C<sub>s</sub> x </span>
				        <span class="fraction">
					        <span class="numerator">SA<sub>adult</sub> x AF<sub>adult</sub> x ABS x EF<sub>worker</sub> x ED<sub>worker</sub> x 10<sup>-6</sup> kg/mg</span>
					        <span class="denominator">BW<sub>adult</sub> x AT<sub>worker</sub></span>
				        </span>
				    </div>	
				
				    <!-- Extra Large Display -->
				    <div class="col-lg hidden-lg-down">
					    <span class="fraction">
						    <span class="numerator">&nbsp;&nbsp;&nbsp;1&nbsp;&nbsp;&nbsp;</span>
						    <span class="denominator">RfD<sub>o</sub></span>
						</span>
				        <span class="formula"> x C<sub>s</sub> x </span>
				        <span class="fraction">
					        <span class="numerator">SA<sub>adult</sub> x AF<sub>adult</sub> x ABS x EF<sub>worker</sub> x ED<sub>worker</sub> x 10<sup>-6</sup> kg/mg</span>
					        <span class="denominator">BW<sub>adult</sub> x AT<sub>worker</sub></span>
				        </span>
				    </div>
			    </div>
			
			    <div class="row content grey-border no-margin-top-bottom padding-variables-header">
			        <span class="variables-header">Constants</span>
			    </div>
			
			    <div class="row content grey-border no-margin-top-bottom padding-variables">
			        <!--Mobile Display -->
			        <div class="col-lg-6 hidden-sm-up">
					    <span class="variables-small">SA<sub>adult</sub> = exposed skin surface area (<?php echo number_format($saAdult_cm); ?> cm<sup>2</sup>)</span><br>
						<span class="variables-small">AF<sub>adult</sub> = soil to skin adherence factor (<?php echo $afAdult_mg; ?> mg/cm<sup>2</sup>)</span><br>
						<span class="variables-small">EF<sub>worker</sub> = exposure frequency (<?php echo $efWorker_days; ?> days/year)</span><br>
				    </div>
				    <div class="col-lg-6 hidden-sm-up">
					    <span class="variables-small">ED<sub>worker</sub> = exposure duration (<?php echo $edWorker_years; ?> years)</span><br>
					    <span class="variables-small">BW<sub>adult</sub> = body weight (<?php echo $bwAdult_kg; ?> kg)</span><br>
					    <span class="variables-small">AT<sub>worker</sub> = averaging time (<?php echo number_format($atWorker_days); ?> days)</span><br>
				    </div>
				
				    <!--Standard - XL Display -->
				    <div class="col-lg-6 hidden-xs-down">
					    <span class="variables">SA<sub>adult</sub> = exposed skin surface area (<?php echo number_format($saAdult_cm); ?> cm<sup>2</sup>)</span><br>
						<span class="variables">AF<sub>adult</sub> = soil to skin adherence factor (<?php echo $afAdult_mg; ?> mg/cm<sup>2</sup>)</span><br>
					    <span class="variables">EF<sub>worker</sub> = exposure frequency (<?php echo $efWorker_days; ?> days/year)</span><br>
					    
				    </div>
				    <div class="col-lg-6 hidden-xs-down">
					    <span class="variables">ED<sub>worker</sub> = exposure duration (<?php echo $edWorker_years; ?> years)</span><br>
					    <span class="variables">BW<sub>adult</sub> = body weight (<?php echo $bwAdult_kg; ?> kg)</span><br>
					    <span class="variables">AT<sub>worker</sub> = averaging time (<?php echo number_format($atWorker_days); ?> days)</span><br>
				    </div>
			    </div>
				
				<!--Loop through analyte data array to populate-->
				<?php
				    for ($row = 0; $row < count($analyteData); $row++) {
						echo "\n";
					    echo "\t\t\t\t".'<div class="row content grey-border no-margin-top-bottom padding-variables-header">'."\n";
						    echo "\t\t\t\t\t".'<span class="variables-header">'.$analyteData[$row]["name"].'</span>'."\n";
						echo "\t\t\t\t".'</div>'."\n";
						echo "\n";
						echo "\t\t\t\t".'<div class="row content grey-border no-margin-top-bottom padding-variables analyte-values-padding">'."\n";
						echo "\t\t\t\t\t".'<!--Mobile Display -->'."\n";
						echo "\t\t\t\t\t".'<div class="col-lg-6 hidden-sm-up">'."\n";
						echo "\t\t\t\t\t\t".'<span class="variables-small">RfD<sub>o</sub> = oral reference dose ('.$analyteData[$row]["rfdo"].' mg/kg)</span><br>'."\n";
						echo "\t\t\t\t\t\t".'<span class="variables-small">ABS = fraction of chemical absorbed from soil ('.$analyteData[$row]["abs"].')</span><br>'."\n";
						echo "\t\t\t\t\t".'</div>'."\n";
						echo "\t\t\t\t\t".'<div class="col-lg-6 hidden-sm-up">'."\n";
						echo "\t\t\t\t\t\t".'<span class="variables-small">C<sub>s</sub> = concentration in soil ('.$analyteData[$row]["concentration"].' mg/kg)</span><br>'."\n";
						echo "\t\t\t\t\t".'</div>'."\n";
						echo "\n";
						echo "\t\t\t\t\t".'<!--Standard - XL Display -->'."\n";
						echo "\t\t\t\t\t".'<div class="col-lg-6 hidden-xs-down">'."\n";
						echo "\t\t\t\t\t\t".'<span class="variables">RfD<sub>o</sub> = oral reference dose ('.$analyteData[$row]["rfdo"].' mg/kg)</span><br>'."\n";
						echo "\t\t\t\t\t\t".'<span class="variables">ABS = fraction of chemical absorbed from soil ('.$analyteData[$row]["abs"].')</span><br>'."\n";
						echo "\t\t\t\t\t".'</div>'."\n";
						echo "\t\t\t\t\t".'<div class="col-lg-6 hidden-xs-down">'."\n";
						echo "\t\t\t\t\t\t".'<span class="variables">C<sub>s</sub> = concentration in soil ('.$analyteData[$row]["concentration"].' mg/kg)</span><br>'."\n";
						echo "\t\t\t\t\t".'</div>'."\n";
						echo "\t\t\t\t".'</div>'."\n";
						echo "\n";
						echo "\t\t\t\t".'<!--Analyte hazard value -->'."\n";
						echo "\t\t\t\t".'<div class="row grey-border no-margin-top-bottom analyte-risk">'."\n";
						echo "\t\t\t\t\t".'<span class="variables-small hidden-sm-up">Hazard = '.$analyteData[$row]["dermalHazCo"].' x 10<sup>'.$analyteData[$row]["dermalHazEx"].'</sup></span>'."\n";
						echo "\t\t\t\t\t".'<span class="variables hidden-xs-down">Hazard = '.$analyteData[$row]["dermalHazCo"].' x 10<sup>'.$analyteData[$row]["dermalHazEx"].'</sup></span>'."\n";
						echo "\t\t\t\t".'</div>'."\n";
					}
				?>
			
			    <div class="row content grey-border no-margin-top-bottom padding-variables-header">
			        <span class="variables-header">Total Dermal Hazard</span>
			    </div>
			
			    <div class="row content grey-border no-margin-top-bottom padding-variables">
			        <!--Mobile Display -->
			        <div class="col-lg hidden-sm-up">
						<span class="variables-small"><?php echo $dermalHazCo." x 10<sup>".$dermalHazEx."</sup>";?></span><br>
				    </div>
				
				    <!--Standard - XL Display -->
				    <div class="col-lg hidden-xs-down">
						<span class="variables"><?php echo $dermalHazCo." x 10<sup>".$dermalHazEx."</sup>";?></span><br>
				    </div>
			    </div>
				
			</div>
			
			<div class="grey-border grey-border-bottom calculations-bottom"></div>
			<div class="footer-space"></div>
		</div>
			
        <!-- jQuery first, then Tether, then Bootstrap JS. -->
        <script src="https://code.jquery.com/jquery-3.1.1.slim.min.js" integrity="sha384-A7FZj7v+d/sdmMqp/nOQwliLvUsJfDHW+k9Omg/a/EheAdgtzNs3hpfag6Ed950n" crossorigin="anonymous"></script>
        <script src="https://cdnjs.cloudflare.com/ajax/libs/tether/1.4.0/js/tether.min.js" integrity="sha384-DztdAPBWPRXSA/3eYEEUWrWCy7G5KFbe8fFjk5JAIxUYHKkDx6Qin1DkWx51bBrb" crossorigin="anonymous"></script>
        <script src="https://maxcdn.bootstrapcdn.com/bootstrap/4.0.0-alpha.6/js/bootstrap.min.js" integrity="sha384-vBWWzlZJ8ea9aCX4pEW3rVHjgjt7zpkNpZk+02D9phzyeVkE+jo0ieGizqPLForn" crossorigin="anonymous"></script>

		<?php
		    if ($_SESSION['oral'] && $_SESSION['receptor']=="resident"){
				echo '<script type="text/javascript"> 
				        $("#oralRiskResident").show();
						$("#oralHazResident").show();
					</script>';
			}
			
			if ($_SESSION['oral'] && $_SESSION['receptor']=="worker"){
				echo '<script type="text/javascript"> 
				        $("#oralRiskWorker").show();
                        $("#oralHazWorker").show(); 						
					</script>';
			}
			
			if ($_SESSION['dermal'] && $_SESSION['receptor']=="resident"){
				echo '<script type="text/javascript"> 
				        $("#dermalRiskResident").show();
						$("#dermalHazResident").show();
					</script>';
			}
			
			if ($_SESSION['dermal'] && $_SESSION['receptor']=="worker"){
				echo '<script type="text/javascript"> 
				        $("#dermalRiskWorker").show();
                        $("#dermalHazWorker").show(); 						
					</script>';
			}
			
			//clear session
			session_unset();
		?>
		
	</body>
</html>