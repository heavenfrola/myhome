<?PHP

	/* +----------------------------------------------------------------------------------------------+
	' |  상품QNA 상세보기
	' +----------------------------------------------------------------------------------------------+*/

	$_tmp = "";
	$_line = getModuleContent("qna_list");
	$_para1 = (!$_skin['qna_list_titlecut']) ? 50 : $_skin['qna_list_titlecut'];
	while($qna = qnaList($_skin['qna_list_connext'])){
		$qna['qna_idx'] = $qna_idx;
		$qna['secret_i'] = ($qna['secret'] == "Y") ? "<img src=\"".$_skin['url']."/img/shop/i_secret.gif\" border=\"0\" alt=\"\">" : "";
		$qna['new_i'] = ($qna['new_check']) ? $_prd_board_icon['new'] : "";
		$qna['reply_i']=(strip_tags($qna['answer'], '<img>') || $qna['upfile3'] || $qna['upfile4']) ? $_prd_board_icon['reply'] : "";
		$qna['reply_b_i']=(strip_tags($qna['answer'], '<img>') || $qna['upfile3'] || $qna['upfile4']) ? '' : $_prd_board_icon['reply_b'];
		$qna['file_icon'] = ($qna['atc']) ? $_prd_board_icon['file'] : "";
		$qna['link2'] = "javascript:prdBoardView('qna','".$qna['no']."');";
		$qna['title_nolink'] = cutstr($qna['title'], $_para1);
		$qna['title'] = "<a href=\"".$qna['link2']."\">".cutstr($qna['title'], $_para1)."</a>";
		$qna['prd_link'] = ($qna['link1'] && $qna['prd_name']) ? $qna['link1'] : '';

		$_tmp .= lineValues("qna_list", $_line, $qna, "", 2);
	}
	$_tmp = listContentSetting($_tmp, $_line);
	$_replace_code[$_file_name]['qna_list'] = $_tmp;

	$rno = numberOnly($_GET['rno']);
	if($rno){
		$_replace_code[$_file_name]['qna_script'] = "
<script type=\"text/javascript\">
<!--
prdBoardView('qna','".$rno."');
//-->
</script>";
	}

?>