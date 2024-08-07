<?PHP

	/* +----------------------------------------------------------------------------------------------+
	' |  회원그룹 설정
	' +----------------------------------------------------------------------------------------------+*/

	if(!$cfg[member_auto_move_use]) $cfg[member_auto_move_use]="N";
	if(!$cfg[member_auto_move_down]) $cfg[member_auto_move_down]="N";
	if(!$cfg['msale_mile_type']) $cfg['msale_mile_type'] = 1;
	if(!$cfg['member_day_down']) $cfg['member_day_down'] = 'N';
	if(!$cfg['member_level_day_down']) $cfg['member_level_day_down'] = 'N';
	if(!$cfg['member_level_field']) $cfg['member_level_field'] = 'prc';

	$tmp = $pdo->iterator("select count(*) as `cnt`, `level` from `$tbl[member]` group by `level`");
    foreach ($tmp as $tm) {
		$total_member[$tm[level]] = $tm[cnt];
	}

	$sql="select no, name, use_group from `$tbl[member_group]` order by `no`";
	$res = $pdo->iterator($sql);
	$gstr="<select name=\"to_group[]\" style=\"width:115px;\">\n";
    foreach ($res as $data) {
		if($data['no'] == 1 && !$data['name']) {
			$data['name'] = '게시판관리자';
			$pdo->query("update $tbl[member_group] set name='게시판관리자' where no=1");
		}
		$pdo->query("update `$tbl[member_group]` set `total_member`='".$total_member[$data[no]]."' where `no`='$data[no]'");
		$color = ($data['use_group'] != 'Y' && $data['no'] != 1) ? "style='color: #aaa;'" : "";
		$gstr.="<option value=\"$data[no]\" $color>".inputText($data[name])."</option>\n";
	}
	$gstr.="</select>";

	$wec_acc = new weagleEyeClient($_we, 'account');
	$result = $wec_acc->call('getAutoMemberGroup');
	$cfg['member_level_day'] = $result[0]->day[0];

	if(!$cfg['member_event_type']) $cfg['member_event_type'] = 2;

