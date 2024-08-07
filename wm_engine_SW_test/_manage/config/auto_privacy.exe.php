<?PHP

	/* +----------------------------------------------------------------------------------------------+
	' |  개인정보취급방침 자동생성 처리
	' +----------------------------------------------------------------------------------------------+*/

	$content=$content_xml="";
	$privacy = $_POST['privacy'];
	$destroy = $_POST['destroy'];

	if($cfg[design_version] == "V3"){
		$_company_name="{{\$회사명}}";
		$_company_privacy_date2="{{\$개인정보취급방침시행일}}";
		$_hidden_start="{{\$숨김처리시작}}";
		$_company_privacy1_part="{{\$고객서비스담당부서}}";
		$_company_privacy1_phone="{{\$고객서비스전화번호}}";
		$_company_privacy1_email="{{\$고객서비스이메일}}";
		$_company_privacy1_name="{{\$보호관리책임자}}";
		$_company_privacy1_phone="{{\$보호관리전화번호}}";
		$_company_privacy1_email="{{\$보호관리이메일}}";
		$_hidden_end="{{\$숨김처리끝}}";
	}else{
		$_company_name="<?=\$cfg['company_name']?>";
		$_company_privacy_date2="<?=\$cfg['company_privacy_date2']?>";
		$_hidden_start="<?if (\$hidden != \"Y\"){?>";
		$_company_privacy1_part="<?=\$cfg['company_privacy1_part']?>";
		$_company_privacy1_phone="<?=\$cfg['company_privacy1_phone']?>";
		$_company_privacy1_email="<?=\$cfg['company_privacy1_email']?>";
		$_company_privacy1_name="<?=\$cfg['company_privacy1_name']?>";
		$_company_privacy1_phone="<?=\$cfg['company_privacy1_phone']?>";
		$_company_privacy1_email="<?=\$cfg['company_privacy1_email']?>";
		$_hidden_end="<?}?>";
	}

	$content="
<p>'$_company_name'은 (이하 '회사'는) 고객님의 개인정보를 중요시하며, \"정보통신망 이용촉진 및 정보보호\"에 관한 법률을 준수하고 있습니다.
회사는 개인정보취급방침을 통하여 고객님께서 제공하시는 개인정보가 어떠한 용도와 방식으로 이용되고 있으며, 개인정보보호를 위해 어떠한 조치가 취해지고 있는지 알려드립니다.</p>

