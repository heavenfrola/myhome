<?PHP

	/* +----------------------------------------------------------------------------------------------+
	' |  대표도메인 설정
	' +----------------------------------------------------------------------------------------------+*/

	if($admin['level'] > 2 && $admin['admin_id'] != 'wisa') {
		msg('최고 관리자만 접근 가능합니다.');
	}

	$wec->service = 'account';
	$domains = $wec->call('getDomains');

	$domains = explode(',', $domains);
	foreach($domains as $key => $val) {
		$domains[$key] = $val;
	}

	$tmp = $wec->call('getAccountDomains');
	$tmp = json_decode($tmp);
	$wdomain = array();
	foreach($tmp as $key => $val) {
		$wdomain[$val->domain] = array(
			'domain' => $val->domain,
			'reg_date' => $val->reg_date,
			'exp_date' => $val->exp_date,
			'edt_date' => $val->edt_date,
		);
	}

	if(!fieldExist($tbl['domain_expire'], 'organization')) {
		addField($tbl['domain_expire'], 'organization', 'varchar(255) after `expire`');
	}

	function parseWisaDom(&$arr) {
		global $domains;

		$data = current($arr);
		if($data == false) return false;
		next($arr);

		$data['registerd'] = (in_array($data['domain'], $domains) == true) ? 'disabled' : '';

		return $data;
	}

