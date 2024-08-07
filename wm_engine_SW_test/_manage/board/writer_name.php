<?php

/**
 * 작성자 표기 설정
 **/

// 기본 값
$scfg->def('writer_name', 'name');
$scfg->def('protect_name', 'N');
$scfg->def('protect_id', 'N');
$scfg->def('protect_name_strlen', '');
$scfg->def('protect_name_suffix', '');
$scfg->def('protect_id_strlen', '');
$scfg->def('protect_id_suffix', '');

// 상품 후기 및 상품 문의 설정
foreach (array('review', 'qna') as $db) {
    $writer_name['bbs_'.$db] = $scfg->get('product_'.$db.'_name');
    $protect_name['bbs_'.$db] = $scfg->get('product_'.$db.'_protect_name');
    $protect_name_strlen['bbs_'.$db] = $scfg->get('product_'.$db.'_protect_name_strlen');
    $protect_name_suffix['bbs_'.$db] = $scfg->get('product_'.$db.'_protect_name_suffix');
    $protect_id['bbs_'.$db] = $scfg->get('product_'.$db.'_protect_id');
    $protect_id_strlen['bbs_'.$db] = $scfg->get('product_'.$db.'_protect_id_strlen');
    $protect_id_suffix['bbs_'.$db] = $scfg->get('product_'.$db.'_protect_id_suffix');

    if (empty($protect_name['bbs_'.$db]) == true) $protect_name['bbs_'.$db] = 'N';
    if (empty($protect_id['bbs_'.$db]) == true) $protect_id['bbs_'.$db] = 'N';
}

// 게시판별 설정
$board_list = $config = array();
$boards = $pdo->iterator("select * from mari_config order by no asc");
foreach ($boards as $val) {
    $board_list[$val['db']] = stripslashes($val['title']);
    $config[$val['db']] = $val;
}
foreach ($board_list as $key => $val) {
    $_config = $config[$key];
    if (empty($_config['writer_name']) == true) $_config['writer_name'] = $cfg['writer_name'];
    if (empty($_config['protect_name']) == true) $_config['protect_name'] = 'N';
    if (empty($_config['protect_id']) == true) $_config['protect_id'] = 'N';
    if (empty($_config['protect_name_strlen']) == true) $_config['protect_name_strlen'] = $cfg['protect_name_strlen'];
    if (empty($_config['protect_name_suffix']) == true) $_config['protect_name_suffix'] = $cfg['protect_name_suffix'];
    if (empty($_config['protect_id_strlen']) == true) $_config['protect_id_strlen'] = $cfg['protect_id_strlen'];
    if (empty($_config['protect_id_suffix']) == true) $_config['protect_id_suffix'] = $cfg['protect_id_suffix'];

    $writer_name[$key] = $_config['writer_name'];
    $protect_name[$key] = $_config['protect_name'];
    $protect_name_strlen[$key] = $_config['protect_name_strlen'];
    $protect_name_suffix[$key] = $_config['protect_name_suffix'];
    $protect_id[$key] = $_config['protect_id'];
    $protect_id_strlen[$key] = $_config['protect_id_strlen'];
    $protect_id_suffix[$key] = $_config['protect_id_suffix'];
}

$board_list = array_merge(array(
    'bbs_qna' => '상품문의',
    'bbs_review' => '상품후기',
), $board_list);

// 개별 게시판
$writer_name_bbs = explode('@', $scfg->get('writer_name_bbs'));
$board_list_spt = array();
foreach ($board_list as $key => $val) {
    if (in_array($key, $writer_name_bbs) == true) continue;
    $board_list_spt[$key] = $val;
}

?>
<style type="text/css">
.board_lists li {
    display: inline-block;
    width: 200px;
    overflow: hidden;
    white-space: nowrap;
}
</style>
<div class="box_title first">
    <h2 class="title">작성자 표기 설정</h2>
</div>
<div class="box_tab" style="margin:0">
    <ul>
        <li><a href="#global" onclick="chgTab(0)" class="active">통합설정</a></li>
        <li><a href="#local" onclick="chgTab(1)">게시판별 설정</a></li>
    </ul>
