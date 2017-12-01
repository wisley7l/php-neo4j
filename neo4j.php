<?php
/* exemple php whit neo4j */
require_once 'vendor/autoload.php';
use Neoxygen\NeoClient\ClientBuilder;// add libary Neo4J

$client = ClientBuilder::create() // create connection Neo4j
    ->addConnection('default', 'http', 'localhost', 7474, true, 'wis', 'neo4j') // initial connection username :wis password:neo4j
    ->setAutoFormatResponse(true)
    ->build();
//$version = $client->getNeo4jVersion();
//  echo $version;

//var_dump($response);
function createContinentNeo4j($continent){ // function to create continent in Neo4J
  $client = $GLOBALS['client']; // retrieves value from global client variable and saves to a local client variable
   $query = "MERGE (n:Continent { id: {id}, name: {name} }) RETURN n"; // cypher to create Continent Node with id property and name
   $parameters = array('id' => $continent["geonameId"], 'name' => $continent["name"] ); // parameters to create node
   $client->sendCypherQuery($query, $parameters); // function to create Node by passing the value of the cypher and the parameters

}

function createCountryNeo4j($country,$continentId){ // function to create country in Neo4J
  $client = $GLOBALS['client']; // retrieves value from global client variable and saves to a local client variable
   $query = "MERGE (n:Country { id: {id}, name: {name},continentId:{continentId} }) RETURN n";
   //cypher to create Country Node with id property and name
   $parameters = array('id' => $country["geonameId"], 'name' => $country["name"],'continentId'=>$continentId );
   $client->sendCypherQuery($query, $parameters);// function to create Node by passing the value of the cypher and the parameters
   // execute other query for create RelationShip
   $query2 = 'MATCH (c:Continent),(c2:Country) WHERE c.id = c2.continentId MERGE (c)-[:HasCountry]->(c2)';
   //Cypher to create Relationship (BelongTo) for Continent and Country
   $client->sendCypherQuery($query2);// function to create Node by passing the value of the cypher

}

function createStatesNeo4j($states,$countryID){
  $client = $GLOBALS['client']; // retrieves value from global client variable and saves to a local client variable
   $query = "MERGE (n:State { id: {id}, name: {name},countryId:{countryId} }) RETURN n";
   //cypher to create State Node with id property and name
   $parameters = array('id' => $states["geonameId"], 'name' => $states["toponymName"],'countryId'=>$countryID );
   $client->sendCypherQuery($query, $parameters);// function to create Node by passing the value of the cypher and the parameters

   // execute other query for create RelationShip
   $query2 = 'MATCH (c:Country),(c2:State) WHERE c.id = c2.countryId MERGE (c)-[:HasState]->(c2)';
   //Cypher to create Relationship (BelongTo) for State and Country
   $client->sendCypherQuery($query2);// function to create Node by passing the value of the cypher


}

function getContinentNeo4j(/* $idContinet */){
  $client = $GLOBALS['client']; // retrieves value from global client variable and saves to a local client variable
   $query = 'MATCH (c:Continent) RETURN c ';
   //cypher to ..
  $result =$client->sendCypherQuery($query);// function to create Node by passing the value of the cypher and the parameters
  $publicResult = $result->getBody(); // get public reponse, because $result is protected
  $response = $publicResult["results"][0]["data"];
  // filtering results
  /* exemple of filtering */
  $res = [];
  for ($i=0; $i < count($response) ; $i++) {
    $sname = $response[$i]["row"][0]["name"];
    $sid = $response[$i]["row"][0]['id'];
    //echo $sname."\n".$sid ."\n" ; // print name and id
      array_push($res,array('id' => $sid , 'name' => $sname ));
  }
  return $res; // return result
}

function deleteByIdNeo4j($ID){
  echo "Delete node for id: ".$ID."\n";
  $client = $GLOBALS['client']; // retrieves value from global client variable and saves to a local client variable
  $parameters = array('idC' => $ID ); // parametrs for seach
  //***************** search continent by id, search country by continentId and search a country relationship with states
  $q1 = 'MATCH (c:Continent {id:{idC}}) MATCH (c1:Country {continentId:c.id})-[rs:HasState]->()';
  $d1 = 'DELETE rs'; // delete relationship
  //***************** search continent by id, search country by continentId and search a continent relationship with country
  $q2 = 'MATCH (c:Continent {id:{idC}})-[rc:HasCountry]->()';
  $d2 = 'DELETE rc';// delete relationship
  //***************** search continent by id, search country by continentId and search state by countryId
  $q3 = 'MATCH (c:Continent {id:{idC}}) MATCH (c1:Country {continentId:c.id}) MATCH (s:State {countryId:c1.id})';
  $d3 = 'DELETE s'; // delete state
  //***************** search continent by id and search country by continentId
  $q4 = 'MATCH (c:Continent {id:{idC}}) MATCH (c1:Country {continentId:c.id})';
  $d4 = 'DELETE c1'; // delete country
  //***************** search continent by id
  $q5 = 'MATCH (c:Continent {id:{idC}})';
  $d5 = 'DELETE c'; // delete continent
  //*****************
  $client->sendCypherQuery($q1.$d1, $parameters); // execute query with parametrs
  $client->sendCypherQuery($q2.$d2, $parameters); // execute query with parametrs
  $client->sendCypherQuery($q3.$d3, $parameters); // execute query with parametrs
  $client->sendCypherQuery($q4.$d4, $parameters); // execute query with parametrs
  $client->sendCypherQuery($q5.$d5, $parameters); // execute query with parametrs
  //*****************

}
 ?>
