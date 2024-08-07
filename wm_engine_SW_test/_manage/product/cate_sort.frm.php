<?PHP

	$QueryString="";
	foreach($_GET as $key=>$val) { // 2007-11-02 - Han
		if($key == "body" || $key == "abc_sort") continue;
		$QueryString.="&".$key."=".$val;
	}

	$asql="";
	if($big) $asql.=" and `big`='$big'";
	if($mid) $asql.=" and `mid`='$mid'";
    $asql.=" and `level`='$level' and `ctype`='$ctype'";
	$_sort=($abc_sort) ? "`name`" : "`sort`"; // 2007-11-02

	$sql="select * from `$tbl[category]` where 1 $asql order by $_sort ";
	$res = $pdo->iterator($sql);
	$cate_list="";
	$ii=0;
    foreach ($res as $data) {
		if($data['name']=="dummy" && $data['code']=="dummy") {
			$str=$dummy_cate.$dummy_cate.$dummy_cate;
		}
		else {
			$ii++;
			$str="($ii) $data[name]";
		}
		$cate_list.="<option value=\"$data[no]\">$str</option>\n";
	}

	$size=$ii;
	if($size<10) $size=10;

?>
<script language="JavaScript">
	var dummy_cate='<?=$dummy_cate?>';
</script>

<form name="cateSortFrm" method="post" action="<?=$_SERVER['PHP_SELF']?>" target="hidden<?=$now?>">
	<input type="hidden" name="body" value="product@cate_update.exe">
	<input type="hidden" name="big" value="<?=$big?>">
	<input type="hidden" name="mid" value="<?=$mid?>">
	<input type="hidden" name="level" value="<?=$level?>">
	<input type="hidden" name="ctype" value="<?=$ctype?>">
	<input type="hidden" name="QueryString" value="<?=$QueryString?>">
	<input type="hidden" name="new_sort" value="">
	<input type="hidden" name="del_dummy" value="">
	<input type="hidden" name="exec" value="sort">
	<table cellpadding="0" cellspacing="0">
		<caption>매장 순서 변경</caption>
		<tr>
			<td><div class="desc1">해당 기능은 사이트내의 카테고리 정보가 이미지로 처리된 경우에는 적용되지 않을 수 있습니다.</div></td>
		</tr>
		<tr>
			<td>
				<select id="cateList" name="cateList" size="<?=$size?>" style="width:100%">
					<?=$cate_list?>
				</select>
			</td>
		</tr>
	</table>
	<div class="footer">
		<span class="box_btn_s"><input type="button" onclick="sel.move(-1);" value="위로 이동"></span>
		<span class="box_btn_s"><input type="button" onclick="sel.move(1);" value="아래로 이동"></span>
		<span class="box_btn_s"><input type="button" onclick="location.href='./?body=<?=$body?><?=$QueryString?>&abc_sort=1'" value="알파벳순으로"></span>
		<span class="box_btn_s"><input type="button" onclick="location.href='./?body=<?=$body?><?=$QueryString?>'" value="원래대로"></span>
	</div>
	<div class="pcenter">
		<span class="box_btn blue"><input type="button" value="확인" onclick="updatechgSort(document.cateSortFrm);"></span>
	</div>
</form>

<script type="text/javascript" src="<?=$engine_url?>/_engine/common/R2Select.js"></script>
<script type="text/javascript">
	var sel = new R2Select('cateList');
	window.onload=function() {
		selfResize();
	}
</script>