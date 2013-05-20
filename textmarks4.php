<META name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable = no" />
<!-- <link type="text/css" rel="stylesheet" media="handheld" href="http://www.septa.org/css/mobile.css" />
<link type="text/css" rel="stylesheet" media="screen and (min-device-width: 481px)" href="http://www.septa.org/css/smartmobile.css" />
<link type="text/css" rel="stylesheet" media="only screen and (max-device-width: 480px)" href="http://www.septa.org/m/css/smartmobile.css" />  -->
<script type="text/javascript" src="http://www.septa.org//site/js/jquery-1.3.2.js"></script>
<script type="text/javascript" src="http://www.septa.org//site/js/isBrowser.js"></script>
<script type="text/javascript" src="http://www.septa.org//site/js/mobile-script.js"></script>
<noscript>
<style type="text/css">
    #BackButtonlnk { display:none; }
</style>
<link rel="stylesheet" href="http://www.septa.org//m/css/smartmobile.css" type="text/css" />
</noscript>
<title>SEPTA | When's My Next Bus?</title>


<body>
  <div id="m_header">
    <a href="http://www.septa.org/m/index.html" class="logo">
      <img src="/m/images/mobile_logo.gif" alt="SEPTA" border="0"/>
    </a>
  </div><br/><br/>
  
<a class="white" href="http://www.septa.org/m/schedules" style="display: block; position:absolute; top:4px; right:7px; text-decoration: none;">
    <div id="BackButton1"></div>
    <div id="BackButton">Schedules</div>
    <div id="BackButton3"></div>
    </a>    
  
<ul id="mobileBtns">
    <li>
    <a href="index.php"><span class="schedBtn"></span><span class="btnText">When's My Next Bus?</span></a><div class="button_last"></div>
    
    </li>
</ul>   

<?php


//$stop_id = $_POST['req1'];
//$route = $_POST['req2'];
$date = $_POST['req3'];
$dateCalender= $_POST['req4'];
//$direction= $_POST['req5'];
//$stopname = $_POST['stopname'];
$parameters = $_POST['parameters'];
$service = strtolower($service);


// explode the parameters variable to break out individual parameters for stopname, stop_id, route, direction and directiondescription
list($stopname, $stop_id, $route, $direction, $directiondescription) = explode("::",$parameters);
?>

<h2 class="normal">Route <?php echo $route?> | To <?php echo $directiondescription?> | <?php echo $stopname?></h2>

<p>The next 10 buses stopping at <strong><?php echo $stopname?></strong> will be arriving at the following times:<br>

<?php





$Routes = array (  "1" => array ( "Weekday" => 1,"Saturday" => 1,"Sunday" => 0),
				   "2" => array ( "Weekday" => 1,"Saturday" => 1,"Sunday" => 0),
				 "116" => array ( "Weekday" => 1,"Saturday" => 0,"Sunday" => 0),
				 "91" => array ( "Weekday" => 0,"Saturday" => 1,"Sunday" => 0)
									 
									 );
									 
		if($date =="")
		{
			$date = date('H:i:s');
		}
		if($dateCalender =="")
		{
			$dateCalender = date('m/d/y');
		}						 
// Bus Schedules

