<?php

/**
 * 무통장 계좌 설정 등록 및 수정 폼
 **/

define('__pop_title__', '무통장 계좌 등록');
define('__pop_width__', '650px');

// 은행명 템플릿
if ($_GET['type'] == 'int') {
    $_bank = array(
        'KOOK MIN BANK / CZNBKRSE',
        'INDUSTRIAL BANK OF KOREA / IBKOKRSE',
        'NATIONAL AGRICULTURAL COOPERATIVE FEDERATION / NACFKRSEXXX',
        'SHIN HAN BANK / SHBKKRSE',
        'KOREA EXCHANGE BANK / KOEXKRSEXXX',
        'WOORI BANK / HVBKKRSEXXX',
        'HANA BANK / HNBNKRSE',
        'CITIBANK KOREA / CITIKRSX',
        'KOREA POST OFFICE / SHBKKRSEKPO',
        'STANDARD CHARTERED FIRST BANK KOREA / SCBLKRSE',
    );
} else {
    $_bank = array('직접입력');
    foreach ($bank_codes as $val) {
        $_bank[] = $val;
    }
}

// 은행 데이터 읽기
if (isset($_GET['no']) == true) {
    $data = $pdo->assoc("select * from {$tbl['bank_account']} where no=:no", array(
        ':no' => $_GET['no']
    ));
    $data['bank_selected'] = (in_array($data['bank'], $_bank) == true) ? $data['bank'] : '직접입력';
}

?>
<form action="./index.php" onsubmit="return checkFrm(this);">
    <input type="hidden" name="body" value="config@account_bank.exe">
    <input type="hidden" name="no" value="<?=$data['no']?>">
    <input type="hidden" name="type" value="<?=$_GET['type']?>">
    <table class="tbl_row">
        <caption class="hidden">무통장 계좌 등록</caption>
        <colgroup>
            <col style="width:20%">
            <col>
        </colgroup>
        <tbody>
            <tr>
                <th scope="row">은행명</th>
                <td>
                    <?=selectArray($_bank, 'bank_selected', true, ':: 은행선택 ::', $data['bank_selected'])?>
                    <input type="text" name="bank" class="input" style="display:none" value="<?=$data['bank']?>">
                </td>
            </tr>
            <tr>
                <th scope="row">계좌번호</th>
                <td><input type="text" name="account" class="input block" value="<?=$data['account']?>"></td>
            </tr>
            <tr>
                <th scope="row">예금주</th>
                <td><input type="text" name="owner" class="input block" value="<?=$data['owner']?>"></td>
            </tr>
        </tbody>
    </table>
    <div class="box_bottom">
        <span class="box_btn blue"><input type="submit" value="확인"></span>
        <span class="box_btn"><input type="button" value="닫기" onclick="bankRegister.close();"></span>
    </div>
</form>
<script type="text/javascript">
function checkFrm(f) {
    $.post('./index.php', $(f).serialize(), function(r) {
        if (r == 'success') {
            printLoading();
            location.reload();
        } else {
            window.alert(r);
        }
    });
    return false;
}

(selectBank = function(sel) {
    if (typeof sel == 'object') sel = '';

    var bank = $('select[name=bank_selected]').val();
    if (bank == '직접입력') $('input[name=bank]').val(sel).show();
    else $('input[name=bank]').val(bank).hide();
})('<?=$data['bank']?>');

$('select[name=bank_selected]').on('change', selectBank);
</script>