<?PHP

	$level = numberOnly($_GET['level']);
	$ctype = numberOnly($_GET['ctype']);
	$sel = numberOnly($_GET['sel']);
	$big = numberOnly($_GET['big']);
	$mid = numberOnly($_GET['mid']);
	$small = numberOnly($_GET['small']);

	if(!$level) $level=1;
	if(!$ctype) $ctype=1;
	$where="";

	if($level==1 && ($ctype==1 || $ctype==3)) {
		$chg="big='+this.value";
	}
	elseif($level==2 && $ctype==1) {
		$chg="big='+this.form.big.value+'&mid='+this.value";
	}
	if($chg) {
		$nlevel=$level+1;
		$chg_str="onChange=\"parent.frames['".$iframe."cate".$ctype.$nlevel."'].location.href='./?body=product@product_cate.frm&ctype=1&level=".$nlevel."&iframe=".$iframe."&".$chg."\"";
	}

	$cate_name[1]=array('','대분류','중분류','소분류');
	$cate_name[2]=array('',$_ctitle[2]);
	$cate_name[3]=array('','항목','분류');
	$cate_name[4]=array('',$cfg['xbig_name']);
	$cate_name[5]=array('',$cfg['ybig_name']);

	if($prd_type) {
		$w=" and `prd_type`='$prd_type'";
	}

	$sql="select * from `".$tbl['category']."` where `level`='$level' and `ctype`='$ctype' and `big`='$big' and `mid`='$mid' $w order by `sort`";
	$res = $pdo->iterator($sql);

	$str="<select name=\"cate\" style=\"width:140px;\" $chg_str>";
	$str.="<option value=\"\">::".$cate_name[$ctype][$level]."::</option>";
    foreach ($res as $data) {
		$str.="<option value=\"$data[no]\" ".checked($data[no],$sel,1).">".inputText($data[name])."</option>";
	}
	$str.="</select>&nbsp;";

?>
<form name="cateFrm" method="post">
<input type="hidden" name="big" value="<?=$big?>">
<?=$str?>
</form>

<script language="JavaScript">
	window.onload=function() {
		selfResize();
	}
</script>