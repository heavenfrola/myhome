<?php

/**
 * 엑셀 일괄처리 후 외부몰 연동 상품 재전송 실행
 **/

$pno = gzuncompress(base64_decode(urldecode($_GET['pno'])));
if (preg_match('/^[0-9,]+$/', $pno) == false) {
    msg('전송된 값에 오류가 확인되어 처리할수 없습니다. 상품별로 데이터를 재전송해주시기 바랍니다.', '?body=product@product_list');
}

$external = array();
if ($cfg['use_kakaoTalkStore'] == 'Y') $external[] = 'use_talkstore';
if ($scfg->comp('n_smart_store', 'Y')) $external[] = 'n_store_check';
$external = implode(',', $external);

$res = $pdo->iterator("select no, name, stat, sell_prc, updir, upfile3, $external from {$tbl['product']} where no in ($pno)");

function parse(&$res) {
    $data = $res->current();
    $res->next();
    if ($data == false) return false;

    $data['name'] = stripslashes($data['name']);
    $data['name_str'] = inputText($data['name']);
    $data['sell_prc'] = parsePrice($data['sell_prc'], true);
    $data['list_img'] = getListImgURL($data['updir'], $data['upfile3']);

    return $data;
}

?>
<div class="box_title first">
	<h2 class="title">외부스토어로 상품 전송</h2>
</div>
<div class="box_middle left">
    <ul class="list_info">
        <li>엑셀 일괄 업로드로 수정된 상품이 외부 스토어에 연동되어있을 경우 개별전송이 필요합니다.</li>
    </ul>
</div>
<table class="tbl_col">
    <caption class="hidden">외부스토어로 상품 전송</caption>
    <colgroup>
        <col>
        <col style="width:100px;">
        <col style="width:100px;">
        <col style="width:180px;">
        <col>
    </colgroup>
    <thead>
        <tr>
            <th>상품명</th>
            <th>상태</th>
            <th>판매가</th>
            <th>실행</th>
        </tr>
    </thead>
    <tbody>
        <?php while($data = parse($res)) { ?>
        <tr>
            <td class="left">
				<div class="box_setup order_stat btn_none">
					<div class="thumb">
                        <img src="<?=$data['list_img']?>" style="max-width:50px;">
                    </div>
                    <div style="margin-left:60px;">
                        <p class="title">
                            <a href="?body=product@product_register&pno=<?=$data['no']?>" target="_blank"><?=$data['name']?></a>
                        </p>
                    </div>
                 </div>
            </td>
            <td><?=$_prd_stat[$data['stat']]?></td>
            <td><?=$data['sell_prc']?></td>
            <td>
                <?php if ($data['n_store_check'] == 'Y') { ?>
                <p style="margin-bottom: 5px">
                    <span class="box_btn_s">
                        <input
                            type="button"
                            value="네이버 스마트스토어 전송"
                            class="sendBtn"
                            style="width:150px"
                            data-service="smartstore"
                            data-pno="<?=$data['no']?>"
                            data-name="<?=$data['name_str']?>"
                        >
                    </span>
                </p>
                <?php } ?>
                <?php if ($data['use_talkstore'] == 'Y') { ?>
                <p>
                    <span class="box_btn_s">
                        <input
                            type="button"
                            value="카카오톡 스토어 전송"
                            class="sendBtn"
                            style="width:150px"
                            data-service="talkstore"
                            data-pno="<?=$data['no']?>"
                            data-name="<?=$data['name_str']?>"
                        >
                    </span>
                </p>
                <?php } ?>
            </td>
        </tr>
        <?php } ?>
    </tbody>
</table>
<div class="box_bottom left">
    <span class="box_btn"><input type="button" value="일괄전송" onclick="updateAll()"></span>
</div>
<script type="text/javascript">
function updateExternal(service, pno, o) {
    printLoading();
    $.get('./?body=product@product_upload_exteral.exe', {'service':service, 'pno':pno}, function(r) {
        if(r == 'success') {
            $(o).parent().html('전송완료');
        } else {
            $(o).parent().replaceWith('<span class="p_color2">전송오류</a>')
            window.alert(r);
        }
        removeLoading();
    });
}

function updateAll() {
    var total_ea = $('.sendBtn').length;
    var processed_ea = 0;

    if(total_ea == 0) {
        window.alert('전송할 상품이 없습니다.');
        return false;
    }

    printLoading();

    $('.sendBtn').each(function() {
        var _this = $(this);
        $.get('./?body=product@product_upload_exteral.exe', {
            'service': _this.data('service'),
            'pno': _this.data('pno'),
        }, function(r) {
            if(r == 'success') {
                _this.parent().replaceWith('전송완료');
                processed_ea++;
            } else {
                window.alert('-'+_this.data('name')+'\n+'+r);
                _this.parent().replaceWith('<span class="p_color2">전송오류</a>');
                removeLoading();
                return false;
            }
            if(total_ea == processed_ea) {
                removeLoading();
            }
        });
    });
}

$(function() {
    $('.sendBtn').on('click', function() {
        updateExternal(
            $(this).data('service'),
            $(this).data('pno'),
            this)
        ;
    });
});
</script>