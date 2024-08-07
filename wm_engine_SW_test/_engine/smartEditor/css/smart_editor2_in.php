<?PHP

	header('Content-type:text/css; charset=utf8;');
	include $engine_dir.'/_engine/include/common.lib.php';

	$skin = getSkinCfg();

?>
@import url('<?=$skin['url']?>/style.css');

body {
	margin: 10px !important;
}