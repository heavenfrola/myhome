<?PHP

	/* +----------------------------------------------------------------------------------------------+
	' |  스킨 메인
	' +----------------------------------------------------------------------------------------------+*/

	use Wing\Design\DesignCache;

	include_once $engine_dir."/_engine/include/design.lib.php";

	if($cfg['design_version'] == '') {
		include_once $engine_dir.'/_manage/design/version_check.php';
	}

	if($cfg['design_version'] == 'V3') {
		$_skin['dir'] = $root_dir.'/_skin';
		if(!is_array($templete_used_key)) $templete_used_key = array();

		if($cfg['mobile_use'] == 'Y' && $_SESSION['browser_type'] == 'mobile') {
			include_once $_skin['dir'].'/mconfig.'.$_skin_ext['g'];
		} else {
			include_once $_skin['dir'].'/config.'.$_skin_ext['g'];
		}

		// 스킨 미리보기일 경우
		if($admin['no'] && $_SESSION['skin_preview_name'] != '') {
			$skin_preview_name = $_SESSION['skin_preview_name'];
			if(is_dir($_skin['dir'].'/'.$skin_preview_name)) $design['skin'] = $skin_preview_name;
		}

		$_skin['folder'] = $_skin['dir'].'/'.$design['skin'];
		$_skin['url'] = $root_url.'/_skin/'.$design['skin'];
		include $_skin['folder'].'/skin_config.'.$_skin_ext['g'];
		include_once $engine_dir.'/_engine/include/shop.lib.php';

		$_file_name = ($_tmp_file_name) ? $_tmp_file_name : $_SERVER['SCRIPT_NAME'];
		$_parse_root_url = parse_url($root_url);
		if($_parse_root_url['path'] != '' && $_parse_root_url['path'] != '/') { // 서브URL을 root_url로 사용 하는 경우
			$_file_name = @str_replace($_parse_root_url['path'], '', $_file_name);
		}
		$_file_name = @str_replace('/', '_', $_file_name);
		$_file_name = @preg_replace('/^_+/', '', $_file_name);

		if(
			$_skin['intro_use'] == 'M' &&
			empty($member['no']) == true &&
            preg_match('/^member_/', $_file_name) == false &&
            $_file_name != 'common_zip_search.php' &&
			!($_file_name == 'content_content.php' && (in_array($_GET['cont'], array('join_rull', 'uselaw', 'privacy')) == true))
		) { // 인트로설정 회원 전용
			if($withdraw_step != "Y") {
				msg('', $root_url.'/member/login.php');
			}
		}
		if(!$_file_name || $_file_name == 'index.php' || $_GET['connect_start']) { // 인트로 페이지
			if($_skin['intro_use'] == 'Y') {
				$_file_name = 'intro_index.php';
			} else if($_skin['intro_use'] == 'R' && $_skin['intro_url']) {
				msg('', $_skin['intro_url']);
			} else {
				$_file_name = 'main_index.php';
				if(empty($cfg['cache_main_use']) == false) {
					new DesignCache('main');
				}
			}
		}
		if($_file_name == 'content_content.php') {
			if(@array_key_exists($_GET['cont'], $_content_add_info)) {
				$_add_content_pg = $cont_file;
				$_title_img_base = 'content_'.$_GET['cont'];
			} else {
				$_file_name = str_replace('_content.php', '_'.$_GET['cont'].'.php', $_file_name);
				if(!file_exists($_skin['folder'].'/CORE/'.str_replace('.php', '.'.$_skin_ext['p'], $_file_name))) {
					return;
				}
			}
			if($_GET['mode'] == 1) $_popup_list[] = $_file_name;
		}
		if($_file_name == 'common_zip_search.php' && $_GET['zip_mode'] == 2) {
			$_file_name = 'common_street_zip_search.php';
		}
		if($_GET['striplayout'] == 1) $_popup_list[] = $_file_name;
		if($_file_name == 'member_join_step2.php' || $_file_name == 'member_edit_step2.php') {
			$_file_name = 'member_join_frm.php';
		}
		if($_file_name == "shop_big_section.php" || $_file_name == "shop_detail.php") {
			$_title_img_base = $_cno1['no'] ? $_cno1['no'] : $prd['small'];
			$_title_img_base2 = $_cno1['mid'] ? $_cno1['mid'] : $prd['mid'];
			$_title_img_base3 = $_cno1['big'] ? $_cno1['big'] : $prd['big'];

			// 개인결제창
			if($prd['big']) {
				$private = $pdo->row("select private from `$tbl[category]` where `no`='$prd[big]'");
				if($type=='popup') {
					if($private=='Y' && file_exists($_skin['folder'].'/CORE/shop_detail_popup_private.wsr')) {
						$_tmp_client_file_name = 'shop_detail_popup_private.wsr';
					}
				}else {
					if($private=='Y' && file_exists($_skin['folder'].'/CORE/shop_detail_private.wsr')) {
						$_tmp_client_file_name = 'shop_detail_private.wsr';
					}
				}
			}
		}
		if($_GET['single_module']) {
			if($_GET['single_module'] == 'ajax') {
				define('_LOAD_AJAX_MODULE_', $_GET['ajaxSkin']);
				unset($_GET['single_module']);
			}
			$module_uri = explode('&', preg_replace('/[^?]+\?/', '', $_GET['document_url']));
			foreach($module_uri as $val) {
				$tmp = explode('=', $val);
				if($tmp[0] == 'page' && $mp[0] == 'rev_page' || $tmp[0] == 'qna_page') continue;
				${$tmp[0]} = $_GET[$tmp[0]] = $tmp[1];
			}
		}

		// 게시판일 경우
		if($_file_name == 'board_index.php') {
			if($_GET['single_module']) {
				$page = $_GET['page'] = numberOnly($_GET['module_page']);

				$bs_module = preg_replace('/\/|\./', '', $_GET['single_module']);
				$bs_module = str_replace('@', '/', $bs_module);
				$main_file = $engine_dir.'/board/'.$bs_module.'.php';
				$no_master = true;
				$ajax_comment = 'Y';

				include_once $engine_dir.'/board/include/lib.php';
				include_once $engine_dir.'/board/index.php';
			}
			if(@is_file($skin_path.'skin_config.'.$_skin_ext['g'])) include_once $skin_path.'skin_config.'.$_skin_ext['g'];
			$_title_img_base = $db;

			ob_start();
			// 상단디자인
			if($config['top_use'] == 'Y' && $config['top_content'] && !$page) include $mari_path.'include/top_design.php';
			include $main_file;
			if($ajax_comment != 'Y') include_once $mari_path.'include/header.php';
			$_board_content = ob_get_contents();
			ob_end_clean();
			$pageRes = $pg_res;

			preg_match_all('/(\{\{\$([^}]+)\}\})|(\{\{if\(([^}]+)\)\}\})/', $_board_content, $matches);
			$GLOBALS['templete_used_key'] = array_unique(array_merge($GLOBALS['templete_used_key'],$matches[2], $matches[4]));
		}

		// 스킨파일 로딩 위치 변경
		$_layout_file_arr = array('{{T}}' => 'header.'.$_skin_ext['c'], '{{L}}' => 'leftmenu.'.$_skin_ext['c'], '{{M}}' => 'content_frame.'.$_skin_ext['c'], '{{Q}}' => 'quick.'.$_skin_ext['c'], '{{B}}' => 'footer.'.$_skin_ext['c']);
		$_client_file_name = str_replace('.php', '.'.$_skin_ext['p'], $_file_name);
		if($_tmp_client_file_name) $_client_file_name = $_tmp_client_file_name;

		// 상품 게시판 아이콘 설정
		$_prd_board_icon['new'] = "<img src=\"".$_skin['url']."/img/shop/i_new.gif\" border=\"0\" alt=\"최신\">";
		$_prd_board_icon['file'] = "<img src=\"".$_skin['url']."/img/shop/file.gif\" border=\"0\" alt=\"첨부파일\">";
		$_prd_board_icon['secret'] = "<img src=\"".$_skin['url']."/img/shop/i_secret.gif\" border=\"0\" alt=\"비밀글\">";
		$_prd_board_icon['reply'] = "<img src=\"".$_skin['url']."/img/shop/reply.gif\" border=\"0\" alt=\"답글있음\">";
		$_prd_board_icon['reply_b'] = "<img src=\"".$_skin['url']."/img/shop/reply_before.gif\" border=\"0\" alt=\"답글없음\">";
		$_prd_board_icon['star'] = "<img src=\"".$_skin['url']."/img/shop/star2.gif\" border=\"0\" alt=\"평점\">";


		// 스킨파일 로딩 위치 변경
		if($_SESSION['browser_type']=='mobile' && $cfg['mobile_use'] == 'Y') $_this_layout="{{T}} {{L}} {{M}} {{Q}} {{B}}";
		else $_this_layout=($_page_layout[$_file_name]) ? $_layout[$_page_layout[$_file_name]] : $_layout[$_skin['default_layout']];

		$_this_layout=($_page_layout[$_file_name]) ? $_layout[$_page_layout[$_file_name]] : $_layout[$_skin['default_layout']];
		$_this_layout=str_replace("<tr>", "<tr valign=\"top\">", $_this_layout);
		$_background_img=($_skin['background_use'] == "Y") ? $_skin['url']."/img/bg/".$_skin['background'] : "";
		$_this_pop_up=(in_array($_file_name, $_popup_list)) ? 1 : 0; // 팝업창인지 검색

		if($_SESSION['browser_type'] != 'mobile' && $mobile_browser == 'mobile' && $cfg['mobile_use'] == 'Y' && $cfg['mobile_ver_show'] == 'Y') {
			$mobile_ver_btn  = '<link rel="stylesheet" type="text/css" href="'.$engine_url.'/_engine/common/mobile_ver.css.php?engine_url='.$engine_url.'\">';
			$mobile_ver_btn .= '<div class="vm_btn"><a href="'.$m_root_url.'" class="mobileView"><span></span> 모바일버전 보기</a></div>';
			$mobile_ver_btn .= '<script type="text/javascript">$(document).ready(function(){$(".vm_btn").width($("body").attr("scrollWidth"));});</script>';
		}

		foreach($_layout_file_arr as $lay_out_key=>$lay_out_val) {

//			if($single_module == 'prd_basic') {
//				$_GET['page'] = numberOnly($_GET['module_page']);
//				include $engine_dir.'/_engine/shop/prd_list.php';
//				$_content_content = getFContent($_skin['folder']."/CORE/".$_client_file_name);
//
//				break;
//			}

			if(!$_this_pop_up) {
				$_layout_content=getFContent($_skin['folder']."/COMMON/".$lay_out_val);
			}
			// 페이지 내용
			if($lay_out_key == "{{M}}" || $_this_pop_up) {
				$_content_content="";
				// 게시판일 경우
				if($_file_name == "board_index.php") {
					$_content_content=$_board_content;
				}elseif($_add_content_pg) {
					$_content_content=getFContent($_add_content_pg);
				}else{
					if($_GET['single_module']) {
						$page = $_GET['page'] = numberOnly($_GET['module_page']);

						if(preg_match('/user([0-9]+)_(list)/', $_GET['single_module'], $moduleinfo)) {
							$_content_content = '{{$사용자리스트'.$moduleinfo[1].'}} ';
							$templete_used_key[] = '사용자리스트'.$moduleinfo[1];
						} elseif(empty($_single_module_code) == false) {
							$_content_content = '{{$'.$_single_module_code.'}}';
							$templete_used_key = array($_single_module_code);
						} elseif($single_module == 'qna_list') {
							include_once $engine_dir.'/_engine/shop/detail.php';
							$_GET['qna_page'] = $qna_page = numberOnly($_GET['module_page']);
							$_content_content = '{{$상품질답리스트}}';
							$templete_used_key[] = '상품질답리스트';
						} elseif($single_module == 'qna_total_list') {
							include_once $engine_dir.'/_engine/shop/product_qna_list.php';
							$_GET['qna_page'] = $qna_page = numberOnly($_GET['module_page']);
							$_content_content = '{{$질답리스트}}';
							$templete_used_key[] = '질답리스트';
						} elseif($single_module == 'review_total_list') {
							include_once $engine_dir.'/_engine/shop/product_review_list.php';
							$_GET['rev_page'] = $rev_page = numberOnly($_GET['module_page']);
							$_content_content = '{{$상품평리스트}}';
							$templete_used_key[] = '상품평리스트';
						} elseif($single_module == 'review_list') {
							include_once $engine_dir.'/_engine/shop/detail.php';
							$_GET['rev_page'] = $rev_page = numberOnly($_GET['module_page']);
							$_content_content = '{{$상품평리스트}}';
							$templete_used_key[] = '상품평리스트';
						} elseif($single_module == 'mypage_milage') {
							include_once $engine_dir.'/_engine/mypage/milage.php';
							$_content_content = '{{$적립금리스트}}';
							$templete_used_key[] = '적립금리스트';
						} elseif($single_module == 'mypage_emoney') {
							include_once $engine_dir.'/_engine/mypage/emoney.php';
							$_content_content = '{{$예치금리스트}}';
							$templete_used_key[] = '예치금리스트';
						} elseif($single_module == 'mypage_point') {
							include_once $engine_dir.'/_engine/mypage/point.php';
							$_content_content = '{{$포인트리스트}}';
							$templete_used_key[] = '포인트리스트';
						} elseif($single_module == 'mypage_counsel_list') {
							include_once $engine_dir.'/_engine/mypage/counsel_list.php';
							$_content_content = '{{$고객상담리스트}}';
							$templete_used_key[] = '고객상담리스트';
						} elseif($single_module == 'mypage_review_list') {
							include_once $engine_dir.'/_engine/mypage/review_list.php';
							$_content_content = '{{$상품평리스트}}';
							$templete_used_key[] = '상품평리스트';
						} elseif($single_module == 'search_result_prd_list') {
							$search_str = $_GET['search_str'] = urldecode($search_str);
							include_once $engine_dir.'/_engine/shop/prd_list.php';
							$_content_content = '{{$상품리스트}}';
							$templete_used_key[] = '상품리스트';
						} elseif($single_module == 'mypage_coupon_down_list') {
							include_once $engine_dir.'/_engine/mypage/coupon_down_list.php';
							$_content_content = '{{$쿠폰리스트}}';
							$templete_used_key[] = '쿠폰리스트';
						} elseif($single_module == 'common_zipcode_list') {
							$search = $_GET['search'] = urldecode($search);
							include_once $engine_dir.'/_engine/common/zip_search.php';
							$_content_content = '{{$우편번호리스트}}';
							$templete_used_key[] = '우편번호리스트';
						} elseif($single_module == 'detail_multi_option_list') {
							include_once $engine_dir.'/_engine/include/shop.lib.php';
							include_once $engine_dir.'/_engine/include/shop_detail.lib.php';
							$_content_content = '{{$선택된멀티옵션리스트}}';
							$templete_used_key[] = '선택된멀티옵션리스트';
						} elseif($single_module == 'detail_qna_list' || $single_module == 'detail_review_list') {
							include_once $engine_dir.'/_engine/include/shop.lib.php';
							include_once $engine_dir.'/_engine/include/shop_detail.lib.php';

							$ano = numberOnly($_GET['ano']);
							$pno = addslashes($_GET['pno']);
							$prd = ($ano) ? checkPrd($pdo->row("select hash from $tbl[product] where no='$ano'")) : checkPrd($pno);
							$_GET['qna_page'] = $qna_page = numberOnly($_GET['module_page']);
							$_GET['rev_page'] = $rev_page = numberOnly($_GET['module_page']);

							$design_hangul = ($single_module == 'detail_qna_list') ? '상품질답리스트' : '상품평리스트';
							$_content_content = '{{$'.$design_hangul.'}}';
							$templete_used_key[] = $design_hangul;
						} else {
							if($single_module == 'click_prd_list') $list_mode = 4;
							$_GET['page'] = $_GET['module_page'];
							include_once $engine_dir.'/_engine/shop/prd_list.php';
							$_content_content = '{{$상품리스트}}';
							$templete_used_key[] = '상품리스트';
						}
					} else {
						$_content_content=getFContent($_skin['folder']."/CORE/".$_client_file_name);
						if(!$_content_content && $_tmp_content) $_content_content=$_tmp_content;
					}
				}
                
				if($_this_pop_up) {
					//$_content_content=contentReset($_content_content, $_file_name);
					break;
				}
				if(!$_GET['single_module'] || !$_this_pop_up) {
					$_content_content = '{{$scr_top}}'.$_content_content.'{{$scr_bottom}}';
				}
				if($_layout_content == "") {
					$_layout_content=$_content_content;
				}
				$_layout_content=str_replace("{{페이지내용}}", $_content_content, $_layout_content);
			}

			if($mobile_ver_btn && $lay_out_key == '{{T}}' && $cfg['mobile_show_top'] == 'Y') {
				$_layout_content = $mobile_ver_btn.$_layout_content;
			}
			if($mobile_ver_btn && $lay_out_key == '{{B}}' && $cfg['mobile_show_top'] != 'Y') {
				$_layout_content = $_layout_content.$mobile_ver_btn;
			}

			$_this_layout=str_replace($lay_out_key, $_layout_content, $_this_layout);
		}

		// 모듈 변수 선언
		include_once $engine_dir."/_manage/skin_module/_skin_module.php";

		if(defined('__do_not_print_skin__') == true) return;

		// 타이틀 이미지
		$_title_img_base=$_title_img_base ? $_title_img_base : $_file_name;
		$_title_img_name=titleIMGName(str_replace(".php", "", $_title_img_base));
		$_title_img_name=(!$_title_img_name && $_title_img_base2) ? titleIMGName($_title_img_base2) : $_title_img_name;
		$_title_img_name=(!$_title_img_name && $_title_img_base3) ? titleIMGName($_title_img_base3) : $_title_img_name;

		// <==================== 모듈 재선언 시작
		include_once $engine_dir."/_engine/skin_module/_skin_module.php";

        if (defined('__MODULE_LOADER__') == true) return;

		$_replace_code['common_module']['pageres']=$pageRes;
		// 모듈 재선언 끝 ========================>

		$_this_layout=contentReset($_this_layout, $_file_name);

		//
		if(!$_GET['single_module'] || !$_this_pop_up) {
			$_this_layout = str_replace('{{$scr_top}}', setMktScript('top'), $_this_layout);
			$_this_layout = str_replace('{{$scr_bottom}}', setMktScript($_file_name).setMktScript('footer'), $_this_layout);
		}

		// 최종 한글 코드 삭제
		$_this_layout=preg_replace("/\{{2}([^}]+)\}{2}/", "", $_this_layout);

		echo open_popup();//팝업

		if($_GET['single_module'] || defined('_LOAD_AJAX_PAGE_') == true || defined('_LOAD_AJAX_MODULE_') == true) {
			header('Content-type:application/json; charet='._BASE_CHARSET_.';');

            $end_page = null;
            if (is_object($PagingInstance) == true) {
                $end_page = $PagingInstance->end;
            }

			exit(json_encode(array(
				'status' => 'success',
				'obj_id' => $obj_id,
				'content' => trim(preg_replace("/\{{2}([^}]+)\}{2}/", "", contentReset($_content_content, $_file_name))),
				'next_page' => ($_GET['full_reload'] == 'true') ? $_GET['module_page'] : $_GET['module_page']+1,
                'end_page' => $end_page,
                'pageRes'=>$pageRes
			)));
		}

		if ($cfg['limit_19'] == 'Y' &&  defined('_wisa_manage_edit_') == false && $member['level'] != 1) {
			if (is_array($_SESSION['ipin_res'])) {
				$birth = array(
					substr($_SESSION['ipin_res']['birth'], 0, 4),
					substr($_SESSION['ipin_res']['birth'], 4, 2),
					substr($_SESSION['ipin_res']['birth'], 6, 2)
				);
			} elseif ($member['birth']) {
				$birth = explode('-', $member['birth']);
			}
			if (is_array($birth)) {
				$age = floor((date('Ymd')-($birth[0].$birth[1].$birth[2]))/10000);
			} else {
				$age = 0;
			}
			if ($_skin['intro_use'] == 'Y') {
				$intro_19use = $root_url.'/';
			} else if($_skin['intro_use'] == 'R' && $_skin['intro_url']) {
				$intro_19use = $_skin['intro_url'];
			}
			if ($age < 19 && $_SESSION['ipin_res']) {
				unset($_SESSION['ipin_res']);
				msg(__lang_member_19join_limit__, '/member/login.php');
			}
			if (
				$age < 19 &&
				$_file_name != 'member_login.php' &&
				$_file_name != 'member_search_id_pwd.php' &&
				$_SERVER['REQUEST_URI'] != '/content/content.php?cont=privacy' &&
				$_SERVER['REQUEST_URI'] != '/content/content.php?cont=uselaw' &&
				$_SERVER['REQUEST_URI'] != '/content/content.php?cont=join_rull&mode=1' &&
				preg_match('/^member_join_step[0-9]\.php$/', $_file_name) == false &&
				preg_match('/^member_find_step[0-9]\.php$/', $_file_name) == false &&
				preg_match('/^member\/ipin/', $_GET['exec_file']) == false &&
				preg_match('/^member\/modify_pwd\.php$/', $_GET['exec_file']) == false &&
				($_skin['intro_use'] == 'Y' && $_file_name == 'intro_index.php') == false &&
				($_skin['intro_use'] == 'R' && strpos(getURL(), $_skin['intro_url']) !== false) == false
			) {
				unset($_SESSION['member_no']);
				msg('', '/member/login.php');
			}
		}

?>
<?php
if (($cfg['kakao_login_use']=='Y' || $cfg['kakao_login_use'] == "S") && $scfg->comp('kakao_url_code') == true && $scfg->comp('kakao_sns_id') == true && !$_GET['from_ajax']) {
    $cfg['kakao_url_code'] = $cfg['kakao_sns_id'];
}
if(count($_POST) == 0 && $cfg['kakao_url_code'] && ($scfg->comp('kakaolink_use', 'Y') == true || $scfg->comp('kakaostory_use', 'Y') == true)) {
	$_defer_scripts .= "<script src='https://developers.kakao.com/sdk/js/kakao.min.js'></script>
						<script type='text/javascript'>Kakao.init('{$cfg['kakao_url_code']}');</script>";
	if(checkCodeUsed('카카오링크')) {
		$kakaourl_img = $prd['upfile2'] ? getFileDir($prd['updir'])."/{$prd['updir']}/{$prd['upfile2']}" : $cfg['noimg2'];
		$_defer_scripts .= "
		<script type='text/javascript'>
			Kakao.Link.createDefaultButton({
			  container: '#kakao-link-btn',
			  objectType: 'feed',
			  content: {
				title: '[{$cfg['company_mall_name']}]\\n{$prd['name']}',
				imageUrl: '$kakaourl_img',
				link: {
				  mobileWebUrl: '$m_root_url',
				  webUrl: '$root_url'
				}
			  },
			  buttons: [
				{
				  title: '상품 보러 가기',
				  link: {
					mobileWebUrl: '$m_root_url/shop/detail.php?pno={$prd['hash']}',
					webUrl: '$root_url/shop/detail.php?pno={$prd['hash']}'
				  }
				}
			  ]
			});
		</script>
		";
		unset($kakaourl_img);
	}
	if(checkCodeUsed('카카오스토리')) {
		$_defer_scripts .= "
		<script type='text/javascript'>
			function kakaostory() {
				Kakao.Story.share({
					url: '$root_url/shop/detail.php?pno={$prd['hash']}',
					text: '[{$cfg['company_mall_name']}]\\n{$prd['name']}'
				});
			}
		</script>
		";
	}
}
	// 퀵카트
	if(is_array($_apps_n) && in_array(1, $_apps_n)) {
		$plugin_path = $engine_dir.'/_plugin/quickCart/engine_common_skin_index.php';
		if(file_exists($plugin_path)) {
			include $plugin_path;
		}
	}


if(($_SESSION['browser_type']=='mobile' && $cfg['mobile_use'] == 'Y') || $_REQUEST['striplayout']) {
	echo $__force_header;
	if(!$_this_pop_up) echo $_this_layout;
	else echo preg_replace('/\{{2}([^}]+)\}{2}/', '', contentReset($_content_content, $_file_name));
	echo $__force_footer;
} else {?>
<style type="text/css">
<!--
#skin_<?=str_replace(".php", "", $_file_name)?>_big_div{width:100%;<?=$_skin['body_color'] ? "background-color:{$_skin['body_color']};" : "";?>
<?php
		// 배경 이미지 설정
		if($_skin['background_use'] == "Y") {
			if($_skin['background_fixed'] == "Y") echo "background-attachment:fixed;";
			if($_background_img) echo "background-image:url($_background_img);";
			echo "background-repeat:";
			if($_skin['background_type'] == "1" || !$_skin['background_type']) echo "repeat";
			elseif($_skin['background_type'] == "2") echo "repeat-y";
			elseif($_skin['background_type'] == "3") echo "repeat-x";
			elseif($_skin['background_type'] == "4") echo "no-repeat";
			echo ";";
		}
?>
}
-->
</style>
<div id="skin_<?=str_replace(".php", "", $_file_name)?>_big_div">
<?php
	// 사용자 레이아웃 (주로 코더 전용)
	if($_skin['default_layout'] == "fixed" && !$_this_pop_up) {
		echo $_this_layout;
	}else{
?>
<table border="0" width="100%" height="100%" cellpadding="0" cellspacing="0">
	<tr>
		<td valign="top" id='__layoutParent__' align="<?=$_skin['site_align']?>">
		<?php
		if($_this_pop_up) { // 팝업창일경우에는 내용만 출력
			echo $__force_header;
			echo preg_replace("/\{{2}([^}]+)\}{2}/", "", contentReset($_content_content, $_file_name));
			echo $__force_footer;
		}else{
		?>
		<table border="0" cellpadding="0" cellspacing="0" class="mall_table">
			<?=$_this_layout?>
		</table>
		<?php } ?>
		</td>
	</tr>
</table>
<?php } ?>
</div>
<?php } ?>
<script type='text/javascript' defer='defer'>
$(document).ready(function() {
	<?php if($_use['user_frame'] == 'Y') { ?>
	$('a').each(function() {
		if(/#[0-9]+_frame$/.test(this.href) == true) {
			var temp = this.href.split('#')[1].split('_')[0];
			this.href = '#';
			$(this).click(function() {
				quickDetailFrame(this, temp, '<?=$cno1?>');
				return false;
			});
		}
	});
	<?php } ?>
});
</script>
<?php

		echo $_defer_scripts;

		designValUnset();
		close(1);
		exit();
	}

?>