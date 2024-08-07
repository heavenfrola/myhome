<?PHP

$cfg['card_web_id'] = $cfg['card_inicis_mobile_id'];
$cfg['iniweb_basic_apikey'] = ($cfg['card_inicis_mobile_id'] == $cfg['card_web_id']) ? $cfg['iniweb_basic_apikey'] : $scfg->get('iniweb_mobile_apikey');
include_once $engine_dir.'/_engine/card.inicis/INIweb/card_cancel.php';