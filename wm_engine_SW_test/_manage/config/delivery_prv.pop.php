<?php

/**
 * 배송업체설정 등록 및 수정 폼
 **/

define('__pop_title__', '배송업체 설정');
define('__pop_width__', '700px');

// 택배사 템플릿
$_dlv_name[] = '직접입력';
$_dlv_url[] = '';
$_dlv_name[] = 'CJ대한통운';
$_dlv_url[] = 'http://redirect.wisa.co.kr/delivery.php?provider=korex&invoice={송장번호}';
$_dlv_name[] = '로젠';
$_dlv_url[] = 'http://redirect.wisa.co.kr/delivery.php?provider=logen&invoice={송장번호}';
//$_dlv_name[] = 'SC로지스';
//$_dlv_url[] = 'http://redirect.wisa.co.kr/delivery.php?provider=sclogis&invoice={송장번호}';
$_dlv_name[] = '우체국택배';
$_dlv_url[] = 'http://redirect.wisa.co.kr/delivery.php?provider=epost&invoice={송장번호}';
//$_dlv_name[] = 'KG로지스';
//$_dlv_url[] = 'http://redirect.wisa.co.kr/delivery.php?provider=yellowcap&invoice={송장번호}';
//$_dlv_name[] = 'KGB택배';
//$_dlv_url[] = 'http://redirect.wisa.co.kr/delivery.php?provider=KGB&invoice={송장번호}';
$_dlv_name[] = '한진택배';
$_dlv_url[] = 'http://redirect.wisa.co.kr/delivery.php?provider=hanjin&invoice={송장번호}';
$_dlv_name[] = '롯데택배';
$_dlv_url[] = 'http://redirect.wisa.co.kr/delivery.php?provider=lotte&invoice={송장번호}';
$_dlv_name[] = '현대택배';
$_dlv_url[] = 'http://redirect.wisa.co.kr/delivery.php?provider=hydex&invoice={송장번호}';
//$_dlv_name[] = '드림택배';
//$_dlv_url[] = 'http://redirect.wisa.co.kr/delivery.php?provider=kglogis&invoice={송장번호}';
//$_dlv_name[] = '동부익스프레스';
//$_dlv_url[] = 'http://redirect.wisa.co.kr/delivery.php?provider=dongbuexpress&invoice={송장번호}';
$_dlv_name[] = '합동택배';
$_dlv_url[] = 'http://redirect.wisa.co.kr/delivery.php?provider=hdexp&invoice={송장번호}';
$_dlv_name[] = '대신택배';
$_dlv_url[] = 'http://redirect.wisa.co.kr/delivery.php?provider=daesin&invoice={송장번호}';
$_dlv_name[] = '경동택배';
$_dlv_url[] = 'http://redirect.wisa.co.kr/delivery.php?provider=kdexp&invoice={송장번호}';
$_dlv_name[] = '롯데글로벌';
$_dlv_url[] = 'http://redirect.wisa.co.kr/delivery.php?provider=lotteGlobalLogis&invoice={송장번호}';
$_dlv_name[] = '건영택배';
$_dlv_url[] = 'http://redirect.wisa.co.kr/delivery.php?provider=kunyoung&invoice={송장번호}';

// 택배사 데이터 읽기
if (isset($_GET['no']) == true) {
    $data = $pdo->assoc("select * from {$tbl['delivery_url']} where no=:no", array(
        ':no' => $_GET['no']
    ));
    $data['provider_selected'] = (in_array($data['name'], $_dlv_name) == true) ? $data['name'] : '직접입력';
}

?>
<form action="./index.php" onsubmit="return checkFrm(this);">
    <input type="hidden" name="body" value="config@delivery.exe">
    <input type="hidden" name="exec" value="register">
    <input type="hidden" name="no" value="<?=$data['no']?>">

    <table class="tbl_row">
        <caption class="hidden">배송업체 설정</caption>
        <colgroup>
            <col style="width:20%">
            <col>
        </colgroup>
        <tbody>
            <tr>
                <th scope="row">배송업체</th>
                <td>
                    <?=selectArray($_overseas_delivery_arr, 'overseas_delivery', false, null, $data['overseas_delivery'])?>
                    <?=selectArray($_dlv_name, 'provider_selected', true, ':: 배송업체 선택 ::', $data['provider_selected'])?>
                    <input type="text" name="name" class="input" style="display:none" value="<?=$data['bank']?>">
                </td>
            </tr>
            <tr>
                <th scope="row">배송추적 URL</th>
                <td><input type="text" name="url" class="input block" value="<?=$data['url']?>"></td>
            </tr>
        </tbody>
    </table>
    <div class="box_bottom">
        <span class="box_btn blue"><input type="submit" value="확인"></span>
        <span class="box_btn"><input type="button" value="닫기" onclick="providerRegister.close();"></span>
    </div>
</form>
<script type="text/javascript">
var delivery_url = {};
<?php foreach ($_dlv_url as $key => $val) {?>
delivery_url[<?=$key?>] = "<?=$val?>";
<?php }?>

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

(selectProvider = function(sel) {
    if (typeof sel == 'object') sel = '';

    var provider = $('select[name=provider_selected]').val();
    if (provider == '직접입력') $('input[name=name]').val(sel).show();
    else $('input[name=name]').val(provider).hide();

    var url = delivery_url[parseInt($('select[name=provider_selected]').prop('selectedIndex'))-1];
    if (typeof url == 'undefined') {
        url = '';
    }
    $('input[name=url]').val(url);

})('<?=$data['name']?>');

$('select[name=provider_selected]').on('change', selectProvider);
</script>