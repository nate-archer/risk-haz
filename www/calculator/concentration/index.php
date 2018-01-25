<?php

    session_start();
	
	//check if initial form values have been entered. If values are empty, redirect to start of form
	if ( !isset ($_SESSION['regulator']) || !isset ($_SESSION['receptor']) || 
	(!isset ($_SESSION['oral']) && !isset ($_SESSION['inhalation']) && !isset ($_SESSION['dermal'])) || 
	!isset ($_SESSION['currentAid']) || !isset ($_SESSION['currentAnalyte']) ) {
		
		header("Location: http://www.riskhaz.com/calculator/");
		
	}
	
	$concentrationUnits = "";
	$formError = false;
	
	if($_POST) {
		//set concenetationUnits local variable
		$concentrationUnits = $_POST["concentrationUnits"];
		
		//check if concentration value was entered. If not, generate error message
		if ($_POST["concentrationVal"] == "") {
			
			$formError = true;
			
		} 
		
		//if no errors, set session variables and load review page
		if (!$formError) {
			
			//check if Analytes session variable has been declared previously. If not, create it.
			if ( !isset ($_SESSION['selectedAnalytes']) ) {
				
				$_SESSION['selectedAnalytes'] = array();
				
			}
			
			//create array for selected analyte and concentration values, then add to session array
			$analyteEntry = array( $_SESSION['currentAid'], $_SESSION['currentAnalyte'], $_POST['concentrationVal'], $_POST['concentrationUnits'] );
			array_push($_SESSION['selectedAnalytes'],$analyteEntry);
		
			header("Location: /calculator/review/");
			
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
		
		<div class="container-fluid top-border">
		</div>
		
		<div class="container">
		    <div class="row content-label">
			    <p class="h5">Enter Concentration</p>
			</div>
			
			<div id="errorConcentration">
				<div class="alert alert-danger" role="alert">
				    Please enter a concentration value.
				</div>
	        </div>
			
			<form id="form" class="calc-form1" method="post" action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]);?>">  
			    <div class="form-group row inputs-row">
					<div class="col-4 col-text-input">
                        <input type="number" step="any" class="form-control" id="concentration" name="concentrationVal" placeholder="ex: 10" onfocus="this.placeholder = ''" onblur="this.placeholder = 'ex: 10'">
					</div>
					<div class="col-8 col-radio-units">
					    <fieldset class="form-group" id="radiosConcentration">
                            <div class="form-check form-check-inline">
                                <label class="form-check-label">
                                    <input type="radio" class="form-check-input" name="concentrationUnits" id="concentrationMg" value="mg" 
									<?php if ($concentrationUnits != "ug") echo "checked"; ?> >
                                        mg/kg
                                </label>
                            </div>
                            <div class="form-check form-check-inline">
                                <label class="form-check-label">
                                    <input type="radio" class="form-check-input" name="concentrationUnits" id="concentrationUg" value="ug"
									<?php if ($concentrationUnits == "ug") echo "checked"; ?> >
                                        &micro;g/kg
                                </label>
                            </div>
                        </fieldset>
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
				if ( !$('#concentration').val() ) {
					$("#errorConcentration").show();
					event.preventDefault();
                }
			});
			
		</script>

		<?php
		    if ($formError){
				echo '<script type="text/javascript"> 
				        $("#errorConcentration").show(); 
					</script>';
			}
		?>

	</body>
</html>