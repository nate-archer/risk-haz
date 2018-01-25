<?php

    session_start();
	
	$regulator = $receptor = "";
	$oral = $inhalation = $dermal = false;
	$formError = false;
	
	if($_POST) {
		
		//assign form values to variables
		$regulator = $_POST["regulator"];
		$receptor = $_POST["receptor"];
		
		if (isset($_POST["pathwayOral"])) {
			$oral = true;
		} 
		
		if (isset($_POST["pathwayInhalation"])) {
			$inhalation = true;
		}

        if (isset($_POST["pathwayDermal"])) {
			$dermal = true;
		}
		
		//need at least 1 pathway checkbox selected; otherwise we have a form error
		if (!isset($_POST["pathwayOral"]) && !isset($_POST["pathwayInhalation"]) && !isset($_POST["pathwayDermal"])) {
			
			$formError = true;
			
		}
		
		//if no errors, store variables in session and load analytes page
		if (!$formError){
			
			$_SESSION['regulator'] = $regulator;
			$_SESSION['receptor'] = $receptor;
			$_SESSION['oral'] = $oral;
			$_SESSION['inhalation'] = $inhalation;
			$_SESSION['dermal'] = $dermal;
			header("Location: /calculator/analyte");
			
		}
		
	}

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
		
		<div class="container-fluid header-background">
		    <div class="container jumbo-container">
			    <div class="text-white jumbo-header">
                    <h1 class="display-4">Calculator</h1>
                    <p class="lead">Calculate risk and hazard values for hazardous substances.</p>
                </div>
			</div>
		</div>
		
		<div class="container" id="errorMessage">
				<div class="alert alert-danger" role="alert">
				    Please select at least one pathway.
				</div>
	    </div>
		
		<div class="container">
			<form id="form" class="calc-form1" method="post" action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]);?>">
			    <div class="form-group row inputs-row">
				    <label for="radiosRegulator" class="col-2 col-form-label col-label">Regulator</label>
					<div class="col-10 col-inputs">
				        <fieldset class="form-group" id="radiosRegulator">
                            <div class="form-check form-check-inline">
                                <label class="form-check-label">
                                    <input type="radio" class="form-check-input" name="regulator" id="regulatorCa" value="california" 
									<?php if ($regulator != "federal") echo "checked"; ?>>
                                        California
                                </label>
                            </div>
                            <div class="form-check form-check-inline">
                                <label class="form-check-label">
                                    <input type="radio" class="form-check-input" name="regulator" id="regulatorFed" value="federal"
									<?php if ($regulator == "federal") echo "checked"; ?>>
                                        Federal
                                </label>
                            </div>
                        </fieldset>
					</div>
				</div>
                <div class="form-group row inputs-row">
				    <label for="radiosReceptor" class="col-2 col-form-label col-label">Receptor</label>
					<div class="col-10 col-inputs">
				        <fieldset class="form-group" id="radiosReceptor">
                            <div class="form-check form-check-inline">
                                <label class="form-check-label">
                                    <input type="radio" class="form-check-input" name="receptor" id="receptorResident" value="resident" 
									<?php if ($receptor != "worker") echo "checked"; ?>>
                                        Resident
                                </label>
                            </div>
                            <div class="form-check form-check-inline">
                                <label class="form-check-label">
                                    <input type="radio" class="form-check-input" name="receptor" id="receptorWorker" value="worker"
									<?php if ($receptor == "worker") echo "checked"; ?>>
                                        Worker
                                </label>
                            </div>
                        </fieldset>
					</div>
				</div>
                <div class="form-group row inputs-row">
				    <div class="col-2 col-label">
				        <label class="col-form-label">Pathway</label>
					</div>
					<div class="col-10 col-inputs">
					    <div class="form-check form-check-inline">
						    <label class="form-check-label">
							    <input type="checkbox" class="form-check-input" name="pathwayOral" id="pathwayOral" 
								<?php if ($oral) echo "checked";?>> 
								    Oral
							</label>
						</div>
						<!-- Add inhalation in a later phase -->
						<!--
						<div class="form-check form-check-inline">
						    <label class="form-check-label">
							    <input type="checkbox" class="form-check-input" name="pathwayInhalation" id="pathwayInhalation" 
								<?php if ($inhalation) echo "checked";?>> 
								    Inhalation
							</label>
						</div>
						-->
						<div class="form-check form-check-inline">
						    <label class="form-check-label">
							    <input type="checkbox" class="form-check-input" name="pathwayDermal" id="pathwayDermal" 
								<?php if ($dermal) echo "checked";?>> 
								    Dermal
							</label>
						</div>
					</div>
				</div>
				<div class="form-group row inputs-row">
                    <button type="submit" class="btn btn-primary" id="next-button">Next</button>
				</div>
			</form>
		</div>

        <!-- jQuery first, then Tether, then Bootstrap JS. -->
        <script src="https://code.jquery.com/jquery-3.1.1.slim.min.js" integrity="sha384-A7FZj7v+d/sdmMqp/nOQwliLvUsJfDHW+k9Omg/a/EheAdgtzNs3hpfag6Ed950n" crossorigin="anonymous"></script>
        <script src="https://cdnjs.cloudflare.com/ajax/libs/tether/1.4.0/js/tether.min.js" integrity="sha384-DztdAPBWPRXSA/3eYEEUWrWCy7G5KFbe8fFjk5JAIxUYHKkDx6Qin1DkWx51bBrb" crossorigin="anonymous"></script>
        <script src="https://maxcdn.bootstrapcdn.com/bootstrap/4.0.0-alpha.6/js/bootstrap.min.js" integrity="sha384-vBWWzlZJ8ea9aCX4pEW3rVHjgjt7zpkNpZk+02D9phzyeVkE+jo0ieGizqPLForn" crossorigin="anonymous"></script>
    
	    <script type="text/javascript">
			
			$( "#form" ).submit(function ( event ) {
				
				if ( !$('#pathwayOral').is(':checked') && !$('#pathwayInhalation').is(':checked') && !$('#pathwayDermal').is(':checked') ) {
					$("#errorMessage").show();
					event.preventDefault();
                }
			});
			
		</script>
	    
		
		<?php
		    if ($formError){
				echo '<script type="text/javascript"> 
				        $("#errorMessage").show(); 
					</script>';
			}
		?>
		
	</body>
</html>