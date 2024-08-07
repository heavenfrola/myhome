<?PHP

	$_line = getModuleContent('common_product_selected_list');

	$pno = preg_replace('/[^0-9,]/', '', trim($_POST['pno'], ','));
	$w = $_skin['common_product_selected_list_w'] ? $_skin['common_product_selected_list_w'] : 100;
	$h = $_skin['common_product_selected_list_h'] ? $_skin['common_product_selected_list_h'] : 100;

	$_tmp = '';
	if($pno) {
		$pno = explode(',' , $pno);
		foreach($pno as $_pno) {
			$data = $pdo->assoc("select * from $tbl[product] where stat in (2,3) and no=$_pno");
			if($cfg['use_prd_perm'] == 'Y' && ($data['perm_list'] == 'N' || $data['perm_sch'] == 'N')) {
				continue;
			}
			$data = prdOneData($data, $w, $h, 3);
			$data['no'] = $_pno;
			$data['remove_link'] = "<a href='#' onclick='refProductRemove($data[no]); return false;'>";
			$_tmp .= lineValues("common_product_selected_list", $_line, $data);
		}
	}
	$_tmp = listContentSetting($_tmp, $_line);
	$_replace_code[$_file_name]['common_product_selected_list'] = $_tmp;

?>