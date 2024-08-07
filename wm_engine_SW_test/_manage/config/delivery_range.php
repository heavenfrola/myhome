<?php

/**
 * 배송 불가 지역 설정
 **/

$scfg->def('dlv_possible_type', 'N');
if ($admin['partner_no'] > 0) {
    $_config = $pdo->row("select value from {$tbl['partner_config']} where name=? and partner_no=?", array(
        'dlv_possible_type', $admin['partner_no']
    ));
    if (!$_config) $_config = 'N';
    $cfg['dlv_possible_type'] = $_config;
}
$_pt = array('D' => 'off', 'A' => 'off', 'N' => 'off');
$_pt[$cfg['dlv_possible_type']] = 'on';

?>
<div class="box_title">
    <h2 class="title">
        주소별 배송 제한 설정
		<div class="btns">
			<span class="box_btn_s icon copy2"><input type="button" value="엑셀업로드"><input type="file" class="file_input_hidden" onchange="uploadDeliveryRange(this)"></span>
			<span class="box_btn_s icon excel"><input type="button" value="엑셀다운" onclick="location.href='?body=config@delivery_range_excel.exe&type='+current_possible_type"></span>
		</div>
    </h2>
</div>

<form method="post" action="./index.php" target="hidden<?=$now?>" onsubmit="return changeDlvPossibleType(this)">
    <input type="hidden" name="body" value="config@config.exe">
    <input type="hidden" name="dlv_possible_type" value="<?=$cfg['dlv_possible_type']?>">

    <div id="dlv_possible_type" class="box_tab first">
        <ul>
            <li>
                <a href="#" data-dlv_possible_type="D" class="active">
                    배송 불가 지역 <span class="toggle <?=$_pt['D']?>"><?=strtoupper($_pt['D'])?></span>
                </a>
            </li>
            <li>
                <a href="#" data-dlv_possible_type="A">
                    배송 가능 지역 <span class="toggle <?=$_pt['A']?>"><?=strtoupper($_pt['A'])?></span>
                </a>
            </li>
            <li>
                <a href="#" data-dlv_possible_type="N" class="active">
                    미사용 <span class="toggle <?=$_pt['N']?>"><?=strtoupper($_pt['N'])?></span>
                </a>
            </li>
        </ul>
    </div>

    <div class="box_middle2 left">
        <ul class="list_info">
            <li>행정구역변경으로 지명이 변경되었을 경우 반드시 함께 변경을 하셔야 합니다.</li>
            <li>엑셀 업로드 이용 시, 정확한 행정구역명만 기재해주셔야 합니다. ex) 강원 전체인 경우 '시/도' 필드에 '강원' 만 입력</li>
            <li>엑셀 업로드 이용 시, 다운로드 받아 수정한 엑셀을 .csv 형식으로 저장하여 업로드해 주시기 바랍니다.</li>
            <li>엑셀 업로드 이용 시, 신규 지역 등록 시 1열 "번호"는 공란으로 두셔야 하며, 수정 시 1열 "번호"는 그대로 유지하셔야 합니다.</li>
            <li>설정한 내용은 네이버페이 주문형 등 외부 주문에서는 적용되지 않습니다.</li>
        </ul>
    </div>
    <table class="tbl_row dlv_possible_el dlv_possible_el_A" style="border-top: 0">
        <colgroup>
            <col style="width:220px">
            <col>
        </colgroup>
        <tbody>
            <th>
                배송 제한 안내문구
                <a href="#" class="tooltip_trigger" data-child="tooltip_possible_d_msg"></a>
                <div class="info_tooltip tooltip_possible_d_msg" style="white-space:nowrap">
                    배송 불가 지역 선택 시 안내 문구에 입력한 내용이 확인됩니다.
                </div>
            </th>
            <td class="left">
                <input type="text" name="dlv_possible_d_msg" class="input input_full" value="<?=inputText($scfg->get('dlv_possible_d_msg'))?>">
            </td>
        </tbody>
    </table>
	<div class="box_bottom">
		<span class="box_btn blue"><input type="submit" value="확인"></span>
	</div>
