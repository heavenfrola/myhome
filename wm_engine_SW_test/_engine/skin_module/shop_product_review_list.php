<?PHP

	/* +----------------------------------------------------------------------------------------------+
	' |  상품 리뷰 리스트
	' +----------------------------------------------------------------------------------------------+*/

	if(!$cfg['product_review_row']) {
		$cfg['product_review_row'] = $_SESSION['browser_type'] == 'mobile' ? 10 :20;
	}

	$_tmp = "";
	$_line = getModuleContent("review_notice_list");
	$_para1 = (!$_skin['review_notice_list_titlecut']) ? 100 : $_skin['review_notice_list_titlecut'];
	$_para2 = (!$_skin['review_notice_list_namecut']) ? 100 : $_skin['review_notice_list_namecut'];
	$_para3 = (!$_skin['review_notice_list_imgw']) ? 1000 : $_skin['review_notice_list_imgw'];
	// (제목줄임,상품명줄임,한페이지줄,페이지블럭,이미지가로)
	while($review = reviewAllList($_para1, $_para2, 20, 10, $_para3, "", "", "Y")) {
		$review['title_nolink'] ="<b>".$review['title']."</b>";
		$review['title'] = "<a href=\"javascript:layTglList('rev','revQna','".$review['no']."')\"><b>".$review['title']."</b></a>";
		$review['file_icon'] = ($review['atc']) ? $_prd_board_icon['file'] : "";
		$review['link3'] = "javascript:layTglList('rev','revQna','".$review['no']."');";

		// 첨부 이미지
		$_attach_para1 = (!$_skin[$pg_type."review_list_imgw"]) ? 700 : $_skin[$pg_type."review_list_imgw"];
		$_attach_para2 = (!$_skin[$pg_type."review_list_imgh"]) ? 1000 : $_skin[$pg_type."review_list_imgh"];
		for($ii=1; $ii<=2; $ii++){
			if($review['upfile'.$ii]){
				$img=prdImg($ii,$review,$_attach_para1,$_attach_para2,$def_img);
				$review['img'.$ii]=$img[0];
				$review['imgstr'.$ii]=$img[1];
			}else{
				continue;
			}
			// 2009-10-08 : 파일 서버 사용 시 스크립트로 리사이즈 - Han
			$review['img'.$ii] = ($review['img'.$ii]) ? "<img src=\"".$review['img'.$ii]."\" ".$review['imgstr'.$ii]." border=\"0\" id=\"review_img".$review['no']."_".$ii."\" style='max-width: 100%;'>" : "";
			if($review['imgstr'.$ii] == ""){
				$review['img'.$ii] = "<div class='review_notice_img' style=\"width:100%; overflow:hidden;\">".$review['img'.$ii]."</div>";
			}
		}

		$_tmp .= lineValues("review_notice_list", $_line, $review);
	}
	$_replace_code[$_file_name]['review_notice_list'] = $_tmp;

	reviewReset();

	$_tmp = "";
    if ($type == '2' && file_exists($_skin['folder'].'/MODULE/review_photo_list.wsm') == true) {
        $_line = $_line_p = getModuleContent('review_photo_list'); // 포토후기 스킨
    } else {
    	$_line = getModuleContent("review_total_list");
    }
	$_para1 = (!$_skin['review_total_list_titlecut']) ? 100 : $_skin['review_total_list_titlecut'];
	$_para2 = (!$_skin['review_total_list_namecut']) ? 100 : $_skin['review_total_list_namecut'];
	$_para3 = (!$_skin['review_total_list_prd_imgw']) ? 50 : $_skin['review_total_list_prd_imgw'];
	$_para4 = (!$_skin['review_total_list_prd_imgh']) ? 50 : $_skin['review_total_list_prd_imgh'];
	// (제목줄임,상품명줄임,한페이지줄,페이지블럭,이미지가로)
	while($review = reviewAllList($_para1, $_para2, $cfg['product_review_row'], 10, $_para3, $_para4, "", "N", 3, $rstat)) {
		$review['prd_img'] = $review['prd_name'] ? "<a href=\"".$review['link1']."\"><img src=\"".$review['img']."\" ".$review['imgstr']."></a>" : "";
		$review['prd_name'] = "<a href=\"".$review['link1']."\">".$review['prd_name']."</a>";
		$review['title_nolink'] = $review['title'];
		$review['title'] = "<a href=\"".$review['link2']."\">".$review['title']."</a>";
		$review['file_icon'] = ($review['atc']) ? $_prd_board_icon['file'] : "";
		$review['new_i'] = ($review['new_check']) ? $_prd_board_icon['new'] : "";
		$review['star'] = reviewStar($_prd_board_icon['star']);
        $review['has_photo'] = ($review['upfile1'] || $review['upfile2']) ? 'Y' : '';
		$_tmp .= lineValues("review_total_list", $_line, $review, "", 2);
	}
	$_tmp = listContentSetting($_tmp, $_line);
	$_replace_code[$_file_name]['review_total_list'] = $_tmp;
    if ($_line_p) {
    	$_replace_code[$_file_name]['review_photo_list'] = $_tmp;
    }

    // 다음 페이지 주소
    $_page = (int) $_GET['page'];
    if (!$_page) $_page = 1;
    $_replace_code[$_file_name]['review_nextpage_link'] =
        ($PagingInstance->end > $_page) ?
        $_SERVER['SCRIPT_NAME'].makeQueryString(true, 'page').'&page='.($_page+1) : '';;

	// 회원추가항목 상품후기 검색
	if(CheckCodeUsed($_replace_hangul[$_file_name]['review_mbr_addr_list'])) {
		$mbr_inc = $root_dir.'/_config/member.php';
		if(file_exists($mbr_inc) == true) {
			include_once $mbr_inc;

			$_line = getModuleContent('review_mbr_addr_list');
			$_tmp = '';
			foreach($_mbr_add_info as $key => $mbr) {
				if($mbr['type'] != 'radio' && $mbr['type'] != 'checkbox') continue;
				if($mbr['review_link'] != 'Y') continue;

				$mbr['name'] = stripslashes($mbr['name']);
				if(strlen($_GET['mbr'.$key]) > 0) {
					$_mbr_search = explode(',', $_GET['mbr'.$key]);
				} else {
					$_mbr_search = array();
				}
				foreach($mbr['text'] as $item_no => $iten_name) {
					$iten_name = stripslashes($iten_name);
					$checked = (in_array($item_no, $_mbr_search)) ? 'checked' : '';
					$mbr['checkbox'] .= "<label class='mbr_item mbr_item$key'><input type='checkbox' class='rev_mbr' name='mbr{$key}[]' value='$item_no' $checked> $iten_name</label>";
				}
				$_tmp .= lineValues('review_mbr_addr_list', $_line, $mbr);
			}
			$_tmp = listContentSetting($_tmp, $_line);
			$_tmp = "<form id='mbr_search'>$_tmp</form>";
			$_replace_code[$_file_name]['review_mbr_addr_list'] = $_tmp;

			unset($_mbr_add_info, $mbr, $_tmp, $_line);
		}
	}

	// override (shop_detail_review.inc.php);
	$_replace_code[$_file_name]['detail_review_sort_lastest'] = '?rev_sort=1';
	$_replace_code[$_file_name]['detail_review_sort_pts_desc'] = '?rev_sort=2';
	$_replace_code[$_file_name]['detail_review_sort_pts_asc'] = '?rev_sort=3';
	$_replace_code[$_file_name]['detail_review_sort_recommend'] = '?rev_sort=4';
	$_replace_code[$_file_name]['detail_review_sort_lastest_sel'] = (!$_GET['rev_sort'] || $_GET['rev_sort'] == 1) ? 'selected' : '';
	$_replace_code[$_file_name]['detail_review_sort_pts_desc_sel'] = ($_GET['rev_sort'] == 2) ? 'selected' : '';
	$_replace_code[$_file_name]['detail_review_sort_pts_asc_sel'] = ($_GET['rev_sort'] == 3) ? 'selected' : '';
	$_replace_code[$_file_name]['detail_review_sort_recommend_sel'] = ($_GET['rev_sort'] == 4) ? 'selected' : '';

?>