//echo $route . "-" . $tp."\n";
	$stopName = getStopName($stop_id); // Check if stop id is valid
	$checkCriteria = getStopCriteria($stop_id,$route,$dateCalender); // Check if stop route combination would yeild any results
	//echo next_weekday($dateCalender) ." <br />";
	// if route combination invalid make route empty to show all possible routes
	if($checkCriteria =='0')
	{
		$route ="";
	}
	// if stopname invalid show reply message
	if($stopName == "")
	{
		echo "reply with:\n stopid# route#";
	}
	// if stopname invalid and combination invaild show reply message
	else if ($stopName == "" && $checkCriteria =='0' )
	{
		echo "reply with:\n stopid# route#";
	}
	else if ($stopName == "" && $checkCriteria !='0' )
	{
		echo "reply with:\n stopid# route#";
	}
	// Perform query 
	else 
	{
		// echo stopname for text message
		// echo $stopName."\n";
		$counter =0;
		//if no date was supplied get server date
		
		
		//check shedule days for routes
		if($route !="")
		{	
			// Get Day of Week
			$d = getdate(strtotime( $dateCalender));
			$weekday = $Routes[$route]["Weekday"];
			$sat = $Routes[$route]["Saturday"];
			$sun = $Routes[$route]["Sunday"];
			// if weekday and saturday schedule
			if($weekday == 1&& $sat== 1 && $sun == 0&& $d['wday']==6)
			{
				$dateCalender = next_weekday($dateCalender);
				$date = date('00:00:00');
			}
			// if weekday schedule
			else if($weekday == 1 && $sat == 0 && $sun == 0 && ($d['wday']==0 || $d['wday']==6) )
			{
				$dateCalender = next_weekday($dateCalender);
				$date = date('00:00:00');
			}
			//if sat schedule
			else if($weekday == 0 && $sat == 1 && $sun == 0 && ($d['wday'] >= 1 || $d['wday'] <= 5 || $d['wday'] == 0))
			{
				$dateCalender = next_sat($dateCalender);
				$date = date('00:00:00');
			}
		}
		
		// Loop through until next 4 stops
		while (  $counter < 10 )
        {
			// Check date to see if early morning trip and check for after midnight trips of the previous day
			switch (substr($date,0,2)) 
			{
				case '00':
					$counter+=bus_schedule($stop_id, $route,(10 - $counter),"24".substr($date,2,strlen($date)-2),date('m/d/y',strtotime(date('m/d/y',strtotime($dateCalender. " -1 day")))),$direction );
				break;
				case '01':
					$counter+=bus_schedule($stop_id, $route,(10 - $counter),"25".substr($date,2,strlen($date)-2),date('m/d/y',strtotime(date('m/d/y',strtotime($dateCalender. " -1 day")))),$direction  );
				break;
				case '02':
					$counter+=bus_schedule($stop_id, $route,(10 - $counter),"26".substr($date,2,strlen($date)-2),date('m/d/y',strtotime(date('m/d/y',strtotime($dateCalender. " -1 day")))),$direction  );
				break;
				case '03':
					$counter+=bus_schedule($stop_id, $route,(10 - $counter),"27".substr($date,2,strlen($date)-2),date('m/d/y',strtotime(date('m/d/y',strtotime($dateCalender. " -1 day")))),$direction  );
				break;
				case '04':
					$counter+=bus_schedule($stop_id, $route,(10 - $counter),"28".substr($date,2,strlen($date)-2),date('m/d/y',strtotime(date('m/d/y',strtotime($dateCalender. " -1 day")))),$direction  );
				break;
				case '05':
					$counter+=bus_schedule($stop_id, $route,(10 - $counter),"29".substr($date,2,strlen($date)-2),date('m/d/y',strtotime(date('m/d/y',strtotime($dateCalender. " -1 day")))),$direction  );
				break;
			}
			//echo (4 - $counter) .date('m/d/y');
			//check for regular trips
			$counter+=bus_schedule($stop_id, $route,(10 - $counter),$date,$dateCalender,$direction );
		
			//Check for after midnight trips a day ahead
			//echo "\n\n m=Main Menu ";
			if ($counter < 10) 
			{
				$dateaftermidnight= strtotime(date('m/d/y',strtotime($dateCalender. " +1 day"))) ;
				//echo date("m/d/y H:i:s",$date);
				$counter+=bus_schedule($stop_id, $route,(10-$counter),"24".substr(date('00:i:s'),date("m/d/y",$dateaftermidnight)),date('m/d/y',strtotime($dateCalender. " +1 day")),$direction );
			}
			//Check for early morning trips a day ahead
			if ($counter < 10) 
			{
				$dateaftermidnight= strtotime(date('m/d/y',strtotime($dateCalender. " +1 day"))) ;
				//echo date("m/d/y H:i:s",$date);
				$counter+=bus_schedule($stop_id, $route,(10-$counter),date('00:i:s'),date("m/d/y",$dateaftermidnight),$direction );
			
			}
			$date = date('00:00:00');
			$dateCalender = date('m/d/y',strtotime($dateCalender. " +1 day"));
			
		if($route !="")
		{	
			// Get Day of Week
			$d = getdate(strtotime( $dateCalender));
			$weekday = $Routes[$route]["Weekday"];
			$sat = $Routes[$route]["Saturday"];
			$sun = $Routes[$route]["Sunday"];
			// if weekday and saturday schedule
			if($weekday == 1&& $sat== 1 && $sun == 0&& $d['wday']==6)
			{
				$dateCalender = next_weekday($dateCalender);
				$date = date('00:00:00');
			}
			// if weekday schedule
			else if($weekday == 1 && $sat == 0 && $sun == 0 && ($d['wday']==0 || $d['wday']==6) )
			{
				$dateCalender = next_weekday($dateCalender);
				$date = date('00:00:00');
			}
			//if sat schedule
			else if($weekday == 0 && $sat == 1 && $sun == 0 && ($d['wday'] >= 1 || $d['wday'] <= 5 || $d['wday'] == 0))
			{
				$dateCalender = next_sat($dateCalender);
				$date = date('00:00:00');
			}
		}
			
			
		}
	}
	if($counter == 0)
	{
		echo "\n There are no results \n";
	}




