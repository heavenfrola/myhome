<?PHP

	/* +----------------------------------------------------------------------------------------------+
	' |  단체메일 발송
	' +----------------------------------------------------------------------------------------------+*/

	set_time_limit(0);

	extract($_POST);
	$check_pno = numberOnly($check_pno);

	if($_POST['mtype'] != 2) {
		include_once $engine_dir."/_manage/member/group_mail.lib.php";
		$mail_ck = mailLimitCk('close'); // 잔여 메일 건수
	}

	$mode = $mmode;
	if($mode == '2') {
		$msg_where = '';
		foreach($check_pno as $key=>$val) {
			$msg_where .= " or `no`='$val'";
		}
		$msg_where = substr($msg_where, 4);
		$msg_where = "and ($msg_where)";
	} elseif($mode == '4') {
		$msg_where = stripslashes($msg_where);
	} else {
		$msg_where = '';
	}

	// eon 대량 메일 주소록 생성
	if($_POST['mtype'] == 2) {
		$feedfile = 'eon_'.$now.'.csv';
		$feedfile = 'eon_'.$now.'.csv';
		$fp = @fopen($root_dir.'/_data/mail/'.$feedfile, 'w');
		if(!$fp) msg('주소록 저장오류', 'close');

		$addrdir = @opendir($root_dir.'/_data/mail');
		if($addrdir) { // 24시간 이상 지난 오랜 주소록 삭제
			while($nm = readdir($addrdir)) {
				if(preg_match('/^eon_([0-9]+)\.csv$/', $nm, $preg)) {
					if($now-$preg[1] > 86400) {
						@unlink($root_dir.'/_data/mail/'.$nm);
					}
				}
			}
		}
	}

	if($mailblock == 'Y') $where = " and `mailing`='Y'"; // 수신거부
	$res = $pdo->iterator("select name, email, member_id, cell, reg_date from `$tbl[member]` x where 1 $msg_where $where order by `no`");
	$total_mail = 0;
	$to_member = $to_mail = $to_name = '';

    foreach ($res as $data) {
		$data['name'] = trim(str_replace(':', '', $data['name']));
		$to_member .= ','.$msplit.$data['name'].'('.$data['email'].')';

		if($total_mail < 500) {
			$to_member2=$to_member;
		}
		$to_mail .= ':'.$data['email'];
		$to_name .= ':'.$data['name'];
		$total_mail++;

		// eon 대량 메일 주소록 내용
		if($_POST['mtype'] == 2) {
			$feed .= fputcsv($fp, array(
				$data['member_id'],
				$data['name'],
				$data['email'],
				'http://deny.wisa.co.kr/public/email/?s='.base64_encode($root_url).'&e='.md5($data['email']),
				$data['cell'],
				date('Y-m-d H:i:s', $data['reg_date']),
			));
		}
	}
	$to_member = substr($to_member, 1);
	$to_member2 = substr($to_member2, 1);
	$to_name = substr($to_name, 1);
	$to_mail = substr($to_mail, 1);
	$rec_str = '총 '.number_format($total_mail).' 명';

	$_month_amount = $mail_ck[0];
	if($mail_ck[2] > 0) {
		$_month_amount -= $mail_ck[2];
		$_month_amount = ($_month_amount < 1) ? "0 <font color=\"#C0C0C0\">[".number_format($mail_ck[0])." 건 모두 사용]</font>" : $_month_amount;
	}
	$temp=$now;

	if($_POST['mtype'] == 2) { // eon 대량메일 오픈
		fclose($fp);

		// 계정정보
		$wec = new weagleEyeClient($_we, 'account');
		$account = $wec->call('getSvcs', array('key_code' => $wec->config['wm_key_code']));
		$domain = preg_replace('@http(s)?://(www.)?@', '', $root_url);
		$from = 'send@'.$domain;
		$hash = md5($account[0]->account_id[0].$account[0]->account_idx[0].$total_mail.'S+3b6zWXGU+fFtZlzQYH554gmT94dpFpPv9qowAtdLI=');
		$is_package = $account[0]->is_package[0];

		?>
		<form id="eonFrm" method="post" action="https://www.eongo.co.kr/weom/servlet/servlet.WSOMWISA001" accept-charset="euc-kr">
			<input type="hidden" name="account_id" value="<?=$account[0]->account_id[0]?>">
			<input type="hidden" name="brnd_cd" value="<?=$account[0]->account_idx[0]?>">
			<input type="hidden" name="brnd_nm" value="<?=$cfg['company_name']?>">
			<input type="hidden" name="admin_name" value="<?=$cfg['admin_name']?>">
			<input type="hidden" name="admin_cell" value="<?=$cfg['admin_cell']?>">
			<input type="hidden" name="admin_phone" value="<?=$cfg['company_phone']?>">
			<input type="hidden" name="send_mail" value="<?=$from?>">
			<input type="hidden" name="send_name" value="<?=$cfg['company_name']?>">
			<input type="hidden" name="domain" value="<?=$root_url?>">
			<input type="hidden" name="port" value="80">
			<input type="hidden" name="addressbook" value="/_data/mail/<?=$feedfile?>">
			<input type="hidden" name="total_cnt" value="<?=$total_mail?>">
			<input type="hidden" name="mapping" value="전화번호,가입일">
			<input type="hidden" name="h" value="<?=$hash?>">
			<input type="hidden" name="package" value="<?=$is_package?>">
		</form>
		<script type="text/javascript">
			$(document).ready(function() {
				$('html, body').css('overflow', 'hidden');
				window.resizeTo(1220, 800);
				$('#eonFrm').submit();
			});
		</script><?
		return;
	}