?>
<form name="mgFrm" method="post" action="<?=$_SERVER['PHP_SELF']?>" target="hidden<?=$now?>" onSubmit="return checkMgFrm()">
	<input type="hidden" name="body" value="member@member_group.exe">
	<input type="hidden" name="exec" value="">
	<input type="hidden" name="exec_gno1" value="">
	<input type="hidden" name="exec_gno2" value="">
	<input type="hidden" name="config_code" value="member_group">
	<div class="box_title first">
		<h2 class="title">회원그룹 설정</h2>
	</div>
	<table class="tbl_col">
		<caption class="hidden">회원그룹 설정</caption>
		<colgroup>
			<col style="width:40px;">
			<col style="width:40px;">
			<col>
			<col>
			<col>
			<col>
			<col>
			<col>
			<col>
			<col>
			<col style="width:120px;">
			<col style="width:175px;">
			<col style="width:85px;">
		</colgroup>
		<thead>
			<tr>
				<th scope="col" rowspan="2">등급</th>
				<th scope="col" rowspan="2">사용</th>
				<th scope="col" rowspan="2">회원그룹명</th>
				<th scope="col" colspan="4">혜택 및 조건</th>
				<th scope="col" colspan="3">등급조건</th>
				<th scope="col" rowspan="2">회원수</th>
				<th scope="col" rowspan="2">이동</th>
				<th scope="col" rowspan="2">추가정보</th>
			</tr>
			<tr>
				<th scope="col">현금결제 <i class="icon_info btt" tooltip="현금결제 시에만 무료배송/할인/적립 혜택을 적용합니다."></i></th>
				<th scope="col">무료배송</th>
				<th scope="col">할인</th>
				<th scope="col">적립</th>
				<th scope="col">등급보호 <i class="icon_info btt" tooltip="회원그룹 자동이동 시 등급이 자동변경되지 않으며,<br>해당 등급으로는 자동승급되지 않습니다."></i></th>
				<th scope="col">구매금액</th>
				<th scope="col">구매횟수</th>
			</tr>
		</thead>
		<tbody>
			<?
				$sql="select * from $tbl[member_group] order by `no`";
				$res = $pdo->iterator($sql);
				$ii=$idx=0;
                foreach ($res as $data) {
					$data[total_member]=$total_member[$data[no]];
					if($data[no]==1) {
			?>
			<tr>
				<td>
					<input type="hidden" name="gno[<?=$data['no']?>]" value="<?=$data['no']?>">
					<input type="hidden" name="total_member[]" id="total_member" value="<?=$data[total_member]?>">
					<?=$data[no]?>
				</td>
				<td></td>
				<td class="left"><?=stripslashes($data[name])?></td>
				<td colspan="7"></td>
				<td><a href="./?body=member@member_list&s_group=<?=$data[no]?>" target="_blank"><u><?=number_format($data[total_member])?>명</u></a></td>
				<td>
					<?=str_replace('to_group[]', 'to_group['.$data['no'].']', $gstr)?>
					<span class="box_btn_s"><input type="button" value="이동" onClick="moveMemberGroup(<?=$data['no']?>)"></span>
				</td>
				<td></td>
			</tr>
			<?
					} else {
						if($data[no]=="9") {
							$ro="readonly onClick=\"this.checked=true;alert('기본그룹은 항상 사용해야 합니다.')\"";
							$ro2="readonly onClick=\"alert('기본그룹은 구매금액 조건이 필요 없습니다.')\"";
							$ro3="readonly onClick=\"alert('기본그룹은 구매횟수 조건이 필요 없습니다.')\"";
						}
						else {
							$ro=$ro2=$ro3="";
						}
			?>
			<tr>
				<td>
					<input type="hidden" name="gno[<?=$data['no']?>]" value="<?=$data['no']?>">
					<input type="hidden" name="total_member[]" value="<?=$data[total_member]?>">
					<?=$data['no']?>
				</td>
				<td><input type="checkbox" name="use_group[<?=$data['no']?>]" value="Y" <?=checked($data[use_group],"Y")?> <?=$ro?>></td>
				<td class="left"><input type="text" name="name[<?=$data['no']?>]" value="<?=inputText($data[name])?>" class="input block"></td>
				<td><input type="checkbox" name="milage_cash[<?=$data['no']?>]" value="Y" <?=checked($data[milage_cash],"Y")?>><label for="milage_cash<?=$data['no']?>"></label></td>
				<td><input type="checkbox" name="free_delivery[<?=$data['no']?>]" value="Y" class="free_delivery" <?=checked($data[free_delivery],"Y")?>></td>
				<td><input type="text" name="milage[<?=$data['no']?>]" value="<?=$data[milage]?>" class="input milage1" size="3"> %</td>
				<td><input type="text" name="milage2[<?=$data['no']?>]" value="<?=$data['milage2']?>" class="input milage2" size="3"> %</td>
				<td><input type='checkbox' name='protect[<?=$data['no']?>]' value='Y' <?=checked($data['protect'], 'Y')?>></td>
				<td><input type="text" name="move_price[<?=$data['no']?>]" value="<?=$data[move_price]?>" <?=$ro2?> class="input right" size="5"><br><?=$cfg['currency_type']?> 이상</td>
				<td><input type="text" name="move_qty[<?=$data['no']?>]" value="<?=$data['move_qty']?>" <?=$ro3?> class="input right" size="1"><br>건 이상</td>
				<td><a href="./?body=member@member_list&s_group=<?=$data[no]?>&detaiil_search=1" target="_blank"><u><?=number_format($data[total_member])?>명</u></a></td>
				<td>
					<?=str_replace('to_group[]', 'to_group['.$data['no'].']', $gstr)?>
					<span class="box_btn_s"><input type="button" value="이동" onClick="moveMemberGroup(<?=$data['no']?>)"></span>
				</td>
				<td><span class="box_btn_s"><input type="button" value="추가정보" onClick="addinfoMemberGroup(<?=$data[no]?>)"></span></td>
			</tr>
			<?
				$ii--;
					}
				$idx++;
				}
			?>
		</tbody>
	</table>

	<table class="tbl_row">
		<caption class="hidden">회원그룹 설정 적용</caption>
		<colgroup>
			<col style="width:5%">
			<col style="width:11%">
			<col>
		</colgroup>
		<tr>
			<th scope="row" colspan="2">회원그룹별<br>혜택 및 조건 사용</th>
			<td>
				<label class="p_cursor"><input type="radio" name="member_event_use" id="member_event_use" value="Y" <?=checked($cfg[member_event_use],"Y")?>> 사용함</label>
				<label class="p_cursor"><input type="radio" name="member_event_use" value="N" id="member_event_use" <?=checked($cfg[member_event_use],"N").checked($cfg[member_event_use],"")?>> 사용안함</label>
				<div class="list_info tp">
					<p><strong>판매설정 내 회원혜택이 적용된 상품</strong>에 한해 회원그룹 혜택(이하 회원혜택)이 적용됩니다.</p>
				</div>
			</td>
		</tr>
		<tr>
			<th scope="row" rowspan="5" class="milageCha line_r">혜택</th>
			<th scope="row">무료배송</th>
			<td>
				<label class="p_cursor"><input type="radio" name="mgroup_free_delivery" id="mgroup_free_delivery" value="Y" <?=checked($cfg[mgroup_free_delivery],"Y")?>> 사용함</label>
				<label class="p_cursor"><input type="radio" name="mgroup_free_delivery" value="N" id="mgroup_free_delivery" <?=checked($cfg[mgroup_free_delivery],"N").checked($cfg[mgroup_free_delivery],"")?>> 사용안함</label>
				<div class="list_info tp">
					<p>무료배송 설정에 체크된 회원그룹에 대해 무료배송 혜택을 적용합니다.</p>
				</div>
			</td>
		</tr>
		<tr>
			<th scope="row">적립/할인</th>
			<td>
				<label class="p_cursor"><input type="radio" name="member_event_type" value="1" <?=checked($cfg[member_event_type],"1")?> onclick="setServerType(1)"> 적립</label>
				<label class="p_cursor"><input type="radio" name="member_event_type" value="2" <?=checked($cfg[member_event_type],"2").checked($cfg[member_event_type],"")?> onclick="setServerType(2)"> 할인</label>
				<label class="p_cursor"><input type="radio" name="member_event_type" value="3" <?=checked($cfg['member_event_type'],'3')?> onclick="setServerType(3)"> 적립+할인</label>
				<ul class="list_info tp">
					<li>회원혜택 적립의 경우 적립금 산정방식 설정에 따라 책정되며, 상품별로 개별 계산 후 합산됩니다.</li>
					<li>회원혜택 할인의 경우 등록된 상품 판매가를 기준으로 책정됩니다.(기타 할인금액 미반영)</li>
				</ul>
			</td>
		</tr>
		<tr class="milageLyr">
			<th scope="row">적립금 산정방식</th>
			<td>
				<label><input type="radio" name="msale_mile_type" value="1" <?=checked($cfg['msale_mile_type'], 1)?>> 상품 판매가 기준</label>
				<label><input type="radio" name="msale_mile_type" value="2" <?=checked($cfg['msale_mile_type'], 2)?>> 실결제금액 기준</label>
			</td>
		</tr>
		<tr class="milageLyr">
			<th scope="row">적립금 지급방식</th>
			<td>
				<label class="p_cursor"><input type="radio" name="member_milage_type" value="1" <?=checked($cfg[member_milage_type],"1").checked($cfg[member_milage_type],"")?>> 치환
				<span class="explain">(상품별로 설정된 적립금이 회원혜택 적립금으로 치환됩니다.)</span></label><br>
				<label class="p_cursor"><input type="radio" name="member_milage_type" value="2" <?=checked($cfg[member_milage_type],"2")?>> 추가
				<span class="explain">(상품별로 설정된 적립금에 회원혜택 적립금이 추가적립됩니다.)</span></label>
			</td>
		</tr>
		<tr>
			<th scope="row">적립금/할인 절사단위</th>
			<td>
				<label class="p_cursor"><input type="radio" name="msale_round" value="1" <?=checked($cfg[msale_round],"1")?>> 절사없음</label>
				<label class="p_cursor"><input type="radio" name="msale_round" value="10" <?=checked($cfg[msale_round],"10").checked($cfg[msale_round],"")?>> 10원 단위</label>
				<label class="p_cursor"><input type="radio" name="msale_round" value="100" <?=checked($cfg[msale_round],"100")?>> 100원 단위</label>
				<label class="p_cursor"><input type="radio" name="msale_round" value="1000" <?=checked($cfg[msale_round],"1000")?>> 1000원 단위</label>
			</td>
		</tr>
		<tr>
			<th scope="row" colspan="2" rowspan="2">등급조건 적용범위</th>
			<td>
				<select name="member_level_limit" onchange="chgLevelLimit()">
					<option value="0">전체</option>
					<option value="1" <?=checked($cfg['member_level_limit'], 1, 1)?>>최근 1개월</option>
					<option value="3" <?=checked($cfg['member_level_limit'], 3, 1)?>>최근 3개월</option>
					<option value="6" <?=checked($cfg['member_level_limit'], 6, 1)?>>최근 6개월</option>
					<option value="12" <?=checked($cfg['member_level_limit'], 12, 1)?>>최근 12개월</option>
					<option value="24" <?=checked($cfg['member_level_limit'], 24, 1)?>>최근 24개월</option>
				</select>
				<div class="list_info tp">
					<p><strong>배송완료</strong> 처리된 주문서를 기준으로 등급이 산정됩니다.</p>
				</div>
			</td>
		</tr>
		<tr>
			<td>
				<ul>
					<li><label><input type="radio" name="member_level_field" value="both" <?=checked($cfg['member_level_field'], 'both')?>> 구매금액과 구매횟수가 모두 충족</label></li>
					<li><label><input type="radio" name="member_level_field" value="either" <?=checked($cfg['member_level_field'], 'either')?>> 구매금액 또는 구매횟수 하나만 충족</label></li>
					<li><label><input type="radio" name="member_level_field" value="prc" <?=checked($cfg['member_level_field'], 'prc')?>> 구매금액만 충족</label></li>
					<li><label><input type="radio" name="member_level_field" value="qty" <?=checked($cfg['member_level_field'], 'qty')?>> 구매횟수만 충족</label></li>
				</ul>
			</td>
		</tr>
		<tr>
			<th scope="row" rowspan="2" class="line_r">일괄</th>
			<th scope="row">월별승급 설정</th>
			<td>
				<select name="member_level_day">
					<option value="">사용안함</option>
					<?for($i = 1; $i <= 28; $i++) {?>
					<option value="<?=$i?>" <?=checked($i, $cfg['member_level_day'], 1)?>>매월 <?=$i?>일</option>
					<?}?>
				</select>
				<ul class="list_info tp">
					<li>매월 설정한 날짜 기준 오전 2시에 회원의 등급조정이 진행됩니다.</li>
					<li>등급조정 회원수가 많을 경우 시간이 다소 소요될 수 있습니다.</li>
					<li>회원그룹별 등급조건 설정이 정확하게 설정되어있는지 확인바랍니다.</li>
				</ul>
			</td>
		</tr>
		<tr>
			<th scope="row">월별강등 설정</th>
			<td>
				<label><input type="radio" name="member_level_day_down" value="Y" <?=checked($cfg['member_level_day_down'], 'Y')?>> 사용함</label>
				<label><input type="radio" name="member_level_day_down" value="N" <?=checked($cfg['member_level_day_down'], 'N')?>> 사용안함</label>
			</td>
		</tr>
		<tr>
			<th scope="row" rowspan="2" class="line_r">개별</th>
			<th scope="row">자동승급 설정</th>
			<td>
				<label class="p_cursor"><input type="radio" name="member_auto_move_use" id="member_auto_move_use" value="Y" <?=checked($cfg[member_auto_move_use],"Y")?>> 사용함 <span class="explain">(등급조건을 만족할 때 자동승급)</span></label>
				<label class="p_cursor"><input type="radio" name="member_auto_move_use" value="N" id="member_auto_move_use"  <?=checked($cfg[member_auto_move_use],"N")?>> 사용안함</label>
				<div class="list_info tp">
					<p>자동승급 설정 시 월별승급과 달리 등급조건을 만족하는 회원의 개별 등급조정이 실시간으로 진행됩니다.</p>
				</div>
			</td>
		</tr>
		<tr>
			<th scope="row">자동강등 설정</th>
			<td>
				<label class="p_cursor"><input type="radio" name="member_auto_move_down" id="member_auto_move_down" value="Y" <?=checked($cfg[member_auto_move_down],"Y")?>> 사용함 <span class="explain">(반품/교환으로 인한 조건미달 시 자동강등)</span></label>
				<label class="p_cursor"><input type="radio" name="member_auto_move_down" value="N" id="member_auto_move_down"  <?=checked($cfg[member_auto_move_down],"N")?>> 사용안함</label>
			</td>
		</tr>
	</table>
	<div class="box_middle2 left">
		<label class="p_cursor"><input type="checkbox" name="member_auto_move" value="Y"> 수정 시 현재 설정되어있는 등급조건으로 회원들의 등급이 조정됩니다.</label>
		<ul class="list_info">
			<li>수동 등급조정 시 자동승급 설정과 상관없이 등급조건을 만족하는 경우 승급이 진행되나, 강등의 경우 자동강등 설정에 따라 해당 유무가 결정됩니다.</li>
			<li>등급조정 회원수가 많을 경우 시간이 다소 소요될 수 있습니다.</li>
		</ul>
	</div>
	<div class="box_middle2 left">
		<div class="list_info">
			<p class="title">[기타 안내사항]</p>
			<ul class="list_info">
				<li>등급의 숫자가 낮을수록 높은 등급이며, 9등급은 가입 시 기본등급으로 항상 사용해야 합니다.</li>
				<li>미사용으로 설정한 그룹의 회원은 자동으로 9등급 회원으로 변경됩니다. (그룹 이동 후 미사용으로 설정바랍니다.)</li>
				<li>구매금액은 배송완료 처리된 주문서의 실결제금액에서 배송비를 제외한 금액입니다.</li>
				<li>
					추가 적립금은 상품 주문 시 상품 적립금 이외 추가로 지급되는 적립금입니다.<br>
					(이벤트와 함께 적용 시 꼭 이벤트 설정을 확인바랍니다. <a href="./?body=promotion@event_list" target="_blank">바로가기</a>)
				</li>
			</ul>
		</div>
	</div>
	<div class="box_bottom">
		<span class="box_btn blue"><input type="submit" value="수정"></span>
	</div>