?>
<form name="" method="post" action="<?=$_SERVER['PHP_SELF']?>" target="hidden<?=$now?>" onsubmit="return ckFrm();">
	<input type="hidden" name="body" value="config@domain.exe">
	<input type="hidden" name="exec" value="main">
	<div class="box_title first">
		<h2 class="title">대표도메인 설정</h2>
	</div>
	<div class="box_middle">
		<ul class="list_info left">
			<li>대표도메인 변경 시 <u>현재 관리자 세션이 종료되며, 회원들의 구매와 로그인상태에 영향</u>을 미칠 수 있습니다.</li>
			<li>연결되지 않는 도메인으로 대표메인 변경 시 사이트 접속되지 않을 수 있습니다.</li>
			<li>대표도메인 설정은 위사 임대형 호스팅 사용 시에만 정상처리되며, <u>그외 호스팅을 사용 중이라면 설정파일에서 수정해 주셔야 합니다.</u></li>
			<?php if ($cfg['ssl_type']=="Y") { ?><li class="warning">보안서버 설정에 의해 대표도메인 변경이 불가능한 상태입니다.</li><?php } ?>
		</ul>
	</div>
	<table class="tbl_col">
		<caption class="hidden">운영자 정보</caption>
		<colgroup>
			<col style="width:200px">
			<col>
			<col style="width:200px">
			<col style="width:200px">
			<col style="width:150px">
		</colgroup>
		<thead>
			<tr>
				<th scope="col" colspan="2">도메인</th>
				<th scope="col">만료일</th>
				<th scope="col">등록기관</th>
				<th scope="col">관리</th>
			</tr>
		</thead>
		<tbody>
			<?php
				$domnum = 0;
				foreach($domains as $key => $val) {
					if(!$val) continue;
					preg_match("/^(m\.)?/", $val, $matches);
					if($matches[0] == 'm.') continue;
					$val = preg_replace('/^https?:\/\//' ,'', $val);

					$checked = $domnum == 0 ? 'checked' : '';
					$dname = $domnum == 0 ? '대표도메인' : '연결도메인';
					$mainclass = $domnum == 0 ? 'highlight' : '';
					$disabled = ($cfg['ssl_type']=="Y" && $key!=0) ? "disabled":"";
					$domain_info = $pdo->assoc("select `expire`, `organization` from `$tbl[domain_expire]` where `domain`='$val'");
					$domnum++;

					$wisa = (isset($wdomain[$val])) ? $wdomain[$val] : array();
					$use_wisa_dns = false;
					$use_wisa_domain = false;
					$expire_date = strtotime($domain_info['expire']);
					if($wisa) { // 위사 네임서버 사용 여부 체크
						$use_wisa_domain = true;
						$dns = dns_get_record('wisa.co.kr');
						foreach($dns as $_dns) {
							if($_dns['type'] == 'NS') {
								if(preg_match('/wisaidc/', $_dns['target'])) $use_wisa_dns = true;
							}
						}
						$wisa['exp_date_s'] = date('Y-m-d', $wisa['exp_date']);
						$expire_date = $wisa['exp_date'];
					}
					if($domain_info['expire']) {
						$left_date = ceil(($expire_date-strtotime(date('Y-m-d', $now)))/86400);
						if($left_date < 0) $disabled = 'disabled';
					}

			?>
			<tr>
				<td <?=$main_bold?>><?=$dname?></td>
				<td class="left">
					<label><input type="radio" name="doms" value="<?=$val?>" <?=$checked?> <?=$disabled?>> <span class="<?=$mainclass?>"><?=$val?></span></label>

				</td>
				<td>
					<input type="hidden" name="target_domain[<?=$key?>]" size="15" value="<?=$val?>" class="input">
					<?php if(preg_match('/\.mywisa\./', $val)){ ?>
						-
					<?php } else if($wisa['exp_date'] > 0) { ?>
						<?=$wisa['exp_date_s']?>
						<span class="box_btn_s2"><a href="https://redirect.wisa.co.kr/domainExtand" target="_blank" class="sclink">연장</a></span>
					<?php } else { ?>
						<input type="text" name="domain_expire[<?=$key?>]" size="15" value="<?=$domain_info['expire']?>" readonly class="input datepicker">
					<?php } ?>
				</td>
				<td>
					<?php if (preg_match('/\.mywisa\./', $val)){ ?>
						무료제공 도메인
						<input type="hidden" name="domain_organization[<?=$key?>]" size="15" value="" class="input">
					<?php } else if($wisa['exp_date'] > 0) { ?>
						위사
					<?php } else { ?>
						<input type="text" name="domain_organization[<?=$key?>]" size="15" value="<?=stripslashes($domain_info['organization'])?>" class="input">
					<?php } ?>
				</td>
				<td>
					<?php if (preg_match('/\.mywisa\./', $val)) { ?>
						-
					<?php } else { ?>
						<span class="box_btn_s"><input type="button" value="후이즈정보" onclick="whoisInfo('<?=$val?>')"></span>
						<?php if ($key != 0) { ?><span class="box_btn_s gray"><input type="button" value="삭제" onclick="removeDomain('<?=$val?>')"></span><?php } ?>
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
<form name="" method="post" action="<?=$_SERVER['PHP_SELF']?>" target="hidden<?=$now?>" onsubmit="return ckFrm();">
	<input type="hidden" name="body" value="config@domain.exe">
	<input type="hidden" name="exec" value="regist">
	<div class="box_title">
		<h2 class="title">연결도메인 추가</h2>
	</div>
	<table class="tbl_row">
		<caption class="hidden">연결도메인 추가</caption>
		<colgroup>
			<col style="width:15%">
		</colgroup>
		<tr>
			<th scope="row">도메인 분류</th>
			<td>
				<label><input type="radio" name="domain_type" value="wisa" checked> 위사에서 구매한 도메인</label>
				<label><input type="radio" name="domain_type" value="others" > 다른 기관에서 구매한 도메인</label>
			</td>
		</tr>
		<tr class="add_dom_wisa">
			<th scope="row">연결도메인 선택</th>
			<td>
				<?php if (count($wdomain) > 0) { ?>
				<ul>
					<?php while($dom = parseWisaDom($wdomain)) { ?>
					<li><label><input type="checkbox" name="domains[]" value="<?=$dom['domain']?>" <?=$dom['registerd']?>> <?=$dom['domain']?></label></li>
					<?php } ?>
				</ul>
				<?php } else { ?>
				<span class="explain">등록 가능한 도메인이 없습니다.</span>
				<?php } ?>
			</td>
		</tr>
		<tr class="add_dom_others" style="display:none;">
			<th scope="row">연결도메인 입력</th>
			<td>
				www.<input type="text" name="domain" class="input" size="50" value="">
                <span class="explain">한글도메인 입력 시 xn--로 시작되는 퓨니코드로 변환됩니다.</span>
			</td>
		</tr>
	</table>
	<div class="box_middle2">
		<ul class="list_info left">
			<li>위사 네임서버 정보 <a href="http://www.wisa.co.kr/start/domain/search" target="_blank">바로가기</a></li>
		</ul>
	</div>
	<div class="box_bottom">
		<span class="box_btn blue"><input type="submit" value="확인"></span>
	</div>
</form>

<script type="text/javascript">
	function ckFrm(){
		if(!confirm('입력하신 도메인의 유효성을 체크한 후 대표도메인으로 변경하실 수 있습니다. 도메인을 추가하시겠습니까?')) return false;
		printLoading();
	}

	function whoisInfo(domain) {
		window.open('http://search.wisa.co.kr/whois?domain='+domain, 'whois', 'width=200px, height=200px, top=100px, left=100px, status=no, scrollbars=yes');
	}

	$(function() {
		$(':radio[name=domain_type]').click(function() {
			if(this.value == 'wisa') {
				$('.add_dom_wisa').show();
				$('.add_dom_others').hide();
			} else {
				$('.add_dom_wisa').hide();
				$('.add_dom_others').show();
			}
		});
	});
</script>

<form id="delFrm" method="post" action="<?=$_SERVER['PHP_SELF']?>" target="hidden<?=$now?>">
	<input type="hidden" name="body" value="config@domain.exe">
	<input type="hidden" name="exec" value="remove">
	<input type='hidden' name='domain' value=''>
</form>

<script type='text/javascript'>
	function removeDomain(domain) {
		if(confirm('선택한 도메인을 제거하시겠습니까?')) {
			printLoading();
			var f = document.getElementById("delFrm");
			f.domain.value = domain;
			f.submit();
		}
	}
</script>