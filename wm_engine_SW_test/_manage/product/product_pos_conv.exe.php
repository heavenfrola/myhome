<?PHP

	/* +----------------------------------------------------------------------------------------------+
	' |  일반상품을 윙POS 상품으로 변환
	' +----------------------------------------------------------------------------------------------+*/

	set_time_limit(0);

	include_once $engine_dir.'/_engine/include/wingPos.lib.php';

	$w = stripslashes($w);
	if($nums && $where == 1) {
		$w = preg_replace('/^@|@$/', '', $nums);
		$w = str_replace('@', ',', $w);
		$w = " and no in ($w)";
	}

	$res = $pdo->iterator("select no, name, ea_type, stat from $tbl[product] where ea_type!=1 and stat in (2,3,4) and wm_sc=0 $w order by no asc");
	$total = $pdo->rowCount($res);
    foreach ($res as $data) {
		createTmpComplexNo($data['no'], 'N');
		$pdo->query("update $tbl[product] set ea_type=1 where no='$data[no]'");
	}

?>
<script type='text/javascript'>
parent.wpStatus("<span style='color:red;'><?=$total?> 개 상품의 처리가 완료되었습니다.</span>", 'process')
</script>\