<p>회사는 개인정보취급방침을 개정하는 경우 웹사이트 공지사항(또는 개별공지)을 통하여 공지할 것입니다.</p>
<p>본 방침은 <strong>$_company_privacy_date2</strong>부터 시행됩니다.</p>
<dl>
	<dt>■ 수집하는 개인정보 항목</dt>
	<dd>회사는 회원가입, 상담, 서비스 신청 등등을 위해 아래와 같은 개인정보를 수집하고 있습니다.
		<dl>
			<dt>ο 수집항목</dt>
	";

	// 수집항목
	$_privacy="";
	foreach($privacy as $key=>$val){
		$_privacy .= ($_privacy) ? " , ".$val : $val;
	}
	$_privacy.= ($privacy_etc) ? " , ".$privacy_etc : "";
	if(!$_privacy) msg("수집항목을 선택하세요");

	// 이용목적
	$_use0=$_use1=$_use2="";
	for($ii=0; $ii<3; $ii++){
		${'use_'.$ii} = $_POST['use_'.$ii];
		if(!is_array(${'use_'.$ii})) continue;
		foreach(${'use_'.$ii} as $key=>$val){
			${'_use'.$ii} .= (${'_use'.$ii}) ? " , ".$val : $val;
		}
	}
	if($_use0.$_use1.$_use2 == "") msg("이용목적을 선택하세요");

	$content .= "
			<dd><strong>$_privacy</strong></dd>
			<dt>ο 개인정보 수집방법</dt>
			<dd><strong>홈페이지(회원가입)</strong></dd>
		</dl>
	</dd>
	<dt>■ 개인정보의 수집 및 이용목적</dt>
	<dd>회사는 수집한 개인정보를 다음의 목적을 위해 활용합니다.
		<dl>
	";
	if($_use0 != ""){
		$content .= "
			<dt>ο 서비스 제공에 관한 계약 이행 및 서비스 제공에 따른 요금정산</dt>
			<dd>
			<strong>$_use0</strong>
			</dd>
		";
	}
	if($_use1 != ""){
		$content .= "
			<dt>ο 회원 관리</dt>
			<dd>
			<strong>$_use1</strong>
			</dd>
		";
	}
	if($_use2 != ""){
		$content .= "
			<dt>ο 마케팅 및 광고에 활용</dt>
			<dd>
			<strong>$_use2</strong>
			</dd>
		";
	}
	if($use_etc != ""){
		$content .= "
			<dt>ο 기타</dt>
			<dd>
			<strong>$use_etc</strong>
			</dd>
		";
	}

	// 파기방법
	$_destroy="";
	foreach($destroy as $key=>$val){
		if(strchr($val,"삭제")) $_destroy .= " - 전자적 파일형태로 저장된 개인정보는 기록을 재생할 수 없는 기술적 방법을 사용하여 삭제합니다.<br>";
		if(strchr($val,"소각")) $_destroy .= " - 종이에 출력된 개인정보는 분쇄기로 분쇄하거나 소각을 통하여 파기합니다.<br>";
	}
	if($destroy_etc != ""){
		$_destroy .= " - $destroy_etc 방법으로 파기합니다.<br>";
	}
	if($_destroy == "") msg("파기방법을 선택하세요");

	$content .= "
		</dl>
	</dd>
	<dt>■ 개인정보의 보유 및 이용기간</dt>
	<dd>회사는 개인정보 수집 및 이용목적이 달성된 후에는 예외 없이 해당 정보를 지체 없이 파기합니다.</dd>

	$_hidden_start

	<dt>■ 개인정보의 파기절차 및 방법</dt>
	<dd>
	회사는 원칙적으로 개인정보 수집 및 이용목적이 달성된 후에는 해당 정보를 지체없이 파기합니다. 파기절차 및 방법은 다음과 같습니다.
		<dl>
			<dt>ο 파기절차</dt>
			<dd>
			<strong>회원님이 회원가입 등을 위해 입력하신 정보는 목적이 달성된 후 별도의 DB로 옮겨져(종이의 경우 별도의 서류함) 내부 방침 및 기타 관련 법령에 의한 정보보호 사유에 따라(보유 및 이용기간 참조) 일정 기간 저장된 후 파기되어집니다.
			별도 DB로 옮겨진 개인정보는 법률에 의한 경우가 아니고서는 보유되어지는 이외의 다른 목적으로 이용되지 않습니다.</strong>
			</dd>
			<dt>ο 파기방법</dt>
			<dd>
			<strong>$_destroy</strong>
			</dd>
		</dl>
	</dd>
	<dt>■ 개인정보 제공</dt>
	<dd>
	회사는 이용자의 개인정보를 원칙적으로 외부에 제공하지 않습니다. 다만, 아래의 경우에는 예외로 합니다.
		<ul>
			<li>이용자들이 사전에 동의한 경우</li>
			<li>법령의 규정에 의거하거나, 수사 목적으로 법령에 정해진 절차와 방법에 따라 수사기관의 요구가 있는 경우</li>
		</ul>
	</dd>
	<dt>■ 수집한 개인정보의 위탁</dt>
	<dd>
	회사는 고객님의 동의없이 고객님의 정보를 외부 업체에 위탁하지 않습니다. 향후 그러한 필요가 생길 경우, 위탁 대상자와 위탁 업무 내용에 대해 고객님에게 통지하고 필요한 경우 사전 동의를 받도록 하겠습니다.
	</dd>
	<dt>■ 이용자 및 법정대리인의 권리와 그 행사방법</dt>
	<dd>
	이용자 및 법정 대리인은 언제든지 등록되어 있는 자신 혹은 당해 만 14세 미만 아동의 개인정보를 조회하거나 수정할 수 있으며 가입해지를 요청할 수도 있습니다.
	이용자 혹은 만 14세 미만 아동의 개인정보 조회?수정을 위해서는 ‘개인정보변경’(또는 ‘회원정보수정’ 등)을 가입해지(동의철회)를 위해서는 “회원탈퇴”를 클릭하여 본인 확인 절차를 거치신 후 직접 열람, 정정 또는 탈퇴가 가능합니다.
	혹은 개인정보관리책임자에게 서면, 전화 또는 이메일로 연락하시면 지체없이 조치하겠습니다.
	귀하가 개인정보의 오류에 대한 정정을 요청하신 경우에는 정정을 완료하기 전까지 당해 개인정보를 이용 또는 제공하지 않습니다. 또한 잘못된 개인정보를 제3자에게 이미 제공한 경우에는 정정 처리결과를 제3자에게 지체없이 통지하여 정정이이루어지도록 하겠습니다.
	회사는 이용자 혹은 법정 대리인의 요청에 의해 해지 또는 삭제된 개인정보는 “회사가 수집하는 개인정보의 보유 및 이용기간”에 명시된 바에 따라 처리하고 그 외의 용도로 열람 또는 이용할 수 없도록 처리하고 있습니다.
	</dd>
	<dt>■ 개인정보 자동수집 장치의 설치 및 그 거부에 관한 사항</dt>
	<dd>
	";
	if($cookie == "Y"){
		$content .= "
		회사는 귀하의 정보를 수시로 저장하고 찾아내는 ‘쿠키(cookie)’ 등을 운용합니다. 쿠키란 회사의 웹사이트를 운영하는데 이용되는 서버가 귀하의 브라우저에 보내는 아주 작은 텍스트 파일로서 귀하의 컴퓨터 하드디스크에 저장됩니다. 회사는 다음과 같은 목적을 위해 쿠키를 사용합니다.

		<p>▶ 쿠키 등 사용 목적</p>
		- 회원과 비회원의 접속 빈도나 방문 시간 등을 분석, 이용자의 취향과 관심분야를 파악 및 자취 추적, 각종 이벤트 참여 정도 및 방문 회수 파악 등을 통한 타겟 마케팅 및 개인 맞춤 서비스 제공

		귀하는 쿠키 설치에 대한 선택권을 가지고 있습니다. 따라서, 귀하는 웹브라우저에서 옵션을 설정함으로써 모든 쿠키를 허용하거나, 쿠키가 저장될 때마다 확인을 거치거나, 아니면 모든 쿠키의 저장을 거부할 수도 있습니다.

		<p>▶ 쿠키 설정 거부 방법</p>
		예: 쿠키 설정을 거부하는 방법으로는 회원님이 사용하시는 웹 브라우저의 옵션을 선택함으로써 모든 쿠키를 허용하거나 쿠키를 저장할 때마다 확인을 거치거나, 모든 쿠키의 저장을 거부할 수 있습니다.

		<p>설정방법 예(인터넷 익스플로어의 경우) <br>
		: 웹 브라우저 상단의 도구 > 인터넷 옵션 > 개인정보 </p>

		<p>단, 귀하께서 쿠키 설치를 거부하였을 경우 서비스 제공에 어려움이 있을 수 있습니다.</p>
		";
	}else{
		$content .= "
		회사는 개인정보 저장을 위한 쿠키(Cookie)를 사용하지 않습니다
		";
	}
	$content .= "
	</dd>
	<dt>■ 개인정보에 관한 민원서비스</dt>
	<dd>
	회사는 고객의 개인정보를 보호하고 개인정보와 관련한 불만을 처리하기 위하여 아래와 같이 관련 부서 및 개인정보관리책임자를 지정하고 있습니다.

	<ul>
		<li>고객서비스담당 부서 : <strong>$_company_privacy1_part</strong></li>
		<li>전화번호 : <strong>$_company_privacy1_phone</strong></li>
		<li>이메일 : <strong>$_company_privacy1_email</strong></li>
	</ul>

	<ul>
		<li>개인정보관리책임자 성명 : <strong>$_company_privacy1_name</strong></li>
		<li>전화번호 : <strong>$_company_privacy1_phone</strong></li>
		<li>이메일 : <strong>$_company_privacy1_email</strong></li>
	</ul>

