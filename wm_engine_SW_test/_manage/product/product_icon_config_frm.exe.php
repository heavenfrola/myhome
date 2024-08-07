<?PHP

	printAjaxHeader();
	$exec = $_GET['exec'];
	if(!$exec) $exec='register';

    $NumTotalRec = $_GET['NumTotalRec'];
    $query_string =  $_GET['query_string'];

?>
<div id="popupContent" class="popupContent layerPop" style="width:620px;">
	<div id="header" class="popup_hd_line">
		<h1 id="logo"><img src="<?=$engine_url?>/_manage/image/wisa.gif" alt="WISA."></h1>
		<div id="mngTab_pop">아이콘관리</div>
	</div>
	<div id="popupContentArea">
		<div id="controlTab" style="margin-top:10px">
			<ul class="tabs">
				<li id="ctab_1" onclick="iconConfig.open('exec=register&NumTotalRec=<?=$NumTotalRec?>&query_string=<?=urlencode($query_string)?>')" <? if($exec == 'register') { ?>class="selected"<? } ?>>등록</li>
				<li id="ctab_2" onclick="iconConfig.open('exec=change&NumTotalRec=<?=$NumTotalRec?>&query_string=<?=urlencode($query_string)?>')" <? if($exec == 'change') { ?>class="selected"<? } ?>>변경</li>
				<li id="ctab_3" onclick="iconConfig.open('exec=delete&NumTotalRec=<?=$NumTotalRec?>&query_string=<?=urlencode($query_string)?>')" <? if($exec == 'delete') { ?>class="selected"<? } ?>>삭제</li>
			</ul>
			<ul class="list_msg left">
				<? if($exec == 'register') {?>
				<li>상품에 등록된 일괄등록할 아이콘을 선택후 하단의 일괄처리하기 버튼을 눌러주시기 바랍니다.</li>
				<?} else if($exec == 'change') {?>
				<li>상품에 등록된 일괄변경할 아이콘을 선택후 하단의 일괄처리하기 버튼을 눌러주시기 바랍니다.</li>
				<li>첫번째 선택 아이콘 <span style="color:#C94445">빨간색테두리(변경전)</span>,두번째 선택 아이콘 <span style="color:#0264cf">파란색테두리(변경후)</span></li>
				<?} else if($exec == 'delete') {?>
				<li>상품에 등록된 일괄삭제할 아이콘을 선택후 하단의 일괄처리하기 버튼을 눌러주시기 바랍니다.</li>
				<?}?>
			</ul>
			<form name="prdIconsFrm" action="./?body=product@product_icon_config.exe" method="post">
				<input type="hidden" name="icons" value="">
				<input type="hidden" name="exec" value="<?=$exec?>">
				<input type="hidden" name="query_string" value="<?=$query_string?>">
				<input type="hidden" name="check_pno" value="">
				<div style="width:100%; height:365px; margin:0 0 20px 0; overflow-y:scroll;">
					<table class="tbl_icon iconTbl">
						<?php
							include_once $engine_dir."/_manage/product/product_icon.inc.php";

							$i=0;
							$sql="select * from `$tbl[product_icon]` where `itype`='' order by `no` desc";
							$res=$pdo->iterator($sql);
                            foreach ($res as $data) {
								$i++;
								$icon=getIconTag($data);
								$icon=preg_replace('/href="([^"]+)" target="_blank"/', 'href="javascript:;" onclick="prdIconSel(document.prdIconsFrm, \''.$data['no'].'\')" onfocus="this.blur()"', $icon);
						?>
						<td id="icon<?=$data['no']?>" class="iconSelD"><?=$icon?></td>
						<?php
							if($i %5 == 0) echo "</tr><tr>";
							}

							while($i %5 != 0) {
								$i++;
								echo "<td></td>";
							}
						?>
					</table>
				</div>
				<div class="pop_bottom top_line left">
					<?if($exec == 'register') {?>
						선택한 아이콘을
						<select name="stype">
							<option value="1">전체상품에</option>
							<option value="2">현재 검색된 모든 상품(<?=number_format($NumTotalRec)?>개)에</option>
							<option value="3" selected>선택된상품에</option>
						</select> 일괄등록합니다.
					<?}else if($exec == 'change') {?>
						선택한 아이콘을
						<select name="stype">
							<option value="1">전체상품에</option>
							<option value="2">현재 검색된 모든 상품(<?=number_format($NumTotalRec)?>개)에</option>
							<option value="3" selected>선택된상품에</option>
						</select>
						<p style="padding-top:5px;"><span style="color:#C94445;">빨간색테두리</span>로 선택되어진 아이콘을 <br><span style="color:#0264cf;">파란색테두리</span>로 선택되어진 아이콘으로 변경합니다.</p>
					<?}else if($exec == 'delete') {?>
						선택한 아이콘을
						<select name="stype">
							<option value="1">전체상품에</option>
							<option value="2">현재 검색된 모든 상품(<?=number_format($NumTotalRec)?>개)에</option>
							<option value="3" selected>선택된상품에</option>
						</select> 일괄삭제합니다.
					<?}?>
				</div>
			</form>
		</div>
	</div>
	<div class="pop_bottom top_line">
		<span class="box_btn_s blue"><input type="button" value="일괄처리하기" onclick="prdIconConfigCheck()"></span>
		<span class="box_btn_s gray"><input type="button" value="창닫기" onclick="iconConfig.close()"></span>
	</div>
