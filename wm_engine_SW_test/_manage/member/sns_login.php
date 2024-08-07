<?PHP

	/* +----------------------------------------------------------------------------------------------+
	' |  sns 로그인 설정
	' +----------------------------------------------------------------------------------------------+*/
	if(!isTable($tbl['sns_join'])) {
		include_once $engine_dir.'/_config/tbl_schema.php';
		$pdo->query($tbl_schema['sns_join']);
	}


	addField($tbl['member'],'login_type',"varchar(20) NOT NULL COMMENT 'sns타입'");

	$sns_login_list = array(
		'payco_login_use',
		'naver_login_use',
		'facebook_login_use',
		'kakao_login_use',
		'wonder_login_use',
		'apple_login_use',
	);
	foreach($sns_login_list as $_name) {
		$_nm = preg_replace('/_.*$/', '', $_name);
		${'on_'.$_nm} = (isset($cfg[$_name]) == true && $cfg[$_name] != 'N') ? 'on' : 'off';
	}

	//위메프로그인 폴더 생성
	if(!file_exists($root_dir.'/_data/compare/wonder/redirect.php')) {
		include_once $engine_dir.'/_engine/include/file.lib.php';
		makeFullDir('_data/compare/wonder');

		$feed = "<?PHP
			\$urlfix = 'Y';
			include '../../../_config/set.php';
			include \$engine_dir.'/_engine/member/wonder_apijoin.exe.php';
		?>";

		$fp = fopen($root_dir.'/_data/compare/wonder/redirect.php', 'w');
		if($fp) {
			fwrite($fp, $feed);
			fclose($fp);
		}
	}
	$processing_url = urlencode('http://wonder.wisa.co.kr/?redirect_url='.$manage_url.'/_data/compare/wonder/redirect.php');
	$wec_etc = new weagleEyeClient($GLOBALS['_we'], 'etc');
	$result = $wec_etc->call('getWonderKey');
	$wisa_wonder = json_decode($result, true);

	$wec_app = new weagleEyeClient($_we, 'push');
	$app = $wec_app->call('appStatus');
	$app = json_decode($app,true);
	if(isset($app['stat']) == false) $app['stat'] = 0;

    $scfg->def('kakaoSync_StoreKey', md5(time()));
    $scfg->def('kakao_autologin_use', 'N');
    $scfg->def('kakao_mailing_use', 'N');
    $scfg->def('kakao_login_y_type', '');