귀하께서는 회사의 서비스를 이용하시며 발생하는 모든 개인정보보호 관련 민원을 개인정보관리책임자 혹은 담당부서로 신고하실 수 있습니다. 회사는 이용자들의 신고사항에 대해 신속하게 충분한 답변을 드릴 것입니다.

기타 개인정보침해에 대한 신고나 상담이 필요하신 경우에는 아래 기관에 문의하시기 바랍니다.
	<ol>
		<li>개인분쟁조정위원회 (<a href=\"http://www.1336.or.kr/\" target=\"_blank\">www.1336.or.kr/</a>1336)</li>
		<li>정보보호마크인증위원회 (<a href=\"http://www.eprivacy.or.kr/\" target=\"_blank\">www.eprivacy.or.kr/</a>02-580-0533~4)</li>
		<li>대검찰청 인터넷범죄수사센터 (<a href=\"http://icic.sppo.go.kr/\" target=\"_blank\">http://icic.sppo.go.kr/</a>02-3480-3600)</li>
		<li>경찰청 사이버테러대응센터 (<a href=\"http://www.ctrc.go.kr/\" target=\"_blank\">www.ctrc.go.kr/</a>02-392-0330)</li>
	</ol>
	</dd>
$_hidden_end
</dl>
	";

	$content_xml1="<?xml version=\"1.0\" encoding=\"UTF-8\" ?>
<META xmlns=\"http://www.w3.org/2002/01/P3Pv1\">
<POLICY-REFERENCES>
	<POLICY-REF about=\"/w3c/p3policy.xml#privacy1\">
		<INCLUDE>/*</INCLUDE>
	</POLICY-REF>
</POLICY-REFERENCES>
</META>";
	$content_xml2="<?xml version=\"1.0\" encoding=\"UTF-8\" ?>
<POLICIES xmlns=\"http://www.w3.org/2002/01/P3Pv1\">
	<DATASCHEMA>
		<DATA-DEF name=\"dynamic.cookies\" short-description=\"쿠키\">
			<CATEGORIES>
			<state/>
			</CATEGORIES>
		</DATA-DEF>
		<DATA-DEF name=\"user.login.question\" short-description=\"비밀번호 질문\">
			<CATEGORIES>
			<online/>
			</CATEGORIES>
		</DATA-DEF>
		<DATA-DEF name=\"user.home-info.postal\" short-description=\"자택주소\">
			<CATEGORIES>
			<physical/>
			</CATEGORIES>
		</DATA-DEF>
		<DATA-DEF name=\"user.home-info.telecom.mobile\" short-description=\"휴대전화번호\">
			<CATEGORIES>
			<physical/>
			</CATEGORIES>
		</DATA-DEF>
		<DATA-DEF name=\"user.business-info.department\" short-description=\"회사명 \">
			<CATEGORIES>
			<demographic/>
			</CATEGORIES>
		</DATA-DEF>
		<DATA-DEF name=\"user.business-info.postal.name\" short-description=\"회사명 \">
			<CATEGORIES>
			<demographic/>
			</CATEGORIES>
		</DATA-DEF>
		<DATA-DEF name=\"user.job-type\" short-description=\"직업종류\">
			<CATEGORIES>
			<physical/>
			</CATEGORIES>
		</DATA-DEF>
		<DATA-DEF name=\"user.social-number\" short-description=\"주민등록번호\">
			<CATEGORIES>
			<government/>
			</CATEGORIES>
		</DATA-DEF>
		<DATA-DEF name=\"user.hobby\" short-description=\"취미\">
			<CATEGORIES>
			<physical/>
			</CATEGORIES>
		</DATA-DEF>
		<DATA-DEF name=\"user.religion\" short-description=\"거주지역\">
			<CATEGORIES>
			<demographic/>
			</CATEGORIES>
		</DATA-DEF>
		<DATA-DEF name=\"user.wedding.wedding-y-or-n\" short-description=\"기혼여부\">
			<CATEGORIES>
			<physical/>
			</CATEGORIES>
		</DATA-DEF>
		<DATA-DEF name=\"user.anniversary\" short-description=\"기념일\">
			<CATEGORIES>
			<physical/>
			</CATEGORIES>
		</DATA-DEF>
		<DATA-DEF name=\"user.school-carrier\" short-description=\"학력\">
			<CATEGORIES>
			<physical/>
			</CATEGORIES>
		</DATA-DEF>
		<DATA-DEF name=\"user.body\" short-description=\"신체정보\">
			<CATEGORIES>
			<health/>
			</CATEGORIES>
		</DATA-DEF>
		<DATA-DEF name=\"user.credit-card\" short-description=\"신용카드 정보\">
			<CATEGORIES>
			<financial/>
			</CATEGORIES>
		</DATA-DEF>
		<DATA-DEF name=\"user.account\" short-description=\"금융계좌 정보\">
			<CATEGORIES>
			<financial/>
			</CATEGORIES>
		</DATA-DEF>
		<DATA-DEF name=\"dynamic.payment\" short-description=\"결제 기록\">
			<CATEGORIES>
			<purchase/>
			</CATEGORIES>
		</DATA-DEF>
		<DATA-DEF name=\"user.agent\" short-description=\"대리인 정보\">
			<CATEGORIES>
			<other-category> 대리인 정보</other-category>
			</CATEGORIES>
		</DATA-DEF>
		<DATA-DEF name=\"user.other-contents\" short-description=\"기타 정보\">
			<CATEGORIES>
			<other-category>기타 정보</other-category>
			</CATEGORIES>
		</DATA-DEF>
	</DATASCHEMA>
	<POLICY name=\"privacy1\" opturi=\"$root_url/member/edit_step2.php\" discuri=\"$root_url/content/content.php?cont=privacy\" >
		<ENTITY>
			<DATA-GROUP>
				<DATA ref=\"#business.name\">$cfg[company_name]</DATA>
				<DATA ref=\"#business.contact-info.online.uri\">$root_url</DATA>
			</DATA-GROUP>
		</ENTITY>
		<ACCESS>
			<all/>
			<EXTENSION optional=\"no\">
				<USER-RIGHT>
					이용자 및 법정 대리인은 언제든지 등록되어 있는 자신 혹은 당해 만 14세 미만
아동의 개인정보를 조회하거나 수정할 수 있으며 가입해지를 요청할 수도 있습니
다.
이용자 혹은 만 14세 미만 아동의 개인정보 조회?수정을 위해서는 ‘개인정보변
경’(또는 ‘회원정보수정’ 등)을 가입해지(동의철회)를 위해서는 “회원탈퇴”를 클릭
하여 본인 확인 절차를 거치신 후 직접 열람, 정정 또는 탈퇴가 가능합니다.
혹은 개인정보관리책임자에게 서면, 전화 또는 이메일로 연락하시면 지체없이 조
치하겠습니다.
귀하가 개인정보의 오류에 대한 정정을 요청하신 경우에는 정정을 완료하기 전까
지 당해 개인정보를 이용 또는 제공하지 않습니다. 또한 잘못된 개인정보를 제3자
에게 이미 제공한 경우에는 정정 처리결과를 제3자에게 지체없이 통지하여 정정이
이루어지도록 하겠습니다.
회사는 이용자 혹은 법정 대리인의 요청에 의해 해지 또는 삭제된 개인정보는
“회사가 수집하는 개인정보의 보유 및 이용기간”에 명시된 바에 따라 처리하고 그 외의
용도로 열람 또는 이용할 수 없도록 처리하고 있습니다.
				</USER-RIGHT>
				<advertising>
				<option/>
				</advertising>
			</EXTENSION>
		</ACCESS>
		<DISPUTES-GROUP>
			<DISPUTES resolution-type=\"service\" service=\"$root_url/content/content.php?cont=privacy\" short-description=\"$cfg[company_name]\">
				<EXTENSION optional=\"no\">
					<DATA ref=\"#business.pdepart.name\">$cfg[company_privacy1_part]</DATA>
					<DATA ref=\"#business.pdepart.email\">$cfg[company_privacy1_email]</DATA>
					<DATA ref=\"#business.pdepart.telephone\">$cfg[company_privacy1_phone]</DATA>
					<DATA ref=\"#business.cpo.name\">$cfg[company_privacy1_name]</DATA>
					<DATA ref=\"#business.cpo.email\">$cfg[company_privacy1_email]</DATA>
					<DATA ref=\"#business.cpo.telephone\">$cfg[company_privacy1_phone]</DATA>
				</EXTENSION>
				<REMEDIES><correct/><money/><law/></REMEDIES>
			</DISPUTES>
		</DISPUTES-GROUP>
		<STATEMENT>
			<EXTENSION optional=\"no\">
				<COLLECTION-METHOD>
					<other-method>
					<website/>
					</other-method>
				</COLLECTION-METHOD>
				<DESTRUCTION-METHOD>
					<format/>";
	if($destroy_etc != ""){
		$content_xml2 .= "
					<shatter/>
					<other-method>
					$destroy_etc
					</other-method>";
	}
	$content_xml2 .= "
				</DESTRUCTION-METHOD>
			</EXTENSION>
			<PURPOSE>
			<individual-decision/>

				<EXTENSION optional=\"no\">
					<PPURPOSE>
						<payment/><delivery/><finmgt/><login/><session/><marketing/>
					</PPURPOSE>
					<content/><cert/><age/><complaint/><statement/>
				</EXTENSION>
			</PURPOSE>
			<RECIPIENT>
				<ours></ours>
			</RECIPIENT>
			<RETENTION>
				<legal-requirement/>
				<EXTENSION optional=\"no\">
					<use-duration>
											<instance/>

					</use-duration>
					<retention-basis>
					</retention-basis>
				</EXTENSION>
			</RETENTION>
			<DATA-GROUP base=\"\">
				<DATA ref=\"#user.social-number\"/>
				<DATA ref=\"#dynamic.payment\"/>
			</DATA-GROUP>
			<DATA-GROUP>
				<DATA ref=\"#user.name\"/>
				<DATA ref=\"#user.gender\"/>
				<DATA ref=\"#user.login.id\"/>
				<DATA ref=\"#user.login.password\"/>
				<DATA ref=\"#user.home-info.telecom.telephone\"/>
				<DATA ref=\"#user.home-info.postal\"/>
				<DATA ref=\"#user.home-info.telecom.mobile\"/>
				<DATA ref=\"#user.home-info.online.email\"/>
				<DATA ref=\"#dynamic.clientevents\"/>
				<DATA ref=\"#dynamic.interactionrecord\"/>
				<DATA ref=\"#dynamic.clickstream.clientip\"/>
			</DATA-GROUP>
		</STATEMENT>
	</POLICY>
</POLICIES>
	";

	$xml1=@fopen($root_dir."/w3c/p3p.xml", "w");
	$xml2=@fopen($root_dir."/w3c/p3policy.xml", "w");

	if(!$xml1 || !$xml2) msg("XML 파일실행에러발생! 1:1고객센터 문의 글로 접수 바랍니다.");

	$content_xml2=iconv("EUC-KR", "UTF-8", $content_xml2);
	$xml1w=fwrite($xml1, $content_xml1);
	$xml2w=fwrite($xml2, $content_xml2);

	fclose($xml1);
	fclose($xml2);

	server_sync($root_dir."/w3c/p3p.xml");
	server_sync($root_dir."/w3c/p3policy.xml");
	chmod($root_dir."/w3c/p3p.xml", 0777);
	chmod($root_dir."/w3c/p3policy.xml", 0777);

	if($cfg[design_version] == "V3"){
		include_once $engine_dir."/_engine/include/img_ftp.lib.php";
		include_once $engine_dir."/_engine/include/design.lib.php";
		include_once $engine_dir."/_manage/design/version_check.php";
		$_filedir=$root_dir."/_skin/".$design[skin]."/CORE/content_privacy.".$_skin_ext[p];
		$_filebakdir=$root_dir."/_data/content_privacy.".$_skin_ext[p];
		$of=fopen($_filebakdir, "w");
		$fw=fwrite($of, $content);
		if(!$fw) msg("계정디렉토리 권한이 잘못되어있습니다. 1:1고객센터 문의 글로 접수 바랍니다.");
		fclose($of);

		$file[name]="content_privacy.".$_skin_ext[p];
		$file[tmp_name]=$_filebakdir;
		ftpUploadFile($root_dir."/_skin/".$design[skin]."/CORE", $file, $_skin_ext[p]);
		unlink($file[tmp_name]);
	}else{
		$cont_file=$root_dir."/_template/content/privacy.php";

		$fp=@fopen($cont_file, "w");
		$w=fwrite($fp, $content);
		if(!$w) msg("파일권한 문제로 실패하였습니다. 1:1고객센터 문의 글로 접수 바랍니다.");
		fclose($fp);
		chmod($cont_file,0777);

		server_sync($cont_file);
	}

	msg("수정되었습니다","reload","parent");
?>