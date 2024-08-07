<?PHP

	/* +----------------------------------------------------------------------------------------------+
	' |  설문조사 리스트
	' +----------------------------------------------------------------------------------------------+*/

	include_once $engine_dir."/_engine/include/common.lib.php";

	$today=date("Y-m-d");
	$no = numberOnly($_GET['no']);
	if($no){
		$poll=get_info($tbl[poll_config],"no",$no);
		if(!$poll[no]) $no="";
		$poll[title]=stripslashes($poll[title]);
		$poll[content]=nl2br($poll[content]);
		$updir="_data/poll_imgs";
		$imgsrc=$updir."/".$poll[upfile1];
		if($poll[upfile1] && is_file($root_dir."/".$imgsrc)){
			if($poll[content]) $poll[content] .= "<br>";
			list($iw,$ih)=@getimagesize($root_dir."/".$imgsrc);
			$isize=setImageSize($iw,$ih,600,5000);
			$poll[content] .= "<center><img src='".$root_url."/".$imgsrc."' $isize[2] vspace='10'></center>";
		}
		$poll[form]="<form method=\"post\" name=\"poll_frm$poll[no]\" action=\"$root_url/main/exec.php\" target=\"hidden$now\" style=\"margin:0px\">\n";
		$poll[form] .= "<input type=\"hidden\" name=\"exec_file\" value=\"shop/poll.exe.php\">\n";
		$poll[form] .= "<input type=\"hidden\" name=\"no\" value=\"$poll[no]\">\n";
		$poll[form] .= "<input type=\"hidden\" name=\"poll\">\n";
		$poll[form] .= "</form>";
		$poll['link']=":: 현재 설문기간이 지났습니다 ::<wisamall 2003";
		if($poll[sdate] <= $today && $poll[fdate] >= $today) $poll['link']="<a href=\"javascript:jsVote(document.poll_frm$poll[no]);\">";
		$add_q=" and `no` != '$poll[no]'";

		$_cm[form1]="<form method=\"post\" action=\"$root_url/main/exec.php\" target=\"hidden$now\" style=\"margin:0px\" onsubmit=\"return jsPollCom(this);\">\n";
		$_cm[form1] .= "<input type=\"hidden\" name=\"exec_file\" value=\"shop/poll.exe.php\">\n";
		$_cm[form1] .= "<input type=\"hidden\" name=\"exec\" value=\"com_insert\">\n";
		$_cm[form1] .= "<input type=\"hidden\" name=\"ref\" value=\"$poll[no]\">\n";
		$_cm[form2]="</form>";

		if($comdelno){
			$comdelfrm1="<form method=\"post\" name=\"compwdfrm\" action=\"$root_url/main/exec.php\" target=\"hidden$now\" style=\"margin:0px\">\n";
			$comdelfrm1 .= "<input type=\"hidden\" name=\"exec_file\" value=\"shop/poll.exe.php\">\n";
			$comdelfrm1 .= "<input type=\"hidden\" name=\"exec\" value=\"com_delete\">\n";
			$comdelfrm1 .= "<input type=\"hidden\" name=\"rUrl\" value=\"$rUrl\">\n";
			$comdelfrm1 .= "<input type=\"hidden\" name=\"no\" value=\"$comdelno\">\n";
			$comdelfrm2="</form>";
			$comdelfrm2 .= "\n<script type='text/javascript'>\n";
			$comdelfrm2 .= "window.onload=function (){\n";
			$ckmem=$pdo->row("select `member_no` from `$tbl[poll_comment]` where `no`='$comdelno'");
			if(($member[no] && $ckmem == $member[no]) || $member[level] == 1 || $admin[no]){
				$comdelfrm2 .= "document.compwdfrm.submit();";
			}else{
				$comdelfrm2 .= "document.compwdfrm.pwd.focus();\n";
			}
			$comdelfrm2 .= "}\n";
			$comdelfrm2 .= "</script>";
		}
	}

	if($poll['auth'] == 2) {
		$auth_script = "<script>
						// 댓글작성시 focus
						$('input[name=\"name\"], input[name=\"pwd\"] ').on('click',function(){
						 alert(\"로그인 부탁드립니다\");
							 window.location.href=root_url+\"/member/login.php?guest=true&rURL=\"+encodeURIComponent(this_url);
						});
						// 댓글작성시 focus
						</script>";
		$_cm[form2] .= $auth_script;
	}

	$sql="select * from `$tbl[poll_config]` where 1 $add_q order by `no` desc";
	include $engine_dir."/_engine/include/paging.php";

	if($page<=1) $page=1;
	if(!$row) $row=10;
	$block=10;
	if(!$QueryString) $QueryString="&no=$no";

	$NumTotalRec=$pdo->row("select count(*) from `$tbl[poll_config]` where 1 $add_q");
	$PagingInstance = new Paging($NumTotalRec, $page, $row, $block);
	$PagingInstance->addQueryString($QueryString);
	$PagingResult=$PagingInstance->result($pg_dsn);
	$sql.=$PagingResult[LimitQuery];

	$pageRes=$PagingResult[PageLink];
	$pres = $pdo->iterator($sql);
	$idx=$NumTotalRec-($row*($page-1))+1;

	function pollList(){
		global $idx,$pres,$page,$no;
		$data = $pres->current();
        $pres->next();
		if($data == false) { unset($pres); return false; }

		$data[title]=stripslashes($data[title]);
		if($no == $data[no]) $data[title]="<b>".$data[title]."</b>";
		$data['link']="$PHP_SELF?no=$data[no]&page=$page";

		$idx--;
		return $data;
	}

	function getPollItem($no,$doPoll=""){
		global $tbl,$itemsql,$total_items,$today, $pdo;
		if(!$itemsql){
			$itemsql = $pdo->iterator("select * from `$tbl[poll_item]` where `ref`='$no' order by `sort`");
			$total_items = $pdo->row("select count(*) from `$tbl[poll_item]` where `ref`='$no'");
		}
		$data = $itemsql->current();
        $itemsql->next();
		if($data == false){ unset($itemsql); return false; }
		$config = $pdo->assoc("select * from `$tbl[poll_config]` where `no`='$no'");
		$total_vote=$config[total_vote];
		if($data[total]) $percent=($data[total]/$total_vote)*100; else $percent=0;
		$data[per]=number_format($percent,2);
		$data[title]=stripslashes($data[title]);
		if($doPoll){
			if($config[sdate] <= $today && $config[fdate] >= $today){
				$radio="<input type=\"radio\" name=\"poll\" value=\"".$data[no]."\"> ";
				$data[title]=$radio.$data[title];
			}
		}
		return $data;
	}

	function pollComment(){
		global $tbl,$member,$admin,$poll,$pollcm_sql,$this_url, $pdo;
		if(!$poll) return;
		if(!$pollcm_sql){
			$pollcm_sql=$pdo->iterator("select * from `$tbl[poll_comment]` where `ref`='$poll[no]' order by `no`");
		}
        $cm = $pollcm_sql->current();
        $pollcm_sql->next();
		if($cm == false){ unset($pollcm_sql); return false; }
		$cm[content]=nl2br($cm[content]);
		$cm[reg_date]=date("Y-m-d H:i",$cm[reg_date]);
		$cm[del_link]="$this_url&comdelno=$cm[no]&rUrl=".urlencode($this_url);
		if(($cm[member_no] && $cm[member_no] == $member[no]) || $member[level] == 1 || $admin[no]) $cm[del_link] .= "\" onclick=\"return confirm('삭제하시겠습니까?');";
		return $cm;
	}

	common_header();

?>
<script type='text/javascript'>
function jsVote(frm) {
	var poll = $(':checked[name=poll]').val();
	if(!poll) {
		window.alert(_lang_pack.vote_select_item);
		return false;
	}
	frm.poll.value = poll;
	frm.submit();
}

function jsPollCom(frm) {
	if(mlv == 10) {
		if(!checkBlank(frm.name, _lang_pack.common_input_name)) return false;
		if(!checkBlank(frm.pwd, _lang_pack.common_input_pwd)) return false;
	}
	if(!checkBlank(frm.content, _lang_pack.common_input_content)) return false;
}
</script>
<?
	// 디자인 버전 점검 & 페이지 출력 (가장하단에위치)
	include_once $engine_dir."/_engine/common/skin_index.php";
?>