//------------------------------------------------------------------------------
//----------------------------------- functions --------------------------------
//------------------------------------------------------------------------------



function getStopName($stopID){
  putenv('TDSVER=8.0');
  $myServer = "209.120.199.18:37433";
  $myUser = "mzdev";
  $myPass = "5ep7ar0ck5";
  $myDB = "GTFS";
  $dbhandle = mssql_connect($myServer, $myUser, $myPass) or die("Couldn't connect to SQL Server on $myServer $myUser");
  $selected = mssql_select_db($myDB, $dbhandle) or die("Couldn't open database $myDB");
  
  $stmt=mssql_init("getStopName", $dbhandle);
  $stopID=$stopID;
  mssql_bind($stmt, '@STOPid',    &$stopID,    SQLVARCHAR,    FALSE,FALSE,20);
  $result = mssql_execute($stmt);
  $row = mssql_fetch_row($result);
  mssql_close($dbhandle);
  return $row[0];
}
function getStopCriteria($stop_id,$route,$datecalender)
{
	putenv('TDSVER=8.0');
  $myServer = "209.120.199.18:37433";
  $myUser = "mzdev";
  $myPass = "5ep7ar0ck5";
  $myDB = "GTFS";
  $dbhandle = mssql_connect($myServer, $myUser, $myPass) or die("Couldn't connect to SQL Server on $myServer $myUser");
  $selected = mssql_select_db($myDB, $dbhandle) or die("Couldn't open database $myDB");
  
  $stmt=mssql_init("checkCriteria", $dbhandle);
  $stopID=$stopID;
  mssql_bind($stmt, '@stop_id',    &$stop_id,    SQLVARCHAR,    FALSE,FALSE,30);
   mssql_bind($stmt, '@route',    &$route,    SQLVARCHAR,    FALSE,FALSE,30);
    mssql_bind($stmt, '@DateCalender',    &$datecalender,    SQLVARCHAR,    FALSE,FALSE,30);
  $result = mssql_execute($stmt);
  $row = mssql_fetch_row($result);
  mssql_close($dbhandle);
  return $row[0];
}
function bus_schedule($stop_id, $route,$NumberOfLoops,$Date,$DateCalender,$direction ){
  $details = "";
  if ($stop_id != ""){
    $counter = 0;
    //echo "TP & Rt";
    //$timepoint = $route;
     $flagdate = false;
    // Get all the detour info for the route
    putenv('TDSVER=8.0');
    $myServer = "209.120.199.18:37433";
    $myUser = "mzdev";
    $myPass = "5ep7ar0ck5";
    $myDB = "GTFS";
    $dbhandle = mssql_connect($myServer, $myUser, $myPass) or die("Couldn't connect to SQL Server on $myServer $myUser");
    $selected = mssql_select_db($myDB, $dbhandle) or die("Couldn't open database $myDB");
    
    $stmt=mssql_init("getNTABus", $dbhandle);
    
    
    /* now bind the parameters to it */
    $STOPid=$stop_id;
    if($route != "")
      {
	$Route=$route;
      }
    else
      {
	$Route=null;
      }
	  if($direction != "")
	  {
	  $Direction = $direction;
	  }
	  else
	  {
	  $Direction=null;
	  }
   // $Date=  date("H:i:s",strtotime($datetime));
    //echo $Date;
    //$Date=date("H:i:s"); 
    
    $NumberOfLoops = $NumberOfLoops;
    
    //$DateCalender= date("m/d/y",strtotime ( $DateCalender));
     
    
    mssql_bind($stmt, '@STOPid',    &$STOPid,    SQLVARCHAR,    FALSE,FALSE,30);
    mssql_bind($stmt, '@Route',  &$Route,  SQLVARCHAR, FALSE,FALSE,30);
    mssql_bind($stmt, "@Date", &$Date, SQLVARCHAR, FALSE,FALSE,30); 
    mssql_bind($stmt, "@NumberOfLoops", &$NumberOfLoops, SQLINT4, FALSE,FALSE);
    mssql_bind($stmt, "@DateCalender", &$DateCalender,SQLVARCHAR, FALSE,FALSE);	
	mssql_bind($stmt, "@Direction", &$Direction,SQLVARCHAR, FALSE,FALSE);		

    // now execute the procedure 
    $result = mssql_execute($stmt);
    //$arr=mssql_fetch_row($result); 
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
      $date = date('H:i:s', strtotime($time));
//      $details .=  "Rt.". $res["route_short_name"]." @ ". substr(date('g:ia',strtotime($date)),0,strlen(date('g:ia',strtotime($date)))-1);
      $details .=  substr(date('g:ia',strtotime($date)),0,strlen(date('g:ia',strtotime($date)))-1);      
		if ($flagdate == true)
		{
			$details .= " ".date("D",strtotime(date('m/d/y',strtotime($DateCalender. " +1 day"))))."\n"; 
		}
		else 
		{
		//$date2 = DateTime::createFromFormat('m/d/y', $DateCalender);
		//echo "<br />".$DateCalender."<br />";
		$details .= " ".date("D",strtotime(date('m/d/y',strtotime($DateCalender))))."<br>"; 
		}
		
      $counter++;
	  $flagdate = false;
    }
    
    mssql_close($dbhandle); 
    if($details != '')
      {
	?><br><?php echo $details;
      }
    
    return $counter;
    
  }
  else {
    echo "reply with:\n stopid# route# \n";
  }
}

