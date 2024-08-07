<?PHP

	/* +----------------------------------------------------------------------------------------------+
	' |  설문조사 리스트
	' +----------------------------------------------------------------------------------------------+*/

	if($poll[no]){
		$_replace_code[$_file_name][poll_apply_form]=$poll[form];
		$_replace_code[$_file_name][poll_title]=$poll[title];
		$_replace_code[$_file_name][poll_content]=$poll[content];

		$_tmp="";
		$_line=getModuleContent("poll_item_list");
		// 설문 조사 항목
		while($item=getPollItem($poll[no],1)){
			$_tmp .= lineValues("poll_item_list", $_line, $item);
		}
		$_tmp=listContentSetting($_tmp, $_line);
		$_replace_code[$_file_name][poll_item_list]=$_tmp;

		$_replace_code[$_file_name][poll_apply_url]=$poll['link'];

		if($comdelno){
			$_replace_code[$_file_name][poll_delete_pwd]=$comdelfrm1.getModuleContent("poll_delete_pwd").$comdelfrm2;
		}

		$_tmp="";
		$_line=getModuleContent("poll_comment_list");
		// 설문 조사 댓글
		while($cm=pollComment()){
			$_tmp .= lineValues("poll_comment_list", $_line, $cm);
		}
		$_tmp=listContentSetting($_tmp, $_line);
		$_replace_code[$_file_name][poll_comment_list]=$_tmp;

		$_replace_code[$_file_name][poll_comment_form_start]=$_cm[form1];
		$_replace_code[$_file_name][poll_comment_form_end]=$_cm[form2];
		$_replace_code[$_file_name][poll_comment_writer_hids]=JC($member[no],true);
		$_replace_code[$_file_name][poll_comment_writer_hide]=JC($member[no],true,1).$member[name];
	}else {
		$_replace_code[$_file_name][poll_view_hids]="<div style='display:none'>";
		$_replace_code[$_file_name][poll_view_hide]="</div>";
	}

	$_tmp="";
	$_line=getModuleContent("poll_list");
	// 설문 조사
	while($poll=pollList()){
		$poll[idx]=$idx;
		$poll[title]="<a href=\"".$poll['link']."\">".$poll[title]."</a>";
		$_tmp .= lineValues("poll_list", $_line, $poll);
	}
	$_tmp=listContentSetting($_tmp, $_line);
	$_replace_code[$_file_name][poll_list]=$_tmp;

?>