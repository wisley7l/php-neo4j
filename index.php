
 <?php
// reference https://stackoverflow.com/questions/16700960/how-to-use-curl-to-get-json-data-and-decode-the-data

function getUrl($url){
  //  Initiate curl
$ch = curl_init();
// Disable SSL verification
curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
// Will return the response, if false it print the response
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
// Set the url
curl_setopt($ch, CURLOPT_URL,$url);
// Execute
$result=curl_exec($ch);
// Closing
curl_close($ch);

// Will dump a beauty json :3
$varRes = json_decode($result, true);
return $varRes;
}

 require_once 'neo4j.php';
 // http://www.geonames.org/childrenJSON?geonameId=3469034 // brazil
 // http://www.geonames.org/childrenJSON?geonameId=6295630 // continents

$str = getUrl("http://www.geonames.org/childrenJSON?geonameId=6295630");
$continent = $str['geonames'];

// for each continet
// execute function to create node continent
for ($i=0; $i <count($continent) ; $i++) {
  createContinentNeo4j($continent[$i]);
  $strC = getUrl("http://www.geonames.org/childrenJSON?geonameId=".$continent[$i]["geonameId"]);
  if ($strC['geonames'] !== array()){ // if it is not empty
  $country = $strC['geonames'];
  // execute function to create node country and relationship
  createCountry($country,$continent[$i]["geonameId"]);
  }
}

function createCountry($country,$continentId){ // function to create Node Country
// var country  = all country in the continet
// for each country
// execute function to create node country and relationship in Neo4j
  for ($i=0; $i <count($country) ; $i++) {
    createCountryNeo4j($country[$i],$continentId); //
    $strS  = getUrl("http://www.geonames.org/childrenJSON?geonameId=".$country[$i]["geonameId"]);
    // add condition if there are no states in the country
    if ($strS['geonames'] !== array()){ // if it is not empty
      $state = $strS['geonames'];
      // execute function to create node country and relationship
      createStates($state,$country[$i]["geonameId"]);
    }


  }
}
function createStates($states,$countryId){ // function to create Node Sates
// var states = all states in the country
// for each states
// execute function to create node states and relationship in Neo4j
  for ($i=0; $i <count($states) ; $i++) {
    //echo $states[$i]["countryId"]."\n";
    createStatesNeo4j($states[$i],$countryId); //
  }
}
 ?>
