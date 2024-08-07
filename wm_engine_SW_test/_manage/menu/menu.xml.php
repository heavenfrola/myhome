<?php echo "<?xml version='1.0' encoding='UTF-8' ?>";?>
<menufile>
	<info>
		<version>wisamall w2</version>
		<modify>2009-12-02</modify>
		<description>위사몰 관리자메뉴 디폴트 양식</description>
	</info>
	<menudata>
		<big name="상품관리" category="product" pgcode="2000" link="2120">
			<mid name="상품관리" pgcode="2000">
				<small>
					<name>상품조회</name>
					<pgcode>2120</pgcode>
					<mcode>C0004</mcode>
					<link>product@product_list</link>
					<rel>
						<item>
							<name>상품정보엑셀 양식</name>
							<link>?body=product@product_excel_config</link>
						</item>
						<item>
							<name>상품진열</name>
							<link>?body=product@product_sort</link>
						</item>
						<item>
							<name>상품진열 설정</name>
							<link>?body=product@product_sort_set</link>
						</item>
						<item>
							<name>사은품 관리</name>
							<link>?body=promotion@product_gift_list</link>
						</item>
					</rel>
				</small>
				<small>
					<name>상품<?=($body == 'product@product_register' && $_GET['pno']) ? '수정' : '등록'?></name>
					<pgcode>2125</pgcode>
					<mcode>C0005</mcode>
					<link>product@product_register</link>
					<up_date>2017-12-18</up_date>
					<rel>
						<item>
							<name>공통정보 관리</name>
							<link>?body=product@product_common</link>
						</item>
						<item>
							<name>항목관리</name>
							<link>?body=product@product_filed</link>
						</item>
						<item>
							<name>옵션세트 관리</name>
							<link>?body=product@product_option</link>
						</item>
						<item>
							<name>사입처 관리</name>
							<link>?body=product@provider</link>
						</item>
					</rel>
				</small>
                <small>
                    <name>사입처 검색</name>
                    <pgcode>2120</pgcode>
                    <mcode>C0004</mcode>
                    <link>product@provider_select</link>
                    <hidden>Y</hidden>
                </small>
                <small>
                    <name>텍스트옵션 추가</name>
                    <pgcode>2120</pgcode>
                    <mcode>C0004</mcode>
                    <link>product@product_option_text.frm</link>
                    <hidden>Y</hidden>
                </small>
                <small>
                    <name>옵션추가</name>
                    <pgcode>2120</pgcode>
                    <mcode>C0004</mcode>
                    <link>product@product_option.frm</link>
                    <hidden>Y</hidden>
                </small>
                <small>
                    <name>옵션세트 불러오기</name>
                    <pgcode>2120</pgcode>
                    <mcode>C0004</mcode>
                    <link>product@product_option_load.frm</link>
                    <hidden>Y</hidden>
                </small>

                <small>
                    <name>[INNERFRAME]상품 옵션 리스트</name>
                    <pgcode>2120</pgcode>
                    <mcode>C0004</mcode>
                    <link>product@product_option_list.frm</link>
                    <hidden>Y</hidden>
                </small>
                <small>
                    <name>[INNERFRAME]상품 파일첨부 폼</name>
                    <pgcode>2120</pgcode>
                    <mcode>C0004</mcode>
                    <link>product@product_file.frm</link>
                    <hidden>Y</hidden>
                </small>
                <small>
                    <name>소비자가/판매가 명칭변경</name>
                    <pgcode>2120</pgcode>
                    <mcode>C0004</mcode>
                    <link>product@product_nece_field</link>
                    <hidden>Y</hidden>
                </small>
                <small>
                    <name>재고관리 기본설정</name>
                    <pgcode>2120</pgcode>
                    <mcode>C0004</mcode>
                    <link>product@product_eatype_setting</link>
                    <hidden>Y</hidden>
                </small>
                <small>
                    <name>스마트스토어 상품정보고시 설정</name>
                    <pgcode>2120</pgcode>
                    <mcode>C0004</mcode>
                    <link>product@product_store_summary_list</link>
                    <hidden>Y</hidden>
                </small>
                <small>
                    <name>카카오스토어 상품정보고시 설정</name>
                    <pgcode>2120</pgcode>
                    <mcode>C0004</mcode>
                    <link>product@kakaoTalkstore_ann</link>
                    <hidden>Y</hidden>
                </small>
				<?php if($cfg['use_set_product'] == 'Y') { ?>
				<small>
					<name>세트상품<?=($body == 'product@set_register' && $_GET['pno']) ? '수정' : '등록'?></name>
					<pgcode>2140</pgcode>
					<mcode>C0284</mcode>
					<link>product@set_register</link>
					<new_date>2020-04-13</new_date>
				</small>
				<?php } ?>
				<small>
					<name>상품진열순서</name>
					<pgcode>2130</pgcode>
					<mcode>C0006</mcode>
					<link>product@product_sort</link>
					<up_date>2017-08-07</up_date>
					<rel>
						<item>
							<name>상품수정/관리</name>
							<link>?body=product@product_list</link>
						</item>
						<item>
							<name>기본 매장분류관리</name>
							<link>?body=product@catework</link>
						</item>
					</rel>
				</small>
				<small>
					<name>기본 매장분류관리</name>
					<pgcode>2010</pgcode>
					<mcode>C0001</mcode>
					<link>product@catework</link>
					<rel>
						<item>
							<name>메인기획상품분류관리</name>
							<link>?body=product@catework&amp;ctype=2</link>
						</item>
						<item>
							<name>상품수정/관리</name>
							<link>?body=product@product_list</link>
						</item>
					</rel>
				</small>
				<small>
					<name>매장분류(<?=$cfg['xbig_name']?>)</name>
					<pgcode>2030</pgcode>
					<mcode>C0002</mcode>
					<link>product@catework&amp;ctype=4</link>
					<modify>
						<?=$cfg['xbig_mng'] != 'Y' ? '미사용' : '설정'?>___
						product@product_cate
					</modify>
					<rel>
						<item>
							<name>상품수정/관리</name>
							<link>?body=product@product_list</link>
						</item>
						<item>
							<name>추가매장분류 설정</name>
							<link>?body=product@product_cate</link>
						</item>
					</rel>
				</small>
				<small>
					<name>매장분류(<?=$cfg['ybig_name']?>)</name>
					<pgcode>2040</pgcode>
					<mcode>C0003</mcode>
					<link>product@catework&amp;ctype=5</link>
					<modify>
						<?=$cfg['ybig_mng'] != 'Y' ? '미사용' : '설정'?>___
						product@product_cate
					</modify>
					<rel>
						<item>
							<name>상품수정/관리</name>
							<link>?body=product@product_list</link>
						</item>
						<item>
							<name>추가매장분류 설정</name>
							<link>?body=product@product_cate</link>
						</item>
					</rel>
				</small>
				<small>
					<name>상품 메모</name>
					<pgcode>2371</pgcode>
					<mcode>C0079</mcode>
					<link>product@product_memo_list</link>
					<new_date>2017-08-21</new_date>
				</small>
				<small>
					<name>상품수정 내역</name>
					<pgcode>2370</pgcode>
					<mcode>C0075</mcode>
					<link>product@product_log</link>
					<rel>
						<item>
							<name>상품조회</name>
							<link>?body=product@product_list</link>
						</item>
					</rel>
				</small>
				<small>
					<name><?=$cfg['product_sell_price_name']?>변경 내역</name>
					<pgcode>2390</pgcode>
					<mcode>C0278</mcode>
					<link>product@product_price_log</link>
					<new_date>2019-10-29</new_date>
				</small>
				<small>
					<name>상품 휴지통</name>
					<pgcode>2380</pgcode>
					<mcode>C0199</mcode>
					<link>product@product_trash</link>
					<modify>
						설정___
						product@product_common
					</modify>
					<count><?=getTrashBoxRows('product')?></count>
					<new_date>2017-01-16</new_date>
				</small>
			</mid>
			<mid name="메인/기획전관리" pgcode="2200">
				<small>
					<name>기획전 분류</name>
					<pgcode>2210</pgcode>
					<mcode>N0007</mcode>
					<link>product@catework&amp;ctype=2</link>
					<rel>
						<item>
							<name>상품조회</name>
							<link>?body=product@product_list</link>
						</item>
						<item>
							<name>기본 매장분류관리</name>
							<link>?body=product@catework</link>
						</item>
					</rel>
				</small>
				<small>
					<name>기획전 상품진열순서</name>
					<pgcode>2220</pgcode>
					<mcode>N0008</mcode>
					<link>product@product_special_sort</link>
					<rel>
						<item>
							<name>상품조회</name>
							<link>?body=product@product_list</link>
						</item>
						<item>
							<name>기획전 분류</name>
							<link>?body=product@catework&amp;ctype=2</link>
						</item>
					</rel>
				</small>
				<small>
					<name>프로모션 기획전</name>
					<pgcode>2230</pgcode>
					<mcode>C0500</mcode>
					<new_date>2018-12-10</new_date>
					<link>product@promotion_list</link>
					<rel>
						<item>
							<name>프로모션 기획전 등록</name>
							<link>?body=product@promotion_register</link>
						</item>
					</rel>
				</small>
				<small>
					<name>프로모션 기획전 등록</name>
					<pgcode>2230</pgcode>
					<mcode>C0500</mcode>
					<new_date>2018-12-10</new_date>
					<link>product@promotion_register</link>
					<hidden>Y</hidden>
				</small>
				<small>
					<name>프로모션 상품그룹 관리</name>
					<pgcode>2240</pgcode>
					<mcode>N0501</mcode>
					<new_date>2018-12-10</new_date>
					<link>product@promotion_group_list</link>
				</small>
			</mid>
			<?php if ($cfg['use_partner_shop'] == 'Y') { ?>
			<mid name="입점사 관리" pgcode='2600'>
				<small>
					<name>입점사 관리</name>
					<pgcode>2610</pgcode>
					<mcode>C0236</mcode>
					<link>product@product_join_shop</link>
					<up_date>2018-04-02</up_date>
					<modify>
						설정___
						config@partner_shop
					</modify>
				</small>
				<small>
					<name>입점사 관리</name>
					<pgcode>2610</pgcode>
					<mcode>C0236</mcode>
					<link>product@product_join_shop_register</link>
					<hidden>Y</hidden>
				</small>
				<?php if ($cfg['use_partner_shop'] == 'Y' && $cfg['partner_prd_accept'] == 'Y') { ?>
				<small>
					<name>상품 등록/수정 신청내역</name>
					<pgcode>2620</pgcode>
					<mcode>C0237</mcode>
					<link>product@product_rev</link>
				</small>
				<?php } ?>
			</mid>
			<?php } ?>
			<mid name="부가관리" pgcode="2300">
				<small>
					<name>추가항목 관리</name>
					<pgcode>2310</pgcode>
					<mcode>C0009</mcode>
					<link>product@product_filed</link>
				</small>
				<small>
					<name>상품정보제공고시 관리</name>
					<pgcode>2315</pgcode>
					<mcode>C0238</mcode>
					<link>product@product_definition</link>
				</small>
				<small>
					<name>상품정보제공고시 관리</name>
					<pgcode>2315</pgcode>
					<mcode>C0238</mcode>
					<link>product@product_definition_register</link>
					<hidden>Y</hidden>
				</small>
				<small>
					<name>카카오톡스토어 정보고시 등록</name>
					<pgcode>2316</pgcode>
					<link>product@product_definition_talkstore_register</link>
					<hidden>Y</hidden>
				</small>
				<small>
					<name>스마트스토어 정보고시 등록</name>
					<pgcode>2311</pgcode>
					<mcode>C0011</mcode>
					<link>product@product_definition_smartstore_register</link>
					<hidden>Y</hidden>
				</small>
				<small>
					<name>옵션세트 관리</name>
					<pgcode>2320</pgcode>
					<mcode>C0013</mcode>
					<link>product@product_option</link>
				</small>
				<small>
					<name>컬러칩 관리</name>
					<pgcode>2322</pgcode>
					<mcode>C0239</mcode>
					<link>product@product_option_colorchip</link>
				</small>
				<small>
					<name>HS코드 관리</name>
					<pgcode>2321</pgcode>
					<mcode>C0015</mcode>
					<link>product@hs_code</link>
				</small>
				<small>
					<name>상품아이콘 관리</name>
					<pgcode>2330</pgcode>
					<mcode>C0014</mcode>
					<link>product@product_icon</link>
					<rel>
						<item>
							<name>할인/적립 이벤트</name>
							<link>?body=promotion@event</link>
						</item>
						<item>
							<name>무료배송 이벤트</name>
							<link>?body=promotion@event_delivery</link>
						</item>
					</rel>
				</small>
				<small>
					<name>상품 일반/항목 설정</name>
					<pgcode>2340</pgcode>
					<mcode>C0072</mcode>
					<link>product@product_common</link>
					<rel>
						<item>
							<name>상품조회</name>
							<link>?body=product@product_list</link>
						</item>
					</rel>
				</small>
				<small>
					<name>사입처 관리</name>
					<pgcode>2350</pgcode>
					<mcode>C0016</mcode>
					<link>product@provider</link>
				</small>
				<small>
					<name>사입처 등록</name>
					<pgcode>2361</pgcode>
					<mcode>C0016</mcode>
					<link>product@provider_register</link>
					<hidden>Y</hidden>
				</small>
				<small>
					<name>사입처 일괄 등록</name>
					<pgcode>2362</pgcode>
					<mcode>C0181</mcode>
					<link>product@provider_in</link>
				</small>
			</mid>
			<mid name="상품일괄등록" pgcode='2500'>
				<small>
					<name>엑셀양식 다운로드</name>
					<pgcode>2510</pgcode>
					<mcode>C0241</mcode>
					<link>product@product_download</link>
				</small>
				<small>
					<name>엑셀일괄 업로드</name>
					<pgcode>2520</pgcode>
					<mcode>C0242</mcode>
					<link>product@product_upload</link>
				</small>
				<small>
					<name>외부스토어 상품 전송</name>
					<pgcode>2520</pgcode>
					<mcode>C0242</mcode>
					<link>product@product_upload_external</link>
                    <hidden>Y</hidden>
				</small>
			</mid>
			<mid name="상품설정" pgcode='2400'>
				<small>
					<name>상품이미지 설정</name>
					<pgcode>2410</pgcode>
					<mcode>C0073</mcode>
					<link>product@product_image</link>
					<rel>
						<item>
							<name>상품조회</name>
							<link>?body=product@product_list</link>
						</item>
					</rel>
				</small>
				<small>
					<name>상품정렬 설정</name>
					<pgcode>2420</pgcode>
					<mcode>C0076</mcode>
					<link>product@product_sort_set</link>
					<rel>
						<item>
							<name>상품조회</name>
							<link>?body=product@product_list</link>
						</item>
						<item>
							<name>상품진열</name>
							<link>?body=product@product_sort</link>
						</item>
					</rel>
				</small>
				<small>
					<name>상품 엑셀 양식 설정</name>
					<pgcode>2430</pgcode>
					<mcode>C0077</mcode>
					<link>product@product_excel_config</link>
					<rel>
						<item>
							<name>상품조회</name>
							<link>?body=product@product_list</link>
						</item>
					</rel>
				</small>
				<small>
					<name>추가매장분류 설정</name>
					<pgcode>2450</pgcode>
					<mcode>C0074</mcode>
					<link>product@product_cate</link>
					<rel>
						<item>
							<name>기본 매장분류관리</name>
							<link>?body=product@catework</link>
						</item>
					</rel>
				</small>
			</mid>
		</big>
		<big name="주문/배송" category="order" pgcode="3000" link="body=3010">
			<mid name="주문조회" pgcode='3000' mcode='order_7'>
				<small>
					<name>전체주문조회</name>
					<pgcode>3010</pgcode>
					<mcode>C0021</mcode>
					<link>order@order_list</link>
					<up_date>2018-07-30</up_date>
					<rel>
						<item>
							<name>주문 설정</name>
							<link>?body=config@order</link>
						</item>
						<item>
							<name>장바구니 설정</name>
							<link>?body=config@cart</link>
						</item>
						<item>
							<name>배송비 설정</name>
							<link>?body=config@delivery</link>
						</item>
					</rel>
				</small>
                <small>
                    <name>배송 후 주문상품 교환</name>
                    <pgcode>3010</pgcode>
                    <mcode>C0021</mcode>
                    <link>order@order_exchg.frm</link>
                    <hidden>Y</hidden>
                </small>
				<small>
					<name>전체주문조회</name>
					<pgcode>3010</pgcode>
					<mcode>C0021</mcode>
					<link>order@order_multi_result</link>
                    <hidden>Y</hidden>
				</small>
                <small>
                    <name>주문 상세보기</name>
                    <pgcode>3010</pgcode>
                    <mcode>C0021</mcode>
                    <link>order@order_view.frm</link>
                    <hidden>Y</hidden>
                </small>
                <small>
                    <name>주문 검색설정</name>
                    <pgcode>3010</pgcode>
                    <mcode>C0021</mcode>
                    <link>order@order_search.frm</link>
                    <hidden>Y</hidden>
                </small>
				<?php if ($cfg['use_sbscr']=='Y') { ?>
				<small>
					<name>정기배송 주문조회</name>
					<pgcode>3080</pgcode>
					<mcode>C0300</mcode>
					<link>order@sbscr_list</link>
				</small>
				<?php } ?>
				<?php if ($scfg->comp(array('opmk_api' => 'shopLinker', 'openmarket_scrap_order' => '40')) == true) { ?>
				<small>
					<name>오픈마켓 수집/등록대기</name>
					<pgcode>3011</pgcode>
					<mcode>C0021</mcode>
					<link>order@order_list&amp;order_stat_group=9</link>
				</small>
				<?php } ?>
				<small>
					<name>주문상품 수량옵션 통계</name>
					<pgcode>3090</pgcode>
					<link>order@order_product</link>
					<hidden>Y</hidden>
					<rel>
						<item>
							<name>주문상품별옵션 엑셀설정</name>
							<link>?body=config@order_product_config</link>
						</item>
					</rel>
				</small>
				<small>
					<name>주문수동등록</name>
					<pgcode>3020</pgcode>
					<mcode>C0182</mcode>
					<link>order@order_admin</link>
				</small>
				<small>
					<name>주문 메모</name>
					<pgcode>3030</pgcode>
					<mcode>C0183</mcode>
					<link>order@order_memo_list</link>
					<up_date>2017-08-21</up_date>
				</small>
				<small>
					<name>PG승인관리</name>
					<pgcode>3040</pgcode>
					<mcode>C0023</mcode>
					<link>order@order_list&amp;order_stat_group=8</link>
					<rel>
						<item>
							<name>전체주문조회</name>
							<link>?body=order@order_list</link>
						</item>
						<item>
							<name>결제방법 설정</name>
							<link>?body=config@account</link>
						</item>
						<item>
							<name>신용카드PG 신청</name>
							<link>?body=config@pg_help</link>
						</item>
					</rel>
				</small>
				<small>
					<name>PG승인결과대사</name>
					<pgcode>3040</pgcode>
					<mcode>C0023</mcode>
					<link>order@pg_compare</link>
					<new_date>2019-09-17</new_date>
					<hidden>Y</hidden>
				</small>
				<small>
					<name>결제내역</name>
					<pgcode>3050</pgcode>
					<link>order@order_payment_list</link>
					<hidden>Y</hidden>
				</small>
				<small>
					<name>무통장 자동입금확인</name>
					<pgcode>3060</pgcode>
					<mcode>C0024</mcode>
					<onclick>goMywisa('?body=wing@bank_list')</onclick>
				</small>
                <small>
                    <name>무통장 자동입금확인</name>
                    <pgcode>3060</pgcode>
                    <mcode>C0024</mcode>
                    <link>wing@bank_list</link>
                    <hidden>Y</hidden>
                </small>
				<?php if ($navi_href[0] == 'order' || $_GET['body'] == '3010' || $_GET['mode'] == '3000') { ?>
				<small>
					<name>주문 휴지통</name>
					<pgcode>3070</pgcode>
					<mcode>C0200</mcode>
					<link>order@order_trash</link>
					<modify>
						설정___
						config@order
					</modify>
					<count><?=getTrashBoxRows('order')?></count>
					<new_date>2017-01-16</new_date>
				</small>
				<?php } ?>
			</mid>
			<mid name="배송처리" pgcode='3200' mcode='order_11'>
				<small>
					<name>주문일괄배송처리</name>
					<pgcode>3210</pgcode>
					<mcode>C0027</mcode>
					<link>order@delivery_fileinput</link>
					<rel>
						<item>
							<name>배송업체 설정</name>
							<link>?body=config@delivery_prv</link>
						</item>
						<item>
							<name>배송비 설정</name>
							<link>?body=config@delivery</link>
						</item>
					</rel>
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
					<pgcode>3220</pgcode>
					<mcode>C0028</mcode>
					<link>order@delivery</link>
					<rel>
						<item>
							<name>배송업체 설정</name>
							<link>?body=config@delivery_prv</link>
						</item>
						<item>
							<name>배송비 설정</name>
							<link>?body=config@delivery</link>
						</item>
						<item>
							<name>부분배송 설정</name>
							<link>?body=config@order_part</link>
						</item>
					</rel>
				</small>
				<small>
					<name>배송완료</name>
					<pgcode>3230</pgcode>
					<mcode>C0029</mcode>
					<link>order@delivery_finish</link>
					<rel>
						<item>
							<name>배송업체 설정</name>
							<link>?body=config@delivery_prv</link>
						</item>
						<item>
							<name>배송비 설정</name>
							<link>?body=config@delivery</link>
						</item>
					</rel>
				</small>
				<small>
					<name>맞춤박스</name>
					<pgcode>3299</pgcode>
					<link>order@mcbox</link>
					<hidden>Y</hidden>
				</small>
			</mid>
			<mid name="취소/환불/반품/교환" pgcode='3300' mcode='order_15'>
				<small>
					<name>취소/환불 진행</name>
					<pgcode>3310</pgcode>
					<mcode>C0030</mcode>
					<link>order@order_list&amp;order_stat_group=4</link>
					<rel>
						<item>
							<name>전체주문조회</name>
							<link>?body=order@order_list</link>
						</item>
						<item>
							<name>주문설정</name>
							<link>?body=config@order</link>
						</item>
					</rel>
				</small>
				<small>
					<name>취소/환불 완료</name>
					<pgcode>3320</pgcode>
					<mcode>C0031</mcode>
					<link>order@order_list&amp;order_stat_group=5</link>
					<rel>
						<item>
							<name>전체주문조회</name>
							<link>?body=order@order_list</link>
						</item>
						<item>
							<name>주문설정</name>
							<link>?body=config@order</link>
						</item>
					</rel>
				</small>
				<small>
					<name>반품/교환 진행</name>
					<pgcode>3330</pgcode>
					<mcode>C0032</mcode>
					<link>order@order_list&amp;order_stat_group=6</link>
					<rel>
						<item>
							<name>전체주문조회</name>
							<link>?body=order@order_list</link>
						</item>
						<item>
							<name>주문설정</name>
							<link>?body=config@order</link>
						</item>
					</rel>
				</small>
				<small>
					<name>반품/교환 완료</name>
					<pgcode>3340</pgcode>
					<mcode>C0033</mcode>
					<link>order@order_list&amp;order_stat_group=7</link>
					<rel>
						<item>
							<name>전체주문조회</name>
							<link>?body=order@order_list</link>
						</item>
						<item>
							<name>주문설정</name>
							<link>?body=config@order</link>
						</item>
					</rel>
				</small>
			</mid>
			<mid name="현금영수증관리" pgcode='3600' mcode='order_25'>
				<small>
					<name>현금영수증 관리</name>
					<pgcode>3610</pgcode>
					<mcode>C0034</mcode>
					<link>order@order_cash_receipt_new</link>
					<rel>
						<item>
							<name>현금영수증 설정</name>
							<link>?body=config@cash_receipt</link>
						</item>
					</rel>
				</small>
				<small>
					<name>현금영수증 개별 발급</name>
					<pgcode>3620</pgcode>
					<mcode>C0243</mcode>
					<link>order@order_cash_receipt_sub</link>
					<rel>
						<item>
							<name>현금영수증 설정</name>
							<link>?body=config@cash_receipt</link>
						</item>
					</rel>
				</small>
			</mid>
		</big>
		<big name="고객CRM" category="member" pgcode="5000" link="body=5010">
			<mid name="회원종합관리" pgcode='5000' mcode='member_1'>
				<small>
					<name>회원 조회</name>
					<pgcode>5010</pgcode>
					<mcode>C0035</mcode>
					<link>member@member_list</link>
					<rel>
						<item>
							<name>회원그룹 설정</name>
							<link>?body=member@member_group</link>
						</item>
						<item>
							<name>자동메일 설정</name>
							<link>?body=member@email_config</link>
						</item>
						<item>
							<name>회원정보엑셀 설정</name>
							<link>?body=member@member_excel_config</link>
						</item>
						<item>
							<name>회원접속통계</name>
							<link>?body=log@member_access_log</link>
						</item>
					</rel>
				</small>
                <small>
                    <name>회원정보&gt;CRM 종합정보</name>
                    <pgcode>5010</pgcode>
                    <mcode>C0035</mcode>
                    <link>member@member_view.frm</link>
                    <hidden>Y</hidden>
                </small>
                <small>
                    <name>회원정보&gt;개인정보</name>
                    <pgcode>5010</pgcode>
                    <mcode>C0035</mcode>
                    <link>member@member_view.frm&amp;smode=info</link>
                    <hidden>Y</hidden>
                </small>
                <small>
                    <name>회원정보&gt;개인정보</name>
                    <pgcode>5010</pgcode>
                    <mcode>C0035</mcode>
                    <link>member@member_view.frm&amp;smode=info</link>
                    <hidden>Y</hidden>
                </small>
                <small>
                    <name>회원정보&gt;주문내역</name>
                    <pgcode>5010</pgcode>
                    <mcode>C0035</mcode>
                    <link>member@member_view.frm&amp;smode=order</link>
                    <hidden>Y</hidden>
                </small>
                <small>
                    <name>회원정보&gt;적립금내역</name>
                    <pgcode>5010</pgcode>
                    <mcode>C0035</mcode>
                    <link>member@member_view.frm&amp;smode=milage</link>
                    <hidden>Y</hidden>
                </small>
                <small>
                    <name>회원정보&gt;예치금내역</name>
                    <pgcode>5010</pgcode>
                    <mcode>C0035</mcode>
                    <link>member@member_view.frm&amp;smode=emoney</link>
                    <hidden>Y</hidden>
                </small>
                <small>
                    <name>회원정보&gt;쿠폰 발급내역</name>
                    <pgcode>5010</pgcode>
                    <mcode>C0035</mcode>
                    <link>member@member_view.frm&amp;smode=cp_list</link>
                    <hidden>Y</hidden>
                </small>
                <small>
                    <name>회원정보&gt;상품Q&amp;A</name>
                    <pgcode>5010</pgcode>
                    <mcode>C0035</mcode>
                    <link>member@member_view.frm&amp;smode=qna</link>
                    <hidden>Y</hidden>
                </small>
                <small>
                    <name>회원정보&gt;1:1상담 내역</name>
                    <pgcode>5010</pgcode>
                    <mcode>C0035</mcode>
                    <link>member@member_view.frm&amp;smode=1to1</link>
                    <hidden>Y</hidden>
                </small>
                <small>
                    <name>회원정보&gt;상품후기</name>
                    <pgcode>5010</pgcode>
                    <mcode>C0035</mcode>
                    <link>member@member_view.frm&amp;smode=review</link>
                    <hidden>Y</hidden>
                </small>
                <small>
                    <name>회원정보&gt;장바구니</name>
                    <pgcode>5010</pgcode>
                    <mcode>C0035</mcode>
                    <link>member@member_view.frm&amp;smode=cart</link>
                    <hidden>Y</hidden>
                </small>
                <small>
                    <name>회원정보&gt;위시리스트</name>
                    <pgcode>5010</pgcode>
                    <mcode>C0035</mcode>
                    <link>member@member_view.frm&amp;smode=wishlist</link>
                    <hidden>Y</hidden>
                </small>
                <small>
                    <name>회원정보&gt;회원메모</name>
                    <pgcode>5010</pgcode>
                    <mcode>C0035</mcode>
                    <link>member@member_view.frm&amp;smode=memo</link>
                    <hidden>Y</hidden>
                </small>
                <small>
                    <name>회원정보&gt;회원그룹 변경내역</name>
                    <pgcode>5010</pgcode>
                    <mcode>C0035</mcode>
                    <link>member@member_view.frm&amp;smode=level</link>
                    <hidden>Y</hidden>
                </small>
                <small>
                    <name>회원정보&gt;접속로그</name>
                    <pgcode>5010</pgcode>
                    <mcode>C0035</mcode>
                    <link>member@member_view.frm&amp;smode=log</link>
                    <hidden>Y</hidden>
                </small>
                <small>
                    <name>윙문자 발송</name>
                    <pgcode>5010</pgcode>
                    <mcode>C0035</mcode>
                    <link>member@sms_sender.frm</link>
                    <hidden>Y</hidden>
                </small>
                <small>
                    <name>이메일 발송</name>
                    <pgcode>5010</pgcode>
                    <mcode>C0035</mcode>
                    <link>member@email_sender.frm</link>
                    <hidden>Y</hidden>
                </small>
				<small>
					<name>탈퇴 회원</name>
					<pgcode>5020</pgcode>
					<mcode>C0037</mcode>
					<link>member@member_list&amp;withdraw=1</link>
				</small>
				<small>
					<name>휴면 조회</name>
					<pgcode>5030</pgcode>
					<mcode>C0244</mcode>
					<link>member@member_deleted</link>
				</small>
				<small>
					<name>수동회원등록</name>
					<pgcode>5035</pgcode>
					<mcode>C0302</mcode>
					<link>member@member_create</link>
    				<?php if ($cache_account['account_idx'] != '31187') { ?>
                    <hidden>Y</hidden>
                    <?php } ?>
				</small>
				<small>
					<name>회원분석</name>
					<pgcode>5040</pgcode>
					<mcode>C0036</mcode>
					<link>member@member_analysis</link>
					<rel>
						<item>
							<name>가입/탈퇴/로그인 설정</name>
							<link>?body=member@member</link>
						</item>
						<item>
							<name>가입추가항목 설정</name>
							<link>?body=member@member_addinfo</link>
						</item>
					</rel>
				</small>
				<small>
					<name>회원메모</name>
					<pgcode>5050</pgcode>
					<mcode>C0245</mcode>
					<link>member@member_memo_list</link>
				</small>
				<small>
					<name>적립금</name>
					<pgcode>5060</pgcode>
					<mcode>C0045</mcode>
					<link>member@milage_list</link>
					<rel>
						<item>
							<name>적립금 설정</name>
							<link>?body=config@milage</link>
						</item>
					</rel>
				</small>
				<small>
					<name>예치금</name>
					<pgcode>5070</pgcode>
					<mcode>C0046</mcode>
					<link>member@emoney_list</link>
					<rel>
						<item>
							<name>예치금 설정</name>
							<link>?body=config@emoney</link>
						</item>
					</rel>
				</small>
			</mid>
			<mid name="상품문의" pgcode='5500' mcode='member_25'>
				<small>
					<name>상품Q&amp;A</name>
					<pgcode>5090</pgcode>
					<mcode>C0038</mcode>
					<link>member@product_qna</link>
					<up_date>2018-07-30</up_date>
					<rel>
						<item>
							<name>상품문의 설정</name>
							<link>?body=member@product_inquery_config</link>
						</item>
						<item>
							<name>상품조회</name>
							<link>?body=product@product_list</link>
						</item>
					</rel>
				</small>
                <small>
                    <name>상품Q&amp;A 보기</name>
                    <pgcode>5090</pgcode>
                    <mcode>C0038</mcode>
                    <link>member@product_qna_view.frm</link>
                    <hidden>Y</hidden>
                </small>
				<small>
					<name>1:1 상담</name>
					<pgcode>5520</pgcode>
					<mcode>C0041</mcode>
					<link>member@1to1</link>
					<rel>
						<item>
							<name>전체주문조회</name>
							<link>?body=order@order_list</link>
						</item>
					</rel>
				</small>
                <small>
                    <name>1:1 상담내용보기</name>
                    <pgcode>5520</pgcode>
                    <mcode>C0041</mcode>
                    <link>member@1to1_view.frm</link>
                    <hidden>Y</hidden>
                </small>
				<small>
					<name>상품문의 설정</name>
					<pgcode>5530</pgcode>
					<mcode>C0104</mcode>
					<link>member@product_inquery_config</link>
					<rel>
						<item>
							<name>상품Q&amp;A 관리</name>
							<link>?body=member@product_qna</link>
						</item>
					</rel>
					<up_date>2019-10-15</up_date>
				</small>
				<small>
					<name>자주쓰는 댓글 설정</name>
					<pgcode>5531</pgcode>
					<mcode>C0108</mcode>
					<link>member@often_comment&amp;often=qna</link>
					<new_date>2019-03-25</new_date>
				</small>
				<small>
					<name>상품Q&amp;A 휴지통</name>
					<pgcode>5540</pgcode>
					<mcode>C0203</mcode>
					<link>member@product_qna_trash</link>
					<modify>
						설정___member@product_inquery_config
					</modify>
					<count><?=getTrashBoxRows('qna')?></count>
					<new_date>2017-01-16</new_date>
				</small>
			</mid>
			<mid name="상품후기" pgcode='5600' mcode='member_26'>
				<small>
					<name>상품후기</name>
					<pgcode>5610</pgcode>
					<mcode>C0039</mcode>
					<link>member@product_review</link>
					<rel>
						<item>
							<name>상품후기 설정</name>
							<link>?body=member@product_review_config</link>
						</item>
						<item>
							<name>상품조회</name>
							<link>?body=product@product_list</link>
						</item>
					</rel>
				</small>
                <small>
                    <name>고객 상품후기 보기</name>
                    <pgcode>5610</pgcode>
                    <mcode>C0039</mcode>
                    <link>member@product_review_view.frm</link>
                    <hidden>Y</hidden>
                </small>
				<small>
					<name>상품후기 댓글</name>
					<pgcode>5620</pgcode>
					<mcode>C0040</mcode>
					<link>member@product_review_comment</link>
					<rel>
						<item>
							<name>상품후기 설정</name>
							<link>?body=member@product_review_config</link>
						</item>
					</rel>
				</small>
				<small>
					<name>상품후기 설정</name>
					<pgcode>5630</pgcode>
					<mcode>C0105</mcode>
					<link>member@product_review_config</link>
					<rel>
						<item>
							<name>상품후기 관리</name>
							<link>?body=member@product_review</link>
						</item>
					</rel>
				</small>
				<small>
					<name>자주쓰는 댓글 설정</name>
					<pgcode>5532</pgcode>
					<mcode>C0112</mcode>
					<link>member@often_comment&amp;often=review</link>
					<new_date>2019-03-25</new_date>
				</small>
				<small>
					<name>상품후기 휴지통</name>
					<pgcode>5650</pgcode>
					<mcode>C0204</mcode>
					<link>member@product_rev_trash</link>
					<modify>
						설정___member@product_review_config
					</modify>
					<count><?=getTrashBoxRows('review')?></count>
					<new_date>2017-01-16</new_date>
				</small>
				<small>
					<name>크리마 리뷰 설정</name>
					<pgcode>5640</pgcode>
					<mcode>C0260</mcode>
					<link>member@crema_config</link>
				</small>
			</mid>
			<mid name="알림/채팅 설정" pgcode='5700'>
				<small>
					<name>고객 문자알림 설정</name>
					<pgcode>5710</pgcode>
					<mcode>C0102</mcode>
					<link>member@sms_config</link>
					<rel>
						<item>
							<name>회원조회</name>
							<link>?body=member@member_list</link>
						</item>
						<item>
							<name>서비스현황</name>
							<link>?body=wing@service_status</link>
						</item>
						<item>
							<name>미입금자동SMS통보신청</name>
							<link>?body=config@account#sms</link>
						</item>
					</rel>
				</small>
				<small>
					<name>자동 이메일 설정</name>
					<pgcode>5720</pgcode>
					<mcode>C0100</mcode>
					<link>member@email_config</link>
					<rel>
						<item>
							<name>자동 이메일 편집</name>
							<link>?body=design@email_msg</link>
						</item>
						<item>
							<name>회원조회</name>
							<link>?body=member@member_list</link>
						</item>
						<item>
							<name>서비스현황</name>
							<link>?body=wing@service_status</link>
						</item>
					</rel>
				</small>
				<?php if ($cfg['alimtalk_id'] && $cfg['alimtalk_profile_key']) { ?>
				<small>
					<name>카카오 알림톡 메시지 관리</name>
					<pgcode>5745</pgcode>
					<mcode>C0217</mcode>
					<link>member@kakao_amt_msg</link>
				</small>
				<small>
					<name>카카오 알림톡 메시지 관리</name>
					<pgcode>5745</pgcode>
					<mcode>C0217</mcode>
					<link>member@kakao_amt_reg</link>
					<hidden>Y</hidden>
				</small>
				<?php } else { ?>
				<small>
					<name>카카오 알림톡 신청</name>
					<pgcode>5745</pgcode>
					<mcode>C0217</mcode>
					<onclick>goMywisa('?body=wing@service@alimtalk')</onclick>
				</small>
				<?php } ?>
				<?php if ($cfg['notify_restock_use'] == "Y") { ?>
                    <small>
                        <name>재입고 알림 신청 내역</name>
                        <pgcode>5770</pgcode>
						<mcode>N0218</mcode>
                        <link>member@notify_restock</link>
						<new_date>2019-06-17</new_date>
                    </small>
				<?php } ?>
                <small>
                    <name>재입고 알림 설정</name>
                    <pgcode>5780</pgcode>
					<mcode>N0219</mcode>
                    <link>member@notify_restock_config</link>
					<new_date>2019-06-17</new_date>
                </small>
				<small>
					<name>카카오 상담톡</name>
					<pgcode>5746</pgcode>
					<mcode>C0219</mcode>
					<link>member@happytalk</link>
					<new_date>2019-02-19</new_date>
				</small>
				<small>
					<name>Channel 채팅 설정</name>
					<pgcode>5750</pgcode>
					<mcode>C0218</mcode>
					<link>member@channel</link>
					<new_date>2018-04-23</new_date>
				</small>
				<small>
					<name>Easemob 채팅 설정</name>
					<pgcode>5760</pgcode>
					<mcode>N9999</mcode>
					<link>member@easemob</link>
					<new_date>2017-11-06</new_date>
				</small>
			</mid>
			<mid name="회원설정" pgcode='5800'>
				<small>
					<name>가입/탈퇴/로그인 설정</name>
					<pgcode>5810</pgcode>
					<mcode>C0092</mcode>
					<link>member@member</link>
					<rel>
						<item>
							<name>회원조회</name>
							<link>?body=member@member_list</link>
						</item>
					</rel>
				</small>
				<small>
					<name>가입추가항목 설정</name>
					<pgcode>5820</pgcode>
					<mcode>C0093</mcode>
					<link>member@member_addinfo</link>
					<rel>
						<item>
							<name>회원조회</name>
							<link>?body=member@member_list</link>
						</item>
						<item>
							<name>회원분석</name>
							<link>?body=member@member_analysis</link>
						</item>
					</rel>
				</small>
				<small>
					<name>SNS 로그인 설정</name>
					<pgcode>5890</pgcode>
					<mcode>C0209</mcode>
					<link>member@sns_login</link>
					<rel>
						<item>
							<name>페이스북 픽셀 연동</name>
							<link>?body=openmarket@facebook</link>
						</item>
					</rel>
				</small>
				<small>
					<name>회원그룹 설정</name>
					<pgcode>5830</pgcode>
					<mcode>C0095</mcode>
					<link>member@member_group</link>
					<rel>
						<item>
							<name>회원조회</name>
							<link>?body=member@member_list</link>
						</item>
					</rel>
				</small>
                <small>
                    <name>회원그룹 추가 정보</name>
                    <pgcode>5830</pgcode>
                    <mcode>C0095</mcode>
                    <link>member@member_group_addinfo</link>
                    <hidden>Y</hidden>
                </small>
				<small>
					<name>특별회원그룹 설정</name>
					<pgcode>5840</pgcode>
					<mcode>C0214</mcode>
					<link>member@member_checker</link>
				</small>
				<small>
					<name>특별회원그룹 설정</name>
					<pgcode>5840</pgcode>
					<mcode>C0214</mcode>
					<link>member@member_checker_detail</link>
					<hidden>Y</hidden>
				</small>
				<small>
					<name>회원정보엑셀 설정</name>
					<pgcode>5860</pgcode>
					<mcode>C0098</mcode>
					<link>member@member_excel_config</link>
					<rel>
						<item>
							<name>회원조회</name>
							<link>?body=member@member_list</link>
						</item>
					</rel>
				</small>
				<?php if ($admin['level'] < 3) { ?>
				<small>
					<name>회원정보 다운로드내역</name>
					<pgcode>5870</pgcode>
					<mcode>C0099</mcode>
					<link>member@member_excel_log</link>
					<rel>
						<item>
							<name>회원조회</name>
							<link>?body=member@member_list</link>
						</item>
					</rel>
				</small>
				<?php } ?>
				<small>
					<name>광고성정보수신동의 설정</name>
					<pgcode>5875</pgcode>
					<mcode>N0100</mcode>
					<link>member@privacy</link>
				</small>
				<small>
					<name>080수신거부 설정</name>
					<pgcode>5881</pgcode>
					<mcode>N0101</mcode>
					<link>member@receive_deny</link>
				</small>
				<small>
					<name>i-PIN 설정</name>
					<pgcode>5880</pgcode>
					<mcode>C0094</mcode>
					<link>member@ipin</link>
				</small>
				<small>
					<name>KCB 본인확인</name>
					<pgcode>5885</pgcode>
					<mcode>C0303</mcode>
					<link>member@kcb</link>
				</small>
			</mid>
		</big>
		<big name="디자인관리" category="design" pgcode="7000" link="body=7120">
			<mid name="디자인관리" pgcode='7000' mcode='design_1'>
				<small>
					<name>스킨 가이드</name>
					<pgcode>7099</pgcode>
					<link>http://redirect.wisa.co.kr/selfdesign</link>
					<target>_blank</target>
					<new_date>2019-06-04</new_date>
				</small>
				<small>
					<name>스킨 관리</name>
					<pgcode>7010</pgcode>
					<mcode>C0049</mcode>
					<link>design@skin</link>
					<rel>
						<item>
							<name>스킨디자인샵</name>
							<link>http://redirect.wisa.co.kr/designshop</link>
						</item>
					</rel>
				</small>
				<small>
					<name>이미지 관리</name>
					<pgcode>7020</pgcode>
					<mcode>C0050</mcode>
					<link>design@common_img</link>
				</small>
				<small>
					<name>SEO 설정</name>
					<pgcode>7030</pgcode>
					<mcode>C0051</mcode>
					<link>design@seo</link>
					<up_date>2019-06-17</up_date>
				</small>
				<small>
					<name>스크립트 매니저</name>
					<pgcode>7035</pgcode>
					<mcode>C0191</mcode>
					<link>design@mkt_script_list</link>
					<up_date>2019-06-17</up_date>
				</small>
				<small>
					<name>스크립트 매니저</name>
					<pgcode>7035</pgcode>
					<mcode>C0196</mcode>
					<link>design@mkt_script_regist</link>
					<hidden>Y</hidden>
				</small>
				<small>
					<name>파비콘 설정</name>
					<pgcode>7050</pgcode>
					<mcode>C0053</mcode>
					<link>design@favicon</link>
				</small>
				<small>
					<name>배경음악 설정</name>
					<pgcode>7060</pgcode>
					<mcode>C0054</mcode>
					<hidden>Y</hidden>
					<link>design@bgm</link>
				</small>
				<small>
					<name>상품이미지 줌이펙트 설정</name>
					<pgcode>7070</pgcode>
					<mcode>C0212</mcode>
					<link>design@zoomEffect</link>
				</small>
				<small>
					<name>퀵프리뷰 설정</name>
					<pgcode>7090</pgcode>
					<mcode>N0211</mcode>
					<link>design@quickDetail</link>
				</small>
				<?php if (is_array($_apps_n) && in_array(1, $_apps_n)) { ?>
				<small>
					<name>퀵카트 설정</name>
					<pgcode>7095</pgcode>
					<mcode>C0249</mcode>
					<link>design@quickCart</link>
				</small>
				<?php } ?>
				<small>
					<name>페이지보안 설정</name>
					<pgcode>7040</pgcode>
					<mcode>C0052</mcode>
					<link>design@design_security</link>
				</small>
				<small>
					<name>호스팅업체정보 게시</name>
					<pgcode>7080</pgcode>
					<mcode>C0210</mcode>
					<link>design@hostingby</link>
				</small>
				<small>
					<name>인스타그램 연동</name>
					<pgcode>7092</pgcode>
					<mcode>C0247</mcode>
					<link>design@instagram</link>
				</small>
			</mid>
			<mid name="개별디자인편집" pgcode='7200' mcode='design_8'>
				<small>
					<name>배너 관리</name>
					<pgcode>7210</pgcode>
					<mcode>C0064</mcode>
					<link>design@design_banner</link>
					<rel>
						<item>
							<name>배너광고코드</name>
							<link>?body=openmarket@ban_list</link>
						</item>
					</rel>
				</small>
				<small>
					<name>배너분류 관리</name>
					<pgcode>7211</pgcode>
					<mcode>N0064</mcode>
					<link>design@design_banner_cate</link>
				</small>
				<small>
					<name>그룹배너 관리</name>
					<pgcode>7215</pgcode>
					<mcode>C0224</mcode>
					<link>design@group_banner</link>
                    <up_date>2020-11-09</up_date>
				</small>
				<small>
					<name>팝업 관리</name>
					<pgcode>7310</pgcode>
					<mcode>C0065</mcode>
					<link>design@design_popup</link>
					<rel>
						<item>
							<name>팝업스킨 편집</name>
							<link>?body=design@design_popup_frame</link>
						</item>
					</rel>
				</small>
				<small>
					<name>팝업 관리</name>
					<pgcode>7310</pgcode>
					<mcode>C0065</mcode>
					<link>design@design_popup_register</link>
					<hidden>Y</hidden>
				</small>
                <small>
                    <name>팝업 미리보기</name>
                    <pgcode>7310</pgcode>
                    <mcode>C0065</mcode>
                    <link>design@design_popup_preview.frm</link>
                    <hidden>Y</hidden>
                </small>
				<small>
					<name>팝업스킨 편집</name>
					<pgcode>7320</pgcode>
					<mcode>C0066</mcode>
					<link>design@design_popup_frame</link>
					<rel>
						<item>
							<name>팝업 관리</name>
							<link>?body=design@design_popup</link>
						</item>
					</rel>
				</small>
				<small>
					<name>팝업스킨 편집</name>
					<pgcode>7320</pgcode>
					<mcode>C0066</mcode>
					<link>design@design_popup_frame_register</link>
					<hidden>Y</hidden>
				</small>
				<small>
					<name>자동 이메일내용 편집</name>
					<pgcode>7410</pgcode>
					<mcode>C0067</mcode>
					<link>design@email_msg</link>
					<modify>
						설정___
						member@email_config
					</modify>
					<rel>
						<item>
							<name>자동 이메일 설정</name>
							<link>?body=member@email_config</link>
						</item>
						<item>
							<name>쇼핑몰정보 설정</name>
							<link>?body=config@info</link>
						</item>
					</rel>
				</small>
			</mid>
			<mid name="HTML 편집" pgcode='7100' mcode='design_26'>
				<small>
					<name>상단공통페이지 편집</name>
					<pgcode>7110</pgcode>
					<mcode>C0055</mcode>
					<link>design@layout&amp;part=%7B%7BT%7D%7D</link>
					<rel>
						<item>
							<name>스킨 가이드</name>
							<link>http://redirect.wisa.co.kr/selfdesign</link>
						</item>
					</rel>
				</small>
				<small>
					<name>하단공통페이지 편집</name>
					<pgcode>7115</pgcode>
					<mcode>C0248</mcode>
					<link>design@layout&amp;part=%7B%7BB%7D%7D</link>
					<rel>
						<item>
							<name>스킨 가이드</name>
							<link>http://redirect.wisa.co.kr/selfdesign</link>
						</item>
					</rel>
				</small>
				<small>
					<name>페이지 편집</name>
					<pgcode>7120</pgcode>
					<mcode>C0056</mcode>
					<link>design@editor</link>
					<rel>
						<item>
							<name>스킨디자인샵</name>
							<link>http://redirect.wisa.co.kr/designshop</link>
						</item>
						<item>
							<name>스킨 가이드</name>
							<link>http://redirect.wisa.co.kr/selfdesign</link>
						</item>
					</rel>
				</small>
                <small>
                    <name>사용자 생성 코드</name>
                    <pgcode>7120</pgcode>
                    <mcode>C0056</mcode>
                    <link>design@editor_user.frm</link>
                    <hidden>Y</hidden>
                </small>
				<small>
					<name>제공 코드 편집</name>
					<pgcode>7125</pgcode>
					<mcode>C0057</mcode>
					<link>design@editor_code</link>
					<rel>
						<item>
							<name>스킨 가이드</name>
							<link>http://redirect.wisa.co.kr/selfdesign</link>
						</item>
					</rel>
				</small>
				<small>
					<name>기본 텍스트 편집</name>
					<pgcode>7128</pgcode>
					<mcode>C0058</mcode>
					<link>design@text_edit</link>
					<rel>
						<item>
							<name>스킨 가이드</name>
							<link>http://redirect.wisa.co.kr/selfdesign</link>
						</item>
					</rel>
				</small>
				<small>
					<name>게시판 스킨 편집</name>
					<pgcode>7130</pgcode>
					<mcode>C0059</mcode>
					<link>design@board</link>
					<rel>
						<item>
							<name>스킨 가이드</name>
							<link>http://redirect.wisa.co.kr/selfdesign</link>
						</item>
					</rel>
				</small>
				<small>
					<name>게시판 스킨 관리</name>
					<pgcode>7130</pgcode>
					<mcode>C0059</mcode>
					<link>design@board_skin</link>
					<hidden>design@board_skin</hidden>
					<rel>
						<item>
							<name>스킨 가이드</name>
							<link>http://redirect.wisa.co.kr/selfdesign</link>
						</item>
					</rel>
				</small>
				<small>
					<name>네비게이터 편집</name>
					<pgcode>7140</pgcode>
					<mcode>C0060</mcode>
					<link>design@title</link>
					<rel>
						<item>
							<name>스킨 가이드</name>
							<link>http://redirect.wisa.co.kr/selfdesign</link>
						</item>
					</rel>
				</small>
				<small>
					<name>스타일 시트 편집</name>
					<pgcode>7150</pgcode>
					<mcode>C0061</mcode>
					<link>design@css</link>
					<rel>
						<item>
							<name>스킨 가이드</name>
							<link>http://redirect.wisa.co.kr/selfdesign</link>
						</item>
					</rel>
				</small>
				<small>
					<name>스크립트 편집</name>
					<pgcode>7160</pgcode>
					<mcode>C0062</mcode>
					<link>design@script</link>
					<rel>
						<item>
							<name>스킨 가이드</name>
							<link>http://redirect.wisa.co.kr/selfdesign</link>
						</item>
					</rel>
				</small>
				<small>
					<name>추가 페이지 편집</name>
					<pgcode>7170</pgcode>
					<mcode>C0063</mcode>
					<link>design@content_add</link>
					<rel>
						<item>
							<name>스킨 가이드</name>
							<link>http://redirect.wisa.co.kr/selfdesign</link>
						</item>
					</rel>
				</small>
				<small>
					<name>추가 페이지 편집</name>
					<pgcode>7170</pgcode>
					<link>design@content</link>
					<hidden>Y</hidden>
				</small>
			</mid>
		</big>
		<big name="쇼핑몰설정" category="config" pgcode="1000">
			<mid name="일반설정" pgcode='1000' mcode='config_1'>
				<small>
					<name>국가별 설정</name>
					<pgcode>1010</pgcode>
					<mcode>C0208</mcode>
					<link>config@multi_shop</link>
				</small>
				<small>
					<name>쇼핑몰정보 설정</name>
					<pgcode>1020</pgcode>
					<mcode>C0068</mcode>
					<link>config@info</link>
				</small>
				<small>
					<name>대표 도메인 설정</name>
					<pgcode>1030</pgcode>
					<mcode>C0069</mcode>
					<link>config@domain</link>
				</small>
				<small>
					<name>보안서버 설정</name>
					<pgcode>1035</pgcode>
					<mcode>C0274</mcode>
					<link>config@ssl</link>
					<new_date>2018-06-25</new_date>
				</small>
				<small>
					<name>개인정보처리방침</name>
					<pgcode>1040</pgcode>
					<mcode>C0070</mcode>
					<link>config@privacy</link>
				</small>
				<small>
					<name>개인정보처리방침</name>
					<pgcode>1040</pgcode>
					<mcode>C0070</mcode>
					<link>config@privacy_write_wizard</link>
					<hidden>Y</hidden>
				</small>
                <small>
                    <name>개인정보처리방침</name>
                    <pgcode>1040</pgcode>
                    <mcode>C0070</mcode>
                    <link>config@privacy_write</link>
                    <hidden>Y</hidden>
                </small>
                <small>
                    <name>개인정보처리방침</name>
                    <pgcode>1040</pgcode>
                    <mcode>C0070</mcode>
                    <link>config@privacy_view</link>
                    <hidden>Y</hidden>
                </small>
				<small>
					<name>파일서버 설정</name>
					<pgcode>1060</pgcode>
					<mcode>C0071</mcode>
					<link>config@fileserver</link>
					<hidden>Y</hidden>
				</small>
				<?php if(!$cache_account['account_id']){ ?>
				<small>
					<name>추가서버 설정</name>
					<pgcode>1070</pgcode>
					<mcode>C0230</mcode>
					<link>config@fileserver</link>
				</small>
				<?php } ?>
				<small>
					<name>관리자 문자알림 설정</name>
					<pgcode>1080</pgcode>
					<mcode>C0103</mcode>
					<link>config@sms_config&amp;sadmin=Y</link>
					<rel>
						<item>
							<name>회원조회</name>
							<link>?body=member@member_list</link>
						</item>
						<item>
							<name>서비스현황</name>
							<link>?body=wing@service_status</link>
						</item>
					</rel>
				</small>
				<small>
					<name>도로명주소 API 설정</name>
					<pgcode>1090</pgcode>
					<mcode>C0240</mcode>
					<link>config@juso</link>
					<up_date>2017-01-23</up_date>
				</small>
				<small>
					<name>IP 접속 차단 설정</name>
					<pgcode>1092</pgcode>
					<mcode>C0250</mcode>
					<link>config@ip_block</link>
					<up_date>2017-05-08</up_date>
				</small>
				<small>
					<name>IP 접속 차단 등록/수정</name>
					<pgcode>1093</pgcode>
					<mcode>C0251</mcode>
					<link>config@ip_block_register</link>
					<hidden>Y</hidden>
				</small>
				<small>
					<name>페이지캐시 설정</name>
					<pgcode>1095</pgcode>
					<mcode>C0277</mcode>
					<link>config@cache</link>
					<new_date>2019-10-15</new_date>
				</small>
				<small>
					<pgcode>1099</pgcode>
					<name>기타설정</name>
					<link>config@others</link>
					<hidden>Y</hidden>
				</small>
			</mid>
			<mid name="판매설정" pgcode='1500'>
				<?php if (is_dir($engine_dir.'/_partner')) { ?>
				<small>
					<name>입점몰 설정</name>
					<pgcode>1510</pgcode>
					<mcode>C0231</mcode>
					<link>config@partner_shop</link>
				</small>
				<?php } ?>
				<?php if (file_exists($engine_dir.'/_engine/api/shopLinker')) { ?>
				<small>
					<name>오픈마켓연동 설정</name>
					<pgcode>1520</pgcode>
					<mcode>C0235</mcode>
					<link>config@openmarket</link>
				</small>
				<?php } ?>
				<small>
					<name>ERP API연동키 설정</name>
					<pgcode>1525</pgcode>
					<mcode>C0301</mcode>
					<link>config@apikey</link>
					<new_date>2020-12-07</new_date>
				</small>
				<?php if (is_dir($engine_dir.'/_plugin/subScription') == true) { ?>
				<small>
					<name>정기배송 설정</name>
					<pgcode>1530</pgcode>
					<mcode>C0302</mcode>
					<link>config@subscription</link>
				</small>
				<?php } ?>
				<small>
					<name>카카오톡스토어 설정</name>
					<pgcode>1550</pgcode>
					<mcode>C0275</mcode>
					<link>config@kakaoTalkStore</link>
					<new_date>2019-08-21</new_date>
				</small>
				<small>
					<name>네이버 스마트스토어 설정</name>
					<pgcode>1560</pgcode>
					<mcode>C0279</mcode>
					<link>config@n_smart_store</link>
				</small>
			</mid>
			<mid name="결제설정" pgcode='1300' mcode='config_18'>
				<small>
					<name>결제 설정</name>
					<pgcode>1310</pgcode>
					<mcode>C0085</mcode>
					<link>config@account</link>
				</small>
				<small>
					<name>무통장계좌 설정</name>
					<pgcode>1320</pgcode>
					<mcode>C0233</mcode>
					<link>config@bank</link>
				</small>
				<small>
					<name>국내결제PG 설정</name>
					<pgcode>1330</pgcode>
					<mcode>C0088</mcode>
					<link>config@card</link>
				</small>
				<small>
					<name>간편결제 설정</name>
					<pgcode>1331</pgcode>
					<mcode>C0234</mcode>
					<link>config@easypay</link>
				</small>
				<?php if ($cfg['use_sbscr']=='Y') { ?>
				<small>
					<name>정기결제 설정</name>
					<pgcode>1340</pgcode>
					<mcode>C0089</mcode>
					<link>config@autobill</link>
				</small>
				<?php } ?>
				<small>
					<name>해외결제 설정</name>
					<pgcode>1390</pgcode>
					<mcode>C0205</mcode>
					<link>config@card_int</link>
				</small>
				<small>
					<name>적립금 설정</name>
					<pgcode>1350</pgcode>
					<mcode>C0086</mcode>
					<link>config@milage</link>
					<up_date>2017-12-18</up_date>
					<rel>
						<item>
							<name>회원조회</name>
							<link>?body=member@member_list</link>
						</item>
						<item>
							<name>전체주문조회</name>
							<link>?body=order@order_list</link>
						</item>
					</rel>
				</small>
				<small>
					<name>예치금 설정</name>
					<pgcode>1360</pgcode>
					<mcode>C0087</mcode>
					<link>config@emoney</link>
					<up_date>2017-12-18</up_date>
					<rel>
						<item>
							<name>회원조회</name>
							<link>?body=member@member_list</link>
						</item>
						<item>
							<name>전체주문조회</name>
							<link>?body=order@order_list</link>
						</item>
					</rel>
				</small>
				<small>
					<name>현금영수증 설정</name>
					<pgcode>1370</pgcode>
					<mcode>C0090</mcode>
					<link>config@cash_receipt</link>
					<rel>
						<item>
							<name>현금 영수증 관리</name>
							<link>?body=order@order_cash_receipt_new</link>
						</item>
					</rel>
				</small>
			</mid>
			<mid name="주문설정" pgcode='1200' mcode='config_11'>
				<small>
					<name>주문 설정</name>
					<pgcode>1210</pgcode>
					<mcode>C0078</mcode>
					<link>config@order</link>
					<up_date>2017-12-11</up_date>
					<rel>
						<item>
							<name>전체주문조회</name>
							<link>?body=order@order_list</link>
						</item>
					</rel>
				</small>
				<small>
					<name>장바구니 설정</name>
					<pgcode>1220</pgcode>
					<mcode>C0080</mcode>
					<link>config@cart</link>
					<rel>
						<item>
							<name>전체주문조회</name>
							<link>?body=order@order_list</link>
						</item>
					</rel>
				</small>
				<small>
					<name>주문정보엑셀 설정</name>
					<pgcode>1260</pgcode>
					<mcode>C0084</mcode>
					<link>config@order_excel_config</link>
					<rel>
						<item>
							<name>전체주문조회</name>
							<link>?body=order@order_list</link>
						</item>
					</rel>
				</small>
				<small>
					<name>주문 상품수량 엑셀설정</name>
					<pgcode>1280</pgcode>
					<mcode>C0195</mcode>
					<link>config@order_product_config</link>
				</small>
				<small>
					<name>주문 추가항목 설정</name>
					<pgcode>1290</pgcode>
					<mcode>C0198</mcode>
					<link>config@order_addinfo</link>
				</small>
				<?php if ($cfg['use_partner_shop'] != 'Y') { ?>
				<small>
					<name>상품금액별 할인 설정</name>
					<pgcode>1295</pgcode>
					<mcode>C0221</mcode>
					<link>config@order_prdprc</link>
				</small>
				<?php } ?>
				<small>
					<name>취소/환불/반품/교환 사유</name>
					<pgcode>1296</pgcode>
					<mcode>C0232</mcode>
					<link>config@claim_code</link>
				</small>
			</mid>
			<mid name="배송설정" pgcode='1400'>
				<small>
					<name>배송업체 설정</name>
					<pgcode>1240</pgcode>
					<mcode>C0082</mcode>
					<link>config@delivery_prv</link>
					<rel>
						<item>
							<name>배송시작</name>
							<link>?body=order@delivery</link>
						</item>
					</rel>
				</small>
				<small>
					<name>국내배송비 설정</name>
					<pgcode>1230</pgcode>
					<mcode>C0081</mcode>
					<link>config@delivery</link>
					<rel>
						<item>
							<name>배송시작</name>
							<link>?body=order@delivery</link>
						</item>
					</rel>
				</small>
				<small>
					<name>국내배송비 설정</name>
					<pgcode>1230</pgcode>
					<mcode>C0081</mcode>
					<link>config@delivery_fileinput_result</link>
					<new_date>2020-06-30</new_date>
					<hidden>Y</hidden>
				</small>
				<?php if ($cfg['use_prd_dlvprc'] == 'Y') { ?>
				<small>
					<name>개별배송비 관리</name>
					<pgcode>1231</pgcode>
					<mcode>C0281</mcode>
					<link>config@delivery_set</link>
					<new_date>2020-04-21</new_date>
				</small>
				<small>
					<name>개별배송비 관리</name>
					<pgcode>1231</pgcode>
					<mcode>C0281</mcode>
					<link>config@delivery_set_regist</link>
					<hidden>Y</hidden>
				</small>
				<?php } ?>
				<small>
					<name>해외배송 설정</name>
					<pgcode>1250</pgcode>
					<mcode>C0083</mcode>
					<link>config@oversea_delivery</link>
				</small>
				<small>
					<name>해외배송비 설정</name>
					<pgcode>1270</pgcode>
					<mcode>N0084</mcode>
					<link>config@oversea_delivery_prc</link>
				</small>
				<small>
					<name>국가별 관세설정</name>
					<pgcode>1297</pgcode>
					<mcode>C0273</mcode>
					<link>config@oversea_tax</link>
				</small>
			</mid>
			<?php/*[매장지도]*/?>
            <mid name="오프라인 매장 관리" pgcode='4000'>
                <small>
                    <name>오프라인 매장</name>
                    <pgcode>5900</pgcode>
                    <mcode>C0400</mcode>
                    <link>config@store_location</link>
                    <new_date>2023-09-30</new_date>
                </small>
                <small>
                    <name>오프라인 매장 등록</name>
                    <pgcode>5900</pgcode>
                    <mcode>C0400</mcode>
                    <link>config@store_location_register</link>
                    <hidden>Y</hidden>
                </small>
                <small>
                    <name>오프라인 매장 설정</name>
                    <pgcode>1400</pgcode>
                    <mcode>C1400</mcode>
                    <link>config@store_location_config</link>
                    <new_date>2023-09-30</new_date>
                </small>
                <small>
                    <name>시설안내 설정</name>
                    <pgcode>5920</pgcode>
                    <mcode>C0420</mcode>
                    <link>config@store_facility</link>
                    <new_date>2023-09-30</new_date>
                </small>
                <small>
                    <name>오프라인 매장 엑셀 업로드</name>
                    <pgcode>5910</pgcode>
                    <mcode>C0410</mcode>
                    <link>config@store_location_upload</link>
                    <new_date>2023-09-30</new_date>
                </small>
            </mid>
        </big>
		<big name="게시판" category="board" pgcode="6000" link="body=6030">
			<mid name="게시판" pgcode='6010' mcode='board_5'>
				<small>
					<name>게시물 관리</name>
					<pgcode>6010</pgcode>
					<mcode>C0113</mcode>
					<link>board@content_list</link>
				</small>
				<small>
					<name>게시물 관리</name>
					<pgcode>6010</pgcode>
					<mcode>C0114</mcode>
					<link>board@content_view</link>
					<hidden>Y</hidden>
				</small>
                <small>
                    <name>게시판 상단 디자인</name>
                    <pgcode>6010</pgcode>
                    <mcode>C0114</mcode>
                    <link>board@board_top.frm</link>
                    <hidden>Y</hidden>
                </small>
                <small>
                    <name>[INNERFRAME]게시판 상단 디자인 업로드</name>
                    <pgcode>6010</pgcode>
                    <mcode>C0114</mcode>
                    <link>board@board_file.frm</link>
                    <hidden>Y</hidden>
                </small>
                <small>
                    <name>[INNERFRAME]게시물 관리자 로그인</name>
                    <pgcode>6010</pgcode>
                    <mcode>C0114</mcode>
                    <link>board@mng_login.frm</link>
                    <hidden>Y</hidden>
                </small>
                <small>
                    <name>게시판 추가항목 설정</name>
                    <pgcode>6010</pgcode>
                    <mcode>C0114</mcode>
                    <link>board@board_temp</link>
                    <hidden>Y</hidden>
                </small>
				<small>
					<name>게시물 관리</name>
					<pgcode>6010</pgcode>
					<mcode>C0114</mcode>
					<link>board@content_write</link>
					<hidden>Y</hidden>
				</small>
				<small>
					<name>댓글 관리</name>
					<pgcode>6020</pgcode>
					<mcode>C0115</mcode>
					<link>board@content_list&amp;mng=2</link>
				</small>
				<small>
					<name>게시판 관리</name>
					<pgcode>6030</pgcode>
					<mcode>C0111</mcode>
					<link>board@board_new_list</link>
					<rel>
						<item>
							<name>게시판 설정</name>
							<link>?body=board@board_config</link>
						</item>
						<item>
							<name>상품Q&amp;A 관리</name>
							<link>?body=member@product_qna</link>
						</item>
						<item>
							<name>상품후기 관리</name>
							<link>?body=member@product_review</link>
						</item>
					</rel>
				</small>
				<small>
					<name>게시판 등록</name>
					<pgcode>6030</pgcode>
					<mcode>C0111</mcode>
					<link>board@board_new</link>
					<hidden>Y</hidden>
				</small>
				<small>
					<name>설문조사 관리</name>
					<pgcode>6040</pgcode>
					<mcode>C0116</mcode>
					<link>board@poll_list</link>
				</small>
				<small>
					<name>설문조사 관리</name>
					<pgcode>6040</pgcode>
					<mcode>C0116</mcode>
					<link>board@poll_frm</link>
					<hidden>Y</hidden>
				</small>
				<small>
					<name>게시판 설정</name>
					<pgcode>6050</pgcode>
					<mcode>C0106</mcode>
					<link>board@board_config</link>
					<rel>
						<item>
							<name>게시판 관리</name>
							<link>?body=board@board_new_list</link>
						</item>
					</rel>
				</small>
				<small>
					<name>작성자 표기 설정</name>
					<pgcode>6070</pgcode>
					<mcode>C0270</mcode>
					<link>board@writer_name</link>
				</small>
				<small>
					<name>게시물 휴지통</name>
					<pgcode>6060</pgcode>
					<mcode>C0202</mcode>
					<link>board@board_trash</link>
					<modify>
						설정___board@board_config
					</modify>
					<count><?=getTrashBoxRows('board')?></count>
					<new_date>2017-01-16</new_date>
				</small>
			</mid>
		</big>
		<big name="매출정산" category="income" pgcode="4000" link="body=4010">
			<mid name="간편매출통계" pgcode='4000' mcode='income_1'>
				<small>
					<name>매출요약</name>
					<pgcode>4010</pgcode>
					<mcode>C0118</mcode>
					<link>income@income_basic</link>
				</small>
				<small>
					<name>월별매출</name>
					<pgcode>4020</pgcode>
					<mcode>C0119</mcode>
					<link>income@income_log</link>
				</small>
				<small>
					<name>일별매출</name>
					<pgcode>4030</pgcode>
					<mcode>C0264</mcode>
					<link>income@income_log&amp;log_mode=1</link>
				</small>
				<small>
					<name>시간별매출</name>
					<pgcode>4040</pgcode>
					<mcode>C0265</mcode>
					<link>income@income_log&amp;log_mode=2</link>
				</small>
			</mid>
			<mid name="주문상품통계" pgcode='4200' mcode='income_6'>
				<small>
					<name>상품별매출</name>
					<pgcode>4200</pgcode>
					<mcode>C0192</mcode>
					<link>income@income_product</link>
				</small>
				<small>
					<name>개별상품판매분석</name>
					<pgcode>4300</pgcode>
					<mcode>C0193</mcode>
					<link>income@income_product_detail</link>
				</small>
			</mid>
			<?php if ($cfg['use_partner_shop'] == 'Y') { ?>
			<mid name="입점사정산관리" pgcode='4300' mcode='income_7'>
				<small>
					<name>입점사 정산등록</name>
					<pgcode>4310</pgcode>
					<mcode>C0290</mcode>
					<link>income@partner_account_reg</link>
				</small>
				<small>
					<name>입점사 정산등록</name>
					<pgcode>4310</pgcode>
					<mcode>C0290</mcode>
					<link>income@partner_account_edt</link>
					<hidden>Y</hidden>
				</small>
				<small>
					<name>입점사 정산환불</name>
					<pgcode>4320</pgcode>
					<mcode>C0291</mcode>
					<link>income@partner_account_refund</link>
				</small>
				<small>
					<name>입점사 정산관리</name>
					<pgcode>4330</pgcode>
					<mcode>C0292</mcode>
					<link>income@partner_account</link>
				</small>
			</mid>
			<?php } ?>
		</big>
		<big name="접속통계" category="log" pgcode="8000" link="body=8110">
			<mid name="방문자" pgcode='8100' mcode='log_4'>
				<small>
					<name>방문자분석</name>
					<pgcode>8110</pgcode>
					<mcode>C0128</mcode>
					<link>log@count_log</link>
					<rel>
						<item>
							<name>고급접속통계</name>
							<link>?body=log@ac_apply</link>
						</item>
					</rel>
				</small>
				<small>
					<name>방문자분석</name>
					<pgcode>8110</pgcode>
					<link>log@count_log_day</link>
					<hidden>Y</hidden>
				</small>
				<!--<small>
					<name>요일별</name>
					<pgcode>8120</pgcode>
					<mcode>C0129</mcode>
					<link>log@count_log_date</link>
					<rel>
						<item>
							<name>고급접속통계</name>
							<link>?body=log@ac_apply</link>
						</item>
					</rel>
				</small>-->
				<small>
					<name>상세접속로그 검색</name>
					<pgcode>8130</pgcode>
					<mcode>C0130</mcode>
					<link>log@count_log_list</link>
					<rel>
						<item>
							<name>고급접속통계</name>
							<link>?body=log@ac_apply</link>
						</item>
						<item>
							<name>접속아이피차단</name>
							<link>?body=config@ip_block</link>
						</item>
					</rel>
				</small>
				<small>
					<name>접속통계 설정</name>
					<pgcode>8140</pgcode>
					<mcode>C0107</mcode>
					<link>log@count_log_config</link>
					<rel>
						<item>
							<name>접속통계</name>
							<link>?body=log@count_log_hour</link>
						</item>
						<item>
							<name>고급접속통계</name>
							<link>?body=log@ac_apply</link>
						</item>
					</rel>
				</small>
				<small>
					<name>회원접속통계</name>
					<pgcode>8150</pgcode>
					<mcode>C0131</mcode>
					<link>log@member_access_log</link>
					<rel>
						<item>
							<name>고급접속통계</name>
							<link>?body=log@ac_apply</link>
						</item>
					</rel>
				</small>
				<small>
					<name>기간별 회원가입 통계</name>
					<pgcode>8151</pgcode>
					<mcode>C0132</mcode>
					<link>log@count_log_join</link>
					<hidden>Y</hidden>
				</small>
                <?php if (constant('__SESSION_ENGINE__') == 'MySQL') { ?>
				<small>
					<name>실시간접속현황</name>
					<pgcode>8160</pgcode>
					<mcode>C0136</mcode>
					<link>log@realtime</link>
					<rel>
						<item>
							<name>회원조회</name>
							<link>?body=member@member_list</link>
						</item>
					</rel>
				</small>
                <?php } ?>
				<small>
					<name>링크URL별</name>
					<pgcode>8170</pgcode>
					<mcode>C0134</mcode>
					<link>log@count_log_server&amp;stype=log_referer</link>
					<rel>
						<item>
							<name>고급접속통계</name>
							<link>?body=log@ac_apply</link>
						</item>
					</rel>
				</small>
				<small>
					<name>상품검색어</name>
					<pgcode>8180</pgcode>
					<mcode>C0137</mcode>
					<link>log@keyword_log</link>
					<rel>
						<item>
							<name>상품조회</name>
							<link>?body=product@product_list</link>
						</item>
					</rel>
				</small>
				<small>
					<name>위시리스트통계</name>
					<pgcode>8190</pgcode>
					<mcode>C0139</mcode>
					<link>log@wish_list</link>
					<rel>
						<item>
							<name>상품조회</name>
							<link>?body=log@ac_apply</link>
						</item>
						<item>
							<name>회원조회</name>
							<link>?body=member@member_list</link>
						</item>
					</rel>
				</small>
				<small>
					<name>장바구니통계</name>
					<pgcode>8195</pgcode>
					<mcode>C0140</mcode>
					<link>log@cart_list</link>
					<rel>
						<item>
							<name>상품조회</name>
							<link>?body=log@ac_apply</link>
						</item>
						<item>
							<name>회원조회</name>
							<link>?body=member@member_list</link>
						</item>
					</rel>
				</small>
			</mid>
			<mid name='제휴서비스' pgcode='8600'>
				<small>
					<name>에이스카운터 신청/안내</name>
					<pgcode>8010</pgcode>
					<mcode>C0123</mcode>
					<link>log@ac_apply</link>
				</small>
				<small>
					<name>에이스카운터 관리자</name>
					<pgcode>8020</pgcode>
					<mcode>C0124</mcode>
					<?php if ($cfg['ace_counter_id']){ ?>
					<onclick>openAceCounter()</onclick>
					<?php } else { ?>
					<link>log@ac_admin</link>
					<?php } ?>
				</small>
				<small>
					<name>히트맵 신청/안내</name>
					<pgcode>8710</pgcode>
					<mcode>C0258</mcode>
					<link>log@heatmap_info</link>
					<hidden>Y</hidden>
				</small>
				<small>
					<name>히트맵 신청/안내</name>
					<pgcode>8710</pgcode>
					<mcode>C0258</mcode>
					<link>log@heatmap_apply</link>
					<hidden>Y</hidden>
				</small>
				<small>
					<name>히트맵 관리자</name>
					<pgcode>8720</pgcode>
					<mcode>C0258</mcode>
					<link>log@heatmap</link>
					<hidden>Y</hidden>
				</small>
				<!--<small>
					<name>스마트MD 신청/안내</name>
					<pgcode>8610</pgcode>
					<mcode>C0259</mcode>
					<link>log@smartMD_info</link>
				</small>
				<small>
					<name>스마트MD 신청/안내</name>
					<pgcode>8610</pgcode>
					<mcode>C0259</mcode>
					<link>log@smartMD_apply</link>
					<hidden>Y</hidden>
				</small>
				<small>
					<name>스마트MD 관리자</name>
					<pgcode>8620</pgcode>
					<mcode>C0259</mcode>
					<link>log@smartMD</link>
				</small>-->
                <small>
                    <name>구글 애널리틱스 연동</name>
                    <pgcode>10180</pgcode>
                    <mcode>N0266</mcode>
                    <link>log@google_Analytics</link>
                    <new_date>2017-08-23</new_date>
                </small>
				<small>
					<name>MS 클레어리티 연동</name>
					<pgcode>8630</pgcode>
					<mcode>C0263</mcode>
					<link>log@clarity</link>
				</small>
			</mid>
		</big>
		<big name="광고마케팅" category="openmarket" pgcode="10000" link='body=10410'>
			<mid name="광고상품" pgcode="10400" mcode="openmarket_1">
				<small>
					<name>추천광고</name>
					<pgcode>10410</pgcode>
					<mcode>C0267</mcode>
					<link>openmarket@ad_recommend</link>
				</small>
				<small>
					<name>쇼핑광고</name>
					<pgcode>10420</pgcode>
					<mcode>C0268</mcode>
					<link>openmarket@ad_shopping</link>
				</small>
				<small>
					<name>검색광고</name>
					<pgcode>10430</pgcode>
					<mcode>C0269</mcode>
					<link>openmarket@ad_sa</link>
				</small>
				<small>
					<name>디스플레이</name>
					<pgcode>10440</pgcode>
					<mcode>C0271</mcode>
					<link>openmarket@ad_da</link>
				</small>
				<small>
					<name>오프라인</name>
					<pgcode>10460</pgcode>
					<mcode>C0272</mcode>
					<link>openmarket@ad_offline</link>
				</small>
			</mid>
			<mid name="연동설정" pgcode='10100' mcode='openmarket_1'>
				<small>
					<name>입점마케팅 대행</name>
					<pgcode>10115</pgcode>
					<mcode>C0261</mcode>
					<onclick>goMywisa('?body=wing@cooperate@shopad')</onclick>
				</small>
				<small>
					<name>카카오 쇼핑하우 연동</name>
					<pgcode>10120</pgcode>
					<mcode>C0147</mcode>
					<link>openmarket@show_setup</link>
				</small>
				<small>
					<name>네이버쇼핑 연동</name>
					<pgcode>10140</pgcode>
					<mcode>C0144</mcode>
					<link>openmarket@compare_setup</link>
				</small>
				<small>
					<name>카카오 쇼핑하우 가입신청</name>
					<pgcode>10130</pgcode>
					<mcode>C0147</mcode>
					<link>openmarket@show_join</link>
					<hidden>Y</hidden>
				</small>
				<small>
					<name>네이버 CPA 설정</name>
					<pgcode>10142</pgcode>
					<mcode>N9999</mcode>
					<link>openmarket@nhn</link>
				</small>
				<small>
					<name>지그재그 연동</name>
					<pgcode>10170</pgcode>
					<mcode>C0282</mcode>
					<link>openmarket@zigzag</link>
				</small>
				<small>
					<name>크리테오 연동</name>
					<pgcode>10150</pgcode>
					<mcode>C0180</mcode>
					<link>openmarket@criteo</link>
				</small>
				<small>
					<name>페이스북 픽셀 연동</name>
					<pgcode>10152</pgcode>
					<mcode>C0216</mcode>
					<link>openmarket@facebook</link>
				</small>
				<small>
					<name>레코픽 연동</name>
					<pgcode>10155</pgcode>
					<mcode>C0266</mcode>
					<link>openmarket@recopick</link>
				</small>
				<!--<small>
					<name>구글 애널리틱스 연동</name>
					<pgcode>10180</pgcode>
					<mcode>N0266</mcode>
					<link>openmarket@google_Analytics</link>
					<new_date>2017-08-23</new_date>
				</small>-->
				<small>
					<name>구글 애드워즈 연동</name>
					<pgcode>10190</pgcode>
					<mcode>N0267</mcode>
					<link>openmarket@google_ads</link>
					<new_date>2020-11-06</new_date>
				</small>
				<small>
					<name>구글 쇼핑 연동</name>
					<pgcode>10195</pgcode>
					<mcode>N0268</mcode>
					<link>openmarket@google_ep</link>
					<new_date>2017-12-07</new_date>
				</small>
				<small>
					<name>광고스크립트 관리</name>
					<pgcode>10160</pgcode>
					<mcode>C0190</mcode>
					<link>openmarket@mm_code</link>
				</small>
			</mid>
			<mid name="배너광고코드" pgcode='10200' mcode='promotion_15'>
				<small>
					<name>배너광고코드 관리</name>
					<pgcode>10210</pgcode>
					<mcode>C0162</mcode>
					<link>openmarket@ban_list</link>
					<rel>
						<item>
							<name>접속통계</name>
							<link>?body=log@count_log_hour</link>
						</item>
						<item>
							<name>회원조회</name>
							<link>?body=member@member_list</link>
						</item>
						<item>
							<name>전체주문조회</name>
							<link>?body=order@order_list</link>
						</item>
					</rel>
				</small>
				<small>
					<name>배너광고코드 등록</name>
					<pgcode>10220</pgcode>
					<mcode>C0163</mcode>
					<link>openmarket@ban_register</link>
					<rel>
						<item>
							<name>접속통계</name>
							<link>?body=log@count_log_hour</link>
						</item>
						<item>
							<name>회원조회</name>
							<link>?body=member@member_list</link>
						</item>
						<item>
							<name>전체주문조회</name>
							<link>?body=order@order_list</link>
						</item>
					</rel>
				</small>
				<small>
					<name>단축URL 등록(Bitly)</name>
					<pgcode>10231</pgcode>
					<mcode>C0223</mcode>
					<new_date>2019-03-11</new_date>
					<link>openmarket@bitly_shortenter</link>
				</small>
				<small>
					<name>단축URL 등록(Google)</name>
					<pgcode>10230</pgcode>
					<mcode>C0222</mcode>
					<new_date>2018-02-12</new_date>
					<link>openmarket@shortenter</link>
				</small>
			</mid>
		</big>
		<big name="프로모션" category="promotion" pgcode="9000" link='body=9010'>
			<mid name="쿠폰" pgcode='9000' mcode='promotion_1'>
				<small>
					<name>온라인쿠폰 관리</name>
					<pgcode>9010</pgcode>
					<mcode>C0148</mcode>
					<link>promotion@coupon</link>
					<hidden>Y</hidden>
					<rel>
						<item>
							<name>회원조회</name>
							<link>?body=member@member_list</link>
						</item>
						<item>
							<name>회원그룹설정</name>
							<link>?body=member@member_group</link>
						</item>
						<item>
							<name>전체주문조회</name>
							<link>?body=order@order_list</link>
						</item>
					</rel>
				</small>
				<small>
					<name>온라인쿠폰 관리</name>
					<pgcode>9010</pgcode>
					<mcode>C0148</mcode>
					<link>promotion@coupon&amp;is_type=A</link>
					<rel>
						<item>
							<name>회원조회</name>
							<link>?body=member@member_list</link>
						</item>
						<item>
							<name>회원그룹설정</name>
							<link>?body=member@member_group</link>
						</item>
						<item>
							<name>전체주문조회</name>
							<link>?body=order@order_list</link>
						</item>
					</rel>
				</small>
				<small>
					<name>쿠폰 발급</name>
					<pgcode>9020</pgcode>
					<mcode>C0150</mcode>
					<link>promotion@coupon_register</link>
					<hidden>Y</hidden>
					<rel>
						<item>
							<name>회원조회</name>
							<link>?body=member@member_list</link>
						</item>
						<item>
							<name>회원그룹설정</name>
							<link>?body=member@member_group</link>
						</item>
						<item>
							<name>전체주문조회</name>
							<link>?body=order@order_list</link>
						</item>
					</rel>
				</small>
				<small>
					<name>온라인쿠폰 생성</name>
					<pgcode>9020</pgcode>
					<mcode>C0151</mcode>
					<link>promotion@coupon_register&amp;is_type=A</link>
					<rel>
						<item>
							<name>회원조회</name>
							<link>?body=member@member_list</link>
						</item>
						<item>
							<name>회원그룹설정</name>
							<link>?body=member@member_group</link>
						</item>
						<item>
							<name>전체주문조회</name>
							<link>?body=order@order_list</link>
						</item>
					</rel>
				</small>
				<small>
					<name>온라인쿠폰 발급내역</name>
					<pgcode>9030</pgcode>
					<mcode>C0152</mcode>
					<link>promotion@coupon_down_list&amp;is_type=A</link>
					<rel>
						<item>
							<name>회원조회</name>
							<link>?body=member@member_list</link>
						</item>
						<item>
							<name>회원그룹설정</name>
							<link>?body=member@member_group</link>
						</item>
						<item>
							<name>전체주문조회</name>
							<link>?body=order@order_list</link>
						</item>
					</rel>
				</small>
				<small>
					<name>생일 자동쿠폰 설정</name>
					<pgcode>9035</pgcode>
					<mcode>C0201</mcode>
					<link>promotion@coupon_birth</link>
					<rel>
						<item>
							<name>온라인쿠폰 관리</name>
							<link>?body=promotion@coupon&amp;is_type=A</link>
						</item>
					</rel>
				</small>
				<small>
					<name>시리얼쿠폰 관리</name>
					<pgcode>9040</pgcode>
					<mcode>C0153</mcode>
					<link>promotion@coupon&amp;is_type=B</link>
					<rel>
						<item>
							<name>회원조회</name>
							<link>?body=member@member_list</link>
						</item>
						<item>
							<name>회원그룹설정</name>
							<link>?body=member@member_group</link>
						</item>
						<item>
							<name>전체주문조회</name>
							<link>?body=order@order_list</link>
						</item>
					</rel>
				</small>
				<small>
					<name>시리얼쿠폰 코드확인</name>
					<pgcode>9040</pgcode>
					<mcode>C0153</mcode>
					<link>promotion@coupon_code_list</link>
					<hidden>Y</hidden>
				</small>

				<small>
					<name>시리얼쿠폰 생성</name>
					<pgcode>9050</pgcode>
					<mcode>C0154</mcode>
					<link>promotion@coupon_register&amp;is_type=B</link>
					<rel>
						<item>
							<name>회원조회</name>
							<link>?body=member@member_list</link>
						</item>
						<item>
							<name>회원그룹설정</name>
							<link>?body=member@member_group</link>
						</item>
						<item>
							<name>전체주문조회</name>
							<link>?body=order@order_list</link>
						</item>
					</rel>
				</small>
				<small>
					<name>시리얼쿠폰 사용내역</name>
					<pgcode>9060</pgcode>
					<mcode>C0155</mcode>
					<link>promotion@coupon_down_list&amp;is_type=B</link>
					<rel>
						<item>
							<name>회원조회</name>
							<link>?body=member@member_list</link>
						</item>
						<item>
							<name>회원그룹설정</name>
							<link>?body=member@member_group</link>
						</item>
						<item>
							<name>전체주문조회</name>
							<link>?body=order@order_list</link>
						</item>
					</rel>
				</small>
				<small>
					<name>소셜쿠폰 관리</name>
					<pgcode>9080</pgcode>
					<mcode>C0253</mcode>
					<link>promotion@sccoupon</link>
				</small>
				<small>
					<name>소셜쿠폰 생성</name>
					<pgcode>9081</pgcode>
					<mcode>C0254</mcode>
					<link>promotion@sccoupon_register</link>
				</small>
				<small>
					<name>소셜쿠폰 교환내역</name>
					<pgcode>9082</pgcode>
					<mcode>C0255</mcode>
					<link>promotion@sccoupon_list</link>
				</small>
				<small>
					<name>소셜쿠폰 수정내역</name>
					<pgcode>9083</pgcode>
					<mcode>C0256</mcode>
					<link>promotion@sccoupon_log</link>
				</small>
				<small>
					<name>소셜쿠폰 코드확인</name>
					<pgcode>9084</pgcode>
					<mcode>C0257</mcode>
					<link>promotion@sccoupon_code_list</link>
					<hidden>Y</hidden>
				</small>
			</mid>
			<mid name="이벤트" pgcode='9100' mcode='promotion_10'>
				<small>
					<name>할인/적립 이벤트</name>
					<pgcode>9110</pgcode>
					<mcode>C0158</mcode>
					<link>promotion@event</link>
					<rel>
						<item>
							<name>회원조회</name>
							<link>?body=member@member_list</link>
						</item>
						<item>
							<name>회원그룹설정</name>
							<link>?body=member@member_group</link>
						</item>
						<item>
							<name>전체주문조회</name>
							<link>?body=order@order_list</link>
						</item>
					</rel>
					<hidden>Y</hidden>
				</small>
				<small>
					<name>할인/적립 이벤트</name>
					<pgcode>9110</pgcode>
					<mcode>C0158</mcode>
					<link>promotion@event_list</link>
				</small>
				<small>
					<name>무료배송 이벤트</name>
					<pgcode>9120</pgcode>
					<mcode>C0159</mcode>
					<link>promotion@event_delivery</link>
					<rel>
						<item>
							<name>회원조회</name>
							<link>?body=member@member_list</link>
						</item>
						<item>
							<name>회원그룹설정</name>
							<link>?body=member@member_group</link>
						</item>
						<item>
							<name>전체주문조회</name>
							<link>?body=order@order_list</link>
						</item>
					</rel>
				</small>
				<small>
					<name>타임세일 설정</name>
					<pgcode>9180</pgcode>
					<mcode>C0215</mcode>
					<link>promotion@timesale</link>
				</small>
				<small>
					<name>타임세일세트 설정</name>
					<pgcode>9170</pgcode>
					<mcode>C0280</mcode>
					<link>promotion@timesale_list</link>
					<new_date>2020-05-26</new_date>
				</small>
				<small>
					<name>타임세일 이벤트</name>
					<pgcode>9170</pgcode>
					<mcode>C0280</mcode>
					<link>promotion@timesale_regist</link>
					<hidden>Y</hidden>
				</small>
				<small>
					<name>사은품 설정</name>
					<pgcode>9130</pgcode>
					<mcode>C0109</mcode>
					<link>promotion@product_gift_config</link>
					<rel>
						<item>
							<name>상품조회</name>
							<link>?body=product@product_list</link>
						</item>
					</rel>
				</small>
				<small>
					<name>사은품 관리</name>
					<pgcode>9140</pgcode>
					<mcode>C0160</mcode>
					<link>promotion@product_gift_list</link>
					<rel>
						<item>
							<name>사은품 설정</name>
							<link>?body=promotion@product_gift_config</link>
						</item>
						<item>
							<name>상품조회</name>
							<link>?body=product@product_list</link>
						</item>
						<item>
							<name>전체주문조회</name>
							<link>?body=order@order_list</link>
						</item>
					</rel>
				</small>
				<small>
					<name>사은품 관리</name>
					<pgcode>9140</pgcode>
					<mcode>C0160</mcode>
					<link>promotion@product_gift_register</link>
					<hidden>Y</hidden>
				</small>
				<small>
					<name>출석체크 설정</name>
					<pgcode>9150</pgcode>
					<mcode>C0110</mcode>
					<link>promotion@attend</link>
					<rel>
						<item>
							<name>온라인쿠폰 관리</name>
							<link>?body=promotion@coupon&amp;is_type=A</link>
						</item>
					</rel>
				</small>
				<small>
					<name>출석체크 관리</name>
					<pgcode>9160</pgcode>
					<mcode>C0161</mcode>
					<link>promotion@attend_list</link>
					<rel>
						<item>
							<name>출석체크 설정</name>
							<link>?body=promotion@attend</link>
						</item>
						<item>
							<name>회원조회</name>
							<link>?body=member@member_list</link>
						</item>
					</rel>
				</small>
				<small>
					<name>출석체크 내역 상세</name>
					<pgcode>9160</pgcode>
					<mcode>C0161</mcode>
					<link>promotion@attend_detail</link>
					<hidden>N</hidden>
				</small>
			</mid>
			<mid name="SNS 서비스" pgcode='9200' mcode='promotion_10'>
				<small>
					<name>SNS 공유하기 연동</name>
					<pgcode>9210</pgcode>
					<mcode>N0161</mcode>
					<link>promotion@sns_list</link>
				</small>
			</mid>
		</big>
		<big name="윙POS" category="erp" pgcode="14000" link='body=erp@lite_stock_list'>
			<mid name="윙POS" pgcode="14000">
				<small>
					<name>윙POS란?</name>
					<pgcode>14020</pgcode>
					<mcode>E0019</mcode>
					<link>erp@whats</link>
				</small>
				<small>
					<name>프리미엄/엔터프라이즈 서비스 신청</name>
					<pgcode>14030</pgcode>
					<mcode></mcode>
					<link>erp@upgrade</link>
					<hidden>Y</hidden>
				</small>
				<small>
					<name>윙POS 설정</name>
					<pgcode>14000</pgcode>
					<mcode>E0020</mcode>
					<link>erp@erp_config</link>
				</small>
				<?php if ($wp_stat > 1) { ?>
				<small>
					<name>발주서 엑셀양식</name>
					<pgcode>14010</pgcode>
					<mcode>E0021</mcode>
					<link>erp@order_config</link>
				</small>
				<?php } ?>
				<small>
					<name>입고서 엑셀양식</name>
					<pgcode>14030</pgcode>
					<mcode>E0029</mcode>
					<link>erp@input_config</link>
				</small>
			</mid>

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
				<small>
					<name>바코드 재고파악</name>
					<pgcode>14160</pgcode>
					<mcode>E0022</mcode>
					<link>erp@stock_check</link>
				</small>
				<small>
					<name>바코드 재고조정</name>
					<pgcode>14170</pgcode>
					<mcode>E0024</mcode>
					<link>erp@stock_barcode</link>
				</small>
				<small>
					<name>건별 출고처리</name>
					<pgcode>14180</pgcode>
					<mcode>E0025</mcode>
					<link>erp@out</link>
				</small>
			</mid>
			<mid name="발주관리" pgcode="14200">
                <small>
                    <name>전체발주</name>
                    <pgcode>14210</pgcode>
                    <mcode>E0007</mcode>
                    <link>erp@order_all</link>
                </small>
				<small>
					<name>발주</name>
					<pgcode>14220</pgcode>
					<mcode>E0008</mcode>
					<link>erp@order</link>
				</small>
				<small>
					<name>발주내역 조회</name>
					<pgcode>14230</pgcode>
					<mcode>E0009</mcode>
					<link>erp@order_list</link>
				</small>
                <small>
                    <name>발주 상세</name>
                    <pgcode>14230</pgcode>
                    <mcode>E0009</mcode>
                    <link>erp@order_detail</link>
                    <hidden>Y</hidden>
                </small>
                <small>
                    <name>발주 수정</name>
                    <pgcode>14230</pgcode>
                    <mcode>E0023</mcode>
                    <link>erp@order_mod</link>
                    <hidden>Y</hidden>
                </small>
                <small>
                    <name>일괄발주 수정</name>
                    <pgcode>14230</pgcode>
                    <mcode>E0024</mcode>
                    <link>erp@order_mod_all</link>
                    <hidden>Y</hidden>
                </small>
			</mid>
			<mid name="입고관리" pgcode="14300">
				<small>
					<name>건별 입고</name>
					<pgcode>14310</pgcode>
					<mcode>E0010</mcode>
					<link>erp@in</link>
				</small>
				<small>
					<name>발주서 입고</name>
					<pgcode>14320</pgcode>
					<mcode>E0011</mcode>
					<link>erp@order_in</link>
				</small>
                <small>
                    <name>일괄발주서 입고</name>
                    <pgcode>14330</pgcode>
                    <mcode>E0012</mcode>
                    <link>erp@order_in_all</link>
                </small>
				<small>
					<name>입고내역 조회</name>
					<pgcode>14340</pgcode>
					<mcode>E0013</mcode>
					<link>erp@in_list</link>
				</small>
				<small>
					<name>창고 관리</name>
					<pgcode>14350</pgcode>
					<mcode>E0027</mcode>
					<link>erp@storage</link>
				</small>
				<small>
					<name>창고 관리</name>
					<pgcode>14350</pgcode>
					<mcode>E0027</mcode>
					<link>erp@storage_register</link>
					<hidden>Y</hidden>
				</small>
				<small>
					<name>창고분류 관리</name>
					<pgcode>14360</pgcode>
					<mcode>E0028</mcode>
					<link>erp@storage_cate</link>
				</small>
				<small>
					<name>바코드 창고 배치</name>
					<pgcode>14370</pgcode>
					<mcode>E0030</mcode>
					<link>erp@storage_in</link>
				</small>
			</mid>
			<mid name="배송처리" pgcode="14500">
				<small>
					<name>안전배송</name>
					<pgcode>14510</pgcode>
					<mcode>E0016</mcode>
					<link>erp@delivery</link>
				</small>
				<small>
					<name>안전배송(상태변경안함)</name>
					<pgcode>14515</pgcode>
					<mcode>E0026</mcode>
					<link>erp@delivery&amp;exec=m</link>
				</small>
				<small>
					<name>바코드 배송처리</name>
					<pgcode>14520</pgcode>
					<mcode>E0017</mcode>
					<link>erp@complete</link>
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
		<big name="<?=$cfg['mobile_name']?>" category="wmb" pgcode="15000" link='body=wmb@config'>
			<mid name="모바일APP" pgcode="15400">
				<small>
					<name>매직앱 안내/신청</name>
					<pgcode>15420</pgcode>
					<link>http://redirect.wisa.co.kr/magicapp</link>
					<target>_blank</target>
					<new_date>2017-04-05</new_date>
				</small>
				<small>
					<name>└ 관리자 접속</name>
					<pgcode>15410</pgcode>
					<link>?body=wmb@push.exe</link>
					<target>_blank</target>
					<new_date>2017-04-05</new_date>
				</small>
				<small>
					<name>└ 매직앱 설치유도</name>
					<pgcode>15430</pgcode>
					<link>wmb@app_config</link>
					<new_date>2019-04-29</new_date>
				</small>
			</mid>
			<mid name="모바일관리" pgcode="15000">
				<?php if ($cfg['mobile_use'] == 'Y') { ?>
				<small>
					<name>스킨 관리</name>
					<pgcode>15050</pgcode>
					<mcode>B0006</mcode>
					<link>wmb@skin</link>
				</small>
				<?php } ?>
				<small>
					<name><?=$cfg['mobile_name']?> 설정</name>
					<pgcode>15010</pgcode>
					<mcode>B0002</mcode>
					<link>wmb@config</link>
				</small>
				<small>
					<name>DTD/확대 설정</name>
					<pgcode>15060</pgcode>
					<mcode>C0246</mcode>
					<link>wmb@title_meta</link>
				</small>
				<?php if ($cfg['mobile_use'] == 'Y') { ?>
				<small>
					<name>매장분류 설정</name>
					<pgcode>15020</pgcode>
					<mcode>B0003</mcode>
					<link>wmb@category_config</link>
				</small>
				<small>
					<name>기획전 분류</name>
					<pgcode>15030</pgcode>
					<mcode>C0007</mcode>
					<link>wmb@category_config2</link>
				</small>
				<small>
					<name>기획전 상품진열순서</name>
					<pgcode>15035</pgcode>
					<mcode>C0008</mcode>
					<link>wmb@product_special_list</link>
				</small>
				<small>
					<name>이미지 관리</name>
					<pgcode>15040</pgcode>
					<mcode>B0007</mcode>
					<link>wmb@common_img&amp;type=mobile</link>
				</small>
				<small>
					<name>퀵프리뷰 설정</name>
					<pgcode>15070</pgcode>
					<mcode>C0211</mcode>
					<link>wmb@quickDetail</link>
				</small>
				<?php } ?>
			</mid>
			<?php if ($cfg['mobile_use'] == 'Y') { ?>
			<mid name="개별디자인편집" pgcode='15300'>
				<small>
					<name>배너 관리</name>
					<pgcode>7210</pgcode>
					<mcode>C0064</mcode>
					<link>design@design_banner</link>
					<rel>
						<item>
							<name>배너광고코드</name>
							<link>?body=openmarket@ban_list</link>
						</item>
					</rel>
				</small>
				<small>
					<name>그룹배너 관리</name>
					<pgcode>7215</pgcode>
					<mcode>C0224</mcode>
					<link>wmb@group_banner&amp;type=mobile</link>
                    <up_date>2020-11-09</up_date>
				</small>
				<small>
					<name>팝업 관리</name>
					<pgcode>7310</pgcode>
					<mcode>C0065</mcode>
					<link>design@design_popup</link>
					<rel>
						<item>
							<name>팝업스킨 편집</name>
							<link>?body=design@design_popup_frame</link>
						</item>
					</rel>
				</small>
				<small>
					<name>팝업스킨 편집</name>
					<pgcode>7320</pgcode>
					<mcode>C0066</mcode>
					<link>design@design_popup_frame</link>
					<rel>
						<item>
							<name>팝업 관리</name>
							<link>?body=design@design_popup</link>
						</item>
					</rel>
				</small>
			</mid>
			<mid name="HTML 편집" pgcode='15200'>
				<small>
					<name>상단공통페이지 편집</name>
					<pgcode>15210</pgcode>
					<mcode>B0008</mcode>
					<link>wmb@layout&amp;part=%7B%7BT%7D%7D</link>
				</small>
				<small>
					<name>하단공통페이지 편집</name>
					<pgcode>15220</pgcode>
					<mcode>B0009</mcode>
					<link>wmb@layout&amp;part=%7B%7BB%7D%7D</link>
				</small>
				<small>
					<name>페이지 편집</name>
					<pgcode>15230</pgcode>
					<mcode>B0010</mcode>
					<link>wmb@editor&amp;type=mobile</link>
				</small>
				<small>
					<name>제공 코드 편집</name>
					<pgcode>15240</pgcode>
					<mcode>B0011</mcode>
					<link>wmb@editor_code&amp;type=mobile</link>
				</small>
				<small>
					<name>스타일 시트 편집</name>
					<pgcode>15250</pgcode>
					<mcode>B0012</mcode>
					<link>wmb@css&amp;type=mobile</link>
				</small>
				<small>
					<name>스크립트 편집</name>
					<pgcode>15260</pgcode>
					<mcode>B0013</mcode>
					<link>wmb@script&amp;type=mobile</link>
				</small>
				<small>
					<name>추가 페이지 편집</name>
					<pgcode>15270</pgcode>
					<mcode>B0014</mcode>
					<link>wmb@content_add&amp;type=mobile</link>
				</small>
				<small>
					<name>추가 페이지 편집</name>
					<pgcode>15280</pgcode>
					<link>wmb@content</link>
					<hidden>Y</hidden>
				</small>
			</mid>
			<?php } ?>
		</big>
		<big name="인트라넷" category="intra" pgcode="11000">
			<mid name="커뮤니티" pgcode='11000' mcode='intra_18'>
				<small>
					<name>메인페이지</name>
					<pgcode>11010</pgcode>
					<mcode>C0164</mcode>
					<link>intra@main</link>
				</small>
				<?php
				$menu_intra_sql = $pdo->iterator("select db, title, no from {$tbl['intra_board_config']} order by title");
                foreach ($menu_intra_sql as $_community) {
					$_community['title'] = str_replace('&', '&amp;', $_community['title']);
				?>
				<small>
					<name><?=$_community['title']?></name>
					<pgcode><?=(11300+$_community['no'])?></pgcode>
					<mcode>C0179</mcode>
					<link>intra@board&amp;db=<?=$_community['db']?></link>
				</small>
				<small>
					<name><?=$_community['title']?></name>
					<pgcode><?=(11300+$_community['no'])?></pgcode>
					<mcode>C0185</mcode>
					<link>intra@board</link>
					<hidden>Y</hidden>
				</small>
				<?php } ?>
				<small>
					<name>조직도</name>
					<pgcode>11130</pgcode>
					<mcode>C0165</mcode>
					<link>intra@view_staffs</link>
				</small>
			</mid>
			<mid name="마이페이지" pgcode='11100' mcode='intra_1'>
				<small>
					<name>내월간근태</name>
					<pgcode>11110</pgcode>
					<mcode>C0166</mcode>
					<link>intra@my_att</link>
				</small>
				<small>
					<name>내정보수정</name>
					<pgcode>11120</pgcode>
					<mcode>C0167</mcode>
					<link>intra@my_info</link>
				</small>
				<small>
					<name>관리자 아이디 변경</name>
					<pgcode>11210</pgcode>
					<mcode>C0169</mcode>
					<link>intra@admin_pwd</link>
					<hidden>Y</hidden>
				</small>
			</mid>
			<mid name="인트라넷관리" pgcode='11200' mcode='intra_5' if="$GLOBALS['admin']['level'] &lt; 3">
				<small>
					<name><?=$_mng_group[3]?> 등록/관리</name>
					<pgcode>11220</pgcode>
					<mcode>C0170</mcode>
					<link>intra@staffs_edt</link>
				</small>
				<small>
					<name>일정 등록/관리</name>
					<pgcode>11250</pgcode>
					<mcode>C0171</mcode>
					<link>intra@schedule</link>
				</small>
				<small>
					<name>게시판 등록/관리</name>
					<pgcode>11240</pgcode>
					<mcode>C0172</mcode>
					<link>intra@board_config</link>
				</small>
				<small>
					<name>조직도 관리</name>
					<pgcode>11230</pgcode>
					<mcode>C0173</mcode>
					<link>intra@group_edt</link>
				</small>
				<small>
					<name><?=$_mng_group[3]?>근태통계</name>
					<pgcode>11255</pgcode>
					<mcode>C0174</mcode>
					<link>intra@staffs_att</link>
				</small>
				<small>
					<name><?=$_mng_group[3]?>접속통계</name>
					<pgcode>11235</pgcode>
					<mcode>C0175</mcode>
					<link>intra@admin_log</link>
				</small>
				<small>
					<name>개인정보 접속기록 내역</name>
					<pgcode>11236</pgcode>
					<mcode>N0175</mcode>
					<link>intra@connect_log</link>
				</small>
				<small>
					<name>데이터 수정 내역</name>
					<pgcode>11280</pgcode>
					<mcode>N0178</mcode>
					<link>intra@work_log</link>
				</small>
			</mid>
			<mid name="인트라넷설정" pgcode='11200' mcode='intra_5' if="$GLOBALS['admin']['level'] &lt; 3">
				<small>
					<name>쇼핑몰관리권한 설정</name>
					<pgcode>11225</pgcode>
					<mcode>C0176</mcode>
					<link>intra@staffs_auth</link>
				</small>
				<small>
					<name>쇼핑몰관리권한 설정 내역</name>
					<pgcode>11227</pgcode>
					<mcode>C0220</mcode>
					<link>intra@staffs_auth_log</link>
				</small>
				<small>
					<name>중요설정 2단계 인증</name>
					<pgcode>11226</pgcode>
					<mcode>C0252</mcode>
					<link>intra@admin_confirm</link>
					<up_date>2018-03-19</up_date>
				</small>
				<small>
					<name>로그인보안 설정</name>
					<pgcode>11270</pgcode>
					<mcode>N0176</mcode>
					<link>intra@access_limit</link>
				</small>
                <?php if ($admin['level'] > 0 && $admin['level'] < 3) { ?>
				<small>
					<name>개인정보보호 설정</name>
					<pgcode>11260</pgcode>
					<mcode>N0177</mcode>
					<link>intra@scm</link>
				</small>
                <?php } ?>
				<small>
					<name>인트라넷 설정</name>
					<pgcode>11265</pgcode>
					<mcode>C0177</mcode>
					<link>intra@main_set</link>
				</small>
				<small>
					<name>인트라넷게시판권한 설정</name>
					<pgcode>11245</pgcode>
					<mcode>C0178</mcode>
					<link>intra@board_config&amp;auth=1</link>
				</small>
			</mid>
		</big>
		<big name="서비스현황" category="wing" pgcode="12000" link='body=wing@cs_list'>
			<mid name="서비스현황" pgcode="12100">
				<small>
					<name>서비스 현황</name>
					<pgcode>12110</pgcode>
					<mcode>m0001</mcode>
					<link>wing@service_status</link>
				</small>
				<small>
					<name>서비스 결제완료</name>
					<pgcode>12110</pgcode>
					<mcode>m0001</mcode>
					<link>wing@service_pay_finish</link>
					<hidden>Y</hidden>
				</small>
				<small>
					<name>서비스 신청</name>
					<pgcode>12120</pgcode>
					<mcode>m0002</mcode>
					<link>wing@service_account</link>
				</small>
				<small>
					<name>서비스 연장/충전</name>
					<pgcode>12130</pgcode>
					<mcode>m0003</mcode>
					<link>wing@service_charge</link>
				</small>
				<small>
					<name>서비스 변경/추가</name>
					<pgcode>12140</pgcode>
					<mcode>m0004</mcode>
					<link>wing@service_change</link>
				</small>
				<small>
					<name>서비스 신청내역</name>
					<pgcode>12150</pgcode>
					<mcode>m0005</mcode>
					<link>wing@service_list</link>
				</small>
				<small>
					<name>세금계산서 발행</name>
					<pgcode>12160</pgcode>
					<mcode>m0006</mcode>
					<link>wing@service_receipt</link>
				</small>
				<small>
					<name>윙BANK 계좌관리</name>
					<pgcode>12170</pgcode>
					<mcode>m0007</mcode>
					<link>wing@bank_account</link>
				</small>
				<small>
					<name>윙문자 포인트 충전</name>
					<pgcode>12180</pgcode>
					<mcode>m0016</mcode>
					<link>wing@sms_charge</link>
				</small>
			</mid>
			<mid name="서비스리포트" pgcode="12200">
				<?php if ($account['type'] == 9){ ?>
				<small>
					<name>트래픽 사용내역</name>
					<pgcode>12250</pgcode>
					<mcode>m0019</mcode>
					<link>wing@trf_log</link>
				</small>
				<?php } ?>
				<small>
					<name>윙문자 발송 내역</name>
					<pgcode>12240</pgcode>
					<mcode>m0018</mcode>
					<link>wing@sms_log</link>
				</small>
				<small>
					<name>윙문자 차단 내역</name>
					<pgcode>12260</pgcode>
					<mcode>m0020</mcode>
					<link>wing@sms_block_log</link>
				</small>
				<small>
					<name>윙문자 포인트 내역</name>
					<pgcode>12230</pgcode>
					<mcode>m0017</mcode>
					<link>wing@sms_point_log</link>
				</small>
				<small>
					<name>그룹메일발송 내역</name>
					<pgcode>12220</pgcode>
					<mcode>m0009</mcode>
					<link>wing@group_mail</link>
					<rel>
						<item>
							<name>회원조회</name>
							<link>?body=member@member_list</link>
						</item>
					</rel>
				</small>
			</mid>
		</big>
		<big name="고객센터" category="customer" pgcode="13000" link='body=wing@cs_list'>
			<mid name="1:1고객센터" pgcode="13100">
				<small>
					<name>문의내역</name>
					<pgcode>13110</pgcode>
					<mcode>m0013</mcode>
					<link>customer@cs_list</link>
				</small>
				<small>
					<name>문의내역</name>
					<pgcode>13110</pgcode>
					<mcode>m0014</mcode>
					<link>customer@cs_view</link>
					<hidden>Y</hidden>
				</small>
				<small>
					<name>문의등록</name>
					<pgcode>13120</pgcode>
					<mcode>m0015</mcode>
					<link>customer@cs_reg</link>
				</small>
                <small>
                    <name>1:1고객센터 문의</name>
                    <pgcode>13110</pgcode>
                    <mcode>m0014</mcode>
                    <link>support@sso&amp;obody=body=customer@list</link>
                    <hidden>Y</hidden>
                </small>
                <small>
                    <name>윙스토어 접속</name>
                    <pgcode>13110</pgcode>
                    <mcode>m0014</mcode>
                    <link>wing@main</link>
                    <hidden>Y</hidden>
                </small>
                <small>
                    <name>윙문자 발송내역</name>
                    <pgcode>13110</pgcode>
                    <mcode>m0014</mcode>
                    <link>wing@sms_list</link>
                    <hidden>Y</hidden>
                </small>
			</mid>
		</big>
	</menudata>
</menufile>