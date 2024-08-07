<?PHP

	/* +----------------------------------------------------------------------------------------------+
	' |  1:1고객상담 상세보기
	' +----------------------------------------------------------------------------------------------+*/

    use Wing\common\EditorFile;

	if(!isTable($tbl['often_comment'])) {
		include_once $engine_dir.'/_config/tbl_schema.php';
		$pdo->query($tbl_schema['often_comment']);
	}

	$no = numberOnly($_GET['no']);

	checkBlank($no,'1:1문의글이 존재 하지 않습니다.');
	$data=get_info($tbl['cs'],"no",$no);
	checkBlank($data['no'],'1:1문의글이 존재 하지 않습니다.');
	if(!$data['member_id']) $data['member_id']="비회원";

	$sms_chk = $pdo->row("select use_check from $tbl[sms_case] where `case` = '8'");
	$e1 = $e2 = "";
	if($sms_chk == "Y") {
		$e2 = "checked";
	}
	else {
		$e1 = "‘상품질문답변시 발송’이 사용안함으로 설정되어 있습니다.";
		$e1 = "<span class='list_info2 warning'>$e1</span>";
	}

	if($data['member_no']) {
		$pmember = $pdo->assoc("select cell,email,blacklist from $tbl[member] where no = '$data[member_no]'");
		$data['blacklist'] = $pmember['blacklist'];
	} else {
		$pmember['cell'] = preg_replace('/[^0-9]/', '', $data['phone']);
		$pmember['email'] = trim($data['email']);
	}
	if(!$pmember['cell']) {
		$e1 = $data['name']."님의 휴대폰 번호가 등록되어 있지 않습니다.";
		$e1 = "<span class='list_info2 warning'>$e1</span>";
		$e2 = "disabled";
	}
	if(!$pmember['email']) {
		$e3 = $data['name']."님의 메일주소가 등록되어있지 않습니다.";
		$e3 = "<span class='list_info2 warning'>$e3</span>";
		$e4 = "disabled";
	}

	$file_url = getFileDir($data['updir']);
	$upfiles = array();
	foreach($data as $key => $val) {
		if(strpos($key, 'upfile') === 0 && $val) {
			$upfiles[$key] = array(
				'name' => $val,
				'path' => $file_url.'/'.$data['updir'].'/'.$val
			);
		}
	}
	$_SESSION['adm_view'] = 'cs@'.$no;

	addPrivacyViewLog(array(
		'page_id' => 'board',
		'page_type' => 'view',
		'target_id' => $data['member_id'],
		'target_cnt' => 1
	));

	//자주 쓰는 댓글
	$ares = $pdo->iterator("select * from `$tbl[often_comment]` where `cate`='cs' order by `no`");

	// 에디터
	if($data['content'] == strip_tags($data['content'])) {
		$data['content'] = nl2br($data['content']);
	}
	$editor_code = '';
    $editor_file = new EditorFile();
    $editor_file->setId('counsel_answer', $no);