?>
<link rel="stylesheet" type="text/css" href="<?=$engine_url?>/_engine/R2Na/R2Na.css">
<script language="javasscript" type="text/javascript" src="<?=$engine_url?>/_engine/R2Na/R2Na.js"></script>
<form name="postFrm" method="post" action="./pop.php">
	<? foreach($_POST as $k => $v) { ?>
		<? if(is_array($v)) { ?>
			<? foreach($v as $k2 => $v2) { ?>
			<input type="hidden" name="<?=$k?>[]" value="<?=$v2?>">
			<? } ?>
		<? } else { ?>
		<input type="hidden" name="<?=$k?>" value="<?=$v?>">
		<? } ?>
	<? } ?>
</form>
<form method="post" name="email" action="./index.php" target="hidden<?=$now?>" onSubmit="return checkMFrm(this)" class="pop_width">
	<input type="hidden" name="total_mail" value="<?=$total_mail?>">
	<input type="hidden" name="temp" value="<?=$temp?>">
	<input type="hidden" name="body" value="member@group_mail.exe">
	<div class="box_title first">
		<h2 class="title">단체메일 발송</h2>
	</div>
	<div class="box_middle left">
		<?if($_REQUEST['mtype'] == 1) {?>
		<p class="p_color2">[베이직 대량메일 발송 안내]</p>
		<ul class="list_msg">
			<li>발송을 누르시면 "예약" 상태로 접수됩니다</li>
			<li>메일 서버에서 예약된 순으로 메일이 발송되며 평균적으로 1시간내에 발송이 시작됩니다</li>
			<li>발송이 시작되면, 메일은 1분에 100통의 메일이 발송됩니다</li>
			<li><u>스팸메일을 발송시 서비스 이용이 중지될 수 있습니다</u></li>
			<li>현재 발송 가능한 건수는 <b>(월 기본제공 <?=$_month_amount?> 건 + 충전 잔여건수 <?=number_format($mail_ck[1])?> 건  = <?=number_format($mail_ck[3])?> 건)</b> 입니다</li>
			<li>
				제한 건수 초과시에는 <u>고객센터에서 추가 충전(유료)이 가능</u>합니다.
				<a href="?body=wing@service_charge" target="_blank">충전신청하러가기</a>
			</li>
			<li>
				링크 주소에 <span>{수신거부링크}</span> 를 입력하시면 고객에게 수신거부 링크를 제공하실수 있습니다.
				(ex &lt;a href='{수신거부링크}' target='_blank'&gt;[수신거부]&lt;/a&gt;)
			</li>
			<li>윙메일이 아닌 외부메일 사용 시 <span>http://deny.wisa.co.kr/public/email/?s=<?=base64_encode($root_url)?>&e=이메일주소</span> 수신거부 링크를 사용하실수 있습니다.</li>
		</ul>
		<?} else {?>
		<p class="p_color2">[프리미엄 대량메일 발송 안내]</p>
		<ul class="list_msg">
			<li><u>스팸메일을 발송시 서비스 이용이 중지될 수 있습니다</u></li>
			<li>현재 발송 가능한 건수는 <b>(충전 잔여건수 <?=number_format($mail_ck[1])?> 건  = <?=number_format($mail_ck[1])?> 건)</b> 입니다</li>
			<li>
				제한 건수 초과시에는 <u>고객센터에서 추가 충전(유료)이 가능</u>합니다.
				<a href="?body=wing@service_charge" target="_blank">충전신청하러가기</a>
			</li>
		</ul>
		<?}?>
	</div>
	<div id="controlTab" class="none_margin">
		<ul class="tabs">
			<li id="ctab_1" onclick="tabSH(1)" <?if($_POST['mtype'] == 1) {?>class="selected"<?}?>><a href="#" onclick="mtypeChg(1); return false;">베이직 메일</a></li>
			<li id="ctab_2" onclick="tabSH(2)" <?if($_POST['mtype'] == 2) {?>class="selected"<?}?>><a href="#" onclick="mtypeChg(2); return false;">프리미엄 메일</a></li>
		</ul>
	</div>
	<table class="tbl_row">
		<caption class="hidden">단체메일 발송</caption>
		<colgroup>
			<col style="width:15%">
			<col>
		</colgroup>
		<?if($_POST['mtype'] == 1) {?>
		<tr>
			<th scope="row">수신자</th>
			<td>
				<u><b><?=$rec_str?></b>의 회원에게 전송합니다</u><?if($total_mail>500){?> (500명만 나타납니다)<?}?>
				<div class="scrollbox"><?=$to_member2?></div>
				<input type="hidden" name="to_name" value="<?=$to_name?>">
				<input type="hidden" name="to_mail" value="<?=$to_mail?>">
			</td>
		</tr>
		<tr>
			<th scope="row"><strong>발신자</strong></th>
			<td>
				이&nbsp;&nbsp;&nbsp;름 : <input type="text" name="from_name" value="<?=inputText($cfg['company_mall_name'])?>" class="input" size="30"><br>
				이메일 : <input type="text" name="from_mail" value="<?=inputText($cfg['company_email'])?>" class="input" size="30">
			</td>
		</tr>
		<tr>
			<th scope="row"><strong>제목</strong></th>
			<td>
				<input type="text" name="title" value="" class="input" size="80">
				<p class="explain">
					<b>제목 및 내용에 <u>{이름}</u>을 입력하시면 회원이름으로 치환됩니다</b><br>
					예) {이름}님을 위한 쇼핑 제안 → 홍길동님을 위한 쇼핑 제안
				</p>
			</td>
		</tr>
		<tr>
			<td colspan="2">
				<textarea id="content2" name="content2" class="txta" style="width:100%"></textarea>
			</td>
		</tr>
		<tr>
			<td colspan="2" align="center">
				<iframe name="imgFr" src="./?body=member@group_mail_file.frm&temp=<?=$temp?>" width="780" height="auto" scrolling="yes" frameborder="0"></iframe>
			</td>
		</tr>
		<?} else {?>
		<tr>
			<td colspan="2">
				<iframe src="<?=$url?>?<?=$param?>" width="100%" height="600px" frameborder="0" border="0" noresize></iframe>
			</td>
		</tr>
		<?}?>
	</table>
	<div class="box_bottom">
		<? if($_POST['mtype'] == 1) { ?><span class="box_btn_s blue"><button type="submit">전송</button></span><? } ?>
		<?=$close_btn?>
	</div>
</form>

<script language="JavaScript">
	var imgFr=1;
	var R2Na = new R2Na('content2');
	<?
		$over_mail=number_format($total_mail-$mail_ck[3]);
		if($total_mail > $mail_ck[3]){
	?>
	if(confirm('\n 현재 최대 발송 가능 건수를 <?=$over_mail?> 건 초과하여 메일발송량을 충전하셔야 사용이 가능합니다.\n\n 충전페이지로 이동하시겠습니까?')){
		opener.location.href='./?body=service@mail_recharge';
		window.self.close();
	}
	<?
		}
	?>
	function checkMFrm(f){
		if (!checkBlank(f.title,'제목을 입력해주세요.')) return false;
		if(!submitContents('content2', '내용을 입력하세요')) return false;
		if (!confirm('\n 메일을 발송하시겠습니까?\n\n 수신자와 제목, 내용을 한번더 확인하십시오\n\n 확인을 누르시면 발송 예약되며,\n\n 대기 메일이 없을 경우 1분내 발송이 시작됩니다       \n'))
		{
			return false;
		}
	}
	function mtypeChg(t) {

		var f=document.postFrm;
		f.mtype.value=t;
		f.submit();
	}
</script>