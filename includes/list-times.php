<?php
define('NUMBEROFRESULTS',10);
define('MYSERVER',"209.120.199.18:37434");
//define('MYSERVER',"172.27.45.41:1434");

define('DEFAULTMESSAGE',"SEPTA SMS \n For schedules reply with:\n stopid# route# \n route is optional");
//$stop_id = $_GET["req1"]; // Stop id
//$route = $_GET["req2"]; // Route
//$direction= $_GET["req3"]; // Direction i or o

$parameters = $_GET['parameters'];
define('PARAMETERS',$_GET['parameters']);
// explode the parameters variable to break out individual parameters for stopname, stop_id, route, direction and directiondescription
list($stopname, $stop_id, $route, $direction, $directiondescription) = explode("::",$parameters);

if ($direction == 1)
$direction = "i";
else if ($direction == 0)
$direction = "o";

?>

<h2 class="normal">Route <?php echo $route?> | To <?php echo $directiondescription?> | <?php echo $stopname?></h2>

<p>The next 10 scheduled departure times from <strong><?php echo $stopname?> (Stop ID: <?php echo $stop_id?>)</strong> are:<br>

<?php


$dateCalender= $_GET["req4"]; // Date m/d/y
$date = $_GET["req5"]; // Time h:i:s
//$user= $_GET["user"];
$SkipArray  = array(); // Array that holds the date and a bool to show if time needs to be reset to midnight
$time_start = microtime(true);
	if(check_valid_stop_times($stop_id) > 0 )
	{
	if(strtolower($route) == "i" && $direction == "")	
	{
		$direction="i";
		$route="";
	}
	else if(strtolower($route) == "o" && $direction == "")
	{
		$direction="o";
		$route="";
	}
 
	if(strtolower($direction) == "i")	
		$direction="1";
	else if(strtolower($direction) == "o")
		$direction="0";
	else
		$direction="";		
		
	// If no time is supplied use the server time					
	if($date =="")
	{
		$date = date('H:i:s');
	}
	
	// If no date is supplied use the server date
	if($dateCalender =="")
	{
		$dateCalender = date('m/d/y');
	}						 

	$stopName = getStopName($stop_id); // Check if stop id is valid
	$checkCriteria = getStopCriteria($stop_id,$route,$dateCalender); // Check if stop route combination would yeild any results
	
	// if route combination invalid make route empty to show all possible routes
	if($checkCriteria =='0')
	{
		$route ="";
	}
	
	// if stopname invalid show reply message
	if($stopName == "")
	{
		echo DEFAULTMESSAGE;
	}
	
	// if stopname invalid and combination invaild show reply message
	else if ($stopName == "" && $checkCriteria =='0' )
	{
		echo DEFAULTMESSAGE;
	}
	else if ($stopName == "" && $checkCriteria !='0' )
	{
		echo DEFAULTMESSAGE;
	}
	// Perform query 
	else 
	{
		$Directions = checkDirection($stop_id,$route); // Check to see if directions exist in both directions
		if( count($Directions) == 1) // If there is one direction check to see if user direction is equal to directions avaliable
		{	
			if($Directions[0]["direction"] != $direction)	
				$direction="";
		}
	
			
		// echo stopname for text message
		// echo $stopName;
		
		$counter = array(); // Array to hold Database values
		$DirectionOne = array(); //Array to hold direction one
		$DirectionZero= array(); //Array to hold direction zero
		
		//check schedule days for routes and skip to next available day 
		if($route !="")
		{	
			$SkipArray = skipCalender($dateCalender,$route);
			$dateCalender = $SkipArray[0]['DateCalender'];
			
			if($SkipArray[0]['resetDate'] == 1)
				$date = date('00:00:00');
		}
		
		// Loop through until next 4 stops
		while (  count($counter) < NUMBEROFRESULTS )
        {
			
			// Check date to see if early morning trip and check for after midnight trips of the previous day
			switch (substr($date,0,2)) 
			{
				case '00':
					$counter=bus_schedule($stop_id, $route, $counter,"24".substr($date,2,strlen($date)-2),date('m/d/y',strtotime(date('m/d/y',strtotime($dateCalender. " -1 day")))),$direction );
				break;
				case '01':
					$counter=bus_schedule($stop_id, $route, $counter,"25".substr($date,2,strlen($date)-2),date('m/d/y',strtotime(date('m/d/y',strtotime($dateCalender. " -1 day")))),$direction  );
				break;
				case '02':
					$counter=bus_schedule($stop_id, $route, $counter,"26".substr($date,2,strlen($date)-2),date('m/d/y',strtotime(date('m/d/y',strtotime($dateCalender. " -1 day")))),$direction  );
				break;
				case '03':
					$counter=bus_schedule($stop_id, $route,$counter,"27".substr($date,2,strlen($date)-2),date('m/d/y',strtotime(date('m/d/y',strtotime($dateCalender. " -1 day")))),$direction  );
				break;
				case '04':
					$counter=bus_schedule($stop_id, $route, $counter,"28".substr($date,2,strlen($date)-2),date('m/d/y',strtotime(date('m/d/y',strtotime($dateCalender. " -1 day")))),$direction  );
				break;
				case '05':
					$counter=bus_schedule($stop_id, $route, $counter,"29".substr($date,2,strlen($date)-2),date('m/d/y',strtotime(date('m/d/y',strtotime($dateCalender. " -1 day")))),$direction  );
				break;
			}
			
			//check for regular trips
			$counter=bus_schedule($stop_id, $route,$counter,$date,$dateCalender,$direction );
		
			//Check for after midnight trips a day ahead
			if (count($counter) < NUMBEROFRESULTS) 
			{
				$dateaftermidnight= strtotime(date('m/d/y',strtotime($dateCalender. " +1 day"))); //Add a day to Date
				
				//Change time to 24:i:s (after midnight e.g. 24:00:00, 25:00:00) and call bus schedule function
				$counter=bus_schedule($stop_id, $route,$counter,"24".substr(date('00:i:s'),date("m/d/y",$dateaftermidnight)),date("m/d/y",$dateaftermidnight),$direction );
			}
			
			// After Loop has finished and four trips have not yet been found increment the day
			$date = date('00:00:00');
			$dateCalender = date('m/d/y',strtotime($dateCalender. " +1 day"));
			
			// Check the schedule again to see if days need to be skipped
			if($route !="")
			{	
				$SkipArray = skipCalender($dateCalender,$route);
				$dateCalender = $SkipArray[0]['DateCalender'];
				
				if($SkipArray[0]['resetDate'] == 1)
					$date = date('00:00:00');
			}
			
		}
	
		$tmp = Array();

		// Get all direction
		foreach($counter as &$count)
		{
			$tmp[] = &$count["Direction"];
		}
		// Store unique direction
		$result = array_unique($tmp);
		
		//Loop through to put print direction separate from route data
		foreach($result as $res)
		{
			if(count($result) > 1)
			{
				if($res == 1)
					echo "\nInbound"; // Print direction
				else 
					echo "\nOutbound"; // Print direction
			}
			$tmpDirection= array();
			foreach($counter as &$count)
			{
				if($res ==$count["Direction"])   
				{
					// If direction equal store data in an array
					array_push($tmpDirection, array ( "Route" => $count["Route"],"date" => substr(date('g:ia',strtotime($count["date"])),0,strlen(date('g:ia',strtotime($count["date"])))-1),"day" =>$count["Day"],"Direction" =>$count["Direction"],"DateCalender" => $count["date"]));
				}
	
			}

			$templevel="0";   
			$newkey=0;
			$grouparr=array();
			$directionOneCounter = 0;
			$directionTwoCounter = 0;
			
			// Loop through array and put data in group order while time stays in order
			foreach ($tmpDirection as $key => $val) {
				if ($templevel==$val["Route"]){
					$grouparr[$templevel][$newkey]=$val;
				} else {
					$grouparr[$val["Route"]][$newkey]=$val;
				}
				$newkey++;       
			}
			
			foreach ($grouparr as $levelOne) {
				foreach ($levelOne as $group) {
					if($group["Direction"] ==1)
						$directionOneCounter++;
					else if($group["Direction"] ==0)
						$directionTwoCounter++;
				}
			}
			
		
			//Begin Adjusting array for when 1:3 ratio
			if($directionOneCounter == 3 )
			{
				$Doubleroute = ""; //Check in array if key has two arrays underneath and pop off last value
				$TripeRoute = "";
				foreach ($grouparr as $group) {
					if(count($group) == 2)
						$Doubleroute = $group[1]["Route"];	
					if(count($group) == 3)
						$TripeRoute = $group[2]["Route"];
				}
				if ($Doubleroute != "" )
					array_pop($grouparr[$Doubleroute]);
				if ($TripeRoute != "" )
					array_pop($grouparr[$TripeRoute]);
				else
					array_pop($grouparr);
				
			}
			if($directionTwoCounter == 1 )
			{
				$array= end($grouparr); //Find last array value
				$arrayafterEnd = array();
				foreach ($array as $group) {
					// Add row after last value of array 
					array_push($arrayafterEnd,array ("Route" => $group["Route"], "date" => $group["date"],"day" =>$group["day"], "Direction" =>$group["Direction"],"DateCalender" =>$group["DateCalender"]));
				}
				
				while(count($nextRec) != 1)
				{
					switch (substr(date('H:i:s',strtotime($arrayafterEnd[0]["date"]."m")),0,2)) 
					{
						case '00':
							$nextRec = getnextRecord($stop_id, $arrayafterEnd[0]["Route"],1,"24".substr(date('H:i:s',strtotime($arrayafterEnd[0]["date"]."m")),0,2),date('m/d/y',strtotime($arrayafterEnd[0]["DateCalender"])),0 );
						break;
						case '01':
							$nextRec = getnextRecord($stop_id, $arrayafterEnd[0]["Route"],1,"25".substr(date('H:i:s',strtotime($arrayafterEnd[0]["date"]."m")),0,2),date('m/d/y',strtotime($arrayafterEnd[0]["DateCalender"])),0 );
						break;
						case '02':
								$nextRec = getnextRecord($stop_id, $arrayafterEnd[0]["Route"],1,"26".substr(date('H:i:s',strtotime($arrayafterEnd[0]["date"]."m")),0,2),date('m/d/y',strtotime($arrayafterEnd[0]["DateCalender"])),0 );
						break;
						case '03':
							$nextRec = getnextRecord($stop_id, $arrayafterEnd[0]["Route"],1,"27".substr(date('H:i:s',strtotime($arrayafterEnd[0]["date"]."m")),0,2),date('m/d/y',strtotime($arrayafterEnd[0]["DateCalender"])),0 );
						break;
						case '04':
							$nextRec = getnextRecord($stop_id, $arrayafterEnd[0]["Route"],1,"28".substr(date('H:i:s',strtotime($arrayafterEnd[0]["date"]."m")),0,2),date('m/d/y',strtotime($arrayafterEnd[0]["DateCalender"])),0 );
						break;
						case '05':
							$nextRec = getnextRecord($stop_id, $arrayafterEnd[0]["Route"],1,"29".substr(date('H:i:s',strtotime($arrayafterEnd[0]["date"]."m")),0,2),date('m/d/y',strtotime($arrayafterEnd[0]["DateCalender"])),0 );
						break;
					}
					if(count($nextRec) != 1)
						$nextRec = getnextRecord($stop_id, $arrayafterEnd[0]["Route"],1,date('H:i:s',strtotime($arrayafterEnd[0]["date"]."m")),date('m/d/y',strtotime($arrayafterEnd[0]["DateCalender"])),0 );
					if (count($nextRec) != 1) 
					{
						$dateaftermidnight= strtotime(date('m/d/y',strtotime(date('m/d/y',strtotime($arrayafterEnd[0]["DateCalender"])). " +1 day"))); //Add a day to Date
						//Change time to 24:i:s (after midnight e.g. 24:00:00, 25:00:00) and call bus schedule function
						$nextRec = getnextRecord($stop_id, $arrayafterEnd[0]["Route"],1,date('H:i:s',strtotime($arrayafterEnd[0]["date"]."m")),date("m/d/y",$dateaftermidnight),0 );	
					}
				}
			
				array_push($grouparr,$nextRec); //Add next record
				
			}
			if($directionTwoCounter == 3  )
			{
				$Doubleroute = ""; //Check in array if key has two arrays underneath and pop off last value
				$TripeRoute = ""; //Check in array if key has two arrays underneath and pop off last value
				
				foreach ($grouparr as $group) {
					if(count($group) == 2 )
						$Doubleroute = $group[1]["Route"];
					if(count($group) == 3)
						$TripeRoute = $group[2]["Route"];
				}
				if ($Doubleroute != "" )
					array_pop($grouparr[$Doubleroute]);
				if ($TripeRoute != "" )
					array_pop($grouparr[$TripeRoute]);
				else
					array_pop($grouparr);
			}
			if($directionOneCounter == 1 )
			{
				$array= end($grouparr); //Find last array value
				$arrayafterEnd = array();
				foreach ($array as $group) {
					// Add row after last value of array 
					array_push($arrayafterEnd,array ("Route" => $group["Route"], "date" => $group["date"],"day" =>$group["day"], "Direction" =>$group["Direction"],"DateCalender" =>$group["DateCalender"]));
				}
				
				while(count($nextRec) != 1)
				{
					switch (substr(date('H:i:s',strtotime($arrayafterEnd[0]["date"]."m")),0,2)) 
					{
						case '00':
							$nextRec = getnextRecord($stop_id, $arrayafterEnd[0]["Route"],1,"24".substr(date('H:i:s',strtotime($arrayafterEnd[0]["date"]."m")),0,2),date('m/d/y',strtotime($arrayafterEnd[0]["DateCalender"])),1 );
						break;
						case '01':
							$nextRec = getnextRecord($stop_id, $arrayafterEnd[0]["Route"],1,"25".substr(date('H:i:s',strtotime($arrayafterEnd[0]["date"]."m")),0,2),date('m/d/y',strtotime($arrayafterEnd[0]["DateCalender"])),1 );
						break;
						case '02':
								$nextRec = getnextRecord($stop_id, $arrayafterEnd[0]["Route"],1,"26".substr(date('H:i:s',strtotime($arrayafterEnd[0]["date"]."m")),0,2),date('m/d/y',strtotime($arrayafterEnd[0]["DateCalender"])),1 );
						break;
						case '03':
							$nextRec = getnextRecord($stop_id, $arrayafterEnd[0]["Route"],1,"27".substr(date('H:i:s',strtotime($arrayafterEnd[0]["date"]."m")),0,2),date('m/d/y',strtotime($arrayafterEnd[0]["DateCalender"])),1 );
						break;
						case '04':
							$nextRec = getnextRecord($stop_id, $arrayafterEnd[0]["Route"],1,"28".substr(date('H:i:s',strtotime($arrayafterEnd[0]["date"]."m")),0,2),date('m/d/y',strtotime($arrayafterEnd[0]["DateCalender"])),1 );
						break;
						case '05':
							$nextRec = getnextRecord($stop_id, $arrayafterEnd[0]["Route"],1,"29".substr(date('H:i:s',strtotime($arrayafterEnd[0]["date"]."m")),0,2),date('m/d/y',strtotime($arrayafterEnd[0]["DateCalender"])),1 );
						break;
					}
					if(count($nextRec) != 1)
						$nextRec = getnextRecord($stop_id, $arrayafterEnd[0]["Route"],1,date('H:i:s',strtotime($arrayafterEnd[0]["date"]."m")),date('m/d/y',strtotime($arrayafterEnd[0]["DateCalender"])),1 );
					if(count($nextRec) != 1)
						$nextRec = getnextRecord($stop_id, $arrayafterEnd[0]["Route"],1,date('H:i:s',strtotime($arrayafterEnd[0]["date"]."m")),date('m/d/y',strtotime($arrayafterEnd[0]["DateCalender"])),1 );
					if (count($nextRec) != 1) 
					{
						$dateaftermidnight= strtotime(date('m/d/y',strtotime(date('m/d/y',strtotime($arrayafterEnd[0]["DateCalender"])). " +1 day"))); //Add a day to Date
				
						//Change time to 24:i:s (after midnight e.g. 24:00:00, 25:00:00) and call bus schedule function
						$nextRec = getnextRecord($stop_id, $arrayafterEnd[0]["Route"],1,date('H:i:s',strtotime($arrayafterEnd[0]["date"]."m")),date("m/d/y",$dateaftermidnight),1 );
					}
				}
				
				array_push($grouparr,$nextRec);
			}
			//End Adjusting array for when 1:3 ratio
			
			foreach ($grouparr as $levelOne) {
				foreach ($levelOne as $group) {
				//print_r($group);
				$now  = new DateTime(date('Y-m-d H:i:s'));
				     $end = new DateTime( date('Y-m-d H:i:s',strtotime($group["DateCalender"])));
    
				
				$interval = $now->diff($end);
				
					//$final_minutes = $final_minutes + ($final_hours * 60) ;
					if($interval->format('%h') !=0)
					echo  "<br>".$group["date"]." (". $interval->format('%hhr %i min ').")";
					else
						echo  "<br>".$group["date"]." (". $interval->format('%i min').")";
					if(date('D') != $group["day"])
						echo " ".$group["day"]." ";
				}
			}
		}
		
		// Track time of script
		$time_end = microtime(true);
		$time = $time_end - $time_start;
			
		// insert db data if a user is present
		if ($user != "")
			dbInsert($stop_id, $route,$user,$direction,round($time,2));
			
	}
	}
	else
	{
	echo "No Valid Stop Times";
	}
	posix_kill( getmypid(), 28 ); 
