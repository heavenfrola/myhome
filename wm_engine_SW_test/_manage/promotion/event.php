<?PHP

	/* +----------------------------------------------------------------------------------------------+
	' |  할인/적립 이벤트
	' +----------------------------------------------------------------------------------------------+*/

	define('evnet_install', true);
	include_once $engine_dir."/_manage/promotion/event_install.exe.php";

	$no = numberOnly($_GET['no']);
	if($no) {
		$data=get_info($tbl['event'], 'no', $no);
		$data[event_begin] =date("Y-m-d/H/i",$data[event_begin]);
		$data[event_finish]=date("Y-m-d/H/i",$data[event_finish]);
	} else {
		$no = "";
		$data[event_ptype] ="0";
		$data[event_use]   ='Y';
		$data[event_begin] = date("Y-m-d",$now)."/00/00";
		$data[event_finish] = date("Y-m-d",$now)."/23/59";
	}
	$begin=explode("/",$data[event_begin]);
	$finish=explode("/",$data[event_finish]);

?>
<script type="text/javascript">
	var use_biz_member='<?=$cfg[use_biz_member]?>'
</script>
<form name="eveFrm" method="post" action="<?=$_SERVER['PHP_SELF']?>" onSubmit="return checkCfgEvent(this)" target="hidden<?=$now?>">
	<input type="hidden" name="body" value="promotion@event.exe">
	<input type="hidden" name="no" value="<?=$no?>">
	<div class="box_title first">
		<h2 class="title">할인/적립 이벤트</h2>
	</div>
	<div class="box_middle">
		<ul class="list_msg left">
			<li><a href="./?body=member@member_group"><u>회원 그룹별 적립/할인 혜택 기능</u></a>을 사용하실 경우, 본 이벤트가 <b>동시에</b> 적용됩니다</li>
			<li>아래 표와 같이 할인가는 전체 구매금액 기준입니다><br>↓ <span class="p_color2">예제</span> : <span class="p_color2">10,000</span><?=$cfg['currency_type']?> 결제시, <u>()안의 설정</u>으로 구매시<li>
		</ul>
		<table class="tbl_mini">
			<tr>
				<th colspan="2" rowspan="2"></th>
				<th scope="colgroup" colspan="2">회원그룹혜택</th>
			</tr>
			<tr>
				<th scope="col">할인 (3%)</th>
				<th scope="col">적립 (3%)</th>
			</tr>
			<tr>
				<th rowspan="2" scope="rowgroup">이벤트</th>
				<th scope="row">할인 (5%)</th>
				<td>총금액의 8% 할인(9,200 <?=$cfg['currency_type']?> 결제)</td>
				<td>총금액의 5% 할인(9,500 <?=$cfg['currency_type']?> 결제)<br>할인금액(실결제액:9,500)의 3%적립(300 <?=$cfg['currency_type']?> 적립)</td>
			</tr>
			<tr>
				<th scope="row">적립 (5%)</th>
				<td>총금액의 3% 할인(9,700 <?=$cfg['currency_type']?> 결제)<br>할인금액(실결제액:9,700원)의 5%적립(500원 적립)</td>
				<td>총금액의 8% 적립(800 <?=$cfg['currency_type']?> 적립)</td>
			</tr>
		</table>
	</div>
	<table class="tbl_row">
		<caption class="hidden">할인/적립 이벤트</caption>
		<colgroup>
			<col style="width:9%">
			<col style="width:9%">
			<col>
		</colgroup>
		<tr>
			<th scope="row" colspan="2">사용여부</th>
			<td>
				<label class="p_cursor"><input type="radio" name="event_use" value="Y" <?=checked($data[event_use],"Y")?>> 사용함</label><br>
				<label class="p_cursor"><input type="radio" name="event_use" value="N" <?=checked($data[event_use],"N").checked($data[event_use],"")?>> 사용안함</label>
				<ul class="explain">
					<li>이벤트 기간이 아니면 적용되지 않습니다.</li>
					<li>이벤트 할인 및 적립금은 할인 전 상품 판매금액 기준으로 책정됩니다.</li>
					<li>이벤트 할인 판매정책이 체크된 상품만 적용됩니다.</li>
				</ul>
			</td>
		</tr>
		<tr>
			<th scope="row" colspan="2">이벤트명</th>
			<td><input type="text" name="event_name" value="<?=$data['event_name']?>" size="75" maxlength="30" class="input"></td>
		</tr>
		<tr>
			<th scope="row" colspan="2">기간</th>
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
			<th scope="row" colspan="2"> 최소 결제 금액<br><span class="explain">(이벤트 적용 상품 총액)</span></th>
			<td>
				<input type="text" name="event_min_pay" value="<?=number_format($data[event_min_pay]+0)?>" class="input" size="6" style="text-align:right" onFocus="this.value=removeComma(this.value);this.select();" onBlur="this.value=setComma(this.value)"> <?=$cfg['currency_type']?> 이상일 경우 이벤트 적용
				<span class="explain">(0 입력시 모두 적용)</span>
			</td>
		</tr>
		<tr>
			<th scope="row" colspan="2">대상</th>
			<td>
				<label class="p_cursor"><input type="radio" name="event_obj" value="1" <?=checked($data[event_obj],"1").checked($data[event_obj],"")?>> 전체 고객</label><br>
				<label class="p_cursor"><input type="radio" name="event_obj" value="2" <?=checked($data[event_obj],"2")?>> 회원만</label>
				<?if($cfg[use_biz_member]=="Y"){?>
				<br><label class="p_cursor"><input type="radio" name="event_obj" value="3" <?=checked($data[event_obj],"3")?>> 기업 회원만</label>
				<?}?>
				<ul class="explain">
					<li>네이버페이 및 페이코 바로구매 시 이벤트가 적용되지 않습니다.</li>
				</ul>
			</td>
		</tr>
		<tr>
			<th scope="row" colspan="2">이벤트방식</th>
			<td>
				<label class="p_cursor"><input type="radio" name="event_type" id="event_type" value="1" onClick="checkEventMilage()" <?=checked($data[event_type],"1")?>> 적립 <span class="explain">(회원만 가능)</span></label><br>
				<label class="p_cursor"><input type="radio" name="event_type" id="event_type" value="2" onClick="checkEventMilage()" <?=checked($data[event_type],"2").checked($data[event_type],"")?>> 할인</label>
			</td>
		</tr>
		<tr>
			<th scope="rowgroup" rowspan="2" class="line_r">상품별 적립금</th>
			<th scope="row">적립시</th>
			<td>
				<label class="p_cursor"><input type="radio" name="event_milage_addable" id="event_milage_addable" value="Y" <?=checked($data[event_milage_addable],"Y")?>> 적립함 <span class="explain">(이벤트 적립금에 상품 적립금을 <u>추가 적립</u>)</span></label><br>
				<label class="p_cursor"><input type="radio" name="event_milage_addable" id="event_milage_addable" value="N" <?=checked($data[event_milage_addable],"N").checked($data[event_milage_addable],"")?>> 적립안함</label>
			</td>
		</tr>
		<tr>
			<th scope="row">할인시</th>
			<td>
				<label class="p_cursor"><input type="radio" name="event_milage_addable2" id="event_milage_addable2" value="Y" <?=checked($data[event_milage_addable2],"Y")?>> 적립함</label><br>
				<label class="p_cursor"><input type="radio" name="event_milage_addable2" id="event_milage_addable2" value="N" <?=checked($data[event_milage_addable2],"N").checked($data[event_milage_addable2],"")?>> 적립안함</label>
			</td>
		</tr>
		<tr>
			<th scope="row" colspan="2">결제수단</th>
			<td>
				<label class="p_cursor"><input type="radio" name="event_ptype" value="0" <?=checked($data[event_ptype],"0")?>> 모든 결제</label><br>
				<label class="p_cursor"><input type="radio" name="event_ptype" value="2" <?=checked($data[event_ptype],"2")?>> 현금 결제일때만 <span class="explain">(현금결제 할인 이벤트)</span></label>
			</td>
		</tr>
		<tr>
			<th scope="row" colspan="2">적립금으로 결제시<br><span class="explain">(복합 결제 포함)</span></th>
			<td>
				<ul>
					<li><b>적립 이벤트</b> : 이벤트 적용 불가 (이벤트 적립금이 적립되지 않습니다)</li>
					<li><b>할인 이벤트</b> : 적용 결제수단을 <u>모든 결제</u>로 설정했을 경우 이벤트 적용</li>
					<li>주문/결제시 <u>적립금 결제</u>는 결제수단의 일종입니다.</li>
				</ul>
			</td>
		</tr>
		<tr>
			<th scope="row" colspan="2">할인(적립)률</th>
			<td><input type="text" name="event_per" value="<?=($data[event_per]+0)?>" size="5" class="input" style="text-align:right"> %</td>
		</tr>
		<tr>
			<th scope="row" colspan="2">절사단위</td>
			<td>
				<label class="p_cursor"><input type="radio" name="event_round" value="10" <?=checked($data[event_round],"10").checked($data[event_round],"")?>> 10자리미만</label><br>
				<label class="p_cursor"><input type="radio" name="event_round" value="100" <?=checked($data[event_round],"100")?>> 100자리미만</label><br>
				<label class="p_cursor"><input type="radio" name="event_round" value="1000" <?=checked($data[event_round],"1000")?>> 1000자리미만</label>
			</td>
		</tr>
	</table>
	<div class="box_bottom">
		<?if($no > 0) {?>
		<span class="box_btn blue"><input type="submit" value="수정"></span>
		<span class="box_btn gray"><input type="button" value="삭제" onclick="removeAttend(<?=$data['no']?>)"></span>
		<span class="box_btn gray"><input type="button" value="닫기" onclick="self.close();"></span>
		<?} else {?>
		<span class="box_btn blue"><input type="submit" value="생성"></span>
		<span class="box_btn gray"><input type="button" value="목록" onclick="location.href='./?body=promotion@event_list'"></span>
		<?}?>
	</div>
