<?PHP

	/* +----------------------------------------------------------------------------------------------+
	' |  상품Q&A
	' +----------------------------------------------------------------------------------------------+*/

    use Wing\common\EditorFile;

	include_once $engine_dir."/_engine/include/shop_detail.lib.php";

	if(!isTable($tbl['often_comment'])) {
		include_once $engine_dir.'/_config/tbl_schema.php';
		$pdo->query($tbl_schema['often_comment']);
	}

	$no = numberOnly($_GET['no']);
	$notice = addslashes($_GET['notice']);

	if(!$notice) {
		checkBlank($no,'필수값(1)을 입력해주세요.');
	}

	if($no) {
		$data = $pdo->assoc("select * from {$tbl['qna']} where no='$no'");
		checkBlank($data[no],'필수값(2)을 입력해주세요.');
		$notice=$data[notice];
		if($data['member_no'] > 0) $blacklist = $pdo->row("select blacklist from `$tbl[member]` where `no` = '$data[member_no]'");
	}

	$prd=get_info($tbl[product],"no",$data[pno]);

	if($admin['level'] == 4) {
		if($prd['partner_no'] != $admin['partner_no']) msg('열람 권한이 없습니다.', 'close');
	}

	$sms_chk=get_info($tbl[sms_case],"case",8);
	$e1=$e2="";
	if($sms_chk[use_check]=="Y") {
		$e2="checked";
	}
	else {
		$e1="‘상품질문답변시 발송’이 사용안함으로 설정되어 있습니다.";
		$e1 = "<span class='list_info2 warning'>$e1</span>";
	}

	if($data[member_no]) {
		$pmember=get_info($tbl[member],"no",$data[member_no]);
	}
	else {
		if($data['sms']) $pmember['cell'] = $data['sms'];
		if($data['email']) $pmember['email'] = $data['email'];
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
	if(!$data['cell'] && $pmember['cell']) {
		$data['cell'] = $pmember['cell'];
	}

	$use_editor = ($cfg['product_qna_use_editor'] == 'Y' || strip_tags($data['content']) != $data['content']) ? true : false;
	if($data['checkout_no'] > 0 || $data['smartstore_no'] > 0 || $data['talkstore_qnaId'] || strpos($data['external_id'], 'talkpay') === 0) { // 네이버페지 연동 QNA 는 에디터 사용 불가
		$use_editor = false;
	}
	$answer_date = ($data['answer_date'] > 0) ? date('Y-m-d H:i:s', $data['answer_date']).' '.$data['answer_id'] : '미답변';

	$file_url = getFileDir($data['updir']);
	$upfiles = array();
	if($data) {
		foreach($data as $key => $val) {
			if(strpos($key, 'upfile') === 0 && $val) {
				$upfiles[$key] = array(
					'name' => $val,
					'path' => $file_url.'/'.$data['updir'].'/'.$val
				);
			}
		}
	}
	$_SESSION['adm_view'] = 'qna@'.$no;

	addPrivacyViewLog(array(
		'page_id' => 'board',
		'page_type' => 'view',
		'target_id' => $data['member_id'],
		'target_cnt' => 1
	));

	// 자주 쓰는 댓글
	$ares = $pdo->iterator("select * from `$tbl[often_comment]` where `cate`='qna' order by `no`");

    // 에디터 파일
    $editor_file = new EditorFile();
    $editor_file->setId('product_qna', $no);

?>
<script language="javasscript" type="text/javascript" src="<?=$engine_url?>/_engine/R2Na/R2Na.js"></script>
<form method="post" action="./index.php" target="hidden<?=$now?>" onSubmit="return checkPrdQna(this)" enctype="multipart/form-data">
	<input type="hidden" name="no" value="<?=$no?>">
	<input type="hidden" name="exec" value="edit">
	<input type="hidden" name="body" value="member@product_qna_update.exe">
	<input type="hidden" name="pno" value="<?=$data[pno]?>">
	<input type="hidden" name="notice" value="<?=$notice?>">
    <input type="hidden" name="editor_code" value="<?=$editor_file->getId()?>">
	<div class="box_qna" <?if($notice=="Y"){?>style="width:800px;"<?}?>>
		<div class="question" <?if($notice=="Y"){?>style="width:100%;"<?}?>>
			<h2>질문</h2>
			<table class="tbl_row">
				<caption class="hidden">상품 문의</caption>
				<colgroup>
					<col style="width:15%;">
					<col style="width:35%;">
					<col style="width:15%;">
					<col style="width:35%;">
				<colgroup>
				<tr>
					<th scope="row">상품</th>
					<td colspan="3">
						<?if($prd[no]){?>
						<a href="<?=$root_url?>/shop/detail.php?pno=<?=$prd[hash]?>" target="_blank"><?=cutStr(stripslashes($prd[name]),100)?></a>
						<a href="./?body=product@product_register&pno=<?=$prd[no]?>" target="_blank"><img src="<?=$engine_url?>/_manage/image/common/icon_edit.png" alt="상품 수정" style="vertical-align:top;"></a>
						<?}else{?>
						연동된 상품이 없습니다.
						<?}?>
					</td>
				</tr>
				<tr>
					<th scope="row">제목</th>
					<td colspan="3">
						<input type="text" name="title" value="<?=inputText($data[title])?>" class="input input_full">
						<?if($notice=="N"){?>
						<input type="checkbox" name="secret" value="Y" <?=checked($data[secret],"Y")?>> 비밀글
						<?}?>

					</td>
				</tr>
				<?if($data['cate']){?>
				<tr>
					<th scope="row">분류</th>
					<td colspan="3"><?=outPutCate("qna",$data['cate']);?></td>
				</tr>
				<?}?>
				<?if($notice=="N") {?>
				<tr>
					<th scope="row">이름</th>
					<td>
						<input type="text" name="name" value="<?=inputText($data[name])?>" class="input" size="6">
						<?php if ($admin['level'] < 4) { if($data['member_no']){?>
						<a href="javascript:viewMember('<?=$data[member_no]?>','<?=$data[member_id]?>')">(<?=$data[member_id]?>) <?=blackIconPrint($blacklist)?></a>
						<?}elseif($notice=="N"){?>
                        (비회원)
        				<?}}?>
					</td>
					<th scope="row">연락처</th>
					<td><?=$data['cell']?></td>
				</tr>
				<?}?>
				<?if($no > 0) {?>
				<tr>
					<th scope="row">등록일시</th>
					<td><?=date('Y-m-d H:i:s', $data['reg_date'])?></td>
					<th>답변일시</th>
					<td><?=$answer_date?></td>
				</tr>
				<?}?>
				<?if($data['ono']){?>
				<tr>
					<th scope="row">주문</th>
					<td colspan="3"><a href="javascript:;" onclick="viewOrder('<?=$data[ono]?>')"><?=$data[ono]?></a></td>
				</tr>
				<?}?>
				<tr>
					<th scope="row">질문</th>
					<td colspan="3"><textarea id='content' name="content" class="txta" rows="15" cols='120' style="height:250px;"><?=stripslashes($data['content'])?></textarea></td>
				</tr>
				<tr>
					<th scope="row">첨부파일</th>
					<td colspan="3">
						<div class="upload_box">
							<ul class="tab">
								<li><a onclick="upload_view(0,'question')" class="active">첨부파일</a></li>
								<li><a onclick="upload_view(1,'question')">미리보기</a></li>
								<li><a onclick="upload_view(2,'question')">업로드</a></li>
							</ul>
							<div class="upload_cnt upload_cnt0">
								<ul>
									<?foreach($upfiles as $key => $val) {?>
										<?if($key == "upfile1" || $key == "upfile2") {?>
										<li class="list_<?=$key?>"><a href="<?=$val['path']?>" target="_blank"><?=$val['name']?></a> <a href="#" onclick="removeAttach(<?=$no?>, '<?=$key?>'); return false;" class="close">삭제	</a></li>
										<?}?>
									<?}?>
								</ul>
							</div>
							<div class="upload_cnt upload_cnt1">
								<ul>
									<?foreach($upfiles as $key => $val) {?>
										<?if($key == "upfile1" || $key == "upfile2") {?>
										<li class="list_<?=$key?>"><a href="<?=$val['path']?>" target="_blank"><img src="<?=$val['path']?>" alt=""></a></li>
										<?}?>
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
			</table>
		</div>
		<?if($notice=="N") {?>
		<div class="answer">
			<h2>답변</h2>
			<table class="tbl_row">
				<caption class="hidden">상품 문의</caption>
				<colgroup>
					<col style="width:15%;">
					<col style="width:35%;">
					<col style="width:15%;">
					<col style="width:35%;">
				<colgroup>
				<tr>
					<th scope="row">자주쓰는 댓글</th>
					<td colspan="3">
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
					<td colspan="3">
                        <?php if ($data['smartstore_no'] && $data['answer_ok'] == 'Y') { ?>
                        <ul class="list_msg">
                            <li>스마트스토어 내에서 작성한 답변은 확인할 수 없습니다.</li>
                        </ul>
                        <?php } ?>
                        <textarea id='answer' name="answer" class="txta" style="height:363px;"><?=stripslashes($data['answer'])?></textarea>
                    </td>
				</tr>
				<tr>
					<th scope="row">답변 첨부파일</th>
					<td colspan="3">
						<div class="upload_box">
							<ul class="tab">
								<li><a onclick="upload_view(0,'answer')" class="active">첨부파일</a></li>
								<li><a onclick="upload_view(1,'answer')">미리보기</a></li>
								<li><a onclick="upload_view(2,'answer')">업로드</a></li>
							</ul>
							<div class="upload_cnt upload_cnt0">
								<ul>
									<?foreach($upfiles as $key => $val) {?>
										<?if($key == "upfile3" || $key == "upfile4") {?>
										<li class="list_<?=$key?>"><a href="<?=$val['path']?>" target="_blank"><?=$val['name']?></a> <a href="#" onclick="removeAttach(<?=$no?>, '<?=$key?>'); return false;" class="close">삭제</a></li>
										<?}?>
									<?}?>
								</ul>
							</div>
							<div class="upload_cnt upload_cnt1">
								<ul>
									<?foreach($upfiles as $key => $val) {?>
										<?if($key == "upfile3" || $key == "upfile4") {?>
										<li class="list_<?=$key?>"><a href="<?=$val['path']?>" target="_blank"><img src="<?=$val['path']?>" alt=""></a></li>
										<?}?>
									<?}?>
								</ul>
							</div>
							<div class="upload_cnt upload_cnt2">
								<input type="file" name="upfile3" value="">
								<input type="file" name="upfile4" value="">
							</div>
						</div>
					</td>
				</tr>
				<tr>
					<th scope="row">관리자메모</th>
					<td colspan="3"><textarea name="mng_memo" class="txta" rows="6" cols='120'><?=stripslashes($data['mng_memo'])?></textarea></td>
				</tr>
				<tr>
					<th scope="row">알림</th>
					<td colspan="3" class="push">
						<p>
							<label for="sms"><input type="checkbox" name="sms" id="sms" value="<?=$pmember[cell]?>" <?=$e2?>> 답변완료 SMS 알림 </label>
							<?=$e1?>
							<?if($sms_chk[use_check]=="N") {?>
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
		</div>
		<?}?>
	</div>
	<div class="fb_btn">
		<span class="box_btn blue"><input type="submit" value="확인"></span>
		<span class="box_btn"><input type="button" value="닫기" onclick="wclose();"></span>
	</div>
</form>
<div style="clear:both;"></div>

<script type="text/javascript">
	this.focus();

	$(window).ready(function() {
		<?if($use_editor) {?>
    		var editor_code = '<?=$editor_file->getId()?>';
			<?if($notice == 'Y') {?>
			seCall('content', editor_code, 'product_qna');
			<?} else {?>
			seCall('content', editor_code, 'product_qna');
			seCall('answer', editor_code+'_a', 'product_qna');
			<?}?>
		<?}?>

		$('.R2Tip').mouseover(function() {
			new R2Tip(this, this.alt, null, event);
		});
	});

	function upload_view(no,obj) {
		var tabs = $('.'+obj+' .upload_box .tab').find('li');
		tabs.each(function(idx) {
			var detail = $('.'+obj+' .upload_cnt'+idx);
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
			$.post('./index.php', {'body':'member@product_qna_update.exe', 'exec': 'remove_attach', 'no':no, 'key':key}, function(r) {
				if(r.result == 'success') {
					$('.list_'+key).remove();
				} else {
					window.alert(r.message);
				}
			});
		}
	}
</script>