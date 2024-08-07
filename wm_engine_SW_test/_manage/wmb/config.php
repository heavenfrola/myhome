<?PHP

	/* +----------------------------------------------------------------------------------------------+
	' |  윙Mobile 설정
	' +----------------------------------------------------------------------------------------------+*/

	$_GET['type']='mobile';

	include_once $engine_dir."/_engine/include/design.lib.php";
	include_once $engine_dir."/_manage/design/version_check.php";
	$_skin_name=editSkinName();

	// 기존 로고 스킨디렉토리로 옮김
	$ofile=$root_dir."/".$dir['upload']."/".$dir['mobile']."/logo.".$cfg['mobile_img_type'];
	$nfile=$root_dir."/_skin/".$_skin_name."/img/logo/logo.".$cfg['mobile_img_type'];
	if(file_exists($ofile) && !file_exists($nfile)) {

		@copy($ofile, $nfile); // 로고 복사
		$header=file_get_contents($root_dir."/_skin/".$_skin_name."/COMMON/header.wsn");
		$header=str_replace("{{\$사이트주소}}/_data/mobile/logo.{{\$로고이미지타입}}", "{{\$이미지경로}}/logo/logo.".$cfg['mobile_img_type'], $header);

		// header.wsn 저장
		$fp=@fopen($root_dir."/_skin/".$_skin_name."/COMMON/header.wsn", "w");
		@fwrite($fp, $header);
		@fclose($fp);
	}

	if(!$cfg['mobile_ver_show']) $cfg['mobile_ver_show'] = 'N';
	if(!$cfg['mobile_show_top']) $cfg['mobile_show_top'] = 'N';

?>
<form name="" method="post" action="<?=$_SERVER['PHP_SELF']?>" target="hidden<?=$now?>">
	<input type="hidden" name="body" value="config@config.exe">
	<input type="hidden" name="config_code" value="mobile_set">
	<div class="box_title first">
		<h2 class="title"><?=$cfg['mobile_name']?> 설정</h2>
	</div>
	<table class="tbl_row">
		<caption class="hidden"><?=$cfg['mobile_name']?> 설정</caption>
		<colgroup>
			<col style="width:15%">
		</colgroup>
		<tr>
			<th scope="row"><?=$cfg['mobile_name']?>사용</th>
			<td>
				<input type="radio" name="mobile_use" id="mobile_use_y" value="Y" <?=checked($cfg['mobile_use'], 'Y')?>> <label for="mobile_use_y" class="p_cursor">사용</label>
				<input type="radio" name="mobile_use" id="mobile_use_n" value="N" <?=checked($cfg['mobile_use'], 'N').checked($cfg['mobile_use'], '')?>> <label for="mobile_use_n" class="p_cursor">사용안함</label>
				<ul class="list_msg">
                    <li class="p_color">반응형 스킨을 사용한다면 '사용안함' 설정을 그대로 유지해주세요.</li>
                    <li><?=$cfg['mobile_name']?> 사용안함으로 체크하실 경우 모바일기기에서 접속시에도 일반PC와 동일하게 기존 PC버전의 홈페이지가 노출됩니다.</li>
					<li>이때 데이터와 설정은 모두 보존되며 유실되지 않습니다.</li>
				</ul>
			</td>
		</tr>
		<tr>
			<th scope="row">PG 설정</th>
			<td>
				<span class="box_btn_s"><a href="./?body=config@card">설정</a></span>
				<ul class="list_msg">
					<li><?=$cfg['mobile_name']?> 결제는 기존 PG사 상점아이디를 추가 발급 받아 연동합니다.</li>
					<li>별도의 <?=$cfg['mobile_name']?>PG운영,혹은 타 PG사와 모바일 결제용 PG를 별도 계약하시는 경우 1:1고객센터 문의 글로 접수 바랍니다.</li>
				</ul>
			</td>
		</tr>
		<tr>
			<th scope="row">하단정보</th>
			<td>
				<span class="box_btn_s"><a href="?body=config@info" target="_blank">바로가기</a></span>
				<ul class="list_msg">
					<li>업체정보의 상호,전화번호,사업장 주소,사업자번호,통신판매신고번호만 표시됩니다.</li>
				</ul>
			</td>
		</tr>
		<tr>
			<th scope="row">모바일접속 주소</th>
			<td>
				<ul class="list_msg">
					<li>모바일 환경에서 접속시 <a href="<?=$m_root_url?>" target="_blank"><?=$m_root_url?></a> 으로 자동 접속됩니다.</li>
					<li>대표도메인 변경시 함께 변경됩니다. <a href="?body=config@domain">대표도메인 설정</a></li>
				</ul>
			</td>
		</tr>
	</table>
	<div class="box_bottom">
		<span class="box_btn blue"><input type="submit" value="확인"></span>
	</div>
</form>
<form method="post" action="<?=$_SERVER['PHP_SELF']?>" target="hidden<?=$now?>">
	<input type="hidden" name="body" value="config@config.exe">
	<input type="hidden" name="config_code" value="mobile_set">
	<div class="box_title">
		<h2 class="title">모바일버전 보기 버튼 설정</h2>
	</div>
	<table class="tbl_row">
		<caption class="hidden">모바일버전 보기 버튼 설정</caption>
		<colgroup>
			<col style="width:15%">
		</colgroup>
		<tr>
			<th scope="row">사용여부</th>
			<td>
				<input type="radio" name="mobile_ver_show" id="mobile_ver_show_y" value="Y" <?=checked($cfg['mobile_ver_show'], 'Y')?>> <label for="mobile_ver_show_y" class="p_cursor">사용</label>
				<input type="radio" name="mobile_ver_show" id="mobile_ver_show_n" value="N" <?=checked($cfg['mobile_ver_show'], 'N')?>> <label for="mobile_ver_show_n" class="p_cursor">사용안함</label>
			</td>
		</tr>
		<tr>
			<th scope="row">노출위치</th>
			<td>
				<input type="radio" name="mobile_show_top" id="mobile_show_top_y" value="Y" <?=checked($cfg['mobile_show_top'], 'Y')?>> <label for="mobile_show_top_y" class="p_cursor">상단</label>
				<input type="radio" name="mobile_show_top" id="mobile_show_top_n" value="N" <?=checked($cfg['mobile_show_top'], 'N')?>> <label for="mobile_show_top_n" class="p_cursor">하단</label>
			</td>
		</tr>
	</table>
	<div class="box_bottom">
		<span class="box_btn blue"><input type="submit" value="확인"></span>
	</div>
</form>