<?php
require_once 'vendor/autoload.php';

//Includes
require_once 'config.php';

$lastModifiedDate = '01/02/2015';
$etag = strtotime( $lastModifiedDate );

$db = new mysqli( $settings['host'], $settings['username'], $settings['password'], $settings['database'] );
if( !$db ) {
    die( $db->error );
}

$app = new \Slim\Slim();

$app->get( '/', function() use ( $app ) {
    $app->redirect( "https://github.com/sean111/iwapi/blob/master/README.md" );
} );

$app->get( '/cards', function () use ( $etag ) {
    $tag = 'cards-' . $etag;
    $body = getCardList();
    sendResponse( $body, $tag );
} );

$app->get( '/factions', function() use ( $etag ) {
    $tag = 'factions-' . $etag;
    $body = getFactionList();
    sendResponse( $body, $tag );
} );

$app->get( '/card/name/:name', function( $name ) use ( $etag ) {
    //Search by card name
    $tag = 'name-' . $name . $etag;
    $body = searchCards( $name, 'name');
    sendResponse( $body, $tag );
} );

$app->get( '/card/set/:set', function( $set ) use ( $etag ) {
    $tag = 'set-' . $set . $etag;
    $body = searchCards( $set, 'card_set' );
    sendResponse( $body, $tag );
} );

$app->get( '/card/faction/:faction+', function( $factions ) use ( $etag ) {
    $tag = 'factions-' . implode( '-', $factions ) . $etag;
    $body = searchFactions( $factions );
    sendResponse( $body, $tag );
} );

$app->get( '/card/:key+', function ( $key ) use ( $etag ) {
    //Search by card key
    $tag = 'key-' . $key . $etag;
    $body = getCardByKey( $key, 'key' );
    sendResponse( $body, $tag );
} );


$app->run();


function getCardList() {
    global $db;
    $data = array();
    $query = $db->query( "SELECT * FROM cards");
    while( $row = $query->fetch_array( MYSQL_ASSOC ) ) {
        $data[] = $row;
    }
    return json_encode( $data );
}

function getFactionList() {
    global $db;
    $query = "SELECT DISTINCT faction FROM cards ORDER BY faction ASC";
    $query = $db->query( $query );
    while( $row = $query->fetch_array( MYSQL_ASSOC ) ) {
        $data[] = $row['faction'];
    }
    return json_encode( $data );
}

function getCardByKey( $key ) {
    global $db;
    $query = array();
    $data = array();
    for( $x = 0; $x < sizeof( $key ); $x++ ) {
        $query[] = "SELECT * FROM cards WHERE `key` = '" . $db->real_escape_string( $key[$x] ) . "'";
    }
    $query = implode( "; ", $query );
    //var_dump( $query );

    $cnt = 0;
    if( $db->multi_query ( $query ) ) {
        do {
            if( $result = $db->store_result() ) {
                $data[] = $result->fetch_array( MYSQL_ASSOC );
                $result->free();
            }
        } while( $db->more_results() && $db->next_result() );
    }
    return json_encode( $data );
}

function searchCards( $search, $field ) {
    global $db;
    $data = array();
    $query = "SELECT * FROM cards WHERE `$field` LIKE '%" . $db->real_escape_string( $search ) . "%'";
    $query = $db->query( $query );
    while( $row = $query->fetch_array( MYSQL_ASSOC ) ) {
        $data[] = $row;
    }
    return json_encode( $data );
}

function searchFactions( $factions ) {
    global $db;
    $tempArray = implode( "','", escapeArray( $factions ) );
    $query = "SELECT * FROM cards WHERE `faction` IN ('" . $tempArray . "') OR `second_faction` IN ('" . $tempArray . "') OR `third_faction` IN ('" . $tempArray ."')";
    $query = $db->query( $query );
    $data = array();
    while( $row = $query->fetch_array( MYSQL_ASSOC ) ) {
        $data[] = $row;
    }
    return json_encode( $data );
}

function sendResponse( $data, $tag ) {
    global $app;
    $app->etag( $tag );
    $app->expires('+1 month');
    $app->response->headers->set( 'Content-Type', 'application/json' );
    $app->response->setBody( $data );
}

function escapeArray( $array ) {
    global $db;
    for( $x = 0; $x < sizeof( $array ); $x++ ) {
        $array[$x] = $db->real_escape_string( $array[$x] );
    }
    return $array;
}
