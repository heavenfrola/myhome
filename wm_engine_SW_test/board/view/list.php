<?PHP

// 단독 실행 불가
	if(!defined("_lib_inc")) exit();

	if(empty($_REQUEST['single_module']) == true) {
		$_SESSION['bbs_rURL'] = getURL();
	}

// 쿼리 작성
	$sql="";
	$where=$board_add_where; // 추가 조건 가능 2006-11-23
    $where_n = '';
	//$vs=1;

	if($member['level'] > 1 && $cfg['mari_board_m_content'] == 'Y') {
		$where .= " and hidden!='Y'";
        $where_n .= " and hidden!='Y'";
	}

	$search = addslashes(trim($_GET['search']));
	$search2 = ($search == "name") ? addslashes(trim($cfg['writer_name'])) : $search;
	$search_str = strip_tags(trim($_GET['search_str']));
	if($search && $search_str) {
		$old_search_str=inputText($search_str);
		$search = str_replace('`', '', addslashes(strip_tags($search)));
		$_search_str = parseParam($search_str);
		if($search2 == "nickname") {
			$nickno = $pdo->row("select group_concat(no) from ".$tbl['member']." where nick like '%$_search_str%'");
			$where .= " and member_no in ($nickno)";
		} else {
			$where .= " and $search2 like '%$_search_str%'";
		}
	}

	// temp 폼 검색
	for($tmpn = 1; $tmpn <= 3; $tmpn++) {
		$searchtmp = trim($_GET['searchtmp'.$tmpn]);
		${'searchtmp'.$tmpn} = $searchtmp;
		if($searchtmp) {
			$_searchtmp = addslashes($searchtmp);
			$where .= " and temp{$tmpn} like '%$_searchtmp%'";
		}
	}
	unset($searchtmp, $_searchtmp);

	if($Mboard_zata == "Y" && $db == "design"){
		if($zataSt != "") $where.=" and `zata_ing`='$zataSt'";
	}

	if($config['use_cate']=="Y") {
		if($cate) {
			$where.=" and `cate`='$cate'";
		}

		$_mari_cate=array();
		$c_res=$pdo->iterator("select * from `mari_cate` where `db`='$db'");
        foreach ($c_res as $c_row) {
			$_mari_cate[$c_row['no']]=$c_row;
		}
	}

	if($config['use_sort'] == 'Y') $_sort[0] = 'reg_date desc, no desc';
	if($sort=="") $sort=0;

	if(!$bs_module) {
		$sql_notice="select * from `$mari_set[mari_board]` where `db`='$db' and `notice`='Y' $where_n order by ".$_sort[$sort];
		$res_notice=$pdo->iterator($sql_notice);
	}

	if($tcc) {
		$where.=" and `total_comment`>1";
	}

	if($config['auth_member'] == 2 && $member['level'] != 1) {
		memberOnly();
		$where.=" and `member_no`='$member[no]'";
	}

    if ($config['list_mode'] == '3' && $data['ref'] && $mari_mode == 'view@view') {
        $where .= " and ref='{$data['ref']}'";
    }

    if (fieldExist($mari_set['mari_board'], 'start_date') == true) {
        $_now = date('Y-m-d H:i:s');
        $where .= " and (n_status!='Hidden' or start_date='0000-00-00 00:00:00' or start_date <= '$_now')";
    }

	$sql="select * from `$mari_set[mari_board]` where `db`='$db' and `notice`<>'Y' $where order by ".$_sort[$sort];

	$par = '';
	foreach($_GET as $skey => $sval) {
		if($skey == 'page' || $skey == 'x' || $skey == 'y' || !trim($sval)) continue;
		$par .= "&$skey=".urlencode($sval);
	}

// 페이징 설정
	include_once $engine_dir."/_engine/include/paging.php";

	if($_GET['module_page'] > 0) $_GET['page'] = $_GET['module_page'];
	$page = numberOnly($_GET['page']);
	if($page<=1) $page=1;
	if($irow) {
		$row=$irow;
	}
	else {
		$row=$config['page_row'];
	}

	$block=$config['page_block'];
	$QueryString=$par;

	$NumTotalRec=$pdo->row("select count(*) from {$mari_set['mari_board']} where db='$db' and notice!='Y' ".$where);
	$PagingInstance = new Paging($NumTotalRec, $page, $row, $block);
	$PagingInstance->addQueryString($QueryString);
	$PagingResult=$PagingInstance->result($pg_dsn);
	$sql.=$PagingResult['LimitQuery'];

	$pg_res=$PagingResult['PageLink'];
	if($vs) echo($sql);
	$res=$pdo->iterator($sql);
	$idx=$NumTotalRec-($row*($page-1));

    $nextPage_link = ($PagingInstance->end > $page)
        ? $_SERVER['SCRIPT_NAME'].makeQueryString(true, 'page').'&page='.($page+1) : '';

