<?PHP

	/* +----------------------------------------------------------------------------------------------+
	' |  적립금 내역
	' +----------------------------------------------------------------------------------------------+*/

	include_once $engine_dir."/_engine/include/common.lib.php";
	include_once $engine_dir."/_engine/include/design.lib.php";
	$_skin = getSkinCfg();

	memberOnly(1,"");

	$sql="select * from {$tbl['milage']} where `member_no`='{$member['no']}' and `member_id`='{$member['member_id']}' order by `no` desc";

	$page = numberOnly($_GET['page']);
	$row = numberOnly($_GET['row']);

	include $engine_dir."/_engine/include/paging.php";
	if($page<=1) $page=1;
	if(!$row) $row=20;
	if(!$block) $block=10;
	$QueryString="";

	$NumTotalRec=$pdo->row("select count(*) from {$tbl['milage']} where `member_no`='$member[no]' and `member_id`='$member[member_id]'");
	$PagingInstance = new Paging($NumTotalRec, $page, $row, $block);
	$PagingInstance->addQueryString(makeQueryString('page'));
	$PagingResult=$PagingInstance->result($pg_dsn);
	$sql.=$PagingResult[LimitQuery];

	$pageRes=$PagingResult[PageLink];
	$resMilage = $pdo->iterator($sql);
	$idx=$NumTotalRec-($row*($page-1))+1;

	include $engine_dir."/_engine/include/milage.lib.php";
	common_header();

    // 전환 가능여부 체크
	$change_use=milageChanging(0,$member,2,1);
	$member[milage_c]=number_format($member[milage]);
	$cfg[point_change_ratio]=($cfg[point_change_ratio]<1) ? 0 : $cfg[point_change_ratio];

	if(!$single_module) {
?>
<script type='text/javascript'>
<!--
function checkFrm(f,s){
	if(s) if(!checkBlank(f.mamount, _lang_pack.mypage_input_transmileage)) return false;
	mamount=f.mamount.value;
	if(mamount > <?=$member[milage]?>){
		alert(_lang_pack.mypage_error_transmileage);
		resetFrm(f);
		return false;
	}
	pamount=Math.floor(mamount*<?=$cfg[point_change_ratio]?>);
	f.pamount.value=pamount;
	if(s){
		if(f.pamount.value < 1){ alert(_lang_pack.mypage_error_moremileage); return false; }
		if(!confirm(_lang_pack.mypage_confirm_transmileage)) return false;
	}
	f.exec.value="to_point";
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