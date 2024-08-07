<?PHP

	/* +----------------------------------------------------------------------------------------------+
	' |  매장분류 관리
	' +----------------------------------------------------------------------------------------------+*/

	addField($tbl['category'], 'small', 'int(4) not null default 0 after mid');
	$pdo->query("alter table $tbl[category] change level level char(1) not null default '1' comment '분류 레벨'");

	$ctype = (int) $_GET['ctype'];
	$no = (int) $_GET['no'];
	if(!$ctype) $ctype = 1;
	if($no > 0) {
		$_cate = $pdo->assoc("select `no` from `$tbl[category]` where `ctype`='$ctype' and no='$no'");
		if(!$_cate) msg ("[$no] 존재하지 않거나, 삭제된 카테고리입니다.");
	} else {
		$no = 0;
	}

	$parent_no = $no;

	switch($ctype) {
		case '1' : $ctype_name = '기본 매장'; break;
		case '2' : $ctype_name = '기획전 '; break;
		case '3' : $ctype_name = '정보고시 '; break;
		case '4' : $ctype_name = $cfg['xbig_name'].' '; break;
		case '5' : $ctype_name = $cfg['ybig_name'].' '; break;
		case '6' : $ctype_name = '모바일 기획전'; break;
	}

?>
<script type="text/javascript" src="<?=$engine_url?>/_manage/category.js"></script>
<script type="text/javascript" src="<?=$engine_url?>/_engine/R2Na/R2Na.js"></script>
<script type="text/javascript" src="<?=$engine_url?>/_engine/common/R2Select.js"></script>
<script type="text/javascript">
	var neko_mode = "<?=$cfg['cate_neko']?>";
</script>
<div class="box_title first">
	<h2 class="title"><?=$ctype_name?>분류관리</h2>
</div>
<div class="frame_cate">
	<div id="category_left">
		<?php if ($ctype != 3) { ?>
		<div id="category_all">
			<span class="box_btn_s gray"><input type="button" value="모두 열기" onclick="categoryAll(1)"></span>
			<span class="box_btn_s gray"><input type="button" value="모두 닫기" onclick="categoryAll(2)"></span>
		</div>
		<?php } ?>
		<div id="category_tree">
			<?php print_cat(); ?>
		</div>
	</div>
	<div id="categoryContent">
		<?php include 'catework_content.frm.php'; ?>
	</div>
	<div class="clear"></div>
</div>
<iframe name="hide" height="0" width="0" style="display:none"></iframe>

<script type="text/javascript">
	var ctype = '<?=$ctype?>';
	document.onkeydown = function() {
		return moveParent();
	}
	<?php if ($moveCateNum) echo "moveCat($moveCateNum);"; ?>
</script>