<?PHP

// 단독 실행 불가
	if(!defined("_lib_inc")) exit();

	$no = numberOnly($_GET['no']);
	if(!$no) msg(__lang_common_error_required__,"/");

	if($config['auth_member'] == 2 && $member['level'] != 1) {
		memberOnly();
		$where = " and `member_no`='$member[no]'";
	}
	if($member['level'] > 1 && $cfg['mari_board_m_content'] == 'Y') {
		$where .= " and hidden!='Y'";
	}
	$sql="select * from `$mari_set[mari_board]` where `no`='$no' and `db`='$db' $where";
	$data=$pdo->assoc($sql);
	if(!$data['no']) msg(__lang_common_error_nodata__,"back");

	if($_GET['module'] == 'true') $_SESSION['bbs_rURL'] ='';
	$listURL = $_SESSION['bbs_rURL'];
	if(!$listURL) $listURL=$PHP_SELF.$db_que2;
	$auth=getDataAuth($data);
	if($data['secret']=='Y') {
		if($data['level'] > 0){ // 답글일 경우 부모의 회원번호와 비교
			$pa_mno=$pdo->row("select `member_no` from `$mari_set[mari_board]` where `ref`='$data[ref]' and `level`=0");
			if($member['no'] == $pa_mno) $auth=10;
		}
		if(!$auth) {
			msg(__lang_board_info_auth4__, "back");
		}
		elseif($auth==3) {
			if($_SESSION['pwd'.$data['no']]!="OK") {
				include $skin_path."secret.php";
				return;
			}
		}
	}
	elseif($data['secret']=="D") { // 시안확인 게시판
		if(!$auth) {
			if($_SESSION['spwd'.$data['no']]!="OK") {
				include $skin_path."s_secret.php";
				return;
			}
		}
	}

	$hidden_yn = ($data['hidden'] == 'Y') ? '[숨김]' : '';

// 조회수
    $log_qry = '';
	if ($config['hit_type'] == '1') { // 무조건 올림
		$log_qry = "update {$mari_set['mari_board']} set hit=hit+1 where no='$no'";
	}
	else if ($config['hit_type'] == '2') {
		if (!$_SESSION['cmp_hitted'] || !preg_match('/_'.$data['no'].'/',$_SESSION['cmp_hitted'])) {
			$_SESSION['cmp_hitted'] = $_SESSION['cmp_hitted'].'_'.$data['no'];
			$log_qry = "update {$mari_set['mari_board']} set hit=hit+1 where no='$no'";
		}
	}
    if ($log_qry) {
        if($cfg['use_log_scheduler'] == 'Y') {
            $pdo->query("insert into {$tbl['log_schedule']} (query, reg_date) values (?, now())", array($log_qry));
        } else {
            $pdo->query($log_qry);
        }
        $data['hit']++; // 페이지에 표시용
    }

	foreach($data as $key=>$val) {
		$data[$key]=stripslashes($val);
		$textbox=array('pwd','title','homepage','email');
		if(in_array($key,$textbox)) {
			$data[$key]=inputText($data[$key]);
		}
	}

	$data['name']=getWriterName($data);

// 파일
	for($ii=1; $ii<=4; $ii++) {
		$j=$ii*2;
		if($data["upfile".$ii]) {
			if (!$file_dir) $file_dir = getFileDir("board/$data[up_dir]");
			$mari_url2 = str_replace($root_url, $file_dir, $mari_url);

			$data["file_url".$ii]=$mari_url2.$data['up_dir'].$data["upfile".$ii];
			$data["file_path".$ii]=$root_dir."/board/".$data['up_dir'].$data["upfile".$ii];

			$data["file_link".$ii]="<a href=\"".$data["file_url".$ii]."\" target=\"blank\">";
			$data["upfile".$ii]=$data["file_link".$ii].$data["ori_upfile".$ii]."<a>";
		}
		else {
			$hidden_file[$j-2]="<!--";
			$hidden_file[$j-1]="//-->";
		}
	}

	if($_SESSION['browser_type'] == 'mobile' && $data['use_m_content'] == 'Y' && $data['m_content']) {
		$data['content'] = $data['m_content'];
	}

// html
	if($data['html'] != '3') {
		$data['content']=nl2br($data['content']);
	}
	if($data['html'] == '1') {
		$data['content']=autolink($data['content']);
	}

// 권한 - 버튼
	$link['reply']=$link['del']=$link['edit']="<a style='display:none;'>";
	if(getAuth("reply")>=0 && $config['use_reply']=="Y") {
		$link['reply']="<a href=\"javascript:mariExec('write@write@reply','','')\">";
	}

	if($auth) {
		if($auth==1 || $config['use_edit']!='N') $link['edit']="<a href=\"javascript:mariExec('write@write@edit','$data[no]','$auth')\">";
		if($auth==1 || $config['use_del']!='N'){ $link['del']="<a href=\"javascript:mariExec('write@del','$data[no]','$auth')\">"; $Delc="Y"; }
	}

	// 답글이 존재할 경우 삭제체크
	$rep_exist=0;
	if($Delc == "Y"){
		if($data['rep_no']) $rep_exist=$pdo->row("select count(*) from `$mari_set[mari_board]` where `no`!='$data[no]' and `rep_no` like '".$data['rep_no']."%'");
		if(($cfg['board_reply_del'] == "" || $cfg['board_reply_del'] == "N") && $rep_exist && $member['level'] != 1){
			$link['del']="<a href=\"javascript:\" onclick=\"alert('".__lang_board_error_rmRep__."');\">";
		}
	}

	// 리스트와 같이 보기일 경우 링크 덮힘 현상 보완
	$link['tmp_reply']=$link['reply'];
	$link['tmp_del']=$link['del'];
	$link['tmp_edit']=$link['edit'];

	$data['homepage']="<a href=\"$data[homepage]\" target=\"_blank\">$data[homepage]</a>";

	$board_data=$data;

	include $skin_path."view.php";

	if($config['use_comment']=="Y") {
		include $mari_path."view/comment.php";
	}

	if ($config['list_mode'] == '1' || $config['list_mode'] == '3') {
		include $mari_path."view/list.php";
	}

?>