<?PHP

	/* +----------------------------------------------------------------------------------------------+
	' |  상품 QNA 리스트
	' +----------------------------------------------------------------------------------------------+*/

	if(!$cfg['product_qna_row']) $cfg['product_qna_row'] = 20;

	$_tmp="";
	$_line=getModuleContent("qna_notice_list");
	$_para1=(!$_skin[qna_notice_list_titlecut]) ? 100 : $_skin[qna_notice_list_titlecut];
	$_para2=(!$_skin[qna_notice_list_namecut]) ? 100 : $_skin[qna_notice_list_namecut];
	$_para3=(!$_skin[qna_notice_list_ymd]) ? "Y/m/d" : $_skin[qna_notice_list_ymd];

	qnaReset();

	// 공지리스트 (제목줄임,상품명줄임,한페이지줄,페이지블럭,답변,날짜,공지)
	$_para4=(!$_skin['qna_notice_list_imgw']) ? 700 : $_skin['qna_notice_list_imgw'];
	$_para5=(!$_skin['qna_notice_list_imgh']) ? 1000 : $_skin['qna_notice_list_imgh'];

	while($qna=qnaAllList($_para1,$_para2,20,10,"<br><b>답변 : </b><br>",$_para3,"Y")) {
		$qna[qna_idx]=$qna_idx;
		$qna[new_i]=($qna[new_check]) ? $_prd_board_icon['new'] : "";
		$qna['file_icon'] = ($qna['atc']) ? $_prd_board_icon['file'] : "";
		$qna[title_nolink]=$qna[title];
		$qna[title]="<a href=\"javascript:layTglList('qna','revQna','".$qna[no]."');\">".$qna[title]."</a>";
		$qna['link2'] = "javascript:layTglList('qna','revQna','".$qna[no]."');";

		for($i = 1; $i <= 2; $i++) {
			$_file = $qna['upfile'.$i];
			if(!$_file) continue;
			if(!$file_url) $file_url = getFileDir($qna['updir']);
			$_ext = strtolower(getExt($_file));
			if(in_array($_ext, array('jpg', 'jpeg', 'gif', 'png'))) {
				$qna['img'.$i] = "<img src='$file_url/$qna[updir]/$_file' class='layTgl_img_$qna[no]' _width='$_para4' _height='$_para5' />";
			} else {
				$qna['img'.$i] .= "<p class='layTgl_file'><a href='$file_url/{$qna['updir']}/$_file' target='hidden$now'>".$qna['ori_file'.$i]."</a></p>";
			}
		}

		$_tmp .= lineValues("qna_notice_list", $_line, $qna);
	}
	$_tmp=listContentSetting($_tmp, $_line);
	$_replace_code[$_file_name][qna_notice_list]=$_tmp;

	qnaReset();

	$_tmp="";
	$_line=getModuleContent("qna_total_list");
	$_para1=(!$_skin[qna_total_list_titlecut]) ? 100 : $_skin[qna_total_list_titlecut];
	$_para2=(!$_skin[qna_total_list_namecut]) ? 100 : $_skin[qna_total_list_namecut];
	$_para3=(!$_skin[qna_total_connect]) ? "<br><b>답변 : </b><br>" : $_skin[qna_total_connect];
	$_para4=(!$_skin[qna_total_list_ymd]) ? "Y/m/d" : $_skin[qna_total_list_ymd];
	$_para5=(!$_skin[qna_total_prd_list_imgw]) ? 50 : $_skin[qna_total_prd_list_imgw];
	$_para6=(!$_skin[qna_total_prd_list_imgh]) ? 50 : $_skin[qna_total_prd_list_imgh];
	// 글리스트 (제목줄임,상품명줄임,한페이지줄,페이지블럭,답변,날짜,공지)
	while($qna=qnaAllList($_para1,$_para2,$cfg['product_qna_row'],10,$_para3,$_para4,"N")) {
		$qna[qna_idx]=$qna_idx;
		// 2009-09-04 : 상품 이미지 추가 - Han
		if($qna[prd_name]){
			$_prd_img=prdImg(3, $_prd_cache[$qna[pno]], $_para5, $_para6);
			$qna[prd_img]="<a href=\"".$qna[link1]."\"><img src=\"".$_prd_img[0]."\" ".$_prd_img[1]."></a>";
		}
		$qna[prd_name]="<a href=\"".$qna[link1]."\">".$qna[prd_name]."</a>";
		$qna[secret_i]=($qna[secret]=="Y") ? $_prd_board_icon['secret'] : "";
		$qna[new_i]=($qna[new_check]) ? $_prd_board_icon['new'] : "";
		$qna['reply_i']=(strip_tags($qna['answer'], '<img>') || $qna['upfile3'] || $qna['upfile4']) ? $_prd_board_icon['reply'] : "";
		$qna['reply_b_i']=(strip_tags($qna['answer'], '<img>') || $qna['upfile3'] || $qna['upfile4']) ? '' : $_prd_board_icon['reply_b'];
		$qna['file_icon'] = ($qna['atc']) ? $_prd_board_icon['file'] : "";
		$qna[title_nolink]=$qna[title];
		$qna[title]="<a href=\"".$qna[link2]."\">".$qna[title]."</a>";
		$_tmp .= lineValues("qna_total_list", $_line, $qna, "", 2);
	}
	$_tmp=listContentSetting($_tmp, $_line);
	$_replace_code[$_file_name]['qna_total_list'] = $_tmp;

    // 다음 페이지 주소
    $_page = (int) $_GET['page'];
    if (!$_page) $_page = 1;
    $_replace_code[$_file_name]['qna_nextpage_link'] =
        ($PagingInstance->end > $_page) ?
        $_SERVER['SCRIPT_NAME'].makeQueryString(true, 'page').'&page='.($_page+1) : '';;

?>