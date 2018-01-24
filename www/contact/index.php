<?php

    $userName = $userEmail = $message = "";
	$userNameError = $userEmailError = $messageError = false;
	
	if($_POST) {
		
		$userName = $_POST["userName"];
		$userEmail = $_POST["userEmail"];
		$message = $_POST["message"];
		
		//check if any values missing. If so, generate error.
		if($userName == "") {
			$userNameError = true;
		}
		
		if($userEmail == "") {
			$userEmailError = true;
		}
		
		if($message == "") {
			$messageError = true;
		}
		
		//if there were no errors, send the email and load the confirmation page
		if(!$userNameError && !$userEmailError && !$messageError) {
			
			$subject = "RiskHaz Message From ".$userName;
            $body = "User Name: ".$userName."\nUser Email: ".$userEmail."\n\nMessage\n".$message;
			
			include "/opt/bitnami/apache2/htdocs/contact/userMessageEmail.php";
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
		
		<title>Risk & Hazard Contact</title>
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
						<li class="nav-item">
							<a class="nav-link" href="http://www.riskhaz.com/calculator">Calculator</a>
						</li>
						<li class="nav-item">
							<a class="nav-link" href="http://www.riskhaz.com/about">About</a>
						</li>
						<li class="nav-item active">
							<a class="nav-link" href="http://www.riskhaz.com/contact">Contact <span class="sr-only">(current)</span></a>
						</li>
					</ul>
				</div>
			</nav>
		</div>
		
		<div class="container-fluid top-border">
		</div>
		
		<div class="container">
		    <div class="row content-label">
			    <p class="h5">Contact</p>
			</div>
			
			<div class="row content-label">
			    <p class="contact-leadingText">Questions or comments? Send me a message.</p>
			</div>
			
			<form id="contact" class="calc-form1" method="post" action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]);?>">  
			    <div class="form-group row inputs-row">
					<div class="col-xs-12 col-lg-8 col-text-input">
                        <div class="form-group" id="userNameDiv">
							<label class="form-control-label" for="userName">Your Name</label>
							<input type="text" class="form-control" id="userName" name="userName" <?php if($userName != "") echo 'value="'.$userName.'"'; ?> >
							<div class="form-control-feedback" id="userNameFeedback">This field is required.</div>
						</div>
						
						<div class="form-group" id="userEmailDiv">
							<label class="form-control-label" for="userEmail">Your Email Address</label>
							<input type="email" class="form-control" id="userEmail" name="userEmail" <?php if($userEmail != "") echo 'value="'.$userEmail.'"'; ?> >
							<div class="form-control-feedback" id="userEmailFeedback">This field is required.</div>
						</div>
						
						<div class="form-group" id="messageDiv">
							<label class="form-control-label" for="message">Message</label>
							<textarea class="form-control" id="message" rows="5" name="message"><?php if($message != "") echo htmlspecialchars($message); ?></textarea>
							<div class="form-control-feedback" id="messageFeedback">This field is required.</div>
						</div>
					</div>
				</div>
				<div class="form-group row inputs-row">
                    <button type="submit" class="btn btn-primary" id="next-button">Submit</button>
				</div>
			</form>
			
			<div class="footer-space"></div>
		</div>
		
		
        <!-- jQuery first, then Tether, then Bootstrap JS. -->
        <script src="https://code.jquery.com/jquery-3.1.1.slim.min.js" integrity="sha384-A7FZj7v+d/sdmMqp/nOQwliLvUsJfDHW+k9Omg/a/EheAdgtzNs3hpfag6Ed950n" crossorigin="anonymous"></script>
        <script src="https://cdnjs.cloudflare.com/ajax/libs/tether/1.4.0/js/tether.min.js" integrity="sha384-DztdAPBWPRXSA/3eYEEUWrWCy7G5KFbe8fFjk5JAIxUYHKkDx6Qin1DkWx51bBrb" crossorigin="anonymous"></script>
        <script src="https://maxcdn.bootstrapcdn.com/bootstrap/4.0.0-alpha.6/js/bootstrap.min.js" integrity="sha384-vBWWzlZJ8ea9aCX4pEW3rVHjgjt7zpkNpZk+02D9phzyeVkE+jo0ieGizqPLForn" crossorigin="anonymous"></script>

		
		<script type="text/javascript">
			
			//error checking
			$( "#contact" ).submit(function ( event ) {
			
			    if ($("#userName").val() == "") {
				    $("#userNameFeedback").show();
					$("#userNameDiv").addClass("has-danger");
				}
				
				if ($("#userEmail").val() == "") {
				    $("#userEmailFeedback").show();
					$("#userEmailDiv").addClass("has-danger");
				}
				
				if ($("#message").val() == "") {
				    $("#messageFeedback").show();
					$("#messageDiv").addClass("has-danger");
				}
				
				if ($("#userName").val() == "" || $("#userEmail").val() == "" || $("#message").val() == "") {
					event.preventDefault();
				}
			});
			
			//remove error if user enters values
			$( "#userName" ).keyup(function() {
                $("#userNameFeedback").hide();
			    $("#userNameDiv").removeClass("has-danger");
            });
			
			$( "#userEmail" ).keyup(function() {
                $("#userEmailFeedback").hide();
			    $("#userEmailDiv").removeClass("has-danger");
            });
			
			$( "#message" ).keyup(function() {
                $("#messageFeedback").hide();
			    $("#messageDiv").removeClass("has-danger");
            });
			
		</script>
		
		<?php
		
		    if($userNameError) {
				
				echo
				    "<script>
					    $(\"#userNameFeedback\").show();
					    $(\"#userNameDiv\").addClass(\"has-danger\");
					</script>"
				;
			}
			
			if($userEmailError) {
				
				echo
				    "<script>
					    $(\"#userEmailFeedback\").show();
					    $(\"#userEmailDiv\").addClass(\"has-danger\");
					</script>"
				;
			}
			
			if($messageError) {
				
				echo
				    "<script>
					    $(\"#messageFeedback\").show();
					    $(\"#messageDiv\").addClass(\"has-danger\");
					</script>"
				;
			}
		
		?>
		
	</body>
</html>