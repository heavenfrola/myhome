<?PHP

	/* +----------------------------------------------------------------------------------------------+
	' |  마이페이지 내가작성한 상품QNA
	' +----------------------------------------------------------------------------------------------+*/

	$_tmp="";
	$_line=getModuleContent("mypage_qna_list");
	while($qna=contentList()){
		$qna[secret_i]=($qna[secret]=="Y") ? $_prd_board_icon['secret'] : "";
		$qna[new_i]=($qna[new_check]) ? $_prd_board_icon['new'] : "";
		$qna[reply_i]=($qna[answer]) ? $_prd_board_icon['reply'] : "";
		$qna['reply_b_i']=($qna['answer']) ? '' : $_prd_board_icon['reply_b'];
		$qna[link2]="javascript:prdBoardView('qna','".$qna[no]."');";
		$qna['title_nolink'] = $qna['title'];
		$qna[title]="<a href=\"".$qna[link2]."\">".cutstr($qna[title],50)."</a>";
		$qna['file_icon'] = ($qna['atc']) ? $_prd_board_icon['file'] : "";
		$_tmp .= lineValues("mypage_qna_list", $_line, $qna, "", 2);
	}
	$_tmp=listContentSetting($_tmp, $_line);
	$_replace_code[$_file_name][mypage_qna_list]=$_tmp;

	$rno = numberOnly($_GET['rno']);
	if($rno){
		$_replace_code[$_file_name][qna_script]="
<script language=\"JavaScript\">
<!--
prdBoardView('qna','".$rno."');
//-->
</script>";
	}

?>