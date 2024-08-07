<?php

/**
 * 배송업체설정 등록 및 수정 폼
 **/

define('__pop_title__', '해외 배송 설정');
define('__pop_width__', '800px');

// 국가 정보
include_once $engine_dir.'/_config/set.country.php';
asort($_nations_kr);

// 배송지 정보 읽기
$no = (isset($_GET['no']) == true) ? $_GET['no'] : 0;
if($no > 0) {
    $data = $pdo->assoc("select * from {$tbl['os_delivery_area']} where no=:no", array(
        ':no' => $_GET['no']
    ));
}

// 사용중인 국가코드 구하기
$res = $pdo->iterator("select no, country_code from {$tbl['os_delivery_country']} where delivery_com=:delivery_com and area_no=:area_no", array(
    ':delivery_com' => $_GET['delivery_com'],
    ':area_no' => $no
));

// 타 배송지에서 사용중인 국가 제거
$selected = array();
$res2 = $pdo->iterator("select country_code from {$tbl['os_delivery_country']} where delivery_com=:delivery_com and area_no!=:area_no", array(
    ':delivery_com' => $_GET['delivery_com'],
    ':area_no' => $no
));
foreach ($res2 as $nation) {
    $selected[] = $nation['country_code'];
}

function parseNationList(&$res) {
    global $selected, $_nations, $_nations_kr;

    $data = current($res);
    $key = key($res);
    next($res);
    if($data == false) return false;

    return array(
        'code' => $key,
        'name' => $_nations[$key],
        'name_kr' => $data,
        'disabled' => (in_array($key, $selected) == true) ? 'disabled' : ''
    );

}

?>
<form action="./index.php" onsubmit="return checkFrm(this);">
    <input type="hidden" name="body" value="config@oversea_delivery.exe">
    <input type="hidden" name="exec" value="area">
    <input type="hidden" name="no" value="<?=$data['no']?>">
    <input type="hidden" name="delivery_com" value="<?=$_GET['delivery_com']?>">

    <table class="tbl_row">
        <colgroup>
            <col style="width:20%">
            <col>
        </colgroup>
        <tbody>
            <tr>
                <th scope="row"><strong>배송지 별칭</strong></th>
                <td>
                    <input type="text" name="name" value="<?=$data['name']?>" class="input" size="80">
                </td>
            </tr>
            <tr>
                <th scope="row" rowspan="2"><strong>국가 등록</strong></th>
                <td>
                    <span class="searching_select">
                        <select class="sel_nation">
                            <option value="">::국가선택::</option>
                            <?php while($nation = parseNationList($_nations_kr)) {?>
                            <option value="<?=$nation['code']?>" <?=$nation['disabled']?>><?=$nation['name_kr']?>(<?=$nation['name']?>)</option>
                            <?}?>
                        </select>
                    </span>
                </td>
            </tr>
            <tr>
                <td>
                    <!-- KDH 여기 코딩 필요 -->
                    <ul class="nations">
                        <?php foreach ($res as $nation) {?>
                        <li class="nations_<?=$nation['country_code']?>" data-no="<?=$nation['no']?>">
                            <?=$_nations_kr[$nation['country_code']]?>(<?=$_nations[$nation['country_code']]?>)
                            <input type="hidden" name="nations[]" value="<?=$nation['country_code']?>">
                            <a href="#" onclick="removeNation('<?=$nation['country_code']?>'); return false;">X</a>
                        </li>
                        <?php }?>
                    </ul>
                </td>
            </tr>
        </tbody>
    </table>
    <div class="box_bottom">
        <span class="box_btn blue"><input type="submit" value="확인"></span>
        <span class="box_btn"><input type="button" value="닫기" onclick="overseaRegister.close();"></span>
    </div>
</form>
<script>
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

function removeNation(code) {
    var item = $('li.nations_'+code);
    if(item.data('no') != '') {
        $.post('./index.php', {
            'body':'config@oversea_delivery.exe',
            'exec':'removeNation',
            'no':item.data('no')
        });
    }
    item.remove();
}

$(function() {
    $('.sel_nation').on('change', function() {
        var sel = $(this).find(':selected');
        if(sel.val() != '') {
            if($('.nations_'+sel.val()).length == 0) {
                /* <!-- KDH 여기도 코딩 필요 --> */
                $('.nations').append(
                    '<li class="nations_'+sel.val()+'" data-no="">'+
                    sel.text()+
                    '<input type="hidden" name="nations[]" value="'+sel.val()+'">'+
                    '<a href="#" onclick="removeNation(\''+sel.val()+'\'); return false;">X</a></li>'
                );
            }
        }
    });
    $('.searching_select>select').select2({'language':'ko'});
});
</script>