<?PHP

	/* +----------------------------------------------------------------------------------------------+
	' |  네이버 마케팅 설정
	' +----------------------------------------------------------------------------------------------+*/

	if(!$cfg['ncpa_use']) $cfg['ncpa_use'] = 'N';

?>
<form method="post" target="hidden<?=$now?>">
	<input type="hidden" name="body" value="config@naverpay.exe">
	<input type="hidden" name="exec" value="naccount_id">
	<input type="hidden" name="config_code" value="naver_cpa">
	<div class="box_title first">
		<h2 class="title">스크립트 설정</h2>
	</div>
	<table class="tbl_row">
		<caption class="hidden">스크립트 설정</caption>
		<colgroup>
			<col style="width:15%">
		</colgroup>
		<tr>
			<th scope="row">AccountID</th>
			<td>
				<input type="text" name="ncc_AccountId" value="<?=$cfg['ncc_AccountId']?>" class="input" size="15">
				<p class="explain">네이버 광고관리자 <span class="p_color2">[쇼핑광고센터 > 정보관리 > 정보수정]</span> 메뉴에서 학인 가능합니다.</p>
			</td>
		</tr>
		<tr>
			<th scope="row">CPA 서비스 사용</th>
			<td>
				<label class="p_cursor"><input type="radio" name="ncpa_use" value="Y" <?=checked($cfg['ncpa_use'] ,'Y')?>> 사용</label>
				<label class="p_cursor"><input type="radio" name="ncpa_use" value="N" <?=checked($cfg['ncpa_use'] ,'N')?>> 사용안함</label>
				<?if($cfg['ncpa_use'] == 'Y' && $cfg['ncpa_use_date'] > 0) {?>
				<p class="p_color"><strong><?=date('Y년 m월 d일 H시 i분', $cfg['ncpa_use_date'])?></strong> 사용설정 및 'CPA 데이터 수집'에 동의 하셨습니다.</p>
				<?}?>
			</td>
		</tr>
	</table>
	<div class="box_middle2">
		<p class="explain icon left">서비스 안내 및 사용에 관한 사항은 nhn 또는 위사의 광고/마케팅 채널을 통해 문의 해 주시기 바랍니다.</p>
	</div>
	<div class="box_middle2">
		<dl class="box_frame">
			<dt class="desc4"><strong>네이버쇼핑 CPA 데이터 수집동의 주요사항</strong></dt>
			<dd>
				1. 데이터 수집목적:
				<ul>
					<li>네이버쇼핑을 통해 유입되는 트래픽(Traffic)을 통한 구매전환 효과측정</li>
				</ul>
			</dd>
			<dd>
				2. 수집 데이터 항목:
				<ul>
					<li>광고주 쇼핑몰에서 발생하는 이용자 주문의 일시 / 번호 / 상품 / 수량 / 금액 등</li>
				</ul>
			</dd>
			<dd>
				3. 수집 데이터 활용범위:
				<ul>
					<li>데이터 수집에 따른 결과는 네이버쇼핑 운영자인 엔에이치엔비즈니스플랫폼㈜ (이하 ‘NBP’라 함)의 내부 분석 목적의 활용</li>
					<li>데이터 수집에 따른 결과는 네이버쇼핑 DB리스팅 노출순위(랭킹) 결정 요소로 활용</li>
				</ul>
			</dd>
			<dd>
				4. 데이터 수집 관련 주요사항:
				<ul>
					<li>광고주는 NBP가 제공하는 스크립트 설치가이드에 따라 쇼핑몰에 스크립트를 설치합니다.</li>
					<li>(네이버와 제휴된 호스팅사를 이용하는 경우 호스팅사에서 호스팅사 솔루션에 스크립트를 일괄 설치합니다. 따라서 수집동의시 주문정보가 NBP에게 제공됩니다.)</li>
					<li>광고주는 NBP가 제공하는 스크립트 설치가이드에서 정한 데이터 수집 운영정책을 준수하여야 하며, 광고주가 해당 운영정책 위반시 NBP는 제재정책에 따라 광고주를 제재할 수 있습니다.</li>
					<li>광고주는 NBP가 요청하는 경우 정상적인 데이터 수집의 검증을 위해 NBP가 정한 기간과 양식에 따라 네이버쇼핑 트래픽(Traffic)을 통해 쇼핑몰에서 발생한 거래내역(주문완료, 결제완료)과 취소/환불/반품내역을 NBP에게 제공합니다.</li>
					<li>광고주는 CPA 데이터 수집 동의 이후라도 언제든 자신의 판단에 따라 NBP에 사전 통지하고 쇼핑몰 내 스크립트를 삭제함으로써 본 동의를 철회할 수 있습니다.</li>
					<li>(네이버와 제휴된 호스팅사 광고주는 동의 철회를 원할 경우 NBP에 통보하여 동의 철회를 진행할 수 있으며 호스팅사에서 주문정보 전달을 중단하게 됩니다.)</li>
					<li>NBP는 광주의 데이터 수집 동의와 스크립트 설치가 완료된 이후부터 CPA 데이터 수집을 시작하며, 광고주가 동의를 철회하거나 광고주의 운영정책 위반으로 NBP가 제재조치로써 데이터 수집을 중단하기 전까지 CPA 데이터를 계속 수집할 수 있습니다.</li>
					<li>NBP는 데이터 수집 및 광고주 스크립트 설치 지원 업무를 제3자에게 위탁하여 처리할 수 있습니다.</li>
				</ul>
			</dd>
			<dd>
				5. 데이터 전송 검증을 위한 테스트:
				<ul>
					<li>CPA 수집동의 몰을 대상으로 NBP는 데이터의 정상적인 전송여부를 검증하기 위해 주기적으로 모니터링 및 테스트 주문을 발생시킬 수 있습니다.</li>
					<li>주문 테스트는 1회 당 4~10건 정도 진행되며, 해당 주문은 테스트 후 즉시 취소 처리합니다.</li>
				</ul>
			</dd>
			<dd>본인은 상기 CPA 데이터 수집 동의 주요 사항에 기재된 내용을 성실히 이행할 것을 동의합니다.</dd>
		</dl>
	</div>
	<div class="box_bottom">
		<span class="box_btn blue"><input type="submit" value="확인"></span>
	</div>
</form>