//------------------------------------------------------------------------------
//----------------------------------- functions --------------------------------
//------------------------------------------------------------------------------
function getnextRecord($stop_id, $route,$NumberOfLoops,$Date,$DateCalender,$direction)
{
try
{
	// Variables for db array
	$FinalRoute= ""; 
	$FinalTime="";
	$FinalDay="";
	$NumberOfLoops = array();
	if ($stop_id != ""){
    
		$flagdate = false; //Flag date as after midnight trip to format Day as next day e.g. (Mon)
   
		putenv('TDSVER=8.0');
		$myServer = MYSERVER;
		$myUser = "xxxx";
		$myPass = "xxxx";
		$myDB = "xxxx";
		$dbhandle = mssql_connect($myServer, $myUser, $myPass) or die("Couldn't connect to SQL Server on $myServer $myUser");
		$selected = mssql_select_db($myDB, $dbhandle) or die("Couldn't open database $myDB");
    
		$stmt=mssql_init("getNTABus", $dbhandle);
    
		$STOPid=$stop_id;
	
		// If route exists assign route unless assign null
		if($route != "")
		{
			$Route=$route;
		}
		else
		{
			$Route=null;
		}
	
		// If direction exists assign direction unless assign null
		if($direction != "")
		{
			$Direction = $direction;
		}
		else
		{
			$Direction=null;
		}
   
		$Top = 1; // Number of trips to search db for
    
		// Bind SQL variables
		mssql_bind($stmt, "@STOPid",    &$STOPid,    SQLVARCHAR,    FALSE,FALSE,30);
		mssql_bind($stmt, "@Route",  &$Route,  SQLVARCHAR, FALSE,FALSE,30);
		mssql_bind($stmt, "@Date", &$Date, SQLVARCHAR, FALSE,FALSE,30); 
		mssql_bind($stmt, "@NumberOfLoops", &$Top, SQLINT4, FALSE,FALSE);
		mssql_bind($stmt, "@DateCalender", &$DateCalender,SQLVARCHAR, FALSE,FALSE);	
		mssql_bind($stmt, "@Direction", &$Direction,SQLVARCHAR, FALSE,FALSE);		

		// now execute the procedure 
		$result = mssql_execute($stmt);
  
		while ($res = mssql_fetch_array($result)) {
      
			//Format time 
			switch (substr($res["arrival_time"],0,2)) {
				case '24':
					$flagdate = true;
					$time = "00";
					$time .= substr($res["arrival_time"],2,strlen($res["arrival_time"])-2);
				break;
				case '25':
					$flagdate = true;
					$time = "01";
					$time .= substr($res["arrival_time"],2,strlen($res["arrival_time"])-2);
				break;
				case '26':
					$flagdate = true;
					$time = "02";
					$time .= substr($res["arrival_time"],2,strlen($res["arrival_time"])-2);
				break;
				case '27':
					$flagdate = true;
					$time = "03";
					$time .= substr($res["arrival_time"],2,strlen($res["arrival_time"])-2);
				break;
				case '28':
					$flagdate = true;
					$time = "04";
					$time .= substr($res["arrival_time"],2,strlen($res["arrival_time"])-2);
				break;
				case '29':
					$flagdate = true;
					$time = "05";
					$time .= substr($res["arrival_time"],2,strlen($res["arrival_time"])-2);
				break;
				default:
					$time = $res["arrival_time"];
				break;
			}
			
		// Assign variables to push to array  
		$date = date('H:i:s', strtotime($time));
		$FinalRoute= $res["route_short_name"];
		$FinalTime= $date;
		
		// Date flagged as next day for after Midnight appears as (Mon) or if not flagged (Sun)
		if ($flagdate == true)
		{
			$FinalDay=date("D",strtotime(date('m/d/y',strtotime($DateCalender. " +1 day"))));
		}
		else 
		{
			$FinalDay=date("D",strtotime(date('m/d/y',strtotime($DateCalender))));
		}
		
		// Push Database Information onto array
		array_push($NumberOfLoops,array ( "Route" => $FinalRoute,"date" => substr(date('g:ia',strtotime($DateCalender ." ".$FinalTime)),0,strlen(date('g:ia',strtotime($DateCalender ." ".$FinalTime)))-1), "day" =>$FinalDay,"Direction" =>$res["direction_id"],"DirectionDesc" => $res["trip_headsign"] ));
		
		// Return flag to original state
		$flagdate = false;
    }
    
    mssql_close($dbhandle); 
	return $NumberOfLoops; // return array of trips
    
	}
	else {
		echo DEFAULTMESSAGE;
	}
	}
	 catch (Exception $e)
	{
		 mssql_close($dbhandle); 
		 exit();
	}
}

