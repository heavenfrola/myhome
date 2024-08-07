<?PHP

	/* +----------------------------------------------------------------------------------------------+
	' |  정기배송 세트 등록/수정 관리
	' +----------------------------------------------------------------------------------------------+*/

    if (file_exists($engine_dir.'/_plugin/subScription/set.common.php') == false) {
        msg('', '/');
    }
    include_once $engine_dir.'/_plugin/subScription/set.common.php';

	$popup = $_GET['popup'];
	if($popup=='Y') {
		$sbscr_set_no = numberOnly($_GET['sbscr_set_no']);
		if($sbscr_set_no) {//세트등록
			$data = $pdo->assoc("select * from $tbl[sbscr_set] where no='$sbscr_set_no'");
		}
	}else {
		if($cfg['sbscr_dlv_period']) $data['dlv_period'] = $cfg['sbscr_dlv_period'];
		if($cfg['sbscr_dlv_week']) $data['dlv_week'] = $cfg['sbscr_dlv_week'];
		if($cfg['sbscr_dlv_type']) $data['dlv_type'] = $cfg['sbscr_dlv_type'];
		if($cfg['sbscr_sale_use']) $data['sale_use'] = $cfg['sbscr_sale_use'];
		if($cfg['sbscr_sale_percent']) $data['sale_percent'] = $cfg['sbscr_sale_percent'];
		if($cfg['sbscr_sale_ea']) $data['sale_ea'] = $cfg['sbscr_sale_ea'];
		if($cfg['sbscr_dlv_end']) $data['dlv_end'] = $cfg['sbscr_dlv_end'];
	}

	if($data['dlv_period']) {
		$sbscr_dlv_period = explode('|', $data['dlv_period']);
		foreach($sbscr_dlv_period as $val) {
			${'sbscr_dlv_period_'.$val} = true;
		}
	}
	if($data['dlv_week']) {
		$sbscr_dlv_week = explode('|', $data['dlv_week']);
		foreach($sbscr_dlv_week as $val) {
			${'sbscr_dlv_week_'.$val} = true;
		}
	}

	$type_disabled = '';
	if($cfg['sbscr_order_all']=='Y') {
		$type_disabled = 'disabled';
		if(!$data['dlv_type']) $data['dlv_type'] = "Y";
	}else {
		if(!$data['dlv_type']) $data['dlv_type'] = "N";
	}

	$week_text = array("월", "화", "수", "목", "금", "토", "일");

