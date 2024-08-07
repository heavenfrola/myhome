<?PHP

	/* +----------------------------------------------------------------------------------------------+
	' |  카테고리 선택
	' +----------------------------------------------------------------------------------------------+*/

	printAjaxHeader();

	if($_GET['exec'] == 'getCategory') {
		$ctype = numberOnly($_GET['ctype']);
		$_str = '';
		$level = 0;
		for($i = ($cfg['max_cate_depth']-1); $i >= 1; $i--) {
			$colname = $_cate_colname[1][$i];
			$cno = numberOnly($_GET[$colname]);
			$tmp .= "[$i] $cno\n";
			if($cno > 0) {
				$asql = " and `$colname`='$cno'";
				break;
			}
		}
		$level = $i+1;
		$res = $pdo->iterator("select no, name from {$tbl['category']} where ctype='$ctype' and level='$level' $asql order by sort asc");
        foreach ($res as $data) {
			$_no = $data['no'];
			$_name = stripslashes($data['name']);
			$_str .= "<option value='$_no'>$_name</option>";
		}

		header('Content-type:application/json; charset='._BASE_CHARSET_);
		exit(json_encode(array(
			'level' => $level,
			'html' => $_str,
			'temp' => $tmp
		)));
	}

	$datas = explode('@', trim($_GET['datas'], '@'));
	$_cate = array();
	foreach($datas as $val) {
		if(preg_match('/^cate([0-9]+)$/', $val, $tmp)) $_cate[] = $tmp[1];
	}
	$_cate = implode(',', $_cate);
	if($_cate) {
		$idx = 0;
		$res = $pdo->iterator("select * from {$tbl['category']} where no in ($_cate) order by no asc");
        foreach ($res as $cate) {
			$cate[$_cate_colname[$cate['ctype']][1]] = $cate['big'];
			$cate[$_cate_colname[$cate['ctype']][2]] = $cate['mid'];
			$cate[$_cate_colname[$cate['ctype']][3]] = $cate['small'];
			$cate[$_cate_colname[$cate['ctype']][$cate['level']]] = $cate['no'];
			$cate['name'] = makeCategoryName($cate, $cate['ctype']);
			unset($_cname_cache);

			$selected_cate_list .= "
			<tr>
				<td class='left'>{$cate['name']}</td>
				<td><span class='box_btn_s gray'><input type='button' value='삭제' onclick='resetTargetPrd(\"cate\", {$cate['no']})'></span></td>
			</tr>
			";
		}
	}

	if($_GET['exec'] == 'selected') {
		exit($selected_cate_list);
	}

?>
<form id="popupContent" class="popupContent layerPop" style="width:800px;">
	<div id="header" class="popup_hd_line">
		<h1 id="logo"><img src="<?=$engine_url?>/_manage/image/wisa.gif" alt="WISA."></h1>
		<div id="mngTab_pop">상품검색</div>
	</div>
	<div id="popupContentArea">
		<select name="ctype" class="cates"  size="5" style="width:17%; height:100px;">
			<option value="1">일반분류</option>
			<option value="4"><?=$cfg['xbig_name']?></option>
			<option value="5"><?=$cfg['ybig_name']?></option>
		</select>
		<select name="big" class="cates"  size="5" style="width:20%; height:100px;">

		</select>
		<select name="mid" class="cates"  size="5" style="width:20%; height:100px;">

		</select>
		<select name="small"  class="cates"  size="5" style="width:20%; height:100px;">

		</select>
		<select name="depth4"  size="5" style="width:20%; height:100px;">

		</select>

		<div class="center" style="padding:15px">
			<span class="box_btn"><input type="button" value="+ 추가" onclick="setTargetCate(this)"></span>
		</div>

		<table class="tbl_col">
			<caption class="hidden">선택된 상품 분류</caption>
			<colgroup>
				<col>
				<col style="width:15%">
			</colgroup>
			<thead>
				<tr>
					<th>상품분류</th>
					<th>삭제</th>
				</tr>
			</thead>
			<tbody id="selectedCates">
			</tbody>
		</table>
	</div>
	<div class="box_bottom">
		<span class="box_btn blue"><input type="button" value="확인" onclick="targetSelector.close();"></span>
	</div>
	<script type="text/javascript">
	reloadTargetCate();
	$('.cates').change(function(idx) {
		$('.cates:gt('+$(this).index()+')').html('');
		$.get('./?body=design@design_popup_cate_inc.exe', $('#popupContent').serialize()+'&exec=getCategory', function(r) {
			$('.cates').eq(r.level).html(r.html);
		});
	});
	</script>
</form>