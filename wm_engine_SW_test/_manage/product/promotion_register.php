<?PHP
	/* +----------------------------------------------------------------------------------------------+
	' |  프로모션 기획전 등록
	' +----------------------------------------------------------------------------------------------+*/

	$prno = numberOnly($_GET['prno']);
	$listURL = $_SESSION['listURL'];

	if($prno) {
		$data = $pdo->assoc(sprintf("select * from `%s` where `no`='%d'", $tbl['promotion_list'], $prno));
		$data = array_map('stripslashes', $data);
		if($data['period_type']=="Y") {
			if($data['date_start']) {
				list($ts_dates, $ts_times) = explode(' ', $data['date_start']);
				if($ts_times) {
					$_ts_times = explode(':', $ts_times);
					$ts_times = $_ts_times[0];
				}
			}
			if($data['date_end']) {
				list($ts_datee, $ts_timee) = explode(' ', $data['date_end']);
				if($ts_timee) {
					$_ts_timee = explode(':', $ts_timee);
					$ts_timee = $_ts_timee[0];
				}
			}
		}else {
			$data['date_start'] = "";
			$data['date_end'] = "";
		}
	}else {
		$data['use_yn'] = "Y";
		$data['period_type'] = "N";
	}

	$neko_id = ($prno) ? "promotion_".$prno : "promotion_temp_".$now;
	$m_neko_id = ($prno) ? "mpromotion_".$prno : "mpromotion_temp_".$now;

