<?php

// Start a session, load the library
session_start();
require_once('tumblroauth/tumblroauth.php');
require_once('tumblr.inc.php');

$settings = GetSettings();
$settings = $settings['tumblr'];
$consumer_key = $settings['oauth-appkey'];
$consumer_secret = $settings['oauth-appsec'];

if ($consumer_key == NULL || $consumer_secret == NULL)
    die("There is no app key. Please fix this in the settings/settings.ini. The variables are tumblr/oauth-appkey and tumblr/oauth-appsec.");

$tum_oauth = new TumblrOAuth($consumer_key, $consumer_secret, $_SESSION['request_token'], $_SESSION['request_token_secret']);

$access_token = $tum_oauth->getAccessToken($_REQUEST['oauth_verifier']);

unset($_SESSION['request_token']);
unset($_SESSION['request_token_secret']);

// Make sure nothing went wrong.
if (200 == $tum_oauth->http_code) {
    echo "oauth-tokkey: " . $access_token['oauth_token'] . "<br>oauth-toksec: " . $access_token['oauth_token_secret'] . "<br><br>"; //print the access token and secret for later use
} else {
    echo('Unable to authenticate');
}
