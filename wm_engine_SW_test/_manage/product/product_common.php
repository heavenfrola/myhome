<?PHP

	/* +----------------------------------------------------------------------------------------------+
	' |  상품공통정보 설정
	' +----------------------------------------------------------------------------------------------+*/

	include_once $engine_dir."/_engine/include/shop_detail.lib.php";

	$partner_content = "";
	if($admin['partner_no'] > 0) {
		$partner_content = "_".$admin['partner_no'];
		$cdir=$dir['upload']."/partner_common_".$admin['partner_no'];
		makeFullDir($cdir);
		$partner_hidden = "style='display:none;'";
	}else {
		$cdir=$dir['upload']."/".$dir['prd_common'];
		makeFullDir($cdir);
		$partner_hidden = "";
	}
	$data=getWMDefault(array("content3".$partner_content, "content4".$partner_content, "content5".$partner_content, "ptn_content_use".$partner_content));

	if($cfg['use_prd_etc1'] != 'Y') $cfg['use_prd_etc1'] = 'N';
	if($cfg['use_prd_etc2'] != 'Y') $cfg['use_prd_etc2'] = 'N';
	if($cfg['use_prd_etc3'] != 'Y') $cfg['use_prd_etc3'] = 'N';
	if(!$cfg['prd_etc1']) $cfg['prd_etc1'] = '기타항목1';
	if(!$cfg['prd_etc2']) $cfg['prd_etc2'] = '기타항목2';
	if(!$cfg['prd_etc3']) $cfg['prd_etc3'] = '기타항목3';
	if(!$cfg['use_prc_consult']) $cfg['use_prc_consult'] = 'N';
	if(!$cfg['use_option_product']) $cfg['use_option_product'] = 'N';
	if(!$cfg['use_trash_prd']) $cfg['use_trash_prd'] = 'N';
	if(isset($cfg['use_qty_discount']) == false) $cfg['use_qty_discount'] = 'N';
	if(isset($cfg['use_no_mile/cpn']) == false) $cfg['use_no_mile/cpn'] = 'N';
	if(isset($cfg['use_set_product']) == false) $cfg['use_set_product'] = 'N';

?>
<script type="text/javascript">
new Clipboard('.clipboard');
</script>
<form name method="post" action="<?=$_SERVER['PHP_SELF']?>?body=product@product_common.exe" target="hidden<?=$now?>" onsubmit="printLoading()">
	<div class="box_title first">
		<h2 class="title">상품 공통정보</h2>
	</div>
	<table class="tbl_row">
		<caption class="hidden">상품 공통정보</caption>
		<colgroup>
			<col style="width:15%">
			<col>
		</colgroup>
		<?php if($admin['partner_no'] > 0) {
			if(!$data['ptn_content_use'.$partner_content]) $data['ptn_content_use'.$partner_content] = "N";
		?>
		<tr>
			<th scope="row">입점사 별 공통정보</th>
			<td>
				<label class="p_cursor"><input type="radio" name="ptn_content_use<?=$partner_content?>" value="Y" <?=checked($data['ptn_content_use'.$partner_content], 'Y')?>> 사용함</label>
				<label class="p_cursor"><input type="radio" name="ptn_content_use<?=$partner_content?>" value="N" <?=checked($data['ptn_content_use'.$partner_content], 'N')?>> 사용안함</label>
			</td>
		</tr>
		<?php } ?>
		<?php for ($ii=3; $ii<=5; $ii++) { ?>
		<tr>
			<th scope="row"><?=$cfg['content'.$ii]?></th>
			<td><textarea name="content<?=$ii?><?=$partner_content?>" class="txta"><?=inputText($data['content'.$ii.$partner_content])?></textarea></td>
		</tr>
		<?php } ?>
	</table>
	<div class="box_middle2 left">
		<p class="explain">
			<i class="icon_info"></i>
			정의된 치환문자를 활용하여 상품 공통정보 입력이 가능합니다.
			<span class="box_btn_s"><input type="button" value="치환문자 확인" class="code_btn" onclick="openReplaceCode(this);"></span>
		</p>
	</div>
	<div class="box_bottom">
		<span class="box_btn blue"><input type="submit" value="확인"></span>
	</div>

    <div class="layer_view seo layer_view_content">
        <dl>
            <dt>(상품 공통정보) 치환문자 안내</dt>
            <dd>
                <table class="tbl_inner full line">
                    <caption class="hidden">(상품 공통정보) 치환문자 안내</caption>
                    <colgroup>
                        <col style="width:200px;">
                        <col>
                    </colgroup>
                    <thead>
                    <tr>
                        <th scope="row">치환문자</th>
                        <th scope="row">설명</th>
                    </tr>
                    </thead>
                    <tbody>
                    <tr>
                        <td class="left">{배송업체명}</td>
                        <td class="left">배송업체 설정에 등록 된 배송업체명을 출력합니다.</td>
                    </tr>
                </table>
            </dd>
        </dl>
        <a onclick="$('.layer_view.seo').hide();" class="close"></a>
    </div>
