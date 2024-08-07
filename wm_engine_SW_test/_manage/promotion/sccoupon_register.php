<?PHP

	/* +----------------------------------------------------------------------------------------------+
	' |  소셜쿠폰 생성
	' +----------------------------------------------------------------------------------------------+*/

	define('sccoupon', true);
	include_once $engine_dir."/_manage/promotion/sccoupon_install.exe.php";

	$no = numberOnly($_GET['no']);
	$cpn_no = numberOnly($_GET['cpn_no']);
	if($no) {
		$data=get_info($tbl['sccoupon'], 'no', $no);
	}  else if ($cpn_no) {
		$data = get_info($tbl['sccoupon'], "no", $cpn_no);
		$data['no'] = '';
	} else {
		$data['is_type']=1;
		$data['date_type']=1;
	}
	$data['issue_type']=1;
	$download_cnt = $pdo->row("select count(*) from `".$tbl[sccoupon_use]."` where `scno` = '$no' and `reg_date` > 0");
	if($download_cnt > 0) {
		$download_cnt = "Y";
		$disabled = "disabled";
	} else {
	   $download_cnt = '';
	}

?>
<form name="couponFrm" method="post" action="./" target="hidden<?=$now?>" onSubmit="return checkFrm()" enctype="multipart/form-data">
	<input type="hidden" name="body" value="promotion@sccoupon.exe">
	<input type="hidden" name="no" value="<?=$no?>">
	<input type="hidden" name="exec" value="">
	<input type="hidden" name="download_cnt" value="<?=$download_cnt?>">
	<div class="box_title first">
		<h2 class="title">소셜쿠폰 생성</h2>
	</div>
	<table class="tbl_row">
		<caption class="hidden">소셜쿠폰 생성</caption>
		<colgroup>
			<col style="width:15%">
		</colgroup>
		<tr>
			<th scope="row"><strong>쿠폰명</strong></h>
			<td><input type="text" name="name" value="<?=inputText($data['name'])?>" class="input input_full"></td>
		</tr>
		<tr>
			<th scope="row"><strong>혜택 구분</strong></th>
			<td>
				<label class="p_cursor"><input type="radio" name="is_type" value="1" <?=checked($data['is_type'], 1)?>> 적립금</label>
				<input type="text" name="milage_prc" value="<?=$data['milage_prc']?>" size="10" class="input"> 원 지급<br>
				<label class="p_cursor"><input type="radio" name="is_type" value="2" <?=checked($data['is_type'], 2)?>> 쿠폰</label>
				<select name="cno">
					<?php
						$today=date("Y-m-d");
						$cpn_q = $pdo->iterator("select `no`, `name` from `$tbl[coupon]` where (`rdate_type`=1 or (`rdate_type`=2 and `rstart_date` <= '$today' and `rfinish_date` >= '$today')) and `is_type`='A'");
						$cpnNum = $cpn_q->rowCount();
						if($cpnNum){
							echo "<option value=\"\">:: 선택 ::</option>";
                            foreach ($cpn_q as $cpn) {
								echo "<option value='$cpn[no]' ".checked($data['cno'], $cpn['no'], 1).">".del_html(stripslashes($cpn[name]))."</option>";
							}
						}else echo "<option value=\"\">쿠폰없음</option>";
					?>
				</select>
				지급
			</td>
		</tr>
		<tr>
			<th scope="row"><strong>쿠폰번호생성</strong></th>
			<td>
				<label class="p_cursor"><input type="radio" name="issue_type" value="1" <?=checked($data['issue_type'], 1)?>> 자동 쿠폰번호</label>
				<input type="text" name="cpn_ea" class="input" size="5" maxlength="5"> 개 <? if($no) { ?>추가<? } else { ?>생성<? } ?> <span class="explain p_color2">(1만 건 이상 생성 시 다소 시간이 소요될 수 있습니다.)</span><br>
				<label class="p_cursor"><input type="radio" name="issue_type" value="2" <?=checked($data['issue_type'], 2)?>> CSV 업로드 일괄<? if($no) { ?> 추가<? } else { ?> 생성<? } ?></label>
				<input type="file" name="cpn_file" class="input" size="20"> <span class="box_btn_s"><a href="<?=$engine_url?>/_engine/common/sccoupon_code_sample.csv" target="_blank">CSV 샘플 다운로드</a></span>
				<ul class="list_info">
					<li>CSV 파일로 업로드 시 코드는 숫자, 영문만 등록할 수 있습니다.</li>
				</ul>
			</td>
		</tr>
		<tr>
			<th scope="row"><strong>교환기간</strong></th>
			<td>
				<label class="p_cursor"><input type="radio" name="date_type" id="date_type" value="1" <?=checked($data[date_type],1)?>> 무제한</label><br>
				<label class="p_cursor"><input type="radio" name="date_type" id="date_type" value="2" <?=checked($data[date_type],2)?>> 기간 설정</label>
				<input type="text" name="start_date" value="<?=$data[start_date]?>" size="10" readonly class="input datepicker">
				~
				<input type="text" name="finish_date" value="<?=$data[finish_date]?>" size="10" readonly class="input datepicker">
			</td>
		</tr>
		<tr>
			<th scope="row">관리자 메모</th>
			<td><textarea class="txta" name="memo"><?=$data['memo']?></textarea></td>
		</tr>
	</table>
	<div class="box_middle2 left">
		<ul class="list_info">
			<li>발급된 쿠폰은 교환기간 및 관리자 메모만 수정할 수 있습니다.</li>
		</ul>
	</div>
	<div class="box_bottom">
		<span class="box_btn blue"><input type="submit" value="확인"></span>
		<?if($no){?>
		<span class="box_btn gray"><input type="button" value="취소" onclick="goM('promotion@sccoupon')"></span>
		<span class="box_btn gray"><input type="button" value="복사" onclick="couponCopy()"></span>
		<?}?>
	</div>
</form>

<script type="text/javascript">
	var f=document.couponFrm;
	var download_cnt = '<?=$download_cnt?>';
	var no = '<?=$no?>';

	if(download_cnt) {
		f.name.disabled = true;
		f.milage_prc.disabled = true;
		f.cpn_ea.disabled = true;
		f.cpn_file.disabled = true;

		$(f.is_type).prop('disabled', true);
		$(f.cno).prop('disabled', true);
		$(f.issue_type).prop('disabled', true);
	}
	function checkFrm(){
		if(!checkBlank(f.name,'소셜 쿠폰명을 입력해주세요.')) return false;
		if(f.is_type[0].checked == true && !checkBlank(f.milage_prc, '지급 적립액을 입력해주세요.')) return false;
		if(f.is_type[1].checked == true && !checkSel(f.cno, '지급 쿠폰을 입력해주세요.')) return false;

		<? if(empty($no)) { ?>
		if(f.issue_type[0].checked == true && !checkBlank(f.cpn_ea, '생성 쿠폰갯수를 입력해주세요.')) return false;
		if(f.issue_type[1].checked == true && !checkBlank(f.cpn_file, '생성할 csv파일을 입력해주세요.')) return false;
		<? } ?>

		if(f.date_type[1].checked == true) {
			if (!checkBlank(f.start_date,'교환 시작일을 입력해주세요.')) return false;
			if (!checkBlank(f.finish_date,'교환 종료일을 입력해주세요.')) return false;
		}
	}

	function couponCopy() {
		if(!confirm('쿠폰을 복사하시겠습니까?')) return false;
		if(no) {
			window.open('./?body=promotion@sccoupon_register&cpn_no='+no);
		}
	}
</script>