// 목록 상단
	if(!$bs_module) include $skin_path."list_top.php";
	if($NumTotalRec==0 && $res_notice->rowCount() == 0) {
		$pg_res="";
	}
	else {
	// 목록 루프
		$sno=$NumTotalRec-($row*($page-1));
        if (defined('__MODULE_LOADER__') == true) {
            $sno = $NumTotalRec;
        }
		$cols=$_cols=0;
		$view_auth=getAuth("view@view");
        $_tmp = '';

		if($config['gallery_cols']) echo "<tr class=\"gal_row\">";

		if(!$bs_module) $use_list_comment = checkCodeUsed('댓글');

		$_replace_datavals[$_file_name]['board_list_vals'] .= "글번호:sno;글분류:cate_str;아이콘:icons;새글아이콘:icon1;첨부아이콘:icon2;비밀아이콘:icon3;글제목:title;글제목2:title2;링크:link;댓글수:total_comment_num;작성자:name;작성일:reg_date;조회수:hit;입금일:temp1;첨부이미지1:upfile1_img;첨부이미지1(링크포함):upfile1_img_link;첨부이미지2:upfile2_img;첨부이미지2(링크포함):upfile2_img_link;첨부이미지3:upfile3_img;첨부이미지3(링크포함):upfile3_img_link;첨부이미지4:upfile4_img;첨부이미지4(링크포함):upfile4_img_link;글내용:content;글코드:no;디비:db;글내용(비밀글잠금):content2;링크1:link1;댓글:comment;글수정링크태그:edit;글삭제링크태그:del;숨김글표시:hidden_yn;시작일:start_day;종료일:end_day;시작일시:start_date;종료일시:end_date;";
		for($i=1; $i <= $cfg['board_add_temp']; $i++) {
			$_replace_datavals[$_file_name]['board_list_vals'] .= ";추가항목{$i}:temp{$i}";
		}
		if ($res_notice instanceof \Traversable) {
			foreach ($res_notice as $data) {
				$cols++;
				dataParse();
				if(function_exists('dataParse2')) $data = dataParse2($data);
				$auth=getDataAuth($data);
				// 루프 스킨
				if($cfg['design_version'] == "V3" || defined('__MODULE_LOADER__') == true){
					if($_nline == ""){
						$_nline=getModuleContent($skin_path."notice.php", 1);
					}
					$_tmp .= lineValues("board_list_vals", $_nline, $data);
				}else include $skin_path."notice.php";
				// 갤러리 타입일 경우 <TR>
				if($config['gallery_cols'] && ($cols%$config['gallery_cols'])==0 && $cols!=$NumTotalRec) {
					$_tmp .= "</tr><tr>";
				}
			}
		}

        foreach ($res as $data) {
			$cols++;
			$_cols++; // 갤러리용(공지랑 섞이지 않도록 분리) - Han

			if(function_exists('dataParse2')) $data = dataParse2($data);

			/*if($use_list_comment && $config['use_comment']=="Y") {
				$_mari_mode = $mari_mode;
				$data['comment']="";
				ob_start();
				echo "<div id=\"mari_comment_ajax_$data[no]\">\n";
				$mari_mode = "view@comment_exec";
				$no = $data['no'];
				include $root_dir."/board/index.php";
				echo "\n</div>";
				echo "<script type=\"text/javascript\">$('#mari_comment_ajax_$data[no] [name=no]').val($data[no]);</script>";
				$data['comment'] = ob_get_contents();
				ob_end_clean();
				$mari_mode = $_mari_mode;
			}*/

			if($_SESSION['browser_type'] == 'mobile' && $data['use_m_content'] == 'Y' && $data['m_content']) {
				$data['content'] = $data['m_content'];
			}

			dataParse();
			$auth=getDataAuth($data);
			// 루프 스킨
			if($cfg['design_version'] == "V3" || defined('__MODULE_LOADER__') == true){
				if($_line == ""){
					$_line=getModuleContent($skin_path."list_loop.php", 1);
				}
				$_tmp .= lineValues("board_list_vals", $_line, $data, "", 2);
			}else include $skin_path."list_loop.php";
			// 갤러리 타입일 경우 <TR>
			if($config['gallery_cols'] && ($_cols%$config['gallery_cols'])==0 && $_cols!=$NumTotalRec&&$_SESSION['browser_type']!='mobile') {
				$_tmp .= "</tr><tr>";
			}
			$sno--;
		}
		// 갤러리 타입일 경우 - 부족한 셀 채우기
		if($config['gallery_cols']) {
			while(($_cols%$config['gallery_cols'])!=0&&$_SESSION['browser_type']!='mobile') {
				$_cols++;
				$_tmp .= "<td>&nbsp;</td>";
			}
		}
		if($config['gallery_cols']) $_tmp .= "</tr>";
	}
    echo $_tmp;
    $_replace_code[$_file_name]['board_list'] = $_tmp;

