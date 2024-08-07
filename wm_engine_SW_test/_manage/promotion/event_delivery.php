<?PHP

/* +----------------------------------------------------------------------------------------------+
' |  무료배송 이벤트
' +----------------------------------------------------------------------------------------------+*/

if (isset($cfg['freedeli_event_begin']) == false || isset($cfg['freedeli_event_finish']) == false) {
    $cfg['freedeli_event_begin'] = date('Y-m-d', $now).':00:00';
    $cfg['freedeli_event_finish'] = date('Y-m-d', $now).':23:59';
}

$begin = preg_split('/ |:/', $cfg['freedeli_event_begin']);
$finish = preg_split('/ |:/', $cfg['freedeli_event_finish']);

?>
<form method="post" action="<?=$_SERVER['PHP_SELF']?>" target="hidden<?=$now?>" onSubmit="return checkFreeDeliEvent(this)">
	<input type="hidden" name="body" value="promotion@event_delivery.exe">
	<div class="box_title first">
		<h2 class="title">무료배송 이벤트</h2>
	</div>
	<table class="tbl_row">
		<caption class="hidden">무료배송 이벤트</caption>
		<colgroup>
			<col style="width:15%">
		</colgroup>
		<tr>
			<th scope="row">사용여부</th>
			<td>
				<label class="p_cursor"><input type="radio" name="freedeli_event_use" value="Y" <?=checked($cfg[freedeli_event_use],"Y")?>> 사용함</label><br>
				<label class="p_cursor"><input type="radio" name="freedeli_event_use" value="N" <?=checked($cfg[freedeli_event_use],"N").checked($cfg[freedeli_event_use],"")?>> 사용안함</label>
				<span class="explain">(이벤트 기간이 아니면 적용되지 않습니다)</span>
			</td>
		</tr>
		<tr>
			<th scope="row">기간</th>
			<td>
                <input type="text" name="begin1" class="datepicker input" size="8" value="<?=$begin[0]?>">
				<?=dateSelectBox(0,23,"begin2",$begin[1])?> 시
				<?=dateSelectBox(0,59,"begin3",$begin[2])?> 분 ~
                <input type="text" name="finish1" class="datepicker input" size="8" value="<?=$finish[0]?>">
				<?=dateSelectBox(0,23,"finish2",$finish[1])?> 시
				<?=dateSelectBox(0,59,"finish3",$finish[2])?> 분
			</td>
		</tr>
		<tr>
			<th scope="row">최소 결제 금액</th>
			<td>
				<input type="text" name="freedeli_event_min_pay" value="<?=$cfg[freedeli_event_min_pay]?>" class="input" size="6" style="text-align:right"> <?=$cfg['currency_type']?> 이상일 경우 이벤트 적용
				<span class="explain">(0 입력시 모두 적용)</span>
			</td>
		</tr>
		<tr>
			<th scope="row">대상</th>
			<td>
				<label class="p_cursor"><input type="radio" name="freedeli_event_obj" value="1" <?=checked($cfg[freedeli_event_obj],"1").checked($cfg[freedeli_event_obj],"")?>> 전체 고객</label><br>
				<label class="p_cursor"><input type="radio" name="freedeli_event_obj" value="2" <?=checked($cfg[freedeli_event_obj],"2")?>> 회원만</label><br>
				<div class="explain">(회원그룹별 설정을 원하실 경우 <a href="./?body=member@member_group" target="_blank" class="p_color">회원관리의 회원그룹설정 페이지</a>를 이용하여 주시기 바랍니다)</div>
			</td>
		</tr>
	</table>
	<div class="box_bottom">
		<span class="box_btn blue"><input type="submit" value="확인"></span>
	</div>
</form>
<?include $engine_dir.'/_manage/promotion/event_apply.php';?>

<script language="JavaScript">
function checkFreeDeliEvent(f){
    printLoading();
    return true;
}
</script>