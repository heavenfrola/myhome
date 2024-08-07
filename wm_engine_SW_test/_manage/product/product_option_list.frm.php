<?PHP

	/* +----------------------------------------------------------------------------------------------+
	' |  옵션세트 관리
	' +----------------------------------------------------------------------------------------------+*/

	$pno = numberOnly($_GET['pno']);
	$stat = numberOnly($_GET['stat']);
	$eatype = numberOnly($_GET['eatype']);

	if($stat!=1 && $stat!=5) {
		$stat=2;
	}

	if($stat == 5) $hidden = "style=\"display:none\"";

	if($pno > 0) {
		$prd = $pdo->assoc("select hash, ea_type, stat from `$tbl[product]` where `no`='$pno'");

		$is_complex = $pdo->row("select count(*) from `erp_complex_option` where `pno`='$pno'");
		$complex_mode = ($eatype == 1 || (!$eatype && $prd['stat'] > 1 && $prd['ea_type'] == 1)) ? true : false;
	}

?>
<style type="text/css">
body {background: none;}
</style>
<form name="prdFrm" method="post" action="<?=$_SERVER['PHP_SELF']?>" target="hidden<?=$now?>">
	<input type="hidden" name="body" value="product@product_option.exe">
	<input type="hidden" name="exec" value="copy">
	<input type="hidden" name="stat" value="5">
	<table class="tbl_col">
		<caption class="hidden">옵션세트 관리</caption>
		<thead>
			<tr>
				<th scope="col" <?=$hidden?> style="width:50px;"><input type="checkbox" onclick="checkAll(document.prdFrm.check_pno,this.checked)"></th>
				<th scope="col">이름</th>
				<th scope="col" style="width:200px;">속성</th>
				<th scope="col" <?=$hidden?> style="width:100px;">순서</th>
				<th scope="col" style="width:100px;">수정</th>
				<th scope="col" style="width:100px;">삭제</th>
				<?php if($cfg['use_opt_addimg'] == 'Y' && $pno > 0) { ?>
				<th scope="col" style="width:100px;">이미지</th>
				<?php } ?>
			</tr>
		</thead>
		<tbody style="position:relative;">
			<?php
				// stat = 1:임시, 2:상품의 옵션, 5:세트
				if($pno) $where = " and `pno`='$pno'";
				if($stat > 2) $where .=" and `stat`='$stat'";

				if($cfg['use_partner_shop'] == 'Y' && !$pno) {
					if(!$admin['partner_no']) $admin['partner_no'] = '0';
					$where .= " and partner_no='$admin[partner_no]'";
				}
                $prev_ness = '';
                $ness_type = array('Y' => '필수옵션', 'N' => '선택옵션', 'P' => '부속옵션');

				$res = $pdo->iterator("select * from `$tbl[product_option_set]` where 1 $where order by necessary='P' asc, necessary='N' asc, sort asc");
				$total = $res->rowCount();
				$idx = 0;
				$complex_cnt = 0;

                $ness_cnt = array();
                foreach ($res as $data) {
                    $ness_cnt[$data['necessary']]++;
                }
                foreach ($res as $data) {
					$idx++;
					if($data['necessary'] == 'C') $data['necessary'] = 'Y';
					switch($data['necessary']) {
						case 'Y' : $necessary = '필수'; break;
						case 'N' : $necessary = '선택'; break;
						case 'P' : $necessary = '부속상품'; break;
					}

					if($data['sort']!=$idx) $pdo->query("update `$tbl[product_option_set]` set `sort`='$idx' where `no`='$data[no]'");
					$data['option_plug'] = ($data['otype'] == '4B') ? '_text' : '';
                    if ($stat != 5) {
                        $ness_changed = ($prev_ness != $data['necessary']);
                        $prev_ness = $data['necessary'];
                    }

			?>
            <?php if (count($ness_cnt) > 1 && $ness_changed) { ?>
            <tr>
                <th colspan="7" class="left"><?=$ness_type[$data['necessary']]?></th>
            </tr>
            <?php } ?>
			<tr class="productOptionSets" data-opno="<?=$data['no']?>">
				<td <?=$hidden?>><input type="checkbox" name="check_pno[]" id="check_pno" value="<?=$data['no']?>"></td>
				<td class="left"><?=stripslashes($data['name'])?></td>
				<td class="left"><?=$_otype[$data['otype']]?> (<?=$necessary?>)</td>
				<td <?=$hidden?> class="necessary_<?=$data['necessary']?>">
                    <a class="btn_up" href="#" onclick="optPrdSort(this, -1); return false;"><img src="<?=$engine_url?>/_manage/image/arrow_up.gif" alt="위로"></a>
                    <a class="btn_dn" href="#" onclick="optPrdSort(this, 1); return false;"><img src="<?=$engine_url?>/_manage/image/arrow_down.gif" alt="아래로"></a>
				</td>
				<td>
					<span class="box_btn_s"><a href="javascript:;" onClick="wisaOpen('./pop.php?body=product@product_option<?=$data['option_plug']?>.frm&pop=1&opno=<?=$data['no']?>&pno=<?=$pno;?>','pfldpot2', 'yes')" class="sclink">수정</a></span>
				</td>
				<td>
					<span class="box_btn_s"><input type="hidden" id="option_necessary_<?=$data['no']?>" value="<?=$data['necessary']?>">
					<a href="javascript:;" onClick="deletePrdOption(<?=$data['no']?>)" class="sclink gray">삭제</a></span>
				</td>
				<?php if ($cfg['use_opt_addimg'] == 'Y' && $pno > 0) { ?>
				<td>
					<?php if ($data['otype'] != '4B') { ?>
					<span class="box_btn_s"><input type="button" value="이미지" onclick="mngProductOptionImg(<?=$pno?>, <?=$data['no']?>, this); "></span>
					<?php } ?>
				</td>
				<?php } ?>
			</tr>
			<?php } ?>
		</tbody>
	</table>
	<?php if ($pno > 0) { ?>
	<div class="box_bottom right">
		<span class="box_btn_s blue"><a href="javascript:;" onclick="wisaOpen('./pop.php?body=product@product_option.frm&pop=1&pno=<?=$pno?>&stat=<?=$stat?>', 'pfldpot2', 'yes', 100,100);">옵션 추가</a></span>
		<span class="box_btn_s blue"><a href="javascript:;" onclick="wisaOpen('./pop.php?body=product@product_option_text.frm&pop=1&pno=<?=$pno?>&stat=<?=$stat?>&otype=4B', 'pfldpot2', 'yes', 100,100);">텍스트옵션 추가</a></span>
		<span class="box_btn_s blue"><a href="javascript:;" onclick="wisaOpen('./pop.php?body=product@product_option_load.frm&pop=1&pno=<?=$pno?>&stat=<?=$stat?>', 'poLoad', 'yes');">옵션세트 불러오기</a></span>
		<!-- 옵션세트 버튼 -->
		<?php if ($stat != 5 && $idx > 0) { ?>
		<span class="box_btn_s"><a href="javascript:;" onclick="loadOption(document.prdFrm);">선택한 옵션을 옵션세트로 저장</a></span>
		<?php } ?>
	</div>
	<?php } ?>
