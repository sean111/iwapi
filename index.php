<?php
ini_set( 'display_errors', 0 );
require_once 'vendor/autoload.php';

$lastModifiedDate = '04/07/2015';
$etag = strtotime( $lastModifiedDate );

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
    $tag = 'key-' . implode( '-', $key ) . $etag;
    $body = getCardByKey( $key, 'key' );
    sendResponse( $body, $tag );
} );


$app->run();


function getCardList() {
    $cards = Card::all();
    return $cards->toJson();
}

function getFactionList() {
    $data = Card::distinct()->select( 'faction' )->get();
    return  $data->toJson();
}

function getCardByKey( $key ) {
    $data = Card::whereIn( 'key', $key )->get();
    return $data->toJson();
}

function searchCards( $search, $field ) {
    $data = Card::where($field, 'LIKE', "%$search%")->get();
    return $data->toJson();
}

function searchFactions( $factions ) {
    $data = Card::whereIn( 'faction', $factions )->orWhereIn( 'second_faction', $factions )->orWhereIn( 'third_faction', $factions )->get();
    return $data->toJson();
}

function sendResponse( $data, $tag ) {
    global $app;
    $app->etag( $tag );
    $app->expires('+1 month');
    $app->response()->header( 'Content-Type', 'application/json' );
    $app->response()->setBody( $data );
}
