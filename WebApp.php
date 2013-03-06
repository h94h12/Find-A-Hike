<?php
if($_GET['output'] != "json")
{
require_once('header_new.php');
}
require_once('hikefinder.php');
?>

<?php

$values_array = array(); 
$address_value = ''; 
isset($_GET['address']) ? $address_value = $_GET['address'] : $address_value = "";
$distance_value = '';
isset($_GET['distance']) ? $values_array[0] = $_GET['distance'] : $values_array[0] = "any";
$length_value = '';
isset($_GET['length']) ? $values_array[1] = $_GET['length'] : $values_array[1] = "any";
$terrain_value = ''; 
isset($_GET['terrain']) ? $values_array[2] = $_GET['terrain'] : $values_array[2] = "any";
$shade_value = ''; 
isset($_GET['shade']) ? $values_array[3] = $_GET['shade'] : $values_array[3] = "any";
$transport_value = '';
isset($_GET['transport']) ? $values_array[4] = $_GET['transport'] : $values_array[4] = "any";



if($_GET['output'] != "json") //print out html if not json 
{
?>
<h1> Find a Hike Near You </h1>
<form action = "WebApp.php" method = "get">
1. Enter your address or zipcode: <input type="text" name="address" value= " <?php echo($address_value);?>"   />
<br />
2. Enter the maximum distance from you: 
<select name="distance">
<?php
$num = 0; 
$options_array = array("short", "med", "long", "any"); 
$display_array = array("up to 5 miles", "up to 10 miles", "up to 20 miles", "any"); 
$string = dropdown($options_array, $display_array, $values_array, $num); 
$num++; 
echo($string); 
?>
</select>
<br />
3. Length of hike: 
<select name="length">
<?php
$options_array = array("short", "med", "long", "any"); 
$display_array = array("short (0 - 2 mi)", "medium (2 - 5 mi)", "long ( 5+ mi)", "any"); 
$string = dropdown($options_array, $display_array, $values_array, $num); 
$num++; 
echo($string); 
?>
</select>
<br/>
4. Terrain difficulty:
<select name="terrain">
<?php
$options_array = array("easy", "med", "diff", "any"); 
$display_array = array("easy", "medium", "difficult", "any"); 
$string = dropdown($options_array, $display_array, $values_array, $num); 
$num++; 
echo($string); 
?>

</select>
<br/>
5. Amount of shade:
<select name="shade">
<?php
$options_array = array("not shady", "shady", "combo", "any"); 
$display_array = array("sunny (less than 50% shade)", "shady (more than 50% shade) ", "combination(50% shade and 50% sunny)", "any"); 
$string = dropdown($options_array, $display_array, $values_array, $num); 
$num++; 
echo($string); 
?>
</select>
<br/>
6. Preferred baby transport device:
<select name="transport">
<?php
$options_array = array("jogger", "stroller", "carrier", "any"); 
$display_array = array("jogger", "small-wheeled stroller", "carrier", "any"); 
$string = dropdown($options_array, $display_array, $values_array, $num); 
$num++; 
echo($string); 
?>
</select>
<br/>
<input type="submit" name = 'submit' value="Find my hike &#8594;" />
</form>
<br />


<h3>Here are the hikes that match your criteria</h3>


<?php
} //end if


$address = $_GET['address'];
$radius = $_REQUEST['distance'];
$length = $_REQUEST['length'];
$terrain = $_REQUEST['terrain'];
$shade = $_REQUEST['shade'];
$transport = $_REQUEST['transport'];




$coordinatesSplit = callGMaps($address); 
$mylat = $coordinatesSplit[1];                    
$mylng = $coordinatesSplit[0];

//For Yahoo! Small Biz hosting, set host to 'mysql': http://www.indexhibit.org/forum/thread/3296/
$host = "mysql";
$user = "strollerhikes";
$pass = "strollerhikes";
$db = "strollerhikes";


$distance_filter = processRadius($radius); 
$length_filter = processLength($length); 
$terrain_filter = processTerrain($terrain);
$shade_filter = processShade($shade); 
$transport_filter = processTransport($transport); 
 
$sql_query = constructQuery($length_filter, $terrain_filter, $shade_filter, $transport_filter); 


$connection = mysql_connect($host, $user, $pass, $db) or die ("Unable to connect to db: " . mysql_error());
mysql_select_db($db) or die("Unable to select db".$db.":".mysql_error());

if ($address != '')
$radius_query = 'SELECT locations.address, locations.name, hikes.name, hikes.summary, ( 3959 * acos( cos( radians('. $mylat .') ) * cos( radians( locations.lat ) ) * cos( radians( locations.lng ) - 			    radians('.$mylng.') ) + sin( radians('.$mylat.') ) * sin( radians( locations.lat ) ) ) ) AS distance FROM locations, hikes WHERE locations.id = hikes.location_id';

	if($sql_query != '')
	$radius_query .= ' AND '.$sql_query;

	if($distance_filter != '' && $address != '') //enters distance and address
	$radius_query .=  ' HAVING '. $distance_filter . ' ORDER BY distance' ; 

	if($distance_filter == '' && $address != '') //enter address
	$radius_query .=  ' ORDER BY distance' ; 


$result = mysql_query($radius_query) or die ("No criteria selected!");



//output json 
if($_GET["output"] == "json")
{
	if(mysql_num_rows($result) > 0) {
 
 	//$hike_array = array();  need to insert name values
	
	$resultarray = array();  //array of arrays
 
 	while($row = mysql_fetch_row($result)) {
   		
	$hikearray[] = array('loc' => $row[1], 'trail' => $row[2], 'summary' => $row[3], 'dist' => $row[4]);
	
	

	//remember to do something about ampersand 
	
  	}
	header('content-type: application/json; charset=utf-8');
	echo json_encode(array('results' => $hikearray)); 
	
  
  }
}



//output html 
else
{
	if(mysql_num_rows($result) > 0) {
		print("<table>");
		print("<tr>");
		print("<th> Location </th>");
		print("<th> Trail </th>");
		print("<th> Summary </th>");
		if ($address != '')
		print("<th> Distance From You </th>");
		print("</tr>");
		
		
	  while($row = mysql_fetch_row($result)) {
		print("<tr>");
		print('<td><a href="http://www.strollerhikes.com/Hikes/'.$row[0].'/'.$row[0].'.php">'.$row[1].'</a></td>');  //Location name with link
		print("<td>" . $row[2] . "</td>"); //trail
		print("<td>" . $row[3] . "</td>"); //summary
		
		//echo json_encode($row); 
		
		if($address != '')
		print("<td>". round($row[4], 1)." miles </td>"); //distance from you
		print("</tr>");
	  }
	  print("</table>");
	}
	else
	print("Sorry no hikes found! Please try a different search.");





?>



</div>
</div>


<div id="footer">Copyright 2006-2011 StrollerHikes.com</div>

</div>
</body>
</html>

<?php

}


?> 