function checkDirection($stop_id,$route_id)
{
	try
	{
	if($route_id != "")
	{
		$Route=$route_id;
	}
	else
	{
		$Route=null;
	}
	putenv('TDSVER=8.0');
	$myServer = MYSERVER;
	$myUser = "xxxx";
	$myPass = "xxxx";
	$myDB = "xxxx";
	$dbhandle = mssql_connect($myServer, $myUser, $myPass) or die("Couldn't connect to SQL Server on $myServer $myUser");
	$selected = mssql_select_db($myDB, $dbhandle) or die("Couldn't open database $myDB");
  
	$stmt=mssql_init("checkDirection", $dbhandle);
	$stopID=$stop_id;
	mssql_bind($stmt, "@stopID", &$stopID, SQLVARCHAR, FALSE,FALSE,20);
	mssql_bind($stmt, "@route", &$Route, SQLVARCHAR, FALSE,FALSE,20);
	$result = mssql_execute($stmt);
	
	$DirectionArray = array();
	
	while ($res = mssql_fetch_array($result)) {
			array_push($DirectionArray,array ( "direction" => $res["direction_id"]));
	}
	mssql_close($dbhandle);
	return $DirectionArray;
	}
	 catch (Exception $e)
	{
		error();
		 mssql_close($dbhandle); 
		 exit();
	}
}

