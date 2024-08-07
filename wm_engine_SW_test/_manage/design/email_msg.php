<?PHP

	/* +----------------------------------------------------------------------------------------------+
	' |  자동메일내용 편집
	' +----------------------------------------------------------------------------------------------+*/

	$_langs = array(
		'kor' => '한국어',
		'eng' => '영어',
		'ch1' => '중국어(간체)',
		'ch2' => '중국어(번체)'
	);
	$mail_case = numberOnly($_GET['mail_case']);

	$_template_ex = false;
	foreach($_langs as $k=>$v){
		if(file_exists($root_dir."/_template/mail/mail_".$k.".php")) $_template_ex = true;
		if(!$_template_ex && file_exists($engine_dir."/_engine/skin_module/default/MODULE/mail_".$k.".wsm")) $_template_ex = true;
	}

	if($_template_ex && !$cfg['mail_lang']) $cfg['mail_lang'] = 'kor';

	$ctype = numberOnly($_GET['ctype']);
	if(!$ctype) $ctype = 1;
	${'active_ctype'.$ctype} = 'active';

	if($ctype == 1) {
		$_mail_menu_arr = array(1=>"회원가입", 13=>"회원가입 인증", 14=>"이메일 정보수정 인증", 19=>"인증번호 발송", 16=>"비밀번호 변경", 12=>"휴면회원 사전안내", 15=>"광고성정보 수신동의 안내", 21=>"광고성정보 수신동의 변경 안내", 17=>"적립금 소멸(정보성)",  18=>"적립금 소멸(광고성)", 22=>"개인정보 이용내역", 24=>"생일쿠폰 발행");
	} else if($ctype == 2) {
		if($cfg['use_partner_shop'] == 'Y') {
			$_mail_menu_arr = array(2=>"주문내역확인",23=>"주문내역확인(입점사)", 3=>"상품배송", 4=>"배송완료");
		} else {
			$_mail_menu_arr = array(2=>"주문내역확인", 3=>"상품배송", 4=>"배송완료");
		}
	} else if($ctype == 4) {
		$_mail_menu_arr = array(9=>"문의글 답변", 6=>"기타안내");
	}

	$preview = $cfg['email_logo_img'] ? "<span class=\"box_btn_s\"><a href=\"{$cfg['email_logo_img']}\" target=\"_blank\">미리보기</a></span>" : '';

?>
<?php if ($_template_ex) { ?>
<form name="frm" method="post" action="<?=$_SERVER['PHP_SELF']?>" target="hidden<?=$now?>" onsubmit="printLoading()">
	<input type="hidden" name="body" value="config@config.exe">
	<input type="hidden" name="exec">
	<div class="box_title first">
		<h2 class="title">자동 이메일 언어 설정</h2>
	</div>
	<table class="tbl_row">
		<caption class="hidden">자동 이메일 언어 설정</caption>
		<colgroup>
			<col style="width:15%">
		</colgroup>
		<tr>
			<th scope="row">언어 선택</th>
			<td>
				<?php foreach ($_langs as $key=>$val){ ?>
					<label><input type="radio" name="mail_lang" id="mail_lang_<?=$key?>" value="<?=$key?>" <?=$cfg['mail_lang']==$key?'checked':''?>/><?=$val?></label>
				<?php } ?>
			</td>
		</tr>
	</table>
	<div class="box_bottom">
		<span class="box_btn blue"><input type="submit" value="확인"></span>
	</div>
</form>
<br/>
<?php } ?>

<form name="frm" method="post" action="<?=$_SERVER['PHP_SELF']?>" target="hidden<?=$now?>" enctype="multipart/form-data" onsubmit="printLoading()">
	<input type="hidden" name="body" value="config@config.exe">
	<input type="hidden" name="exec">
	<input type="hidden" name="config_code" value="email_config">
	<div class="box_title first">
		<h2 class="title">자동 이메일 디자인 설정</h2>
	</div>
	<table class="tbl_row">
		<caption class="hidden">자동 이메일 디자인 설정</caption>
		<colgroup>
			<col style="width:15%">
		</colgroup>
		<tr>
			<th scope="row">메일 로고</th>
			<td>
				<input type="file" name="email_logo_img" class="input input_full"> <?=$preview?>
				<div class="list_info">
					<p>등록된 메일 로고의 경우 치환문자 {로고} 또는 {로고URL}를 통해 사용할 수 있습니다.</p>
				</div>
			</td>
		</tr>
	</table>
	<div class="box_bottom">
		<span class="box_btn blue"><input type="submit" value="확인"></span>
	</div>
