<?PHP

	/* +----------------------------------------------------------------------------------------------+
	' |  구 mysql 호환 라이브러리
	' +----------------------------------------------------------------------------------------------+*/

    if(function_exists('mysql_error') == false) {
        function mysql_error($idenifier = null) {
            global $pdo;
            return $pdo->getError();
        }
    }

	function dbCon() {
		global $connect, $con_info;
		if(!$connect) $connect = @mysql_connect($con_info[1], $con_info[2], $con_info[3]);

		@mysql_select_db($con_info[4], $connect) or idc_alert(2);
		@mysql_query('set names '.str_replace('-', '', _BASE_CHARSET_), $connect);
		@mysql_query('set character_set_connection='.str_replace('-', '', _BASE_CHARSET_), $connect);
	}

	function idc_alert($n) {
		global $root_dir, $cfg, $root_url;
		include_once $root_dir."/_config/config.php";

		if(!$_COOKIE['wm_db_error']) {
			setcookie('wm_db_error',1,time()+60*10,'/');
			$db_report=1;
		}

		exit("Database Error - $n");
	}

	function close($h="") {
		global $pdo, $_inc, $admin, $cfg, $nvcpa, $tbl, $root_url, $scfg;

		if($_GET['striplayout'] || $_GET['stripheader']) return;

		if($h && defined('_wisa_manage_edit_') == false) {
			if($cfg['ace_counter_gcode'] && !strchr($_SERVER['SCRIPT_NAME'], '/_manage/')) { // acecounter wisa ver.
				if($cfg['ace_counter_Ver'] == 2) {
					include_once $GLOBALS['engine_dir'].'/_engine/log/acecounter.inc.php';
				} else {
					include_once $GLOBALS['engine_dir'].'/_engine/log.acecounter/acecounter_common.js.php';
				}
			}

			if($cfg['criteo_use'] == '1' && $cfg['criteo_P']) { // criteo
				include_once $GLOBALS['engine_dir'].'/_engine/log/criteo.inc.php';
			}

			if($cfg['recopick_use'] == '1' && $cfg['recopick_id'] && $cfg['recopick_url']) { // recopick
				include_once $GLOBALS['engine_dir'].'/_engine/log/recopick.inc.php';
			}

			if($nvcpa) { // naver cpa
				include_once $GLOBALS['engine_dir'].'/_engine/log/naverCPA.js.php';
			}

			if($cfg['logger_smartMD_id'] && $cfg['logger_smartMD_sid']) {
				include_once $GLOBALS['engine_dir'].'/_engine/log/ad_logger_smartMD.inc.php';
			}

			if($cfg['logger_heatmap_HM_U']) {
				include_once $GLOBALS['engine_dir'].'/_engine/log/ad_heatmap.inc.php';
			}

			if($cfg['crema_app_id'] && $cfg['crema_secret']) { // crema
				include_once $GLOBALS['engine_dir'].'/_engine/log/crema.inc.php';
			}

			if($cfg['use_ga'] == 'Y' && $cfg['ga_code'] && $cfg['use_ga_enhanced_ec']!="Y") { // google analytics
				include_once $GLOBALS['engine_dir'].'/_engine/log/google_Analytics.inc.php';
			}

            if ($cfg['use_ga'] == '4') {
                include_once $GLOBALS['engine_dir'].'/_engine/log/google_Analytics4.inc.php';
            }

			if($scfg->comp('use_google_ads', 'Y') == true && $scfg->comp('google_ads_id')) { // 구글 애즈워드
                include_once __ENGINE_DIR__.'/_engine/log/google_ads.inc.php';
			}

			if($cfg['use_channel_plugin'] == 'Y' && $cfg['channel_plugin_id']) {
				include_once $GLOBALS['engine_dir'].'/_engine/log/channel.inc.php';
			}

			if($cfg['use_easemob_plugin'] == 'Y' && $cfg['easemob_plugin_id']) {
				include_once $GLOBALS['engine_dir'].'/_engine/log/easemob.inc.php';
			}
			if($cfg['use_happytalk'] == 'Y' && $cfg['happytalk_site_id']) {
				include_once $GLOBALS['engine_dir'].'/_engine/log/happytalk.inc.php';
			}

            if ($scfg->comp('use_clarity', 'Y') == true && $scfg->comp('clarity_code') == true) { // 클레어리티
                include_once $GLOBALS['engine_dir'].'/_engine/log/clarity.inc.php';
            }

            if ($scfg->comp('use_kcb', 'Y')) {
                include_once __ENGINE_DIR__ . '/_engine/member/kcb/form.php';
            }

			$ires = $pdo->query("select * from $tbl[config] where name like 'relation_channel%' and value!='' order by name asc ");
			$channel_cnt = $pdo->row("select count(*) from $tbl[config] where name like 'relation_channel%' and value!=''");
			if($channel_cnt > 0) {
				echo("<span itemscope=\"\" itemtype=\"https://schema.org/Organization\">\n");
					echo("<link itemprop=\"url\" href=\"$root_url\">\n");
                    foreach ($ires as $data) {
						echo("<a itemprop=\"sameAs\" href=\"$data[value]\"></a>\n");
					}
				echo("</span>\n");
			}
			echo("</body>\n</html>");
		}

		unset($GLOBALS['cfg'], $GLOBALS['_use'], $GLOBALS['tbl'], $GLOBALS['dir'], $GLOBALS['log_instance']);
	}

	function get_info($tbl, $col, $val) {
		global $pdo;

		return $pdo->assoc("select * from `$tbl` where `$col`=:val", array(
            ':val' => $val
        ));
	}

	function sql_query($sql) {
		global $connect, $pdo;

        $split = explode(' ', strtolower(trim($sql)));
        if($split[0] != 'select') {
            return $pdo->query($sql);
        }

		if(!$connect) {
            if(function_exists('mysql_connect') == true) {
                dbCon();
                global $now, $admin, $member;
                if(defined('_wisa_manage_edit_') == true) {
                    sql_query("SET @runid='$now';");
                    sql_query("SET @admin_id='{$admin['admin_id']}';");
                    sql_query("SET @member_id='';");
                } else {
                    sql_query("SET @admin_id='';");
                    sql_query("SET @member_id='{$member['member_id']}';");
                }
            } else {
                exit('mysql_query not support.');
            }
        }

		return mysql_query($sql, $connect);
	}

	function sql_assoc($sql){
        global $pdo;
        return $pdo->assoc($sql);
	}

	function sql_fetch($sql){
        global $pdo;
        return $pdo->assoc($sql);
	}

	function sql_row($sql){
        global $pdo;
        return $pdo->row($sql);
	}

	function sql_password($passwd){
		global $cfg, $engine_dir, $amember, $pdo;

		if(defined('_wisa_manage_edit_') && $cfg['pwd_db'] == 'Y' && !$amember['no']) {
			return $passwd;
		}

		if($cfg['pwd'] == 'Y') {
			if($cfg['pwd_md5'] == 'Y') {
                $row = md5($passwd);
            } elseif ($cfg['pwd_db'] == 'Y') {
				$row = $pdo->row("SELECT OLD_PASSWORD(:passwd)", array(
                    ':passwd' => $passwd
                ));
			} elseif ($cfg['pwd_md5_sha256'] == 'Y') {
				$row = hash('sha256', md5($passwd));
			} else {
				$row = hash('sha256', $passwd);
			}
			return $row;
		} else {
			return $passwd;
		}
	}

	function common_header() {
		global $cfg, $root_url, $now, $member, $this_url, $engine_url, $engine_dir, $design, $cpii, $_click_prd, $admin, $nvcpa, $prd, $tbl, $pdo;

		if(defined("_common_header")) return;
		define("_common_header",true);

		if($_GET['stripheader']) return;

		$ssl_host = $cfg['ssl_host'];

		if(($member['level'] == 1 && $cfg['mng_secutiry_block'] == "Y")){
			$cfg['secutiry_drag'] = 'N';
			$cfg['secutiry_click'] = 'N';
		}

		$dtd = '<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.0 Transitional//EN">';
		if($cfg['frontDTD']) {
			$dtd = preg_replace('/\\\\+/', '\\\\', $cfg['frontDTD']);
			$dtd = stripslashes(trim($dtd))."\n";
		}

		// 브라우저 TITLE 변경
		switch($_SERVER['SCRIPT_NAME']) {
			case '/shop/detail.php' :
				if($cfg['br_title_prd'] > 0) {
					$prd = $GLOBALS['prd'];
					$cfg['br_title'] = ($cfg['br_title_prd'] == 2 && $prd['keyword']) ? trim($prd['keyword']) : trim($prd['name']);
					if($cfg['br_title_prd'] == 1 && $cfg['br_title_cate'] == 'Y') {
						$big = sql_row("select name from $tbl[category] where no='$prd[big]'");
						$cfg['br_title'] = "[$big] ".$cfg['br_title'];
					}
					$cfg['br_title'] = htmlspecialchars(strip_tags($cfg['br_title']));
					$cfg['br_title'] = addslashes($cfg['br_title']);
				}
			break;
			case '/board/index.php' :
				if($cfg['br_title_board'] > 0) {
					$no = numberOnly($_GET['no']);
					if($no > 0) {
						$cfg['br_title'] = trim(sql_row("select title from mari_board where no='$no'"));
						$cfg['br_title'] = htmlspecialchars(strip_tags($cfg['br_title']));
						$cfg['br_title'] = addslashes($cfg['br_title']);
					}
				}
			break;
			case '/shop/product_review.php' :
				if($cfg['br_title_board'] > 0) {
					$rno = numberOnly($_GET['rno']);
					if($rno > 0) {
						$cfg['br_title'] = trim(sql_row("select title from $tbl[review] where no='$rno'"));
						$cfg['br_title'] = htmlspecialchars(strip_tags($cfg['br_title']));
						$cfg['br_title'] = addslashes($cfg['br_title']);
					}
				}
			break;
		}

		if($cfg['use_seo_advanced'] == 'Y') { // SEO설정 > 고급설정
			include 'header/seo_advanced.inc.php';
		}

		if($_SESSION['browser_type'] != 'pc' && $cfg['mobile_use'] == 'Y') { // 윙모바일 헤더
			$mdtd = ($cfg['mfrontDTD']) ? stripslashes(str_replace("\\", "", $cfg['mfrontDTD'])) : '<!DOCTYPE html>';
			$mdtd = trim($mdtd)."\n";

			if(isset($cfg['viewportAll']) == false || $cfg['viewportAll'] < 1) $cfg['viewportAll'] = '1.0';
			if(isset($cfg['viewportDetail']) == false) $cfg['viewportDetail'] = '2.0';
			if(isset($cfg['viewportBoard']) == false) $cfg['viewportBoard'] = '2.0';

			$maximum = $cfg['viewportAll'];
			if($cfg['viewportDetail'] > 1 && $_SERVER['SCRIPT_NAME'] == '/shop/detail.php') {
				$maximum = $cfg['viewportDetail'];
			}
			if($cfg['viewportBoard'] > 1 && $_SERVER['SCRIPT_NAME'] == '/board/index.php') {
				$maximum = $cfg['viewportBoard'];
			}
			$viewport = $GLOBALS['m_viewport'] = '<meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale='.$maximum.', minimum-scale=1.0, user-scalable=yes, target-densitydpi=device-dpi, viewport-fit=cover">';

			include $engine_dir.'/_engine/include/header/mobile.inc.php';
		} else { // PC용해더
			include $engine_dir.'/_engine/include/header/pc.inc.php';
		}

		if($cfg['design_version'] == 'V3'){
			if($_SESSION['browser_type'] == 'mobile' && $cfg['mobile_use'] == 'Y') include_once $GLOBALS['root_dir']."/_skin/mconfig.cfg";
			else include_once $GLOBALS['root_dir']."/_skin/config.cfg";
			$_skin_name = ($_SESSION['skin_preview_name'] && $admin['no']) ? $_SESSION['skin_preview_name'] : $design['skin'];

			if($cfg['ssl_type'] == 'Y') {
				if($urlfix != 'Y')  {
					$root_url = preg_replace('@^[a-z]+://@', 'https://', $root_url);
				}
			}

			$_css_normal_url=$root_url."/_skin/".$_skin_name."/style.css";
			$_css_normal_dir=$GLOBALS['root_dir']."/_skin/".$_skin_name."/style.css";
			$_css_tmp_url=$root_url."/".$GLOBALS['dir']['upload']."/wing_".$_skin_name."_temp.css";
			$_css_tmp_dir=$GLOBALS['root_dir']."/".$GLOBALS['dir']['upload']."/wing_".$_skin_name."_temp.css";
			$_css_cashe_time=$now-(5);
			if($_css_cashe_time > @filemtime($_css_tmp_dir) || !@is_file($_css_tmp_dir) || $admin['no']){
				$_css_contents=@file_get_contents($_css_normal_dir);
				$_css_contents=@str_replace("{{\$이미지경로}}", getFileDir('_skin/skin/img')."/_skin/".$_skin_name."/img", $_css_contents);
				$_css_contents=@str_replace("{{\$사이트주소}}", $root_url, $_css_contents);
				if($_css_contents != ""){
					$_css_fo=@fopen($_css_tmp_dir, "w");
					$_css_fw=@fwrite($_css_fo, $_css_contents);
				}
			}
			$_css_url=(@is_file($_css_tmp_dir)) ? $_css_tmp_url : $_css_normal_url;
		}

		include $engine_dir.'/_engine/include/header/common.inc.php';
	}

	function isTable($table) {
        global $pdo;

		$res = $pdo->query("show tables like '%$table%'");
        foreach($res as $t) {
			if($t[0] == $table) {
				return true;
			}
		}
		return false;
	}

	function addField($tbl, $f, $dtype) {
        global $pdo;

		if(!fieldExist($tbl,$f)) {
			return $pdo->query("alter table `$tbl` ADD `$f` $dtype");
		}
		return  false;
	}

	function modifyField($tbl, $f, $dtype) {
        global $pdo;

		if(fieldExist($tbl,$f)) {
			$pdo->query("alter table `$tbl` MODIFY `$f` $dtype");
		}
	}

	function fieldExist($tbl,$f) {
        global $pdo;

        $res = $pdo->query("show columns from `$tbl` like '$f'");
		return ($res && $res->rowCount() > 0);
	}

	function routineExists($routine) {
        global $pdo;

		$res = $pdo->query('SHOW FUNCTION STATUS');
		foreach($res as $_tdata) {
			if($_tdata['Name'] == 'curr_stock') return true;
		}
		return false;
	}

    function addIndex($table, $name, $column)
    {
        global $pdo;

        $exists = $pdo->row("
            select count(*) from information_schema.statistics
            where table_schema=DATABASE() and TABLE_NAME=? AND index_name=?
        ", array(
            $table, $name
        ));
        if ($exists) return true;

        return $pdo->query("alter table `{$table}` add index `$name` ($column)");
    }

	/* +----------------------------------------------------------------------------------------------+
	' |  mixed isAutoIncrement(string 테이블명) - 테이블의 auto_increment 번호를 출력/미세팅시 false
	' +----------------------------------------------------------------------------------------------+*/
	function isAutoIncrement($table) {
        global $pdo;

		$tblinfo = $pdo->assoc("SHOW TABLE STATUS LIKE '$table'");
        if(is_null($tblinfo['Auto_increment']) == true) return false;
        else {
    		return $tblinfo['Auto_increment'];
        }
	}

?>