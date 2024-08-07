<?PHP

	/* +----------------------------------------------------------------------------------------------+
	' |  접속로그 기록
	' +----------------------------------------------------------------------------------------------+*/

    use donatj\phpuseragentparser;

    if (preg_match('/^121\.254\.(156|159|238)\./', $_SERVER['REMOTE_ADDR']) == true) {
        return;
    }

	if(isset($cfg['count_log_use']) && $cfg['count_log_use'] == 'N') {
		return;
	}

	if(defined('__LOG_SCHEDULER__') == true) return;

    $ua_info = parse_user_agent();
    $os = $ua_info['platform'];
    $browser = $ua_info['browser'];

	if(!$_SERVER['HTTP_USER_AGENT']) {
		return;
	}

    if ($scfg->comp('log_unknown', 'Y') == false && (!$os || !$browser)) {
        return;
    }

	if(preg_match("@^http://$_SERVER[HTTP_HOST]@",$_SERVER['HTTP_REFERER'])) {
		return;
	}

	if($admin['no'] || preg_match("@$root_url/_manage@",$_SERVER['HTTP_REFERER'])) {
		return;
	}

	if($cfg['log_term']!=3) {
		$cfg['log_term']=2;
	}


	if($cfg['log_term']>1 && $cfg['log_term']==$_COOKIE['wm_log_counter'] && $_COOKIE['wm_log_counter']) {
		return;
	}
	if(!defined("_wisa_lib_included")) exit;



	if($_SERVER['HTTP_REFERER']) {
		$_SESSION['log_ref']=$_SERVER['HTTP_REFERER'];
	}

	$ip=$_SERVER['REMOTE_ADDR'];
	list($yy, $mm, $dd, $hh, $week) = explode(' ', date('Y n j G w',$now));

	// 오늘내일 카운터
	$nowYMD=date("Ymd",$now);
	$_today = $pdo->assoc("select * from {$tbl['log_today']} where no=1");
	if(substr($_today['today'], 0, 6) != date('Ym') || !isTable($tbl['log_count'])) {
		if(isTable($tbl['log_count']) == false) {
            $pdo->query("
                CREATE TABLE `{$tbl['log_count']}` (
                `no` int(11) not null auto_increment,
                `ip` varchar(30) not null,
                `id` varchar(30) not null,
                `time` int(11) not null,
                `yy` int(4) not null,
                `mm` int(2) not null,
                `dd` int(2) not null,
                `hh` int(2) not null,
                `week` int(1) not null,
                `os` varchar(50) not null,
                `browser` varchar(50) not null,
                `referer` text not null,
                `conversion` varchar(200) not null,
                `engine` varchar(16) not null,
                `keyword` varchar(64) not null,
                `browser_type` enum('pc','mobile') not null default 'pc',
                PRIMARY KEY  (`no`),
                KEY `keyword` (`keyword`)
                ) ENGINE=InnoDB DEFAULT CHARSET=utf8 AUTO_INCREMENT=1 ;"
            );
        }
    }
	if($_today) {
		if($_today['today']==$nowYMD) {
			$asql="`today_hit`=`today_hit`+1,`total`=`total`+1";
		}
		else {
			$asql="`today`='$nowYMD',`yesterday_hit`='$_today[today_hit]', `today_hit`='1'";
			if($_today['today_hit']>$_today['peak_hit']) $asql.=",`peak_hit`='$_today[today_hit]'";
		}
		$log_qry = "update {$tbl['log_today']} set $asql where `no`='1'";
		if($cfg['use_log_scheduler'] == 'Y') {
			$log_qry = addslashes($log_qry);
			$pdo->query("insert into {$tbl['log_schedule']} (query, reg_date) values ('$log_qry', now())");
		} else {
			$pdo->query($log_qry);
		}
	}
	else {
		$pdo->query("INSERT IGNORE INTO {$tbl['log_today']} ( `no` , `today` , `today_hit` , `yesterday_hit` ) VALUES ( '1','$nowYMD','1','0')");
	}

	$dlog = addslashes($_SERVER['HTTP_REFERER']);
	list($__engine, $__keyword) = getSearchQuery($dlog);
	$__engine = trim($__engine);
	$__keyword = trim($__keyword);
	$browser_type = $_SESSION['browser_type'] == 'mobile' ? 'mobile' : 'pc';

	if($cfg['log_file']=="Y") {
		// 파일로 저장 2007-03-02
		include_once $engine_dir."/_engine/include/file.lib.php";
		$count_dir=$dir['upload']."/".$dir['conut_log']."/".date("Yn",$now);
		$abs_count_dir=$root_dir."/".$count_dir;
		if(!is_dir($abs_count_dir)) {
			makeFulldir($count_dir);
		}
		$count_file=$abs_count_dir."/".date("j",$now).".log";
		$value="";
		if(is_file($count_file)) $value="\n";
		$value.="$ip||$member[member_id]||$now||$yy||$mm||$dd||$hh||$week||$os||$browser||$_SERVER[HTTP_REFERER]||$_SESSION[conversion]||$__engine||$__keyword";
		$cfp=fopen($count_file, "a");
		flock($cfp,LOCK_EX);
		fwrite($cfp, $value);
		flock($cfp,LOCK_UN);
		fclose($cfp);
	}
	else {
		$pdo->query("insert IGNORE into {$tbl['log_count']} values ('','$ip','$member[member_id]','$now','$yy','$mm','$dd','$hh','$week','$os','$browser','$dlog','$_SESSION[conversion]','$__engine','$__keyword', '$browser_type')");
	}

	// 일별 통계 저장
	if($pdo->row("select count(*) from {$tbl['log_day']} where yy='$yy' and mm='$mm' and dd='$dd'") ==0) {
		$pdo->query("insert IGNORE {$tbl['log_day']} (`yy`,`mm`,`dd`,`h$hh`,`week`,`hit`) values ('$yy','$mm','$dd','1','$week','1')");
	} else {
		$log_qry = "update {$tbl['log_day']} set `h$hh`=`h$hh`+1, `hit`=`hit`+1 where `yy`='$yy' and `mm`='$mm' and `dd`='$dd'";
		if($cfg['use_log_scheduler'] == 'Y') {
			$log_qry = addslashes($log_qry);
			$pdo->query("insert into {$tbl['log_schedule']} (query, reg_date) values ('$log_qry', now())");
		} else {
			$pdo->query($log_qry);
		}
	}

	// 리퍼러 로그 저장
    if ($scfg->comp('log_refer_check', 'Y') == false) {
        $scfg->import(array('log_refer_check' => 'Y'));
        $pdo->query("delete from {$tbl['log_referer']} where hit=1");
    }
	if($pdo->row("select count(*) from {$tbl['log_referer']} where log='$dlog'") > 0) {
		$log_qry = "update {$tbl['log_referer']} set hit=hit+1, time='$now' where log='$dlog'";
		if($cfg['use_log_scheduler'] == 'Y') {
			$log_qry = addslashes($log_qry);
			$pdo->query("insert into {$tbl['log_schedule']} (query, reg_date) values ('$log_qry', now())");
		} else {
			$pdo->query($log_qry);
		}
	} else {
		$pdo->query("insert IGNORE into {$tbl['log_referer']} (`log`,`hit`,`time`) values ('$dlog','1','$now')");
	}

	// 설정 따른 쿠키 생성
	$expire=($cfg['log_term']==3) ? $now+86400 : 0;

	$rdom = preg_replace("/^(http:\/\/)?(www\.)?/", "", $_SERVER['HTTP_HOST']);
	@setcookie('wm_log_counter',$cfg['log_term'],$expire,"/",".".$rdom);

?>