</form>

<div id="delivery_range" class="box_middle2 left" style="position: relative">
    <div id="delivery_range_filter"></div>
    <form id="delivery_range_list">
        <?php require 'delivery_range.inc.php'; ?>
    </form>

    <form method="post" action="./index.php" target="hidden<?=$now?>" onsubmit="return setDeliveryRange(this);">
        <input type="hidden" name="body" value="config@config.exe">
        <table class="tbl_col" style="margin:10px 0;">
            <colgroup>
                <col style="width:220px">
                <col>
                <col class="dlv_possible_el dlv_possible_el_D">
                <col style="width:150px">
            </colgroup>
            <thead>
                <tr>
                    <th scope="col">배송지 별칭</th>
                    <th scope="col">지역</th>
                    <th scope="col" class="dlv_possible_el dlv_possible_el_D">배송 제한 안내문구</th>
                    <th></th>
                </tr>
            </thead>
            <tbody>
                <tr>
                    <td><input type="text" id="range_name" name="range_name" class="input" size="25" value=""></td>
                    <td id="selectedRange">아래에서 지역을 선택해 주세요.</td>
                    <td class="dlv_possible_el dlv_possible_el_D">
                        <input type="text" id="reason" name="reason" class="input" style="width: 95%" value="">
                    </td>
                    <td>
                        <span class="box_btn_s blue dlv_btn3"><input type="submit" value="추가"></span>
                        <span class="box_btn_s gray dlv_btn4"><input type="button" value="취소" onclick="modifyDeliveryRange()" style="display:none;"></span>
                    </td>
                </tr>
            </tbody>
        </table>
    </form>

    <div class="addrbox_frame dlv_range">
        <!-- 시/도 -->
        <ul class="addrbox" data-label="sido">
            <?=getAddr('sido')?>
        </ul>
        <!-- 구/군 -->
        <ul class="addrbox" data-label="gugun">
        </ul>
        <!-- 동/읍/면 -->
        <ul class="addrbox" data-label="dong">
        </ul>
        <!-- 리 -->
        <ul class="addrbox" data-label="ri">
        </ul>
    </div>
</div>

<script>
// 배송 불가/가능 타입 설정
const dlv_possible_select = $('#dlv_possible_type > ul > li > a');
let   current_possible_type = '';
function setPossibleSelect(value) {
    const current = dlv_possible_select.filter('[data-dlv_possible_type='+value+']');

    current_possible_type = value;
    if (current.hasClass('active') == false) {
        reloadDeliveryRange();
    }

    dlv_possible_select.removeClass('active');
    current.addClass('active');

    $('.dlv_possible_el').hide();
    $('.dlv_possible_el_'+value).show();

    $('input[name=dlv_possible_type]').val(value);

    if (value == 'N') {
        $('#delivery_range_filter').css({
            'position': 'absolute',
            'z-index': 1,
            'top': 0,
            'left': 0,
            'width': '100%',
            'height': $('#delivery_range').prop('scrollHeight'),
            'opacity': 0.2,
            'background-color': '#000'
        }).fadeIn('fast');
    }
    else $('#delivery_range_filter').fadeOut('fast');
}

dlv_possible_select.on('click', function(e) {
    e.preventDefault();

    let value = $(e.currentTarget).data('dlv_possible_type');
    setPossibleSelect(value);
});

function changeDlvPossibleType(f)
{
    if (current_possible_type == 'A') {
        if (!checkBlank(f.dlv_possible_d_msg, '배송 제한 안내문구를 입력해주세요.')) return false;
    }
    return true;
}
$(function() {
    setPossibleSelect('<?=$cfg['dlv_possible_type']?>');
});

