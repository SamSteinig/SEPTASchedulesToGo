<?php
$Route = '';
$Direction = 0;

$Route = $_POST['Route'];
$Direction = $_POST['Direction'];


define('MYSERVER',"xxxx");
define('MYUSER',"xxxx");
define('MYPASS',"xxxx");
define('MYDB',"xxxx");
try {
putenv('TDSVER=8.0');
		$myServer = MYSERVER;
		$myUser = MYUSER;
		$myPass = MYPASS;
		$myDB = MYDB;
		$dbhandle = mssql_connect($myServer, $myUser, $myPass) or die("Couldn't connect to SQL Server on $myServer $myUser");
	$selected = mssql_select_db($myDB, $dbhandle) or die("Couldn't open database $myDB");
		$query = "select * from bus_stop_directions where route = '$Route' and direction = '$Direction'";
		$dbresult = mssql_query($query);
while($res = mssql_fetch_array($dbresult)) {
$Direction= $res["Direction"];
$DirectionDescription=$res["DirectionDescription"];
$DirectionDescription  = str_replace("\"", "", $DirectionDescription);
}
mssql_close($dbhandle);
}
catch (Exception $e)
{
	echo 'Too many requests- Please try again';
	mssql_close($dbhandle);
	exit();
}




try {
$dbhandle = mssql_connect($myServer, $myUser, $myPass) or die("Couldn't connect to SQL Server on $myServer $myUser");
	$selected = mssql_select_db($myDB, $dbhandle) or die("Couldn't open database $myDB");
		$query = "SELECT [Signup],[Route],  RTRIM(REPLACE([Direction], '\"', '')) as Direction,[DirCode],[Seq],[NodeAbbr],[StopAbbr],[Nodeid],[Stopid],REPLACE([StopName] , '\"', '') as StopName,[Lon],[Lat] FROM [SchedulesToGo].[dbo].[bus_stop_ids]  where route = '$Route' and  RTRIM(REPLACE(Direction, '\"', '')) = '$Direction' order by Stopname";
		
	$dbresult = mssql_query($query);


switch ($Route) {
	
case "BSS":
	?>
	<h2 class="normal">Broad Street Line | To <?php echo $DirectionDescription;?></h2><?php;
	break;
case "BSO":
	?>
	<h2 class="normal">Broad Street Line | Nite Owl | To <?php echo $DirectionDescription;?></h2><?php;
	break;
case "MFL":
	?>
	<h2 class="normal">Market-Frankford Line | To <?php echo $DirectionDescription;?></h2><?php;
	break;
case "MFO":
	?>
	<h2 class="normal">Market-Frankford Line | Nite Owl | To <?php echo $DirectionDescription;?></h2><?php;
	break;
case "NHSL":
	?>
	<h2 class="normal">Norristown High-Speed Line | To <?php echo $DirectionDescription;?></h2><?php;
	break;
default:
	?>
	<h2 class="normal">Route <?php echo $Route;?> | To <?php echo $DirectionDescription;?></h2><?php;
}

?>
<p>Choose Your Stop</p>
<form action="stop-times.php" method="get">
<select name="parameters">

<?php

$i=0;
/*while ($i < $num_rows) {


$Seq = mysql_result($stops_result,$i,"Seq");
$Direction = mysql_result($stops_result,$i,"Direction");
$DirCode = mysql_result($stops_result,$i,"DirCode");
$Stopid = mysql_result($stops_result,$i,"Stopid");
$Stopname = mysql_result($stops_result,$i,"StopName");
$StripPosition = strpos($Stopname,' -');
if ($StripPosition > 0) {
	$StopnameStripped = substr($Stopname,0,strpos($Stopname,' -'));
}
else
{
	$StopnameStripped = $Stopname;
}

$Lat = mysql_result($stops_result,$i,"Lat");
$Lon = mysql_result($stops_result,$i,"Lon");
$Lat = $Lat / 1000000;
$Lon = $Lon / 1000000;*/
while($res = mssql_fetch_array($dbresult)) {
$Route_title= $res["Route"];
$Seq = $res["Seq"];
$Direction = $res["Direction"]; 
$DirCode = $res["DirCode"];  
$Stopid = $res["Stopid"]; 
$Stopname = $res["StopName"]; 
$StripPosition = strpos($Stopname,' -');
if ($StripPosition > 0) {
	$StopnameStripped = substr($Stopname,0,strpos($Stopname,' -'));
}
else
{
	$StopnameStripped = $Stopname;
}
/*
$Lat = mysql_result($stops_result,$i,"Lat");
$Lon = mysql_result($stops_result,$i,"Lon");
$Lat = $Lat / 1000000;
$Lon = $Lon / 1000000;
*/


$parameters = $StopnameStripped . "::" . $Stopid . "::" . $Route . "::" . $DirCode . "::" . $DirectionDescription;

?>

<option value="<?php echo $parameters?>"><?php echo $StopnameStripped?></option>


<?php


$i++;
}
?>
</select>

                                                 
<p><input type="submit" / value="NEXT: List Times >>"></p>

<div align="right"><a href="index.php"><< search again</a></div>

<?php 
mssql_close($dbhandle);
 }
  catch (Exception $e)
{
// close the database connection
mssql_close($dbhandle);
exit();
}
?>