</form>
<form name method="post" action="<?=$_SERVER['PHP_SELF']?>?body=product@product_common.exe" target="hidden<?=$now?>" enctype="multipart/form-data" onsubmit="printLoading()">
	<input type="hidden" name="exec" value="upload">
	<table class="tbl_row" style="border-top:0;">
		<caption class="hidden">상품 공통 이미지 업로드</caption>
		<colgroup>
			<col style="width:15%">
			<col>
		</colgroup>
		<tr>
			<th scope="row">공통정보 첨부이미지</th>
			<td>
				<div class="list_info">
					<p>공통정보에 사용할 이미지를 업로드하여 등록할 수 있습니다.</p>
				</div>
				<ul class="list_upload">
					<?php
						if($_use['file_server'] == 'Y' && fsConFolder($cdir)) {
							$fs_ftp_con = "";
							fileServerCon($file_server_num);

							$open_dir = ftp_nlist($fs_ftp_con, "/".$file_server[$file_server_num]['file_dirname']."/".$cdir);
							$file_url = $file_server[$file_server_num]['url'];

							if(is_array($open_dir)) {
								foreach ( $open_dir as $val) {
									$val = basename($val);
									if(preg_match("/^\./",$val)) continue;
									$cimg = $file_url."/".$cdir."/".$val;
									?>
									<li>
										<div class="img"><a href="<?=$cimg?>" target="_blank"><img src="<?=$cimg?>"></a></div>
										<div class="copy_del">
											<span class="box_btn_s"><a href="javascript:tagCopy('<img src=<?=$cimg?>>')">태그복사</a></span>
											<span class="box_btn_s"><a href="javascript:imgDel('<?=$val?>')">삭제</a></span>
										</div>
									</li>
									<?php
								}
							} else {
								echo "파일 서버 리스트를 가져오지 못했습니다 [$file_server_num]";
							}
						} else {
							$ci=0;
							$open_dir=opendir($root_dir."/".$cdir);
							while($cfile=readdir($open_dir)){
								if($cfile!="." && $cfile!="..") {
									if(!is_file($root_dir."/".$cdir."/".$cfile)) continue;
									$cimg=$root_url."/".$cdir."/".$cfile;
									?>
									<li>
										<div class="img"><a href="<?=$cimg?>" target="_blank"><img src="<?=$cimg?>"></a></div>
										<div class="copy_del">
											<span class="box_btn_s"><input type="button" value="태그복사" class="clipboard" data-clipboard-text="<img src='<?=$cimg?>'>"></span>
											<span class="box_btn_s"><a href="javascript:imgDel('<?=$cfile?>')">삭제</a></span>
										</div>
									</li>
									<?php
								}
							}
							closedir($open_dir);
						}
					?>
				</ul>
			</td>
		</tr>
		<tr>
			<th scope="row">이미지 업로드</th>
			<td>
				<input type="file" name="upfile" class="input input_full">
				<span class="box_btn_s blue"><input type="submit" value="업로드"></span>
			</td>
		</tr>
	</table>
</form>

