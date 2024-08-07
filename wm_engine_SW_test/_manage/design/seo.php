<?PHP

	/* +----------------------------------------------------------------------------------------------+
	' |  SEO설정
	' +----------------------------------------------------------------------------------------------+*/

	if(!$cfg['use_seo_advanced']) $cfg['use_seo_advanced']= 'N';
	if($_GET['mode']) {
		$mode = ($_GET['mode'] == 'advanced') ? 'advanced' : 'basic';
	}
	if(!$mode) $mode = ($cfg['use_seo_advanced'] == 'Y') ? 'advanced' : 'basic';

	$inc_file = ($mode == 'advanced') ? 'seo_advanced' : 'title_meta';
	$tab_basic = ($cfg['use_seo_advanced'] == 'Y') ? 'off' : 'on';
	$tab_advanced = ($cfg['use_seo_advanced'] != 'Y') ? 'off' : 'on';
	$active[$mode] = 'active';

	$head_tags_res = $pdo->iterator("select * from $tbl[default] where code like 'head_%' order by code asc");
	//$ires = $pdo->iterator("select * from $tbl[config] where name like 'relation_channel%' and value!='' order by name asc ");

	$channels = array();
	for($i = 1; $i <= 9; $i++) {
		$val = trim($cfg['relation_channel'.$i]);
		if($val) {
			$channels['relation_channel'.$i] = $cfg['relation_channel'.$i];
		}
	}
	if(count($channels) == 0) {
		$channels = array('relation_channel1' => '');
	}
	$channel_rowspan = count($channels);

	$robots_content = (file_exists($_SERVER['DOCUMENT_ROOT'].'/robots.txt'))? file_get_contents($_SERVER['DOCUMENT_ROOT'].'/robots.txt'):'';

?>
<div id="sns_login">
	<div class="box_title first">
		<h2 class="title">SEO 설정</h2>
	</div>
	<div class="box_tab first tablist">
		<ul>
			<li>
				<a href="?body=design@seo&mode=basic" class="<?=$active['basic']?>">일반설정<span class="toggle <?=$tab_basic?>"><?=strtoupper($tab_basic)?></span></a>
			</li>
			<li>
				<a href="?body=design@seo&mode=advanced" class="<?=$active['advanced']?>">고급설정<span class="toggle <?=$tab_advanced?>"><?=strtoupper($tab_advanced)?></span></a>
			</li>
		</ul>
	</div>
</div>

<?php require $inc_file.'.php'; ?>

<form id="cfgfrm" name="" method="post" action="<?=$_SERVER['PHP_SELF']?>" target="hidden<?=$now?>" onsubmit="printLoading()">
	<input type="hidden" name="body" value="config@config.exe">
	<input type="hidden" name="config_code" value="dtd">
	<div class="box_title">
		<h2 class="title">기타 설정</h2>
	</div>
	<table class="tbl_row">
		<caption class="hidden">기타설정</caption>
		<colgroup>
			<col style="width:15%">
			<col>
		</colgroup>
		<tbody>
			<tr>
				<th scope="row">HEAD 태그관리</th>
				<td>
					<table class="tbl_inner full line">
						<caption class="hidden">HEAD 태그관리</caption>
						<colgroup>
							<col>
							<col style="width:80px;">
						</colgroup>
						<thead>
						<tr>
							<th>태그</th>
							<th>삭제</th>
						</tr>
						</thead>
						<tbody>
						<?php foreach ($head_tags_res as $data) {?>
						<tr class="<?=$data['code']?>">
							<td class="left"><?=htmlspecialchars(stripslashes($data['value']))?></td>
							<td><span class="box_btn_s"><a href="#" onclick='removeDefault("<?=$data['code']?>"); return false;'>삭제</a></span></td>
						</tr>
						<?php } ?>
						<tr>
							<td colspan="2" class="left">
								<ul class="list_info">
									<li>새 태그 입력 후 확인 버튼을 눌러주세요.</li>
									<li>HEAD 태그관리 이외 스크립트 매니저를 통해 보다 편리하게 관리할 수 있습니다. <a href="?body=design@mkt_script_list">바로가기</a></li>
								</ul>
								<input type="text" name="head" class="input block" placeholder="새 태그 입력">
							</td>
						</tr>
						</tbody>
					</table>
				</td>
			</tr>
			<tr>
				<th scope="row">DTD</th>
				<td>
					<input type="text" name="frontDTD" value="<?=inputText(stripslashes(stripslashes($cfg['frontDTD'])))?>" class="input block">
					<p><label class="p_cursor"><input type="checkbox" name="DTDuse" value="N" onclick="disabledDTD(this)"> DTD사용하지 않음 (DTD를 브라우저가 자동으로 선택하도록 합니다.)</label></p>
					<ul class="list_info">
						<li>내용을 입력하지 않을 경우 <span class="warning">&lt;!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.0 Transitional//EN"&gt;</span>로 적용됩니다.</li>
						<li>DTD 변경시 디자인이 틀려지거나 자바스크립트에 오류가 발생할수 있습니다. 변경 후 각 페이지를 테스트 해주시기 바랍니다.</li>
					</ul>
				</td>
			</tr>
			<tr>
				<th scope="row">호환성보기 차단</th>
				<td>
					<label class="p_cursor"><input type="checkbox" name="compatible_edge" value="Y" <?=checked($cfg['compatible_edge'], 'Y')?>> IE 호환성보기 기능을 차단합니다.</label>
					<ul class="list_info">
						<li>인터넷익스플로러의 '호환성보기' 기능을 사용중인 접속자에게 자동으로 최신버전 기준으로 사이트를 출력하도록 처리합니다.</li>
						<li>코딩방식에 따라 특정 버전의 브라우저에서 사이트가 깨질수 있으니, 충분한 테스트 후 기능을 설정해 주시기 바랍니다.</li>
					</ul>
				</td>
			</tr>
		</tbody>
	</table>
	<div class="box_bottom">
		<span class="box_btn blue"><input type="submit" value="확인"></span>
	</div>
