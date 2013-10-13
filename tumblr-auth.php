<?php

session_start();

require_once('tumblroauth/tumblroauth.php');
require_once('tumblr.inc.php');

$settings = GetSettings()['tumblr'];
$consumer_key = $settings['oauth-appkey'];
$consumer_secret = $settings['oauth-appsec'];

if ($consumer_key == NULL || $consumer_secret == NULL)
    die("There is no app key. Please fix this in the settings/settings.ini. The variables are tumblr/oauth-appkey and tumblr/oauth-appsec.");

$tum_oauth = new TumblrOAuth($consumer_key, $consumer_secret);
$request_token = $tum_oauth->getRequestToken($callback_url);
$_SESSION['request_token'] = $token = $request_token['oauth_token'];
$_SESSION['request_token_secret'] = $request_token['oauth_token_secret'];

switch ($tum_oauth->http_code) {
    case 200:
        $url = $tum_oauth->getAuthorizeURL($token);
        header('Location: ' . $url);
        break;
    default:
        echo 'Could not connect to Tumblr. Refresh the page or try again later.';
}
exit();