</form>

<script type="text/javascript">
	var f=document.mgFrm;
	function moveMemberGroup(n) {
		var from = $("input[name='gno["+n+"]']").val();
		var to = $("select[name='to_group["+n+"]']").val();

		if(from == to) {
			window.alert('이동 전후의 회원그룹이 같습니다.');
			return;
		}

		if(!confirm('선택 한 회원그룹의 회원을 모두 이동 하시겠습니까?\t')) return;
		f.exec.value = 'move';
		f.exec_gno1.value = from;
		f.exec_gno2.value = to;
		f.submit();
	}

	function checkMgFrm(){
		if(f.member_auto_move_use[0].checked==true || f.member_auto_move.checked==true) {
			if(!checkAutoMove()) {
				return false;
			}
		}
		for(i=0; i<=7; i++) {
            var milage = f.elements['milage['+i+']'];
            if (!milage) continue;
            if(parseFloat(milage.value) < 0 || parseFloat(milage.value) > 100) {
                alert('할인(적립률)은 0 ~ 100 으로 입력하세요.(소수점 한자리까지 가능)');
                milage.focus();
                return false;
            }
		}

		if(!confirm('현재 회원그룹 별 설정을 적용하시겠습니까?')) {
			return false;
		}

        printLoading();

		return true;
	}

	function checkAutoMove(){
		old_prc='X';
		for(i=0; i<=7; i++)
		{
            var use_group = f.elements['use_group['+i+']'];
            var move_price = f.elements['move_price['+i+']'];
            var protect = f.elements['protect['+i+']'];
            if (!use_group) continue;
			if(use_group.checked==true)
			{
                if (protect.checked == true) continue;
				if(!checkBlank(move_price,'구매금액 조건을 입력해주세요.')) return false;
				if(!checkNum(move_price,'구매금액 조건을 입력해주세요.')) return false;
				nprc=eval(move_price.value);
				if(old_prc!='X' && nprc>=old_prc)
				{
					alert('\n 등급의 숫자가 빠른 그룹일수록 구매금액이 높아야합니다.      \n\n (즉, 2등급보다 1등급의 구매금액이 높아야 합니다.)\n');
					move_price.focus();
					return false;
				}
				old_prc=nprc;
			}
		}
		return true;
	}

	function addinfoMemberGroup(n){
		nurl='./pop.php?body=member@member_group_addinfo&no='+n;
		window.open(nurl,'wm_saddinfoMemberGroup','top=10,left=200,width=500,height=250,status=no,toolbars=no,scrollbars=no');
	}

	function chgLevelLimit() {
		if(f.member_level_limit.selectedIndex == 0) {
			f.member_level_day.disabled = true;
		} else {
			f.member_level_day.disabled = false;
		}
	}

	function setServerType(type) {
		if(type == 2) {
			$('.milageLyr').hide();
			$('.milageCha').attr('rowspan','3');
		} else {
			$('.milageLyr').show();
			$('.milageCha').attr('rowspan','5');
		}

		var milage1 = milage2 = false;
		if(type == 1) milage1 = true;
		if(type == 2) milage2 = true;
		$('.milage1').attr('disabled', milage1);
		$('.milage2').attr('disabled', milage2);
	}

	function setMoveType(o) {
		if(!o) {
			o = $(':checked[name=member_level_field]')[0];
		}
		$('.move_qty, .move_price').prop('disabled', false);
		if(o.value == 'prc') {
			$('.move_qty').prop('disabled', true);
		} else if(o.value == 'qty') {
			$('.move_price').prop('disabled', true);
		}
	}

	$(function() {
		chgLevelLimit();
		setServerType(<?=$cfg['member_event_type']?>);
		setMoveType();
	});

	$(':radio[name=member_level_field]').change(function() {
		setMoveType(this);
	});
</script>