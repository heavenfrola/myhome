<?PHP

	/* +----------------------------------------------------------------------------------------------+
	' |  포인트 적립/사용내역
	' +----------------------------------------------------------------------------------------------+*/

	include_once $engine_dir."/_engine/include/common.lib.php";
	memberOnly(1,"");


	$sql="select * from `$tbl[point]` where `member_no`='$member[no]' and `member_id`='$member[member_id]' order by `no` desc";

	include $engine_dir."/_engine/include/paging.php";
	if($page<=1) $page=1;
	if(!$row) $row=20;
	if(!$block) $block=10;
	$QueryString="";

	$NumTotalRec=$pdo->row("select count(*) from `$tbl[point]` where `member_no`='$member[no]' and `member_id`='$member[member_id]'");
	$PagingInstance = new Paging($NumTotalRec, $page, $row, $block);
	$PagingInstance->addQueryString($QueryString);
	$PagingResult=$PagingInstance->result($pg_dsn);
	$sql.=$PagingResult[LimitQuery];

	$pageRes=$PagingResult[PageLink];
	$resPoint=$pdo->query($sql);
	$idx=$NumTotalRec-($row*($page-1))+1;

	include $engine_dir."/_engine/include/milage.lib.php";
	common_header();

    // 전환이 가능한지 체크
	$change_use=milageChanging(0,$member,1,1);
	$member[point_c]=number_format($member[point]);
	$cfg[point_change_ratio]=($cfg[point_change_ratio]<1) ? 0 : $cfg[point_change_ratio];

	if(!$single_module){
?>
<script language="JavaScript">
<!--
function checkFrm(f,s){
	if(s) if(!checkBlank(f.pamount, _lang_pack.mypage_input_transpts)) return false;
	pamount=f.pamount.value;
	if(pamount > <?=$member[point]?>){
		alert(_lang_pack.mypage_error_transpts);
		resetFrm(f);
		return false;
	}
	mamount=Math.floor(pamount/<?=$cfg[point_change_ratio]?>);
	f.mamount.value=mamount;
	if(s){
		if(f.mamount.value < 1){ alert(_lang_pack.mypage_error_morepts); return false; }
		if(!confirm(_lang_pack.mypage_confirm_transpts)) return false;
	}
	f.exec.value="to_milage";
}
function resetFrm(f){
	f.pamount.value="";
	f.mamount.value="";
}
function onlyNumber(){
    if(event.keyCode != 13 && event.keyCode != 110 && event.keyCode != 190){
        if(event.keyCode != 8 && ((event.keyCode < 48) || (event.keyCode > 57))) event.returnValue=false;
    }
}//-->
</script>
<?
	}

	// 디자인 버전 점검 & 페이지 출력
	include_once $engine_dir."/_engine/common/skin_index.php";
?>