?>
<form name="prmFrm" id="prmFrm" method="post" action="./index.php" target="hidden<?=$now?>" enctype="multipart/form-data" action="./?body=product@promotion_register.exe">
	<input type="hidden" name="body" value="product@promotion_register.exe">
	<input type="hidden" name="prno" value="<?=$prno?>">
	<input type="hidden" name="content" value="">
	<input type="hidden" name="mcontent" value="">
	<input type="hidden" name="pgrp_merge" value="">
	<input type="hidden" name="neko_id" value="<?=$neko_id?>">
	<input type="hidden" name="m_neko_id" value="<?=$m_neko_id?>">
	<div class="box_title first">
		<h2 class="title">프로모션 기획전 등록</h2>
	</div>
	<table class="tbl_row">
		<caption class="hidden">프로모션 기획전 등록</caption>
		<colgroup>
			<col style="width:15%">
			<col style="width:85%">
		</colgroup>
		<tr>
			<th scope="row"><strong>프로모션 기획전명</strong></th>
			<td>
				<input type="text" id="promotion_nm" name="promotion_nm" class="input input_full" value="<?=$data['promotion_nm']?>">
				<?if($prno) {?><span class="box_btn_s"><a href="/shop/promotion.php?pno=<?=$data[no]?>" target="_blank">바로가기</a></span><?}?>
			</td>
		</tr>
		<tr>
			<th scope="row">진행기간</th>
			<td>
				<label class="p_cursor"><input type="radio" name="period_type" value="N" <?=checked($data['period_type'],"N")?> onClick="chgperiodType(this.form)"> 무제한</label>
				<label class="p_cursor"><input type="radio" name="period_type" value="Y" <?=checked($data['period_type'],"Y")?> onClick="chgperiodType(this.form)"> 기간선택</label>&nbsp;
				<input type="text" name="ts_dates" value="<?=$ts_dates?>" size="10" class="input datepicker">
				<select name="ts_times">
					<?for($i = 0; $i <= 23; $i++) {$i = sprintf('%02d', $i)?>
					<option value="<?=$i?>" <?=checked($ts_times, $i, 1)?>><?=$i?> 시</option>
					<?}?>
				</select> ~
				<input type="text" name="ts_datee" value="<?=$ts_datee?>" size="10" class="input datepicker">
				<select name="ts_timee">
					<?for($i = 0; $i <= 23; $i++) {$i = sprintf('%02d', $i)?>
					<option value="<?=$i?>" <?=checked($ts_timee, $i, 1)?>><?=$i?> 시</option>
					<?}?>
				</select>
			</td>
		</tr>
		<tr>
			<th scope="row">사용여부</th>
			<td>
				<label class="p_cursor"><input type="radio" name="use_yn" value="Y" <?=checked($data['use_yn'], 'Y')?>> 사용</label>
				<label class="p_cursor"><input type="radio" name="use_yn" value="N" <?=checked($data['use_yn'], 'N')?>> 미사용</label>
			</td>
		</tr>
		<tr>
			<th scope="row">프로모션 기획전 내용</th>
			<td>
				<ul class="tab_pr">
					<li class="on">
						<a onclick="tabover(0); return false;" class="box">PC 프로모션 기획전 내용</a>
					</li>
					<li>
						<a onclick="tabover(1); return false;" style="padding-left:10px" class="box">모바일 프로모션 기획전 내용</a>
						<label><input type="checkbox" name="use_m_content" value="Y" <?=checked($data['use_m_content'], 'Y')?>> 사용함</label>
					</li>
				</ul>
				<div class="tab_pr_cnt tabcnt0">
					<textarea id="content2" name="content2" style="margin:0; padding:0; border:0; width:100%; height:<?=($_COOKIE[product_content_height] > 0) ?  $_COOKIE[product_content_height] : "500"; ?>px;"><?=stripslashes($data['content'])?></textarea>
				</div>
				<div class="tab_pr_cnt tabcnt1" style="display:none;">
					<textarea id="m_content" name="m_content" style="margin:0; padding:0; border:0; width:100%; height:<?=($_COOKIE[product_content_height] > 0) ?  $_COOKIE[product_content_height] : "500"; ?>px;"><?=stripslashes($data['m_content'])?></textarea>
				</div>
			</td>
		</tr>
		<tr>
			<th scope="row">프로모션 상품그룹 추가</th>
			<td>
				<div style="padding-bottom:10px">
					<span class="box_btn_s"><a onclick="searchGroup(this)">프로모션 상품그룹 등록</a></span>
					<span class="box_btn_s"><a onclick="searchGroupcall(this)">프로모션 상품그룹 불러오기</a></span>
					<ul class="list_btn_move">
						<li><span class="btn_move last_h"><input type="button" name="" value="마지막" onclick="srt2.toBottom()"></span></li>
						<li><span class="btn_move next_h"><input type="button" name="" value="아래" onclick="srt2.move(+1)"></span></li>
						<li><span class="btn_move prev_h"><input type="button" name="" value="위" onclick="srt2.move(-1)"></span></li>
						<li><span class="btn_move first_h"><input type="button" name="" value="처음" onclick="srt2.toTop()"></span></li>
					</ul>
				</div>
				<table class="tbl_inner line full" name="groupFrm" id="groupFrm">
					<caption class="hidden">상품수정/관리 리스트</caption>
					<colgroup>
						<col>
						<col style="width:80px">
						<col style="width:120px">
					</colgroup>
					<thead>
						<tr>
							<th scope="col">프로모션 상품그룹명</th>
							<th scope="col">상품 수</th>
							<th scope="col">수정/삭제</th>
						</tr>
					</thead>
					<tbody id="prd_add_list">
						<?php
						if($prno) {
							$res = $pdo->iterator("select * from $tbl[promotion_link] where prm_no='$prno' order by `sort` asc");
                            foreach ($res as $data) {
								$pgrp_no = $data['pgrp_no'];
								include $engine_dir."/_manage/product/promotion_register_add.exe.php";
							}
						}
						?>
					</tbody>
				</table>
			</td>
		</tr>
	</table>
	<div class="box_bottom">
		<span class="box_btn blue"><input type="button" onclick="prmSubmit();" value="확인"></span>
		<span class="box_btn gray"><a href="./?body=product@promotion_list">취소</a></span>
	</div>