function next_weekday($timestamp) {
        
        $next = strtotime( $timestamp."midnight");
        $d = getdate($next);
        if($d['wday'] == 0 || $d['wday'] == 6) 
		$next = strtotime( $timestamp. "midnight next monday");
        return date("m/d/y",$next);
    } 
function next_sat($timestamp) {
        
        $next = strtotime( $timestamp."midnight");
        $d = getdate($next);
        if(($d['wday'] >= 1 && $d['wday'] <= 5) || $d['wday'] == 0) 
		$next = strtotime( $timestamp."midnight next saturday");
        return date("m/d/y",$next);
    } 






?>

<div align="right"><a href="index.php"><< search again</a></div>

<div id="m_footer">
<a href="http://www.septa.org/m/cs/comment/">Comment</a>&nbsp;|
<a href="http://www.septa.org/m/cs/contact/">Contact Us</a>&nbsp;|
<a href="http://www.septa.org?noredirect=true">Full Site</a></div>

<script type="text/javascript">
  var gaJsHost = (("https:" == document.location.protocol) ? "https://ssl." : "http://www.");
  document.write(unescape("%3Cscript src='" + gaJsHost + "google-analytics.com/ga.js' type='text/javascript'%3E%3C/script%3E"));
  </script>
  
  <script type="text/javascript">
  try {
  var pageTracker = _gat._getTracker("UA-10756839-1");
  pageTracker._setDomainName(".septa.org");
  pageTracker._trackPageview();
  
  } catch(err) {}
  </script>
  
 </body>
</html>   
