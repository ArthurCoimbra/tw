<?php
/**
 * Created by PhpStorm.
 * User: Jerez
 * Date: 1/6/2016
 * Time: 2:42 AM
 */
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

//Path to autoload.php from current location
require_once '../vendor/abraham/twitteroauth/autoload.php';
use Abraham\TwitterOAuth\TwitterOAuth;

session_start();

define('CONSUMER_KEY', 'gIzJEq3F92MWnMdFA2lcX7MWT');
define('CONSUMER_SECRET', '9H6V0Vh2uvri3NKI3VUd7WgCDbvnPYdmfjMP7YhniyoEymjz07');
define('OAUTH_CALLBACK', 'http://localhost/tw/auth/twitter-finish.php');


$connection = new TwitterOAuth(CONSUMER_KEY, CONSUMER_SECRET, $_REQUEST['oauth_token'], $_REQUEST['oauth_token_secret']);
$twitterUser = $connection->get("account/verify_credentials");

$media1 = $connection->upload('media/upload', ['media' => $_REQUEST['image']]);
$parameters = [
    'status' => $_REQUEST['status'],
    'media_ids' => implode(',', [$media1->media_id_string]),
];
$result = $connection->post('statuses/update', $parameters);
echo json_encode($result);