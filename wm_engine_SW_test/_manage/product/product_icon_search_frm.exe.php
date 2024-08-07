<?PHP

	printAjaxHeader();

?>
<div id="popupContent" class="popupContent layerPop" style="width:620px;">
	<div id="header" class="popup_hd_line">
		<h1 id="logo"><img src="<?=$engine_url?>/_manage/image/wisa.gif" alt="WISA."></h1>
		<div id="mngTab_pop">아이콘검색</div>
	</div>
	<div id="popupContentArea">
		<p class="explain">* 검색을 원하시는 아이콘을 <span class="p_color2">빨간색</span>으로 활성화 한 후 버튼을 눌러주시기 바랍니다.</p>
		<table class="tbl_icon">
			<?php
				include_once $engine_dir."/_manage/product/product_icon.inc.php";

				$icons=preg_replace("/^,|,$/", "", $icons);
				$_icons=explode(",", $icons);

				$i=0;
				$sql="select * from `$tbl[product_icon]` where `itype`='' order by `no` desc";
				$res = $pdo->iterator($sql);
                foreach ($res as $data) {
					$i++;
					$icon=getIconTag($data);
					$icon=preg_replace('/href="([^"]+)" target="_blank"/', 'href="javascript:;" onclick="prdIconSel(document.prdSearchFrm, \''.$data['no'].'\')" onfocus="this.blur()"', $icon);
					?>
					<td id="icon<?=$data['no']?>" <? if(is_array($_icons) && in_array($data['no'], $_icons)) { ?>class="iconSel"<? } else { ?>class="iconSelD"<? } ?>>
						<?=$icon?>
					</td>
					<?
					if($i %5 == 0) echo "</tr><tr>";
				}

				while($i %5 != 0) {
					$i++;
					echo "<td class=\"iconSelD\"></td>";
				}
			?>
		</table>
	</div>
	<div class="pop_bottom">
		<span class="box_btn_s blue"><input type="button" value="바로검색" onclick="document.prdSearchFrm.submit()"></span>
		<span class="box_btn_s"><input type="button" value="선택초기화" onclick="document.prdSearchFrm.icons.value=''; iconSearch.open()"></span>
		<span class="box_btn_s gray"><input type="button" value="창닫기" onclick='iconSearch.close()'></span>
	</div>
</div>

<script type="text/javascript">
	function prdIconSel(f, no) {

		var icons=f.icons;
		var iconsV=icons.value;
		if($('#icon'+no).hasClass('iconSel') == true) {

			icons.value=iconsV.replace(','+no, '');
			$('#icon'+no).removeClass('iconSel');
			$('#icon'+no).addClass('iconSelD');
			return;
		} else {

			$('#icon'+no).addClass('iconSel');
			$('#icon'+no).removeClass('iconSelD');
		}

		var nos=new Array;
		ono=icons.value;
		new_no=ono+','+no;
		nos=new_no.split(',');
		nos=array_unique(nos);
		new_no=implode(",", nos);
		icons.value=new_no;
	}
</script>