?>
<form method="post" action="<?=$_SERVER['PHP_SELF']?>" target="hidden<?=$now?>" enctype='multipart/form-data' onSubmit="return checkReplyCS(this)" class="pop_width fixbtn">
	<input type="hidden" name="body" value="member@1to1.exe">
	<input type="hidden" name="no" value="<?=$no?>">
	<input type="hidden" name="exec" value="reply">
    <input type="hidden" name="editor_code" value="<?=$editor_file->getId()?>">

	<table class="tbl_row">
		<caption class="hidden">상담 답변 달기</caption>
		<colgroup>
			<col style="width:15%">
			<col style="width:85%;">
		</colgroup>
		<tr>
			<th scope="row">제목</th>
			<td><?=stripslashes($data['title'])?></td>
		</tr>
		<tr>
			<th scope="row">분류</th>
			<td><?=$_cust_cate[$data['cate1']][$data['cate2']]?></td>
		</tr>
		<?if($data['ono']){?>
		<tr>
			<th scope="row">주문번호</th>
			<td><a href="javascript:;" onClick="viewOrder('<?=$data['ono']?>')"><?=$data['ono']?></a></td>
		</tr>
		<?}?>
		<tr>
			<th scope="row">이름</th>
			<td><a href="javascript:;" onClick="viewMember('<?=$data['member_no']?>','<?=$data['member_id']?>')"><?=$data['name']?></b> (<?=$data['member_id']?>) <?=blackIconPrint($data['blacklist'])?></a></td>
		</tr>
		<?if($data['email']) {?>
		<tr>
			<th scope="row">이메일</th>
			<td><?=$data['email']?></td>
		</tr>
		<?}?>
		<?if($no > 0) {?>
		<tr>
			<th scope="row">등록일시</th>
			<td><?=date('Y-m-d H:i:s', $data['reg_date'])?></td>
		</tr>
		<?}?>
		<?if($data['reason']) {?>
		<tr>
			<th scope="row">요청 사유</th>
			<td><?=stripslashes($data['reason'])?></td>
		</tr>
		<?}?>
		<?if($data['reply_date'] > 0) {?>
		<tr>
			<th>답변일시</th>
			<td><?=date('Y-m-d H:i:s', $data['reply_date'])?></td>
		</tr>
		<?}?>
		<tr>
			<th scope="row">문의내용</th>
			<td>
				<div id="question" style="width:100%; overflow: hidden;"><?=stripslashes($data['content'])?></div>
			</td>
		</tr>
		<tr>
		<th scope="row">자주쓰는 댓글</th>
			<td>
				<select name="often_comment" onchange = "getOftenComment(this.value);">
					<option value="">선택안함</option>
                    <?php foreach ($ares as $adata) {?>
						<option value="<?=$adata['no']?>"><?=stripslashes($adata['title'])?></option>
					<?}?>
				</select>
			</td>
		</tr>
		<tr>
			<th scope="row">답변</th>
			<td>
				<textarea name="answer" id="answer" class="txta" cols="70" rows="10"><?=stripslashes($data['reply'])?></textarea><br>
				<?if($data['reply_date']) {?>
				<label class="p_cursor"><input type="checkbox" name="chg_date" value="Y"> 현재시간으로 답변일시 변경</label>
				<?}?>
			</td>
		</tr>
		<tr>
			<th scope="row">첨부파일</th>
			<td>
				<div class="upload_box">
					<ul class="tab">
						<li><a onclick="upload_view(0,this)" class="active">첨부파일</a></li>
						<li><a onclick="upload_view(1,this)">미리보기</a></li>
						<li><a onclick="upload_view(2,this)">업로드</a></li>
					</ul>
					<div class="upload_cnt upload_cnt0">
						<ul>
							<?foreach($upfiles as $key => $val) {?>
							<li class="list_<?=$key?>"><a href="<?=$val['path']?>" target="_blank"><?=$val['name']?></a> <a href="#" onclick="removeAttach(<?=$no?>, '<?=$key?>'); return false;" class="close">삭제</a></li>
							<?}?>
						</ul>
					</div>
					<div class="upload_cnt upload_cnt1">
						<ul>
							<?foreach($upfiles as $key => $val) {?>
							<li class="list_<?=$key?>"><a href="<?=$val['path']?>" target="_blank"><img src="<?=$val['path']?>" alt=""></a></li>
							<?}?>
						</ul>
					</div>
					<div class="upload_cnt upload_cnt2">
						<input type="file" name="upfile1" value="">
						<input type="file" name="upfile2" value="">
					</div>
				</div>
			</td>
		</tr>
		<tr>
			<th scope="row">관리자메모</th>
			<td><textarea name="mng_memo" class="txta" rows="6" cols='120'><?=stripslashes($data['mng_memo'])?></textarea></td>
		</tr>
		<tr>
			<th scope="row">알림</th>
			<td>

				<p>
					<label for="sms"><input type="checkbox" name="sms" id="sms" value="<?=$pmember['cell']?>" <?=$e2?>> 답변완료 SMS 알림 </label>
					<?=$e1?>
					<?if($sms_chk == 'Y') {?>
						<a href="/_manage/?body=member@sms_config" target="_blank" style="color:#26ace2; font-size:11px; text-decoration:underline;">설정변경</a>
					<?}?>
				</p>
				<p>
					<label for="email"><input type="checkbox" name="email" id="email" value="<?=$pmember['email']?>" <?=$e4?>> 답변완료 이메일 알림</label>
					<?=$e3?>
				</p>

			</td>
		</tr>
	</table>
	<div class="fb_btn">
		<span class="box_btn blue"><input type="submit" value="확인"></span>
		<span class="box_btn"><input type="button" value="닫기" onclick="wclose();"></span>
	</div>
</form>
<script type="text/javascript" src="<?=$engine_url?>/_engine/smartEditor/js/HuskyEZCreator.js"></script>
<script type="text/javascript" src="<?=$engine_url?>/_engine/R2Na/R2Na.js"></script>
<script language="JavaScript">
    var editor_code = '<?=$editor_file->getId()?>';
	var editor = null;
	$(window).load(function() {
		$('[name=reply_sms]').each(function(idx) {
			if(idx == 1) this.checked = true;
		});

		$('#question').find('img').each(function() {
			if($(this).width() > $(this).parent().innerWidth()) {
				$(this).width($(this).parent().innerWidth())
			}
		});
		<?php if (isset($cfg['counsel_use_editor']) == true && $cfg['counsel_use_editor'] == 'Y') { ?>
		if(editor_code) {
			seCall('answer', editor_code, 'counsel_answer');
		}
		<?php } ?>
	});

	this.focus();

	function upload_view(no,obj) {
		var tabs = $('.upload_box .tab').find('li');
		tabs.each(function(idx) {
			var detail = $('.upload_cnt'+idx);
			var active = $(this).find('a');
			if(no == idx) {
				active.addClass('active');
				detail.css('display', 'block');
			} else {
				active.removeClass('active');
				detail.css('display', 'none');
			}
		})
	}

	function removeAttach(no, key) {
		if(confirm('선택한 첨부파일을 삭제하시겠습니까?') == true) {
			$.post('./index.php', {'body':'member@1to1.exe', 'exec': 'remove_attach', 'no':no, 'key':key}, function(r) {
				if(r.result == 'success') {
					$('.list_'+key).remove();
				} else {
					window.alert(r.message);
				}
			});
		}
	}
</script>