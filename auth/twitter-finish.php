<?php
/**
 * Created by PhpStorm.
 * User: Jerez
 * Date: 1/6/2016
 * Time: 2:42 AM
 */
//ini_set('display_errors', 1);
//ini_set('display_startup_errors', 1);
//error_reporting(E_ALL);

//Path to autoload.php from current location
require_once '../vendor/abraham/twitteroauth/autoload.php';
use Abraham\TwitterOAuth\TwitterOAuth;

session_start();

$request_body = file_get_contents('php://input');
$data = json_decode($request_body);

define('CONSUMER_KEY', 'gIzJEq3F92MWnMdFA2lcX7MWT');
define('CONSUMER_SECRET', '9H6V0Vh2uvri3NKI3VUd7WgCDbvnPYdmfjMP7YhniyoEymjz07');
define('OAUTH_CALLBACK', 'http://localhost/tw/auth/twitter-finish.php');

$request_token = [];
$request_token['oauth_token'] = $_SESSION['oauth_token'];
$request_token['oauth_token_secret'] = $_SESSION['oauth_token_secret'];

if (isset($data->oauth_token) && $request_token['oauth_token'] !== $data->oauth_token) {
    // Abort! Something is wrong.
    echo 'Abort! Something is wrong.';
    print_r($data);
    print_r($_SESSION);
    print_r($request_token);
    session_unset();
    exit;
}
$connection = new TwitterOAuth(CONSUMER_KEY, CONSUMER_SECRET, $request_token['oauth_token'], $request_token['oauth_token_secret']);

$access_token = $connection->oauth("oauth/access_token", array("oauth_verifier" => $_REQUEST['oauth_verifier']));
$_SESSION['access_token'] = $access_token;

echo '<script>window.opener.twit = '.json_encode($access_token).';window.close();</script>';
exit();