function check_valid_stop_times($stop_id)
{
	try
	{
	
	putenv('TDSVER=8.0');
	$myServer = MYSERVER;
	$myUser = "xxxx";
	$myPass = "xxxx";
	$myDB = "xxxx";
	$dbhandle = mssql_connect($myServer, $myUser, $myPass) or die("Couldn't connect to SQL Server on $myServer $myUser");
	$selected = mssql_select_db($myDB, $dbhandle) or die("Couldn't open database $myDB");
  
	$stmt=mssql_init("check_valid_stop_times", $dbhandle);
	$stopID=$stop_id;
	mssql_bind($stmt, "@stop_id", &$stopID, SQLVARCHAR, FALSE,FALSE,20);
	$result = mssql_execute($stmt);
	
	$count = "";
	
	while ($res = mssql_fetch_array($result)) {
				$count= $res["count"];
	}
	mssql_close($dbhandle);
	return $count;
	}
	 catch (Exception $e)
	{
		return $count;
		 mssql_close($dbhandle); 
		 exit();
	}
}
function dbInsert($stop_id, $route_sms,$user_sms,$direction,$time)
{
try
{
	putenv('TDSVER=8.0');
	$myServer = MYSERVER;
	$myUser = "xxxx";
	$myPass = "xxxx";
	$myDB = "xxxx";
	$dbhandle = mssql_connect($myServer, $myUser, $myPass) or die("Couldn't connect to SQL Server on $myServer $myUser");
	$selected = mssql_select_db($myDB, $dbhandle) or die("Couldn't open database $myDB");
  
	$stmt=mssql_init("insertSMSData", $dbhandle);
	$STOPid=$stop_id;
	
		// If route exists assign route unless assign null
		if($route_sms != "")
		{
			$Route=$route_sms;
		}
		else
		{
			$Route=null;
		}
		if($user_sms != "")
		{
			$User=$user_sms;
		}
		else
		{
			$User=null;
		}
		
		// If direction exists assign direction unless assign null
		if($direction != "")
		{
			$Direction=$direction;
		}
		else
		{
			$Direction=null;
		}
		
		// Bind SQL variables
		mssql_bind($stmt, "@stop_id",    &$STOPid,    SQLVARCHAR,    FALSE,FALSE,30);
		mssql_bind($stmt, "@route",  &$Route,  SQLVARCHAR, FALSE,FALSE,30);
		mssql_bind($stmt, "@smsuser", &$User,SQLVARCHAR, FALSE,FALSE,30);
		mssql_bind($stmt, "@direction", &$Direction,SQLVARCHAR, FALSE,FALSE,1);
		mssql_bind($stmt, "@timeToExecute", &$time,SQLFLT8, FALSE,FALSE);
		
	$result = mssql_execute($stmt);
	mssql_close($dbhandle);
	}
	 catch (Exception $e)
	{
		error();
		 mssql_close($dbhandle); 
		 exit();
	}
	
}


