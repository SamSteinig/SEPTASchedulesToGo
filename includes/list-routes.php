<?php


try {
define('MYSERVER',"XXXX");
define('MYUSER',"xxxx");
define('MYPASS',"XXXX");
define('MYDB',"XXXX");

putenv('TDSVER=8.0');
		$myServer = MYSERVER;
		$myUser = MYUSER;
		$myPass = MYPASS;
		$myDB = MYDB;
		$dbhandle = mssql_connect($myServer, $myUser, $myPass) or die("Couldn't connect to SQL Server on $myServer $myUser");
	$selected = mssql_select_db($myDB, $dbhandle) or die("Couldn't open database $myDB");
		
		$query = "SELECT TOP 1000 [route_description].[route_id]
      ,[description]
      ,[sequence]
	    , route_name
  FROM [Control_Center_Web].[dbo].[route_description]
  inner join [Control_Center_Web].dbo.route_info on 
  [Control_Center_Web].dbo.route_info.route_id=[route_description].route_id
  where mode='".$RouteType."' and marked_for_deletion=0
   order by sequence ";
		$dbresult = mssql_query($query);
		
?>


	
<?php
$RouteTypeDisplay = $RouteType;
if ($RouteType == "RailTransit") {
$RouteTypeDisplay = "Subway/High Speed";
}
?>

<p><b><?php echo $RouteTypeDisplay?></b></p>
<p>Choose Your Route</p>

<form action="direction.php" method="post">
<select name="Route">

<?php

$i=0;

while($res = mssql_fetch_array($dbresult)) {
		
		
$Route = $res["route_name"];
// $RouteType = mysql_result($stops_result,$i,"RouteType");


if ($RouteType == "Bus") {
?>

<option value="<?php echo $Route?>">Route <?php echo $Route?></option><br>
<?php
}


if ($RouteType == "Trolley") {
?>

<option value="<?php echo $Route?>">Route <?php echo $Route?></option><br>
<?php
}



$i++;
}

if ($RouteType == "RailTransit") {
?>

<option value="BSS">Broad Street Line</option>
<option value="BSO">Broad Street Line | Nite Owl</option> 
<option value="MFL">Market-Frankford Line</option>
<option value="MFO">Market-Frankford Line | Nite Owl</option>
<option value="NHSL">Norristown High-Speed Line</option>

<?php
}


?>
</select>
<p><input type="submit" / value="NEXT: Choose Direction >>"></p>
</form>



<?php
 mssql_close($dbhandle);
}
// close the database connection
catch (Exception $e)
{
 mssql_close($dbhandle);
 exit();
 }
?>
