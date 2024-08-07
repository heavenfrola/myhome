<?PHP

	use Wing\common\Xml;

	if(defined('_wisa_set_included') == false) {
		exit('Fobidden');
	}

    $body = (isset($_REQUEST['body']) == true) ? $_REQUEST['body'] : null;
	$body = preg_replace('/[^a-z0-9_@.]/i', '', $body);

	if (preg_match('/\.exe/', $body)) set_time_limit(0);

	include_once 'manage.header.php';

    if ($asvcs[0]->mall_goods_idx[0] == '5' && $asvcs[0]->wdisk_finish[0] < $now) {
        msg(
            "클라우드버전 PRO 요금제의 서비스 기간이 만료되었습니다.\\n만료일로부터 3일이 경과되면, 프런트 페이지 이미지를 불러올 수 없습니다.\\n정상적으로 서비스 이용을 원할 경우 서비스 기간을 연장 바랍니다.",
            'https://www.wisa.co.kr/expire?account_idx='.$asvcs[0]->account_idx[0]
        );
    }

	$ori_root_url = preg_replace('@^https?:@', '', $root_url);
	if($scfg->get('manage_url') && !$_REQUEST['ssid'] && !$_REQUEST['obody'] && isset($urlfix) == false) {
		$_manage_url = preg_replace('@https?://@', '', $cfg['manage_url']);
		if($_SERVER['HTTP_HOST'] != $_manage_url) {
			header('Location: '.$cfg['manage_url'].'/_manage/');
			exit;
		} else {
			$root_url = $cfg['manage_url'];
		}
	}

	if($_GET['body'] == 'css@manage.css') {
		include $engine_dir.'/_manage/css/manage.css.php';
		exit;
	}

	$pg_dsn = 'admin';

	if($body == 'menu@menu.xml') {
		header("Content-Type: text/xml; charset="._BASE_CHARSET_."");
		echo $xml_menu_source;
		exit;
	}

    if (preg_match('/\.pop/', $_inc[1])) {
        ob_start();
        require $body_file;
        $pop_content = ob_get_clean();

        if (defined('__pop_width__') == false) define('__pop_width__', '600px');
        if (defined('__pop_title__') == false) define('__pop_title__', '');

        ?><div id="popupContent" class="popupContent layerPop" style="width:<?=__pop_width__?>;">
            <div id="header" class="popup_hd_line">
                <h1 id="logo"><img src="<?=$engine_url?>/_manage/image/wisa.gif" alt="WISA."></h1>
                <div id="mngTab_pop"><?=__pop_title__?></div>
            </div>
            <div id="popupContentArea">
                <?=$pop_content?>
            </div>
        </div><?php
        exit;
    }

	if(preg_match('/\.frm|\.exe|\.css/',$_inc[1]) || $_REQUEST['dm_main_access'] == "Y"  || $body=='support@sso' || $_REQUEST['execmode'] == 'ajax') {
		include_once $body_file;
		close(); // 2007-03-12 추가
		exit();
	}

	$left_menu_n=($body == "main@main" || $body == "log@ac_view") ? 1 : 0;
	$mid_n=($body == "promotion@mcbox") ? 1 : 0;
	$mngTab = array("product", "order", "member", "design", "config", "board", "income", "log", "openmarket", "promotion", "wdisk", "erp", "wmb", "store");
	$mngTabNum = array_keys($mngTab, $_inc[0]);
	$pageOver[$mngTabNum[0]] = ' class="selected"';

	/* +----------------------------------------------------------------------------------------------+
	' |  호스팅/업데이트 공지사항
	' +----------------------------------------------------------------------------------------------+*/
	$cdata = trim(comm(_HOSTING_NOTICE_XML_, 'ver=wing&account_idx='.$wec->config['account_idx'].'&wm_key_code='.$wec->config['wm_key_code'].'&body='.$body));

	$cxml = new Xml();
	$xmldata = $cxml->xmlData($cdata);
	if($cxml->arr) {
		$cxml = $cxml->arr->wing[0];
	}


	/* +----------------------------------------------------------------------------------------------+
	' |  고객센터
	' +----------------------------------------------------------------------------------------------+*/
	$customer = $wec->get(161, null, 1);
	$total_customer = $customer1 = $customer2 = 0;
	if(is_array($customer) == true && count($customer) > 0) {
		foreach($customer as $key => $val) {
			if(gettype($customer[$key]) == 'object') {
				$customer[$key]->idx = $val->idx[0];
				$customer[$key]->stat_str = ($val->stat[0] == 103 || $val->stat[0] == 104) ? '처리완료' : '처리중';
				$customer[$key]->is_fin = ($val->stat[0] == 103 || $val->stat[0] == 104) ? 'fin' : '';
				$customer[$key]->title = stripslashes($val->title[0]);
				$customer[$key]->link = '?body=customer@list&idx='.$val->idx;

				$total_customer++;
				if($customer[$key]->stat == 1) $customer1++;
				else $customer2++;
			}
		}
	}
	$customer2_per = @ceil(($customer2/$total_customer)*100);
	$customer1_per = 100-$customer2_per;

	$qna_sdate = strtotime(date('Y-m-d 00:00:00'));
	$qna_edate = strtotime(date('Y-m-d H:i:s'));

	$total_qna = $qna1 = $qna2 = 0;
	$qres = $pdo->iterator("select * from ${tbl['qna']} where reg_date between ${qna_sdate} and ${qna_edate} and notice='N'");
    foreach ($qres as $qdata) {
		if(trim($qdata['answer'])) $qna1++;
		else $qna2++;
		$total_qna++;
	}
    $qna2_per = ($total_qna > 0) ? ceil(($qna2/$total_qna)*100) : 0;
	$qna1_per = 100-$qna2_per;


	/* +----------------------------------------------------------------------------------------------+
	' |  업데이트 공지
	' +----------------------------------------------------------------------------------------------+*/
	$article = $cxml->notice_main[0]->article;
	$notice_cnt = 0;
	if(is_array($article)) {
		foreach($article as $key => $val) {
			if($val->reg_date[0]+172800 > $now) {
				$notice_cnt++;
			} else {
				$article[$key]->is_fin = 'fin';
			}
		}
	}


	/* +----------------------------------------------------------------------------------------------+
	' |  인트라
	' +----------------------------------------------------------------------------------------------+*/
	$intra_bbs = array();
	$ires = $pdo->iterator("select date, content from $tbl[intra_schedule] order by date desc limit 5");
    foreach ($ires as $intr) {
		$intr['date'] = strtotime($intr['date'])+86399;
		$intra_bbs[$intr['date']] = array(
			'type' => 'schedule',
			'title' => strip_tags(stripslashes($intr['content'])),
			'link' => './?body=intra@main',
			'is_fin' => ($intr['date'] > $now) ? '' : 'fin',
			'reg_date' => $intr['date'],
		);
	}
	if($admin['partner_no']>0) {
		$main_w = " and `view_member` like '%@$admin[partner_no]@%'";
	}
	$ires = $pdo->iterator("select reg_date, title from $tbl[intra_board] where db='notice' $main_w order by reg_date desc limit 5");
    foreach ($ires as $intr) {
		$intr['date'] = $intr['reg_date'];
		$intra_bbs[$intr['date']] = array(
			'type' => 'notice',
			'title' => strip_tags(stripslashes($intr['title'])),
			'link' => './?body=intra@board&db=notice&mode=view&no=$intr[no]',
			'is_fin' => ($intr['date'] > $now) ? '' : 'fin',
			'reg_date' => $intr['date'],
		);
	}
	krsort($intra_bbs);
	$tmp = 0;
	foreach($intra_bbs as $key => $val) {
		$tmp++;
		if($tmp > 5) unset($intra_bbs[$key]);
	}
	$_admin_level = array(
		1 => '시스템관리자',
		2 => '최고관리자',
		3 => '부관리자',
		4 => '입점사관리자',
	);
	$admin_level = $_admin_level[$admin['level']];

	/* +----------------------------------------------------------------------------------------------+
	' |  멀티계정
	' +----------------------------------------------------------------------------------------------+*/
	$sess_id = session_id();
	$ret_url = urlencode($root_url);
	function parseAccount($data) {
		global $engine_url, $admin, $sess_id, $ret_url;

		$account_id = $data->account_id[0];
		$domain = $data->domain[0];
		$domains = $data->domains[0];
		$flag = preg_replace('/^https?:/', '', $data->flag_url[0]);
		$site_name = $data->site_name[0];

		if(!$domain && !$domains) return;
		if(!$domain) {
			$domain = explode(',', $domains);
			$domain = preg_replace("/^<|>$/", '', $domain[0]);
		}
		if(!preg_match('/^https?:\/\//', $domain)) $domain = 'http://'.$domain;
        $domain = urlencode($domain);

		if(!$flag) $flag = $engine_url.'/_manage/image/common/multi_kor.jpg';

		if($data->current[0] == 1) {
			echo "<li class='selected'><div class='btt' tooltip=\"$site_name\"><a href='#' onclick='return false;'><img src='$flag' alt=''></a></div></li>";
		} else {
            $body = urlencode($_GET['body']);
			echo "<li><div class='btt' tooltip=\"$site_name\"><a href='?body=main@sso.exe&domain=$domain&ret_url=$ret_url&nbody=$body'><img src='$flag' alt='$account_id'></a></div></li>";
		}
	}

	if(!$cfg['flag_url']) $cfg['flag_url'] = $engine_url.'/_manage/image/common/multi_kor.jpg';

	/* +----------------------------------------------------------------------------------------------+
	' |  매뉴얼
	' +----------------------------------------------------------------------------------------------+*/
	$mcode = (is_object($current_menu)) ? $current_menu->val('mcode') : null;
	$manual_link = (empty($mcode)) ? '' : 'manual/index/'.$mcode;
	$manual_toggle = ($_COOKIE['manual_opened'] == 'true') ? 'class="view_manual"' : '';

	// 업데이트 날짜 표시
	$update_checked = false;
	if(
		$admin['admin_id'] != 'wisa'
		&& file_exists($engine_dir.'/_engine/include/account/getMngUrl.inc.php') == true
		&& file_exists($engine_dir.'/update.txt') == true
	) {
		$update_date = file($engine_dir.'/update.txt');
		$update_date = trim($update_date[0]);

		$update_date_now = $pdo->row("select value from {$tbl['default']} where code='update_date'");
		if($update_date  > $update_date_now) {
			$update_checked = true;
			if($update_date_now > 0) $pdo->query("update {$tbl['default']} set value='$update_date' where code='update_date'");
			else $pdo->query("insert into {$tbl['default']} (code, value, ext) values ('update_date', '$update_date', '$now')");
		}

		$update_date = date('Y. m. d', strtotime($update_date));
	}

	$pgcode_big = '';
	if(is_object($current_big) == true) {
		$pgcode_big = $current_big->attributes();
		$pgcode_big = (string)$pgcode_big->pgcode;
	}