</div>
<form method="POST" class="pannel pannel0" target="hidden<?=$now?>" onsubmit="printLoading();">
    <input type="hidden" name="body" value="board@writer_name.exe">
    <input type="hidden" name="exec" value="global">
    <table class="tbl_row">
        <colgroup>
            <col style="width:15%">
            <col>
        </colgroup>
        <tbody>
            <tr>
                <th scope="row">
                    게시판 선택
					<a href="#" class="tooltip_trigger" data-child="tooltip_select_board">설명</a>
					<div class="info_tooltip tooltip_select_board">
						<h3>게시판 선택</h3>
                        <ul class="list_info">
                            <li>선택 해제된 게시판은 게시판별 설정에서 따로 설정 가능합니다.</li>
                        </ul>
						<a href="#" class="tooltip_closer">닫기</a>
                    </div>
                </th>
                <td colspan="2">
                    <ul class="board_lists">
                        <li><label><input type="checkbox" class="check_all"> 전체</label></li>
                        <?php foreach ($board_list as $key => $val) { ?>
                        <li>
                            <label>
                                <input
                                    type="checkbox"
                                    name="writer_name_bbs[]"
                                    value="<?=$key?>"
                                    class="check_sub"
                                    <?=checked(in_array($key, $writer_name_bbs), true)?>
                                > <?=$val?></label>
                        </li>
                        <?php } ?>
                    </ul>
                </td>
            </tr>
            <tr>
                <th scope="row">작성자 표시</th>
                <td colspan="2">
                    <ul>
                        <li>
                            <label><input type="radio" name="writer_name" value="name" <?=checked($cfg['writer_name'], 'name')?>> 이름</label>
                        </li>
                        <li>
                            <label><input type="radio" name="writer_name" value="member_id" <?=checked($cfg['writer_name'], 'member_id')?>> 아이디</label>
                            <span class="list_info2">비회원은 '이름'이 출력됩니다.</span>
                        </li>
                        <li>
                            <label><input type="radio" name="writer_name" value="name_id" <?=checked($cfg['writer_name'], 'name_id')?>> 이름(아이디)</label>
                            <span class="list_info2">회원일 경우에만 표기됩니다.</span>
                        </li>
                        <li>
                            <label><input type="radio" name="writer_name" value="nickname" <?=checked($cfg['writer_name'], 'nickname')?>> 닉네임</label>
                            <span class="list_info2">미 입력 시 '이름'이 출력됩니다.</span>
                        </li>
                        <li>
                            <label><input type="radio" name="writer_name" value="icon" <?=checked($cfg['writer_name'], 'icon')?>> 아이콘</label>
                            <span class="list_info2">등급 아이콘이 등록되어있지 않으면 '이름'이 출력됩니다.</span>
                        </li>
                    </ul>
                </td>
            </tr>
            <tr>
                <th scope="row" rowspan="2">작성자 표시보호</th>
                <td>
                    <h3>이름 보호</h3>
                    <ul>
                        <li><label><input type="radio" name="protect_name" value="Y" <?=checked($cfg['protect_name'], 'Y')?>> 사용</label></li>
                        <li><label><input type="radio" name="protect_name" value="N" <?=checked($cfg['protect_name'], 'N')?>> 사용안함</label></li>
                    </ul>
                </td>
                <td class="lb">
                    <ul>
                        <li>
                            <strong class="p_color3">노출문자 수</strong>
                            이름의 첫 <input type="text" name="protect_name_strlen" value="<?=$cfg['protect_name_strlen']?>" class="input" size="5"> 자리 노출
                        </li>
                        <li>
                            <strong class="p_color3">보호문자 </strong>
                            <input type="text" name="protect_name_suffix" value="<?=$cfg['protect_name_suffix']?>" class="input" size="5">
                            <span class="list_info2">노출문자 뒤에 입력한 보호문자가 출력됩니다.</span>
                        </li>
                    </ul>
                </td>
            </tr>
            <tr>
                <td>
                    <h3>아이디 보호</h3>
                    <ul>
                        <li><label><input type="radio" name="protect_id" value="Y" <?=checked($cfg['protect_id'], 'Y')?>> 사용</label></li>
                        <li><label><input type="radio" name="protect_id" value="N" <?=checked($cfg['protect_id'], 'N')?>> 사용안함</label></li>
                    </ul>
                </td>
                <td class="lb">
                    <ul>
                        <li>
                            <strong class="p_color3">노출문자 수</strong>
                            아이디의 첫 <input type="text" name="protect_id_strlen" value="<?=$cfg['protect_id_strlen']?>" class="input" size="5"> 자리 노출
                        </li>
                        <li>
                            <strong class="p_color3">보호문자 </strong>
                            <input type="text" name="protect_id_suffix" value="<?=$cfg['protect_id_suffix']?>" class="input" size="5">
                            <span class="list_info2">노출문자 뒤에 입력한 보호문자가 출력됩니다.</span>
                        </li>
                    </ul>
                </td>
            </tr>
        </tbody>
    </table>
    <div class="box_bottom">
        <span class="box_btn blue"><input type="submit" value="확인"></span>
    </div>
</form>

