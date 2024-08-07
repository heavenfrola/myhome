<?PHP

	/* +----------------------------------------------------------------------------------------------+
	' |  쇼핑몰정보 설정
	' +----------------------------------------------------------------------------------------------+*/

?>
<form name="" method="post" action="<?=$_SERVER['PHP_SELF']?>" target="hidden<?=$now?>" onsubmit="return essentialCheck(this)">
	<input type="hidden" name="body" value="config@config.exe">
	<div class="box_title first">
		<h2 class="title">운영자 정보</h2>
	</div>
	<table class="tbl_row">
		<caption class="hidden">운영자 정보</caption>
		<colgroup>
			<col style="width:15%">
			<col>
		</colgroup>
		<tr>
			<th scope="row"><strong>실명</strong></th>
			<td><input type="text" name="admin_name" value="<?=inputText($cfg['admin_name'])?>" class="input"></td>
		</tr>
		<tr>
			<th scope="row">닉네임</th>
			<td>
				<input type="text" name="admin_nick" value="<?=inputText($cfg['admin_nick'])?>" class="input">
				<span class="explain">게시판에서 사용됩니다</span>
			</td>
		</tr>
		<tr>
			<th scope="row"><strong>대표 이메일</strong></th>
			<td>
				<input type="text" name="admin_email" value="<?=inputText($cfg['admin_email'])?>" class="input">
				<span class="explain">주문 메일</span>
			</td>
		</tr>
		<tr>
			<th scope="row"><strong>휴대폰</strong></th>
			<td>
				<input type="text" name="admin_cell" value="<?=inputText($cfg['admin_cell'])?>" class="input">
				<span class="explain">업데이트 소식, 보안 패치등의 문자 메시지를 받으실 수 있습니다</span>
			</td>
		</tr>
	</table>
	<div class="box_bottom">
		<span class="box_btn blue"><input type="submit" value="확인"></span>
	</div>
</form>
<form name="" method="post" action="<?=$_SERVER['PHP_SELF']?>" target="hidden<?=$now?>" onsubmit="printLoading()">
    <input type="hidden" name="body" value="config@config.exe">
    <div class="box_title">
        <h2 class="title">개인정보처리방침</h2>
    </div>
    <table class="tbl_row">
        <caption class="hidden">개인정보처리방침</caption>
        <colgroup>
            <col style="width:15%">
            <col>
        </colgroup>
        <tbody><tr>
            <td colspan="2"><b>기본 정보</b></td>
        </tr>
        <tr>
            <th scope="row">시행일자</th>
            <td><input type="text" name="company_privacy_date2" value="<?=$cfg['company_privacy_date2']?>" class="input"></td>
        </tr>
        <tr>
            <th scope="row">개인정보수집항목</th>
            <td><input type="text" name="company_privacy_items" value="<?=$cfg['company_privacy_items']?>" class="input input_full"></td>
        </tr>
        <tr>
            <th scope="row">개인정보수집방법</th>
            <td><input type="text" name="company_privacy_get" value="<?=$cfg['company_privacy_get']?>" class="input"></td>
        </tr>

        <tr>
            <td colspan="2"><b>고객서비스 담당부서</b></td>
        </tr>
        <tr>
            <th scope="row">고객서비스 담당부서</th>
            <td><input type="text" name="company_privacy1_part" value="<?=$cfg['company_privacy1_part']?>" class="input"></td>
        </tr>
        <tr>
            <th scope="row">이메일</th>
            <td><input type="text" name="company_privacy1_email" value="<?=$cfg['company_privacy1_email']?>" class="input"></td>
        </tr>
        <tr>
            <th scope="row">전화번호</th>
            <td><input type="text" name="company_privacy1_phone" value="<?=$cfg['company_privacy1_phone']?>" class="input"></td>
        </tr>
        <tr>
            <td colspan="2"><b>개인정보보호책임자</b></td>
        </tr>
        <tr>
            <th scope="row">성명</th>
            <td><input type="text" name="company_privacy2_name" value="<?=$cfg['company_privacy2_name']?>" class="input"></td>
        </tr>
        <tr>
            <th scope="row">이메일</th>
            <td><input type="text" name="company_privacy2_email" value="<?=$cfg['company_privacy2_email']?>" class="input"></td>
        </tr>
        <tr>
            <th scope="row">전화번호</th>
            <td><input type="text" name="company_privacy2_phone" value="<?=$cfg['company_privacy2_phone']?>" class="input"></td>
        </tr>
        </tbody></table>
    <div class="box_bottom">
        <span class="box_btn blue"><input type="submit" value="확인"></span>
    </div></form>