<form name="" method="post" action="<?=$_SERVER['PHP_SELF']?>" target="hidden<?=$now?>" <?=$partner_hidden?> onsubmit="printLoading()">
	<input type="hidden" name="body" value="config@config.exe">
	<input type="hidden" name="config_code" value="common">
	<div class="box_title">
		<h2 class="title">상품조회 리스트 항목 설정</h2>
	</div>
	<table class="tbl_row">
		<caption class="hidden">상품조회 리스트 항목 설정</caption>
		<colgroup>
			<col style="width:15%">
			<col>
		</colgroup>
		<tr>
			<th scope="row">상품관리 항목설정</th>
			<td>
				<label for="prd_prd_code" class="p_cursor"><input type="checkbox" name="prd_prd_code" id="prd_prd_code" value="Y" <?=checked($cfg['prd_prd_code'],"Y")?>> 상품코드</label>
				<label for="prd_name_referer" class="p_cursor"><input type="checkbox" name="prd_name_referer" id="prd_name_referer" value="Y" <?=checked($cfg['prd_name_referer'],"Y")?>> 참고상품명</label>
				<label for="prd_reg_date" class="p_cursor"><input type="checkbox" name="prd_reg_date" id="prd_reg_date" value="Y" <?=checked($cfg['prd_reg_date'],"Y")?>> 등록일</label>
				<label for="prd_normal_prc" class="p_cursor"><input type="checkbox" name="prd_normal_prc" id="prd_normal_prc" value="Y" <?=checked($cfg['prd_normal_prc'],"Y")?>> <?=$cfg['product_normal_price_name']?></label>
                <label for="prd_origin_name" class="p_cursor"><input type="checkbox" name="prd_origin_name" id="prd_origin_name" value="Y" <?=checked($cfg['prd_origin_name'],"Y")?>> 장기명</label>
                <label for="prd_seller" class="p_cursor"><input type="checkbox" name="prd_seller" id="prd_seller" value="Y" <?=checked($cfg['prd_seller'],"Y")?>> 사입처</label>
                <label for="prd_origin_prc" class="p_cursor"><input type="checkbox" name="prd_origin_prc" id="prd_origin_prc" value="Y" <?=checked($cfg['prd_origin_prc'],"Y")?>> 사입원가</label>
			</td>
		</tr>
	</table>
	<div class="box_bottom">
		<span class="box_btn blue"><input type="submit" value="확인"></span>
	</div>
</form>
<div id="go_refprds"></div>
<form method="post" action="<?=$_SERVER['PHP_SELF']?>" target="hidden<?=$now?>" <?=$partner_hidden?> onsubmit="printLoading()">
	<input type="hidden" name="body" value="config@config.exe">
	<input type="hidden" name="exec" value="refprds">
	<input type="hidden" name="config_code" value="refprds">
	<div class="box_title">
		<h2 class="title">다중 관련상품 설정</h2>
	</div>
	<table class="tbl_row">
		<caption class="hidden">다중 관련상품 설정</caption>
		<colgroup>
			<col style="width:15%">
			<col>
		</colgroup>
		<tr>
			<th scope="row">관련상품 세트수</th>
			<td>
				<select name="refprds" id="refprds">
					<?php for ($i = 1; $i <= 5; $i++) { ?>
					<option value="<?=$i?>" <?=checked($i, $cfg['refprds'], 1)?>><?=$i?></option>
					<?php } ?>
				</select>
			</td>
		</tr>
		<?php for ($i = 2; $i <= 5; $i++) { ?>
		<tr id="refprd_tr<?=$i?>" class="refprd_tr" <?=$cfg['refprds'] >= $i?'':'style="display:none;"'?>>
			<th scope="row">관련상품<?=$i?> 명칭</th>
			<td><input type="text" name="refprd<?=$i?>_name" value="<?=$cfg['refprd'.$i.'_name']?$cfg['refprd'.$i.'_name']:"관련상품${i}"?>" class="input input_full"></td>
		</tr>
		<?php } ?>
	</table>
	<div class="box_bottom">
		<span class="box_btn blue"><input type="submit" value="확인"></span>
	</div>
</form>

