<div id="pg_help">
	<div class="box_title first">
		<h2 class="title">신용카드결제(pg) 신청</h2>
	</div>
	<div class="box_bottom top_line">
		<table class="tbl_pg">
			<caption class="hidden">신용카드결제(pg) 리스트</caption>
			<colgroup>
				<col style="width:14.3%">
				<col style="width:14.3%">
				<col style="width:14.3%">
				<col style="width:14.3%">
				<col style="width:14.3%">
				<col style="width:14.3%">
				<col style="width:14.3%">
			</colgroup>
			<thead>
				<tr>
					<th scope="col"><img src="<?=$engine_url?>/_manage/image/config/pg_help/logo_lg.gif" alt="LG유플러스"></th>
					<th scope="col"><img src="<?=$engine_url?>/_manage/image/config/pg_help/logo_kcp.gif" alt="KCP"></th>
					<th scope="col"><img src="<?=$engine_url?>/_manage/image/config/pg_help/logo_inicis.gif" alt="이니시스"></th>
					<th scope="col"><img src="<?=$engine_url?>/_manage/image/config/pg_help/logo_allat.gif" alt="올앳"></th>
					<th scope="col"><img src="<?=$engine_url?>/_manage/image/config/pg_help/logo_allthegate.gif" alt="올더게이트"></th>
					<th scope="col"><img src="<?=$engine_url?>/_manage/image/config/pg_help/logo_ksnet.gif" alt="ksnet"></th>
					<th scope="col"><img src="<?=$engine_url?>/_manage/image/config/pg_help/logo_danal.gif" alt="다날"></th>
				</tr>
			</thead>
			<tbody>
				<tr>
					<td>수수료 3.5%</td>
					<td>수수료 3.5%</td>
					<td>수수료 3.5%</td>
					<td>수수료 3.5%</td>
					<td>수수료 3.5%</td>
					<td>수수료 3.5%</td>
					<td>수수료 3.5%</td>
				</tr>
				<tr>
					<td>초기가입비 20만원</td>
					<td>초기가입비 20만원</td>
					<td>초기가입비 20만원</td>
					<td>초기가입비 20만원</td>
					<td>초기가입비 20만원</td>
					<td>초기가입비 20만원</td>
					<td>초기가입비 20만원</td>
				</tr>
				<tr>
					<td>연관리비 면제</td>
					<td>연관리비 면제</td>
					<td>연관리비 면제</td>
					<td>연관리비 면제</td>
					<td>연관리비 면제</td>
					<td>연관리비 면제</td>
					<td>연관리비 면제</td>
				</tr>
				<tr>
					<td>보증보험 면제</td>
					<td>보증보험 면제</td>
					<td>보증보험 면제</td>
					<td>보증보험 면제</td>
					<td>보증보험 면제</td>
					<td>보증보험 면제</td>
					<td>보증보험 면제</td>
				</tr>
				<tr>
					<td>정산주기 D+7일</td>
					<td>정산주기 D+7일</td>
					<td>정산주기 D+7일</td>
					<td>정산주기 D+7일</td>
					<td>정산주기 D+7일</td>
					<td>정산주기 D+7일</td>
					<td>정산주기 D+7일</td>
				</tr>
				<tr>
					<td>신청익일 서비스 개통</td>
					<td>신청익일 서비스 개통</td>
					<td>신청익일 서비스 개통</td>
					<td>신청익일 서비스 개통</td>
					<td>신청익일 서비스 개통</td>
					<td>신청익일 서비스 개통</td>
					<td>신청익일 서비스 개통</td>
				</tr>
				<tr>
					<td>카드 부분취소 가능</td>
					<td>카드 부분취소 가능</td>
					<td>카드 부분취소 가능</td>
					<td>카드 부분취소 가능</td>
					<td>카드 부분취소 가능</td>
					<td>카드 부분취소 가능</td>
					<td>카드 부분취소 가능</td>
				</tr>
				<tr>
					<td>
						<span class="box_btn full"><a onclick="pg_detail('lg')">상세안내</a></span>
						<span class="box_btn full blue"><a href="<?=$redirect_url?>/pg_help_dacom01" target="_blank">신청하기</a></span>
					</td>
					<td>
						<span class="box_btn full"><a onclick="pg_detail('kcp')">상세안내</a></span>
						<span class="box_btn full blue"><a href="<?=$redirect_url?>/pg_help_kcp01" target="_blank">신청하기</a></span>
					</td>
					<td>
						<span class="box_btn full"><a onclick="pg_detail('inicis')">상세안내</a></span>
						<span class="box_btn full blue"><a href="<?=$redirect_url?>/pg_help_inicis01" target="_blank">신청하기</a></span>
					</td>
					<td>
						<span class="box_btn full"><a onclick="pg_detail('allat')">상세안내</a></span>
						<span class="box_btn full blue"><a href="<?=$redirect_url?>/pg_help_allat01" target="_blank">신청하기</a></span>
					</td>
					<td>
						<span class="box_btn full"><a onclick="pg_detail('allthegate')">상세안내</a></span>
						<form action ="http://www.allthegate.com/ags/partner/ptn_start.jsp" method="post" target="_blank">
							<input type="hidden" name="ptn_id" value="wisamall">
							<input type="hidden" name="rtnurl" value="http://www.allthegate.com/AGS_pay_ing.html">
							<span class="box_btn full blue">
								<input type="submit" value="신청하기">
							</span>
						</form>
					</td>
					<td>
						<span class="box_btn full"><a onclick="pg_detail('ksnet')">상세안내</a></span>
						<span class="box_btn full blue"><a href="">신청하기</a></span>
					</td>
					<td>
						<span class="box_btn full"><a onclick="pg_detail('danal')">상세안내</a></span>
						<span class="box_btn full blue"><a href="">신청하기</a></span>
					</td>
				</tr>
			</tbody>
		</table>
		<h3>신용카드결제(pg) 신청 절차</h3>
		<ol class="step">
			<li>
				<div class="step1">온라인 신청</div>
			</li>
			<li>
				<div class="step2">계약서 작성</div>
			</li>
			<li>
				<div class="step3">계약서류 발송</div>
			</li>
			<li>
				<div class="step4">계약서 접수</div>
			</li>
			<li>
				<div class="step5">카드사 심사</div>
			</li>
			<li>
				<div class="step6">카드사 승인</div>
			</li>
		</ol>
		<ol class="msg">
			<li><b>1</b>.온라인상에서 가입신청을 해주세요</li>
			<li><b>2</b>.계약서를 다운로드 받아 작성하신 후, 작성하신 계약서를 2부 출력, 날인합니다.</li>
			<li><b>3</b>.작성이 끝난 2부계약서와 구비서류를 첨부하여 계약담당자 앞으로 등기 발송해 주십시오.</li>
		</ol>
	</div>
