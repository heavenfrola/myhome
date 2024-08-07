<?PHP

	/* +----------------------------------------------------------------------------------------------+
	' |  매장분류 관리
	' +----------------------------------------------------------------------------------------------+*/

	printAjaxHeader();

	$ctype = numberOnly($_REQUEST['ctype']);
	$parent = numberOnly($_REQUEST['parent']);
	$order = addslashes($_REQUEST['order']);
	if(!$order) $order = "sort";

	$pdata = $pdo->assoc("select `level`,`big`,`mid`,`ctype` from `$tbl[category]` where `no`='$parent'");
	$pcode = getcatecode($pdata['level']);
	if($pcode) $psearch = " and `$pcode` = '$parent'";

	$list = '';
	$sql = $pdo->iterator("select `no`,`name` from `$tbl[category]` where `level`='$pdata[level]'+1 and `ctype`='$ctype' $psearch order by `$order`");
    foreach ($sql as $data) {
		$name = stripslashes($data['name']);
		$item .= "<option value='$data[no]'>$name</option>\n";
		$list .= "@$data[no]";
	}

	ob_start();

?>
<form id="searchbar">
	<input type="hidden" name="level" value="<?=$pdata['level']?>">
	<input type="hidden" name="big" value="<?=$pdata['big']?>">
	<input type="hidden" name="mid" value="<?=$pdata['mid']?>">
	<input type="hidden" name="ctype" value="<?=$pdata['ctype']?>">
</form>
<h3 id="cateworkTitle">카테고리 정렬</h3>
<div class="cateworkPad">현재 위치 :: <?=make_tree($parent)?></div>
<form id="ordFrm" name="ordFrm" method="post" action="/_manage/index.php" target="hide">
	<input type="hidden" name="body" value="product@catework_add.exe">
	<input type="hidden" name="wmode" value="sort">
	<input type="hidden" name="cat_list" value="<?=$list?>">
	<input type="hidden" name="parent" value="<?=$parent?>">
	<div style="padding:10px;">
		<select id="sel" name="order" size="20" class="select_n" style="width:100%">
			 <?=$item?>
		</select>
	</div>
	<div style="padding:10px;">
		<span class="box_btn_s icon up"><input type="button" onclick="new R2Select('sel').move(-1);" value="위로"></span>
		<span class="box_btn_s icon down"><input type="button" onclick="new R2Select('sel').move(1);" value="아래로"></span>
	</div>
	<div class="bottom_btn center">
		<span class="box_btn blue"><input type="button" onclick="catework_order()" value="저장하기"></span>
		<span class="box_btn"><input type="button" onclick="moveCat(<?=$parent?>)" value="리스트"></span>
	</div>
</form>
<?php
	$skin = ob_get_contents();
	ob_end_clean();

	exit($skin);

?>