</form>
<br>
<form name="frm2" method="post" action="<?=$_SERVER['PHP_SELF']?>" target="hidden<?=$now?>" onSubmit="return ckFrm(this)">
	<input type="hidden" name="body" value="design@email_msg.exe">
	<input type="hidden" name="mail_case" value="<?=$mail_case?>">
	<input type="hidden" name="exec">
	<div class="box_title first">
		<h2 class="title">자동 이메일내용 편집</h2>
	</div>
	<div class="box_middle sort">
		<ul class="tab_sort">
			<li class="<?=$active_ctype1?>"><a href="?body=<?=$_GET['body']?>&ctype=1">회원</a></li>
			<li class="<?=$active_ctype2?>"><a href="?body=<?=$_GET['body']?>&ctype=2">주문</a></li>
			<li class="<?=$active_ctype4?>"><a href="?body=<?=$_GET['body']?>&ctype=4">관리</a></li>
		</ul>
	</div>
	<table class="tbl_row">
		<caption class="hidden">자동 이메일내용 편집</caption>
		<colgroup>
			<col style="width:15%">
		</colgroup>

		<tr>
			<th scope="row">항목 선택</th>
			<td>
				<?php foreach($_mail_menu_arr as $key=>$val) { ?>
				<label class="p_cursor <?=(($mail_case==$key) ? 'p_color' : '')?>" style="white-space:nowrap">
					<input type="radio" name="mail_case" id="mail_case_<?=$key?>" value="<?=$key?>" onclick="location.href='./?body=<?=$body?>&mail_case=<?=$key?>&ctype=<?=$ctype?>';" <?=checked($mail_case,$key)?>> <?=$val?>
				</label>
				<?php } ?>
			</td>
		</tr>
		<?php if ($mail_case) {
			include_once $engine_dir."/_engine/include/mail.lib.php";
			$title_value = $mail_title[$mail_case];

		?>
		<?php if ($mail_case!=6) { ?>
		<tr>
			<th scope="row">제목</th>
			<td><input type="text" name="email_title_<?=$mail_case?>" value="<?=$title_value?>" class="input block"></td>
		</tr>
		<?php } ?>
		<?php
				$_mtitle=$_mail_menu_arr[$mail_case];
				if(!$_mtitle) msg("잘못된 코드입니다","back");
				$mcontent=genMailContent($mail_case,1);
				$mcontent=@htmlspecialchars($mcontent);
		?>
		<tr>
			<th scope="row"><?=$_mtitle?><br>치환문자</th>
			<td>
				<?php
					foreach($_mstr as $mkey=>$mval){
						if($mkey == "br_title") break;
						echo "{".$mkey."} ";
					}
				?>
				<br>
				<div class="list_info">
					<p>치환문자를 html 소스에 입력하면 자동으로 치환되어 메일에 적용됩니다.</p>
				</div>
			</td>
		</tr>
	</table>
	<div class="box_middle2">
		<textarea id="content2" name="content2" rows="30" class="txta" style="height:500px;"><?=$mcontent?></textarea>
		<?php if (!$_mail_from_file) { ?>
		<div class="box_title first">
			<span class="btns">
				<span class="box_btn"><input type="button" value="기본내용으로 되돌리기" onclick="resetMSG(this.form);"></span>
			</span>
		</div>
		<?php } ?>
	</div>
	<div class="box_bottom">
		<span class="box_btn blue"><input type="submit" value="확인"></span>
	</div>
	<script type="text/javascript" src="<?=$engine_url?>/_engine/R2Na/R2Na.js"></script>
	<script type="text/javascript">
		var editor_code = 'email_design';
		var editor_gr = 'mail';
		var editor = new R2Na('content2');
		editor.initNeko(editor_code, editor_gr, "img");
		function resetMSG(f){
			if(!confirm("해당 메일을 기본내용으로 초기화하시겠습니까? ")) return false;
			f.exec.value = 'delete';
			f.submit();
		}
	</script>
	<?php } else { ?>
	</table>
	<div class="box_bottom left">
		<div class="list_info">
			<p class="warning">편집하실 메일 항목을 선택해주시기 바랍니다.</p>
		</div>
	</div>
	<?php } ?>
</form>

<script type="text/javascript">
	function ckFrm(f) {
		submitContents('content2');
        printLoading();
	}
</script>