</form>

<form name="optFrm" method="post" action="<?=$_SERVER['PHP_SELF']?>" target="hidden<?=$now?>">
	<input type="hidden" name="body" value="product@product_option.exe">
	<input type="hidden" name="exec" value="sort">
	<input type="hidden" name="new_sort" value="">
	<input type="hidden" name="opno" value="">
</form>

<script type="text/javascript">
function mngProductOptionImg(pno, ino, o) {
	var win = $('.product_option_img_'+ino);
	if(win.length > 0) {
		win.remove();
		$('iframe[name=optFrame]', parent.document).height(0).height($('body').prop('scrollHeight'));
		return;
	}

	var div = $(o).parents('tr');
	var colspan = div.find('td').length;
	div.after("<tr class='product_option_img_"+ino+"'><td></td><td colspan='"+(colspan-1)+"'><iframe id='up_aimg_"+ino+"' src='./index.php?body=product@product_file.frm&filetype=4&sno="+ino+"&stat=<?=$stat?>&pno="+pno+"' width='100%' height='50' scrolling='no' frameborder='0'></td></tr>");
}

function setSortButtons() {
    $('.btn_up, .btn_dn').show();
    new Array('Y', 'N', 'P').forEach(function(ness) {
        $('.necessary_'+ness).find('.btn_up').first().hide();
        $('.necessary_'+ness).find('.btn_dn').last().hide();
    });
}

window.onload=function() {
	$('iframe[name=optFrame]', parent.document).height(0).height($('body').prop('scrollHeight'));
    setSortButtons();
}
</script>
<?PHP

	if(!$pno) return;

	// 윙POS 기초재고 입력
	if($complex_mode) include $engine_dir.'/_manage/product/product_pos.inc.php';

?>