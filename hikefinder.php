<?php
  
function dropdown($options_array, $display_array, $values_array, $num){ 
    $selected = null; 
	$return = null; 
    for ($i = 0; $i < sizeof($options_array); $i++) 
    { 
        $return .= '<option value="'.$options_array[$i].'"'. 
                   (($values_array[$num] == $options_array[$i]) ? ' selected="selected"' : null ). 
                   '>'.ucfirst($display_array[$i]).'</option>'."\n"; 
		
				   
    } 
    return $return; 
  } 
 
 function processRadius($radius){ //DISTANCE AWAY FROM USER
	  if ($radius == 'short')
		  return ' distance <= 5';
	  else if ($radius == 'med')
		  return ' distance <= 10';
	  else if ($radius == 'long')
		  return ' distance <= 20';
	  else
		  return ''; 
	  
  }
	  
  function processLength($length){//LENGTH OF HIKE
	  if ($length == 'short')
		  return ' distance <= 2';
	  else if ($length == 'med')
		  return ' distance <= 5 AND distance > 2';
	  else if ($length == 'long')
		  return ' distance > 5';
	  else
		  return ''; 
  }
	  
  function processTerrain($terrain){//TERRAIN DIFFICULTY
	  if ($terrain == 'easy')
		  return ' hikes.flat = 1';
	  else if ($terrain == 'med')
		  return ' hikes.rolling = 1';
	  else if ($terrain == 'diff')
		  return ' hikes.steep = 1';
	  else
		  return ''; 
  }
	  
  function processShade($shade){//SHADE
	  if ($shade == 'shady')
		  return '(hikes.shade_50 = 1 || hikes.shade_75 = 1 || hikes.all_shade = 1)';
	  else if ($shade == 'combo')
	  	  return '(hikes.shade_50 = 1)'; 
	  else if ($shade == 'not shady')
		  return '(hikes.shade_50 = 1 || hikes.shade_25 = 1 || hikes.no_shade = 1)';
	  else
		  return ''; 
  }
	  
  function processTransport($transport){//TRANSPORT
	  if ($transport == 'jogger')
		  return  ' hikes.jogger = 1';
	  else if ($transport == 'stroller')
		  return  ' hikes.small_wheeled_stroller = 1';
	  else if ($transport == 'carrier')
		  return  ' hikes.carrier = 1';
	  else
		  return ''; 
  }
	  
  function constructQuery($length_filter, $terrain_filter, $shade_filter, $transport_filter){//CONSTRUCT QUERY STRING
	  $sql_query = ''; 
	  $sql_query .= $length_filter;
	  if($terrain_filter != '' && $length_filter != '') {
		  $sql_query .= ' AND ';
		  $sql_query .= $terrain_filter;
	  }
	  if($shade_filter != '' && $terrain_filter != '') {
		  $sql_query .= ' AND ';
		  $sql_query .= $shade_filter;
	  }
	  if($transport_filter != '' && $shade_filter !='') {
		  $sql_query .= ' AND ';
		  $sql_query .= $transport_filter; 
	  }
	  return $sql_query; 
  }
	  
  function processInput($address, $radius, $length, $terrain, $shade, $transport){
  
	  $distance_filter = processRadius($radius); 
	  $length_filter = processLength($length); 
	  $terrain_filter = processTerrain($terrain); 
	  $shade_filter = processShade($shade); 
	  $transport_filter = processTransport($transport); 
   
	  $sql_query = constructQuery($length_filter, $terrain_filter, $shade_filter, $transport_filter); 
   
  } // end of function processInput 
  

 function callGMaps($address) { //CONNECTING TO GMAPS
  
	  define("MAPS_HOST", "maps.google.com");
	  define("KEY", "ABQIAAAAeZsgL9okPk0KNK5MgSNlOBRQ5aq9k7S4nQzta_MdHpYBQkz9SRR5pMbOJR4m_KhAfeDzgH_WHD4T9A");
   
	  $google_maps_api_url ="http://" . MAPS_HOST . "/maps/geo?output=xml" . "&key=" . KEY . "&q=" . urlencode($address); 
  
	  // use curl to call google maps api
	  $ch = curl_init($google_maps_api_url) or die ("Error in curl init");
	  curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1) or die ("Error in curl setopt RETURNTRANSFER");
	  $xml_string = curl_exec($ch) or die ("Error in curl_exec: " . curl_error($ch));
	  curl_close($ch);
  
	  $xml = simplexml_load_string($xml_string);
	  $status = $xml->Response->Status->code;
	  
	  if (strcmp($status, "200") == 0) {
		  // Successful geocode
		  $coordinates = $xml->Response->Placemark->Point->coordinates;
		  $coordinatesSplit = split(",", $coordinates);
		  // Format: Longitude, Latitude, Altitude
	
		  return $coordinatesSplit; 
	 
		  // $mylat = $coordinatesSplit[1];                     //changed variables to mylat to avoid confusion with table
		  //  $mylng = $coordinatesSplit[0];
	  }	
  }
  
  
  ?>