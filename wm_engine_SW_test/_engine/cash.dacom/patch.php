<?PHP

	$configPath = $engine_dir.'/_engine/cash.dacom';

    $CST_PLATFORM   = $HTTP_POST_VARS['CST_PLATFORM'];
    $CST_MID        = $HTTP_POST_VARS['CST_MID'];
    $LGD_MID        = (('test' == $CST_PLATFORM)?'t':'').$CST_MID;

    if( $CST_PLATFORM == null || $CST_PLATFORM == '' ) {
        echo '[TX_PING error] 파라미터 누락<br />';
        return;
    }

    if($LGD_MID == null || $LGD_MID == '') {
        echo '[TX_PING error] 파라미터 누락<br />';
        return;
    }

    require_once($configPath.'/XPayClient.php');
    $xpay = new XPayClient($configPath, $CST_PLATFORM);
    $xpay->Init_TX($LGD_MID);

    echo 'patch result = '.$xpay->Patch('lgdacom.conf');

?>