// 주소 tree 검색
function deliveryRange(o)
{
    let name = o.data('name');
    let type = o.data('type');
    let current = o.parents('.addrbox');
    let next = current.next();

    // 선택 표시
    current.find('.selected').removeClass('selected');
    if (next) {
        o.addClass('selected');
    }

    if (type == 'checkbox') { // 세부 체크박스 선택 시 '전체' 체크박스 해제
        current.find('li:first-child :checkbox').prop('checked', false);
        if (current.find(':checked').length == 0) {
            current.find(':checkbox').eq(0).prop('checked', true);
        }
        // 리 선택 시 동 중복 선택 해제
        current.prev().find('li:not(.selected) :checkbox').prop('checked', false);
    } else { // '전체' 체크박스 선택 시 세부 체크박스 해제
        current.find('li:gt(0) :checkbox').prop('checked', false);
    }

    // 현재 선택된 주소 라벨 출력
    getDeliveryRangeLabel();

    // 하위 라벨 삭제
    let n = next;
    while(n.length) {
        n.html('');
        n = n.next();
    }

    if (!next) return; // 마지막 선택 박스
    if (name == '') return; // 전체 선택일 경우 다음 스텝 없음

    // 검색 parameter
    let param  = {'body': 'config@delivery_range.exe', 'exec': 'getAddr', 'next_child': next.data('label')};
    let last_selected = null;
    $('.dlv_range .addrbox').each(function() {
        label = $(this).data('label');
        $(this).find('li.selected').each(function() {
            param[label] = $(this).data('name');
        });
    });

    $.post('./index.php', param, function(r) {
        next.html(r).find('li').on('click', function(e) {
            deliveryRange($(this));
        });
    });
};

$('.dlv_range .addrbox li').on('click', function(e) {
    deliveryRange($(this));
});

// 선택한 배송지 라벨명 출력
function getDeliveryRangeLabel() {
    let print_name = ''; // 선택된 지역명
    let type = '';

    $('.dlv_range .addrbox').each(function() {
        var tmp = $(this).find('li.selected, :checked');
        var name = (tmp.prop('tagName') == 'LI') ? tmp.data('name') : tmp.val();
        if (tmp.length == 0) return false;
        if (!name) return false;
        current = $(this);

        if (print_name) print_name += ' ';
        print_name += name;
    });

    var checked = current.find(':checked').length;
    if (checked > 1) {
        print_name += ' 외 '+(checked-1);
    } else {
        print_name += ' 전체';
    }

    $('#selectedRange').html(print_name);
}

// 배송지 저장
function setDeliveryRange(f)
{
    if (typeof window.range_no == 'undefined') {
        window.range_no = 0;
    }

    let param  = {
        'body': 'config@delivery_range.exe',
        'exec': 'setRange',
        'no': window.range_no,
        'type': current_possible_type,
        'range_name': f.range_name.value,
        'sido': '',
        'gugun': '',
        'dong': '',
        'reason': f.reason.value,
        'ri': ''
    };
    $('.dlv_range .addrbox').each(function() {
        var tmp = '';
        $(this).find('li.selected, :checked').each(function() {
            if (this.tagName == 'LI' && $(this).data('type') == 'checkbox') return;

            if (tmp) tmp += ',';
            tmp += (this.tagName == 'LI') ? $(this).data('name') : this.value;
        });
        if (tmp) {
            param[$(this).data('label')] = tmp;
        }
    });

    if (!f.range_name.value) {
        window.alert('배송지 별칭을 입력해주세요.');
        return false;
    }
    if (!param['sido']) {
        window.alert('지역을 선택해주세요.');
        return false;
    }
    if (current_possible_type == 'D' && !param['reason']) {
        window.alert('배송불가/가능 사유를 입력해주세요.');
        return false;
    }

    printLoading();
    $.post('./index.php', param, function(r) {
        $('#delivery_range_list').html(r);
        f.reset();
        window.range_no = 0;
        removeLoading();

        $('.dlv_range .selected').removeClass('selected');
        $('.dlv_range .addrbox:gt(0)').html('');
        $('.dlv_btn3>input').val('추가');
        $('.dlv_btn4>input').hide();
    });

    return false;
}

