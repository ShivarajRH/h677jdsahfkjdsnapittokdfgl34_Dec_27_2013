<?php
if (! defined ( 'BASEPATH' ))
	exit ( 'No direct script access allowed' );
	
/*
 * Simple String Helper Function to strip string based on selcted number of charecters ...
 */
if (! function_exists ( 'strip_string' )) {
	function strip_string($str, $limit) {
		if (strlen ( $str ) >= $limit) {
			return substr ( $str, 0, $limit ) . '...';
		}
		return $str;
	}
}
if (! function_exists ( 'time_left' )) {
	//FUNCTION FOR TIME LEFT
	function time_left($integer) {
		$minutes = 0;
		$seconds = $integer;
		$return = '';
		if ($seconds / 60 >= 1) 

		{
			
			$minutes = floor ( $seconds / 60 );
			
			if ($minutes / 60 >= 1) 

			{ # Hours 
				

				$hours = floor ( $minutes / 60 );
				
				if ($hours / 24 >= 1) 

				{ #days 
					

					$days = floor ( $hours / 24 );
					
					if ($days / 7 >= 1) 

					{ #weeks 
						

						$weeks = floor ( $days / 7 );
						
						if ($weeks >= 2)
							$return = "$weeks Weeks";
						
						else
							$return = "$weeks Week";
					
					} #end of weeks 
					

					$days = $days - (floor ( $days / 7 )) * 7;
					
					if ($weeks >= 1 && $days >= 1)
						$return = "$return, ";
					
					if ($days >= 2)
						$return = "$return $days days";
					
					if ($days == 1)
						$return = "$return $days day";
				
				} #end of days
				

				$hours = $hours - (floor ( $hours / 24 )) * 24;
				
				if ($days >= 1 && $hours >= 1)
					$return = "$return, ";
				
				if ($hours >= 2)
					$return = "$return $hours hours";
				
				if ($hours == 1)
					$return = "$return $hours hour";
			
			} #end of Hours
			

			$minutes = $minutes - (floor ( $minutes / 60 )) * 60;
			
			if ($hours >= 1 && $minutes >= 1)
				$return = "$return, ";
			
			if ($minutes >= 2)
				$return = "$return $minutes minutes";
			
			if ($minutes == 1)
				$return = "$return $minutes minute";
		
		} #end of minutes 
		

		$seconds = $integer - (floor ( $integer / 60 )) * 60;
		
		if ($minutes >= 1 && $seconds >= 1)
			$return = "$return, ";
		
		if ($seconds >= 2)
			$return = "$return $seconds seconds";
		
		if ($seconds == 1)
			$return = "$return $seconds second";
		
		$return = "$return.";
		
		return $return;
	
	}
}

function sortkeys_cbk($a, $b)
{
    if (count($a) == count($b))
        return 0;
    return (count($a) < count($b)) ? -1 : 1;
}

function aasort(&$a)
{
  uasort($a, "sortkeys_cbk");
}

function rsortkeys_cbk($a, $b)
{
    if (count($a) == count($b))
        return 0;
    return (count($a) > count($b)) ? -1 : 1;
}

function aarsort(&$a)
{
  uasort($a, "rsortkeys_cbk");
}

function validate_is_email($email)
{
	if(preg_match("/^([a-zA-Z0-9])+([a-zA-Z0-9\._-])*@([a-zA-Z0-9_-])+([a-zA-Z0-9\._-]+)+$/",$email))
	{
		list($username,$domain)=explode('@',$email);
		if(!checkdnsrr($domain,'MX'))
		{
			return false;
		}
		return true;
	}
	return false;
}	

function validate_is_mobile($mob)
{
	if(strlen($mob)==10)
	{
		if(preg_match("/^([0-9]{10})$/",$mob))
		{
			return true;
		}
	}
	
	return false;
}


function format_datetime($d)
{
	return strtotime($d)?date('d/m/Y h:i a',strtotime($d)):'--na--';
}

function format_date($d)
{
	return strtotime($d)?date('d/m/Y',strtotime($d)):'--na--';
}

function format_datetime_ts($ts)
{
	return $ts?date('d/m/Y h:i a',$ts):'--na--';
}

function format_date_ts($ts)
{
	return $ts?date('d/m/Y',$ts):'--na--';
}


function makecomma($input)
{
    // This function is written by some anonymous person - I got it from Google
    if(strlen($input)<=2)
    { return $input; }
    $length=substr($input,0,strlen($input)-2);
    $formatted_input = makecomma($length).",".substr($input,-2);
    return $formatted_input;
}

function formatInIndianStyle($num){
	$num = $num*1;
    // This is my function
    $pos = strpos((string)$num, ".");
    if ($pos === false) { $decimalpart="";}
    else { $decimalpart= substr($num, $pos+1, 2); $num = substr($num,0,$pos); }

    if(strlen($num)>3 & strlen($num) <= 12){
                $last3digits = substr($num, -3 );
                $numexceptlastdigits = substr($num, 0, -3 );
                $formatted = makecomma($numexceptlastdigits);
                $stringtoreturn = $formatted.",".$last3digits ;
                if($decimalpart)
                	$stringtoreturn .= ".".$decimalpart ;
    }elseif(strlen($num)<=3){
                $stringtoreturn = $decimalpart?$num.".".$decimalpart:$num ;
    }elseif(strlen($num)>12){
                $stringtoreturn = number_format($num, 2);
    }

    if(substr($stringtoreturn,0,2)=="-,"){$stringtoreturn = "-".substr($stringtoreturn,2 );}

    return $stringtoreturn;
}

?>