// Get the stopName
function getStopName($stopID){
try
	{
	putenv('TDSVER=8.0');
	$myServer = MYSERVER;
	$myUser = "xxxx";
	$myPass = "xxxx";
	$myDB = "xxxx";
	$dbhandle = mssql_connect($myServer, $myUser, $myPass) or die("Couldn't connect to SQL Server on $myServer $myUser");
	$selected = mssql_select_db($myDB, $dbhandle) or die("Couldn't open database $myDB");
  
	$stmt=mssql_init("getStopName", $dbhandle);
	$stopID=$stopID;
	mssql_bind($stmt, "@STOPid", &$stopID, SQLVARCHAR, FALSE,FALSE,20);
	$result = mssql_execute($stmt);
	$row = mssql_fetch_row($result);
	mssql_close($dbhandle);
	return $row[0];
	}
	 catch (Exception $e)
	{
		error();
		 mssql_close($dbhandle); 
		 exit();
	}
}

// Check to see if stop is valid
function getStopCriteria($stop_id,$route,$datecalender)
{
	try
	{
	putenv('TDSVER=8.0');
	$myServer = MYSERVER;
	$myUser = "xxxx";
	$myPass = "xxxx";
	$myDB = "xxxx";
	$dbhandle = mssql_connect($myServer, $myUser, $myPass) or die("Couldn't connect to SQL Server on $myServer $myUser");
	$selected = mssql_select_db($myDB, $dbhandle) or die("Couldn't open database $myDB");
  
	$stmt=mssql_init("checkCriteria", $dbhandle);
	$stopID=$stopID;
  
	mssql_bind($stmt, "@stop_id",&$stop_id,SQLVARCHAR,FALSE,FALSE,30);
	mssql_bind($stmt, "@route",&$route,SQLVARCHAR,FALSE,FALSE,30);
  
	$result = mssql_execute($stmt);
	$row = mssql_fetch_row($result);
	mssql_close($dbhandle);
	
	return $row[0];
	
	}
	 catch (Exception $e)
	{
		error();
		 mssql_close($dbhandle); 
		 exit();
	}
}

