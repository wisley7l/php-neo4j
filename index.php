
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
function addBd(){
  $str = getUrl("http://www.geonames.org/childrenJSON?geonameId=6295630");
  $continent = $str['geonames'];

  // for each continet
  // execute function to create node continent
  for ($i=0; $i <count($continent) ; $i++) {
    echo "****** ".$continent[$i]['name']." ****** \n";
    createContinentNeo4j($continent[$i]);
    $strC = getUrl("http://www.geonames.org/childrenJSON?geonameId=".$continent[$i]["geonameId"]);
    if (is_array($strC['geonames'])){ // if it is not empty
    $country = $strC['geonames'];
    // execute function to create node country and relationship
    createCountry($country,$continent[$i]["geonameId"]);
    }
  }
}

function createCountry($country,$continentId){ // function to create Node Country
// var country  = all country in the continet
// for each country
// execute function to create node country and relationship in Neo4j
  for ($i=0; $i <count($country) ; $i++) {
    createCountryNeo4j($country[$i],$continentId); //
    echo "--- ".$country[$i]['name']." --- \n";
    $strS  = getUrl("http://www.geonames.org/childrenJSON?geonameId=".$country[$i]["geonameId"]);
    // add condition if there are no states in the country
    if (is_array($strS['geonames'])){ // if it is not empty
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
    echo $states[$i]['toponymName']."\n";
    createStatesNeo4j($states[$i],$countryId); //
  }
}
function deleteNotExists(){
  $all = getContinentNeo4j(/*$idContinet*/); // get all continent with id and name property
  $str = getUrl("http://www.geonames.org/childrenJSON?geonameId=6295630");
  $continent = $str['geonames'];
  // for each continet
  // create an object for comparison
  $res =[]; // array of objects that still exists, comparison array
  for ($i=0; $i <count($continent) ; $i++) {
      $sname = $continent[$i]['name']; //
      $sid = $continent[$i]['geonameId']; //
      array_push($res,array('id' => $sid , 'name' => $sname )); // add id and name in object
  }
  $idDel =[]; // array with id of objects that no longer exist DB
  for ($i=0; $i <count($all) ; $i++) {
    if (in_array($all[$i], $res) === FALSE) { // comparison all (object array in DB) with res (object array that still exists)
      // echo "YES ".$all[$i]["name"]."\n";
      array_push($idDel,$all[$i]['id']); // Add the ID of the object that can no longer exist in the DB
    }
  }
  if(empty($idDel)){ // check if there is an id
    echo "DB already updated"."\n";
  }
  else {
    // for each id, execute the function to delete the NODE
    for ($i=0; $i <count($idDel) ; $i++) {
      deleteByIdNeo4j($idDel[$i]); // execute function to delete NODE in Neo4j
    }
  }
}
//execute function
//addBd();
deleteNotExists();
 ?>