</form>

<form id="cfgfrm3" name="" method="post" action="<?=$_SERVER['PHP_SELF']?>" target="hidden<?=$now?>" onsubmit="printLoading()">
	<input type="hidden" name="body" value="design@title_meta.exe">
	<input type="hidden" name="exec" value="robots">
	<div class="box_title">
		<h2 class="title">검색로봇 접근제어 설정</h2>
		<div class="btns">
			<span class="box_btn_s icon guide"><a onclick="$('.layer_view.robots').toggle();">작성 시 참고사항</a></span>
			<span class="box_btn_s icon setup"><a onclick="robot_log(this);">변경이력</a></span>
		</div>
		<div class="layer_view robots">
			<dl>
				<dt>검색로봇 접근제어 상세설정(robots.txt) 작성 시 참고사항</dt>
				<dd>
					<table class="tbl_inner full line">
						<caption class="hidden">검색로봇 접근제어 상세설정(robots.txt) 작성 시 참고사항</caption>
						<colgroup>
							<col style="width:200px;">
							<col>
						</colgroup>
						<thead>
							<tr>
								<th scope="row">내용</th>
								<th scope="row">의미</th>
							</tr>
						</thead>
						<tbody>
							<tr>
								<td class="left">User-agent: *<br>Allow: /</td>
								<td class="left">모든 검색로봇에 대해 <br>쇼핑몰 전체내용 접근 허용</td>
							</tr>
							<tr>
								<td class="left">User-agent: *<br>Disallow: /</td>
								<td class="left">모든 검색로봇에 대해 <br>쇼핑몰 전체내용 접근 제한</td>
							</tr>
							<tr>
								<td class="left">예) 네이버<br>User-agent: NaverBot<br>Allow: /<br>User-agent: *<br>Disallow: /</td>
								<td class="left">특정 검색로봇(NaverBot)에 대해 <br>쇼핑몰 전체내용 접근 허용</td>
							</tr>
							<tr>
								<td class="left">User-agent: *<br>Disallow: /a/<br>Disallow: /_manage/</td></td>
								<td class="left">모든 검색로봇에 대해 <br>a, _manage 디렉토리를 제외하고 쇼핑몰 내용 접근 허용</td>
							</tr>
						</tbody>
					</table>
				</dd>
				<dt class="tm">검색로봇의 종류와 표기방식</dt>
				<dd>
					<table class="tbl_inner full line">
						<caption class="hidden">검색로봇의 종류와 표기방식</caption>
						<colgroup>
							<col style="width:200px;">
							<col>
						</colgroup>
						<thead>
							<tr>
								<th scope="row">종류</th>
								<th scope="row">표기방식</th>
							</tr>
						</thead>
						<tbody>
							<tr>
								<td class="left">구글 로봇</td>
								<td class="left">Googlebot</td>
							</tr>
							<tr>
								<td class="left">네이버 로봇</td>
								<td class="left">NaverBot</td>
							</tr>
							<tr>
								<td class="left">다음 로봇</td>
								<td class="left">Daumoa</td>
							</tr>
						</tbody>
					</table>
				</dd>
			</dl>
			<a onclick="$('.layer_view.robots').hide();" class="close"></a>
		</div>
		<div class="layer_view robots_log">
		<?php include_once $engine_dir.'/_manage/design/robots_log_inc.exe.php'; ?>
		</div>
	</div>
	<table class="tbl_row" cellspacing="0" cellpadding="0">
		<caption class="hidden">검색로봇 접근제어 설정</caption>
		<colgroup>
			<col style="width:15%">
			<col>
		</colgroup>
		<tbody>
			<tr>
				<th class="right">검색로봇 접근제어 상세설정<br>(robots.txt)<br><br><span class="box_btn_s"><input type="button" value="기본설정 불러오기" onclick="robot_basic();"></span></th>
				<td class="left">
					<textarea id="robots_content" name="robots_content" class="txta" cols="200" rows="5"><?=$robots_content?></textarea><br><br>
					<ul class="list_info">
						<li>검색로봇 접근제어 상세설정에 따라 특정 경로 및 특정 검색로봇의 접근 가능 여부를 허용하거나 제한할 수 있습니다.</li>
						<li class="warning">a, _manage 디렉토리와 같이 계정 및 개인정보들이 포함되어 있는 디렉토리는 보호하는 것을 권장합니다.</li>
						<li class="warning">잘못된 입력으로 인한 정보노출에 대한 모든 책임은 쇼핑몰에 있습니다.</li>
					</ul>
				</td>
			</tr>
		</tbody>
	</table>
	<div class="box_bottom">
		<span class="box_btn blue"><input type="submit" value="확인"></span>
	</div>
