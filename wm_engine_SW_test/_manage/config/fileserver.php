<?PHP

	/* +----------------------------------------------------------------------------------------------+
	' |  파일서버 설정
	' +----------------------------------------------------------------------------------------------+*/
	if(!$cfg['file_server_option']) $cfg['file_server_option']="1";
	if(!$cfg['file_server_ea']) $cfg['file_server_ea'] = "0";
	if(empty($cfg['use_file_server']) == true) $cfg['use_file_server'] = 'N';

    // db load banance
    if (file_exists($root_dir.'/_data/config/db_read.json')) {
        $db_list = file_get_contents($root_dir.'/_data/config/db_read.json');
        $db_list = json_decode($db_list);
        $db_cnt = count($db_list);
    }
    if (!$db_cnt) {
        $db_list = array();
        $db_cnt = 1;
    }

    $scfg->def('use_mail_server', 'N');

?>
<form method="post" action="<?=$_SERVER['PHP_SELF']?>" target="hidden<?=$now?>" onsubmit="printLoading()">
	<input type="hidden" name="body" value="config@config.exe">
	<div class="box_title first">
		<h2 class="title">파일서버 사용 옵션</h2>
	</div>
	<table class="tbl_row">
		<caption class="hidden">운영자 정보</caption>
		<colgroup>
			<col style="width:15%">
			<col>
		</colgroup>
		<tr>
			<th scope="row">파일서버 사용</th>
			<td>
				<label class="p_cursor"><input type="radio" name="file_server_option" value="1" <?=checked($cfg['file_server_option'],'1')?>> 웹서버와 동일한 서버를 사용합니다 (서버 1대구성)</label><br>
				<label class="p_cursor"><input type="radio" name="file_server_option" value="2" <?=checked($cfg['file_server_option'],'2')?>> 웹서버와 상품 이미지 파일서버를 분리합니다 (서버 2대이상 구성)</label><br>
				└ 총 추가 서버 대수를 입력해 주십시오(웹서버 로드밸런싱의 경우 모든 서버 입력)
				<input type="text" name="file_server_ea" value="<?=$cfg['file_server_ea']?>" class="input" size="2"> 대
			</td>
		</tr>
	</table>
	<div class="box_bottom">
		<span class="box_btn blue"><input type="submit" value="확인"></span>
	</div>
</form>

<?PHP
	$file_server_info = 'block';
	if($cfg['file_server_option'] == "1"){
		if(@is_file($_file_server_dir)) include_once $engine_dir."/_engine/include/img_ftp.lib.php";
		$file_server_info = 'none';
	}
	if(@is_file($_file_server_dir)) include_once $_file_server_dir;
	$fs_category = array (
		"loadbalance" => "웹서버(로드 밸런싱)",
		"product" => "상품이미지",
		"attach" => "상품 상세 이미지",
		"review" => "리뷰 이미지",
		"qna" => "상품문의 이미지",
		"mari_board" => "게시판 첨부파일",
		"image_ftp" => "이미지 FTP",
		"popup" => "팝업 이미지",
		"banner" => "디자인 배너",
	);
	$total_server = ($cfg['file_server_option'] == '2') ? 1 : 2;
