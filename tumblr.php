<?php

//echo 'a';
//ini_set('display_errors', 1);
//error_reporting(E_ALL);

require_once('tumblroauth/tumblroauth.php');
require_once("tumblr.inc.php");
require_once("feedcreator.class.php");

if (GetSettings()['general']['pwd'] != $_GET['pwd']) {
    header('HTTP/1.1 403 Forbidden');
    exit();
}
$settings = GetSettings()['tumblr'];
$consumer_key = $settings['oauth-appkey'];
$consumer_secret = $settings['oauth-appsec'];
$token_key = $settings['oauth-tokkey'];
$token_secret = $settings['oauth-toksec'];

$rss = new UniversalFeedCreator();
$rss->title = "Tumblr Dashboard";
$rss->description = "All your tumblr stuff as rss.";

//optional
$rss->descriptionTruncSize = 0;
$rss->descriptionHtmlSyndicated = true;
$rss = new UniversalFeedCreator();
//$rss->link = "http://www.dailyphp.net/news";
//$rss->syndicationURL = "http://www.dailyphp.net/" . $_SERVER["PHP_SELF"];

$image = new FeedImage();
$image->title = "Tumblr";
$image->url = "http://assets.tumblr.com/images/apple_touch_icon.png";
$image->link = "http://tumblr.com/";
$image->description = "Feed from Tumblr Dashboard";

//optional
$image->descriptionTruncSize = 500;
$image->descriptionHtmlSyndicated = true;

$rss->image = $image;

if ($consumer_key == NULL || $consumer_secret == NULL) {
    $item = new FeedItem();
    $item->title = "Configuration incomplete.";
    $item->link = "http://rss-collect.phi1010.com/tumblr-auth.php";
    $item->description = "Please add an app id and secret in the settings.ini file.";
    $item->date = date(DATE_RSS, $timestamp);
    $item->source = "http://rss-collect.phi1010.com";
    $item->author = "Tumblr RSS Collector";

    $rss->addItem($item);
    $rss->outputFeed("RSS1.0");
    exit();
}
if ($token_key == NULL || $token_secret == NULL) {
    $item = new FeedItem();
    $item->title = "Missing authorization token.";
    $item->link = "http://rss-collect.phi1010.com/tumblr-auth.php";
    $item->description = "Please add an token id and secret in the settings.ini file.";
    $item->date = date(DATE_RSS, time());
    $item->source = "http://rss-collect.phi1010.com";
    $item->author = "Tumblr RSS Collector";

    $rss->addItem($item);
    $rss->outputFeed("RSS1.0");
    exit();
}

$tum_oauth = new TumblrOAuth($consumer_key, $consumer_secret, $token_key, $token_secret);

//for($offset = 0; $offset < 300; $offset+=20) {
$dashboard = $tum_oauth->get('user/dashboard');

if (200 != $tum_oauth->http_code) {
    $code = $tum_oauth->http_code;
    $item = new FeedItem();
    $item->title = "Unable to get info.";
    $item->link = "http://rss-collect.phi1010.com/tumblr-auth.php";
    $item->description = "HTTP response code was $code.";
    $item->date = date(DATE_RSS, $timestamp);
    $item->source = "http://rss-collect.phi1010.com";
    $item->author = "Tumblr RSS Collector";

    $rss->addItem($item);
    $rss->outputFeed("RSS1.0");
    exit();
}
//$item = new FeedItem();
//$item->title = "DATA";
//$item->link = "http://tumblr.com";
//$item->description = print_r($dashboard, TRUE);
//$item->date = date(DATE_RSS, time());
//$item->source = "http://tumblr.com";
//$item->author = "Tumblr RSS Collector";
//$rss->addItem($item);

foreach ($dashboard->response->posts as $data) {

    $item = new FeedItem();
    $item->link = $data->post_url;
    switch ($data->type) {
        case "photo":
            //Don't take the first if there are more.
            $fallback= array_shift($data->photos);
            $item->title = $data->slug;
            $item->description = $data->caption;
            $tmp = array_map(function($a) {
                        return $a->original_size->width * $a->original_size->height;
                    }, $data->photos);
            asort($tmp);
            $size = array_shift($tmp);
            $tmp = array_filter($data->photos, function($e) {
                        return $e->original_size->width * $e->original_size->height == $size;
                    });
            $item->image = array_rand($tmp)->original_size->url;
            if($item->image ==  NULL)
                $item->image = $fallback->original_size->url;
            break;
        case "video":
            $item->title = $data->slug;
            $item->description = $data->caption;
            $item->image = $data->thumbnail_url;
            break;
        case "quote":
            $item->title = $data->souce;
            $item->description = $data->text;
            break;
        case "link":
            $item->title = $data->title;
            $item->description = $data->url . "<br/>" . $data->description;
            break;
        case "audio":
            $item->title = $data->caption;
            $item->description = $data->artist . "<br/>" . $data->album . "<br/>" . $data->year;
            break;
        case "answer":
            $item->title = $data->asking_name . "<br/>" . $data->question;
            $item->description = $data->answer;
            break;
        default :
        case "text":
        case "chat":
            $item->title = $data->title;
            $item->description = $data->body;
            break;
    }
    $item->descriptionHtmlSyndicated = true;

    $item->date = date(DATE_RSS, $data->timestamp);
    $item->source = "http://" . $data->blog_name . ".tumblr.com";
    $item->author = $data->blog_name;
    $item->guid = $data->id;

    $rss->addItem($item);
}
//}
// valid format strings are: RSS0.91, RSS1.0, RSS2.0, PIE0.1 (deprecated),
// MBOX, OPML, ATOM, ATOM10, ATOM0.3, HTML, JS
$rss->outputFeed("RSS1.0");