</form>
<script type="text/javascript">
	var srt2 = null;
	$(function() {
		srt2 = new Sorttbl('groupFrm');
	});
	//그룹등록
	var pgsearch = new layerWindow('product@promotion_group.frm');
	pgsearch.pcan = function(opno) {
		var prdArray = [];
		var f = document.prmgFrm;
		var pno = f.pno.value;
		var gno = $('#gno').val();
		if(pno) {
			_pno = pno.split("|");
			_pno.forEach(function(val) {
				if(opno != val) {
					prdArray.push(val);
				}
			})
			f.pno.value = prdArray.join("|");
			$.get('?body=product@promotion_group.exe', {"pno":prdArray}, function(data) {
				$('#sort_list').html(data);
			})
			$.get('?body=product@promotion_product.exe', {"exec":"product_delete", "gno":gno, "pno":opno}, function(data) {
				$('#prm_sort #'+opno).remove();
			})
		}
	}
	//그룹불러오기
	var pgcall = new layerWindow('product@promotion_group_call.frm');
	function searchGroupcall(obj) {
		pgcall.input = obj;
		pgcall.open();
	}
	function searchGroup(obj, pgrp_no) {
		var sparam = '';
		if(pgrp_no) sparam += '?pgrp_no='+pgrp_no;

		pgsearch.input = obj;
		pgsearch.open(sparam, {"name":"pop1", "topmargin":20, "leftmargin":-250});
	}
	function searchGroupcancel(pgrp_no, prno) {
		if(!confirm("상품그룹을 삭제하시겠습니까?")) return false;
		$.post('?body=product@promotion_product.exe', {"pgrp_no":pgrp_no, "prno":prno, "exec":"delete"}, function(data) {
			alert("상품그룹이 삭제 되었습니다.");
			$('#prd_add_list #'+pgrp_no).remove();
		})
	}
	// 기획전내용 탭
	function tabover(no) {
		var tabs = $('.tab_pr').find('li');
		tabs.each(function(idx) {
			var detail = $('.tabcnt'+idx);
			var img = $(this);
			if(no == idx) {
				detail.show();
				img.addClass('on');
			} else {
				detail.hide();
				img.removeClass('on');
			}
		})

		if(no == 1) {
			editor_code="<?=$m_neko_id?>";
			editor_gr="m_board_pro";
			editor = new R2Na("m_content", "", "");
			editor.initNeko('<?=$m_neko_id?>', 'm_board_pro', "img");
		}else {
			editor_code="<?=$neko_id?>";
			editor_gr="board_pro";
			editor = new R2Na("content2", "", "");
			editor.initNeko('<?=$neko_id?>', 'board_pro', "img");
		}
	}
	
	function prmSubmit() {
		var prdArray = [];
		var f = document.prmFrm;

		if(!f.promotion_nm.value) {
			alert("기획전명을 입력해주세요.");
			return false;
		}

		$('#prd_add_list tr').each(function() {
			var pgrp_no = $(this).attr('id');
			if(!pgrp_no) return;
			prdArray.push(pgrp_no);
		});

		f.pgrp_merge.value = prdArray;

		if(typeof f.content2!='undefined') {
			try { submitContents('content2', ''); } catch (ex) { }
			f.content.value = f.content2.value;
		}
		if(typeof f.m_content!='undefined') {
			try { submitContents('m_content', ''); } catch (ex) { }
			f.mcontent.value = f.m_content.value;
		}
		f.submit();
	}

</script>
<script type="text/javascript" src="<?=$engine_url?>/_engine/R2Na/R2Na.js"></script>
<script type="text/javascript" src="<?=$engine_url?>/_engine/smartEditor/js/HuskyEZCreator.js"></script>
<script type='text/javascript'>
	var editor_code="<?=$neko_id?>";
	var editor_gr="board_pro";
	var editor = new R2Na("content2", "", "");
	editor.initNeko('<?=$neko_id?>', 'board_pro', "img");
	$(document).ready(function() {
		chgperiodType(document.prmFrm);
	})
	function chgperiodType(f){
		var type = $(':checked[name=period_type]').val();
		if(type=='Y') {
			f.ts_dates.style.backgroundColor='';
			f.ts_datee.style.backgroundColor='';
			f.ts_times.style.backgroundColor='';
			f.ts_timee.style.backgroundColor='';
			f.ts_dates.disabled=false;
			f.ts_datee.disabled=false;
			f.ts_times.disabled=false;
			f.ts_timee.disabled=false;
		}else {
			f.ts_dates.style.backgroundColor='#EFEFEF';
			f.ts_datee.style.backgroundColor='#EFEFEF';
			f.ts_datee.style.backgroundColor='#EFEFEF';
			f.ts_times.style.backgroundColor='#EFEFEF';
			f.ts_timee.style.backgroundColor='#EFEFEF';
			f.ts_dates.disabled=true;
			f.ts_datee.disabled=true;
			f.ts_times.disabled=true;
			f.ts_timee.disabled=true;
		}
	}
</script>