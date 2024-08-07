<?PHP

/**
 * 토스페이먼트 윙스토어 가입 연동
 **/

require_once $engine_dir.'/_engine/include/common.lib.php';

$wec = new weagleEyeClient($_we, 'account');
$keycode = $wec->call('getKeyCodeByAPI');
if($keycode != $_GET['keycode']) {
    exit('Wrong keycode');
}

if ($exec == 'reset') {
    $scfg->remove('card_dacom_id');
    $scfg->remove('card_dacom_key');
    $scfg->remove('card_mobile_dacom_id');
    $scfg->remove('card_mobile_dacom_key');
    exit;
}

// PC 설정
unset($_POST);
$_POST['config_code'] = 'card_pg';
$_POST['card_pg'] = 'dacom';
$_POST['pg_version'] = 'XpayNon';
$_POST['card_dacom_id'] = $_GET['mid'];
$_POST['card_dacom_key'] = $_GET['mertkey'];
$_POST['card_test'] = 'N';
$_POST['pay_type_1'] = 'Y';
$_POST['mobile_pg_use'] = 'Y';

$no_reload_config = true;
include $engine_dir.'/_manage/config/config.exe.php';

// 모바일 설정
unset($_POST);
$_POST['config_code'] = 'card_pg';
$_POST['card_mobile_pg'] = 'dacom';
$_POST['pg_mobile_version'] = 'smartXpaySubmit';
$_POST['card_mobile_dacom_id'] = $_GET['mid'];
$_POST['card_mobile_dacom_key'] = $_GET['mertkey'];
$_POST['card_mobile_test'] = 'N';

$no_reload_config = true;
include $engine_dir.'/_manage/config/config.exe.php';

exit('OK');