// Get next trips from database
function bus_schedule($stop_id, $route,$NumberOfLoops,$Date,$DateCalender,$direction ){
	try
	{
	
	// Variables for db array
	$FinalRoute= ""; 
	$FinalTime="";
	$FinalDay="";
  
	if ($stop_id != ""){
    
		$flagdate = false; //Flag date as after midnight trip to format Day as next day e.g. (Mon)
   
		putenv('TDSVER=8.0');
		$myServer = MYSERVER;
		$myUser = "xxxx";
		$myPass = "xxxx";
		$myDB = "xxxx";
		$dbhandle = mssql_connect($myServer, $myUser, $myPass) or die("Couldn't connect to SQL Server on $myServer $myUser");
		$selected = mssql_select_db($myDB, $dbhandle) or die("Couldn't open database $myDB");
    
		$stmt=mssql_init("getNTABus", $dbhandle);
    
		$STOPid=$stop_id;
	
		// If route exists assign route unless assign null
		if($route != "")
		{
			$Route=$route;
		}
		else
		{
			$Route=null;
		}
	
		// If direction exists assign direction unless assign null
		if($direction != "")
		{
			$Direction = $direction;
		}
		else
		{
			$Direction=null;
		}
   
		$Top = NUMBEROFRESULTS-count($NumberOfLoops); // Number of trips to search db for
    
		// Bind SQL variables
		mssql_bind($stmt, '@STOPid', &$STOPid,    SQLVARCHAR,    FALSE,FALSE,30);
		mssql_bind($stmt, '@Route', &$Route,  SQLVARCHAR, FALSE,FALSE,30);
		mssql_bind($stmt, "@Date", &$Date, SQLVARCHAR, FALSE,FALSE,30); 
		mssql_bind($stmt, "@NumberOfLoops", &$Top, SQLINT4, FALSE,FALSE);
		mssql_bind($stmt, "@DateCalender", &$DateCalender,SQLVARCHAR, FALSE,FALSE);	
		mssql_bind($stmt, "@Direction", &$Direction,SQLVARCHAR, FALSE,FALSE);		

		// now execute the procedure 
		$result = mssql_execute($stmt);
  
		while ($res = mssql_fetch_array($result)) {
      
			//Format time 
			switch (substr($res["arrival_time"],0,2)) {
				case '24':
					$flagdate = true;
					$time = "00";
					$time .= substr($res["arrival_time"],2,strlen($res["arrival_time"])-2);
				break;
				case '25':
					$flagdate = true;
					$time = "01";
					$time .= substr($res["arrival_time"],2,strlen($res["arrival_time"])-2);
				break;
				case '26':
					$flagdate = true;
					$time = "02";
					$time .= substr($res["arrival_time"],2,strlen($res["arrival_time"])-2);
				break;
				case '27':
					$flagdate = true;
					$time = "03";
					$time .= substr($res["arrival_time"],2,strlen($res["arrival_time"])-2);
				break;
				case '28':
					$flagdate = true;
					$time = "04";
					$time .= substr($res["arrival_time"],2,strlen($res["arrival_time"])-2);
				break;
				case '29':
					$flagdate = true;
					$time = "05";
					$time .= substr($res["arrival_time"],2,strlen($res["arrival_time"])-2);
				break;
				default:
					$time = $res["arrival_time"];
				break;
			}
		// Assign variables to push to array  
		$date = date('H:i:s', strtotime($time));
		$FinalRoute= $res["route_short_name"];
		$FinalTime= $date;
		
		// Date flagged as next day for after Midnight appears as (Mon) or if not flagged (Sun)
		if ($flagdate == true)
		{
			$FinalDay=date("D",strtotime(date('m/d/y',strtotime($DateCalender. " +1 day"))));
		}
		else 
		{
			$FinalDay=date("D",strtotime(date('m/d/y',strtotime($DateCalender))));
		}
		
		// Push Database Information onto array
		array_push($NumberOfLoops,array ("Route" => $FinalRoute,"date" => date('m/d/y h:i a',strtotime($DateCalender ." ".$FinalTime)), "Day" =>$FinalDay,"Direction" =>$res["direction_id"],"DirectionDesc" => $res["trip_headsign"]));
		
		// Return flag to original state
		$flagdate = false;
    }
    
    mssql_close($dbhandle); 
	return $NumberOfLoops; // return array of trips
    
	}
	else {
		echo DEFAULTMESSAGE;
	}
	
	}
	 catch (Exception $e)
	{
		error();
		 mssql_close($dbhandle); 
		 exit();
	}
	
}

