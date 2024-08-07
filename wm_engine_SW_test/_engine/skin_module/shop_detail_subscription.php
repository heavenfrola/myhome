<?PHP

/**
 * 정기배송 상세페이지 v2
 **/

require 'shop_subscription.php';

// 기본폼에 내용 추가
$_replace_code[$_file_name]['detail_sbscr_form_start'] .=
    '<input type="hidden" id="sbscr_date_list" name="sbscr_date_list" value="'.$caldata['date_list'].'">'.
    '<input type="hidden" name="sbscr" value="Y">';

// 정기배송 주기 선택
$_tmp = '';
$_line = getModuleContent('detail_sbscr_period_list');
$detail_sbscr_period = $sbscr_data['sbscr_dlv_period'];
foreach ($detail_sbscr_period as $key => $val) {
    $_checked = ($key == 0) ? 'checked' : '';
    $_tmp .= lineValues('detail_sbscr_period_list', $_line, array(
        'name' => $_sbscr_periods[$val],
        'radio' => "<input type='radio' name='sbscr_period' value='$val' $_checked>"
    ));
}
$_replace_code[$_file_name]['detail_sbscr_period_list'] = listContentSetting($_tmp, $_line);
unset($_line, $_tmp);