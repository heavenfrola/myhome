<?PHP

	/* +----------------------------------------------------------------------------------------------+
	' |  KCP PG 공통설정
	' +----------------------------------------------------------------------------------------------+*/

	$g_conf_home_dir	= $engine_dir.'/_engine/card.kcp';
	$g_conf_gw_url		= ($cfg['card_test'] == '_test') ? 'testpaygw.kcp.co.kr' : 'paygw.kcp.co.kr';
	$g_conf_js_url		= 'https://pay.kcp.co.kr/plugin/payplus'.$cfg['card_test'].'.js';
	$g_conf_site_cd		= $cfg['card_site_cd'];
	$g_conf_site_key	= $cfg['card_site_key'];
	$g_conf_site_name	= $cfg['card_site_name'];
	$g_conf_log_level	= '3';
	$g_conf_gw_port		= '8090';

?>