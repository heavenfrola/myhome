<?php

/**
 * 페이스북 전환 API
 **/

use FacebookAds\Api;
use FacebookAds\Logger\CurlLogger;
use FacebookAds\Object\ServerSide\ActionSource;
use FacebookAds\Object\ServerSide\Content;
use FacebookAds\Object\ServerSide\CustomData;
use FacebookAds\Object\ServerSide\DeliveryCategory;
use FacebookAds\Object\ServerSide\Event;
use FacebookAds\Object\ServerSide\EventRequest;
use FacebookAds\Object\ServerSide\UserData;

if ($GLOBALS['scfg']->comp('use_fb_conversion', 'Y') == false) return;

$access_token = $cfg['fb_pixel_conversion'];
$pixel_id = $cfg['fb_pixel_id'];
if (isset($member['email']) == false) $member['email'] = '';
if (isset($member['cell']) == false) $member['cell'] = '';

if (!$access_token) return;
if (!$pixel_id) return;

try
{
    $api = Api::init(null, null, $access_token);
    $api->setLogger(new CurlLogger());

    $user_data = (new UserData())
        ->setEmails(array($member['email']))
        ->setPhones(array($member['cell']))
        ->setClientIpAddress($_SERVER['REMOTE_ADDR'])
        ->setClientUserAgent($_SERVER['HTTP_USER_AGENT']);

    $custom_data = (new CustomData())
        ->setSearchString($_GET['search_str'])
        ->setValue(1);

    $event = (new Event())
        ->setEventName('Search')
        ->setEventTime(time())
        ->setEventSourceUrl(getURL())
        ->setUserData($user_data)
        ->setCustomData($custom_data)
        ->setActionSource(ActionSource::WEBSITE);

    $events = array();
    array_push($events, $event);

    $request = (new EventRequest($pixel_id))
        ->setEvents($events);
    $response = $request->execute();
}
catch(Exception $e)
{}
