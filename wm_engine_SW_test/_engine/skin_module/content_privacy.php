<?PHP

	/* +----------------------------------------------------------------------------------------------+
	' |  개인정보보호정책
	' +----------------------------------------------------------------------------------------------+*/

    use Wing\API\Booster\Config;
if (PHP_MAJOR_VERSION >= 7) {
    //PHP 7 이상 지원
    $_replace_code[$_file_name][hidden_start] = ($hidden == "Y") ? "<!-- " : "";
    $_replace_code[$_file_name][hidden_end] = ($hidden == "Y") ? "-->" : "";
    $_replace_code[$_file_name]['privacy_join_part'] = ($mode == 1) ? getModuleContent('privacy_join_part') : getModuleContent('privacy_use_part');

    if(isTable($tbl['privacy_policy'])) {
        $privacy_api = new Config();
        $_replace_code[$_file_name]['company_privacy_list'] = '<div style="text-align:right; margin-bottom:30px;"><select onchange="changePrivacy(this.value);">';
        $_replace_code[$_file_name]['company_privacy_list'] .= '<option value="">이전개인정보처리방침</option>';

        $res = $privacy_api->privacyListGet(array('state' => 'show', 'retJson' => false, 'LimitQuery' => ''));
        if ($res['status'] === 'success') {
            foreach ($res['data'] as $ldata) {
                $selected = '';
                if ($_POST['contents']) {
                    //미리보기 호출시
                    $_replace_code[$_file_name]['company_privacy_cont'] = $_POST['contents'];
                } elseif ($_GET['privacy_no'] && ($ldata['no'] === $_GET['privacy_no'])) {
                    //특정 게시물 선택시 내용
                    $_replace_code[$_file_name]['company_privacy_cont'] = $ldata['contents'];
                    $selected = 'selected';
                } elseif (!$_GET['privacy_no'] && $ldata['default_yn'] === 'Y') {
                    //현재 게시중인 내용
                    $_replace_code[$_file_name]['company_privacy_cont'] = $ldata['contents'];
                }
                $_replace_code[$_file_name]['company_privacy_list'] .= sprintf(
                    '<option value="%s" %s>%s 시행</option>',
                    $ldata['no'],
                    $selected,
                    $ldata['effective_date']
                );
            }
        } else {
            $_replace_code[$_file_name]['company_privacy_cont'] = '<div>작성된 처리방침이 없습니다</div>';
        }
        $_replace_code[$_file_name]['company_privacy_list'] .= '</select></div>';
    }
} else {
    $_replace_code[$_file_name]['company_privacy_list'] = $_replace_code[$_file_name]['company_privacy_cont'] = '';
}
