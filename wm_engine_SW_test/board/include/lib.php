<?PHP

	if(defined("_lib_inc")) return;
	else define("_lib_inc",true);

	include_once $engine_dir."/_engine/include/common.lib.php";
	include_once $engine_dir."/_engine/include/shop_detail.lib.php";

	$mari_set['pwd']=$cfg['pwd'];

	if(!$pg_dsn) $pg_dsn="wonhwa";
	$mari_set['err_code']="N";
	if(!$mari_set['new_date']) {
		$mari_set['new_date']=172800; // 최근글 기준
	}

	$mari_set['mng_level']=1;
	if(!$mari_set['name']) {
		$mari_set['name']="name";
	}


	// 데이터베이스
	$mari_set['mari_board'] = $tbl['mari_board'];
	$mari_set['mari_comment'] = $tbl['mari_comment'];
	$mari_set['mari_config'] = $tbl['mari_config'];
	$mari_set['mari_cate'] = $tbl['mari_cate'];
	$mari_set['member']=$tbl['member'];
	if(!$member['no']) $member=get_info($mari_set['member'],"no",$_SESSION["member_no"]);
	if(!$member['level']) $member['level']=10;


	// 정렬
	$_sort[0]="`ref` desc, `step` asc";
	$_sort[1]="`hit` desc";
	$_sort[2]="`vote_avg` desc";


	// 권한
	$_auth["view@list"]="list";
	$_auth["view@view"]="view";
	$_auth["view@secret_exec"]="view";
	$_auth["view@vote_exec"]="view";
	$_auth["write@write"]="write";
	$_auth["write@write_exec"]="write";
	$_auth["write@write@edit"]="write";
	$_auth["write@write_exec@edit"] = 'write';
	$_auth["write@del"]="write";
	$_auth["write@del_exec"]="write";
	$_auth["write@write@reply"]="reply";
	$_auth["write@write_exec@reply"]="reply";
	$_auth["write@comment_exec"]="comment";
	$_auth["write@comment_del"]="comment";
	$_auth["write@comment_del_exec"]="comment";
	$_auth["write@comment_exec@comment"]="comment";

	function normalContent($content,$br="",$cut="", $ShortenStr="...") {
		$content=stripslashes($content);
		if($cut) $content=cutStr($content, $cut, $ShortenStr);
		$content=del_html($content);
		if($br) $content=nl2br($content); // BR
		return $content;
	}

	function getExtName($upfile_name) {
		$full_filename = explode("\.", $upfile_name);
		$s_point=count($full_filename)-1;
		$file_extention = $full_filename[$s_point];
		return $file_extention;
	}

	function debug($sql,$cnt="") {
		echo($sql);
		if(!$cnt) exit();
	}

	function getFixedSize($upfile,$fixedsize) {
		$imginfo=@GetImageSize($upfile);
		$width=$imginfo[0];
		if($width>$fixedsize) {
			$width=$fixedsize;
		}
		return $width;
	}

	function checkDB($db) {
		global $config, $mari_set, $cate, $cate_info, $_board_cate, $pdo;
		if(!$db) msg(__lang_board_error_noDB__, '/', 'parent');

		$config=get_info($mari_set[mari_config],"db",$db); // 게시판 정보
		if($GLOBALS[mng_preview]){
			if(function_exists("mngPreviewConfig")) mngPreviewConfig();
			return;
		}
		if(!$config[no]) msg(__lang_board_error_dbNotExist__, '/', 'parent');

		if($cate) {
			if($config[use_cate]=="N") msg(__lang_board_error_cateNotUse__, '/', 'parent');
			$cate_info = $pdo->assoc("select * from `$mari_set[mari_cate]` where `no`='$cate' and `db`='$db'"); // 카테고리
			if(!$cate_info[no]) msg(__lang_board_error_cateNotExist__, '/', 'parent');
		}

		if($config[use_cate]=="Y") {
			$res = $pdo->iterator("select * from `$mari_set[mari_cate]` where `db`='$db'");
            foreach ($res as $data) {
				$_board_cate[$data[no]]=$data;
			}
		}
	}

	$han_auth['view'] = __lang_board_info_auth1__;
	$han_auth['list'] = __lang_board_info_auth2__;
	$han_auth['write'] = __lang_board_info_auth3__;

	// 모드별 권한 체크
	function getAuth($mode,$alert="") {
		global $member,$config,$han_auth;
		$auth_level=$config["auth_".$mode];

		$res=$auth_level-$member[level]; // 권한이 있을경우 0 보다 같거나 큼

		if($han_auth[$mode]) {
			$ems=$han_auth[$mode]." ";
		}
		if(!$member[no]) {
			$ems2=true;
		}

		if($alert && $res<0) {
			if($ems2) {
				memberOnly(1, "");
			}
			else {
				msg($ems, 'back', 'parent');
			}
		}
		return $res;
	}

	function getDataAuth($data,$after="") {
		global $member,$mari_set;
		if($member[level]==$mari_set[mng_level]) $res=1;
		elseif(!$data[member_no]) $res=3;
		elseif($member[no] && $data[member_no] && $data[member_no]==$member[no] && $data[member_id]==$member[member_id]) $res=2;
		elseif(!$member[no] && $data[level] > 0 && $data[secret] == "Y") $res=3;
		else $res=0;
		if($after && !$res) msg(__lang_common_error_noperm__, '/', 'parent');
		return $res;
	}

	function dellAllFile($mydir) {
		$dir = opendir($mydir);
		while((false!==($file=readdir($dir))))
			if($file!="." and $file !="..")
				unlink($mydir.'/'.$file);
		closedir($dir);
	}

	function getWriterName($data) {
		$r = getBoardName($data['db'], $data);

		return $r;
	}

	function checkWriteLimit($mode,$after="") {
		global $mari_set,$config,$cfg,$now,$member, $pdo;
		if($member[level]<=$mari_set[mng_level]) {
			return true;
		}
		$date=date("Y-m-d",$now);

		if($cfg['board_day_'.$mode]>0) {
			$total1=$pdo->row("select sum(`$mode`) from `mari_board_day` where `date`='$date' and  `member_no`='$member[no]' and  `member_id`='$member[member_id]'");
			if($total1>=$cfg['board_day_'.$mode]) {
				if($after) {
					if($mode=="comment") msg(sprintf(__lang_board_error_dayLimit2__, $cfg['board_day_'.$mode]), 'back');
					else  msg(sprintf(__lang_board_error_dayLimit1__, $cfg['board_day_'.$mode]), 'back');
				}
				return false;
			}
		}

		if($config['day_'.$mode]>0) {
			$total2=$pdo->row("select `$mode` from `mari_board_day` where `date`='$date' and  `member_no`='$member[no]' and `db`='$config[db]' and  `member_id`='$member[member_id]'");
			if($total2>=$config['day_'.$mode]) {
				if($after) msg(sprintf(__lang_board_error_dayLimit1__, $config['day_'.$mode]), 'back');
				return false;
			}
		}
		return true;
	}

	function checkWriteInput($mode) {
		global $mari_set,$config,$cfg,$now,$member, $pdo;
		if($member[level]<=$mari_set[mng_level] || !$member[no]) {
			return;
		}
		$date=date("Y-m-d",$now);

		$pdo->query("delete from `mari_board_day` where `date`!='$date'");
		$data = $pdo->assoc("select * from `mari_board_day` where `date`='$date' and  `member_no`='$member[no]' and `db`='$config[db]' and  `member_id`='$member[member_id]'"); // 2007-02-15 memebr_id

		if($data[no]) {
			$sql="update `mari_board_day` set `$mode`=`$mode`+1 where `no`='$data[no]'";
		}
		else {
			$write=$comment=0;
			${$mode}=1;
			$sql="INSERT INTO `mari_board_day` ( `member_no` , `member_id` , `db` , `date` , `write` , `comment` ) VALUES ( '$member[no]', '$member[member_id]','$config[db]','$date','$write','$comment')";
		}
		$pdo->query($sql);
	}

	include_once $engine_dir."/_engine/include/milage.lib.php";
	function putBBSPoint($mode) {
		global $mari_set,$config,$cfg,$now,$member;
		if(!$member[no]) {
			return;
		}
		$point=$config['point_'.$mode];
		if($mode=="write") $pt_no=2;
		else $pt_no=3;
	}

	function edtImgSize($content,$width,$onClick="window.open(this.src)") {
		global $skin_url;
		$content=preg_replace("/<img /i","<img onClick=\"$onClick\" style=\"cursor:pointer\" onload=\"seImgSize(this,$width)\" ",$content);
		return $content;
	}

	function neko_check( $margin = 1) { // 주어진 일수 이전에 작성된 Unlock 게시물 삭제
		global $tbl, $dir, $root_dir, $now;
		return;
	}

	function neko_lock($neko_id, $mode = "Y") { // 게시물 저장시 사용한 이미지에 Locking
        global $pdo;

		$pdo->query("update {$GLOBALS['tbl']['neko']} set `lock`=:mode where neko_id=:neko_id", array(
            ':mode' => $mode,
            ':neko_id' => $neko_id,
        ));
	}

	function neko_imgs($neko_id) { // 업로드한 이미지 정보를 가져옴
        global $pdo;

		$idx = 0;
		$nFiles = array();

		$file_dir = getFileDir("_data/neko");
		$res = $pdo->iterator("select * from {$GLOBALS['tbl']['neko']} where `neko_id` = '$neko_id' and `hidden` = 'N' order by `no` asc");
        foreach ($res as $data) {
            $nFiles[$idx]['path'] = $file_dir.'/'.$data['updir'].'/'.$data['filename'];
            $nFiles[$idx]['width'] = $data['width'];
            $nFiles[$idx]['height'] = $data['height'];
            $nFiles[$idx]['size'] = $data['size'];
            $nFiles[$idx]['data'] = $data;
            $idx++;
		}

		$nFiles['cnt'] = $idx;

		return $nFiles;
	}

?>