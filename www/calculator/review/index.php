<?php

    session_start();
	
	$noAnalytes = false;
	
	//check if initial form values have been entered. If values are empty, redirect to start of form
	if ( !isset ($_SESSION['regulator']) || !isset ($_SESSION['receptor']) || 
	(!isset ($_SESSION['oral']) && !isset ($_SESSION['inhalation']) && !isset ($_SESSION['dermal'])) ) {
		
		header("Location: http://www.riskhaz.com/calculator/");
		
	}
	
	//check if the user wants to delete one of the analytes. If so, find it and remove it from the selectedAnalytes array.
	if($_POST){
		
		$analyteToDelete = $_POST["aid"];
		
		for ($row = 0; $row < count($_SESSION['selectedAnalytes']); $row++) {
			if ($_SESSION['selectedAnalytes'][$row][0] == $analyteToDelete ) {
				array_splice($_SESSION['selectedAnalytes'],$row,1);
			}
		}
		
	}
	
	//set noAnalytes sentinel
	if ( count($_SESSION['selectedAnalytes']) < 1 ) {
		$noAnalytes = true;
	}else {
		$noAnalytes = false;
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
        
		<script src="https://use.fontawesome.com/f0890c5f46.js"></script>
		
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
			    <p class="h5">Review Selected Analytes</p>
			</div>
			
			<div class="row spacer-top">
			</div>
			
			<div id="no-analytes" class="text-center">
			    <p>No analytes selected. Add an analyte to continue.</p>
			</div>
			
			<?php
			    //Loop through selectedAnalytes session variable and display selected analytes
				for ($row = 0; $row < count($_SESSION['selectedAnalytes']); $row++) {
					echo "\n";
					echo "\t\t\t".'<form action="'.htmlspecialchars($_SERVER["PHP_SELF"]).'" method="post">'."\n"; 
				        echo "\t\t\t\t".'<input type="hidden" name="aid" value="'.$_SESSION['selectedAnalytes'][$row][0].'">'."\n"; //displays AID
				        echo "\t\t\t\t".'<div class="row analyte-selected">'."\n";
					        echo "\t\t\t\t\t".'<div class="col-10 analyte-data">'."\n";
						        echo "\t\t\t\t\t\t".'<span>'.$_SESSION['selectedAnalytes'][$row][1].'</span> <br>'."\n"; //displays Analyte Name
								//displays conentration and units
								if ($_SESSION['selectedAnalytes'][$row][3] == "ug") { 
									echo "\t\t\t\t\t\t".'<span class="concentration">'.$_SESSION['selectedAnalytes'][$row][2].' &micro;g/kg</span>'."\n"; 
								}else {
									echo "\t\t\t\t\t\t".'<span class="concentration">'.$_SESSION['selectedAnalytes'][$row][2].' mg/kg</span>'."\n";
								}
					        echo "\t\t\t\t\t".'</div>'."\n";
					        echo "\t\t\t\t\t".'<div class="col-2 analyte-data">'."\n";
						        echo "\t\t\t\t\t\t".'<button class="btn-block button-close  type="submit">'."\n";
						            echo "\t\t\t\t\t\t\t".'<i class="fa fa-times" aria-hidden="true"></i>'."\n";
						        echo "\t\t\t\t\t\t".'</button>'."\n";
					        echo "\t\t\t\t\t".'</div>'."\n";
				        echo "\t\t\t\t".'</div>'."\n";
			        echo "\t\t\t".'</form>'."\n";					
				}
			?>
			
			<div class="row buttons-submit">
				<a href="http://www.riskhaz.com/calculator/analyte/"><button type="button" class="btn btn-primary" id="add-button">Add Analyte</button></a>
				<a href="http://www.riskhaz.com/calculator/result/"><button type="button" class="btn btn-warning" id="calculate-button" <?php if($noAnalytes){echo 'disabled';} ?>>Calculate</button></a>
			</div>
		</div>
		
        <!-- jQuery first, then Tether, then Bootstrap JS. -->
        <script src="https://code.jquery.com/jquery-3.1.1.slim.min.js" integrity="sha384-A7FZj7v+d/sdmMqp/nOQwliLvUsJfDHW+k9Omg/a/EheAdgtzNs3hpfag6Ed950n" crossorigin="anonymous"></script>
        <script src="https://cdnjs.cloudflare.com/ajax/libs/tether/1.4.0/js/tether.min.js" integrity="sha384-DztdAPBWPRXSA/3eYEEUWrWCy7G5KFbe8fFjk5JAIxUYHKkDx6Qin1DkWx51bBrb" crossorigin="anonymous"></script>
        <script src="https://maxcdn.bootstrapcdn.com/bootstrap/4.0.0-alpha.6/js/bootstrap.min.js" integrity="sha384-vBWWzlZJ8ea9aCX4pEW3rVHjgjt7zpkNpZk+02D9phzyeVkE+jo0ieGizqPLForn" crossorigin="anonymous"></script>
   
        <?php
		    if ($noAnalytes){
				echo '<script type="text/javascript"> 
				        $("#no-analytes").show(); 
					</script>';
			}
		?>
	    

	</body>
</html>