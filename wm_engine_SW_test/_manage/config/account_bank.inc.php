<?PHP

/**
 *  무통장 계좌 설정
 **/

if (!fieldExist($tbl['bank_account'], 'type')) { // 해외은행 필드 체크
    $pdo->query("alter table `$tbl[bank_account]` add `type` enum('', 'int') not null default ''");
    $pdo->query("alter table `$tbl[bank_account]` add index `type`(`type`)");
}

$res = $pdo->iterator("select * from `".$tbl['bank_account']."` where type='$type' order by `sort`");

function parser($res)
{
    $data = $res->current();
    if ($data == false) return false;

    $data['idx'] = $res->key()+1;
    $data['hidden_up'] = ($data['idx'] == 1) ? "style='visibility:hidden'" : "";
    $data['hidden_dn'] = ($data['idx'] == $res->rowCount()) ? "style='visibility:hidden'" : "";

    $res->next();

    return array_map('stripslashes', $data);
}

?>
<form id="bankFrm" method="post" action="<?=$_SERVER['PHP_SELF']?>">
	<input type="hidden" name="body" value="config@account_bank.exe">
	<input type="hidden" name="exec" value="remove">

	<div class="box_title first">
		<h2 class="title">무통장 계좌 설정</h2>
	</div>
	<table id="bankTbl" class="tbl_col">
		<caption class="hidden">무통장 계좌 설정</caption>
		<colgroup>
            <col style="width:50px">
            <col>
            <col>
            <col>
            <col style="width:100px">
            <col style="width:70px">
            <col style="width:70px">
		</colgroup>
		<thead>
			<tr>
                <th scope="col"><input type="checkbox" class="all_check"></th>
                <th scope="col">은행명</th>
                <th scope="col">계좌번호</th>
                <th scope="col">예금주</th>
                <th scope="col">순서</th>
                <th scope="col">수정</th>
                <th scope="col">삭제</th>
			</tr>
		</thead>
		<tbody>
            <?php if ($res->rowCount() > 0) { ?>
            <?php while ($data = parser($res)) {?>
            <tr>
                <td><input type="checkbox" name="no[<?=$data['no']?>]" value="<?=$data['no']?>" class="sub_check"></td>
                <td class="left"><a href="#" class="editBank" data-no="<?=$data['no']?>"><?=$data['bank']?></a></td>
                <td class="left"><?=$data['account']?></td>
                <td class="left"><?=$data['owner']?></td>
                <td>
                    <span class="sort_arrow arrow_up" <?=$data['hidden_up']?>><input type="button" value="" onclick="sortBank(this, -1);" data-no="<?=$data['no']?>"></span>
                    <span class="sort_arrow arrow_down" <?=$data['hidden_dn']?>><input type="button" value="" onclick="sortBank(this, 1);"data-no="<?=$data['no']?>"></span>
                </td>
                <td><span class="box_btn_s"><input type="button" value="수정" class="editBank" data-no="<?=$data['no']?>"></span></td>
                <td><span class="box_btn_s"><input type="button" value="삭제" class="removeBank" data-no="<?=$data['no']?>"></span></td>
            </tr>
            <?php }?>
			<?php } else { ?>
            <tr class="none">
                <td colspan="7"><p class="nodata">등록된 계좌번호가 없습니다.</p></td>
            </tr>
            <?php } ?>
        </tbody>
    </table>
    <div class="box_bottom">
        <div class="left_area">
            <span class="box_btn_s icon delete"><input type="button" value="선택 삭제" onclick="removeBank()"></span>
        </div>
        <div class="right_area">
            <span class="box_btn_s icon regist"><input type="button" value="등록" onclick="bankRegister.open()"></span>
        </div>
	</div>
</form>
<script type="text/javascript">
var bankRegister = new layerWindow('config@account_bank.pop&type=<?=$type?>');

function removeBank(no) {
    if (no) {
        var param = {
            'body': 'config@account_bank.exe',
            'exec':'remove',
            'no[]': no
        }
    } else {
        if($('.sub_check:checked').length < 1) {
            window.alert('삭제할 계좌 정보를 선택해주세요.');
            return false;
        }
        var param = $('#bankFrm').serialize();
    }

    if (confirm('무통장 계좌 정보를 삭제하시겠습니까?') == true) {
        printLoading();
        $.post('./index.php', param, function(r) {
            location.reload();
        });
    }
}

function sortBank(o, step) {
    $.post('./index.php', {
        'body': 'config@account_bank.exe',
        'exec':'sort',
        'type': '<?=$type?>',
        'no': $(o).data('no'),
        'step' : step
    }, function(r) {
        var content  = $(r).find('#bankTbl').html();
        $('#bankTbl').html(content);
    });
}

$(function() {
    new chainCheckbox(
        $('.all_check'),
        $('.sub_check')
    )

    $('.editBank').click(function() {
        bankRegister.open('&no='+$(this).data('no'));
    });

    $('.removeBank').click(function() {
        removeBank($(this).data('no'));
    });
});
</script>