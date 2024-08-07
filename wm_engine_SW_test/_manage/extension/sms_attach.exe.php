<?php

/**
 * MMS 첨부이미지 업로드
 **/

use Wing\HTTP\CurlConnection;

include_once $engine_dir."/_engine/sms/sms_module.php";
$mms_config = $we_mms->call('getMmsConfig');

$curl = new CurlConnection('http:'.$mms_config[0]->uploader[0], 'POST', array(
    'account_idx' => $_POST['account_idx'],
    'bin' => $_POST['bin'],
    'mode' => $_POST['mode'],
    'return_type' => 'json'
));
$curl->setReferer($root_url);
$curl->exec();

header('Content-type:application/json');
exit($curl->getResult());

?>