// 목록 하단
	if(!$bs_module) include $skin_path."list_bottom.php";

	function dataParse() {
		global $data,$no,$skin_url,$config,$link,$new_icon,$view_auth,$sno,$now,$mari_set,$member,$Mboard_zata,$db,$DesignStateN,$_mari_cate,$root_dir,$root_url,$_board_skin, $pdo;
		// 제목
		$data['otitle'] = stripslashes($data['title']);
		if($config['cut_title']) {
			$data['title'] = preg_replace('/<[^\p{Hangul}]+>/u', '', $data['title']);
			$data['title'] = mb_strimwidth($data['otitle'], 0, $config['cut_title']);
		}
		// 현재글
		if($no && $data['no']==$no) $data['loop_class']="now_row";
		elseif($sno%2==0) $data['loop_class']="normal_row2";
		else $data['loop_class']="normal_row";
		// 이름
		$data['name']=getWriterName($data);

		// 답글일 경우 뒤로 밀리게
		if($data['level'] > 0) {
			$wid = $data['level']*13;
			$RE = "[RE] ";
			if(is_file("$skin_url/imgage/icon_reply.gif")) $RE_src = "$skin_url/imgage/icon_reply.gif";
			if($GLOBALS['_reply_img']) $RE_src = $GLOBALS['_reply_img'];
			if($RE_src) $RE = "<img src=\"".$GLOBALS['_reply_img']."\" border=\"0\">";
			$t_head = '<span class="spacer" style="display:inline-block; width:'.$wid.'px"></span>'.$RE;
		}
		else {
			$t_head = '';
		}
		$data['title']=$t_head.$data['title'];
		$data['title2']=$data['title']; // 2010-02-11 : 댓글, 링크 미포함 제목
		$data['cate_str']=($config['use_cate'] == "Y" && $data['cate']) ? $_mari_cate[$data['cate']]['name'] : "";

		$data['total_comment_num']=$data['total_comment'] ? $data['total_comment'] : 0;
		// 코멘트수
		if($data['total_comment']>0) $data['total_comment'] = '['.$data['total_comment'].']';
		else $data['total_comment']="";
		// 권한
		$auth=getDataAuth($data);
		if($config['use_view']=="N") { // 블로그 타입
			$data['reply']=$data['del']=$data['edit']="<a href=\"#\" style=\"display: none; \">";
			if(getAuth("reply")>=0 && $config['use_reply']=="Y") {
				$data['reply']="<a href=\"javascript:mariExec('write@write@reply','$data[no]')\">";
			}
			if($auth) {
				if($auth==1 || $config['use_edit']!='N') $data['edit']="<a href=\"javascript:mariExec('write@write@edit','$data[no]','$auth')\">";
				if($auth==1 || $config['use_del']!='N') $data['del']="<a href=\"javascript:mariExec('write@del','$data[no]','$auth')\">";
			}
		}
		// 글 아이콘
		$new_icon=getDataIcon($data);
		// 날짜
		$data['oreg_date']=$data['reg_date'];
		$data['reg_date']=$config['date_type_list'] ? parseDateType($config['date_type_list'], $data['reg_date']) : date("Y/m/d",$data['reg_date']);
		$data['icons']=$new_icon;

		if(($now-$data['oreg_date']) <= $mari_set['new_date']) $data['icon1'] = "<img src='$skin_url/img/i_new.gif' align='absmiddle' />";
		if($data['upfile1'] != '' || $data['upfile2'] != '' || $data['upfile3'] != '' || $data['upfile4'] != '') $data['icon2'] = "<img src='$skin_url/img/i_file.gif' align='absmiddle' />";
		if($data['secret'] == 'Y') $data['icon3'] = "<img src='$skin_url/img/i_secret.gif' align='absmiddle' />";

		// 비밀글 보기 가능
		$Vlink="";
		if($view_auth && ($data['secret']!="Y" || $auth)) {
			if($Mboard_zata == "Y" && $db == "design"){ // 2006-12-18 : 자타시안확정 팝업 - Han
				$data['title'] = "[".DesignState($data['zata_ing'])."] ".$data['title'];
				$data['title']="<a href=\"javascript:designPop('$data[no]');\">$data[title]</a>";
				if($member['level'] == 1) $data['title'].= " <a href=\"javascript:mariExec('write@write@edit','$data[no]')\"><font color=#333399>(Edit)</font></a>";
			}else $Vlink="Y";
		}else{ // 2007-01-25 : 답글일 경우 부모의 회원번호가 일치하는지 검사한다 - Han
			if($data['secret'] == "Y" && $data['level'] > 0){
				$pa_mno=$pdo->row("select `member_no` from `$mari_set[mari_board]` where `ref`='$data[ref]' and `level`=0");
				if($member['no'] == $pa_mno) $Vlink="Y";
			}
		}
		$data['link']="javascript:mariExec('view@view','$data[no]');";
		$data['title']=($Vlink == "Y") ? "<a href=\"".$data['link']."\">$data[title]</a>" : $data['title'];

		$data['content']=stripslashes($data['content']);
		$data['content2']=(($auth==0 || $auth==3) && $data['secret']=='Y') ? "비밀글입니다." : $data['content'] ; //2011-06-07 비밀글은 보이지 않도록 한 글내용 Jung
		$data['sno']=$sno;
		$data['title']=$data['title']." ".$data['total_comment'];

		if($_board_skin['board_list_content_cut']){
			$data['content']=@strip_tags($data['content']);
			$data['content']=cutStr($data['content'], $_board_skin['board_list_content_cut']);
		}

		$_imgw=$_board_skin['board_list_imgw'] ? $_board_skin['board_list_imgw'] : 130;
		$_imgh=$_board_skin['board_list_imgh'] ? $_board_skin['board_list_imgh'] : 130;

		for($ii = 1; $ii <= 4; $ii++) {

			if($data['upfile'.$ii] && $data['up_dir'] && preg_match("/\.(gif|jpg|jpeg|bmp|png)$/i", $data['upfile'.$ii])){
				$file_url = getFileDir('board/'.$data['up_dir']);
				if(!$data['upfile'.$ii.'_w']) {
					if(!fsConFolder('board/'.$data['up_dir'])) {
						list($w, $h)=@getimagesize($root_dir."/board/".$data['up_dir']."/".$data['upfile1']);
					} else {
						$w = $_imgw;
						$h = $_imgh;
					}
				} else {
					$w = $data['upfile'.$ii.'_w'];
					$h = $data['upfile'.$ii.'_h'];
				}
				list($_w, $h, $_size_str)=setImageSize($w, $h, $_imgw, $_imgh);
				if(!$_w ||  !$h) $_size_str = '';
				$data['upfile'.$ii.'_img']="<img src=\"".$file_url."/board/".$data['up_dir']."/".$data['upfile'.$ii]."\" ".$_size_str.">";
				$data['upfile'.$ii.'_img_link']="<a href=\"".$data['link']."\">".$data['upfile'.$ii.'_img']."</a>";
			}
		}

	// html
		if($data['html']!=3) {
			$data['content']=nl2br($data['content']);
		}
		if($data['html']==1) {
			$data['content']=autolink($data['content']);
		}
		$data['hidden_yn'] = ($data['hidden'] == 'Y') ? '[숨김]' : '';

		if($data['start_date'] == '0000-00-00 00:00:00') $data['start_date'] = '';
		if($data['end_date'] == '0000-00-00 00:00:00') $data['end_date'] = '';

		if($data['start_date']) $data['start_day'] = parseDateType($config['date_type_list'], strtotime($data['start_date']));
		if($data['end_date']) $data['end_day'] = parseDateType($config['date_type_list'], strtotime($data['end_date']));
	}

	function getDataIcon($data,$mode="") {
		global $mari_set,$now,$skin_url,$test;
		$term=$now-$data['reg_date'];
		if($data['secret']=="Y") {
			$icon_file="secret";
			$icon="<img src=\"$skin_url"."img/i_$icon_file.gif\" align=\"absmiddle\">";
			if($term<=$mari_set['new_date']) {
				$icon_file="new";
				$icon.="<img src=\"$skin_url"."img/i_$icon_file.gif\" align=\"absmiddle\">";
			}
		}
		else {
			if($term<=$mari_set['new_date']) {
				$icon_file="new";
				$icon="<img src=\"$skin_url"."img/i_$icon_file.gif\" align=\"absmiddle\">";
			}
			else {
				$icon_file="doc";
				$icon="<img src=\"$skin_url"."img/i_$icon_file.gif\" align=\"absmiddle\">";
			}
		}
		return $icon;
	}
?>