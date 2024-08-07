<?PHP

	/* +----------------------------------------------------------------------------------------------+
	' |  매장분류 관리
	' +----------------------------------------------------------------------------------------------+*/

	printAjaxHeader();

	$parent = numberOnly($_GET['parent']);
	$cno = numberOnly($_GET['cno']);
	if (isset($_GET['ctype'])) $ctype = numberOnly($_GET['ctype']);

?>
<form id="searchbar">
	<input type="hidden" name="level" value="<?=$pdata['level']?>">
	<input type="hidden" name="big" value="<?=$pdata['big']?>">
	<input type="hidden" name="mid" value="<?=$pdata['mid']?>">
	<input type="hidden" name="ctype" value="<?=$pdata['ctype']?>">
</form>
<h2 id="cateworkTitle">
	현재위치 : <?=make_tree($parent)?>
</h2>
<form method="post" enctype="multipart/form-data" name="cateFrm" action="/_manage/index.php" target="hide">
	<input type="hidden" name="body" value="product@catework_add.exe">
	<input type="hidden" name="level" value="<?=$level?>">
	<input type="hidden" name="big" value="<?=$big?>">
	<input type="hidden" name="mid" value="<?=$mid?>">
	<input type="hidden" name="ctype" value="<?=$ctype?>">
	<input type="hidden" name="parent" value="<?=$parent_no?>">
	<?PHP

		$wmode = 'modify';
		foreach($cno as $loop => $_cno) {
			$data = $pdo->assoc("select * from `$tbl[category]` where `no` = '$_cno'");
			$sql = "select * from `$tbl[member_group]` where `use_group`='Y' and `no`!='1' order by `no` desc";
			$res = $pdo->iterator($sql);
			include $engine_dir.'/_manage/product/catework_form.inc.php';
		}

	?>
</form>