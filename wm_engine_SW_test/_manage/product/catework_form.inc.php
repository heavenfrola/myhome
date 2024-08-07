<?PHP

	/* +----------------------------------------------------------------------------------------------+
	' |  매장분류 관리
	' +----------------------------------------------------------------------------------------------+*/

	if($data['access_member']) {
		$access_limit = strpos($data['access_member'], 'buy') === 0 ? 3 : 2;
	}
	else $access_limit=1;

	$parent_parent = $data[getcatecode($level-1)];
	if (!$parent_parent) $parent_parent = 0;

	if ($data['private'] == null) {
		addField($tbl['category'], "private", "ENUM( 'N', 'Y' ) NOT NULL DEFAULT 'N'");
	}

?>
<input type="hidden" name="cno[<?=$loop?>]" value="<?=$data['no']?>">
<input type="hidden" name="template[<?=$loop?>]" value="basic">
<input type="hidden" name="use_top[<?=$loop?>]" value="Y">
<input type="hidden" name="apply_limit[<?=$loop?>]" value="1">
<?php if ($wmode == 'modify') { ?>
<div class="cateworkPad">
	<?=stripslashes($data['name'])?>
</div>
<?php } ?>
<table class="tbl_row">
	<caption class="hidden"><?=stripslashes($data['name'])?></caption>
	<colgroup>
		<col style="width:150px">
		<col>
	</colgroup>
	<tr>
		<th scope="row">분류명</th>
		<td>
			<input type="text" onfocus="onInput=1" onblur="onInput=0" name="name[<?=$loop?>]" value="<?=inputText($data['name'])?>" class="input" size="50">
			<?php if ($ctype != 3 && $ctype != 10 && $ctype != 9) { ?>
			<span class="box_btn_s"><a href="<?=$root_url?>/shop/big_section.php?cno1=<?=$data['no']?>" target="_blank">매장 바로가기</a></span>
			<?php } ?>
		</td>
	</tr>
	<?php if (in_array($ctype, array(3, 9, 10)) == false) { ?>
	<tr>
		<th scope="row">분류 코드</th>
		<td><input type="text" onfocus="onInput=1" onblur="onInput=0" name="code[<?=$loop?>]" value="<?=inputText($data['code'])?>" class="input"> <span class="explain">(영문 최대 30자, 상품 코드 자동 입력시 필수)</span></td>
	</tr>
	<tr>
		<th scope="row">속성</th>
		<td>
			<label class="p_cursor"><input type="checkbox" name="hidden[<?=$loop?>]" value="Y" <?=checked($data['hidden'],"Y")?>> 숨김</label>
			<p class="explain">* <?=$cfg['mobile_name']?>사용시 모바일기기에서 숨김은 <a href="./?body=wmb@category_config" class="p_color"><?=$cfg['mobile_name']?> > 매장분류 설정</a> 에서 하실 수 있습니다.</p>
		</td>
	</tr>
	<tr>
		<th scope="row">타이틀 이미지</th>
		<td>
			<input type="file" name="upfile1[<?=$loop?>]" class="input"> <?=delImgStr($data, 1)?>
			<ul class="list_msg">
				<li>등록한 이미지는 노출할 페이지에 <a>{{$분류타이틀이미지}}</a> 디자인코드를 삽입하셔야 합니다.</li>
			</ul>
		</td>
	</tr>
    <tr>
        <th scope="row">성인인증</th>
        <td>
            <label class="p_cursor"><input type="checkbox" name="adult_agree[<?=$loop?>]" value="Y" <?=checked($data['adult_agree'],"Y")?>> 사용</label>
            <ul class="list_msg">
                <li>..................................</li>
            </ul>
        </td>
    </tr>
	<tr>
		<th scope="row">접근 권한</th>
		<td>
			<p class="explain p_color2"> * 쇼핑몰 관리자로 로그인시 항상 접근 가능합니다</p>
			<label class="p_cursor"><input type="radio" name="access_limit[<?=$loop?>]" value="1" <?=checked($access_limit,1)?> onClick="cateAccess(this.form,<?=$loop?>)"> 제한 없음</label>
			<label class="p_cursor"><input type="radio" name="access_limit[<?=$loop?>]" value="3" <?=checked($access_limit,3)?> onClick="cateAccess(this.form,<?=$loop?>)"> 구매제한</label>
			<?php if ($data['ctype'] != 2) { ?>
			<label class="p_cursor"><input type="radio" name="access_limit[<?=$loop?>]" value="2" <?=checked($access_limit,2)?> onClick="cateAccess(this.form,<?=$loop?>)"> 접근 제한</label>
			<?php } ?>
			<p class="explain icon" style="margin:10px 0;">
				접근 가능 회원<br>
				<input type="checkbox" checked="" disabled=""> 게시판 관리자
				<?php foreach ($res as $gr) {?>
				<label class="p_cursor"><input type="checkbox" name="access_member[<?=$loop?>][]" id="access_member" <?=checked(preg_match("/@".$gr['no'].'/',$data['access_member']),true)?> value="<?=$gr['no']?>"> <?=$gr['name']?></label>
				<?php } ?>
			</p>
			<p class="explain icon">
				접근 불가 고객 리다이렉트 페이지<br><input type="text" onfocus="onInput=1" onblur="onInput=0" name="no_access_page[<?=$loop?>]" value="<?=inputText($data['no_access_page'])?>" class="input" size="50"><br>
			</p>
			<p class="explain icon" style="margin:10px 0;">
				접근차단시 출력메시지 <span class="explain">(미입력시 기본메시지 출력)</span><br><input type="text" onfocus="onInput=1" onblur="onInput=0" name="no_access_msg[<?=$loop?>]" value="<?=inputText($data['no_access_msg'])?>" class="input" size="50"><br>
			</p>
			<?php if ($data['ctype'] != 2) { ?>
			<p class="explain icon">
				구매제한시 출력메시지 <span class="explain">(미입력시 기본메시지 출력)</span><br><input type="text" onfocus="onInput=1" onblur="onInput=0" name="no_buy_msg[<?=$loop?>]" value="<?=inputText($data['no_buy_msg'])?>" class="input" size="50">
			</p>
			<?php } ?>
		</td>
	</tr>
	<?php if ($use_pack['print']=='Y') {?>
	<tr>
		<th scope="row">생플 이미지 크기</th>
		<td>
			<input type="text" onfocus="onInput=1" onblur="onInput=0" name="width[<?=$loop?>]" value="<?=$data['width']?>" class="input" size="4"> <span class="explain">(가로 PX)</span> X
			<input type="text" onfocus="onInput=1" onblur="onInput=0" name="height[<?=$loop?>]" value="<?=$data['height']?>" class="input" size="4"> <span class="explain">(세로 PX)</span>
		</td>
	</tr>
	<?php } ?>
	<?php if ($cfg['design_version'] != 'V3') { ?>
	<tr id="tr_designer_control_<?=$loop?>">
		<th scope="row">디자인 수정하기</th>
		<td><input type="checkbox" name="open_topdesigner" value="<?=$loop?>" onclick="open_topDesigner(this,<?=$data['no']?>)"></td>
	</tr>
	<tr id="tr_designer_main_<?=$loop?>" style="display:none">
		<td colspan="2">
			<img src="<?=$engine_url?>/_manage/image/b4.gif" border="0" alt="" align="middle"> <b>상단 출력 디자인</b>
			<?php if ($no && $data['level'] != 3) { ?>
			(<input type="checkbox" name="cont_copy[<?=$loop?>]" value="Y"> 하위분류에도 적용)
			<?php } ?>
			<textarea id="content2[<?=$loop?>]" name="content2[<?=$loop?>]" class="txta2" style="width: 570px; height:250px"><?=stripslashes($data['add_cont1'])?></textarea>
			<div id="nekopos_<?=$loop?>"></div>
		</td>
	</tr>
	<tr>
		<th scope="row">페이지당 상품<?php if ($use_pack['print'] == 'Y') { ?>(샘플)<?php } ?>수</th>
		<td>
			<input type="text" onfocus="onInput=1" onblur="onInput=0" name="cols[<?=$loop?>]" value="<?=$data['cols']?>" class="input" onBlur="countCateLines(this.form,<?=$loop?>)" size="2"> <font class="help">(가로)</font> X
			<input type="text" onfocus="onInput=1" onblur="onInput=0" name="rows[<?=$loop?>]" value="<?=$data['rows']?>" class="input" onBlur="countCateLines(this.form,<?=$loop?>)" size="2"> <font class="help">(세로)</font> =
			<input type="text" onfocus="onInput=1" onblur="onInput=0" name="lines[<?=$loop?>]" value="<?=($data['cols']*$data['rows'])?>" class="input" readonly  size="2"> <font class="help">(자동 계산)</font>
		</td>
	</tr>
	<tr>
		<th scope="row">상품 제목 줄임</th>
		<td>
			<input type="text" onfocus="onInput=1" onblur="onInput=0" name="cut_title[<?=$loop?>]" value="<?=$data['cut_title']?>" class="input" size="2"> <font class="help">(적지 않을 경우 줄이지 않습니다)</font>
		</td>
	</tr>
	<?php } else { ?>
	<tr>
		<th scope="row">상품 진열 설정</th>
		<td>
			<ul class="list_msg">
				<li>상품수 및 상품명 줄임 설정은 디자인관리를 통해 설정하실 수 있습니다.</li>
				<li>
					일반 상품 : 디자인관리 > 페이지 편집 > 상품 리스트 > {{$상품리스트}} 코드의 내부 설정 값
					<a href="#" onClick="window.open('./pop.php?body=design@editor.frm&design_edit_key=shop_big_section.tmp&design_edit_code=product_list&type=','userCode','top=10px, left=10px,width=850px, height=700px, status=no, toolbars=no, scrollbars=yes');" class="p_color3">바로가기</a>
				</li>
				<li>
					기획전 상품 : 디자인관리 > 제공 코드 편집 > 사용자 코드 > 생성된 사용자 상품 코드의 내부 설정 값
					<a href="./?body=design@editor_code&default_code=user_code" target="_blank" class="p_color3">바로가기</a>
				</li>
			</ul>
		</td>
	</tr>
	<?php } ?>
	<?php if ($ctype == 1) { ?>
	<tr>
		<th scope="row">개인결제창</th>
		<td>
			<label class="p_cursor"><input type="checkbox" name="private[<?=$loop?>]" value="Y" <?=checked($data['private'],"Y")?>> 개인결제창 카테고리는 검색광고 및 상품정렬 등에 나타나지 않습니다.</label>
		</td>
	</tr>
	<?php } ?>
	<?php } else if ($cfg['opmk_api'] == 'shopLinker') { ?>
	<tr>
		<th scope="row">샵링커 품목코드</th>
		<td>
			<input type="text" onfocus="onInput=1" onblur="onInput=0" name="code[<?=$loop?>]" value="<?=inputText($data['code'])?>" class="input">
			<span class="explain">샵링크 상품의 상품항목고시 연동시 필요합니다.</span>
		</td>
	</tr>
	<?php } ?>
</table>
<?php if (count($cno)-1 == $loop) { ?>
<div class="bottom_btn2 center">
	<span class="box_btn blue"><input type="submit" value="확인"></span>
	<?php if (count($cno) == 1) { ?>
	<span class="box_btn gray"><input type="button" value="삭제" onclick="controlByajex('product@catework_del.exe&parent=<?=$parent_parent?>&cno[]=<?=$data['no']?>','<?=$parent_parent?>','<?=$parent_no?>',1)"></span>
	<?php } ?>
	<?php if ($wmode == "modify") { ?>
	<span class="box_btn"><input type="button" value="리스트" onclick="moveCat(<?=$parent?>);"></span>
	<?php } ?>
</div>
<?php } ?>