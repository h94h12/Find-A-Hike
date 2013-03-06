

<?php
//CONNECTING TO GMAPS
define("MAPS_HOST", "maps.google.com");
define("KEY", "ABQIAAAAeZsgL9okPk0KNK5MgSNlOBRQ5aq9k7S4nQzta_MdHpYBQkz9SRR5pMbOJR4m_KhAfeDzgH_WHD4T9A");
 
 
$google_maps_api_url ="http://" . MAPS_HOST . "/maps/geo?output=xml" . "&key=" . KEY . "&q=" . urlencode($address); 

//print("<a href='" . $google_maps_api_url . "'>URL = " . $google_maps_api_url . "</a><br>");

// use curl to call google maps api
$ch = curl_init($google_maps_api_url) or die ("Error in curl init");
curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1) or die ("Error in curl setopt RETURNTR\
ANSFER");
$xml_string = curl_exec($ch) or die ("Error in curl_exec: " . curl_error($ch));
curl_close($ch);
//print(htmlentities($xml_string));

$xml = simplexml_load_string($xml_string);

$status = $xml->Response->Status->code;
if (strcmp($status, "200") == 0) {
    // Successful geocode
    $coordinates = $xml->Response->Placemark->Point->coordinates;
    $coordinatesSplit = split(",", $coordinates);
    // Format: Longitude, Latitude, Altitude
    $mylat = $coordinatesSplit[1];                     //changed variables to mylat to avoid confusion with table
    $mylng = $coordinatesSplit[0];

   // print("\n\n Lat = " . $mylat . ", " . "Lng = " . $mylng);
}





//For Yahoo! Small Biz hosting, set host to 'mysql': http://www.indexhibit.org/forum/thread/3296/
$host = "mysql";
$user = "strollerhikes";
$pass = "strollerhikes";
$db = "strollerhikes";

$connection = mysql_connect($host, $user, $pass, $db) or die ("Unable to connect to db: " . mysql_error());
mysql_select_db($db) or die("Unable to select db".$db.":".mysql_error());

$radius_query = 'SELECT locations.address, locations.name, hikes.name, hikes.summary, ( 3959 * acos( cos( radians('. $mylat .') ) * cos( radians( locations.lat ) ) * cos( radians( locations.lng ) - 			    radians('.$mylng.') ) + sin( radians('.$mylat.') ) * sin( radians( locations.lat ) ) ) ) AS distance FROM locations, hikes WHERE locations.id = hikes.location_id';

	if($sql_query != '')
	$radius_query .= ' AND '.$sql_query;

	if($distance_filter != '' && $address != '') //enters distance and address
	$radius_query .=  ' HAVING '. $distance_filter . ' ORDER BY distance' ; 

	if($distance_filter == '' && $address != '') //enter address
	$radius_query .=  ' ORDER BY distance' ; 

//print($radius_query);
$result = mysql_query($radius_query) or die ("No criteria selected!");

?>