?>
<script type="text/javascript">
const use_magicapp = <?=$app['stat']?>;
</script>
<div id="sns_login">
	<div class="box_title first">
		<h2 class="title">SNS로그인 설정</h2>
	</div>
	<div class="box_tab first tablist">
		<ul>
			<li><a href="#tab1" onclick="tabview(1,'sl')" class="active">네이버<span class="toggle <?=$on_naver?>"><?=strtoupper($on_naver)?></span></a></li>
			<li><a href="#tab3" onclick="tabview(3,'sl')">카카오<span class="toggle <?=$on_kakao?>"><?=strtoupper($on_kakao)?></span></a></li>
			<li><a href="#tab2" onclick="tabview(2,'sl')">페이스북<span class="toggle <?=$on_facebook?>"><?=strtoupper($on_facebook)?></span></a></li>
			<li><a href="#tab5" onclick="tabview(5,'sl')">애플<span class="toggle <?=$on_apple?>"><?=strtoupper($on_apple)?></span></a></li>
			<li><a href="#tab0" onclick="tabview(0,'sl')">페이코<span class="toggle <?=$on_payco?>"><?=strtoupper($on_payco)?></span></a></li>
			<li><a href="#tab4" onclick="tabview(4,'sl')">위메프<span class="toggle <?=$on_wonder?>"><?=strtoupper($on_wonder)?></span></a></li>
		</ul>
	</div>
	<div class="tab_box_sl sl0">
		<form name="paycologinFrm"  method="post" action="<?=$_SERVER['PHP_SELF']?>" onSubmit="return paycoFormCheck(this);" target="hidden<?=$now?>">
			<input type="hidden" name="body" value="config@config.exe">
			<input type="hidden" name="config_code" value="payco_login">
			<div class="box_sort left">
				<i class="icon_info"></i> <span class="explain">페이코 결제서비스 이용 시에만 신청 가능합니다.</span>
			</div>
			<table class="tbl_row">
				<caption class="hidden">페이코 아이디 로그인 설정</caption>
				<colgroup>
					<col style="width:15%">
				</colgroup>
				<tr>
					<th scope="row">페이코로그인 사용</th>
					<td>
						<label class="p_cursor"><input type="radio" id="payco_login_use_y" name="payco_login_use" value="Y" <?=checked($cfg['payco_login_use'] ,'Y')?>> 사용함</label>
						<label class="p_cursor"><input type="radio" id="payco_login_use_n" name="payco_login_use" value="N" <?=checked($cfg['payco_login_use'] ,'N').checked($cfg['payco_login_use'],"")?>> 사용안함</label>
					</td>
				</tr>
				<tr>
					<th scope="row">Client ID</th>
					<td>
						<input type="text" name="payco_login_client_id" value="<?=$cfg['payco_login_client_id']?>" class="input" size="50" >
					</td>
				</tr>
				<tr >
					<th scope="row">Secret Key</th>
					<td>
						<input type="text" name="payco_login_client_secret" value="<?=$cfg['payco_login_client_secret']?>" class="input"  size="50">
					</td>
				</tr>
			</table>
			<div class="box_middle2 left">
				<div class="summary_sns">
                    <p class="title">페이코 로그인 Client ID, Secret Key 얻는 방법</p>
                    <ol>
                        <li>1) <a href="https://developers.payco.com/ " target="_blank" class="p_color">https://developers.payco.com/</a> 접속 후 로그인합니다.</li>
                        <li>
                            2) 상단 메뉴 중 애플리케이션 관리 -> 애플리케이션 등록을 합니다.
                            <ul>
                                <li>- 애플리케이션 이름 : 쇼핑몰명으로 등록을 권장드립니다.</li>
                                <li>- 플랫폼 : 웹사이트로 선택 후 대표 도메인주소를 등록합니다.</li>
                                <li>- Callback URL : <?=$root_url?>/main/exec.php</li>
                            </ul>
                        </li>
                        <li>4) 애플리케이션이 등록되면, 추가된 내 애플리케이션 -> 개요에서 Client ID 및 Secret Key를 확인할 수 있습니다.</li>
                        <li>5) 위사 관리자에서 Client ID 및 Secret Key 등록합니다.</li>
                        <li>※ 등록 후 실제 서비스 적용까지 최대 1시간 정도 소요될 수 있습니다.</li>
                    </ol>
                </div>
			</div>
			<div class="box_bottom">
				<span class="box_btn blue"><input type="submit" value="확인"></span>
			</div>
		</form>
	</div>
	<div class="tab_box_sl sl1">
		<form name="naverloginFrm"  method="post" action="<?=$_SERVER['PHP_SELF']?>" onSubmit="return naverFormCheck(this);" target="hidden<?=$now?>">
			<input type="hidden" name="body" value="config@config.exe">
			<input type="hidden" name="config_code" value="naver_login">
			<div class="box_sort left">
			</div>
			<table class="tbl_row">
				<caption class="hidden">네이버 아이디 로그인 설정</caption>
				<colgroup>
					<col style="width:15%">
				</colgroup>
				<tr>
					<th scope="row">네이버로그인 사용</th>
					<td>
						<label class="p_cursor"><input type="radio" id="naver_login_use_y" name="naver_login_use" value="Y" <?=checked($cfg['naver_login_use'] ,'Y')?>> 사용함</label>
						<label class="p_cursor"><input type="radio" id="naver_login_use_n" name="naver_login_use" value="N" <?=checked($cfg['naver_login_use'] ,'N').checked($cfg['naver_login_use'],"")?>> 사용안함</label>
					</td>
				</tr>
				<tr>
					<th scope="row">클라이언트 이름</th>
					<td>
						<input type="text" name="naver_login_client_name" value="<?=$cfg['naver_login_client_name']?>" class="input" size="50" >
					</td>
				</tr>
				<tr>
					<th scope="row">Client ID</th>
					<td>
						<input type="text" name="naver_login_client_id" value="<?=$cfg['naver_login_client_id']?>" class="input" size="50" >
					</td>
				</tr>
				<tr >
					<th scope="row">Client Secret</th>
					<td>
						<input type="text" name="naver_login_client_secret" value="<?=$cfg['naver_login_client_secret']?>" class="input"  size="50">
					</td>
				</tr>
			</table>
			<div class="box_middle2 left">
				<div class="summary_sns">
					<p class="title">네이버 로그인 Client ID, Client Secret 얻는 방법</p>
					<ol>
						<li>1) <a href="https://developers.naver.com/" target="_blank" class="p_color">https://developers.naver.com/</a> 접속 후 로그인</li>
						<li>2) 상단 Application &gt; 애플리케이션 등록 (이미 애플리케이션 등록이 되어있다면 '내 애플리케이션'에서 등록된 애플리케이션 선택)</li>
							<li>3) 등록 절차에 따라 애플리케이션 등록</li>
							<ul>
								<li>- 애플리케이션 이름(쇼핑몰명)</li>
								<li>- 사용 API : 네이버 로그인</li>
								<li>- 로그인 오픈 API 서비스 환경 : PC 웹 / Mobile 웹</li>
								<li>- PC 서비스URL( 예 - <?=$root_url?> )</li>
								<li>- PC Callback URL( 예 - <?=$root_url?>/main/exec.php?exec_file=promotion/naver_callback.exe.php )</li>
								<li>- Mobile 서비스URL( 예 -<?=$m_root_url?> )</li>
								<li>- Mobile Callback URL( 예 - <?=$m_root_url?>/main/exec.php?exec_file=promotion/naver_callback.exe.php )</li>
							</ul>
							<li>Callback URL은 꼭 도메인/main/exec.php?exec_file=promotion/naver_callback.exe.php 을 입력해주세요.</li>
					</ol>
				</div>
			</div>
			<div class="box_bottom">
				<span class="box_btn blue"><input type="submit" value="확인"></span>
			</div>
		</form>
	</div>
	<div class="tab_box_sl sl2">
		<form name="facebookloginFrm"  method="post" action="<?=$_SERVER['PHP_SELF']?>" onSubmit="return faceFormCheck(this);" target="hidden<?=$now?>">
			<input type="hidden" name="body" value="config@config.exe">
			<input type="hidden" name="config_code" value="facebook_login">
			<table class="tbl_row">
				<caption class="hidden">페이스북 로그인 설정</caption>
				<colgroup>
					<col style="width:15%">
				</colgroup>
				<tr>
					<th scope="row">페이스북로그인 사용</th>
					<td>
						<label class="p_cursor"><input type="radio" id="facebook_login_use_y" name="facebook_login_use" value="Y" <?=checked($cfg['facebook_login_use'] ,'Y')?>> 사용함</label>
						<label class="p_cursor"><input type="radio" id="facebook_login_use_n" name="facebook_login_use" value="N" <?=checked($cfg['facebook_login_use'] ,'N').checked($cfg['facebook_login_use'],"")?>> 사용안함</label>
						<?if($_SERVER['HTTPS'] != 'on' && $_SERVER['apple_login_use'] != 'Y') {?>
						<span class="msg_bubble warning">보안서버를 이용 중일때만 사용하실수 있습니다. <a href="/_manage/?body=config@ssl" target="_blank">설정하기</a></span>
						<?}?>
					</td>
				</tr>
				<tr class="facebook_confirm">
					<th scope="row">App ID</th>
					<td>
						<input type="text" name="facebook_id" value="<?=$cfg['facebook_id']?>" class="input" size="50">
					</td>
				</tr>
			</table>
			<div class="box_middle2 left">
                <a href="https://r.wisa.co.kr/login_facebook" target="_blank">
                    <img src="<?=$engine_url?>/_manage/image/shortcut2.gif">
                    <strong>페이스북 로그인 설정 안내</strong>
                </a>
			</div>
			<div class="box_bottom">
				<span class="box_btn blue"><input type="submit" value="확인"></span>
			</div>
		</form>
		<?if($_SERVER['HTTPS'] != 'on' && $_SERVER['facebook_login_use'] != 'Y') {?>
		<script type="text/javascript">
			$('[name=facebook_login_use][value=Y]').prop('disabled', true);
		</script>
		<?}?>
	</div>
	<div class="tab_box_sl sl3">
		<form name="kakaologinFrm"  method="post" action="<?=$_SERVER['PHP_SELF']?>" onSubmit="return kakaoFormCheck(this);" target="hidden<?=$now?>" enctype="multipart/form-data">
			<input type="hidden" name="body" value="member@kakao_sync.exe">
			<input type="hidden" name="config_code" value="kakao_login">

			<div class="box_sort left">
				<i class="icon_info"></i> <span class="explain">SNS공유하기 연동(카카오링크)을 사용하는 경우 동일한 JavaScript 키를 사용해야 됩니다.<a href="?body=promotion@sns_list" class="p_color">바로가기
				</a></span>
			</div>
			<table class="tbl_row">
				<caption class="hidden">카카오 로그인 설정</caption>
				<colgroup>
					<col style="width:15%">
				</colgroup>
				<tr>
					<th scope="row">카카오로그인 사용</th>
					<td>
                        <?php if ($cfg['kakao_login_use'] == 'Y') { ?>
						<label class="p_cursor"><input type="radio" id="kakao_login_use_y" name="kakao_login_use" value="Y" <?=checked($cfg['kakao_login_use'] ,'Y')?>> 카카오 로그인</label>
                        <?php } ?>
						<label class="p_cursor"><input type="radio" id="kakao_login_use_s" name="kakao_login_use" value="S" <?=checked($cfg['kakao_login_use'] ,'S')?>> 카카오싱크</label>
						<label class="p_cursor"><input type="radio" id="kakao_login_use_n" name="kakao_login_use" value="N" <?=checked($cfg['kakao_login_use'] ,'N').checked($cfg['kakao_login_use'],"")?>> 사용안함</label>
					</td>
				</tr>
                <tr class="kakao_login_use_s">
					<th scope="row">storeKey</th>
					<td>
                        <input type="text" name="kakaoSync_StoreKey" value="<?=$scfg->get('kakaoSync_StoreKey')?>" class="input readOnly" size="50" readonly>
					</td>
                </tr>
                <tr class="kakao_login_use_s kakao_login_use_y">
					<th scope="row">JavaScript 키</th>
					<td>
						<input type="text" name="kakao_sns_id" value="<?=$cfg['kakao_sns_id']?>" class="input " size="50">
					</td>
				</tr>
                <tr class="kakao_login_use_s kakao_login_use_y">
					<th scope="row">REST API 키</th>
					<td>
						<input type="text" name="kakao_rest_api" value="<?=$cfg['kakao_rest_api']?>" class="input readOnly" size="50">
					</td>
                </tr>
                <tr>
                    <th scope="row">자동 로그인</th>
                    <td>
                        <label><input type="radio" name="kakao_autologin_use" value="Y" <?=checked($cfg['kakao_autologin_use'], 'Y')?>> 사용함</label>
                        <label><input type="radio" name="kakao_autologin_use" value="N" <?=checked($cfg['kakao_autologin_use'], 'N')?>> 사용안함</label>
                        <ul class="list_info">
                            <li>이 설정을 사용할 경우 카카오 앱에서 접속 시 다른 계정이 있더라도 현재 카카오 앱과 연결된 아이디로 자동 로그인 됩니다.</li>
                            <li>카카오아이디로 로그인 되어있지 않을 경우 카카오 아이디로 자동 SNS가입 되며, 이메일 주소나 휴대폰 번호가 같은 다른 아이디가 있을 경우 아이디 통합화면으로 이동됩니다.</li>
                        </ul>
                    </td>
                </tr>
                <tr>
                    <th scope="row">광고성<br>SMS 및 이메일 동의 </th>
                    <td>
                        <label><input type="radio" name="kakao_mailing_use" value="Y" <?=checked($cfg['kakao_mailing_use'], 'Y')?>> 사용함</label>
                        <label><input type="radio" name="kakao_mailing_use" value="N" <?=checked($cfg['kakao_mailing_use'], 'N')?>> 사용안함</label>
                    </td>
                </tr>
                <tr class="kakao_login_use_y">
					<th scope="row">연동 방식</th>
					<td>
                        <input type="radio" name="kakao_login_y_type" value="" <?=checked($cfg['kakao_login_y_type'], '')?>> 팝업
                        <input type="radio" name="kakao_login_y_type" value="self" <?=checked($cfg['kakao_login_y_type'], 'self')?>> 현재창
					</td>
                </tr>
                <tr class="kakao_login_use_s">
                    <th scope="row">사이트 로고</th>
                    <td>
                        <input type="file" name="kakao_site_logo">
                        <?php if ($scfg->comp('kakao_site_logo') == true) { ?>
                        <img src="/_data/config/<?=$cfg['kakao_site_logo']?>" style="max-height:100px">
                        <?php } ?>
                    </td>
                </tr>
                <tr class="kakao_login_use_s">
                    <th scope="row">카카오톡 채널 프로필 ID</th>
                    <td>
                        <input type="text" name="kakao_channel_public_id" class="input" size="10" value="<?=$scfg->get('kakao_channel_public_id')?>">
                        <ul class="list_info">
                            <li>채널 프로필 ID는 카카오톡 채널 기능이 필요할 경우 입력합니다.</li>
                            <li>카카오비즈니스
                                <a href="https://business.kakao.com/dashboard/" target="_blank">
                                    https://business.kakao.com/dashboard/
                                </a>
                                에 접속 후 채널을 선택합니다.
                            </li>
                            <li>
                                브라우저의 주소창을 확인하여 프로필 아이디를 획득합니다.
                                (https://center-pf.kakao.com/<strong>프로필아이디</strong>/dashboard)
                            </li>
                        </ul>
                    </td>
                </tr>
			</table>
			<div class="box_middle2 left kakao_login_use_y">
				<div class="summary_sns">
					<p class="title">카카오톡 JavaScript 키 얻는 방법</p>
					<ol>
						<li>1) <a href="https://developer.kakao.com/" target="_blank" class="p_color">https://developer.kakao.com/</a> 접속 후 로그인</li>
						<li>
							2) 상단 바 또는 왼쪽 바의 앱 만들기( 이미 애플리케이션 등록이 되어있다면 등록 된 애플리케이션 선택 )
							<ul>
								<li>- 이름(쇼핑몰명)</li>
								<li>- 플랫폼 추가( 왼쪽 바 설정-일반 ): 플랫폼은 일반적으로 웹을 선택해주세요.</li>
								<li>- 플랫폼 추가( 왼쪽 바 설정-일반 ): 사이트 도메인(예 - <?=$root_url?>)</li>
								<li>- JavaScript 키 복사</li>
								<li>- 플랫폼 추가를 통해 도메인을 꼭 등록해주셔야 해당 도메인에서 사용 가능합니다.</li>
								<li>- 도메인은 10개까지 등록 가능하며, 모바일 도메인 URL을 등록 해주셔야 모바일 환경에서 사용 가능합니다.</li>
								<li>- 모바일 사용 시, 스크립트 버전을 업데이트 하셔야합니다. 모바일 -> 스킨관리 -> 사용중인 스킨의 스킨설정 -> jquery-1.11.3.min.js 으로 변경(1.8이상 버전 모두 호환가능)</li>
							</ul>
						</li>
					</ol>
				</div>
                <br>
				<div class="summary_sns">
					<p class="title">현재창 방식 사용 시 설정 방법</p>
					<ol>
                        <li>타사앱(인스타그램, 네이버앱 등)에서 문제가 발생하는 경우 사용해주세요.</li>
                        <li>1) <a href="https://developers.kakao.com/" target="_blank">카카오 개발자센터</a>의 요약정보에서 REST API키를 확인하여 설정에 입력</li>
                        <li>
                            2) <a href="https://developers.kakao.com/" target="_blank">카카오 개발자센터</a>의 플랫폼 메뉴의 Web파트에 다음의 사이트 도메인을 추가
                            <ul>
                                <li>- <?=$root_url?></li>
                                <li>- <?=$m_root_url?></li>
                            </ul>
                        </li>
                        <li>
                            3) <a href="https://developers.kakao.com/" target="_blank">카카오 개발자센터</a> > 카카오 로그인 메인 메뉴 하단의 Redirect URI에 다음 주소 입력
                            <ul>
                                <li>- <?=$root_url?>/_data/compare/kakao/kakao_login_auth.php</li>
                                <li>- <?=$m_root_url?>/_data/compare/kakao/kakao_login_auth.php</li>
                            </ul>
                        </li>
                    </ol>
                </div>
			</div>
            <div class="box_middle2 left kakao_login_use_s">
                <ul class="list_info">
                    <li>카카오싱크란, 원클릭으로 회원가입을 구현할 수 있는 카카오의 간편가입 서비스 입니다.</li>
                    <li>
                        카카오싱크를 통해 수집 가능한 개인정보 항목
                        <ol>
                            <li>프로필, 이메일, 전화번호, 배송지, 성별, 생년월일</li>
                            <li>카카오싱크 신청 전에 <a href="?body=member@member" target="_blank">회원설정>가입/탈퇴/로그인설정>가입설정항목</a> 메뉴에서 고객에게 수집할 개인정보 설정을 먼저 확인해주세요.</li>
                        </ol>
                    </li>
                    <li>카카오싱크 간편설정팝업 가이드 <a href="https://help.wisa.co.kr/document/article/2979" target="_blank">바로가기</a></li>
                </ul>
            </div>
			<div class="box_bottom">
				<span class="box_btn blue"><input type="submit" value="확인"></span>
			</div>
		</form>
	</div>
	<div class="tab_box_sl sl4">
		<form name="paycologinFrm"  method="post" action="<?=$_SERVER['PHP_SELF']?>" onSubmit="return wonderFormCheck(this);" target="hidden<?=$now?>">
			<input type="hidden" name="body" value="config@config.exe">
			<input type="hidden" name="config_code" value="wonder_login">
			<div class="box_sort left">
			</div>
			<table class="tbl_row">
				<caption class="hidden">위메프 로그인 설정</caption>
				<colgroup>
					<col style="width:15%">
				</colgroup>
				<?
				if(!$cfg['wonder_login_client_id'] && !$cfg['wonder_login_client_secret']) {?>
				<tr>
					<th>가입신청</th>
					<td><a href="https://contract.wonders.app/login-api?client_id=<?=$wisa_wonder['wonder_wkey']?>&processing_url=<?=$processing_url?>"><span class="box_btn_s gray"><input type="button" value="위메프로그인 가입신청"></span></a></td>
				</tr>
				<?} ?>
				<tr>
					<th scope="row">위메프로그인 사용</th>
					<td>
						<label class="p_cursor"><input type="radio" id="wonder_login_use_y" name="wonder_login_use" value="Y" <?=checked($cfg['wonder_login_use'] ,'Y')?>> 사용함</label>
						<label class="p_cursor"><input type="radio" id="wonder_login_use_n" name="wonder_login_use" value="N" <?=checked($cfg['wonder_login_use'] ,'N').checked($cfg['wonder_login_use'],"")?>> 사용안함</label>
					</td>
				</tr>
				<tr>
					<th scope="row">Client ID</th>
					<td>
						<input type="text" name="wonder_login_client_id" value="<?=$cfg['wonder_login_client_id']?>" class="input" size="50" >
					</td>
				</tr>
				<tr>
					<th scope="row">Client Secret</th>
					<td>
						<input type="text" name="wonder_login_client_secret" value="<?=$cfg['wonder_login_client_secret']?>" class="input"  size="50">
					</td>
				</tr>
			</table>
			<div class="box_bottom">
				<span class="box_btn blue"><input type="submit" value="확인"></span>
			</div>
		</form>
	</div>
	<div class="tab_box_sl sl5">
		<form name="appleloginFrm"  method="post" action="<?=$_SERVER['PHP_SELF']?>" target="hidden<?=$now?>">
			<input type="hidden" name="body" value="config@config.exe">
			<input type="hidden" name="config_code" value="apple_login">

			<table class="tbl_row">
				<caption class="hidden">애플 로그인 설정</caption>
				<colgroup>
					<col style="width:15%">
				</colgroup>
				<tr>
					<th scope="row">애플로그인 사용</th>
					<td>
						<label class="p_cursor"><input type="radio" name="apple_login_use" value="Y" <?=checked($cfg['apple_login_use'] ,'Y')?>> 사용함</label>
						<label class="p_cursor"><input type="radio" name="apple_login_use" value="N" <?=checked($cfg['apple_login_use'] ,'N').checked($cfg['apple_login_use'],"")?>> 사용안함</label>
						<?if($_SERVER['HTTPS'] != 'on' && $_SERVER['apple_login_use'] != 'Y') {?>
						<span class="msg_bubble warning">보안서버를 이용 중일때만 사용하실수 있습니다. <a href="/_manage/?body=config@ssl" target="_blank">설정하기</a></span>
						<?}?>
					</td>
				</tr>
				<tr>
					<th scope="row">Client ID</th>
					<td>
						<input type="text" name="apple_login_client_id" value="<?=$cfg['apple_login_client_id']?>" class="input" size="50" >
					</td>
				</tr>
			</table>
			<div class="box_middle2 left">
				<div class="summary_sns">
					<ol>
						<li><a href="#" onclick="toggleManual(); return false;" class="p_color">도움말</a>을 참조하여 애플 개발자 화면의 다음 메뉴에 아래 내용을 입력해 주시기 바랍니다.</li>
						<li class="p_color3">- Account > Certificates, Identifiers & Profiles > Identifiers</li>
					</ol>
					<p class="title">Domains and Subdomains</p>
					<ol>
						<li><?=preg_replace('@^https?://@', '', $root_url)?>,</li>
						<li><?=preg_replace('@^https?://@', '', $m_root_url)?></li>
					</ol>
					<p class="title">Return URLs</p>
					<ol>
						<li><?=$root_url?>/main/exec.php?exec_file=promotion/apple_login.exe.php,</li>
						<li><?=$m_root_url?>/main/exec.php?exec_file=promotion/apple_login.exe.php</li>
					</ol>
				</div>
			</div>
			<div class="box_bottom">
				<span class="box_btn blue"><input type="submit" value="확인"></span>
			</div>
		</form>
	</div>