<form method="post" action="<?=$_SERVER['PHP_SELF']?>" target="hidden<?=$now?>" <?=$partner_hidden?> onsubmit="printLoading()">
	<input type="hidden" name="body" value="config@config.exe">
	<input type="hidden" name="config_code" value="prdetc">
	<div class="box_title">
		<h2 class="title">상품 추가정보 설정</h2>
	</div>
	<table class="tbl_row">
		<caption class="hidden">상품 추가정보 설정</caption>
		<colgroup>
			<col style="width:15%">
			<col>
		</colgroup>
		<tr>
			<th scope="row" rowspan="2">상품 추가정보1</th>
			<td>
				<label><input type="radio" name="use_prd_etc1" value="Y" <?=checked($cfg['use_prd_etc1'], 'Y')?>> 사용함</label>
				<label><input type="radio" name="use_prd_etc1" value="N" <?=checked($cfg['use_prd_etc1'], 'N')?>> 사용안함</label>
			</td>
		</tr>
		<tr>
			<td>
				항목명 <input type="text" name="prd_etc1" class="input" value="<?=$cfg['prd_etc1']?>">
			</td>
		</tr>
		<tr>
			<th scope="row" rowspan="2">상품 추가정보2</th>
			<td>
				<label><input type="radio" name="use_prd_etc2" value="Y" <?=checked($cfg['use_prd_etc2'], 'Y')?>> 사용함</label>
				<label><input type="radio" name="use_prd_etc2" value="N" <?=checked($cfg['use_prd_etc2'], 'N')?>> 사용안함</label>
			</td>
		</tr>
		<tr>
			<td>
				항목명 <input type="text" name="prd_etc2" class="input" value="<?=$cfg['prd_etc2']?>">
			</td>
		</tr>
		<tr>
			<th scope="row" rowspan="2">상품 추가정보3</th>
			<td>
				<label><input type="radio" name="use_prd_etc3" value="Y" <?=checked($cfg['use_prd_etc3'], 'Y')?>> 사용함</label>
				<label><input type="radio" name="use_prd_etc3" value="N" <?=checked($cfg['use_prd_etc3'], 'N')?>> 사용안함</label>
			</td>
		</tr>
		<tr>
			<td>
				항목명 <input type="text" name="prd_etc3" class="input" value="<?=$cfg['prd_etc3']?>">
			</td>
		</tr>
	</table>
	<div class="box_bottom">
		<span class="box_btn blue"><input type="submit" value="확인"></span>
	</div>
</form>

<form method="post" action="<?=$_SERVER['PHP_SELF']?>" target="hidden<?=$now?>" <?=$partner_hidden?> onsubmit="printLoading()">
	<input type="hidden" name="body" value="config@config.exe">
	<input type="hidden" name="config_code" value="prc_consultation">
	<div class="box_title">
		<h2 class="title">상품등록 설정</h2>
	</div>
	<table class="tbl_row">
		<caption class="hidden">상품등록 설정</caption>
		<colgroup>
			<col style="width:15%">
			<col>
		</colgroup>
		<tr>
			<th scope="row">판매가 대체문구 사용</th>
			<td>
				<label><input type="radio" name="use_prc_consult" value="Y" <?=checked($cfg['use_prc_consult'], 'Y')?>> 사용함</label>
				<label><input type="radio" name="use_prc_consult" value="N" <?=checked($cfg['use_prc_consult'], 'N')?>> 사용안함</label>
			</td>
		</tr>
		<tr>
			<th scope="row">부속상품 사용</th>
			<td>
				<label><input type="radio" name="use_option_product" value="Y" <?=checked($cfg['use_option_product'], 'Y')?>> 사용함</label>
				<label><input type="radio" name="use_option_product" value="N" <?=checked($cfg['use_option_product'], 'N')?>> 사용안함</label>
			</td>
		</tr>
		<tr>
			<th scope="row">모바일 상세설명 사용</th>
			<td>
				<label><input type="radio" name="use_m_content_product" value="Y" <?=checked($cfg['use_m_content_product'], 'Y')?>> 사용함</label>
				<label><input type="radio" name="use_m_content_product" value="N" <?=checked($cfg['use_m_content_product'], 'N')?>> 사용안함</label>
			</td>
		</tr>
		<?php if (isset($cfg['use_qty_discount']) == false || $cfg['use_qty_discount'] != 'Y') { ?>
		<tr>
			<th scope="row">상품별 수량할인 사용</th>
			<td>
				<label><input type="radio" name="use_qty_discount" value="Y" <?=checked($cfg['use_qty_discount'], 'Y')?>> 사용함</label>
				<label><input type="radio" name="use_qty_discount" value="N" <?=checked($cfg['use_qty_discount'], 'N')?>> 사용안함</label>
				<ul class="list_info">
					<li class="warning">상품이나 주문이 많을 경우 잠시 사이트가 느려지거나 접속이 안될수 있습니다. 유휴시간대에 진행해 주시기 바랍니다.</li>
				</ul>
			</td>
		</tr>
		<?php } ?>
		<?php if ($cfg['use_set_product'] != 'Y') { ?>
		<tr>
			<th scope="row">세트상품 사용</th>
			<td>
				<label><input type="radio" name="use_set_product" value="Y" <?=checked($cfg['use_set_product'], 'Y')?>> 사용함</label>
				<label><input type="radio" name="use_set_product" value="N" <?=checked($cfg['use_set_product'], 'N')?>> 사용안함</label>
				<ul class="list_info warning">
					<li>설정 시 사이트가 수 초에서 수십 분까지 멈출 수 있습니다. 반드시 유휴시간에 설정해 주시기 바랍니다.</li>
				</div>
			</td>
		</tr>
		<?php } ?>
		<?php if ($cfg['use_no_mile/cpn'] != 'Y') { ?>
		<tr>
			<th scope="row">상품별 적립금 및 쿠폰 사용 제한</th>
			<td>
				<label><input type="radio" name="use_no_mile/cpn" value="Y" <?=checked($cfg['use_no_mile/cpn'], 'Y')?>> 사용함</label>
				<label><input type="radio" name="use_no_mile/cpn" value="N" <?=checked($cfg['use_no_mile/cpn'], 'N')?>> 사용안함</label>
				<ul class="list_info">
					<li class="warning">최초 설정 시 상품이 많을 경우 사이트가 수초 ~ 수십 초 동안 멈출 수 있습니다.</li>
				</ul>
			</td>
		</tr>
		<?php } ?>
        <?php if($scfg->comp('compare_explain', 'Y') == false) { ?>
		<tr>
			<th scope="row">쇼핑엔진 제외</th>
			<td>
				<label><input type="radio" name="compare_explain" value='Y' <?=checked($cfg['compare_explain'], 'Y')?>> 사용함 </label>
				<label><input type="radio" name="compare_explain" value='' <?=checked($cfg['compare_explain'], '')?>> 사용 안함</label>
			</td>
		</tr>
        <?php } ?>
	</table>
	<div class="box_bottom">
		<span class="box_btn blue"><input type="submit" value="확인"></span>
	</div>
