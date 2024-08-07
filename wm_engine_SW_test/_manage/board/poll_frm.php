<?PHP

	/* +----------------------------------------------------------------------------------------------+
	' |  설문조사 등록
	' +----------------------------------------------------------------------------------------------+*/

	$no = numberOnly($_GET['no']);
	if($no) {
		$data=get_info($tbl[poll_config],"no",$no);
		if(!$data[no]) {
			msg("존재하지 않는 자료입니다");
		}
		$data[updir]="_data/poll_imgs";
	}
	else {
		$data[sdate]=$data[fdate]=date("Y-m-d",$now);
	}

?>
<form name="" method="post" enctype="multipart/form-data" action="./" target="hidden<?=$now?>" onSubmit="return checkPoll(this)">
	<input type="hidden" name="body" value="board@poll.exe">
	<input type="hidden" name="no" value="<?=$no?>">
	<div class="box_title first">
		<h2 class="title">설문조사 등록</h2>
	</div>
	<table class="tbl_row">
		<caption class="hidden">설문조사 등록</caption>
		<colgroup>
			<col style="width:15%">
			<col>
		</colgroup>
		<tr>
			<th scope="row"><strong>제목</strong></th>
			<td><input type="text" name="title" value="<?=inputText($data[title])?>" class="input" size="50"></td>
		</tr>
		<tr>
			<th scope="row">권한</th>
			<td>
				<label class="p_cursor"><input type="radio" name="auth" value="1" <?=checked($data[auth],1).checked($data[auth],"")?>> 누구나</label>
				<label class="p_cursor"><input type="radio" name="auth" value="2" <?=checked($data[auth],2)?>> 회원</label>
			</td>
		</tr>
		<tr>
			<th scope="row">중복 투표</th>
			<td>
				<label class="p_cursor"><input type="radio" name="dupl" value="1" <?=checked($data[dupl],1).checked($data[dupl],"")?>> 가능</label>
				<label class="p_cursor"><input type="radio" name="dupl" value="2" <?=checked($data[dupl],2)?>> 불가</label>
				<ul class="list_msg">
					<li>회원 전용일 경우 : 투표 여부를 DB에 저장</li>
					<li>누구나 가능할 경우 : 투표 여부를 사용자 컴퓨터 쿠키로 저장</li>
				</ul>
			</td>
		</tr>
		<tr>
			<th scope="row">기간</th>
			<td>
				<input type="text" name="sdate" value="<?=$data[sdate]?>" size="10" class="input datepicker"> ~ <input type="text" name="fdate" value="<?=$data[fdate]?>" size="10" class="input datepicker">
			</td>
		</tr>
		<tr>
			<th scope="row">적립금 부여</th>
			<td>
				<input type="text" name="milage" value="<?=$data[milage]?>" class="input" size="10"> 점
				<p class="explain">중복투표 불가로 선택하셨을때만 적용됩니다</p>
			</td>
		</tr>
		<tr>
			<th scope="row"><strong>문항</strong></th>
			<td>
				<dl>
					<dt>입력한 문항만 사용합니다</dt>
					<?php
					$res = $pdo->iterator("select * from `$tbl[poll_item]` where `ref`='$no' order by sort limit 10");
                    for($ii=1; $ii<=10; $ii++) {
                        $item = $res->current();
                        $res->next();
						$label = addZero($ii, 2);
					?>
					<dd style="padding:2px 0;"><input type="hidden" name="item_no[]" value="<?=$item[no]?>"></dd>
					<?=$label?> - <input type="text" name="item[]" id="item" value="<?=inputText($item[title])?>" class="input" size="40">
					<?}?>
				</dl>
			</td>
		</tr>
		<tr>
			<th scope="row">내용 (Html+Text)</th>
			<td>
				<textarea name="content" rows="10" cols="100" class="txta"><?=$data[content]?></textarea>
			</td>
		</tr>
		<tr>
			<th scope="row">이미지삽입</th>
			<td>
				<input type="file" name="upfile1" class="input input_full">
				<?=delImgStr($data,1)?>
			</td>
		</tr>
	</table>
	<div class="box_bottom">
		<span class="box_btn blue"><input type="submit" value="확인"></span>
		<span class="box_btn gray"><input type="button" value="취소" onclick="history.back();"></span>
	</div>
</form>

<script type="text/javascript">
	function checkPoll(f){
		if(!checkBlank(f.title, '설문 제목을 입력해주세요.')) return false;
		ic=0;
		for (i=0; i<f.item.length; i++) {
			if (f.item[i].value) {
				ic=1;
				break
			}
		}
		if (ic<1) {
			alert('설문 문항을 하나 이상 입력하세요');
			return false;
		}
	}
</script>