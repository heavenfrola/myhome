<?php

/**
 * 페이스북 구매 전환 API
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

// 픽셀 객체 생성
function fbheader()
{
    global $scfg;

    if ($scfg->comp('use_fb_conversion', 'Y') == false) return false;
    if ($scfg->comp('fb_pixel_conversion') == false) return false;
    if ($scfg->comp('fb_pixel_id') == false) return false;

    try {
        $api = Api::init(null, null, $scfg->get('fb_pixel_conversion'));
        $api->setLogger(new CurlLogger());

        return $api;
    } catch (Exception $e) {
        return false;
    }
}

// 주문 완료 구매전환
function fbPurchase($ord)
{
    global $pdo, $scfg, $tbl, $root_url;

    $api = fbheader();
    if (!$api) return false;

    $currency_type = strtolower($scfg->get('currency_type'));
    if ($currency_type == '원') $currency_type = 'krw';
    try {
        $user_data = (new UserData())
            ->setEmails(array($ord['buyer_email']))
            ->setPhones(array($ord['buyer_cell']))
            ->setClientIpAddress($ord['ip'])
            ->setClientUserAgent($_SERVER['HTTP_USER_AGENT']);

        $content = array();
        $res = $pdo->iterator("select pno, buy_ea, total_prc from {$tbl['order_product']} where ono='{$ord['ono']}'");
        foreach ($res as $prd) {
            $content[] = (new Content())
                ->setProductId($prd['pno'])
                ->setQuantity($prd['buy_ea'])
                ->setItemPrice($prd['total_prc'])
                ->setDeliveryCategory(DeliveryCategory::HOME_DELIVERY);
        }

        $custom_data = (new CustomData())
            ->setContents($content)
            ->setCurrency($currency_type)
            ->setOrderId($ord['ono'])
            ->setValue(parsePrice($ord['pay_prc']));

        $event = (new Event())
            ->setEventName('Purchase')
            ->setEventTime(time())
            ->setEventSourceUrl($root_url.'/shop/order_finish.php')
            ->setUserData($user_data)
            ->setCustomData($custom_data)
            ->setActionSource(ActionSource::WEBSITE);

        $events = array();
        array_push($events, $event);

        $request = (new EventRequest($scfg->get('fb_pixel_id')))
            ->setEvents($events);
        $response = $request->execute();
    } catch (Exception $e) {
    }
}