<?PHP

	/* +----------------------------------------------------------------------------------------------+
	' |  윙Mobile 신용카드PG 신청
	' +----------------------------------------------------------------------------------------------+*/

?>
<div id="pg_help">
<div class="btbl">
	<h1><img src="<?=$engine_url?>/_manage/image/config/pg_mobile/title_mobile_pg_help.gif" alt="윙Mobile 전자결제서비스"></h1>
	<div class="guide">
		<h2><img src="<?=$engine_url?>/_manage/image/config/pg_mobile/title_mobile_pg_help_guide.gif" alt="윙 Mobile 전자결제 안내"></h2>
		<p class="content"><img src="<?=$engine_url?>/_manage/image/config/pg_mobile/mobile_paygate_service.gif" alt=""></p>
		<h2><img src="<?=$engine_url?>/_manage/image/config/pg_mobile/title_mobile_pg_help_process.gif" alt="윙Mobile 전자결제 지원안내"></h2>
		<p class="content"><img src="<?=$engine_url?>/_manage/image/config/pg_mobile/mobile_paygate_service_content.gif" alt="스마트폰 결제지원 PG사: LG U+(순차적으로 다른 PG사도 업데이트 진행중 입니다."></p>
		<h2><img src="<?=$engine_url?>/_manage/image/config/pg_mobile/title_mobile_pg_process.gif" alt="윙Mobile 전자결제 신청방법"></h2>
		<div class="content">
			<dl>
				<dt><img src="<?=$engine_url?>/_manage/image/config/pg_mobile/mobile_pg_process_subtitle_old.gif" alt="1. 가입된 기존 고객"></dt>
				<dd><img src="<?=$engine_url?>/_manage/image/config/pg_mobile/mobile_pg_process_content_old.gif" alt="PG 가입된 기존 사용 고객분들은 1:1고객센터 문의 글로 접수해 주시면 신속히 처리 해 드리겠습니다."></dd>
			</dl>
			<dl>
				<dt><img src="<?=$engine_url?>/_manage/image/config/pg_mobile/mobile_pg_process_subtitle_new.gif" alt="2 신규 신청 고객"></dt>
				<dd class="center"><img src="<?=$engine_url?>/_manage/image/config/pg_mobile/mobile_pg_process_step_new.gif" alt="윙 쇼필몰 관리자 접속 > 쇼핑몰 설정 > 모바일 PG 신청"></dd>
				<dd><img src="<?=$engine_url?>/_manage/image/config/pg_mobile/mobile_pg_process_content_new.gif" alt="1.Wing 관리자모드에 접속하여 쇼핑몰설정의 윙MobilePG 신청에서 가입신청을 합니다. 2.상점아이디 추가발급 계약서를 다운로드 받아 작성하신 후 , 작성하신 계약서를 2부 출력, 날인합니다. 3.작성이 끝난 2부 계약서와 아래의 구비서류를 첨부하여, 계약담당자 앞으로 등기 발송 해 주십시오."></dd>
			</dl>
		</div>
	</div>
	<?	$shuff = array("dacom","kcp","inicis","allat", "allthegate");?>
    <div class="tabList">
        <img src="<?=$engine_url?>/_manage/image/config/pg_mobile/tab_0.gif" alt="" usemap="#pgTab" id="pgTabImg">
		<map name="pgTab" id="pgTab">
			<area shape="rect" coords="2,1,129,27" href="javascript:tabView(0)" alt="LG유플러스">
			<area shape="rect" coords="130,1,257,27" href="javascript:tabView(1)" alt="KCP">
			<area shape="rect" coords="260,1,387,27" href="javascript:tabView(2)" alt="이니시스">
			<area shape="rect" coords="390,1,517,27" href="javascript:tabView(3)" alt="삼성올앳">
			<area shape="rect" coords="519,1,647,27" href="javascript:tabView(4)" alt="올더게이트">
		</map>
    </div>
	<div class="serviceTab">
		<?for($ii=0; $ii<count($shuff); $ii++) {?>
		<div id="tab_<?=$shuff[$ii]?>" style="display:none;">
		<?include "$engine_dir/_manage/config/card_desc_{$shuff[$ii]}.php"; ?>
		</div>
		<?}?>
		<h3 id="item_08">
			<img src="<?=$engine_url?>/_manage/image/config/pg_mobile/paygate08.gif" alt="08 서비스 연동">
		</h3>
		<div class="content">
			<img src="<?=$engine_url?>/_manage/image/config/pg_mobile/paygate08_content.gif" alt="카드사 등록이 완료되면, 승인정보를 관리자에 적용합니다. 신용카드를 제외한 부가서비스는 연동 즉시 이용하실 수 있습니다.">
		</div>

		<h3 id="item_09">
			<img src="<?=$engine_url?>/_manage/image/config/pg_mobile/paygate09.gif" alt="09 서비스 오픈">
		</h3>
		<div id="item_10" class="content">
			<img src="<?=$engine_url?>/_manage/image/config/pg_mobile/paygate09_content.gif" alt="결제서비스 테스트를 통하여 정상적인 신용카드 결제가 가능합니다.">
		</div>
	</div>
</div>
</div>

<script type="text/javascript">
	function tabView(sel) {
		var pgList = new Array("dacom","kcp","inicis","allat","allthegate");
		for (var i = 0; i < pgList.length; i++){
			document.getElementById("tab_"+pgList[i]).style.display = (sel == i) ? "block" : "none";
			if (sel == i) document.getElementById("pgTabImg").src = "<?=$engine_url?>/_manage/image/config/pg_mobile/tab_"+pgList[i]+".gif";
		}
		if(sel == 2) {
			$('#item_08').hide();
			$('#item_09').hide();
			$('#item_10').hide();
		} else {
			$('#item_08').show();
			$('#item_09').show();
			$('#item_10').hide();
		}
	}

	tabView(0);
</script>