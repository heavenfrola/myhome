<?php

/**
 * ERP APIKey 관리자 등록 폼
 **/

if ($_GET['idx'] > 0) {
    $data = $pdo->assoc(
        "select * from {$tbl['erp_api']} where idx=?",
        array($_GET['idx'])
    );
}

?>
<form method="POST" action="./index.php" onsubmit="this.target=hid_frame; printLoading();">
    <input type="hidden" name="body" value="config@apikey.exe">
    <input type="hidden" name="idx" value="<?=$data['idx']?>">

    <table class="tbl_row">
        <caption class="hidden">ERP API연동키 설정</caption>
        <colgroup>
            <col style="width:20%">
            <col>
        </colgroup>
        <tbody>
            <tr>
                <th scope="row">연결 서비스명</th>
                <td>
                    <input type="text" name="name" class="input block" value="<?=inputText($data['name'])?>">
                </td>
            </tr>
        </tbody>
    </table>
    <div class="box_bottom">
        <span class="box_btn blue"><input type="submit" value="확인"></span>
        <span class="box_btn"><input type="button" value="닫기" onclick="keyGen.close();"></span>
    </div>
</form>