</div>

<script type="text/javascript">
	function prdIconConfigCheck() {
		var f=document.prdIconsFrm;
		var pf=document.prdFrm;
		if(f.icons.value == '') {
			alert('아이콘을 선택하세요');
			return;
		}
		if(f.stype.selectedIndex == 1) {
			<? if(empty($NumTotalRec)) { ?>
			alert('검색된 상품이 없습니다.\t');
			return;
			<? } ?>
		}
		else if(f.stype.selectedIndex == 2) {
			if(!checkCB(pf.check_pno, "일괄 수정할 상품을 선택해주세요.")) return;
			var pnos='';
			for(i=0; i < pf.length; i++) {

				if(pf[i].type=="checkbox") {

					if(pf[i].checked == true) pnos+=','+pf[i].value;
				}
			}
			f.check_pno.value=pnos;
		}
        printLoading();
		f.target=hid_frame;
		f.submit();
	}

	var selectedNum=0;
	function prdIconSel(f, no) {
		var icons=f.icons;
		var iconsV=icons.value;
		if($('#icon'+no).hasClass('iconSelR') == true || $('#icon'+no).hasClass('iconSelB') == true) {
			selectedNum--;
			icons.value=iconsV.replace(','+no, '');
			$('#icon'+no).removeClass('iconSelR');
			<? if($exec == 'change') { ?>
				icons.value='';
				$('.iconTbl tr td').removeClass('iconSelR');
				$('.iconTbl tr td').removeClass('iconSelB');
				selectedNum=0;
			<? } ?>
			return;
		} else {
			<? if($exec == 'change') { ?>
			if(selectedNum >= 2) {
				alert('다른 아이콘을 선택할 경우 기존 선택된아이콘 체크를 해제 후\n 선택하여 주시기 바랍니다.');
				return;
			}
			<? } ?>
			selectedNum++;
			<? if($exec == 'change') { ?>
				if(selectedNum == 1) $('#icon'+no).addClass('iconSelR');
				else if(selectedNum == 2) $('#icon'+no).addClass('iconSelB');
				else $('#icon'+no).addClass('iconSelR');
			<? } else { ?>
				$('#icon'+no).addClass('iconSelR');
			<? } ?>
		}
		var nos=new Array;
		ono=icons.value;
		new_no=ono+','+no;
		nos=new_no.split(',');
		nos=array_unique(nos);
		new_no=implode(",", nos);
		icons.value=new_no;
	}
	var iconConfig = new layerWindow('product@product_icon_config_frm.exe');
</script>