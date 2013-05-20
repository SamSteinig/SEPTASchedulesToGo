<?php

$Route = '';

$Route = $_POST['Route'];

try {
define('MYSERVER',"xxxx");
define('MYUSER',"xxxx");
define('MYPASS',"xxxx");
define('MYDB',"SchedulesToGo");

putenv('TDSVER=8.0');
		$myServer = MYSERVER;
		$myUser = MYUSER;
		$myPass = MYPASS;
		$myDB = MYDB;
		$dbhandle = mssql_connect($myServer, $myUser, $myPass) or die("Couldn't connect to SQL Server on $myServer $myUser");
	$selected = mssql_select_db($myDB, $dbhandle) or die("Couldn't open database $myDB");
		$query = "SELECT [Route]
      ,[Direction]
      ,REPLACE([DirectionDescription] , '\"', '')as DirectionDescription
      ,[ListOrder]
      ,[Active]
      ,[RouteType]
      ,[dircode]
      ,[route_id]
  FROM [SchedulesToGo].[dbo].[bus_stop_directions]
GO


 WHERE Route = '$Route'";
		$dbresult = mssql_query($query);


switch ($Route) {
	
case "BSS":
	?>
	<h2 class="normal">Broad Street Line</h2><?php;
	break;
case "BSO":
	?>
	<h2 class="normal">Broad Street Line | Nite Owl</h2><?php;
	break;
case "MFL":
	?>
	<h2 class="normal">Market-Frankford Line</h2><?php;
	break;
case "MFO":
	?>
	<h2 class="normal">Market-Frankford Line | Nite Owl</h2><?php;
	break;
case "NHSL":
	?>
	<h2 class="normal">Norristown High-Speed Line</h2><?php;
	break;
default:
	?>
	<h2 class="normal">Route <?php echo $Route?></h2><?php;
}

?>

<p>Choose Your Direction</p>
<form action="bus-stop-ids.php" method="post">
<select name="Direction">	
<?php

$i=0;

while($res = mssql_fetch_array($dbresult)) {
$Direction= $res["Direction"];
$DirectionDescription=$res["DirectionDescription"];

?>
<option value="<?php echo $Direction?>">To <?php echo $DirectionDescription?></option>
<?php

$i++;
}

?>
</select>
<input name="Route" type="hidden" value="<?php echo $Route?>">

<p><input type="submit" / value="NEXT: List Stops >>"></p>

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