?>
<?php if($body != 'main@main') { ?>
<style type="text/css" title="">
body {background:url('<?=$engine_url?>/_manage/image/common/line.gif') repeat-y left top #e8e8e8;}
</style>
<?php } ?>
<div id="admin_content" <?=$manual_toggle?>>
	<div id="container">
		<header id="adminHeader">
			<div class="logo">
				<h1><a href="/_manage/?body=main@main">WISA. WING</a></h1>
			</div>
			<div class="menu">
				<div class="area">
					<div class="multi">
						<a href="http://www.<?=$hp_dom?>/mypage/addAccount/step1" target="_blank" class="add btt" tooltip="멀티샵 추가">멀티샵 추가</a>
						<ul class="list">
							<?php
								if(is_array($_SESSION['myAccounts'])) {
									foreach($_SESSION['myAccounts'] as $val) {
										parseAccount($val);
									}
									if(count($_SESSION['myAccounts']) < 13) $countshop = "hidden";
								}
							?>
						</ul>
						<span class="more btt <?=$countshop?>" tooltip="멀티샵 더보기" onclick="multi_more(this)">멀티샵 더보기</span>
					</div>
					<div class="quick">
						<div class="intra">
							<a class="view_layer" alt="인트라넷" title="인트라넷"><?=$admin['name']?>님</a>
							<div class="box box_intra">
								<div class="profile">
									<p class="name"><?=$admin['name']?></p>
                                    <?php if ($admin['level'] < 4) { ?>
									<p class="level"><?=$admin_level?> <a href="?body=intra@my_info">수정</a></p>
                                    <?php } ?>
								</div>
								<div class="btn">
                                    <?php if ($admin['level'] > 3) { ?>
									<a href="./?body=4010" class="more">게시판</a>
                                    <?php } else { ?>
									<a href="./?body=intra@main" class="more">인트라넷</a>
                                    <?php } ?>
									<a href="./?body=main@logout.exe">로그아웃</a>
								</div>
							</div>
						</div>
						<div class="icon shortcut_pc">
							<a href="<?=$root_url?>" target="_blank" alt="PC 쇼핑몰 보기" title="PC 쇼핑몰 보기"><span class="xi-desktop"></span></a>
						</div>
						<?php if($cfg['mobile_use'] == 'Y') { ?>
						<div class="icon shortcut_mobile">
							<a href="#" onclick="window.open('<?=$m_root_url?>', 'mobile_web', 'width=520px, height=900px, left=10px, top=10px'); return false;" target="_blank" alt="모바일 쇼핑몰 보기" title="모바일 쇼핑몰 보기"><span class="xi-mobile"></span></a>
						</div>
						<?php } ?>
						<div class="icon tsearch">
							<a href="#search" class="search" onclick="tsearchOpen(true); return false;" alt="통합검색" title="통합검색"><span class="xi-search"></span></a>
						</div>
						<div class="icon more">
							<a class="view_layer" alt="더보기" title="더보기"><span class="xi-apps"></span> <?php if($notice_cnt > 0) { ?><span class="count"><?=$notice_cnt?></span><?php } ?></a>
							<div class="box box_more">
								<h2>바로가기</h2>
								<ul>
									<?php if($admin['level'] < 4) {?><li><a onclick="goMywisa('?body=customer@list'); return false;" class="customer">1:1 고객센터</a></li><?php } ?>
									<li><a href="http://redirect.wisa.co.kr/notice/notice" target="_blank" class="notice">공지, 업데이트 <?php if($notice_cnt > 0) { ?><span class="count"><?=$notice_cnt?></span><?php } ?></a></li>
									<?php if($admin['level'] < 4) { ?><li><a onclick="goMywisa('?body=wing@main'); return false;" class="wingstore">윙스토어</a></li><?php } ?>
									<li><a href="#" onclick="window.open('http://redirect.wisa.co.kr/couponshop', 'couponshop', 'width=780px, height=900px, left=10px, top=10px'); return false;" class="mchange" target="_blank">모바일 교환권</a></li>
									<li><a onclick="toggleManual(false, 1); return false;" class="help">도움말</a></li>
								</ul>
							</div>
						</div>
					</div>
				</div>
				<nav class="category">
					<ul class="list">
						<?php if($admin['level'] == 4) { ?>
						<li><a href="./?body=1010"<?=$pageOver[4]?>>설정</a></li>
						<li><a href="./?body=2010"<?=$pageOver[0]?>>상품관리</a></li>
						<li><a href="./?body=3010"<?=$pageOver[1]?>>주문배송</a></li>
						<li><a href="./?body=4010"<?=$pageOver[5]?>>게시판</a></li>
						<li><a href="./?body=5010"<?=$pageOver[6]?>>매출정산</a></li>
						<li><a href="./?body=14110"<?=$pageOver[11]?>>재고관리</a></li>
						<?php } else { ?>
						<li><a href="./?body=1010"<?=$pageOver[4]?>>설정</a></li>
						<li><a href="./?body=2120"<?=$pageOver[0]?>>상품관리</a></li>
						<li><a href="./?body=3010"<?=$pageOver[1]?>>주문배송</a></li>
						<li><a href="./?body=5010"<?=$pageOver[2]?>>고객CRM</a></li>
						<li><a href="./?body=6010"<?=$pageOver[5]?>>게시판</a></li>
						<li><a href="./?body=<?=($cfg['design_version'] != "V3") ? "7020" : "7010";?>"<?=$pageOver[3]?>>디자인</a></li>
						<li><a href="./?body=<?=($cfg['mobile_use']=='Y' ? 15050 : 15010)?>"<?=$pageOver[12]?>><?=$cfg['mobile_name']?></a></li>
						<li><a href="./?body=9010"<?=$pageOver[9]?>>프로모션</a></li>
						<li><a href="./?body=10410"<?=$pageOver[8]?>>광고마케팅</a></li>
						<li><a href="./?body=4010"<?=$pageOver[6]?>>매출정산</a></li>
						<li><a href="./?body=8110"<?=$pageOver[7]?>>접속통계</a></li>
						<?php if(is_dir($engine_dir.'/_manage/erp')){ ?>
						<li><a href="./?body=14110"<?=$pageOver[11]?>>재고관리</a></li>
						<?php } ?>
						<!--
						<li><a href="./?body=16010"<?=$pageOver[13]?>>스토어</a></li>
						-->
						<?php } ?>
					</ul>
					<div class="favorite">
						<a class="view_layer p_cursor btt" tooltip="퀵메뉴 등록하기" onmouseout="hideToolTip();"></a>
						<div class="box">
							<h2>퀵메뉴</h2>
							<div id="quickMenuSearch">
								<input type="text" id="qmFrom" class="input" value="메뉴 자동검색" onfocus="qmFocus(this,1)" onblur="qmFocus(this,2)" onkeyup="qmSearch(this,event)">
								<ul id="quickSearchList">
								</ul>
							</div>
							<ul class="quickMenu">
								<?=$qm_list?>
							</ul>
							<span class="arrow"></span>
						</div>
					</div>
				</nav>
			</div>
		</header>
		<section id="wrapper">
			<article id="contentArea">
				<?php
					if($_SESSION['partner_login_no'] > 0) {?>
					<div class="box_middle2"><span class="box_btn_s blue"><input type="button" value="본사 관리자로 복귀" onclick="goM('main@logout.exe');"></div>
					<?php }

					if($_inc[0] != "main") {
						$wec_acc = new weagleEyeClient($_we, 'Etc');
						$_online_manual = $wec_acc->call('movieManualExists', array('mcode'=>$current_menu->mcode[0]));
                        $_online_manual = json_decode($_online_manual);

						if($body == 'order@order_list') include $engine_dir.'/_manage/order/order_top.inc.php';
						else include $engine_dir.'/_manage/main/common_top.inc.php';
					}
					include $body_file;

					if($admin['level'] == 4) {
						echo "<br><br>";
					}else{
				?>
				<div class="solving">
					<div class="title">
						<h2 class="solving_toggle">이용에 불편하시면 고객센터에서 도와드려요</h2>
					</div>
					<div class="solving_contents box_bottom" style="display:none;">
						<ol class="list">
							<li class="cell">
								<div class="box">
									<h3>문제해결 1</h3>
									<div class="content">
										<p>보고계신 페이지가 궁금하시다면<br>자주묻는 질문과 답변, 매뉴얼이<br>준비되어있습니다.</p>
										<?php if(!empty($_manual)) { ?>
										<span class="box_btn_s blue"><a href="http://help.wisa.co.kr/manual/index/<?=$current_menu->mcode[0]?>" target="_blank"><?=$current_menu->name[0]?>에 관한 도움말 보기</a></span>
										<?php } else { ?>
										<span class="box_btn_s blue"><a href="#" onclick="toggleManual('always', 3); return false;">도움말 보기</a></span>
										<?php } ?>
									</div>
								</div>
							</li>
							<li class="cell">
								<div class="box">
									<h3>문제해결 2</h3>
									<div class="content">
										<p>도움말 보기에서 해결이 안되신다면<br>전화보다 더 빠르고 정확한<br>1:1 고객센터를 이용해보세요.</p>
										<span class="box_btn_s blue"><a href="#" onclick="goMywisa('?body=customer@write&ref=wing&mcode=<?=$current_menu->mcode?>'); return false;">지금 페이지관련 문의</a></span>
									</div>
								</div>
							</li>
							<li class="cell">
								<div class="box">
									<h3>문제해결 3</h3>
									<div class="content">
										<p class="ars"><span>1599-4435</span>평일 09:30~17:30 (점심시간 12~13시)</p>
										<ol class="number">
											<li>ㆍ쇼핑몰운영지원</li>
											<li>ㆍ디자인유지보수</li>
											<li>ㆍ광고&마케팅관리</li>
										</ol>
									</div>
								</div>
							</li>
							<li class="cell">
								<div class="box">
									<h3>문제해결 4</h3>
									<?php if(!$_SESSION['wisa_manager']['name']){ ?>
									<div class="content">
										<p>지속관리기업 고객이시라면<br>1:1담당 매니저를 찾아주세요.<br><br></p>
										<!--<span class="box_btn_s blue"><a href="">지속관리기업 서비스란?</a></span>-->
									</div>
									<?php } else { ?>
									<div class="content manager">
										<div class="img"><img src="<?=$_SESSION['wisa_manager']['photo']?>" alt=""></div>
										<dl>
											<dt>매니저</dt>
											<dd><?=$_SESSION['wisa_manager']['name']?> <?=$_SESSION['wisa_manager']['pos']?></dd>
											<dd class="tel"><img src="<?=$engine_url?>/_manage/image/common/icon_tel.png" alt="전화번호"> | <?=$_SESSION['wisa_manager']['phone']?></dd>
										</dl>
									</div>
									<?php } ?>
								</div>
							</li>
						</ol>
					</div>
				</div>
				<?php } ?>
			</article>
		</section>
		<nav id="navigation">
			<?php include $engine_dir."/_manage/menu/menu.php"; ?>
		</nav>
		<div id="btn_scroll">
			<a id="scroll_top"><img src="<?=$engine_url?>/_manage/image/common/btn_top.png" alt="최상위" onmouseover="imgOver(this)" onmouseout="imgOver(this,'out')"></a>
			<a id="scroll_bottom"><img src="<?=$engine_url?>/_manage/image/common/btn_down.png" alt="최하단" onmouseover="imgOver(this)" onmouseout="imgOver(this,'out')"></a>
		</div>
	</div>
	<div id="manual_content" class="right_navigator">
		<a href="https://help.wisa.co.kr/<?=$manual_link?>" class="link" target="_blank">새창열기</a>
		<a onclick="toggleManual(); return false;" class="close">닫기</a>
		<div class="content">
			<iframe id="manualWIndow" src="" width="100%" height="100%" scrolling="yes" frameborder="0"></iframe>
		</div>
	</div>
	<div id="total_search" class="right_navigator">
		<?PHP
			include 'main/search.inc.php';
		?>
	</div>
</div>
<script>
var _MANUAL_LINK_ = '<?=$manual_link?>';
var _MANUAL_PGCODE_ = '<?=$pgcode_big?>';
</script>

<?php if(preg_match("/^log@.*/", $body)){ ?>
<!-- 에이스카운터 -->
<?PHP
	if($cfg['ace_counter_Ver'] == 2) {
		$wec = new weagleEyeClient($_we, 'account');
		$str = $wec->call('getAcecounterLoginHash', array('acecounter_id'=>$cfg['ace_counter_id']));
		?>
		<form name="acForm" method="post" action="" target="">
			<input type="hidden" name="r" value="<?=$str?>">
		</form>
		<?php
	} else {
		?>
		<form name="acForm" method="post" action="" target="">
			<input type="hidden" name="id" value="<?=$cfg['ace_counter_id']?>">
			<input type="hidden" name="pw" value="<?=$cfg['ace_counter_pwd']?>">
		</form>
		<?php
	}
}?>

<script type="text/javascript">
	/*
	var	ts = new R2TS('customerNotice', 'ts', 10, 150);
	var	tst = new R2TS('topSNotice', 'tst', 10, 150);
	onloaded = true;
	*/
	// 검색인풋 토글
	function toggle_shadow() {
		if ($('.select_input').hasClass('shadow')){
			$('.select_input').removeClass('shadow');
		} else {
			$('.select_input').addClass('shadow');
		}
	}

	$(document).ready(function() {
		// 위로가기
		$('#scroll_top').click(function(){
			$('html, body').animate({scrollTop:0}, 'slow');
			return false;
		});
		// 아래로가기
		$('#scroll_bottom').click(function(){
			$('html, body').animate({scrollTop:$(document).height()}, 'slow');
			return false;
		});
		// 레이어 닫기
		$(document).mouseup(function (e){
			var container = $('.multi');
			var list = $('.multi .list');
			if(container.has(e.target).length == 0) {
				list.removeClass('full');
				list.parent().find('.more').removeClass('show');
			}
		});
		var totalWidth = 0;
		$('#controlTab .tabs-wrap .tabs > *').each(function(index) {
			totalWidth += parseInt($(this).outerWidth(), 10);
		});

		$('[class*="scroll-"]').click(function(){
			var scrollMove;
			var scrollElement;
			if($(this).data('element')) scrollElement = $(this).data('element');
			else if($(this).attr('href')) scrollElement = $(this).attr('href');

			if($(this).hasClass('scroll-right') == true) scrollMove = $(scrollElement).prop('clientWidth');
			else scrollMove = 0;
			$($(this).data('element')).animate({scrollLeft:scrollMove}, 'fast');

			return false;
		});
	});

	// 스크롤 이동
	function move_category(obj) {
		var pos = $(obj).offset();
		var extra_space = 0;
		var duration = "400";
		$('html, body').animate({scrollTop : pos.top - extra_space}, duration);
	}

	// 멀티샵 더보기
	function multi_more(obj){
		var multi_list = $('.multi .list');
		if (multi_list.hasClass('full')) {
			multi_list.removeClass('full');
			$(obj).removeClass('show');
		} else {
			multi_list.addClass('full');
			$(obj).addClass('show');
		}
	}

	// 탑메뉴 토글
	$('.view_layer').click(function(){
		$('#version_alarm').fadeOut(200);
		var box = $(this).parent().find('.box');
		if (box.css('display') == 'block') {
			box.fadeOut(200);
			box.removeClass('view_layer_toggle');
			$(this).removeClass('selected');
		} else {
			$('.quick').find('.box').hide();
			box.fadeIn(200);
			box.addClass('view_layer_toggle');
			$(this).addClass('selected');
		}
	});

	// 다른데 클릭시 토글 숨김
	$(document).click(function(e){
		$('.view_layer').each(function(){
			var box = $(this).parent().find('.box');
			if (box.css('display') == 'block') {
				if(e.target != ""){
					if (!$('.view_layer').has(e.target).length && !$('.view_layer_toggle').has(e.target).length) {
						box.fadeOut(200);
						box.removeClass('view_layer_toggle');
						$(this).removeClass('selected');
					}
				}else{
					if(e.target.className.indexOf('view_layer') == -1){
						box.fadeOut(200);
						box.removeClass('view_layer_toggle');
						$(this).removeClass('selected');
					}
				}
			}
		});
	});

	// 하단 고객센터 토글
	$(".solving_toggle").click(function(){
		if ($('.solving_contents').css('display') == 'block') {
			$('.solving_contents').slideUp('fast');
			$('.solving_toggle').removeClass('selected');
		} else {
			$('.solving_contents').slideDown('fast');
			$('.solving_toggle').addClass('selected');
			move_category('.solving');
		}
	});

	$(function(){
		setDatepicker();

		$('.R2Tip').mouseover(function() {
			new R2Tip(this, this.alt, null, event);
		});

		$('.btt').btt('tooltip_square');

		// 금액필드 자동 컴마
		$('.input_won').bind({
			'focus' : function() {
				this.value = removeComma(this.value);
				if($(this).attr('data-decimal') && !parseInt($(this).attr('data-decimal'))) this.value = removeDot(this.value);
			},
			'blur' : function() {
				this.value = setComma(this.value);
				if($(this).attr('data-decimal') && !parseInt($(this).attr('data-decimal'))) this.value = removeDot(this.value);
			},
			'keyup' : function() {
				if($(this).attr('data-decimal') && !parseInt($(this).attr('data-decimal'))) this.value = removeDot(this.value);

				// 환율 계산
				if($(this).attr('data-type')){
					var _type = $(this).attr('data-type');
					var _manage_price = removeComma("<?=$cfg['cur_manage_price']?>");
					var _sell_price = removeComma("<?=$cfg['cur_sell_price']?>");
					var _name = $(this).attr('name');

					if(_type=='sell'){
						var price = (removeComma(this.value)/_sell_price) * _manage_price;
						if($('input[name="m_'+_name+'"]').attr('data-decimal') && !parseInt($('input[name="m_'+_name+'"]').attr('data-decimal'))) price = Math.round(price);
						price= setComma(price);

						if($('input[name="m_'+_name+'"]').attr('data-decimal') && !parseInt($('input[name="m_'+_name+'"]').attr('data-decimal'))) price = removeDot(price);

						$('input[name="m_'+_name+'"]').val(price);
					}else{
						var price = (removeComma(this.value)/_manage_price) * _sell_price;

						_name = _name.replace('m_','');
						if($('input[name="'+_name+'"]').attr('data-decimal') && !parseInt($('input[name="'+_name+'"]').attr('data-decimal'))){
							price = Math.round(removeDot(price));
						}else{
							price = price.toFixed($('input[name="'+_name+'"]').attr('data-decimal'));
						}
						price= setComma(price);

						$('input[name="'+_name+'"]').val(price);
					}
				}
			}
		}).each(function() {
			if($(this).attr('data-decimal') && !parseInt($(this).attr('data-decimal'))) this.value = Math.floor(removeComma(this.value));
			this.value = setComma(this.value);
		});

        $('.tooltip_trigger').each(function() {
            var trigger = $(this);
            var tooltip = $('.'+trigger.data('child'));
            if(tooltip.length == 1) {
                trigger.on('click', function() {
                    if(tooltip.css('display') == 'none') {
                        tooltip.css({'top': trigger.position().top, 'left': (trigger.position().left+trigger.width()+5)});
                        tooltip.fadeIn('fast');
                    } else {
                        tooltip.fadeOut('fast');
                    }
                    return false;
                });
            }
        });
        $('.tooltip_closer').click(function() {
            $(this).parents('.info_tooltip').fadeOut('fast');
            return false;
        });
	});
</script>
<ul class="list_msg">
	<?=$elstr?>
</ul>

</body>
</html>