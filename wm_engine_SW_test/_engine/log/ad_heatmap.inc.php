<?PHP

	/* +----------------------------------------------------------------------------------------------+
	' |  heatmap 삽입 스크립트
	' +----------------------------------------------------------------------------------------------+*/

	switch($GLOBALS['_file_name']) {
		case 'main_index.php' :
			$w .= "and value like '%main/index.php'";
		break;
		case 'shop_order_finish.php' :
			$ord = $GLOBALS['ord'];
			$ord['total_prc'] = numberOnly($ord['total_prc']);

			$subdata = "
			_TRK_PRC = '$ord[total_prc]';
			_TRK_PI = 'ODR';
			";
		break;
		case 'member_join_step3.php' :
			$subdata = "
			_TRK_PI = “RGR”;
			";
		break;
		case 'shop_big_section.php' :
			$cno1 = $GLOBALS['cno1'];
			$w .= " and value like '%shop/big_section.php%' and value like '%cno1=$cno1%'";
		break;
	}

	if($GLOBALS['member']['no'] > 0) {
		$gender = $GLOBALS['member']['sex'] == '남' ? 'M' : 'F';
		$subdata .= "_TRX_SX='$gender';\n";

	}

	if($w) {
		$heatmap = $pdo->assoc("select * from {$GLOBALS['tbl']['default']} where code like 'heatmap_%' and ext='A' $w");
		if($heatmap['code']) {
			$_HM_IDX = str_replace('heatmap_', '', $heatmap['code']);
			$hmdata = "var _HM_IDX = '$_HM_IDX';\n";
		}
	}

?>
<?if($subdata) {?>
<script type="text/javascript">
	<?=$subdata?>
</script>
<?}?>

<!-- HEATMAP(TM) SCRIPT V.1 -->
<script type="text/javascript">
	var _HM_U = "<?=$cfg['logger_heatmap_HM_U']?>";
	var _HM_SCRIPT = (location.protocol == "https:" ? "https://fs.bizspring.net" : "http://fs.bizspring.net") + "/fs4/hm.ms.v1.js";
	document.writeln("<scr"+"ipt type='text/javascript' src='" + _HM_SCRIPT + "'></scr"+"ipt>");
</script>

<script type="text/javascript">
	<?=$hmdata?>
</script>
<?unset($subdata, $w, $ord)?>