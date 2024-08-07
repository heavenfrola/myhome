<?PHP

	if(!$config) {
		printAjaxheader();

		$db = $data['db'] = addslashes($_GET['db']);
		$config = $pdo->assoc("select * from `mari_config` where `db` = '$db'");
	}

	if(!$config['use_cate']) return;

	$cates = '';
	$cate_list = array();
	if($config['use_cate'] == 'Y') {
		$csql = $pdo->iterator("select * from `mari_cate` where `db` = '$data[db]' order by `sort` asc");
        foreach ($csql as $cdata) {
			$sel = $cdata['no'] == $data['cate'] ? 'selected' : '';
			$cname = stripslashes($cdata['name']);
			$cates .= "<option value='$cdata[no]' $sel>$cname</option>\n";
			$cate_list[$cdata['no']] = $cname;
		}
	}
	$cnt = count($cate_list);
	if($cnt == 0) {
		return;
		$cate_list[''] = '설정된 분류가 없습니다.';
		$cates .= '<option value="">설정된 분류가 없습니다.</option>';
	}

	if(!$cates) return;

	ob_start();

?>
<tr class="tr_cate">
	<th scope="row">분류</th>
	<td colspan="2">
		<select name='cate'>
			<?=$cates?>
		</select>
	</td>
</tr>
<?PHP

	$html = ob_get_clean();

	if($_GET['from_ajax'] == true) {
		header('Content-type:application/json; charset=utf-8;');
		exit(json_encode(array(
			'count' => $cnt,
			'html' => $html
		)));
	}

	echo $html;

?>