<?PHP

	if(fieldExist($tbl['order'],"prd_nums")) {
		$chk=sql_row("select count(`no`) from `".$tbl['order']."` where `prd_nums`=''");
		if($chk>0) {
			$no_set_ps=1;
		}
	}
	else {
		$no_set_ps=2;
	}

	if($no_set_ps) {
?>
<font class="help">주문상품검색을 사용하시려면 주문상품 업데이트를 하셔야합니다 </font><input type="button" value="주문상품 업데이트" class="btn2" onClick="prdSearchSet()">
<script language="JavaScript">
<!--
window.onload=function() {
	selfResize();
}
function prdSearchSet(){
	window.open('<?=$root_url?>/_manage/?body=order@product_search_set.frm','pss_pss','top=10,left=10,status=no,toolbars=no,scrollbars=yes,height=120,width=530');
}
//-->
</script>
<?
			exit();
	}

	if($prd_no) {
		$data=get_info($tbl[product],"no",$prd_no);
		if($data[no]) {

?>
<?=stripslashes($data[name])?> <input type="button" value="주문상품 재검색" class="btn2" onClick="location.href='<?=$root_url?>/_manage/?body=order@product_search.frm'">
<script language="JavaScript">
<!--
window.onload=function() {
	selfResize();
}
//-->
</script>
<?
			exit();
		}
	}

	$w="";

?>
<table width="620" border=0 cellspacing=3 cellpadding=1 bgcolor="#E0E0E0">
	<tr>
		<td class="bcol2" colspan="20">



<?
	include_once $engine_dir."/_manage/product/product_search.inc.php";

if($exec=="search" && ($big || $search_str)) {
	$sql="select * from `".$tbl['product']."` where 1 $w order by binary(`name`)";
	$res=sql_query($sql);
	$total=mysql_num_rows($res);
}
?>

<form name="prdSearchFrm" method="get" action="./" style="margin:0px" onSubmit="searchPrd(this)">
<input type="hidden" name="body" value="<?=$body?>">
<input type="hidden" name="exec" value="search">
<input type="hidden" name="pno" value="<?=$pno?>">
<input type="hidden" name="del_no" value="">
<input type="hidden" name="big" value="">
<input type="hidden" name="mid" value="">
<input type="hidden" name="small" value="">

		<table border=0 cellspacing=0 cellpadding=2>
			<tr>
				<td>
					<iframe name="cate11" src="./?body=product@product_cate.frm&ctype=1&level=1&sel=<?=$big?>" width="30" height="20" scrolling="no" frameborder="0"></iframe>
					<iframe name="cate12" src="./?body=product@product_cate.frm&ctype=1&level=2&big=<?=$big?>&sel=<?=$mid?>" width="30" height="20" scrolling="no" frameborder="0"></iframe>
					<iframe name="cate13" src="./?body=product@product_cate.frm&ctype=1&level=3&big=<?=$big?>&mid=<?=$mid?>&sel=<?=$small?>" width="30" height="20" scrolling="no" frameborder="0"></iframe>
				</td>
			</tr>
			<tr>
				<td>
				<?=selectArray($_search_type,"search_type",2,"",$search_type)?>
				<input type="text" name="search_str" value="<?=inputText($search_str)?>" class="input" style="width:155;">
				<input type="submit" value="검색" class="btn2">
				<select name="prd_no" onChange="parent.document.prdFrm.prd_no.value=this.value" style="width:300;">
					<option value="">:: 주문 상품을 검색하여 선택하세요 (이름순) ::</option>
<?
if($total) {
	while($data=mysql_fetch_array($res)) {
?>
					<option value="<?=$data[no]?>" <?=checked($data[no],$prd_no)?>><?=inputText($data[name])?></option>
<?
	}
}
?>
				</select>
				</td>
			</tr>
		</table>
</form>
		</td>
	</tr>
</table>

<script language="JavaScript">
<!--
window.onload=function() {
	selfResize();
}

var f=document.prdSearchFrm;
if (f.prd_no.selectedIndex==0)
{
	parent.document.prdFrm.prd_no.value='';
}

function addRefPrd(){
	if (!checkSel(f.prd_no,'등록할 관련상품을')) return false;
	f.body.value='product@product_ref.exe';
	f.method='post';
	f.target=hid_frame;
	f.submit();
}

function delRefPrd(n){
	f.body.value='product@product_ref.exe';
	f.method='post';
	f.target=hid_frame;
	f.del_no.value=n;
	f.submit();
}
//-->
</script>