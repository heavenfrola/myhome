<?PHP

	/* +----------------------------------------------------------------------------------------------+
	' |  다날 결제 실패
	' +----------------------------------------------------------------------------------------------+*/

	include_once $engine_dir."/_engine/include/common.lib.php";

	if($AbleBack) $btn_error = "btn_retry.gif";
	else $btn_error = "btn_cancel.gif";

	$BackURL = urldecode($_POST['BackURL']);
	if($mobile_browser  == 'mobile') {
		$BackURL = $root_url.'/shop/order.php';
		?><meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, minimum-scale=1.0, user-scalable=no, target-densitydpi=medium-dpi" /><?
	} else {
		$BackURL = 'javascript:self.close();';
	}

	$ord = $pdo->assoc("select stat from $tbl[order] where ono='$ono'");

	if($ord['stat'] == 11) {
		$_POST['reason'] = $Result;
		include_once $engine_dir."/_engine/order/pay_cancel.php";
	}

	if(!$Result) {
		javac("
        opener.parent.layTgl3('order1', 'Y');
        opener.parent.layTgl3('order2', 'N');
        opener.parent.layTgl3('order3', 'Y');
		self.close();
		");
		exit;
	}

?>
	<link rel="stylesheet" href="<?=$engine_url?>/_engine/card.danal/css/style.css" type="text/css">
	<div class="paymentPop cType<?=$BgColor?>">
		<p class="tit">
			<img src="<?=$engine_url?>/_engine/card.danal/images/img_tit.gif" width="494" height="48" alt="다날휴대폰결제" />
			<span class="logo"><img src="<?=$URL?>" width="119" height="47" alt="" /></span>
		</p>
		<div class="tabArea">
			<ul class="tab">
				<li class="tab01">결제서비스에러</li>
			</ul>
			<p class="btnSet">
				<a href="JavaScript:OpenHelp();"><img src="<?=$engine_url?>/_engine/card.danal/images/btn_useInfo.gif" width="55" height="20" alt="이용안내" /></a>
				<a href="JavaScript:OpenCallCenter();"><img src="<?=$engine_url?>/_engine/card.danal/images/btn_customer.gif" width="55" height="20" alt="고객센터" /></a>
			</p>
		</div>
		<div class="content">
			<div class="alertBox">
				<p class="type01"><strong>에러 내용(<?=$Result?>)</strong><br/><?=$ErrMsg?></p>
			</div>
			<div class="infoText">
				<p class="t02">다날 고객센터 : <strong>1566-3355</strong> (전국공통)</p>
			</div>
			<div class="grayBox" style="margin-top:11px;">
				<p class="type02">상담원 통화가능시간 : <br/>
				평일 : 9시 ~ 19시<br/>
				<strong>토요일, 일요일, 공휴일 휴무</strong></p>
			</div>
			<p class="btnRetry"><a href="<?=$BackURL?>"><img src="<?=$engine_url?>/_engine/card.danal/images/<?=$btn_error?>" alt="결제 재시도" /></a></p>
		</div>
		<div class="footer">
			<dl class="noti">
				<dt>공지사항</dt>
				<dd>다날 휴대폰 결제를 이용해주셔서 감사합니다.</dd>
			</dl>
		</div>
	</div>