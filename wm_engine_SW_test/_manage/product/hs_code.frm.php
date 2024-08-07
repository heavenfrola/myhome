<?php

	$no = numberOnly($_GET['no']);

	if($no) {
		$title = "수정";

		$data = get_info($tbl['hs_code'], "no", $no);
		if(!$data['no']) msg("존재하지 않는 자료입니다", "close");
	}else {
		$title = "추가";
	}

	$ww=($pop) ? "1000px" : "100%";
	$btns=($pop) ? "_s" : "";

?>
<?php if ($body == 'product@hs_code.frm') { ?>
<style type="text/css" title="">
body {background:none;}
.box_title{position:relative;}
#hs_link{position:absolute;right:10px;top:1px;}
#hs_link a{color:red;}
</style>
<?php } ?>

<form name="hsfieldFrm" method="post" action="<?=$_SERVER['PHP_SELF']?>" target="hidden<?=$now?>" onSubmit="return checkPrdHsCode(this)">
	<input type="hidden" name="body" value="product@hs_code.exe">
	<input type="hidden" name="no" value="<?=$no?>">
	<input type="hidden" name="exec" value="">
	<div class="box_title first">
		<h2 class="title">HS코드 <?=$title?></h2>
	</div>
	<table class="tbl_row" style="width:<?=$ww?>">
		<caption class="hidden">HS코드 <?=$title?></caption>
		<colgroup>
			<col style="width:15%">
		</colgroup>
		<tr>
			<th scope="row"><strong>항목명</strong></th>
			<td><input type="text" name="name" value="<?=inputText($data['name'])?>" class="input input_full"></td>
		</tr>
		<tr>
			<th scope="row"><strong>HS 코드</strong></th>
			<td><input type="text" name="hs_code" value="<?=inputText($data['hs_code'])?>" class="input input_full">
				<p class="explain">
					<i class="icon_info"></i> <span class="explain">관세율은 <a href="https://unipass.customs.go.kr/clip/index.do" target="_blank" class="p_color">관세법령정보포털 홈페이지</a> > 세계HS > 관세율표를 참고하시기 바랍니다.</span>
				</p>
			</td>
		</tr>
	</table>
	<div class="box_bottom">
		<span class="box_btn<?=$btns?> blue"><input type="submit" value="확인"></span>
		<?=$close_btn?>
	</div>
</form>
<script>
	function checkPrdHsCode(f){
		if(!checkBlank(f.name,'항목명을 입력하세요.')) return false;
		if(!checkBlank(f.hs_code,'HS코드를 입력하세요.')) return false;
	}
</script>