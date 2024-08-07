<?PHP

	/* +----------------------------------------------------------------------------------------------+
	' |  매장분류 관리
	' +----------------------------------------------------------------------------------------------+*/

	printAjaxHeader();

	// 현재분류 정보 로딩
	if(isset($_GET['no'])) $no = numberOnly($_GET['no']);
	if(isset($_GET['ctype'])) $ctype = numberOnly($_GET['ctype']);
	$parent_no = $no;

	$data = $pdo->assoc("select * from `$tbl[category]` where `no` = '$parent_no'");
	$big = $data['big'];
	$mid = $data['mid'];
	$small = $data['small'];
	$level = $data['level'];

	$pcode = getcatecode($level);
	if($level < 4 && $pcode) $psearch = " and `$pcode` = '$no'";

	$sql = $pdo->iterator("select * from `$tbl[category]` where `ctype`='$ctype' and `level`='$level'+1 $psearch order by `sort`");
    foreach ($sql as $sdata) {
		$name = stripslashes($sdata['name']);

		$lists .= "
		<tr>
			<td><input type='checkbox' name='cno[]' value='$sdata[no]'></td>
			<td class='left'>
				<img src='$engine_url/_manage/image/icon/ic_folder_c.gif'>
				<a href='javascript:moveCat($sdata[no])'>$name</a>
			</td>
			<td>
				<span class=\"box_btn_s\"><a href=\"javascript:controlByajex('product@catework_mod.frm&parent=$parent_no&cno[]=$sdata[no]&ctype=$ctype')\" hidefocus>수정</a></span>
				<span class=\"box_btn_s\"><a href=\"javascript:controlByajex('product@catework_del.exe&parent=$parent_no&cno[]=$sdata[no]',$parent_no,$sdata[no])\" onclick=\"return confirm('삭제하시겠습니까')\">삭제</a></span>
			</td>
		</tr>
		";
	}

	// 접근 가능 회원 리스트
	if($no) {
		$res = $pdo->iterator("select * from `$tbl[member_group]` where `use_group`='Y' and `no`!='1' order by `no` desc");
	}

?>
<?php if ($ctype != 3) { ?>
<h3 id="cateworkTitle"><?=make_tree($no)?></h3>
<?php } ?>
<form method="post" enctype="multipart/form-data" name="cateFrm" action="/_manage/index.php" target="hide">
	<input type="hidden" name="body" value="product@catework_add.exe">
	<input type="hidden" name="level" value="<?=$level?>">
	<input type="hidden" name="big" value="<?=$big?>">
	<input type="hidden" name="mid" value="<?=$mid?>">
	<input type="hidden" name="small" value="<?=$small?>">
	<input type="hidden" name="ctype" value="<?=$ctype?>">
	<input type="hidden" name="parent" value="<?=$parent_no?>">
	<?php
		if($no) {
			$loop = 0;
			$cno[0] = $data['no'];
			include $engine_dir."/_manage/product/catework_form.inc.php";
		}
	?>
</form>

<?php if (($ctype != 2 && $ctype != 6 && $ctype != 3 && $level < $cfg['max_cate_depth']) || !$level || ($ctype == 9 && $level < 4)) { ?>
<form method="post" id="searchbar" action="/_manage/index.php" target="hide">
	<input type="hidden" name="body" value="product@catework_add.exe">
	<input type="hidden" name="level" value="<?=$level?>">
	<input type="hidden" name="big" value="<?=$big?>">
	<input type="hidden" name="mid" value="<?=$mid?>">
	<input type="hidden" name="ctype" value="<?=$ctype?>">
	<input type="hidden" name="parent" value="<?=$parent_no?>">

	<div class="cateworkPad">
		신규 분류 생성
		<input type="text" name="name[]" size="40" class="input" title="분류 이름을 입력 해 주십시오" onfocus="onInput=1" onblur="onInput=0">
		<span class="box_btn_s blue"><input type="submit" value="생성"></span>
	</div>
</form>
<form id="cat_list" name="catFRM" target="hide">
	<input type="hidden" name="parent" value="<?=$parent_no?>">
	<input type="hidden" name="destination">
	<table class="tbl_col">
		<colgroup>
			<col style="width:50px">
			<col>
			<col style="width:140px">
		</colgroup>
		<thead>
			<tr>
				<th><input type="checkbox" onClick="ckall('cno[]',this.checked)" ></th>
				<th scope="row">이름</th>
				<th scope="row">관리</th>
			</tr>
		</thead>
		<tbody>
			<?=$lists?>
		</tbody>
	</table>
	<div class="bottom_btn">
		<span class="box_btn_s blue"><input type="button" value="선택수정" onclick="modifyItems(<?=$parent_no?>)"></span>
		<span class="box_btn_s gray"><input type="button" value="선택삭제" onclick="deleteItems(<?=$parent_no?>,1)"></span>
		<span class="box_btn_s"><input type="button" value="카테고리 순서변경" onclick="controlByajex('product@catework_order.frm&parent=<?=$parent_no?>')"></span>
		<span class="box_btn_s"><input type="button" value="알파벳순 정렬" onclick="controlByajex('product@catework_order.frm&order=name&parent=<?=$parent_no?>')"></span>
	</div>
</form>
<?php } ?>