</form>
<script language="JavaScript">
	function removeAttend(no) {
		if(confirm('이벤트 내역이 삭제됩니다.\n정말 삭제하시겠습니까?')) {
			$.post('./index.php?body=promotion@event.exe', {'no':no, 'exec':'remove'}, function(r) {
				opener.location.reload();
				self.close();
			});
		}
	}


	function checkCfgEvent(f){
		f.event_min_pay.value=removeComma(f.event_min_pay.value);
		if(!checkNum(f.event_min_pay,'최소 결제금액은 숫자만 입력해주세요.')) return false;

		if(!checkBlank(f.event_per,'할인률을 입력해주세요.')) return false;
		if (!CheckType(f.event_per.value,NUM)) {
			alert('할인률은 숫자만 입력하세요');
			f.event_per.focus();
			return false;
		}
		if (eval(f.event_per.value)>100) {
			alert('할인률을 100 이하로 입력하세요');
			f.event_per.focus();
			return false;
		}

        printLoading();

        return true;
	}

	function checkEventMilage(){
		var evf = byName('eveFrm');
		if(evf.event_type[0].checked==true) { // 적립
			evf.event_obj[0].disabled=true;
			if (evf.event_obj[0].checked==true) {
				evf.event_obj[1].checked=true;
			}
		} else { // 할인
			evf.event_obj[0].disabled=false;
		}
	}

	checkEventMilage();
</script>