</form>

<form id="cfgfrm2" name="" method="post" action="<?=$_SERVER['PHP_SELF']?>" onSubmit="printLoading()" target="hidden<?=$now?>">
	<input type="hidden" name="body" value="design@title_meta.exe">
	<input type="hidden" name="config_code" value="relation_channel">
	<input type="hidden" name="exec" value="relation_channel">

	<div class="box_title">
		<h2 class="title">네이버 연관채널 설정</h2>
		<div class="btns">
			<span class="box_btn_s icon list"><a onclick="$('.layer_view.channel').toggle();">미리보기</a></span>
		</div>
		<div class="layer_view channel">
			<dl>
				<dt>네이버 연관채널이란?</dt>
				<dd>네이버 검색결과에 쇼핑몰과 관련된 SNS채널을 적용할 수 있게 하는 네이버 검색기능입니다.</dd>
			</dl>
			<a onclick="$('.layer_view.channel').hide();" class="close"></a>
		</div>
	</div>
	<table class="tbl_row" name="channel2" id ="channel2" cellspacing="0" cellpadding="0">
		<caption class="hidden">네이버 연관채널 설정</caption>
		<colgroup>
			<col style="width:15%">
			<col>
		</colgroup>
		<tbody>
			<tr>
				<th class="right" rowspan="<?=$channel_rowspan?>">연관채널</th>
			<?php foreach ($channels as $key => $val) { if ($key != 'relation_channel1' && !$val) continue; ?>
			<?php if ($key != 'relation_channel1') { ?><tr><?php } ?>
			<?php
                $tmp = parse_url($val);
				if(!$tmp['scheme'] || ($tmp['scheme'] != 'http' && $tmp['scheme'] != 'https')) $tmp['scheme'] = "http";
				$val =  str_replace($tmp['scheme'].'://', '', $val);?>
				<td class="left">
					<select name="scheme[]">
						<option value="http://" <?=checked($tmp['scheme'].'://','http://',1)?>>http://</option>
						<option value="https://" <?=checked($tmp['scheme'].'://','https://',1)?>>https://</option>
					</select>
					<input type="text" name="relation_channel[]" class="input" size="100" placeholder="ex) www.instagram.com/wisa.co.kr" value="<?=$val?>">
					<?php if ($key != 'relation_channel1') { ?>
					<span class="box_btn_s"><input type="button" name="remove" value="-" onclick="removechannel(this,'<?=$key?>')"></span>
					<?php } ?>
				</td>
			</tr>
			<?php } ?>
		</tbody>
	</table>
	<div class="box_bottom">
		<span class="box_btn blue"><input type="submit" value="확인"></span>
	</div>
