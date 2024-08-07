<?PHP

	use Wing\common\SimpleXMLExtended;

	$navi_href = explode('@', $_REQUEST['body']);
	$qm_items = explode('@', $admin['quickmenu']);
	$qm_list = '';

	if(is_object($menudata) == false) {
		ob_start();
		include_once $menu_file;
		$xml_menu_source = ob_get_contents();
		ob_end_clean();

		$extended_body = preg_replace('/.*body=|(&[^&]+).*/', '$1', $_SERVER['QUERY_STRING']);
		$extended_body = str_replace('%40', '@', $extended_body);
		if(preg_match('/^[0-9]+$/', $_REQUEST['body'])) $bodyPgcode = $body - ($body % 1000);
		if($bodyPgcode) $pgcode = $bodyPgcode;

		// 사용자 추가메뉴
		if($admin['level'] < 4 && file_exists($root_dir.'/_manage/menu/menu.xml.php')) {
			ob_start();
			include_once $root_dir.'/_manage/menu/menu.xml.php';
			$ex_menu = ob_get_contents();
			ob_end_clean();

			$menu_org = new DOMDocument();
			$menu_org->loadXml($xml_menu_source);
			$xpath1 = new DOMXpath($menu_org);

			$menu_usr = new DOMDocument();
			$menu_usr->loadXml($ex_menu);

			foreach($menu_usr->getElementsByTagName('mid') as $_mid) {
				$_pgcode = $_mid->getAttribute('pgcode');
				$_tmp = $xpath1->query("//mid[@pgcode=$_pgcode]");
				if($_tmp->length > 0) { // 신규 소메뉴를 기존 중메뉴 아래에 추가
					foreach($_mid->getElementsByTagName('small') as $_small) {
						$fragment = $menu_org->createDocumentFragment();
						$fragment->appendXml($menu_usr->saveXml($_small));
						$_tmp->item(0)->appendChild($fragment);
					}
				} else { // 신규 중메뉴를 대메뉴 아래에 추가
					$_pgcode = $_mid->parentNode->getAttribute('pgcode');
					$_tmp = $xpath1->query("//big[@pgcode=$_pgcode]");
					$fragment = $menu_org->createDocumentFragment();
					$fragment->appendXml($menu_usr->saveXml($_mid));
					$_tmp->item(0)->appendChild($fragment);
				}
			}
			$xml_menu_source = $menu_org->saveXml($menu_org);
		}
		$xml_menu = new SimpleXMLExtended($xml_menu_source);
		$menudata = $xml_menu->menudata;

		if(is_object($menudata) == true) {
			foreach($menudata->big as $key => $val) {
				if(($val->attr('category') == $navi_href[0] || $val->attr('pgcode') == $bodyPgcode) && $val->mid) {
					foreach($val->mid as $objmid) {
						if(is_object($objmid->small) == false) continue;
						foreach($objmid->small as $objsmall) {
							$match_ok = false;
							$linkcnt = count(explode('&', $objsmall->val('link')));

							if(($extended_body && $extended_body == $objsmall->val('link')) || $body == $objsmall->val('pgcode')) $match_ok = true;
							if((!$current_menu && !$pgcode && $objsmall->val('link') && $body == $objsmall->val('link') && $linkcnt == 1) || ($_REQUEST['pgcode'] == $objsmall->val('pgcode'))) $match_ok = true;
							if($body == $objsmall->val('pgcode')) $body = preg_replace('/&.*$/', '', $objsmall->val('link'));

							if($match_ok) {
								$current_menu = $objsmall;
								$current_mid = $objmid;
								$current_big = $val;
							}
						}
					}
				}
			}
			// 퀵메뉴 등록
			foreach($menudata->big as $big_menu) {
				foreach($big_menu->mid as $mid_menu) {
					foreach($mid_menu->small as $small_menu) {
						if(in_array($small_menu->val('pgcode'), $qm_items)) {
							$pgcode = $small_menu->val('pgcode');
							if(!$pgcode) continue;
							if ($small_menu->val('hidden') == "Y") continue;
							$qm_first = ($qm_list == '') ? "class='first-child'" : "";
							$qm_list .= "<li $qm_first>
								<a href='?body=".$small_menu->val('link')."'>".$small_menu->val('name')."</a>
								<a id='qmdel_$mcode' href='javascript:;' onclick=\"qmDelOK('$pgcode')\" class='delete'>삭제</a>
							</li>";
						}
					}
				}
			}
		}

		if(is_object($current_menu)) $pgcode = $current_menu->val('pgcode');
		if($pgcode) $_SESSION['pgcode_ref'] = $pgcode;

		$bigcode = floor($pgcode / 1000)*1000;

		if(!function_exists("parseMenuS")) {
			function parseMenuS($object) {
				if(!$object) return;

				# 메뉴명 파싱
				$object->name = stripslashes($object->val('name'));
				if($object->val('sc') == 'Y') $menu_name .= " <img src='{$GLOBALS['engine_url']}/_manage/image/_backup/shortcut2.gif'>";
				if($object->val('pgcode') == $GLOBALS['pgcode']) $object->name = $object->val('name'); // 현재 선택된 메뉴
				if($object->val('pgcode') == $GLOBALS['pgcode']) $object->mevent = " class='over'"; // 현재 선택된 메뉴

				# 메뉴 출력조건 확인
				if($object->val('if')) if(!eval("return (".$object->val('if').");")) $object->hidden = true; // 메뉴 출력 조건식
				if($object->val('hidden') == 'Y') $object->hidden = true;

				# 링크정리
				if($object->val('link2')) $object->link = eval("return ".$object->val('link2').";");
				if($object->val('target') == '' && $object->val('link')) {
					$object->link = "./?body=".$object->val('link');
				}
				if($object->val('link') == '') $object->link= 'javascript:;';
				if($object->val('onclick')) $object->onclick = "onclick=\"".$object->val('onclick')."\"";

				# 서브링크
				if($object->val('modify')) {
					list($mname, $mlink) = explode('___', $object->val('modify'));
					$object->modify = "<a href=\"./?body=$mlink\" id='sm_".$object->val('pgcode')."' class='set btt' tooltip='설정'>$mname</a>";
				}

				return $object;
			}
		}

		//관련메뉴 읽기
		$_rel_preload = array();
		if(is_object($current_menu->rel->item) == true && count($current_menu->rel->item) > 0) {
			foreach($current_menu->rel->item as $key => $val) {
				$rname = $val->val('name');
				$rlink = $val->val('link');
				$rtarget = (preg_match('/^http:\/\//', $val->val('link'))) ? '_blank' : '_self';
				$_rel_preload[$rname] = $rtarget.'___'.$rlink;
			}
		}
	}

	// 메뉴 출력
	if($only_read_menu) return;

	$_midCookie = explode('@', trim($_COOKIE['midCookie'], '@'));

