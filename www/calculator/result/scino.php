<?php

//the functions below accept a float or int and return the coefficient and exponent to display in scientific notation,
//rounded to 2 significant digits.
//Example: 0.017578 returns 1.8 for the coefficient and -2 for the exponent, which can then be displayed as 1.8 x 10^-2

function getCoefficient($input) {
	
	if ($input < 1) {
		
		//note that if input is smaller than X E-20, this function won't work properly. Input is formated to 20 digits past the decimal point,
	    //which should be enough for most, if not all, scenarios
		$input = number_format($input,20);
        $coefficient = ltrim($input, '0.'); //removes the zeros and decimal point from the left side of the number
		$coefficient = substr_replace($coefficient, '.', 1, 0); //inserts a decimal point after the first digit
		$coefficient = round($coefficient,1);	//rounds the number to 1 digit after the decimal point
		return $coefficient;
		
	}else {
		
		$input = number_format($input,20,'.',''); //remove any commas
		$coefficient = str_replace(".","",$input); //number is greater than 1. Remove any existing decimal points before reinserting the decimal
		$coefficient = substr_replace($coefficient, '.', 1, 0); //inserts a decimal point after the first digit
		$coefficient = round($coefficient,1);	//rounds the number to 1 digit after the decimal point
		return $coefficient;
		
	}
}

function getExponent($input) {
	
	if ($input == 0) {
		return 0; //should be an edge case
	}
	
	if ($input < 1) {
		
		$input = number_format($input,20); //make sure the number is formatted in decimal form. First 2 characters should be "0."
		
		//split decimal number to array, then count how many zeros before a non-zero digit to get exponent
		$numSplit = str_split($input);
		$exponent = 0;
		
		for ($i = 0; $i < count($numSplit); $i++) {
			
			//as soon as we come to a character other than 0 or decimal point, stop counting
			if ($numSplit[$i] != 0 && $numSplit[$i] != "." ) { 
				break;
			}
			$exponent++;	
		}
		
		//need to remove the decimal point from the count
		$exponent = $exponent - 1; 
		
		//since the input is less than 1, we are dealing with a decimal number. 
		//Decimal is moved to the right, so our exponent needs to be negative
		$makeNegative = $exponent * 2; 
		$exponent = $exponent - $makeNegative;
		return $exponent;

	}else {
		
		//if number is greater than 1, round to an integer and count digits to get exponent
		
		$input = number_format($input,0,'.',''); //remove any digits after the decimal and any commas
		
		//split input to array, then count digits
		$numSplit = str_split($input);
		$exponent = count($numSplit);
		
		//decimal is moved to the left 1 less than the number of digits
		$exponent = $exponent - 1;
		return $exponent;

	}	
}

?>