
 <?php
 require_once 'neo4j.php';
 // http://www.geonames.org/childrenJSON?geonameId=3469034 // brazil
 // http://www.geonames.org/childrenJSON?geonameId=6295630 // continents
$file = file_get_contents("http://www.geonames.org/childrenJSON?geonameId=6295630");
$str = json_decode($file,true);
$continent = $str['geonames'];

// for each continet
// execute function to create node continent
for ($i=0; $i <count($continent) ; $i++) {
  createContinentNeo4j($continent[$i]);
  $fileCountry = file_get_contents("http://www.geonames.org/childrenJSON?geonameId=".$continent[$i]["geonameId"]);
  $strC = json_decode($fileCountry,true);
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
    $fileState = file_get_contents("http://www.geonames.org/childrenJSON?geonameId=".$country[$i]["geonameId"]);
    $strS = json_decode($fileState,true);
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
