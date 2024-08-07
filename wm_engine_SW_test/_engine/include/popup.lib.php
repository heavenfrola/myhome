<?PHP

	/* +----------------------------------------------------------------------------------------------+
	' |  팝업처리 클래스
	' +----------------------------------------------------------------------------------------------+*/

	function open_popup($no="",$onload="") {
		global $now, $tbl, $cfg, $root_url, $preview, $engine_url, $manage_url, $_skin, $pdo;

		if(defined('open_popup')) return;
		define('open_popup', true);
		if(isset($_GET['makecache']) == true) return;

		$no = numberOnly($no);
		if($no) {
			$where = "`no`='$no'";
		} else {
			if($cfg['update_popup_time'] != 'Y' && $cfg['update_popup_time'] != '1') {
				$pdo->query("alter table $tbl[popup] modify start_date datetime not null");
				$pdo->query("alter table $tbl[popup] modify finish_date datetime not null");
				$pdo->query("insert into $tbl[config] (name, value) values ('update_popup_time', 'Y')");
			}
			$ymds = date('Y-m-d H:i:00', $now);
			$ymde = date('Y-m-d H:i:59', $now);

			if($_skin['jquery_ver']) {
				$r  = "<script type='text/javascript' src='$engine_url/_engine/common/jquery/".str_replace('jquery-', 'jquery-ui-', $_skin['jquery_ver'])."'></script>";
			} else {
				$r  = "<script type='text/javascript' src='$engine_url/_engine/common/jquery/jquery-ui-1.11.3.min.js'></script>";
			}
			$r .= "<script language=\"JavaScript\">\n";
			$where="(`start_date`<='$ymds' or start_date='0000-00-00 00:00:00') and (`finish_date`>='$ymde' or `finish_date`='0000-00-00 00:00:00') and `use`='Y'";
			if($onload) {
				$r.="window.onload=new function(){\n";
			}
		}

		// 페이지별 출력
		if(fieldExist($tbl['popup'], 'page') == true) {
			switch($GLOBALS['_file_name']) {
				case 'main_index.php';
					$where .= " and (page='' or page='@@' or page like '%@main@%')";
				break;
				case 'shop_big_section.php' :
					addField($tbl['popup'], 'page_detail', 'varchar(500) not null');
					$tmp = array();
					for($i = 1; $i <= 2; $i++) {
						$_cno = $GLOBALS['_cno'.$i];
						for($x = 1; $x <= 4; $x++) {
							$_no = $_cno[(100+$x)];
							if($_no) $tmp[] = "page_detail like '%@cate{$_no}@%'";
						}
					}
					$tmp = implode(' or ', $tmp);
                    if (empty($tmp) == true) return;
					$where .= " and page like '%@list@%' and ($tmp)";
				break;
				case 'shop_detail.php';
					addField($tbl['popup'], 'page_detail', 'varchar(500) not null');
					$where .= " and (page like '%@detail@%' and page_detail like '%prd{$GLOBALS['prd']['no']}@%')";
				break;
				case 'intro_index.php';
					$where .= " and page like '%@intro@%'";
				break;
				default :
					return;
				break;
			}
		} else {
			if($GLOBALS['_file_name'] != 'main_index.php') return;
		}

		$res = $pdo->iterator("select * from `$tbl[popup]` where $where order by no desc");
		foreach($res as $data) {
			if($data['device'] == 'mobile' && $_SESSION['browser_type'] != 'mobile') continue;
			if($data['device'] != 'mobile' && $_SESSION['browser_type'] == 'mobile') continue;

			$idx++;
			if($_COOKIE['pop'.$data['reg_date']]=="Y" && !$preview) continue;
			$url="/main/pop.php?popno=".$data['no']."&preview=$preview&urlfix=Y";
			if($preview) $data['layer']="N";
			$r .= "if(getCookie('pop{$data['reg_date']}')!='Y') {";
			if($data['device'] == 'mobile') {
				$r.= "$(window).ready(function(){\n";
				$r .= "setDimmed();\n";
				$r .= "$('#qdBackground').attr('id', 'qdBackground_$data[no]');\n";
				$r .= "$('body').append('<div id=\"wm_popup_$data[no]\" style=\"z-index:1001; position:fixed; left: $data[x]px; top:$data[y]px;\">".generate_popup($data['no'])."</div>')";
				$r.= "});";
			} else if($data['layer']=="Y") {
				$r.= "$(window).ready(function(){\n";
				$r.="$('body').append('";
				$r.="<div id=\"wm_popup_".$data['no']."\" style=\"position:absolute; left:{$data['x']}px; top:{$data['y']}px; width:{$data['w']}px; height:{$data['h']}px; z-index:".(500+$data['no']).";\"></div>";
				$r.="<iframe src=\"$url\" width=\"0\" height=\"0\" scrolling=\"no\" frameborder=\"0\" style=\"display:none\"></iframe>');\n";
				$r.="$('#wm_popup_{$data['no']}').draggable({cursor:'pointer', containment:'body'});";
				$r.= "});";
			}
			else {
				$r.="$(window).ready(function() {
						window.open('$url','popup$data[reg_date]','top=$data[y],left=$data[x],height=$data[h],width=$data[w],status=no,scrollbars=no,toolbar=no,menubar=no');
					});";
			}
			$r .= "}";
			if(!$no) $r.="\n";
		}

		if(!$no) {
			if($onload) {
				$r.="}\n";
			}
			$r.="</script>";
		}

		$r .= '<style type="text/css">.pop100 img { width: 100%; }</style>';

		$GLOBALS['_defer_scripts'] .= $r;
		unset($r);

		return $r;
	}

	function generate_popup($no, $target="parent.") {
		global $tbl,$preview, $_tmp_frame;

		if(is_array($no)) {
			$data=$no;
		} else {
			$no = numberOnly($no);
			if(!$no) msg('팝업 정보가 없습니다.', 'close');
			$data = get_info($tbl['popup'], 'no', $no);
		}
		if($preview) $data['layer'] = 'N';

		$c1 = generate_popup_content($data);

		$frame = (is_array($_tmp_frame)) ? $_tmp_frame : get_info($tbl['popup_frame'], 'no', $data['frame']);
		$c2 = generate_popup_content($frame);

		$r = str_replace('{내용}', $c1, $c2);

		if(($data['layer'] == 'Y' || $data['device'] == 'mobile') && $preview != true) {
			$r = str_replace("{창닫기}","javascript:;\" onClick=\"closePopup2('pop{$data['reg_date']}', '{$data['no']}'); return false;",$r);
			$r = str_replace("{하루창}","javascript:;\" onClick=\"closePopup2('pop{$data['reg_date']}', '{$data['no']}', true); return false;",$r);

			$r = addslashes($r);
			$r = str_replace("\r", "", $r);
			$r = str_replace("\n", "", $r);
			$r = str_replace("/", "\/", $r);

			if($data['device'] == 'mobile') {
				return $r;
			}
			?><script type='text/javascript'>$('#wm_popup_<?=$data[no]?>', <?=$target?>document).html("<?=$r?>");</script><?php
		} else {
			$r = str_replace('{창닫기}', "javascript:window.close();", $r);
			$r = str_replace('{하루창}', "javascript:closePopup('pop".$data['reg_date']."')", $r);
			echo $r;
		}
	}

	function generate_popup_content($data) {
		global $cfg, $root_url, $manage_url;

		$r = stripslashes($data['content']);
		$r = str_replace($manage_url.'/_manage/{창닫기}', '{창닫기}', $r);
		$r = str_replace($manage_url.'/_manage/{하루창}', '{하루창}', $r);
		if($data['html'] == '1') $r = nl2br($r);
		$file_url = getFileDir($data['updir']);
		if($cfg['use_icb_storage'] == 'Y' && $data['upurl']) {
			$file_url = $data['upurl'];
		}

		for($ii=1; $ii<=3; $ii++) {
			$img = $file_url.'/'.$data['updir'].'/'.$data['upfile'.$ii];

			$r = str_replace('{이미지경로'.$ii.'}', $img, $r);
			$r = str_replace('{이미지'.$ii.'}', '<img src="'.$img.'" border=0>', $r);
		}
		return $r;
	}

?>