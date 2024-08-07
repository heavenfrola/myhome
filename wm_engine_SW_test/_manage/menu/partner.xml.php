<?echo "<?xml version='1.0' encoding='UTF-8' ?>";?>
<menufile>
	<info>
		<version>wisamall w2</version>
		<modify>2009-12-02</modify>
		<description>위사몰 관리자메뉴 디폴트 양식</description>
	</info>
	<menudata>
		<big name="업체관리" category="config" pgcode="1000" link="1010">
			<mid name="업체관리" pgcode="1000">
				<small>
					<name>입점사 정보</name>
					<pgcode>1010</pgcode>
					<link>config@info</link>
				</small>
				<?if(file_exists($engine_dir.'/_engine/include/account/setHosting.inc.php') == false) {?>
				<small>
					<name>관리자정보 수정</name>
					<pgcode>1020</pgcode>
					<link>config@staff_edt</link>
				</small>
				<?}?>
			</mid>
			<mid name="주문/배송설정" pgcode="1100">
			<?if($cfg['use_partner_delivery'] == "Y") {?>
				<small>
					<name>주문 설정</name>
					<pgcode>1160</pgcode>
					<mcode>C0078</mcode>
					<link>config@order</link>
				</small>
				<?}?>
				<small>
					<name>주문정보엑셀 설정</name>
					<pgcode>1130</pgcode>
					<mcode>C0084</mcode>
					<link>config@order_excel_config</link>
				</small>
				<small>
					<name>배송업체 설정</name>
					<pgcode>1120</pgcode>
					<mcode>C0082</mcode>
					<link>config@delivery_prv</link>
				</small>
				<small>
					<name>국내배송 설정</name>
					<pgcode>1110</pgcode>
					<mcode>C0081</mcode>
					<link>config@delivery</link>
				</small>
				<small>
					<name>국내배송 설정</name>
					<pgcode>1170</pgcode>
					<mcode>C0081</mcode>
					<link>config@delivery_fileinput_result</link>
					<new_date>2022-04-05</new_date>
					<hidden>Y</hidden>
				</small>
				<?if($cfg['use_partner_delivery'] == 'Y' && $cfg['use_prd_dlvprc'] == 'Y') {?>
				<small>
					<name>└ 개별배송비 관리</name>
					<pgcode>1231</pgcode>
					<mcode>C0279</mcode>
					<link>config@delivery_set</link>
				</small>
				<small>
					<name>└ 개별배송비 관리</name>
					<pgcode>1231</pgcode>
					<mcode>C0279</mcode>
					<link>config@delivery_set_regist</link>
					<hidden>Y</hidden>
				</small>
				<?}?>
			</mid>
			<mid name="알림설정" pgcode="1140">
				<small>
					<name>입점사 문자알림 설정</name>
					<pgcode>1150</pgcode>
					<mcode>C0103</mcode>
					<link>config@sms_config</link>
					<new_date>2018-04-02</new_date>
				</small>
				<small>
					<name>입점사 자동이메일 설정</name>
					<pgcode>1151</pgcode>
					<mcode>C0100</mcode>
					<link>config@email_config</link>
					<new_date>2018-04-02</new_date>
				</small>
			</mid>
			<?php
                /*[매장지도]*/
			if($cfg['use_store_partner_yn'] == 'Y') {
            ?>
            <mid name="오프라인 매장" pgcode='1200'>
<!--                <small>-->
<!--                    <name>시설안내 설정</name>-->
<!--                    <pgcode>1200</pgcode>-->
<!--                    <mcode>C0200</mcode>-->
<!--                    <link>config@store_facility</link>-->
<!--                    <new_date>2023-09-30</new_date>-->
<!--                </small>-->
                <small>
                    <name>오프라인 매장</name>
                    <pgcode>1210</pgcode>
                    <mcode>C0210</mcode>
                    <link>config@store_location</link>
                    <new_date>2023-09-30</new_date>
                </small>
                <small>
                    <name>오프라인 매장 등록</name>
                    <pgcode>1220</pgcode>
                    <mcode>C0220</mcode>
                    <link>config@store_location_register</link>
                    <hidden>Y</hidden>
                </small>
<!--                <small>-->
<!--                    <name>오프라인 매장 엑셀 업로드</name>-->
<!--                    <pgcode>1230</pgcode>-->
<!--                    <mcode>C0230</mcode>-->
<!--                    <link>config@store_location_upload</link>-->
<!--                    <new_date>2023-09-30</new_date>-->
<!--                </small>-->
            </mid>
            <?php } ?>
		</big>
		<big name="상품관리" category="product" pgcode="2000" link="2010">
			<mid name="상품관리" pgcode="2000">
				<small>
					<name>판매 상품 내역</name>
					<pgcode>2010</pgcode>
					<mcode>C0004</mcode>
					<link>product@product_list</link>
				</small>
				<small>
					<name>상품 등록</name>
					<pgcode>2020</pgcode>
					<mcode>C0005</mcode>
					<link>product@product_register</link>
				</small>
				<?php if($scfg->comp('use_set_product', 'Y') == true && $scfg->comp('partner_prd_ref', 'N') == true) { ?>
				<small>
					<name>세트상품등록</name>
					<pgcode>2140</pgcode>
					<mcode>C0280</mcode>
					<link>product@set_register</link>
					<new_date>2020-04-13</new_date>
				</small>
				<?php } ?>
				<?if($cfg['use_partner_shop'] == 'Y' && $cfg['partner_prd_accept'] == 'Y') {?>
				<small>
					<name>상품 등록/수정 신청 내역</name>
					<pgcode>2030</pgcode>
					<mcode>C0005</mcode>
					<link>product@product_rev</link>
				</small>
				<?}?>
			</mid>
			<mid name="부가관리" pgcode="2100">
				<small>
					<name>옵션세트 관리</name>
					<pgcode>2110</pgcode>
					<mcode>C0013</mcode>
					<link>product@product_option</link>
				</small>
				<small>
					<name>상품 일반 설정</name>
					<pgcode>2111</pgcode>
					<mcode>C0072</mcode>
					<link>product@product_common</link>
				</small>
			</mid>
		</big>
		<big name="주문배송" category="order" pgcode="3000" link="3010">
			<mid name="주문조회" pgcode="3000">
				<small>
					<name>전체주문조회</name>
					<pgcode>3010</pgcode>
					<mcode>C0021</mcode>
					<link>order@order_list</link>
				</small>
			</mid>
			<mid name="배송처리" pgcode="3100">
				<small>
					<name>주문일괄배송처리</name>
					<pgcode>3110</pgcode>
					<mcode>C0027</mcode>
					<link>order@delivery_fileinput</link>
				</small>
				<small>
					<name>주문일괄배송처리</name>
					<pgcode>3210</pgcode>
					<mcode>C0027</mcode>
					<link>order@delivery_fileinput_result</link>
					<hidden>Y</hidden>
				</small>
				<small>
					<name>배송시작</name>
					<pgcode>3120</pgcode>
					<mcode>C0028</mcode>
					<link>order@delivery</link>
				</small>
			</mid>
		</big>
		<big name="고객CRM" category="board" pgcode="4000" link="4010">
			<mid name="상품게시판" pgcode="4100">
				<small>
					<name>상품Q&amp;A</name>
					<pgcode>4010</pgcode>
					<mcode>C0038</mcode>
					<link>board@product_qna</link>
				</small>
				<small>
					<name>상품후기</name>
					<pgcode>4020</pgcode>
					<mcode>C0039</mcode>					>
					<link>board@product_review</link>
				</small>
			</mid>
			<?php
			$menu_intra_chk = $pdo->row("select count(*) from `$tbl[intra_board_config]` where auth_list>='$admin[level]'");
			if($menu_intra_chk>0) {
			?>
			<mid name="커뮤니티" pgcode='4200'>
				<?php
				$ii = '4200';
				$menu_intra_sql = $pdo->iterator("select `auth_list`, `db`, `title`, `no` from `$tbl[intra_board_config]` where auth_list>='$admin[level]' order by `title`");
                foreach ($menu_intra_sql as $_community) {
					$ii++;
					$_community['title'] = str_replace('&', '&amp;', $_community['title']);
				?>
					<small>
						<name><?=$_community['title']?></name>
						<pgcode><?=$ii?></pgcode>
						<link>board@board&amp;db=<?=$_community['db']?></link>
					</small>
					<small>
						<name><?=$_community['title']?></name>
						<pgcode><?=$ii?></pgcode>
						<link>board@board</link>
						<hidden>Y</hidden>
					</small>
				<?}?>
			</mid>
			<?}?>
		</big>
		<big name="매출정산" category="income" pgcode="5000" link="5010">
			<mid name="주문상품통계" pgcode="5100">
				<small>
					<name>상품별매출</name>
					<pgcode>5010</pgcode>
					<mcode>C0192</mcode>
					<link>income@income_product</link>
				</small>
					<small>
					<name>개별상품판매분석</name>
					<pgcode>5020</pgcode>
					<mcode>C0193</mcode>
					<link>income@income_product_detail</link>
				</small>
			</mid>
			<mid name="정산관리" pgcode="5200">
				<small>
					<name>정산내역</name>
					<pgcode>5030</pgcode>
					<link>income@account_list</link>
				</small>
			</mid>
		</big>
		<big name="재고관리" category="erp" pgcode="14000" link='body=erp@lite_stock_list'>
			<mid name="재고관리" pgcode="14100">
				<small>
					<name>실시간재고 조회</name>
					<pgcode>14110</pgcode>
					<mcode>E0003</mcode>
					<link>erp@stock_list</link>
				</small>
				<small>
					<name>실시간재고 상세조회</name>
					<pgcode>14110</pgcode>
					<mcode>E0003</mcode>
					<link>erp@stock_detail</link>
					<hidden>Y</hidden>
				</small>
				<small>
					<name>재고 조정</name>
					<pgcode>14130</pgcode>
					<mcode>E0004</mcode>
					<link>erp@stock_adjust</link>
				</small>
                <small>
                    <name>일괄재고 조정</name>
                    <pgcode>14140</pgcode>
                    <mcode>E0005</mcode>
                    <link>erp@stock_adjust_all</link>
                </small>
				<small>
					<name>재고조정 내역 조회</name>
					<pgcode>14150</pgcode>
					<mcode>E0006</mcode>
					<link>erp@stock_adjust_list</link>
				</small>
			</mid>
			<mid name="바코드 관리" pgcode="14600">
				<small>
					<name>바코드 관리</name>
					<pgcode>14610</pgcode>
					<mcode>E0018</mcode>
					<link>erp@barcode</link>
				</small>
			</mid>
		</big>
	</menudata>
</menufile>