</form>
<script type="text/javascript">
	// 검색로봇 접근 제어
	function robot_basic() {
		$("#robots_content").val("User-agent: *\nDisallow: /a/\nDisallow: /_manage/");
	}

	var robotcall = new layerWindow('design@robots_log_inc.exe');
	function robot_log(obj) {
		robotcall.input = obj;
		robotcall.open();
	}

	function disabledDTD(ckbtn) {
		var f = document.getElementById('cfgfrm');
		if(!ckbtn || ckbtn.checked == true) {
			f.frontDTD.value = ' ';
			f.frontDTD.style.backgroundColor = '#f2f2f2';
			f.frontDTD.readOnly = true;
			f.DTDuse.checked = true;
		} else {
			f.frontDTD.value = '';
			f.frontDTD.style.backgroundColor = '';
			f.frontDTD.readOnly = false;
		}
	}

	function removeDefault(code) {
		if(confirm('삭제 시 복구가 불가능합니다.\n선택하신 태그를 삭제하겠습니까?')) {
			$.post('./index.php', {'body':'design@title_meta.exe', 'exec':'removeHead', 'code':code}, function(r) {
				$('.'+code).remove();
			});
		}
	}

	// 네이버 연관채널 설정
	function addChannel() {
		var table = $('#channel2').find('tbody');
		var count = $('input[name="relation_channel[]"]').length;
		var tr = document.createElement('TR');
		var td2 = document.createElement('TD');

		if(count >= 9) {
			window.alert("연관 채널은 최대 9개까지 등록할 수 있습니다.");
			return;
		}
		$(table).find('th').attr('rowspan', count+1);

		td2.className = 'left';
		td2.innerHTML ="<select name='scheme[]'><option selected='selected'>http://</option><option>https://</option></select>";
		td2.innerHTML += " <input type='text' name='relation_channel[]' class='input' size='100' placeholder='ex) www.instagram.com/wisa.co.kr'>";
		td2.innerHTML += " <span class='box_btn_s'><input type='button' name='remove' value='-' onclick='removechannel(this)'></span> ";
		tr.appendChild(td2);
		table.append(tr);

		attachBtn();
	}

	function removechannel(obj, name) {
		if(confirm('선택하신 연관채널을 삭제하시겠습니까?')) {
			var tr = obj.parentNode.parentNode.parentNode;
			tr.parentNode.removeChild(tr);
			$.post('./index.php', {'body':'design@title_meta.exe', 'exec':'removechannel', 'name':name});
			attachBtn();
		}
	}

	function attachBtn() {
		$('#addBtn').remove();
		$('#listmsg').remove();
		var btn = $('#addBtn');
		var msg = $('#listmsg');
		if(btn.length == 0) {
			var btn = $(document.createElement('SPAN'));
			btn.attr('id', 'addBtn');
			btn.addClass('box_btn_s');
			btn.html("<input type='button' value='+ 채널추가' onclick='addChannel()'>");
			var msg = $(document.createElement('SPAN'));
			msg.html("<ul class='list_info' id='listmsg'><li>쇼핑몰과 관련된 SNS채널주소를 입력하시면 '네이버 연관채널'에 등록할 수 있습니다.</li><li>네이버 정책에 따라 일부 외부 채널만 등록할 수 있으며, 검색 알고리즘에 의해 자동으로 노출 여부가 결정됩니다.</li></ul>");
		}
		var last = $('#channel2').find('tr').last().find('td');
		last.last().append(btn);
		last.last().append(msg);
	}

	document.body.onload = function() {
		attachBtn();
		$('.layer_view.robots').hide();
		$('.layer_view.seo').hide();

		<?php if ($cfg['frontDTD'] == ' ') { ?>
		disabledDTD();
		<?php } ?>
	}
</script>