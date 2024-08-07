<?PHP

/**
 *  배송업체 설정
 **/

addField($tbl['delivery_url'],'overseas_delivery',"enum('O', 'D') default 'D' comment '국내/해외배송여부'");

if ($cfg['use_partner_shop'] == 'Y') {
    $w .= " and partner_no='{$admin['partner_no']}'";
}

$res = $pdo->iterator("select * from {$tbl['delivery_url']} where 1 $w order by sort, no desc");

function parser($res)
{
    global $_overseas_delivery_arr;

    $data = $res->current();
    if ($data == false) return false;

    if(empty($data['overseas_delivery']) == true) {
        $data['overseas_delivery'] = 'D';
    }

    $data['idx'] = $res->key()+1;
    $data['name'] = sprintf("[%s] {$data['name']}", $_overseas_delivery_arr[$data['overseas_delivery']]);
    $data['hidden_up'] = ($data['idx'] == 1) ? "style='visibility:hidden'" : "";
    $data['hidden_dn'] = ($data['idx'] == $res->rowCount()) ? "style='visibility:hidden'" : "";

    $res->next();

    return array_map('stripslashes', $data);
}

?>
<form id="providerFrm" method="post" action="<?=$_SERVER['PHP_SELF']?>">
	<input type="hidden" name="body" value="config@delivery.exe">
	<input type="hidden" name="exec" value="remove">
	<div class="box_title first">
		<h2 class="title">배송업체 설정</h2>
	</div>
	<div class="box_middle left">
		<ul class="list_info">
			<li>배송추적 URL 입력 시 {송장번호}는 주문서 내 입력한 송장번호로 치환됩니다.</li>
			<li>배송업체 삭제/수정 시 기존 주문서 내 입력된 배송업체 정보가 변경됩니다. (삭제 시 주의)</li>
		</ul>
	</div>
	<table id="providerTbl" class="tbl_col">
		<caption class="hidden">배송업체 설정</caption>
		<colgroup>
            <col style="width:50px">
            <col>
            <col>
            <col style="width:100px">
            <col style="width:70px">
            <col style="width:70px">
		</colgroup>
		<thead>
			<tr>
                <th scope="col"><input type="checkbox" class="all_check"></th>
                <th scope="col">배송업체명</th>
                <th scope="col">배송추적URL</th>
                <th scope="col">순서</th>
                <th scope="col">수정</th>
                <th scope="col">삭제</th>
			</tr>
		</thead>
		<tbody>
            <?php if ($res->rowCount() > 0) { ?>
            <?php while($data = parser($res)) {?>
            <tr>
                <td><input type="checkbox" name="no[<?=$data['no']?>]" value="<?=$data['no']?>" class="sub_check"></td>
                <td class="left"><a href="#" class="editProvider" data-no="<?=$data['no']?>"><?=$data['name']?></a></td>
                <td class="left"><?=$data['url']?></td>
                <td>
                    <span class="sort_arrow arrow_up" <?=$data['hidden_up']?>><input type="button" value="" onclick="sortProvider(this, -1);" data-no="<?=$data['no']?>"></span>
                    <span class="sort_arrow arrow_down" <?=$data['hidden_dn']?>><input type="button" value="" onclick="sortProvider(this, 1);"data-no="<?=$data['no']?>"></span>
                </td>
                <td><span class="box_btn_s"><input type="button" value="수정" class="editProvider" data-no="<?=$data['no']?>"></span></td>
                <td><span class="box_btn_s"><input type="button" value="삭제" class="removeProvider" data-no="<?=$data['no']?>"></span></td>
            </tr>
            <?php }?>
            <?php } else { ?>
            <tr class="none">
            <td colspan="6"><p class="nodata">등록된 배송업체가 없습니다.</p></td>
            </tr>
            <?}?>
		</tbody>
	</table>
    <div class="box_bottom">
        <div class="left_area">
            <span class="box_btn_s icon delete"><input type="button" value="선택 삭제" onclick="removeProvider()"></span>
        </div>
        <div class="right_area">
            <span class="box_btn_s icon regist"><input type="button" value="등록" onclick="providerRegister.open()"></span>
        </div>
    </div>
</form>

<form method="POST" action="?" target="hidden<?=$now?>">
	<input type="hidden" name="body" value="config@config.exe">
	<div class="box_title">
		<h2 class="title">배송추적 설정</h2>
	</div>
	<table class="tbl_row">
		<caption class="hidden">배송추적 설정</caption>
		<colgroup>
			<col style="width:15%">
			<col>
		</colgroup>
		<tbody>
			<tr>
				<th scope="row">배송추적 방법 선택</th>
				<td>
					<label><input type="radio" name="invoice_prv" value="" <?=checked($cfg['invoice_prv'], '')?>> 택배사 링크(배송추적URL)</label>
					<label><input type="radio" name="invoice_prv" value="daum" <?=checked($cfg['invoice_prv'], 'daum')?>> 다음 모바일</label>
				</td>
			</tr>
		</tbody>
	</table>
	<div class="box_bottom">
		<span class="box_btn blue"><input type="submit" value="확인"></span>
	</div>
</form>

<script type="text/javascript">
var providerRegister = new layerWindow('config@delivery_prv.pop');

function removeProvider(no) {
    if (no) {
        var param = {
            'body': 'config@delivery.exe',
            'exec':'remove',
            'no[]': no
        }
    } else {
        if($('.sub_check:checked').length < 1) {
            window.alert('삭제할 계좌 정보를 선택해주세요.');
            return false;
        }
        var param = $('#providerFrm').serialize();
    }

    if (confirm('배송업체 정보를 삭제하시겠습니까?') == true) {
        printLoading();
        $.post('./index.php', param, function(r) {
            location.reload();
        });
    }
}

function sortProvider(o, step) {
    $.post('./index.php', {
        'body': 'config@delivery.exe',
        'exec':'sort',
        'type': '<?=$type?>',
        'no': $(o).data('no'),
        'step' : step
    }, function(r) {
        var content  = $(r).find('#providerTbl').html();
        $('#providerTbl').html(content);

        attachEvent();
    });
}

function attachEvent() {
    new chainCheckbox(
        $('.all_check'),
        $('.sub_check')
    )

    $('.editProvider').click(function() {
        providerRegister.open('&no='+$(this).data('no'));
    });

    $('.removeProvider').click(function() {
        removeProvider($(this).data('no'));
    });
}

$(function() {
    attachEvent();
});
</script>