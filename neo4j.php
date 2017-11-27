<?php
/* exemple php whit neo4j */
require_once 'vendor/autoload.php';

use Neoxygen\NeoClient\ClientBuilder;// add libary Neo4J

$client = ClientBuilder::create() // create connection Neo4j
    ->addConnection('default', 'http', 'localhost', 7474, true, 'wis', 'neo4j') // initial connection username :wis password:neo4j
    ->build();

$version = $client->getNeo4jVersion();
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
   $query2 = 'MATCH (c:Continent),(c2:Country) WHERE c.id = c2.continentId MERGE (c)-[:BelongTo]->(c2)';
   //Cypher to create Relationship (BelongTo) for Continent and Country
   $client->sendCypherQuery($query2);// function to create Node by passing the value of the cypher

}

function createStatesNeo4j($states,$countryID){
  $client = $GLOBALS['client']; // retrieves value from global client variable and saves to a local client variable
   $query = "MERGE (n:State { id: {id}, name: {name},countryId:{countryId} }) RETURN n";
   //cypher to create State Node with id property and name
   $parameters = array('id' => $states["geonameId"], 'name' => $states["name"],'countryId'=>$countryID );
   $client->sendCypherQuery($query, $parameters);// function to create Node by passing the value of the cypher and the parameters

   // execute other query for create RelationShip
   $query2 = 'MATCH (c:Country),(c2:State) WHERE c.id = c2.countryId MERGE (c)-[:BelongTo]->(c2)';
   //Cypher to create Relationship (BelongTo) for State and Country
   $client->sendCypherQuery($query2);// function to create Node by passing the value of the cypher 

}

 ?>