</div>

<script type="text/javascript">
function pg_detail(name) {
	nurl='/_manage/?body=config@pg_help_detail.frm&mode='+name;
	window.open(nurl,'pg_detail','top=10,left=10,status=no,toolbars=no,scrollbars=yes,location=no,titlebar=no,resizable=no,height=700,width=920');
}
</script>

<div id="pg_help" style="display:none;">
<div class="btbl">
	<h1><img src="<?=$engine_url?>/_manage/image/config/pg/title_pg_help.gif" alt="전자결제서비스 - 전자결제시스템으로 구매 및 결제를 보다 안전하고 편리하게 이용할 수 있습니다. (신용카드/계좌이체/가상계좌/에스크로/휴대폰/현금영수증)"></h1>
	<div class="guide">
		<h2><img src="<?=$engine_url?>/_manage/image/config/pg/title_pg_help_guide.gif" border="0" alt="전자결제(PG) 안내"></h2>
		<div class="content"><img src="<?=$engine_url?>/_manage/image/config/pg/paygate_service.gif" alt="전자결제 서비스(Paygate Service)란? 온라인쇼핑몰의 결제를 상점과 구매자를 양방향으로 지원하는 편리한 결제 서비스로 다양한 결제수단을 지원하여 구매자 유치에 많은 도움이 됨으로 고객만족과 매출향상도 필요한 서비스를 제공합니다."></div>
		<h2><img src="<?=$engine_url?>/_manage/image/config/pg/title_pg_help_process.gif" border="0" alt="신용카드결제(PG) 신청 절차"></h2>
		<ul class="process">
			<li class="first-child"><img src="<?=$engine_url?>/_manage/image/config/pg/paygate_step1.gif" alt="온라인 신청"></li>
			<li><img src="<?=$engine_url?>/_manage/image/config/pg/paygate_step2.gif" alt="계약서 작성"></li>
			<li><img src="<?=$engine_url?>/_manage/image/config/pg/paygate_step3.gif" alt="계약서류 발송"></li>
			<li><img src="<?=$engine_url?>/_manage/image/config/pg/paygate_step4.gif" alt="계약서 접수"></li>
			<li><img src="<?=$engine_url?>/_manage/image/config/pg/paygate_step5.gif" alt="카드사 심사"></li>
			<li><img src="<?=$engine_url?>/_manage/image/config/pg/paygate_step6.gif" alt="카드사 승인"></li>
		</ul>
		<div class="content"><img src="<?=$engine_url?>/_manage/image/config/pg/paygate_service_content.gif" alt=""></div>
	</div>
	<?	$shuff = array("dacom","kcp","inicis","allat", "allthegate");?>
	<div class="tabList">
		<img src="<?=$engine_url?>/_manage/image/config/pg/tab_0.gif" alt="" usemap="#pgTab" id="pgTabImg">
		<map name="pgTab" id="pgTab">
			<area shape="rect" coords="2,1,129,27" href="javascript:tabView(0)" alt="LG유플러스">
			<area shape="rect" coords="130,1,257,27" href="javascript:tabView(1)" alt="KCP">
			<area shape="rect" coords="260,1,387,27" href="javascript:tabView(2)" alt="이니시스">
			<area shape="rect" coords="390,1,517,27" href="javascript:tabView(3)" alt="삼성올앳">
			<area shape="rect" coords="519,1,647,27" href="javascript:tabView(4)" alt="올더게이트">
		</map>
    </div>
	<div class="serviceTab">
		<? for($ii=0; $ii<count($shuff); $ii++) {?>
		<div id="tab_<?=$shuff[$ii]?>" style="display:none;">
		<? include "$engine_dir/_manage/config/card_desc_{$shuff[$ii]}.php";?>
		</div>
		<?}?>
		<h3 id="item_08">
			<img src="<?=$engine_url?>/_manage/image/config/pg/paygate08.gif" alt="08 서비스 연동">
		</h3>
		<div class="content">
			<img src="<?=$engine_url?>/_manage/image/config/pg/paygate08_content.gif" alt="카드사 등록이 완료되면, 승인정보를 관리자에 적용합니다. 신용카드를 제외한 부가서비스는 연동 즉시 이용하실 수 있습니다.">
		</div>
		<h3 id="item_09">
			<img src="<?=$engine_url?>/_manage/image/config/pg/paygate09.gif" alt="09 서비스 오픈">
		</h3>
		<div id="item_10" class="content">
			<img src="<?=$engine_url?>/_manage/image/config/pg/paygate09_content.gif" alt="결제서비스 테스트를 통하여 정상적인 신용카드 결제가 가능합니다.">
		</div>
	</div>
</div>
</div>

<script type="text/javascript">
	function tabView(sel) {
		var pgList = new Array("dacom","kcp","inicis","allat","allthegate");
		for (var i = 0; i < pgList.length; i++){
			document.getElementById("tab_"+pgList[i]).style.display = (sel == i) ? "block" : "none";
			if (sel == i) document.getElementById("pgTabImg").src = "<?=$engine_url?>/_manage/image/config/pg/tab_"+pgList[i]+".gif";
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