?>
<form name="sbscrsetFrm" method="post" action="<?=$_SERVER['PHP_SELF']?>" target="hidden<?=$now?>" onsubmit="printLoading();">
	<input type="hidden" name="body" value="product@sub_set.exe">
	<input type="hidden" name="sbscr_set_no" value="<?=$sbscr_set_no?>">
	<input type="hidden" name="popup" value="<?=$popup?>">
	<table class="tbl_row">
		<caption class="hidden">정기배송 세트 등록/수정 관리</caption>
		<colgroup>
			<?php if($popup == 'Y') { ?>
			<col style="width:23%">
			<?php } else { ?>
			<col style="width:15%">
			<?php } ?>
			<col>
		</colgroup>
		<?php if ($popup=='Y') { ?>
		<!--
		<tr>
			<th scope="row">기본값 설정</th>
			<td><label class="p_cursor"><input type="checkbox" name="set_default" value="Y" <?=checked($data['default'],"Y")?>> 기본 설정값으로 지정</label></td>
		</tr>
		-->
		<tr>
			<th scope="row">세트명</th>
			<td>
				<input type="text" name="name" value="<?=inputText($data['name'])?>" class="input">
			</td>
		</tr>
		<?php } ?>
		<tr>
			<th scope="row">배송주기</td>
			<td>
				<?php foreach($_sbscr_periods as $key => $val) { ?>
					<label class="p_cursor"><input type="checkbox" name="sbscr_dlv_period[]" value="<?=$key?>" <?=checked(${'sbscr_dlv_period_'.$key}, true)?>> <?=$val?></label>
				<?php } ?>
			</td>
		</tr>
		<tr>
			<th scope="row">배송요일</td>
			<td>
				<?php for($i = 1; $i <= 7; $i++) { ?>
					<label for="sbscr_dlv_week_<?=$i?>" class="p_cursor"><input type="checkbox" name="sbscr_dlv_week[]" id="sbscr_dlv_week_<?=$i?>" value="<?=$i?>" <?=checked(${'sbscr_dlv_week_'.$i}, true)?>> <?=$week_text[$i-1]?></label>
				<?php } ?>
			</td>
		</tr>
		<tr>
			<th id="dlv_rowspan" scope="row" rowspan="2">배송기간</th>
			<td>
				<label for="sbscr_dlv_type_n" class="p_cursor"><input type="radio" name="sbscr_dlv_type" id="sbscr_dlv_type_n" value="N" <?=$type_disabled?> <?=checked($data['dlv_type'],'N')?> onclick="dlvtypeCheck('N');">
				기간없음</label>
				<label for="sbscr_dlv_type_y" class="p_cursor"><input type="radio" name="sbscr_dlv_type" id="sbscr_dlv_type_y" value="Y" <?=checked($data['dlv_type'],'Y')?> onclick="dlvtypeCheck('Y');">
				주문최대 기간 설정</label>
			</td>
		</tr>
		<tr id="sbscr_dlv_date" style="display:none;">
			<td>
				<select name="sbscr_dlv_end">
					<?php for($i = 1; $i <= 24; $i++) { ?>
					<option value="<?=$i?>" <?=checked($data['dlv_end'], $i, 1)?>><?=$i?> 개월</option>
					<?php } ?>
				</select>
			</td>
		</tr>
		<tr>
			<th scope="row">정기배송 할인</th>
			<td>
				<label for="sbscr_sale_use" class="p_cursor"><input type="checkbox" name="sbscr_sale_use" id="sbscr_sale_use" value="Y" <?=checked($data['sale_use'], 'Y')?>> 사용함</label>
				<input type="text" name="sale_ea" value="<?=$data['sale_ea']?>" class="input input_won" style="width:40px"> 회차 이상 구매시 <input type="text" name="sale_percent" value="<?=$data['sale_percent']?>" class="input input_won" style="width:60px"> % 할인
			</td>
		</tr>
	</table>
	<div class="box_bottom left">
		<ul class="list_msg">
			<?php if ($popup=='Y') { ?>
				<li>상품별로 등록 가능한 정기배송 설정세트를 관리합니다.정기배송 설정을 세트로 관리하신 후 상품별로 등록하시거나 일괄로 등록하실 수 있습니다.</li>
			<?php } else { ?>
				<li>배송기간 설정 중 특정기간 설정은 상품별로만 가능합니다.</li>
			<?php } ?>
		</ul>
	</div>
	<div class="box_bottom">
		<span class="box_btn<?php if ($popup=='Y') { ?>_s<?php } ?> blue"><input type="submit" value="확인"></span>
		<?=$close_btn?>
	</div>
</form>
<script type="text/javascript">
	setPoptitle('정기배송 세트 등록/수정 관리');
	$(document).ready(function() {
		var dlv_type = "<?=$data['dlv_type']?>";
		dlvtypeCheck(dlv_type);
	});
	function dlvtypeCheck(type) {
		if(type=='N') {
			$('#dlv_rowspan').attr('rowspan','1');
			$('#sbscr_dlv_date').hide();
		}else if(type=='Y') {
			$('#dlv_rowspan').attr('rowspan','2');
			$('#sbscr_dlv_date').show();
		}
	}

    // 배송기간 '없음' 선택 시 일괄결제 사용 불가
    $(':radio[name=sbscr_dlv_type]').on('change', function() {
        const check_paytype = $(':checkbox[name=sbscr_order_all][value=Y]');
        if (this.value == 'N') {
            check_paytype.prop('disabled', true);
        } else {
            check_paytype.prop('disabled', false);
        }
    });
</script>