// 배송 불가 정책 삭제
function removeDeliveryRange(no)
{
    if (typeof no == 'object') {
        let tmp = [];
        $(no).find(':checked.rangeone').each(function() {
            tmp.push(this.value);
        });
        no = tmp;
        if (no.length == 0) {
            window.alert('삭제할 정책을 선택해주세요.');
            return false;
        }
    }
    if (window.confirm('선택 된 정책을 삭제하시겠습니까?') == false) {
        return false;
    }

    printLoading();
    $.post('./index.php', {'body': 'config@delivery_range.exe', 'exec': 'remove', 'no': no}, function(r) {
        $('#delivery_range_list').html(r);
        window.range_no = 0;
        removeLoading();
    });
}

// 배송 불가 정책 수정
function modifyDeliveryRange(no)
{
    if (!no) {
        window.range_no = 0;

        $('#range_name').val('');
        $('#reason').val('');

        // 주소 패널 초기화
        $('.dlv_range .addrbox').eq(1).html('');
        $('.dlv_range .addrbox').eq(2).html('');
        $('.dlv_range .addrbox').eq(3).html('');
        $('.dlv_range .addrbox').eq(2).find('');
        $('.dlv_range .addrbox').eq(3).find('');

        $('#selectedRange').html('아래에서 지역을 선택해 주세요.');

        return false;
    }

    printLoading();

    $.post('./index.php', {'body': 'config@delivery_range.exe', 'exec': 'modify', 'no': no}, function(json) {
        window.range_no = json.no;

        $('#range_name').val(json.name);
        $('#reason').val(json.reason);

        // 주소 패널 출력
        $('.dlv_range .addrbox').eq(0).html(json.sido_list);
        $('.dlv_range .addrbox').eq(1).html(json.gugun_list);
        $('.dlv_range .addrbox').eq(2).html(json.dong_list);
        $('.dlv_range .addrbox').eq(3).html(json.ri_list);
        $('.dlv_range .addrbox').eq(2).find('.selected:gt(0)').removeClass('selected');
        $('.dlv_range .addrbox').eq(3).find('.selected:gt(0)').removeClass('selected');

        // 이벤트 지정
        $('.dlv_range .addrbox li').on('click', function(e) {
            deliveryRange($(this));
        });

        // 라벨 출력
        getDeliveryRangeLabel();

        removeLoading();
        $('#range_name').focus();
        $(document).scrollTop($('#range_name').offset().top-100);

        // 버튼명 변경
        $('.dlv_btn3>input').val('수정');
        $('.dlv_btn4>input').show();
    });
}

// 새로 고침
function reloadDeliveryRange(row, page)
{
    printLoading();
    $.post('./index.php?body=config@delivery_range.inc', {'execmode': 'ajax', 'row':row, 'page': page, 'type': current_possible_type}, function(r) {
        $('#delivery_range_list').html(r);
        removeLoading();
    });
}

// 엑셀 업로드
function uploadDeliveryRange(file)
{
    printLoading();

    var fd = new FormData();
    fd.append('excel', file.files[0]);
    fd.append('exec', 'excelUpload');
    fd.append('accept_json', 'Y');
    fd.append('type', current_possible_type);

    $.ajax({
        'url': './index.php?body=config@delivery_range.exe',
        'type':'post',
        'contentType': false,
        'processData': false,
        'async': false,
        'data': fd,
        'success': function(r) {
            file.value = '';
            if (typeof r == 'string') {
                r = $.parseJSON(r);
            }
            if (r.message) {
                removeLoading();
                window.alert(r.message);
                return false;
            }

            reloadDeliveryRange(10, 1);
        }
    });
}
</script>