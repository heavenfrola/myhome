<?PHP

	/* +----------------------------------------------------------------------------------------------+
	' |  모듈별 변수설정
	' +----------------------------------------------------------------------------------------------+*/

	if($_edit_pg == "product_list.".$_skin_ext['m']){
		$_skin_set_vals['text']=array("product_list_imgw"=>"상품이미지 가로", "product_list_imgh"=>"상품이미지 세로", "product_list_cols"=>"한줄상품수", "product_list_rows"=>"한페이지줄수", "product_list_namecut"=>"글자수 제한");
		$_skin_set_vals['radio'] = array('product_disable_tr' => '자동 &lt;tr&gt;태그 사용안함', 'distinct_sc' => '바로가기중복제거');
		$_use_rollover_img = true;
	}elseif($_edit_pg == "click_prd_product_list.".$_skin_ext['m']){
		$_skin_set_vals['text']=array("click_prd_product_list_imgw"=>"상품이미지 가로", "click_prd_product_list_imgh"=>"상품이미지 세로", "click_prd_product_list_cols"=>"한줄상품수", "click_prd_product_list_rows"=>"한페이지줄수", "click_prd_product_list_namecut"=>"글자수 제한");
	}elseif($_edit_pg == "price_search_product_list.".$_skin_ext['m']){
		$_skin_set_vals['text']=array("price_search_product_list_imgw"=>"상품이미지 가로", "price_search_product_list_imgh"=>"상품이미지 세로", "price_search_product_list_cols"=>"한줄상품수", "price_search_product_list_rows"=>"한페이지줄수", "price_search_product_list_namecut"=>"글자수 제한");
	}elseif($_edit_pg == "search_result_product_list.".$_skin_ext['m']){
		$_skin_set_vals['text']=array("search_result_product_list_imgw"=>"상품이미지 가로", "search_result_product_list_imgh"=>"상품이미지 세로", "search_result_product_list_cols"=>"한줄상품수", "search_result_product_list_rows"=>"한페이지줄수", "search_result_product_list_namecut"=>"글자수 제한");
	}elseif($_edit_pg == "search_rank_list.".$_skin_ext['m']){
		$_skin_set_vals['text']=array("search_rank_total"=>"검색어 순위 개수");
	}elseif($_edit_pg == "prd_img_list.".$_skin_ext['m']){
		$_skin_set_vals['text']=array('zoom_width'=>'창 가로사이즈', 'zoom_height'=>'창 세로사이즈', "prd_img_bimgw"=>"최대 가로사이즈", "prd_img_bimgh"=>"최대 세로사이즈", "prd_img_simgw"=>"최소 가로사이즈", "prd_img_simgh"=>"최소 세로사이즈");
	}elseif($_edit_pg == "recent_view_list.".$_skin_ext['m']){
		$_skin_set_vals['radio']=array("recent_view_box_use"=>"박스스크롤 사용");
		$_skin_set_vals['text']=array("recent_view_boxw"=>"박스사이즈 가로", "recent_view_boxy"=>"박스사이즈 세로", "recent_view_box_scroll"=>"스크롤 사이즈", "recent_view_total"=>"총 출력 상품수", "recent_view_imgw"=>"상품이미지 가로", "recent_view_imgh"=>"상품이미지 세로", "recent_view_namecut"=>"글자수 제한");
	}elseif(preg_match("/detail_ref[0-9]*_list\.".$_skin_ext['m']."/", $_edit_pg)){
		$refkey = preg_replace("/detail_ref([0-9]*)_list\.".$_skin_ext['m']."/", '$1', $_edit_pg);
		$_skin_set_vals['text']=array("detail_ref{$refkey}_list_imgw"=>"상품이미지 가로", "detail_ref{$refkey}_list_imgh"=>"상품이미지 세로", "detail_ref{$refkey}_list_cols"=>"한줄상품수", "detail_ref{$refkey}_list_namecut"=>"글자수 제한");
		//$_skin_set_vals['radio'] = array('ref{$refkey}_disable_tr' => '자동 &lt;tr&gt;태그 사용안함');
	}elseif($_edit_pg == "detail_review_list.".$_skin_ext['m']){
		$_skin_set_vals['text']=array("detail_review_list_titlecut"=>"제목 줄임", "detail_review_list_rows"=>"한페이지줄수");
		$_skin_set_vals['radio']=array("detail_review_list_best_use"=>"베스트 우선 정렬");
	}elseif($_edit_pg == "detail_qna_list.".$_skin_ext['m']){
		$_skin_set_vals['text']=array("detail_qna_list_titlecut"=>"제목 줄임", "detail_qna_list_rows"=>"한페이지줄수");
	}elseif($_edit_pg == "cart_list.".$_skin_ext['m']){
		$_skin_set_vals['text']=array("cart_list_imgw"=>"상품이미지 가로", "cart_list_imgh"=>"상품이미지 세로", "cart_list_btw_opt"=>"옵션 연결", "cart_list_opt_f"=>"옵션 앞", "cart_list_opt_b"=>"옵션 뒤", "cart_list_opt_prc"=>"옵션명 연결");
	}elseif($_edit_pg == "order_cart_list.".$_skin_ext['m']){
		$_skin_set_vals['text']=array("order_cart_list_imgw"=>"상품이미지 가로", "order_cart_list_imgh"=>"상품이미지 세로", "order_cart_list_btw_opt"=>"옵션 연결", "order_cart_list_opt_f"=>"옵션 앞", "order_cart_list_opt_b"=>"옵션 뒤", "order_cart_list_opt_prc"=>"옵션 가격 연결");
	}elseif($_edit_pg == "qna_list.".$_skin_ext['m']){
		$_skin_set_vals['text']=array("qna_list_titlecut"=>"제목 줄임", "qna_list_connext"=>"질문과답변 연결");
	}elseif($_edit_pg == "review_list.".$_skin_ext['m']){
        $_skin_set_vals['text']=array("review_list_titlecut"=>"제목 줄임", "review_list_rows"=>"한페이지줄수");
		$_skin_set_vals['radio']=array("review_list_best_use"=>"베스트 우선 정렬");
	}elseif($_edit_pg == "review_notice_list.".$_skin_ext['m']){
		$_skin_set_vals['text']=array("review_notice_list_titlecut"=>"제목 줄임", "review_notice_list_namecut"=>"상품명 줄임");
	}elseif($_edit_pg == "review_total_list.".$_skin_ext['m']){
		$_skin_set_vals['text']=array("review_total_list_titlecut"=>"제목 줄임", "review_total_list_namecut"=>"상품명 줄임", "review_total_list_prd_imgw"=>"상품이미지 가로", "review_total_list_prd_imgh"=>"상품이미지 세로");
	}elseif($_edit_pg == "qna_notice_list.".$_skin_ext['m']){
		$_skin_set_vals['text']=array("qna_notice_list_titlecut"=>"제목 줄임", "qna_notice_list_ymd"=>"날짜 출력 형식", "qna_notice_list_imgw"=>"상품이미지 가로", "qna_notice_list_imgh"=>"상품이미지 세로");
	}elseif($_edit_pg == "qna_total_list.".$_skin_ext['m']){
		$_skin_set_vals['text']=array("qna_total_list_titlecut"=>"제목 줄임", "qna_total_list_namecut"=>"상품명 줄임", "qna_total_list_connect"=>"질문과답변 연결", "qna_total_list_ymd"=>"날짜 출력 형식", "qna_total_prd_list_imgw"=>"상품이미지 가로", "qna_total_prd_list_imgh"=>"상품이미지 세로");
	}elseif($_edit_pg == "mypage_wish_list.".$_skin_ext['m']){
		$_skin_set_vals['text']=array("mypage_wish_list_imgw"=>"상품이미지 가로", "mypage_wish_list_imgh"=>"상품이미지 세로", "mypage_wish_list_btw_opt"=>"옵션 사이 연결", "mypage_wish_list_cols"=>"한줄상품수", "mypage_wish_list_rows"=>"한페이지줄수");
		$_skin_set_vals['radio'] = array('mypage_wish_list_disable_tr' => '자동 &lt;tr&gt;태그 사용함');
	}elseif($_edit_pg == "mypage_ord_cart_list.".$_skin_ext['m']){
		$_skin_set_vals['text']=array("mypage_ord_cart_list_btw_opt"=>"옵션 연결", "mypage_ord_cart_list_opt_f"=>"옵션 앞", "mypage_ord_cart_list_opt_b"=>"옵션 뒤", "mypage_ord_cart_list_opt_prc"=>"옵션명 연결");
	}elseif($_edit_pg == "coordi_list.".$_skin_ext['m']){
		$_skin_set_vals['text']=array("coordi_list_imgw"=>"상품이미지 가로", "coordi_list_imgh"=>"상품이미지 세로", "coordi_list_cols"=>"한줄상품수", "coordi_list_rows"=>"한페이지줄수", "coordi_list_contentcut"=>"내용 글자수 제한");
	}elseif($_edit_pg == "coordi_ref_prd_list.".$_skin_ext['m']){
		$_skin_set_vals['text']=array("coordi_ref_prd_list_imgw"=>"상품이미지 가로", "coordi_ref_prd_list_imgh"=>"상품이미지 세로", "coordi_ref_prd_list_cols"=>"한줄상품수");
	}elseif($_edit_pg == "shop_product_request.".$_skin_ext['p']){
		$_skin_set_vals['text']=array("recom_mail_sizew"=>"팝업창 가로", "recom_mail_sizeh"=>"팝업창 세로", "recom_mail_prd_imgw"=>"상품이미지 가로", "recom_mail_prd_imgh"=>"상품이미지 세로");
	}elseif($_edit_pg == "auto_slide.".$_skin_ext['m']){
		$_skin_set_vals['radio']=array("auto_slide"=>"자동 Slide 사용");
		$_skin_set_vals['text']=array("auto_slide_top"=>"Slide Top (px)", "auto_slide_left"=>"Slide Left (px)", "auto_slide_right"=>"Slide Right (px)",  "auto_slide_speed"=>"Slide 속도", "auto_slide_limittop"=>"Slide 상단 제한", "auto_slide_limitbottom"=>"Slide 하단 제한");
	}elseif($_edit_pg == "member_sms_find.".$_skin_ext['p']){
		$_skin_set_vals['text']=array("sms_find_sizew"=>"팝업창 가로", "sms_find_sizeh"=>"팝업창 세로");
	}elseif($_edit_pg == "fb_like.".$_skin_ext['m']){
		$_skin_set_vals['text']=array("fb_data"=>"가로길이");
		$_skin_set_vals['radio']=array("fb_layout"=>"계정명출력");
	}elseif($_edit_pg == "fb_like_detail.".$_skin_ext['m']){
		$_skin_set_vals['text']=array("fb_data_detail"=>"가로길이");
		$_skin_set_vals['radio']=array("fb_layout_detail"=>"계정명출력");
	}elseif($_edit_pg == 'common_product_selected_list.'.$_skin_ext['m']) {
		$_skin_set_vals['text']=array('common_product_selected_list_w' => '상품이미지 가로', 'common_product_selected_list_h' => '상품이미지 세로');
	}elseif($_edit_pg == 'common_product_select_list.'.$_skin_ext['m']) {
		$_skin_set_vals['text']=array('common_product_select_list_w' => '상품이미지 가로', 'common_product_select_list_h' => '상품이미지 세로');
	}elseif($_edit_pg == 'search_cate_list.wsm') {
		$_skin_set_vals['text']=array('cate_total_name'=>'전체 카테고리 라벨명');
	}elseif($_edit_pg == 'search_xbig_list.wsm') {
		$_skin_set_vals['text']=array('xbig_total_name'=>'전체 카테고리 라벨명');
	}elseif($_edit_pg == 'search_ybig_list.wsm') {
		$_skin_set_vals['text']=array('ybig_total_name'=>'전체 카테고리 라벨명');
	}elseif($_edit_pg == 'detail_multi_option_list.wsm') {
		$_skin_set_vals['text']=array('mo_split_big'=>'옵션별구분자', 'mo_split_small'=>'옵션명/값구분자');
	}elseif($_edit_pg == 'order_finish_prd_list.wsm') {
		$_skin_set_vals['text'] = array("order_finish_prd_list_imgw"=>"상품이미지 가로", "order_finish_prd_list_imgh"=>"상품이미지 세로");
	} elseif($_edit_pg == 'mypage_1to1_prd_list.wsm') {
		$_skin_set_vals['text'] = array(
			'mypage_counsel_product_w' => '상품이미지 가로',
			'mypage_counsel_product_h' => '상품이미지 세로'
		);
	}elseif($_edit_pg == 'promotion_product_list.'.$_skin_ext['m']) {
		$_skin_set_vals['text'] = array(
			'promotion_product_img_w' => '상품이미지 가로',
			'promotion_product_img_h' => '상품이미지 세로',
			'promotion_product_namecut' => '글자수 제한'
		);
		$_use_rollover_img = true;
	} else if($_edit_pg == 'mypage_review_list.'.$_skin_ext['m']) {
		$_skin_set_vals['text'] = array(
			'mypage_review_list_prd_imgw' => '상품이미지 가로',
			'mypage_review_list_prd_imgh' => '상품이미지 세로',
		);
	} else if($_edit_pg == 'detail_review_pts_list.'.$_skin_ext['m'] || $_edit_pg == 'product_review_pts_list.'.$_skin_ext['m']) {
		$_skin_set_vals['radio'] = array(
			'review_pts_revert' => '높은 평점(점수)부터 출력'
		);
	} else if($_edit_pg == 'detail_content2_more.'.$_skin_ext['m']) {
		$_skin_set_vals['text'] = array(
			'detail_more_height' => '상세설명높이(단위포함입력)'
		);
	}

	$_skin_val_name="_skin";
	// 게시판
	if($filename == "list_loop.php"){
		$_skin_set_vals['text']=array("board_list_imgw"=>"이미지 가로", "board_list_imgh"=>"이미지 세로");
		$_skin_val_name="_board_skin";
	}

	if(is_array($_skin_set_vals)){
?>
				<br><div style="background-color:#F3F3F3; padding:5px 10px; text-align:left;">
<?php
		$ii=1;
		// 이미지 선택 필드 출력 개수
		$_img_select=($_use_rollover_img == true) ? 2 : 1;

		for($kk=1; $kk<=$_img_select; $kk++){
			if(@strchr($_edit_pg, "product_list.".$_skin_ext['m']) || preg_match("/detail_ref([0-9]*)_list/", $_edit_pg) || $_edit_pg == 'order_finish_prd_list.wsm'){
				if(@strchr($_edit_pg, "product_list.".$_skin_ext['m'])) $_skin_fd_name=@str_replace("product_list.".$_skin_ext['m'], "", $_edit_pg);
				if(preg_match("/detail_ref([0-9]*)_list/", $_edit_pg)) $_skin_fd_name = preg_replace("/detail_ref([0-9]*)_list\.".$_skin_ext['m']."/", 'ref$1_', $_edit_pg);
				if($_edit_pg == 'order_finish_prd_list.wsm') $_skin_fd_name = 'order_finish_';

				//order_finish_prd_list.wsm
				// 상품리스트 롤오버 이미지 필드 선택
				if($_use_rollover_img == true && $kk == 2){
					if($_edit_pg == "product_list.".$_skin_ext['m']) {
						$_skin_fd_name="over_";
					} else {
						$_skin_fd_name .= 'over_';
					}
					$_this_fd_name="롤오버";
					if(!$_skin[$_skin_fd_name."product_img_fd"]) $_skin[$_skin_fd_name."product_img_fd"]="N";
				}
?>
				<u><span style="width:90px;"><?=$_this_fd_name?>이미지 선택</span></u> : <select name="skin[<?=$_skin_fd_name?>product_img_fd]" style="width:80px;">
<?php
				if($_skin_fd_name == "over_"){
?>
					<option value="N" <?=checked($_skin[$_skin_fd_name."product_img_fd"], "N", 1)?>>사용안함</option>
<?php
				}
?>
					<option value="3" <?=checked($_skin[$_skin_fd_name."product_img_fd"], "3", 1)?>>소</option>
					<option value="2" <?=checked($_skin[$_skin_fd_name."product_img_fd"], "2", 1)?>>중</option>
					<option value="1" <?=checked($_skin[$_skin_fd_name."product_img_fd"], "1", 1)?>>대</option>
<?php
				if($cfg['add_prd_img'] != "" && $cfg['add_prd_img'] > 3){
					for($jj=4; $jj<=$cfg['add_prd_img']; $jj++){
						$fd_name=($cfg["prd_img".$jj]) ? $cfg["prd_img".$jj] : "추가사진".$jj;
?>
					<option value="<?=$jj?>" <?=checked($_skin[$_skin_fd_name."product_img_fd"], $jj, 1)?>><?=$fd_name?></option>
<?php
					}
				}
?>
				</select> &nbsp;
<?php
				$ii++;
			}
		}
		foreach($_skin_set_vals as $key=>$val){
			if(!is_array($_skin_set_vals[$key])) continue;
			foreach($_skin_set_vals[$key] as $key2=>$val2){
				if($ii > 5 && $ii % 5 == 1){
?>
				<br>
<?php
				}
				if($key == "text"){
?>
				<u><span><?=$val2?></span></u> : <input type="<?=$key?>" name="skin[<?=$key2?>]" style="width:60px;" class="input" value="<?=${$_skin_val_name}[$key2]?>" onkeydown="if(event.keyCode == 13){ skinSetVals(); return false; }"> &nbsp;
<?php
				}
				if($key == "radio"){
?>
				<u><?=$val2?></u> : <input type="<?=$key?>" name="skin[<?=$key2?>]" value="Y" <?=checked(${$_skin_val_name}[$key2], "Y")?>> 예 <input type="<?=$key?>" name="skin[<?=$key2?>]" value="N" <?=checked(${$_skin_val_name}[$key2], "N").checked(${$_skin_val_name}[$key2], "")?>> 아니오 &nbsp;
<?php
				}
				$ii++;
			}
		}
		unset($_skin_set_vals);
?>

				<span class="box_btn_s blue">
					<input type="button" value="설정저장" onClick="skinSetVals();" class="btn2">
				</span>
				</div>
<?php
	}
?>