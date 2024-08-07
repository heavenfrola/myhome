<?php

// 2008-07-24 : 템플릿 페이지명 정리 - Han

$dir_arr=array("common"=>"정보 페이지",
"content"=>"정보 페이지",
"css"=>"스타일시트 & 자바스크립트",
"js"=>"스타일시트 & 자바스크립트",
"main"=>"메인 페이지",
"member"=>"회원 페이지",
"mypage"=>"마이 페이지",
"shop"=>"상품 & 주문 정보"
);
//$dir_sub_arr[common]=array("zip_search.php"=>"우편번호 찾기");
$dir_sub_arr['content']=array("company.php"=>"회사 소개",
"guide.php"=>"고객 지원 센터",
"join_rull.php"=>"회원 가입 약관",
"privacy.php"=>"개인 정보 취급 방침",
"uselaw.php"=>"이용 약관");
$dir_sub_arr['css']=array("style.css"=>"스타일시트");
$dir_sub_arr['js']=array("common.js"=>"자바스크립트");
$dir_sub_arr['main']=array("index.php"=>"메인 페이지");
$dir_sub_arr['member']=array("edit_step1.php"=>"회원 정보 수정 비밀번호 입력",
"edit_step3.php"=>"회원 정보 수정 완료",
"find_step1.php"=>"아이디 비밀번호 찾기",
"join_frm.php"=>"회원 가입/정보 수정 폼",
"join_step1.php"=>"회원 가입 약관 동의",
"join_step3.php"=>"회원 가입 완료",
"login.php"=>"회원 로그인");
$dir_sub_arr['common']=array("zip_search.php"=>"우편번호 찾기");
$dir_sub_arr['mypage']=array("counsel_list.php"=>"고객 상담 리스트",
"counsel_step1.php"=>"고객 상담 입력 폼",
"counsel_step2.php"=>"고객 상담 완료",
"coupon_down_list.php"=>"쿠폰 리스트",
"emoney.php"=>"예치금 내역",
"milage.php"=>"적립금 내역",
"mypage.php"=>"마이페이지 메인",
"order_detail.php"=>"주문서 상세 내용",
"order_list.php"=>"주문서 리스트",
"point.php"=>"포인트 내역",
"wish_list.php"=>"찜한 상품/위시 리스트",
"withdraw_step1.php"=>"회원 탈퇴 신청",
"withdraw_step2.php"=>"회원 탈퇴 완료");
$dir_sub_arr['shop']=array("big_section.php"=>"상품 리스트",
"cart.php"=>"장바구니 리스트",
"detail.php"=>"상품 상세 내용",
"order.php"=>"주문서 입력 폼",
"order_finish.php"=>"주문 접수 완료",
"price_search.php"=>"가격별 상품 리스트",
"click_prd.php"=>"최근 본 상품 리스트",
"product_qna.php"=>"상품 질문과 답변",
"product_qna_list.php"=>"상품 질문과 답변 리스트",
"product_qna_mod_frm.php"=>"상품 질문과 답변 수정 폼",
"product_qna_secret.php"=>"상품 질문과 답변 비밀번호 확인 폼",
"product_request.php"=>"상품 추천 메일 폼",
"product_review.php"=>"상품 이용 후기",
"product_review_list.php"=>"상품 이용 후기 리스트",
"product_review_mod_frm.php"=>"상품 이용 후기 수정 폼",
"poll_list.php"=>"설문 조사 리스트",
"search_result.php"=>"상품 검색 결과 리스트",
"zoom.php"=>"상품 확대 이미지");


// 2008-09-18 : 공통페이지(_include)명 정리

$common_arr=array("common"=>"상단/하단 공통정보",
"product"=>"상품관련 정보",
"smsg"=>"기타페이지"
);
$common_sub_arr['common']=array("header.php"=>"상단 정보",
"footer.php"=>"하단 정보");
$common_sub_arr['product']=array("mini_detail.php"=>"상품 게시판 상단 상품",
"product_qna.php"=>"상품 질문과 답변 리스트",
"product_qna_form.php"=>"상품 질문과 답변 등록 폼",
"product_review.php"=>"상품 이용 후기 리스트",
"product_review_form.php"=>"상품 이용 후기 등록 폼",
"product0.php"=>"리스트페이지 상품",
"click_prd.php"=>"최근 본 상품",
"product_main.php"=>"메인페이지 상품",
"product1.php"=>"기타페이지 상품",
"product2.php"=>"기타페이지 상품");
$common_sub_arr['smsg']=array("paging.php"=>"리스트 검색 하단 페이지 링크");

// 2008-10-08 : 게시판 스킨명 정리

$board_sub_arr=array("calendar.php"=>"달력 디자인",
"style.css"=>"게시판 내부 스타일시트",
"list_top.php"=>"글 상단",
"notice.php"=>"글 상단 공지사항 목록",
"list_loop.php"=>"글 목록",
"list_bottom.php"=>"글 하단",
"view.php"=>"글 내용 보기",
"write.php"=>"글 입력/수정 폼",
"secret.php"=>"비밀글 비밀번호 확인 폼",
"del.php"=>"글 삭제시 비밀번호 확인 폼",
"edit.php"=>"글 수정시 비밀번호 확인 폼",
"comment_list_top.php"=>"댓글 상단",
"comment_list_loop.php"=>"댓글 목록",
"comment_list_bottom.php"=>"댓글 하단",
"comment_write.php"=>"댓글 입력 폼",
"comment_del.php"=>"댓글 편집시 비밀번호 확인 폼",
"file_top.php"=>"첨부파일 상단",
"file_loop.php"=>"첨부파일 목록",
"file_bottom.php"=>"첨부파일 하단",
"products_list.php"=>"관련상품 출력 리스트");


?>