<form name="joinFrm" method="post" action="<?=$_SERVER['PHP_SELF']?>" target="hidden<?=$now?>">
	<input type="hidden" name="body" value="config@config.exe">
	<input type="hidden" name="config_code" value="shop_info">
	<div class="box_title">
		<h2 class="title">업체 정보 (전자상거래를 위한 사이버몰 표시사항, 공정위 표준 약관, 각종 메일)</h2>
	</div>
	<table class="tbl_row">
		<caption class="hidden">업체 정보 (전자상거래를 위한 사이버몰 표시사항, 공정위 표준 약관, 각종 메일)</caption>
		<colgroup>
			<col style="width:15%">
			<col>
		</colgroup>
		<tr>
			<th scope="row">상호</th>
			<td><input type="text" name="company_name" value="<?=inputText($cfg['company_name'])?>" class="input"></td>
		</tr>
		<tr>
			<th scope="row">사업자 등록번호</th>
			<td><input type="text" name="company_biz_num" value="<?=inputText($cfg['company_biz_num'])?>" class="input"></td>
		</tr>
		<tr>
			<th scope="row">통신판매신고번호</th>
			<td><input type="text" name="company_online_num" value="<?=inputText($cfg['company_online_num'])?>" class="input"></td>
		</tr>
		<tr>
			<th scope="row">업태</th>
			<td><input type="text" name="company_biz_type1" value="<?=inputText($cfg['company_biz_type1'])?>" class="input"></td>
		</tr>
		<tr>
			<th scope="row">종목</th>
			<td><input type="text" name="company_biz_type2" value="<?=inputText($cfg['company_biz_type2'])?>" class="input"></td>
		</tr>
		<tr>
			<th scope="row">대표자 성명</th>
			<td><input type="text" name="company_owner" value="<?=inputText($cfg['company_owner'])?>" class="input"></td>
		</tr>
		<tr>
			<th scope="row">이메일</th>
			<td><input type="text" name="company_email" value="<?=inputText($cfg['company_email'])?>" class="input"></td>
		</tr>
		<tr>
			<th scope="row">전화번호</th>
			<td><input type="text" name="company_phone" value="<?=inputText($cfg['company_phone'])?>" class="input"></td>
		</tr>
		<tr>
			<th scope="row">팩스</th>
			<td><input type="text" name="company_fax" value="<?=inputText($cfg['company_fax'])?>" class="input"></td>
		</tr>
		<tr>
			<th scope="row">사업장 주소</th>
			<td>
				<input type="text" name="company_zip" value="<?=inputText($cfg['company_zip'])?>" class="input"  size="7">
				<span class="box_btn_s"><input type="button" value="우편번호검색" class="btn2" onClick="zipSearch('joinFrm','company_zip','company_addr1','company_addr2')"></span><br>
				<span style="display:block; padding:5px 0;"><input type="text" name="company_addr1" value="<?=inputText($cfg['company_addr1'])?>" class="input input_full" maxlength="50"></span>
				<input type="text" name="company_addr2" value="<?=inputText($cfg['company_addr2'])?>" class="input input_full" maxlength="100">
			</td>
		</tr>
		<tr>
			<th scope="row">반품 주소</th>
			<td>
				<input type="text" name="return_zip" value="<?=inputText($cfg['return_zip'])?>" class="input"  size="7">
				<span class="box_btn_s"><input type="button" value="우편번호검색" class="btn2" onClick="zipSearch('joinFrm','return_zip','return_addr1','return_addr2')"></span><br>
				<span style="display:block; padding:5px 0;"><input type="text" name="return_addr1" value="<?=inputText($cfg['return_addr1'])?>" class="input input_full" maxlength="50"></span>
				<input type="text" name="return_addr2" value="<?=inputText($cfg['return_addr2'])?>" class="input input_full" maxlength="100">
			</td>
		</tr>
	</table>
	<div class="box_bottom">
		<span class="box_btn blue"><input type="submit" value="확인"></span>
	</div>
</form>
<script type="text/javascript">
	function essentialCheck(f) {
		if(!checkBlank(f.admin_name,'실명을 입력해주세요.')) return false;
		if(!checkBlank(f.admin_email,'대표 이메일을 입력해주세요.')) return false;
		if(!checkBlank(f.admin_cell,'휴대폰번호를 입력해주세요')) return false;
	}
</script>
<?return?>
<form name="" method="post" action="<?=$_SERVER['PHP_SELF']?>" target="hidden<?=$now?>">
	<input type="hidden" name="body" value="config@config.exe">
	<?
		$privacy_file = ( $cfg['privacy'] == "Y" ) ? "new" : "old";
		include ("info_privacy_".$privacy_file.".inc.php");
	?>
</form>