?>
<ul id="manageSideMenu">
	<?PHP
	foreach($menudata->big as $objbig) {
		if(is_object($current_big) == false) continue;
		if($current_big->attr('pgcode') != $objbig->attr('pgcode')) continue;
		foreach($objbig->mid as $objmid) {
		if($objmid->attr('if')) if(!eval("return (".$objmid->attr('if').");")) continue;

		$_mid_css = (in_array($objmid->attr('pgcode'), $_midCookie)) ? 'over' : '';
		$_sml_css = (in_array($objmid->attr('pgcode'), $_midCookie)) ? 'style="display:none;"' : '';
	?>
	<li class="title <?=$_mid_css?>">
		<a class="mid" data="<?=$objmid->attr('pgcode')?>"><span class="icon" style="background:url('<?=$engine_url?>/_manage/image/common/navi/<?=$objmid->attr('pgcode')?>.png') no-repeat center; background-size:16px 16px;"></span><?=$objmid->attr('name')?><span class="arrow" onmouseover="midToolTip(event, this)" onmouseout="hideToolTip();"></span></a>
		<?php if (!is_object($objmid->small)) { ?>
	</li>
		<?php continue;}?>
		<ul id="pgcode<?=$objmid->attr('pgcode')?>" class="sideSmall" <?=$_sml_css?>>
			<?php
				foreach($objmid->small as $objsmall) {
					$objsmall = parseMenuS($objsmall);
					if($objsmall->val('hidden')) continue;

					// 신규 메뉴 아이콘
					$_is_new_menu = $_is_up_menu = false;
					if($objsmall->val('new_date')) {
						$_new_date = strtotime($objsmall->val('new_date'));
						if(($now-$_new_date) < (86400*30)) {
							$_is_new_menu = true;
						}
					}
					// 업데이트 메뉴 아이콘
					if($objsmall->up_date[0]) {
						$_up_date = strtotime($objsmall->up_date[0]);
						if(($now-$_up_date) < (86400*30)) {
							$_is_up_menu = true;
						}
					}
			?>
			<li<?=$objsmall->mevent?>>
				<a href="<?=$objsmall->link?>" target="<?=$objsmall->val('target')?>" <?=$objsmall->onclick?> class="small">
					<?=$objsmall->name?>
					<?php if ($objsmall->val('count')) { ?><span class="data_count"><?=$objsmall->val('count')?></span><?php } ?>
				</a><?=$objsmall->val('modify')?>
				<?php if ($_is_new_menu == true) { ?><span class="icon"><img src="<?=$engine_url?>/_manage/image/common/cate_new.png" alt="new"></span><?php } ?>
				<?php if ($_is_up_menu == true) { ?><span class="icon"><img src="<?=$engine_url?>/_manage/image/common/cate_up.png" alt="update"></span><?php } ?>
			</li>
			<?php } ?>
		</ul>
	</li>
	<?php }} ?>
</ul>
<div class="quickmenu" style="display:none;">
	<ul>
		<li><a href="?body=wing@service_status">서비스관리</a></li>
		<li><a href="?body=customer@cs_list">고객센터</a></li>
		<li><a href="">메뉴얼FAQ</a></li>
		<li><a href="http://redirect.wisa.co.kr/emergency" target="_blank">긴급접수</a></li>
	</ul>
</div>

<script type="text/javascript">
	$(function(){
		$('#manageSideMenu .big').click(function(){
			if($($(this).attr('href')).css('display') == 'block') $($(this).attr('href')).slideUp('fast');
			else $($(this).attr('href')).slideDown('fast');
			return false;
		});
	});

	$("#manageSideMenu .title .mid").click(function(){
		var midcate = $(this).parent();
		var smallcate = $(this).parent().find('.sideSmall');
		var mode = 1;
		if (smallcate.css('display') == 'block') {
			midcate.addClass('over');
			smallcate.slideUp('fast');
			mode = 0;
		} else {
			midcate.removeClass('over');
			smallcate.slideDown('fast');
		}

		// 중단메뉴 ON,OFF 쿠키
		var midCookie = getCookie('midCookie');
		var pgcode = $(this).attr('data');
		if(mode == 0) {
			if(midCookie.search('@'+pgcode+'@') < 0) {
				if(!midCookie) midCookie = '@';
				midCookie += pgcode+'@';
			}
		} else {
			midCookie = midCookie.replace(pgcode+'@', '');
		}
		setCookie('midCookie', midCookie, 365);
	});
</script>