// Function to skip to next weekday
function next_weekday($timestamp) {
        
    $next = strtotime( $timestamp."midnight"); // same day at midnight
    $d = getdate($next); //Get day of week
	
    if($d['wday'] == 0 || $d['wday'] == 6) // If Saturday or Sunday
		$next = strtotime( $timestamp. "midnight next monday"); // Skip to next Monday
		
    return date("m/d/y",$next);
} 

// Function to skip to next Saturday
function next_sat($timestamp) {
        
    $next = strtotime( $timestamp."midnight"); // same day at midnight
    $d = getdate($next); //Get day of week
	
    if(($d['wday'] >= 1 && $d['wday'] <= 5) || $d['wday'] == 0) //If Sunday or Monday through Friday
		$next = strtotime( $timestamp."midnight next saturday"); // Skip to next Saturday
		
    return date("m/d/y",$next);
} 

// Function that skips calender days
function skipCalender($dateCalender,$route)
{	
	//Array to hold schedule values for weekend and weekday schedules
	$Routes = array (  "1" => array ( "Weekday" => 1,"Saturday" => 1,"Sunday" => 0),
				   "2" => array ( "Weekday" => 1,"Saturday" => 1,"Sunday" => 0),
				   "8" => array ( "Weekday" => 1,"Saturday" => 0,"Sunday" => 0),
				   "9" => array ( "Weekday" => 1,"Saturday" => 1,"Sunday" => 1), 
				   "19" => array ( "Weekday" => 1,"Saturday" => 1,"Sunday" => 0),
				   "62" => array ( "Weekday" => 1,"Saturday" => 0,"Sunday" => 0),
				   "71" => array ( "Weekday" => 1,"Saturday" => 0,"Sunday" => 0),
				   "78" => array ( "Weekday" => 1,"Saturday" => 0,"Sunday" => 0),
				   "80" => array ( "Weekday" => 1,"Saturday" => 0,"Sunday" => 0),
				   "LUCY" => array ( "Weekday" => 1,"Saturday" => 0,"Sunday" => 0),
				   "90" => array ( "Weekday" => 1,"Saturday" => 1,"Sunday" => 0),
				   "91" => array ( "Weekday" => 0,"Saturday" => 1,"Sunday" => 0),
				   "92" => array ( "Weekday" => 1,"Saturday" => 1,"Sunday" => 0),
				   "95" => array ( "Weekday" => 1,"Saturday" => 1,"Sunday" => 0),
				   "106" => array ( "Weekday" => 1,"Saturday" => 1,"Sunday" => 0),
				   "107" => array ( "Weekday" => 1,"Saturday" => 1,"Sunday" => 0),
				   "112" => array ( "Weekday" => 1,"Saturday" => 1,"Sunday" => 0),
				   "116" => array ( "Weekday" => 1,"Saturday" => 0,"Sunday" => 0),
				   "118" => array ( "Weekday" => 1,"Saturday" => 1,"Sunday" => 0),
				   "127" => array ( "Weekday" => 1,"Saturday" => 1,"Sunday" => 0),
				   "128" => array ( "Weekday" => 1,"Saturday" => 1,"Sunday" => 0),
				   "131" => array ( "Weekday" => 1,"Saturday" => 1,"Sunday" => 0),
				   "134" => array ( "Weekday" => 1,"Saturday" => 0,"Sunday" => 0),
				   "139" => array ( "Weekday" => 1,"Saturday" => 1,"Sunday" => 0),
				   "150" => array ( "Weekday" => 1,"Saturday" => 1,"Sunday" => 0),
				   "201" => array ( "Weekday" => 1,"Saturday" => 0,"Sunday" => 0),
				   "205" => array ( "Weekday" => 1,"Saturday" => 0,"Sunday" => 0),
				   "206" => array ( "Weekday" => 1,"Saturday" => 0,"Sunday" => 0),
				   "304" => array ( "Weekday" => 1,"Saturday" => 1,"Sunday" => 0),
				   "306" => array ( "Weekday" => 1,"Saturday" => 0,"Sunday" => 0),
				   "310" => array ( "Weekday" => 1,"Saturday" => 1,"Sunday" => 0),
				   "314" => array ( "Weekday" => 1,"Saturday" => 0,"Sunday" => 0) 
				);	
			
			// Get Day of Week
			$d = getdate(strtotime($dateCalender));
			$weekday = $Routes[$route]["Weekday"]; //Weekday schedule from route array
			$sat = $Routes[$route]["Saturday"]; //Saturday schedule from route array
			$sun = $Routes[$route]["Sunday"]; //Sunday schedule from route array
			$resetDate = 0;
			
			// If route has weekday and Saturday schedule on Sunday skip to next Monday
			if($weekday == 1&& $sat== 1 && $sun == 0&& $d['wday']==6)
			{
				$dateCalender = next_weekday($dateCalender);
				$resetDate= 1;
			}
			
			// If route has only weekday schedule skip to next Monday on weekends
			else if($weekday == 1 && $sat == 0 && $sun == 0 && ($d['wday']==0 || $d['wday']==6) )
			{
				$dateCalender = next_weekday($dateCalender);
				$resetDate= 1;
			}
			
			//If route has only Saturday schedule on Sundays and weekdays skip to next Saturday
			else if($weekday == 0 && $sat == 1 && $sun == 0 && ($d['wday'] >= 1 || $d['wday'] <= 5 || $d['wday'] == 0))
			{
				$dateCalender = next_sat($dateCalender);
				$resetDate= 1;
			}
			
		$SkipArray=array();
		array_push($SkipArray,array ("DateCalender" => $dateCalender,"resetDate" => $resetDate));
	return $SkipArray;
}
function error()
{
	$myFile = "includes/errorlog.txt";
$fh = fopen($myFile, 'a') or die("can't open file");
$stringData = PARAMETERS."  ".date('l jS \of F Y h:i:s A')."\n";
fwrite($fh, $stringData);
fclose($fh);
	echo 'Too Many requests Please try again.';
}


?>
