<?php

use Wing\HTTP\CurlConnection;

/**
 * ERP APIKey 관리자 목록
 **/

// API 세팅 테스트
$curl = new CurlConnection($root_url.'/api/erp/apitest');
$curl->exec();
$info = $curl->getInfo();
if ($info['http_code'] != '200') {
    echo "
        <div class='msg_topbar warning' style='margin-top:10px; border:1px solid #c9c9c9;'>
            API 사용을 위한 서버세팅이 되어있지 않습니다. 고객센터로 문의하세요.
        </div>
    ";
    return;
}

// mod-rewirte 체크
if (isTable($tbl['erp_api']) == false) {
    include_once __ENGINE_DIR__.'/_config/tbl_schema.php';
    $r = $pdo->query($tbl_schema['erp_api']);
    if ($r == false) {
        echo "
            <div class='msg_topbar warning' style='margin-top:10px; border:1px solid #c9c9c9;'>
                서비스를 설정할수 없습니다.\\n테이블 생성권한이 없습니다.
            </div>
        ";
    }
}

// api 리스트
$res = $pdo->iterator("select * from {$tbl['erp_api']} order by idx desc");

function parseAPIKey(&$res)
{
    $data = $res->current();
    if ($data == null) return false;

    $data['on'] = ($data['is_active'] == 'Y') ? 'on' : '';

    $res->next();
    return $data;
}

?>
<div class="box_title first">
    <h2 class="title">ERP API연동키 설정</h2>
</div>
<table class="tbl_col">
    <colgroup>
        <col>
        <col>
        <col style="width: 90px;">
        <col style="width: 90px;">
        <col style="width: 90px;">
        <col style="width: 90px;">
    </colgroup>
    <thead>
        <tr>
            <th scope="col">연결 서비스명</th>
            <th scope="col">API 키</th>
            <th scope="col">사용</th>
            <th scope="col">키 재발급</th>
            <th scope="col">수정</th>
            <th scope="col">삭제</th>
        </tr>
    </thead>
    <tbody>
        <?php while ($data = parseAPIKey($res)) { ?>
        <tr>
            <td class="left"><?=$data['name']?></td>
            <td>
                <span class="clipboard" data-clipboard-text="<?=$data['apikey']?>">
                    <?=$data['apikey']?>
                    <span class="box_btn_s2"><input type="button" value="복사"></span>
                </span>
            </td>
			<td>
				<div class="switch <?=$data['on']?>" onclick="toggleKey(<?=$data['idx']?>, this)"></div>
			</td>
            <td><span class="box_btn_s"><input type="button" value="재발급" onclick="regenerateKey(<?=$data['idx']?>)"></span></td>
            <td><span class="box_btn_s"><input type="button" value="수정" onclick="keyGen.open('idx=<?=$data['idx']?>')"></span></td>
            <td><span class="box_btn_s"><input type="button" value="삭제" onclick="removeKey(<?=$data['idx']?>)"></span></td>
        </tr>
        <?php } ?>
        <?php if (is_object($res) == false) { ?>
        <tr class="none">
            <td colspan="6"><p class="nodata">생성된 API연동키가 없습니다.</p></td>
        </tr>
        <?php } ?>
    </tbody>
</table>
<div class="box_middle2" style="height: 30px">
    <div class="right_area">
        <span class="box_btn_s icon regist"><input type="button" value="등록" onclick="keyGen.open()"></span>
    </div>
</div>
<div class="box_bottom">
    <ul class="list_info left">
        <li>API Key 발급을 이용하여 다양한 서비스를 연동하실 수 있습니다.</li>
        <li>API 키 발급 후 재발급을 사용하실 경우 연동된 서비스가 중단될 수 있습니다.</li>
    </ul>
</div>
<script type="text/javascript">
var keyGen = new layerWindow('config@apikey.pop');

function toggleKey(idx, o) {
    $.post('./index.php', {'body':'config@apikey.exe', 'exec':'toggle', 'idx':idx}, function(r) {
        if (r.status == 'Y') $(o).addClass('on');
        else  $(o).removeClass('on');
    });
}

function regenerateKey(idx) {
    if (confirm('API키를 재발급 합니다.\n기존 서비스 연동이 해제됩니다.') == true) {
        printLoading();
        $.post('./index.php', {'body':'config@apikey.exe', 'exec':'regenerate', 'idx':idx}, function() {
            location.reload();
        });
    }
}

function removeKey(idx) {
    if (confirm('API키를 삭제하시겠습니까?\n기존 서비스 연동이 해제됩니다.') == true) {
        printLoading();
        $.post('./index.php', {'body':'config@apikey.exe', 'exec':'remove', 'idx':idx}, function() {
            location.reload();
        });
    }
}

$(function() {
    chainCheckbox($('.all_check'), $('.sub_check'));
	new Clipboard('.clipboard').on('success', function(e) {
		window.alert('코드가 복사되었습니다.');
	});
});

$('body').on('keyup', function(e) {
    console.log(e.keyCode);
});
</script>