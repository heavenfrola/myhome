<?PHP

// 단독 실행 불가
	if(!defined("_lib_inc")) exit();

	$orderby=($config['board_comment_sort'] == 2) ? "order by `no` desc" : "order by `no`";
	$sql="select * from `$mari_set[mari_comment]` where `ref`='$no' and `db`='$db' $orderby";
	$cres=$pdo->iterator($sql);



	include $skin_path."comment_list_top.php";
	$_replace_datavals[$_file_name][board_comment_list_vals]="댓글작성자:name;댓글작성일시:reg_date;댓글내용:content;댓글삭제링크태그:comment_del_link;";
    foreach ($cres as $cdata) {
		$cdata[name]=getWriterName($cdata);
		$cdata[reg_date]=date("Y/m/d H:i",$cdata[reg_date]);
		$cdata[content]=autolink(nl2br(del_html(stripslashes($cdata[content]))));
		$auth=getDataAuth($cdata);
		$link[comment_del]="<truemari.com ";
		if($auth) {
			if($auth==1 || $config[comment_del]!='N') $link[comment_del]="<a href=\"javascript:mariExec('write@comment_del','$cdata[no]','$auth')\">";
		}
		if($cfg[design_version] == "V3"){
			if($_cmline == ""){
				$_cmline=getModuleContent($skin_path."comment_list_loop.php", 1);
			}
			$cdata[comment_del_link]=$link[comment_del];
			echo lineValues("board_comment_list_vals", $_cmline, $cdata);
		}else include $skin_path."comment_list_loop.php";
	}
	include $skin_path."comment_list_bottom.php";

	if(getAuth("comment")>=0) {

		if($member[level]<10) {
			$hidden_member[0]="<!--";
			$hidden_member[1]="//-->";
		}
		include $skin_path."comment_write.php";
	}

?>