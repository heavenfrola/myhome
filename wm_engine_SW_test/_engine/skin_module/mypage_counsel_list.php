<?PHP

	/* +----------------------------------------------------------------------------------------------+
	' |  주문 1:1 게시판 리스트
	' +----------------------------------------------------------------------------------------------+*/

	$_tmp="";
	$_line = getModuleContent('mypage_1to1_list');
	while($cs = counselLoop()){
		$cs_title = $cs['title'];
		$cs['widx'] = $widx;
		$cs['title'] = "<a href=\"".$cs['link']."\"><b>".cutstr($cs_title, 60)."</b></a>";
		$cs['title2'] = "<b>".cutstr($cs_title, 60)."</b>";
		$cs['link'] = str_replace('javascript:', '', $cs['link']);
		$cs['reply_yn'] = ($cs['reply_date']) ? __lang_mypage_info_ctype2__ : __lang_mypage_info_ctype1__;
		// 첨부 이미지
		$cs['atc'] = "";
		for($ii=1; $ii<=2; $ii++){
			if($cs['upfile'.$ii]){
				$cs['atc'] = "Y";
				$img = prdImg($ii, $cs, $_para1, $_para2, $def_img);
                if ( !in_array(strtolower(getExt($img[0])), array('jpg','jpeg','png','gif','webp')) ) {
                    //이미지파일이 아닌 경우
                    $cs['img'.$ii] = '<a href="'.$img[0].'" target="_blank" download="'.__lang_common_info_attachFile__.$ii.'.'.getExt($img[0]).'" ><strong>['.__lang_common_info_attachFile__.' #'.$ii.']</strong></a>';
                } else {
                    $cs['img'.$ii] = $img[0];
                    $cs['img'.$ii] = ($cs['img'.$ii]) ? "<img src=\"".$cs['img'.$ii]."\" border=\"0\" id=\"cs_img".$cs[no]."_".$ii."\">" : "";
                }
			}	
		}
		$cs['file_icon'] = ($cs['atc']) ? $_prd_board_icon['file'] : "";
		$_tmp .= lineValues('mypage_1to1_list', $_line, $cs);
	}
	$_tmp = listContentSetting($_tmp, $_line);
	$_replace_code[$_file_name]['mypage_1to1_list'] = $_tmp;

?>