</div>

<script type="text/javascript">
	function tabview(no,name) {
		var tabs = $('.tablist').find('li');
		tabs.each(function() {
			var obj = $(this).find('a');
			var idx = obj.prop('href').replace(/.*#tab([0-9]+)$/, '$1');
			var box = $('.'+name+idx);
			if(no == idx) {
				obj.addClass('active');
				box.show();
			} else {
				obj.removeClass('active');
				box.hide();
			}
		})
	}
</script>

<script type="text/javascript">
	function naverFormCheck(f){
		if($('#naver_login_use_y').is(":checked")) {
			if(!checkBlank(f.naver_login_client_name,'클라이언트 이름을 입력해주세요.')) return false;
			if(!checkBlank(f.naver_login_client_id,'CLIENT ID를 입력해주세요.')) return false;
			if(!checkBlank(f.naver_login_client_secret,'CLIENT SECRET ID를 입력해주세요.')) return false;
		}
		return true;
	}

	function faceFormCheck(f){
		if($('#facebook_login_use_y').is(":checked")) {
			if(!checkBlank(f.facebook_id,'페이스북 APP ID를 입력해주세요.')) return false;
		}
		return true;
	}

	function kakaoFormCheck(f){
		if($('#kakao_login_use_s').is(":checked")) {
			//
		} else {
            if($('#kakao_login_use_n').is(":checked") == false) {
                if(!checkBlank(f.kakao_sns_id,'카카오톡 JavaScript 키를 입력해주세요.')) return false;
            }
        }
        printLoading();
		return true;
	}

    var kakaoFormView = function() {
        // 카카오 추가 항목 보이기
        if($('#kakao_login_use_s').is(":checked")) {
            $('input[name=kakao_rest_api], input[name=kakao_sns_id]').prop('readOnly', true).addClass('readOnly');
        } else {
            $('input[name=kakao_rest_api], input[name=kakao_sns_id]').prop('readOnly', false).removeClass('readOnly');
        }

        $('.kakao_login_use_y, .kakao_login_use_s').hide();
        if($('#kakao_login_use_y').is(":checked")) {
            $('.kakao_login_use_y').show();
        }
        if($('#kakao_login_use_s').is(":checked")) {
            $('.kakao_login_use_s').show();
        }
    }

	function paycoFormCheck(f){
		if($('#payco_login_use_y').is(":checked")) {
			if(!checkBlank(f.payco_login_client_id,'CLIENT ID를 입력해주세요.')) return false;
			if(!checkBlank(f.payco_login_client_secret,'CLIENT SECRET ID를 입력해주세요.')) return false;
		}
		return true;
	}

	function wonderFormCheck(f){
		if($('#wonder_login_use_y').is(":checked")) {
			if(!checkBlank(f.wonder_login_client_id,'CLIENT ID를 입력해주세요.')) return false;
			if(!checkBlank(f.wonder_login_client_secret,'CLIENT SECRET ID를 입력해주세요.')) return false;
		}
		return true;
	}

    $(function() {
        var url = location.href.split('#');
        if(url[1]) {
            tabview(url[1].replace('tab', ''),'sl');
        }
        kakaoFormView();
        $(':radio[name=kakao_login_use]').change(kakaoFormView);

        new Clipboard('.clipboard').on('success', function(e) {
            window.alert('주소가 복사되었습니다.');
        });
    });

    <?php if ($cfg['kakao_login_use'] != 'S') { ?>
    setInterval(function() {
        if ($('#kakao_login_use_s').is(":checked")) {
            $.post('./index.php', {'body':'member@kakao_sync.exe', 'exec':'check_callback'}, function(r) {
                if (r == 'true') {
                    location.reload();
                }
            });
        }
    }, 2000);
    <?}?>

	// 매직앱 이용시 애플로그인 체크
	$('#sns_login').find('form').not('[name=appleloginFrm]').on('submit', function() {
		if($(this).find(':checked[name$=_use]').val() == 'Y') {
			if(use_magicapp > 0) {
				if($(':checked[name=apple_login_use]').val() != 'Y') {
					window.alert('매직앱 이용 시 SNS로그인을 사용하시려면\n애플 정책에 의해 반드시 애플로그인을 설정하셔야 합니다.');
					tabview(5, 'sl');
					return false;
				}
			}
		}
	});
</script>