</table>

<form method="post" action="<?=$_SERVER['PHP_SELF']?>" target="hidden<?=$now?>" <?=$partner_hidden?>>
	<input type="hidden" name="body" value="config@config.exe">
	<input type="hidden" name="config_code" value="prc_consultation">
	<div class="box_title">
		<h2 class="title">상품 삭제/보관 기간 설정</h2>
	</div>
	<table class="tbl_row">
		<caption class="hidden">상품 삭제/보관 기간 설정</caption>
		<colgroup>
			<col style="width:15%">
			<col>
		</colgroup>
		<tr>
			<th scope="row">삭제 상품 이동</th>
			<td>
				<label><input type="radio" name="use_trash_prd" value="Y" <?=checked($cfg['use_trash_prd'], 'Y')?>> 상품 휴지통으로 이동</label>
				<label><input type="radio" name="use_trash_prd" value="N" <?=checked($cfg['use_trash_prd'], 'N')?>> 즉시 영구 삭제(복구 불가)</label>
			</td>
		</tr>
		<tr>
			<th scope="row">삭제 상품 보관 기간</th>
			<td>
				<?=selectArray(array(1, 2, 3, 4, 5, 6, 7, 8, 9, 10), 'trash_prd_trcd', true, '삭제안함', $cfg['trash_prd_trcd'])?>
				일 후 영구삭제
				<ul class="list_info">
					<li>영구삭제된 상품의 모든 데이터(이미지 포함)는 복구가 불가능합니다.</li>
					<li>휴지통에 담긴 상품도 일반상품과 동일하게 전체 사용 이미지 용량에 포함됩니다.</li>
				</ul>
			</td>
		</tr>
	</table>
	<div class="box_bottom">
		<span class="box_btn blue"><input type="submit" value="확인"></span>
	</div>
</table>

<script type="text/javascript">
	function imgCode(chaineAj) {
		var myQuery = document.input_frm.content;
		chaineAj='<img src="../_img/'+chaineAj+'">';

		if(parent.document.selection) {
			myQuery.focus();
			sel = parent.document.selection.createRange();
			sel.text = chaineAj;
		}
	}
	function imgDel(img){
		if(confirm('선택한 이미지를 삭제하시겠습니까?')) {
			hidden<?=$now?>.location.href='./?body=product@product_common.exe&exec=delete&img='+img;
		}
	}

	$('#refprds').change(function(){
		var _num = $(this).val();
		$('.refprd_tr').hide();
		if(_num > '1'){
			for(i=2;i<=parseInt(_num);i++){
				$('#refprd_tr'+i).show();
			}
		}


	});

    function openReplaceCode(o) {
        var f = $(o).parents('form');
        var layer = f.find('.layer_view.seo');

        $('.layer_view.seo').not(layer).hide();
        layer.toggle();
        layer.css('top', f.find('.code_btn').offset().top+40);
    }
</script>