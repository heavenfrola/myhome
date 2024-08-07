<?PHP

	/* +----------------------------------------------------------------------------------------------+
	' |  상품상세 퀵프리뷰 메인
	' +----------------------------------------------------------------------------------------------+*/

	include_once $engine_dir.'/_engine/include/common.lib.php';
	include_once $engine_dir.'/_engine/include/design.lib.php';
	if($_SESSION['browser_type'] == 'mobile' && $cfg['mobile_use'] == 'Y') {
		include_once $root_dir.'/_skin/mconfig.cfg';
		$is_mobile = 'Y';
	} else {
		include_once $root_dir.'/_skin/config.cfg';
		$is_mobile = 'N';
	}
	include_once $root_dir.'/_skin/'.$design['skin'].'/skin_config.cfg';

	$type = $_GET['type'];
	$type = preg_replace('/[^a-z0-9_]/', '', $type);

	switch($type) {
		case 'popup' :
			$str = '';
			$cname = array('qd1_use', 'qd1_use', 'qd1_width', 'qd1_margin', 'qd1_bgcolor', 'qd1_opacity', 'qd1_scroll');
			foreach($cname as $val) {
				$str .= ",\"$val\":\"".addslashes($_skin[$val])."\"";
			}
			$str = substr($str, 1);
			exit('{'.$str.'}');
		break;
		case 'getFrame' :
			$str = '';
			$cname = array('qd2_width', 'qd2_height');
			foreach($cname as $val) {
				$str .= ",\"$val\":\"".addslashes($_skin[$val])."\"";
			}
			$str = substr($str, 1);
			exit('{'.$str.'}');
		break;
		case 'frame' :
			if(file_exists($root_dir."/_skin/".$design['skin']."/user_code.".$_skin_ext[g])) {
				include_once $root_dir."/_skin/".$design['skin']."/user_code.".$_skin_ext[g];
			}

			$user_cfg = $_user_code[numberOnly($_GET['frameno'])];

			switch($user_cfg['orderby']) {
				case '1' : $sort = 'edt_date desc'; break;
				case '2' : $sort = 'reg_date desc'; break;
				case '3' : $sort = 'rand()'; break;
				case '4' : $sort = 'hit_view desc'; break;
				case '5' : $sort = 'hit_order desc'; break;
				case '6' :
					if($user_cfg['ctype'] == 2 || $user_cfg['ctype'] == 6) {
						$sort = 'sort'.$user_cfg['cate'].' asc';
					} else {
						$cinfo = $pdo->assoc("select * from $tbl[category] where no='$user_cfg[cate]'");
						$sort = 'sort'.$_cate_colname[1][$cinfo['level']].' desc';
					}
				break;
			}

			$pno = addslashes($_GET['pno']);
			if(strlen($pno) != 32) $_GET['pno'] = $pno = $pdo->row("select hash from $tbl[product] where no='$pno'");

			if(!$pno) {
				switch($user_cfg['ctype']) {
					case 1 :
						$cinfo = $pdo->assoc("select * from $tbl[category] where no='$user_cfg[cate]'");
						$cinfo = $_cate_colname[1][$cinfo['level']];
						$where .= " and $cinfo='$user_cfg[cate]'";
					break;
					case 2 :
					case 6 :
						$cinfo = explode(',', $user_cfg['cate']);
						foreach($cinfo as $key => $val) {
							$where .= " and ebig like '%@$val%'";
						}
					break;
					case 4 :
					case 5 :
						$cinfo = $pdo->assoc("select * from $tbl[category] where no='$user_cfg[cate]'");
						$cinfo = $_cate_colname[$cinfo['ctype']][$cinfo['level']];
						$where .= " and $cinfo='$user_cfg[cate]'";
					break;
				}
				$pno = $_GET['pno'] = $pdo->row("select hash from $tbl[product] where 1 $where order by $sort limit 1");
			}

			$_GET['pno'] = $pno;
			if(!$pno) exit;

			include $engine_dir.'/_engine/shop/quickDetail.inc.php';
		break;
	}
?>