?>
<form method="post" action="<?=$_SERVER['PHP_SELF']?>" target="hidden<?=$now?>" onsubmit="return filefrmCk(this);" style="display:<?=$file_server_info?>">
	<input type="hidden" name="body" value="config@fileserver.exe">
	<div class="box_title">
		<h2 class="title">추가서버 정보</h2>
	</div>
	<div class="box_middle">
		<ul class="list_msg left">
			<li>현재 운영중이던 사이트의 경우 계정정보를 입력하기전 <u>해당 서버내에 기존 데이터가 설치</u>된 상태여야 하며</li>
			<li>부주의한 변경으로 인해 발생된 피해에 대해서는 책임지지 않으므로 충분한 협의가 이루어진 상태에서 변경해주시기 바랍니다.</li>
		</ul>
	</div>
	<table class="tbl_row">
		<caption class="hidden">추가서버 정보</caption>
		<colgroup>
			<col style="width:10%">
			<col style="width:15%">
			<col>
		</colgroup>
		<?php for ($ii=1; $ii <= $cfg['file_server_ea']; $ii++) { ?>
		<tr>
			<th scope="rowgroup" rowspan="8" class="line_r">추가서버 <?=$ii?></th>
			<th scope="row">서버 명칭</th>
			<td>
				<input type="text" name="ftp_name<?=$ii?>" value="<?=$file_server[$ii]['name']?>" class="input"> (예 : 파일서버 #1)
			</td>
		</tr>
		<tr>
			<th scope="row">IP 또는 도메인주소</th>
			<td>
				<input type="text" name="ftp_addr<?=$ii?>" value="<?=$file_server[$ii]['file_server'][0]?>" class="input"> (http:// 제외)
			</td>
		</tr>
		<tr>
			<th scope="row">ID</th>
			<td><input type="text" name="ftp_id<?=$ii?>" value="<?=$file_server[$ii]['file_server'][1]?>" class="input"></td>
		</tr>
		<tr>
			<th scope="row">Password</th>
			<td><input type="password" name="ftp_pwd<?=$ii?>" value="<?=$file_server[$ii]['file_server'][2]?>" class="input"></td>
		</tr>
		<tr>
			<th scope="row">Port</th>
			<td><input type="text" name="ftp_port<?=$ii?>" value="<?=$file_server[$ii]['file_server'][3]?>" class="input"></td>
		</tr>
		<tr>
			<th scope="row">웹접속 URL</th>
			<td><input type="text" name="url<?=$ii?>" value="<?=$file_server[$ii]['url']?>" class="input"> (예 : http://file<?=$ii?>.xxxxxx.com)</td>
		</tr>
		<tr>
			<th scope="row">기본 디렉토리명</th>
			<td><input type="text" name="file_dirname<?=$ii?>" value="<?=$file_server[$ii]['file_dirname']?>" class="input"> (예 : public_html)</td>
		</tr>
		<tr>
			<th scope="row">서버용도</th>
			<td>
				<ul>
					<?php
						if(!is_array($file_server[$ii]['file_type'])) $file_server[$ii]['file_type'] = array();
						foreach($fs_category as $key => $val) {
							$checked = (in_array($key, $file_server[$ii]['file_type'])) ? 'checked' : '';
							echo "<li><label class=\"p_cursor\"><input type=\"checkbox\" name=\"fs_category[$ii][]\" value=\"$key\" $checked> $val</label></li>";
					}
					?>
				</ul>
			</td>
		</tr>
		<?php } ?>
	</table>
	<div class="box_bottom">
		<span class="box_btn blue"><input type="submit" value="확인"></span>
	</div>
</form>

<script type="text/javascript">
	function filefrmCk(f){
		if(!checkBlank(f.ftp_addr1, "IP 또는 도메인주소를 입력해주세요.")) return false;
		if(!checkBlank(f.ftp_id1, "ID 를 입력해주세요.")) return false;
		if(!checkBlank(f.ftp_pwd1, "Password 를 입력해주세요.")) return false;
		if(!checkBlank(f.ftp_port1, "접속 Port 를 입력해주세요.")) return false;
		if(!checkBlank(f.url1, "웹접속 URL을 입력해주세요.")) return false;
		<?php if($cfg['file_server_option'] == 3){ ?>
		if(!checkBlank(f.ftp_addr2, "IP 또는 도메인주소를 입력해주세요.")) return false;
		if(!checkBlank(f.ftp_id2, "ID 를 입력해주세요.")) return false;
		if(!checkBlank(f.ftp_pwd2, "Password 를 입력해주세요.")) return false;
		if(!checkBlank(f.ftp_port2, "접속 Port 를 입력해주세요.")) return false;
		if(!checkBlank(f.url2, "웹접속 URL을 입력해주세요.")) return false;
		<?php } ?>

        printLoading();
	}
</script>

<form method="post" action="<?=$_SERVER['PHP_SELF']?>" target="hidden<?=$now?>" onsubmit="printLoading()">
	<input type="hidden" name="body" value="config@config.exe">
	<div class="box_title">
		<h2 class="title">메일서버 설정</h2>
	</div>
	<div class="box_middle">
		<ul class="list_msg left">
			<li>위사 메일 서버가 아닌 자체 메일서버를 운영하실 경우 설정해주세요.</li>
			<li>설정 전 메일서버 설치 및 <a href="https://spam.kisa.or.kr/white/sub1.do" target="_blank">화이트도메인 등록</a> 등이 모두 완료되었는지 확인해주시기 바랍니다.</li>
		</ul>
	</div>
	<table class="tbl_row">
		<caption class="hidden">메일서버 설정</caption>
		<colgroup>
			<col style="width:15%">
			<col>
		</colgroup>
		<tr>
			<th scope="row" >자체 메일서버 사용</th>
			<td>
				<label><input type="radio" name="use_mail_server" value="Y" <?=checked($cfg['use_mail_server'], 'Y')?>> 사용함</label>
				<label><input type="radio" name="use_mail_server" value="N" <?=checked($cfg['use_mail_server'], 'N')?>> 사용안함</label>
			</td>
		</tr>
	</table>
	<div class="box_bottom">
		<span class="box_btn blue"><input type="submit" value="확인"></span>
	</div>
</form>

<form action="./index.php" method="POST" onsubmit="return setReadDB($(this))">
    <input type="hidden" name="body" value="config@db_read.exe">
	<div class="box_title">
		<h2 class="title">데이터베이스 로드 밸런스 설정</h2>
	</div>
    <table class="tbl_row">
        <caption class="hidden">읽기 데이터베이스 밸런스 설정</caption>
        <colgroup>
            <col style="width:15%">
            <col>
        </colgroup>
        <tr>
            <th scope="row">데이터베이스 등록</th>
            <td>
                <ul id="db_list">
                    <?php for($i = 0; $i < $db_cnt; $i++) { ?>
                    <li style="padding: 2px 0;">
                        <input type="text" name="db_read_ip[]" value="<?=$db_list[$i][0]?>" class="input" placeholder="Server IP" size="15">
                        <input type="text" name="db_read_rt[]" value="<?=$db_list[$i][1]?>" class="input" placeholder="Rate" size="4"> %
                        <span class="box_btn_s"><input type="button" value="추가" onclick="addServer(this)"></span>
                        <span class="box_btn_s"><input type="button" value="삭제" onclick="removeServer(this)"></span>
                    </li>
                    <?php } ?>
                </ul>
            </td>
        </tr>
    </table>
    <div class="box_middle2">
        <ul class="list_info left">
            <li>DB서버의 아이피를 제외한 다른 계정정보는 기본 데이터베이스 정보와 동일해야 합니다.</li>
            <li>잘못 설정하실 경우 사이트 접속이 안될수 있습니다.</li>
        </ul>
    </div>
	<div class="box_bottom">
		<span class="box_btn blue"><input type="submit" value="확인"></span>
	</div>
</form>
<script type="text/javascript">
function addServer(o)
{
    var origin = $(o).parents('li');
    var copy = origin.clone();
    copy.find('input[type=text]').val('');
    origin.after(copy);
}

function removeServer(o)
{
    if ($('#db_list>li').length == 1) {
        $(o).parents('li')
            .find('input[type=text]')
            .val('');
        return false;
    }
    $(o).parents('li').remove();
}

function setReadDB(f)
{
    var err = false;

    if (confirm('잘못된 설정이 적용될 경우 쇼핑몰과 관리자 접속이 불가능합니다.\n정확한 설정이 맞는지 한번 더 확인해주세요.\n정말로 설정한대로 적용하시겠습니까?') == false) {
        return false;
    }

    if ($('#db_list>li').length > 1) {
        f.find('[name="db_read_ip[]"]').each(function(idx) {
            if (this.value == '') {
                err = true;
                $(this).focus();
                window.alert((idx+1)+'번 데이터베이스 서버의 아이피를 입력해주세요.');
                return false;
            }
        });

        if (err == true) return false;

        var total_rate = 0;
        f.find('[name="db_read_rt[]"]').each(function(idx) {
            var rate = parseInt(this.value);
            if (isNaN(rate) == true) {
                err = true;
                $(this).focus();
                window.alert((idx+1)+'번 데이터베이스 서버의 접근 비율을 입력해주세요.');
                return false;
            }
            total_rate += rate;
        });
        if (total_rate != 100) {
            window.alert('총 접근 비율의 합이 100%가 되어야합니다.');
            return false;
        }
    }

    printLoading();
    $.post('./index.php', f.serialize(), function(r) {
        removeLoading();
    });

    return false;
}
</script>