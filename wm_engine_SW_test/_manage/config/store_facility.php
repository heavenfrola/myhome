<?PHP
/**
 * [매장지도] 시설 안내 등록
 */

$sql = "select * from {$tbl['store_facility_set']} where 1 order by sort asc, no asc";
$res = $pdo->iterator($sql);
$total = $pdo->rowCount($sql);
$ii = 0;

if(!$cfg['store_facility_icon_config']) $cfg['store_facility_icon_config'] = 'A';
?>
<div class="box_title first">
    <h2 class="title">시설안내 관리 </h2>
</div>
<form id="fieldSortFrm">
    <input type="hidden" name="body" value="config@store_facility.exe">
    <input type="hidden" name="exec" value="remove">

    <table class="tbl_col tbl_col_bottom">
        <caption class="hidden">시설안내 관리</caption>
        <colgroup>
            <col style="width:30px">
            <col style="width:250px">
            <col style="width:50px">
            <col style="width:250px">
            <col style="width:50px">
            <col style="width:100px">
        </colgroup>
        <thead>
        <tr>
            <th>고유번호</th>
            <th scope="col">시설명</th>
            <th scope="col">아이콘</th>
            <th scope="col">설명</th>
            <th scope="col">순서</th>
            <th scope="col">관리</th>
        </tr>
        </thead>
        <tbody>
		<?php if ($total == 0) { ?>
            <tr>
                <td colspan="6"><p class="nodata">등록된 시설안내가 없습니다.</p></td>
            </tr>
		<?php } ?>
		<?php
		foreach ($res as $data) {
			$ii++;
			$fd_img = $data['upfile1'] ? "<img src=".$root_url."/".$data['updir']."/".$data['upfile1']." style='max-height:50px;'>" : "";

			$idx = sprintf("%02d", $ii);
			$up_disabled = $ii == 1 ? "visibility: hidden;" : "";
			$dn_disabled = $ii == $total ? "visibility: hidden;" : "";
			?>
            <tr id="fno_<?php echo $data['no'];?>" class="fieldset">
                <td><?php echo $data['no']; ?></td>
                <td class="left"><a href="#" onclick="addFieldset('<?php echo $data['no']; ?>'); return false;"><strong><?php echo stripslashes($data['name']); ?></strong></a></td>
                <td><?php echo $fd_img; ?></td>
                <td><?php echo $data['content']; ?></td>
                <td style="line-height:34px;">
                    <img src="<?php echo $engine_url; ?>/_manage/image/arrow_up.gif" onclick="fieldSort(this, -1);" class="p_cursor" style="<?php echo $up_disabled; ?>">
                    <img src="<?php echo $engine_url; ?>/_manage/image/arrow_down.gif" onclick="fieldSort(this, 1);" class="p_cursor" style="<?php echo $dn_disabled; ?>">
                </td>
                <td>
                    <span class="box_btn_s"><input type="button" value="수정" onclick="addFieldset('<?php echo $data['no']; ?>');"></span>
                    <span class="box_btn_s gray"><input type="button" value="삭제" onClick="removePrdField(<?php echo $data['no']; ?>)"></span>
                </td>
            </tr>
		<?php }?>
        </tbody>
    </table>
    <div class="box_bottom" style="height: 30px;">
        <div class="right_area">
            <span class="box_btn blue"><input type="button" value="항목추가" onclick="addFieldset();"></span>
        </div>
    </div>
</form>
<br>
<form method="post" enctype="multipart/form-data" action="./index.php" target="hidden<?php echo $now; ?>" onsubmit="printLoading();">
    <input type="hidden" name="body" value="config@config.exe" />
    <table class="tbl_row">
        <colgroup>
            <col style="width:250px;">
        </colgroup>
        <caption>시설안내 설정</caption>
        <tr>
            <th scope="row">노출 형식
                <a href="#" class="tooltip_trigger" data-child="tooltip_facility_setting">설명</a>
                <div class="info_tooltip tooltip_facility_setting w700">
                    <h3>노출 형식</h3>
                    <p>매장안내 상세 페이지에서의 시설안내 정보 노출 형식을 선택합니다.</p>
                    <a href="#" class="tooltip_closer">닫기</a>
                </div>
            </th>
            <td>
                <label><input type="radio" name="store_facility_icon_config" value="A"  <?php echo checked($cfg['store_facility_icon_config'], 'A'); ?>> 아이콘 + 시설명 표시</label>
                <label><input type="radio" name="store_facility_icon_config" value="B"  <?php echo checked($cfg['store_facility_icon_config'], 'B'); ?>> 아이콘만 표시</label>
            </td>
        </tr>
    </table>
    <div class="box_bottom" style="height: 30px;">
        <div class="right_area">
            <span class="box_btn blue"><input type="submit" value="등록" </span>
        </div>
    </div>
</form>

<script type="text/javascript">
    var fdFrm = new layerWindow('config@store_facility_frm.exe');
    fdFrm.reload = function() {
        $.get('<?php echo getURL().'&execmode=ajax'?>', function(r) {
            $('#fieldSortFrm').html($(r).filter('#fieldSortFrm').html());
            window.fdFrm.close();
            removeLoading();
        });
    }

    function fieldSort(obj, s) {
        var source = $(obj).parents('tr.fieldset');
        var target = (s > 0) ? $(obj).parents('tr.fieldset').next() : $(obj).parents('tr.fieldset').prev();

        if(source.length == 1 && target.length == 1) {
            source = source.attr('id').replace('fno_', '');
            target = target.attr('id').replace('fno_', '');

            $.post('./index.php', {'body':'config@store_facility.exe', 'exec':'sort', 'source':source, 'target':target}, function() {
                fdFrm.reload();
            });
        }
    }

    function removePrdField(f) {
        var param = null;
        if(typeof f == 'object') {
            if($('.check_one:checked').length == 0) {
                window.alert('삭제할 데이터를 선택해주세요.');
                return false;
            }
            param = $(f).serialize();
        } else {
            var form = document.getElementById('fieldSortFrm');
            param = {'body':form.body.value, 'exec':'remove', 'no[]':f};
        }

        if(confirm('선택한 항목을 삭제하시겠습니까?')) {
            printLoading();
            $.post('./index.php', param, function() {
                location.reload();
            });
        }
    }

    function addFieldset(fno) {
        fdFrm.open('&fno='+fno);
    }

    $(function() {
        chainCheckbox(
            $('.check_all'),
            $('.check_one')
        );
    });

    function checkStoreField(f){
        if(!checkBlank(f.name,'상호명을 입력해주세요.')) return false;

        f.target = hid_frame;
        printLoading();
    }
</script>