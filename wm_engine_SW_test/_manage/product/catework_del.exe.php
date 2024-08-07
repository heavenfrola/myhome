<?PHP

	/* +----------------------------------------------------------------------------------------------+
	' |  매장분류 관리
	' +----------------------------------------------------------------------------------------------+*/

	printAjaxHeader();

	$parent = numberOnly($_GET['parent']);
	$cno = $_GET['cno'];
	if(!is_array($cno)) exit();

	// 하위분류 존재 여부
	foreach($cno as $val) {
		$_cate = get_info($tbl['category'], 'no', $val);
		if($_cate['ctype']) $ctype = $_cate['ctype'];

		if($_cate['level'] < 4) {
			$_cname = $_cate_colname[1][$_cate['level']];
			$w = "$_cname='{$_cate['no']}' and level>{$_cate['level']}";
			$sub = $pdo->row("select count(*) from `$tbl[category]` where $w");
			$is_prd = $pdo->row("select count(*) from `$tbl[product]` where `big`='$_cate[no]'");

			if($sub > 0) {
				exit("ERROR- $_cate[name]\n하위 분류가 존재합니다\n하위 분류를 먼저 삭제하세요.");
			}

			if($is_prd > 0) {
				exit("ERROR- $_cate[name]\n해당 분류에 등록된 상품이 있어\n해당 카테고리를 삭제할 수 없습니다.\n※ 상품 휴지통도 확인해주시기 바랍니다.");
			}
		}

		$pdo->query("delete from `$tbl[category]` where `no`='$_cate[no]'");
		if($_cate['ctype'] == '2') {
			$pdo->query("update `".$tbl['product']."` set `ebig`=replace(`ebig`,'@".$val."','')");
		}

		$_cate_nm = 'n'.$_cate_colname[1][$_cate['level']];
		$pdo->query("delete from $tbl[product_link] where ctype='$_cate[ctype]' and $_cate_nm='$_cate[no]'");
	}

	// 변경된 화면 HttpRequest 로 넘김
	$cno = array(0);
	$execmode = 'ajax';
	$no = $parent;
	include $engine_dir."/_manage/product/catework_content.frm.php";

?>