<form method="POST" class="pannel pannel1" target="hidden<?=$now?>" onsubmit="printLoading();" style="display:none">
    <input type="hidden" name="body" value="board@writer_name.exe">
    <input type="hidden" name="exec" value="local">
    <table class="tbl_row">
        <colgroup>
            <col style="width:15%">
            <col>
        </colgroup>
        <tbody>
            <?php if (count($board_list_spt) == 0) { ?>
            <tr>
                <td colspan="3">
                    <div class="nodata">
                        개별로 설정할 게시판이 없습니다.<br>
                        통합설정 탭에서 설정해주세요.
                    </div>
                </td>
            </tr>
            <?php } ?>
            <?php foreach ($board_list_spt as $key => $val) { ?>
            <tr>
                <td colspan="3"><strong><?=$val?></strong></td>
            </tr>
            <tr>
                <th scope="row">작성자 표시</th>
                <td colspan="2">
                    <ul>
                        <li><label><input type="radio" name="writer_name[<?=$key?>]" value="name" <?=checked($writer_name[$key], 'name')?>> 이름</label></li>
                        <li>
                            <label><input type="radio" name="writer_name[<?=$key?>]" value="member_id" <?=checked($writer_name[$key], 'member_id')?>> 아이디</label>
                            <span class="list_info2">비회원은 '이름'이 출력됩니다.</span>
                        </li>
                        <li>
                            <label><input type="radio" name="writer_name[<?=$key?>]" value="name_id" <?=checked($writer_name[$key], 'name_id')?>> 이름(아이디)</label>
                            <span class="list_info2">회원일 경우에만 표기됩니다.</span>
                        </li>
                        <li>
                            <label><input type="radio" name="writer_name[<?=$key?>]" value="nickname" <?=checked($writer_name[$key], 'nickname')?>> 닉네임</label>
                            <span class="list_info2">미 입력 시 '이름'이 출력됩니다.</span>
                        </li>
                        <li>
                            <label><input type="radio" name="writer_name[<?=$key?>]" value="icon" <?=checked($writer_name[$key], 'icon')?>> 아이콘</label>
                            <span class="list_info2">등급 아이콘이 등록되어있지 않으면 '이름'이 출력됩니다.</span>
                        </li>
                    </ul>
                </td>
            </tr>
            <tr>
                <th scope="row" rowspan="2">작성자 표시보호</th>
                <td>
                    <h3>이름 보호</h3>
                    <ul>
                        <li><label><input type="radio" name="protect_name[<?=$key?>]" value="Y" <?=checked($protect_name[$key], 'Y')?>> 사용</label></li>
                        <li><label><input type="radio" name="protect_name[<?=$key?>]" value="N" <?=checked($protect_name[$key], 'N')?>> 사용안함</label></li>
                    </ul>
                </td>
                <td class="lb">
                    <ul>
                        <li>
                            <strong class="p_color3">노출문자 수</strong>
                            이름의 첫 <input type="text" name="protect_name_strlen[<?=$key?>]" value="<?=$protect_name_strlen[$key]?>" class="input" size="5"> 자리 노출
                        </li>
                        <li>
                            <strong class="p_color3">보호문자 </strong>
                            <input type="text" name="protect_name_suffix[<?=$key?>]" value="<?=$protect_name_suffix[$key]?>" class="input" size="5">
                            <span class="list_info2">노출문자 뒤에 입력한 보호문자가 출력됩니다.</span>
                        </li>
                    </ul>
                </td>
            </tr>
            <tr>
                <td>
                    <h3>아이디 보호</h3>
                    <ul>
                        <li><label><input type="radio" name="protect_id[<?=$key?>]" value="Y" <?=checked($protect_id[$key], 'Y')?>> 사용</label></li>
                        <li><label><input type="radio" name="protect_id[<?=$key?>]" value="N" <?=checked($protect_id[$key], 'N')?>> 사용안함</label></li>
                    </ul>
                </td>
                <td class="lb">
                    <ul>
                        <li>
                            <strong class="p_color3">노출문자 수</strong>
                            아이디의 첫 <input type="text" name="protect_id_strlen[<?=$key?>]" value="<?=$protect_id_strlen[$key]?>" class="input" size="5"> 자리 노출
                        </li>
                        <li>
                            <strong class="p_color3">보호문자 </strong>
                            <input type="text" name="protect_id_suffix[<?=$key?>]" value="<?=$protect_id_suffix[$key]?>" class="input" size="5">
                            <span class="list_info2">노출문자 뒤에 입력한 보호문자가 출력됩니다.</span>
                        </li>
                    </ul>
                </td>
            </tr>
            <?php } ?>
        </tbody>
    </table>
    <div class="box_bottom">
        <span class="box_btn blue"><input type="submit" value="확인"></span>
    </div>
</form>
<script type="text/javascript">
function chgTab(idx) {
    $('.box_tab>ul>li>a').removeClass('active').eq(idx).addClass('active');
    $('.pannel').hide();
    $('.pannel'+idx).show();
}

$(function() {
    if (document.URL.replace(/.*#/, '') == 'local') {
        chgTab(1);
    }

    chainCheckbox($('.check_all'), $('.check_sub'));
});
</script>