<?php

    session_start();
	
	//check if initial form values have been entered. If values are empty, redirect to start of form
	if ( !isset ($_SESSION['regulator']) || !isset ($_SESSION['receptor']) || 
	(!isset ($_SESSION['oral']) && !isset ($_SESSION['inhalation']) && !isset ($_SESSION['dermal'])) ) {
		
		header("Location: http://www.riskhaz.com/calculator/");
		
	}
	
	//Check if user has made an analyte selection. If so, store in session and load Concentration page
	if($_POST) {
		$_SESSION["currentAid"] = $_POST["aid"];
		$_SESSION["currentAnalyte"] = $_POST["analyte"];
		header("Location: /calculator/concentration/");
	}
	
	//connect to DB
	include "/opt/bitnami/apache2/htdocs/calculator/connection.php";
	
	$query = "";
	if ($_SESSION['regulator']=='federal') {
		//get the analytes listed in the federal table only
		$query = "SELECT DISTINCT toxicity_fed.aid_fk, analyte.analyteName
            FROM toxicity_fed
            INNER JOIN analyte ON toxicity_fed.Aid_fk=analyte.Aid
            ORDER BY analyte.analyteName;";
	}else {
		//regulator = california. Get all the available analytes.
		$query = "SELECT DISTINCT aid, analyteName
            FROM analyte
            ORDER BY analyteName;";
	}
	
	$result = mysqli_query($link, $query);

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
			    <p class="h5">Select Analyte</p>
			</div>
		    
			<div class="form-group row filter">
				<label for="filter" class="col-2 col-form-label" id="filter-label">Filter</label>
				<div class="col-10 filter-input">
					<input type="search" class="form-control" id="filter" placeholder="ex: Benzyl Chloride" onfocus="this.placeholder = ''" onblur="this.placeholder = 'ex: Benzyl Chloride'">
				</div>
			</div>
			
			<div class="row spacer">
			</div>
			
			<div id="no-matches" class="text-center">
			    <p>No results</p>
			</div>
			<?php
			
			    //Loop through result object and list all the analytes on the page. Populate hidden values with AID and Analyte Names.
			    while( $row = mysqli_fetch_array($result, MYSQLI_NUM) ) {
					echo "\n";
					echo "\t\t\t".'<form action="'.htmlspecialchars($_SERVER["PHP_SELF"]).'" method="post" class="analyte-form">'."\n";
					    echo "\t\t\t\t".'<input type="hidden" name="analyte" value="'.$row[1].'">'."\n";
						echo "\t\t\t\t".'<input type="hidden" name="aid" value="'.$row[0].'">'."\n";
						echo "\t\t\t\t".'<div class="row analyte-container">'."\n";
						    echo "\t\t\t\t\t".'<div class="col-10 analyte-data">'."\n";
							    echo "\t\t\t\t\t\t".'<button class="btn-block button-style analyte-name text-left" type="submit">'."\n";
								    echo "\t\t\t\t\t\t\t".$row[1]."\n";
								echo "\t\t\t\t\t\t".'</button>'."\n";
							echo "\t\t\t\t\t".'</div>'."\n";
							echo "\t\t\t\t\t".'<div class="col-2 analyte-data">'."\n";
							    echo "\t\t\t\t\t\t".'<button class="btn-block button-style text-right" type="submit">'."\n";
								    echo "\t\t\t\t\t\t\t".'<i class="fa fa-arrow-right" aria-hidden="true"></i>'."\n";
								echo "\t\t\t\t\t\t".'</button>'."\n";
							echo "\t\t\t\t\t".'</div>'."\n";
						echo "\t\t\t\t".'</div>'."\n";
					echo "\t\t\t".'</form>'."\n";
	            }
				mysqli_free_result($result);
				mysqli_close($link);
				
			?>
			
			<div class="row footer-space">
			</div>
			
		</div>
		
        <!-- jQuery first, then Tether, then Bootstrap JS. -->
        <script src="https://code.jquery.com/jquery-3.1.1.slim.min.js" integrity="sha384-A7FZj7v+d/sdmMqp/nOQwliLvUsJfDHW+k9Omg/a/EheAdgtzNs3hpfag6Ed950n" crossorigin="anonymous"></script>
        <script src="https://cdnjs.cloudflare.com/ajax/libs/tether/1.4.0/js/tether.min.js" integrity="sha384-DztdAPBWPRXSA/3eYEEUWrWCy7G5KFbe8fFjk5JAIxUYHKkDx6Qin1DkWx51bBrb" crossorigin="anonymous"></script>
        <script src="https://maxcdn.bootstrapcdn.com/bootstrap/4.0.0-alpha.6/js/bootstrap.min.js" integrity="sha384-vBWWzlZJ8ea9aCX4pEW3rVHjgjt7zpkNpZk+02D9phzyeVkE+jo0ieGizqPLForn" crossorigin="anonymous"></script>
    
	    <script type="text/javascript">
		
		    $( "#filter" ).on('input',function() {
			    var entry = $(this).val();
				var exp = new RegExp(entry, 'i');
				
				$( ".analyte-form" ).each(function() {
				    var analyteName = $( ".analyte-name", this).text();
				    var isMatch = exp.test(analyteName);
					$(this).toggle(isMatch); //hides or shows the analyte form if "isMatch" is true or false
				});
				
				if ( $("form.analyte-form:visible").length === 0) {
				    $("#no-matches").show();
				}else {
				    $("#no-matches").hide();
				}
				
			});
		
		</script>

	</body>
</html>