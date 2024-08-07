<?PHP
	/* +----------------------------------------------------------------------------------------------+
	' |  주문서 저장
	' +----------------------------------------------------------------------------------------------+*/

	include_once $engine_dir."/_engine/include/common.lib.php";
	include_once $engine_dir."/_engine/include/shop.lib.php";
	include_once $engine_dir."/_engine/include/milage.lib.php";
	include_once $engine_dir.'/_engine/include/wingPos.lib.php';

	checkBasic();
	$ono = $sno = addslashes($_POST['sno']);

	if(!$ono){
		msg('정상적인 접근이 아닙니다.');
	}

	$ord = get_info($tbl['sbscr'], 'sbono', $ono);
    $card = $pdo->assoc("select * from {$tbl['card']} where wm_ono=?", array($ono));

	if(isset($cfg['autobill_pg']) == false || empty($cfg['autobill_pg']) == true) {
		$cfg['autobill_pg'] = 'dacom';
	}
	switch($cfg['autobill_pg']) {
		case 'dacom' : $pg_version = 'XpayAutoBilling/'; break;
		case 'nicepay' : $pg_version = 'autobill/'; break;
	}
    if ($card['pg'] == 'nsp') {
        $cfg['autobill_pg'] = 'naverSimplePay';
        $pg_version = '';
    }

	include_once $engine_dir."/_engine/card.{$cfg['autobill_pg']}/{$pg_version}card_pay.php";

	exit;

?>