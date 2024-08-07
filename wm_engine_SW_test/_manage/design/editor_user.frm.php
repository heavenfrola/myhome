<?PHP

    use Wing\Design\BannerGroup;

	/* +----------------------------------------------------------------------------------------------+
	' |  사용자 코드 편집
	' +----------------------------------------------------------------------------------------------+*/

	include_once $engine_dir."/_engine/include/design.lib.php";
	include_once $engine_dir."/_manage/design/version_check.php";
	$_skin_name=editSkinName();
	include_once $root_dir."/_skin/".$_skin_name."/skin_config.".$_skin_ext['g'];
	if(file_exists($root_dir."/_skin/".$_skin_name."/user_code.".$_skin_ext['g'])) include_once $root_dir."/_skin/".$_skin_name."/user_code.".$_skin_ext['g'];

	$type = addslashes($_GET['type']);
	$user_code = addslashes($_GET['user_code']);
	$code_type = $_GET['code_type'];

	versionChk("V3");

	if($user_code){
		if(!$_user_code[$user_code]) msg("해당 코드는 삭제되었습니다", "close");
		$_code_type=$_user_code[$user_code]['code_type'];
		$_code_name=userCodeName($user_code);
		foreach($_user_code[$user_code] as $ukey=>$uval){
			${$ukey}=$uval;
		}
		$file_src=$root_dir."/_skin/".$_skin_name."/MODULE/".$_code_name.".".$_skin_ext['m'];

		$file_content=getFContent($file_src, 1);
		$file_content=getListFContent($file_content, $_code_name);
		if($code_type == "p" || $code_type == "c" || $code_type == 'd'){
			if($ctype == "1") list($data['big'], $data['mid'], $data['small'], $data['depth4'])=explode(",",$cate);
			elseif($ctype == "2") $data['ebig']=str_replace(",", "@", $cate);
			elseif($ctype == "4") list($data['xbig'], $data['xmid'], $data['xsmall'], $data['xdepth4'])=explode(",",$cate);
			elseif($ctype == "5") list($data['ybig'], $data['ymid'], $data['ysmall'], $data['ydepth4'])=explode(",",$cate);
			elseif($ctype == "6") $data['mbig']=str_replace(",", "@", $cate);
		}
	}else{
		$new_code=count($_user_code)+1;
		$_tmp=count($_user_code)+1;
		while($_tmp){
			if(@is_array($_user_code[$_tmp])) $_tmp++;
			else{
				$new_code=$_tmp;
				$_tmp="";
				break;
			}
		}
		if($new_code < 1) $new_code=1;

        if ($code_type == 'is') {
            while(1) { // 편집중 데이터 삭제 및 동시 편집 대응
                $bn = new BannerGroup($type, $new_code);
                $json = $bn->getData();
                if (count($json) == 0) break;
                else {
                    if ($json['creator'] == session_id()) {
                        $bn->removeCode();
                        break;
                    } else {
                        $new_code++;
                    }
                }
            }
        }
	}

	if(!$new_code) $new_code=$user_code;
	if(!$code_type) $code_type="p";
	if(!$page_type) $page_type="p";
	if($product_disable_tr != 'Y') $product_disable_tr = 'N';
	if($pause_type > 1) {
		$pause_type2 = $pause_type;
		$pause_type = 2;
	}
	if (isset($is_datetype) == false) $is_datetype = 'Y';

	// 추가페이지
	$_content_add_info=$_content_cate=array();
	$cont_edit_file=$root_dir."/_config/content_add.php";
	if(file_exists($cont_edit_file)){
		include_once $cont_edit_file;

		foreach($_content_add_info as $val) {
			$content_edit_list['content_content_'.$val['pg_name']] = '추가페이지 : '.$val['name'];
		}
	}

	if ($start_date) $start_date = explode(' ',date('Y-m-d H i', $start_date));
	if ($finish_date) $finish_date = explode(' ',date('Y-m-d H i', $finish_date));

    $_folder_dir = $root_dir.'/_skin/'.$_skin_name.'/img/banner';
    $_folder_url = $root_url.'/_skin/'.$_skin_name.'/img/banner';

	function codeIn(){
		global $_user_code_typec, $code_type;

		$_user_code_in = explode(";", $_user_code_typec[$code_type]);

        $list = '';
        foreach ($_user_code_in as $key=>$val){
            if(!$val) continue;
            list($_hangul, $_val, $_comment) = explode(':', $val);

		    $list .= "<li><a href=\"#\" onclick=\"insertCode('{{\${$_hangul}}}'); return false\">{{\${$_hangul}}}</a></li>";
        }

        return "<div class=\"codeIn\"><ul>$list</ul></div>";
    }

?>
<style type="text/css">
.ucate{width:100px;}
.list_content_tr{display:;}
.list_content_tr_hid{display:none;}
.hidden_layer{
	display:none;
	border: double 3px #aaa;
	padding: 10px 10px 0 10px;
	margin: 10px 0 0 0;
}
.imgTr{width:40px; height:40px;border:5px solid #EAEAEA; background-color:#FFFFFF; font-size:8pt; color:#D5D5D5; text-align:center; line-height:40px;}
textarea{width:100%; font-size:9pt;}
.tbl_row td {padding:10px;}
.tbl_mini th,
.tbl_mini td {text-align:left !important;}
.codeIn ul li {display:inline-block; width:25%; padding: 2px 0; overflow: hidden; white-space:nowrap; }
</style>

<div class="box_title first">
	<h2 class="title">사용자 생성 코드</h2>
</div>
<div class="box_middle">
	<ul class="list_msg left">
		<li><?=editSkinNotice()?></li>
		<li>생성하실 코드의 유형을 먼저 확인하신 후 세부 설정을 해주시기 바랍니다.</li>
	</ul>
</div>
<div class="box_tab" style="margin-top:0">
	<ul>
		<li><a href="#basic" class="active">일반설정</a></li>
		<li><a href="#source">코드설정</a></li>
	</ul>
</div>
<form name="editFrm" action="./?type=<?=$_GET['type']?>" method="post" target="hidden<?=$now?>" onsubmit="printLoading();" enctype="multipart/form-data">
<input type="hidden" name="body" value="design@editor_user.exe">
<input type="hidden" name="user_code" value="<?=$user_code?>">
<input type="hidden" name="new_code" value="<?=$new_code?>">
<input type="hidden" name="type" value="<?=$type?>">
<input type="hidden" name="exec" value="modify">
<input type="hidden" name="img_num">
	<div id="area_basic" class="edit_area">
	<table class="tbl_row" style="width:900px;">
		<caption class="hidden">사용자 생성 코드</caption>
		<colgroup>
			<col style="width:18%">
			<col>
		</colgroup>
		<?php if($code_type == 'is') { ?>
		<tr>
			<td colspan="2"><b>기본 정보</b></td>
		</tr>
		<?php } ?>
		<tr>
			<th><?=($user_code) ? '생성된' : '생성할';?> 코드 유형</th>
			<td>
				<?php if($user_code || $code_type == 'is') { ?>
				<input type="hidden" name="code_type" value="<?=$code_type?>">
				<?=$_user_code_form[$code_type]?>
				<?php } else { ?>
				<select name="code_type" style="width:100px;" onchange="location.href='<?=$PHP_SELF?>?body=<?=$body?>&code_type='+this.value+'&type=<?=$_GET['type']?>';" style="color:#FF0000">
					<?php foreach ($_user_code_form as $key => $val) { ?>
					<option value="<?=$key?>" <?=checked($key, $code_type, 1)?>><?=$val?></option>
					<?php } ?>
				</select>
				<?php } ?>
			</td>
		</tr>
		<?php if($user_code) { ?>
		<tr>
			<th>코드명</th>
			<td>
				<?=userCodeName($user_code, 1)?>
			</td>
		</tr>
		<?php } ?>
		<tr>
			<th>코드 설명</th>
			<td>
				<input type="text" name="code_comment" class="input" maxlength="40" style="width:350px;" value="<?=$code_comment?>">
				<span class="explain">(코드 목록의 설명 부분에 출력됩니다)</span>
			</td>
		</tr>
		<?php if($code_type == 'instagram') { ?>
		<tr>
			<th>출력갯수</th>
			<td>
				<input type="text" name="instagram_cnt" class="input" size="5" value="<?=$instagram_cnt?>">
				<span class="desc1">최대 20개까지 지정가능합니다.</span>
			</td>
		</tr>
		<?php } ?>
		<?php
			if ($code_type == 'p' || $code_type == 'c' || $code_type == 'd') {

				$prd_not_register = 1;
				$w = '';
				include_once $engine_dir."/_manage/product/product_register.inc.php";
				if (!$ctype) $ctype = ($code_type == 'p') ? 2 : 1;
		?>
		<tr>
			<th>카테고리 선택</th>
			<td>
				<table class="tbl_mini full">
					<colgroup>
						<col style="width:25%;">
					</colgroup>
					<?php if($code_type == "p" || $code_type == 'd') { ?>
					<tr>
						<th><input type="radio" name="ctype" value="2" id="ctype2" <?=checked($ctype, 2)?>> <label for="ctype2"><?=$_ctitle[2]?></label></th>
						<td class="left"><?=$ebig_str?></td>
					</tr>
					<?php } ?>
					<tr>
						<th><input type="radio" name="ctype" value="1" id="ctype1" <?=checked($ctype, 1)?>> <label for="ctype1">일반</label></th>
						<td class="left">
							<select name="big" onchange="chgCateInfinite(this, 2, '')" class="ucate">
								<option value="">::대분류::</option>
								<?=$item_1_1?>
							</select>
							<select name="mid" onchange="chgCateInfinite(this, 3, '')" class="ucate">
								<option value="">::중분류::</option>
								<?=$item_1_2?>
							</select>
							<select name="small" onchange="chgCateInfinite(this, 4, '')" class="ucate">
								<option value="">::소분류::</option>
								<?=$item_1_3?>
							</select>
							<?php if ($cfg['max_cate_depth'] >= 4) { ?>
							<select name="depth4" class="ucate">
								<option value="">::세분류::</option>
								<?=$item_1_4?>
							</select>
							<?php } ?>
						</td>
					</tr>
					<?php if($_use['xbig'] == 'Y') { ?>
					<tr>
						<th><input type="radio" name="ctype" value="4" id="ctype4" <?=checked($ctype, 4)?>> <label for="ctype4"><?=$cfg['xbig_name']?></label></th>
						<td class="left">
							<select name="xbig" onchange="chgCateInfinite(this, 2, 'x')" class="ucate">
								<option value="">::대분류::</option>
								<?=$item_4_1?>
							</select>
							<select name="xmid" onchange="chgCateInfinite(this, 3, 'x')" class="ucate">
								<option value="">::중분류::</option>
								<?=$item_4_2?>
							</select>
							<select name="xsmall" onchange="chgCateInfinite(this, 4, 'x')" class="ucate">
								<option value="">::소분류::</option>
								<?=$item_4_3?>
							</select>
							<?php if ($cfg['max_cate_depth'] >= 4) { ?>
							<select name="xdepth4" class="ucate">
								<option value="">::세분류::</option>
								<?=$item_4_4?>
							</select>
							<?php } ?>
						</td>
					</tr>
					<?php } if ($_use['ybig'] == 'Y') { ?>
					<tr>
						<th><input type="radio" name="ctype" value="5" id="ctype5" <?=checked($ctype, 5)?>> <label for="ctype5"><?=$cfg['ybig_name']?></label></th>
						<td class="left">
							<select name="ybig" onchange="chgCateInfinite(this, 2, 'y')" class="ucate">
								<option value="">::대분류::</option>
								<?=$item_5_1?>
							</select>
							<select name="ymid" onchange="chgCateInfinite(this, 3, 'y')" class="ucate">
								<option value="">::중분류::</option>
								<?=$item_5_2?>
							</select>
							<select name="ysmall" onchange="chgCateInfinite(this, 4, 'x')" class="ucate">
								<option value="">::소분류::</option>
								<?=$item_5_3?>
							</select>
							<?php if ($cfg['max_cate_depth'] >= 4) { ?>
							<select name="ydepth4" class="ucate">
								<option value="">::세분류::</option>
								<?=$item_5_4?>
							</select>
							<?php } ?>
						</td>
					</tr>
					<?php } ?>
					<?php if ($code_type == 'p' || $code_type == 'd') {?>
					<tr>
						<th><input type="radio" name="ctype" value="6" id="ctype6" <?=checked($ctype, 6)?>> <label for="ctype6"><?=$_ctitle[6]?></label></th>
						<td class="left"><?=$mbig_str?></td>
					</tr>
					<?php } ?>
				</table>
				<div style="padding-top:10px;"><input type="checkbox" name="use_cate_info" id="use_cate_info" value="Y" <?=checked($use_cate_info, "Y")?>>
				<?php if ($code_type == 'p' || $code_type == 'd') {  ?>
				<label for="use_cate_info">카테고리 정보가 존재할 경우 해당 카테고리와 일치하는 상품 출력</label><br>
				&nbsp; &nbsp; &nbsp;<span class="explain">(예 : 'Dress' 상품리스트 페이지에서 출력될 경우 상단의 설정과 'Dress' 카테고리가 모두 일치하는 상품)</span>
				<?php } else if ($code_type == 'c') { ?>
				<label for="use_cate_info">카테고리 정보가 존재할 경우 해당 카테고리와 일치하는 하위 분류 출력</label><br>
				&nbsp; &nbsp; &nbsp;최대 출력 단계를 <?=$cfg['max_cate_depth']?>차로 하실 경우 자동으로 한단계 낮은 하위 분류를 출력합니다<br>
				&nbsp; &nbsp; &nbsp;<span class="explain">(예 : 'Dress' 1차에서는 해당하는 'Summer' 2차 분류, 'Summer' 2차에서는 해당하는 'mini', 'long' 3차 분류)</span>
                <p>
                    <label><input type="checkbox" name="use_cate_info2" value="Y" <?=checked($use_cate_info2, 'Y')?>> 카테고리 정보가 존재할 경우 해당 카테고리와 부모가 같은 분류를 출력</label>
                    <p style="margin-left: 23px">
                        └<label><input type="checkbox" name="use_cate_info2_child" value="Y" <?=checked($use_cate_info2_child, 'Y')?>> 하위 카테고리가 없을 경우 출력하지 않음</label>
                    </p>
                </p>
				<?php } ?>
				</div>
				<?php if ($code_type == 'c') { ?>
				<div><label><input type="checkbox" name="child_cate_chk" value="Y" <?=checked($child_cate_chk, 'Y')?>> 하위분류가 없을 경우 현재 카테고리 출력</label></div>
				<?php } ?>
			</td>
		</tr>
		<?php
			}
			if ($code_type == 'p') {
				if (!$over_product_img_fd) $over_product_img_fd = $_skin['over_product_img_fd'];
		?>
		<tr>
			<th>정렬 방식</th>
			<td>
				<select name="orderby" style="width:100px;">
					<option value="" <?=checked($orderby, "", 1)?>>기본 정렬</option>
					<option value="`no` desc" <?=checked($orderby, "`no` desc", 1)?>>최신등록일</option>
					<option value="`hit_view` desc" <?=checked($orderby, "`hit_view` desc", 1)?>>조회수</option>
					<option value="`hit_order` desc" <?=checked($orderby, "`hit_order` desc", 1)?>>주문수</option>
				</select>
			</td>
		</tr>
		<tr>
			<th>품절상품 노출</th>
			<td>
				<label><input type="checkbox" name="prd_hide_soldout" value="Y" <?=checked($prd_hide_soldout, 'Y')?>> 노출안함</label>
			</td>
		</tr>
		<tr>
			<th>품절상품 진열</th>
			<td>
				<label><input type="checkbox" name="prd_sort_soldout" value="Y" <?=checked($prd_sort_soldout, 'Y')?>> 리스트 끝으로 보내기</label>
			</td>
		</tr>
		<tr>
			<th>목록 세부 설정</th>
			<td>
			<table class="tbl_mini full">
				<colgroup>
					<col style="width:25%;">
				</colgroup>
				<tr>
					<th>이미지 선택</th>
					<td class="left">
						<select name="product_img_fd" style="width:100px;">
							<option value="">소</option>
							<option value="2" <?=checked($product_img_fd, "2", 1)?>>중</option>
							<option value="1" <?=checked($product_img_fd, "1", 1)?>>대</option>
							<?php
								// 2009-10-23 : 상품 리스트 관련 이미지 필드 선택 - Han
								if ($cfg['add_prd_img'] != '' && $cfg['add_prd_img'] > 3){
									for($jj = 4; $jj<=$cfg['add_prd_img']; $jj++){
										$fd_name = ($cfg['prd_img'.$jj]) ? $cfg["prd_img".$jj] : '추가사진'.$jj;
							?>
							<option value="<?=$jj?>" <?=checked($product_img_fd, $jj, 1)?>><?=$fd_name?></option>
							<?php
									}
								}
							?>
						</select>
					</td>
				</tr>
				<tr>
					<th>롤오버이미지 선택</th>
					<td class="left">
						<select name="over_product_img_fd" style="width:100px;">
							<option value="N" <?=checked($over_product_img_fd, "N", 1)?>>사용안함</option>
							<option value="3" <?=checked($over_product_img_fd, "3", 1)?>>소</option>
							<option value="2" <?=checked($over_product_img_fd, "2", 1)?>>중</option>
							<option value="1" <?=checked($over_product_img_fd, "1", 1)?>>대</option>
							<?php
								if($cfg['add_prd_img'] != '' && $cfg['add_prd_img'] > 3){
									for ($jj = 4; $jj <= $cfg['add_prd_img']; $jj++){
										$fd_name = ($cfg['prd_img'.$jj]) ? $cfg['prd_img'.$jj] : '추가사진'.$jj;
							?>
							<option value="<?=$jj?>" <?=checked($over_product_img_fd, $jj, 1)?>><?=$fd_name?></option>
							<?php
									}
								}
							?>
						</select>
					</td>
				</tr>
				<?php
					$_list_vals = array('product_list_imgw'=>'상품이미지 가로', 'product_list_imgh'=>'상품이미지 세로', 'product_list_cols'=>'한줄상품수', 'product_list_rows'=>'한페이지줄수', 'product_list_maxcnt'=>'최대 상품수', 'product_list_namecut'=>'글자수 제한');
					$_list_comment = array('product_list_imgw'=>'픽셀', 'product_list_imgh'=>'픽셀', 'product_list_cols'=>'상품 테이블(필수 형식)의 각 줄마다 상품수가 다를 경우 \'/\' 로 구분 예) 2/3/4', 'product_list_maxcnt'=>'미입력 시 무제한');
					foreach ($_list_vals as $key => $val){
				?>
				<tr>
					<th><?=$val?></th>
					<td class="left"><input type="text" name="<?=$key?>" style="width:100px;" class="input" value="<?=${$key}?>" onkeypress="onlyNumber(this);">
					<span class="p_color3"><?=$_list_comment[$key]?></span></td>
				</tr>
				<?php } ?>
				<tr>
					<th>페이징 사용</th>
					<td class="left"><input type="checkbox" name="paging_use" id="paging_use" value="Y" <?=checked($paging_use, "Y")?>> <label for="paging_use">사용</label> <span class="p_color3">페이지 선택 기능 사용시 <a class="clipboard p_cursor" data-clipboard-text="{{$페이지선택(<?=$new_code?>)}}">{{$페이지선택(<?=$new_code?>)}}</a> 코드를 원하시는 위치에 삽입해주시기 바랍니다</span></td>
				</tr>
				<tr>
					<th>자동 &lt;tr&gt;태그 사용안함</th>
					<td class="left">
						<input type='radio' name='product_disable_tr' value='Y' <?=checked($product_disable_tr,'Y')?>> 예
						<input type='radio' name='product_disable_tr' value='N' <?=checked($product_disable_tr,'N')?>> 아니오
					</td>
				</tr>
			</table>
			</td>
		</tr>
		<?php
			} else if($code_type == 'c') {
				$prd_not_register=1;
				include_once $engine_dir.'/_manage/product/product_register.inc.php';
				if (!$cate_type) $cate_type = 'text';
		?>
		<tr>
			<th>출력 단계</th>
			<td>
				<select name="min_category" style="width:100px;">
					<?php for ($i = 1; $i <= $cfg['max_cate_depth']; $i++) { ?>
					<option value="<?=$i?>" <?=checked($min_category, $i, 1)?>><?=$i?>차</option>
					<?php } ?>
				</select>
                ~
				<select name="max_category" style="width:100px;">
					<?php for($i = 1; $i <= $cfg['max_cate_depth']; $i++) { ?>
					<option value="<?=$i?>" <?=checked($max_category, $i, 1)?>><?=$i?>차</option>
					<?php } ?>
				</select>
			</td>
		</tr>
		<tr>
			<th>카테고리 출력 유형</th>
			<td>
				<input type="radio" name="cate_type" value="img" id="cate_type1" <?=checked($cate_type, 'img')?>>
				<label for="cate_type1">이미지</label> <span class="explain">(이미지 유형의 경우 상품 이미지 폴더내에 카테고리 코드로 해당이미지 저장)</span>
				<br>
				<input type="radio" name="cate_type" value="text" id="cate_type2" <?=checked($cate_type, 'text')?>>
				<label for="cate_type2">텍스트</label>
			</td>
		</tr>
		<tr>
			<th>연결 문자 삽입</th>
			<td>
				<input type="text" name="cate_joint" class="input" maxlength="40" style="width:100px;" value="<?=htmlspecialchars($cate_joint)?>">
				<span class="explain">(문자 사이에 출력을 원할 경우에 입력 예 : Top <span class='p_color3'>|</span> Bottom)</span>
			</td>
		</tr>
		<?php
			} else if ($code_type == 'b' || $code_type == 'bs') {
		?>
		<tr>
			<th>게시판 선택</th>
			<td>
				<select name="board_name" style="width:100px;" onchange="changeBoardType(this);">
					<option value="prd:review" <?=checked($board_name, "prd:review", 1)?>>상품 후기</option>
					<option value="prd:qna" <?=checked($board_name, "prd:qna", 1)?>>상품 Q&A</option>
					<?php
						$bsql = $pdo->iterator("select `db`,`title` from `mari_config`");
                        foreach ($bsql as $barr) {
					?>
					<option value="<?=$barr['db']?>" <?=checked($board_name, $barr['db'], 1)?>><?=$barr['title']?></option>
					<?php
						}
					?>
				</select>
				<span id="board_cate_view" style="display:none;">
				</span>
			</td>
		</tr>
		<?php if ($code_type == 'b') { ?>
		<tr>
			<th>출력 형식</th>
			<td>
				<select name="board_type" style="width:100px;">dd
					<option value="text" <?=checked($board_type, "text", 1)?>>일반</option>
					<option value="img" <?=checked($board_type, "img", 1)?>>갤러리</option>d
				</select><span class="explain"> (갤러리 형식의 경우 첨부파일 게시물에 한하여 출력되며 '상품 Q&A' 게시물에는 적용되지 않습니다)</span>
			</td>
		</tr>
		<tr>
			<th>게시물 속성 선택</th>
			<td>
				<select name="board_is_notice" style="width:100px;">
					<option value='' <?=checked($board_is_notice, '', 1)?>>전체</option>
					<option value='Y' <?=checked($board_is_notice, 'Y', 1)?>>공지게시물</option>
					<option value='N' <?=checked($board_is_notice, 'N', 1)?>>일반게시물</option>
					<option class="review_opt" value='B' <?=checked($board_is_notice, 'B', 1)?>>베스트게시물</option>
					<option class="review_opt" value='I' <?=checked($board_is_notice, 'I', 1)?>>포토후기</option>
					<option class="review_opt" value='I2' <?=checked($board_is_notice, 'I2', 1)?>>상품별 포토후기</option>
				</select>
			</td>
		</tr>
		<tr>
			<th>정렬 방식</th>
			<td>
				<select name="orderby" style="width:100px;">
					<option value="`no` desc" <?=checked($orderby, "`no` desc", 1)?>>최신등록일</option>
					<option value="`hit` desc" <?=checked($orderby, "`hit` desc", 1)?>>조회수</option>
					<option class="review_opt" value="recommend_y desc, reg_date desc" <?=checked($orderby, "recommend_y desc, recommend_N asc, reg_date desc", 1)?>>추천순</option>
					<option class="review_opt" value="rev_pt desc" <?=checked($orderby, "rev_pt desc", 1)?>>평점 높은순</option>
					<option class="review_opt" value="rev_pt asc" <?=checked($orderby, "rev_pt asc", 1)?>>평점 낮은순</option>
				</select>
			</td>
		</tr>
		<tr>
			<th>목록 세부 설정</th>
			<td>
				<table class="tbl_mini full">
					<colgroup>
						<col style="width:25%;">
					</colgroup>
					<?php
						$_list_vals = array('board_list_total'=>'총 출력 게시물 수', 'board_list_titlecut'=>'제목 줄임', 'board_list_contentcut'=>'내용 줄임', 'board_list_imgw'=>'이미지 가로 사이즈', 'board_list_imgh'=>'이미지 세로 사이즈');
						foreach ($_list_vals as $key => $val){
					?>
					<tr>
						<th><?=$val?></th>
						<td class="left"><input type="text" name="<?=$key?>" style="width:100px;" class="input" value="<?=${$key}?>"></td>
					</tr>
					<?php
						}
					?>
				</table>
			</td>
		</tr>
		<?php } ?>
		<?php if ($_code_type == 'bs') { ?>
		<tr>
			<th>검색항목</th>
			<td>
				<label><input type="radio" name="search_column" value="3" <?=checked($search_column, '3')?> checked> 제목</label>
				<label><input type="radio" name="search_column" value="4" <?=checked($search_column, '4')?>> 내용</label>
			</td>
		</tr>
		<?php }
		}
		if ($code_type == 'd') {
			if (!$htype) $htype = 1;
		?>
		<tr>
			<th></th>
			<td>
				선택 분류의
				<select name='orderby'>
					<option value='6' <?=checked($orderby, '6', 1)?>>기본정렬</option>
					<option value='1' <?=checked($orderby, '1', 1)?>>최신수정 상품</option>
					<option value='2' <?=checked($orderby, '2', 1)?>>최신등록 상품</option>
					<option value='3' <?=checked($orderby, '4', 1)?>>조회수</option>
					<option value='3' <?=checked($orderby, '5', 1)?>>주문수</option>
					<option value='3' <?=checked($orderby, '3', 1)?>>랜덤상품</option>
				</select> 을 프리뷰에 기본으로 출력합니다.
			</td>
		</tr>
		<tr>
			<th>가로길이</th>
			<td>
				<input type='text' name='width' class='input numberOnly' size='10' value='<?=$width?>'>
				<span class="explain">px</span>
				<span class="desc1">(모바일에서는 100% 로 출력됩니다.)</span>
			</td>
		</tr>
		<tr>
			<th>세로길이</th>
			<td>
				<input type='text' name='height' class='input numberOnly' size='10' value='<?=$height?>'>
				<span class="explain">px</span>

				<ul>
					<li><Input type='radio' name='htype' value='1' <?=checked($htype, '1')?>> 내용에 따라 세로 길이를 자동으로 리사이즈 합니다.</li>
					<li><Input type='radio' name='htype' value='2' <?=checked($htype, '2')?>> 내용이 세로길이를 초과하면 스크롤바를 만듭니다.</li>
				</ul>

			</td>
		</tr>
		<tr>
			<td colspan='2'>
				<ul class='list_info'>
					<li>한페이지에 프리뷰를 한개만 사용하실 경우 다음과 같이 사용하시면 자동으로 링크됩니다. &lt;a href='{{$상품링크(프리뷰)}}'&gt;이미지&lt;/a&gt;</li>
					<li>두개 이상 사용하실 경우 프리뷰 코드를 추가하셔야 합니다.</li>
					<li>예) &lt;a href='{{$상품링크(프리뷰)}}' frameno='<?=$user_code?>' &gt;이미지&lt;/a&gt;</li>
				</ul>
			</td>
		</tr>
		<?php } ?>
		<?php if ($code_type == 'i') { ?>
		<tr>
			<th>자동 스크롤 설정</th>
			<td style="padding-top:5px; padding-bottom:5px;">
				<span class="explain">자바 스크립트를 이용하여 <u>일정 방향과 규칙으로 움직이는 목록</u>으로 설정을 원하실 경우 사용하여 주시기 바랍니다.</span><br>
				<input type="checkbox" name="auto_scroll" id="auto_scroll" value="Y" <?=checked($auto_scroll, "Y")?> onclick="autoScroll(this);"> <label for="auto_scroll">자동 스크롤 사용함</label>
				<div id="auto_scroll_detail" class="" class='register'>
				<table class="tbl_mini" cellspacing='0' cellpadding='0' style="width:100%;">
					<caption class="hidden">스크롤 세부 설정</caption>
					<tr>
						<th>효과 선택</th>
						<td class="left">
						<select name="style_filter" onchange="filterSelect();">
							<option value="">================= 스크립트 효과 선택 =================</option>
							<option value="scroll" <?=checked($style_filter, "scroll", 1)?>>기본 롤링 (일정방향으로 멈춤없이 흐름)</option>
							<option value="rollv2" <?=checked($style_filter, "rollv2", 1)?>>확장 롤링 (지정 방향으로 롤링)</option>
							<option value="escroll" <?=checked($style_filter, "escroll", 1)?>>일정한 시간/간격 스크롤 (일정방향으로 멈춤, 흐름 반복)</option>
							<option value="revealtrans_22" <?=checked($style_filter, "revealtrans_22", 1)?>>터치 슬라이드 [모바일 전용]</option>
						</select>
						</td>
					</tr>
					<tr>
						<th>박스 사이즈</th>
						<td class="left">
						<input type="text" name="scroll_box_w" size="5" maxlength="5" class="input" value="<?=$scroll_box_w?>" onkeypress="onlyNumber();"> px &nbsp; X &nbsp;
						<input type="text" name="scroll_box_h" size="5" maxlength="5" class="input" value="<?=$scroll_box_h?>" onkeypress="onlyNumber();"> px
						</td>
					</tr>
					<tr>
						<th>스크롤 속도</th>
						<td class="left">
							<select name='scroll_speed'>
								<option value='1' <?=checked($scroll_speed, 1, 1)?>>1</option>
								<option value='2' <?=checked($scroll_speed, 2, 1)?>>2</option>
								<option value='3' <?=checked($scroll_speed, 3, 1)?>>3</option>
								<option value='4' <?=checked($scroll_speed, 4, 1)?>>4</option>
								<option value='5' <?=checked($scroll_speed, 5, 1)?>>5</option>
								<option value='10' <?=checked($scroll_speed, 10	, 1)?>>10</option>
								<option value='20' <?=checked($scroll_speed, 20	, 1)?>>20</option>
							</select>
							<span class="explain">스크롤 속도는 상대적인 값이며, PC속도와 브라우저 성능에 따라 크게 달라질 수 있습니다.</span>
						</td>
					</tr>
					<tr id="scroll_direction_tr" class='ascroll_trs'>
						<th>스크롤 방향</th>
						<td class="left">
						<label><input type="radio" name="scroll_direction" value="1" <?=checked($scroll_direction, 1)?>> 상</label> &nbsp;
						<label><input type="radio" name="scroll_direction" value="2" <?=checked($scroll_direction, 2)?>> 하</label> &nbsp;
						<label><input type="radio" name="scroll_direction" value="3" <?=checked($scroll_direction, 3)?>> 좌</label> &nbsp;
						<label><input type="radio" name="scroll_direction" value="4" <?=checked($scroll_direction, 4)?>> 우</label>
						<br>
						<span class="explain">
							* 방향 변경 링크 :
							&lt;a href="<a class="clipboard p_cursor" data-clipboard-text="{{$스크롤역방향<?=$new_code?>}}"><u>{{$스크롤역방향<?=$new_code?>}}</u></a>"&gt;
							&lt;a href="<a class="clipboard p_cursor" data-clipboard-text="{{$스크롤기본방향<?=$new_code?>}}"><u>{{$스크롤기본방향<?=$new_code?>}}</u></a>"&gt;
							<br>
							* 정지/재개 링크 :
							&lt;a href="<a class="clipboard p_cursor" data-clipboard-text="{{$자동스크롤멈춤<?=$new_code?>}}"><u>{{$자동스크롤멈춤<?=$new_code?>}}</u></a>"&gt;
							&lt;a href="<a class="clipboard p_cursor" data-clipboard-text="{{$자동스크롤시작<?=$new_code?>}}"><u>{{$자동스크롤시작<?=$new_code?>}}</u></a>"&gt;
							&lt;a href="<a class="clipboard p_cursor" data-clipboard-text="{{$자동스크롤토글<?=$new_code?>}}"><u>{{$자동스크롤토글<?=$new_code?>}}</u></a>"&gt;
						</span>
						</td>
					</tr>
					<tr id="scroll_time_tr" class='ascroll_trs'>
						<th>진행 간격 시간</th>
						<td class="left">
						<input type="text" name="scroll_time" size="5" maxlength="5" value="<?=$scroll_time?>" onkeypress="onlyNumber();"> 초
						</td>
					</tr>
					<tr id="board_line_tr" class='ascroll_trs'>
						<th>박스 라인 수</th>
						<td class="left">
						<input type="text" name="board_line" size="5" maxlength="5" value="<?=$board_line?>" onkeypress="onlyNumber();">
						</td>
					</tr>
					<tr id="duration_tr" class='ascroll_trs'>
						<th>효과 진행 시간</th>
						<td class="left">
						<input type="text" name="duration" size="5" maxlength="5" value="<?=$duration?>" onkeypress="onlyNumber();"> 초 (효과 진행 시간이 진행 간격 시간보다 길 경우 효과가 부드럽지 않습니다)
						</td>
					</tr>
					<tr id="autostart_tr" class='ascroll_trs'>
						<th>자동시작</th>
						<td class="left">
							<input type='radio' name='scauto_start' value='Y' <?=checked($scauto_start, 'Y')?> checked> 자동시작
							<input type='radio' name='scauto_start' value='N' <?=checked($scauto_start, 'N')?>> 수동시작(역방향/기본방향 클릭시 이동)
						</td>
					</tr>
					<tr id="pause_tr" class='ascroll_trs'>
						<th>대기옵션</th>
						<td class="left">
							<input type='radio' name='pause_type' value='' <?=checked($pause_type, '')?>> 대기없음
							<input type='radio' name='pause_type' value='1' <?=checked($pause_type, '1')?>> 상품(게시물)마다 대기
							<input type='radio' name='pause_type' value='2' <?=checked($pause_type, '2')?>>
							<input type="text" name="pause_type2" value='<?=$pause_type2?>' class='input' size='3'>
							개씩 이동
							<p>대기시간 <input type='text' name='pause_time' value='<?=$pause_time?>' class='input' size='3'> 초</p>
						</td>
					</tr>
				</table>
				</div>
			</td>
		</tr>
		<tr>
			<th>이미지 목록</th>
			<td style="padding-top:5px; padding-bottom:5px;">
				<ul class="list_msg">
					<li>저장된 이미지의 개수만큼 출력되므로 사용하지 않을 이미지는 비우거나 삭제해주시기 바랍니다.</li>
					<li>플래시 XML 연동 기능은 Over 이미지 기능이 적용되지 않으므로 일반 형식을 사용해주시기 바랍니다.</li>
				</ul>
				<table class="tbl_mini full">
					<tr>
						<td colspan="5" class="left">최대 이미지 출력 개수 :
							<select name="img_sum" onchange="location.href='./?body=<?=$body?>&code_type=<?=$code_type?>&user_code=<?=$user_code?>&img_sum='+this.value;">
								<?php
									// 2010-03-05 : 이미지 개수 설정 - Han
									$img_sum=($img_sum) ? $img_sum : $_user_img_default_number;
									$img_sum=$_GET['img_sum'] ? $_GET['img_sum'] : $img_sum;
									$img_sum=($img_sum > $_user_img_number) ? $_user_img_number : $img_sum;
									for($ii=$_user_img_default_number; $ii<=$_user_img_number; $ii=$ii+5){
								?>
								<option value="<?=$ii?>" <?=checked($img_sum, $ii, 1)?>><?=$ii?></option>
								<?php
									}
								?>
							</select>
							<span class="p_color3">(자동 스크롤, 플래시 XML 연동 사용 시)</span>
						</td>
					</tr>
					<?php
						if ($image_link) $_image_link=ucodeImageLink(1, $image_link);
						for ($ii=0; $ii<$img_sum; $ii++){
							$_tr_id=($auto_scroll == "Y" && $flash_xml_use == "Y") ? "list_content_tr_hid" : "list_content_tr";
							// 저장 이름을 미리 정함
							$_ucode_img_name="ucode_".$new_code."_".$ii;
							$_ucode_img_name2="ucode_".$new_code."_".$ii."r";
							$_uimg=titleIMGName($_ucode_img_name, "banner");
							$_uimg2=titleIMGName($_ucode_img_name2, "banner");
							if(!$_uimg) $_uimg=$_ucode_img_name.".jpg";
							if(!$_uimg2) $_uimg2=$_ucode_img_name2.".jpg";
							$file=$_folder_dir."/".$_uimg;
							$file2=$_folder_dir."/".$_uimg2;
							$_ori_img=(@is_file($file)) ? "<a href=\"".$_folder_url."/".$_uimg."\" target=\"_blank\"><img src=\"".$_folder_url."/".$_uimg."\" width=\"40\" height=\"40\"></a>" : "";
							$_ori_img2=(@is_file($file2)) ? "<a href=\"".$_folder_url."/".$_uimg2."\" target=\"_blank\"><img src=\"".$_folder_url."/".$_uimg2."\" width=\"40\" height=\"40\"></a>" : "";
							$_del_img=($_ori_img || $_ori_img2) ? 1 : 0;
							$_ori_img=$_ori_img ? $_ori_img : "NO";
							$_ori_img2=$_ori_img2 ? $_ori_img2 : "NO";
							if($_image_link[$ii]){
								$_target=ucodeImageLink("", $_image_link[$ii], 2);
								$_link=ucodeImageLink("", $_image_link[$ii], 4);
							}
					?>
					<input type="hidden" name="ori_img[<?=$ii?>]" value="<?=$file?>">
					<input type="hidden" name="ori_img[<?=$ii+100?>]" value="<?=$file2?>">
					<tr <?php if ($ii != 0) { ?> class="list_content_tr_img <?=$_tr_id?>"<?php } ?> valign="top">
						<td width="25" style="padding-top:15px;"><?=$ii+1?>. </td>
						<td width="55" align="center"><div class="imgTr"><?=$_ori_img?></div><span class="desc1">일반</span></td>
						<td width="55" align="center"><div class="imgTr"><?=$_ori_img2?></div><span class="desc1">Over</span></td>
						<td width="20" style="padding-top:25px;">
							<?php
								if($_del_img){
							?>
							<span class='rBtn gray small'>
								<input type="button" value="삭제" onClick="delUserImage('<?=$ii?>');">
							</span>
							<?php
								} else echo "&nbsp;";
							?>
						</td>
						<td>
						<table cellpadding="0" cellspacing="0" class="tbl_mini full">
							<tr>
								<td width="30">일반</td>
								<td class="left"><input type="file" name="replace_img[<?=$ii?>]" style="width:330px;" class="input"> <span class="explain">(500KB 제한)</span></td>
							</tr>
							<tr>
								<td>Over</td>
								<td class="left"><input type="file" name="replace_img[<?=$ii+100?>]" style="width:330px;" class="input"> <span class="explain">(500KB 제한)</span></td>
							</tr>
							<tr>
								<td>링크</td>
								<td class="left">
									<select name="image_target[<?=$ii?>]">
										<option value="" <?=checked($_target,"",1)?>>같은 창</option>
										<option value="_blank" <?=checked($_target,"_blank",1)?>>새창</option>
										<option value="_parent" <?=checked($_target,"_parent",1)?>>부모</option>
										<option value="_top" <?=checked($_target,"_top",1)?>>최상</option>
									</select> <input type="text" name="image_link[<?=$ii?>]" style="width:260px;" class="input" value="<?=$_link?>"> <span class="explain">(http:// 포함)</span>
								</td>
							</tr>
						</table>
						</td>
					</tr>
					<?php } ?>
				</table>
			</td>
		</tr>
        <?php } ?>
		<?php if ($code_type == 'is') { ?>
		<tr>
			<th rowspan="2">기간</th>
			<td>
				<label><input type="checkbox" name="is_datetype" value="Y" <?=checked($is_datetype, 'Y')?>> 무제한</label>
			</td>
		</tr>
		<tr>
			<td>
				<input type="text" name="start_date_day" value="<?=$start_date[0]?>" size="10" readonly class="input datepicker">
				<?=dateSelectBox(0,23,"start_date_h",$start_date[1])?> 시
				<?=dateSelectBox(0,59,"start_date_m",$start_date[2])?> 분 ~
				<input type="text" name="finish_date_day" value="<?=$finish_date[0]?>" size="10" readonly class="input datepicker">
				<?=dateSelectBox(0,23,"finish_date_h",$finish_date[1])?> 시
				<?=dateSelectBox(0,59,"finish_date_m",$finish_date[2])?> 분
			</td>
		</tr>
		<tr>
			<th>사용여부</th>
			<td>
				<label><input type="radio" name="use_yn" value="Y" <?=checked($use_yn, 'Y')?>> 사용함</label>
				<label><input type="radio" name="use_yn" value="N" <?=checked(($use_yn != 'Y' ? 'N' : 'Y'), 'N')?>> 사용안함</label>
			</td>
		</tr>
		</table>
		<?PHP require 'editor_group_banner.frm.php'?>
		<?php } ?>
	</table>
	</div>

	<div id="area_source" class="edit_area" style="display:none;">
	<table class="tbl_row" style="width:900px;">
		<colgroup>
			<col style="width:18%">
			<col>
		</colgroup>
		<?php
			if ($code_type == "n" || $code_type == 'bs') {
				include_once $engine_dir."/_manage/design/editor_page.inc.php";
				$app_page = ($_GET['app_page']) ? $_GET['app_page'] : "common";
		?>
		<tr>
			<th>편집 페이지 선택</th>
			<td style="padding-top:5px;">
				<span class="p_color3">페이지별 디자인을 개별적으로 편집하실 수 있습니다.</span><br>
				<div style="width:100%; height:200px; overflow-y:auto; border:1px solid #D0D0D0; background-color:#FFFFFF; padding:5px;">
				<label style="text-decoration:none;"><input type="radio" name="app_page" value="common" onclick="location.href='./?body=<?=$body?>&user_code=<?=$user_code?>&type=<?=$type?>&code_type=<?=$code_type?>&app_page='+this.value+'#source';" <?=checked($app_page, "common")?>> 공통</label>
				<?php
					$ii=1;
					foreach ($_edit_list as $key=>$val){
				?>
				<hr style="border: 1px solid #EBEBEB;">
				<?php
					foreach ($_edit_list[$key] as $key2=>$val2){
						// 정상적인 편집페이지가 아닐 경우 패스
						if (preg_match("/^\.\/\?body=/", $key2)) continue;
						if (in_array($key2, $_idvy_not_used_list) && !preg_match("/member_join_frm|shop_detail|coordi_coordi_view/", $key2)) continue;
						$_pg_val=str_replace(".".$_skin_ext['p'], ".php", $key2);
						$_pg_val=str_replace("/", "_", $_pg_val);
						$_pg_val=str_replace("board_", "board_index.php", $_pg_val);
						$_pg_val=@preg_replace("/\?.*=(.*)/", ":$1", $_pg_val);
						if ($_pg_val != 'content_customer.php') $_pg_val=@preg_replace("/(^content).(.*)(\.php)$/", "content_content.php:$2", $_pg_val);
						$_this_ce=(@strpos(".".$file_content, "<!-- wstart:".$_pg_val)) ? 1 : 0;
						if ($app_page == $_pg_val) $_ck_num=$ii;
				?>
				<label style="text-decoration:none;"><input type="radio" name="app_page" value="<?=urlencode($_pg_val)?>" onclick="location.href='./?body=<?=$body?>&user_code=<?=$user_code?>&type=<?=$type?>&code_type=<?=$code_type?>&app_page='+this.value+'#source';"<?=checked($_ck_num, $ii)?>> <?=$val2?></label>
				<?=$_this_ce ? "<span class=\"p_color2\">편집완료</span>" : "";?>
				<br>
				<?php
							$ii++;
						}
					}

					$file_content=getPageAppContent($app_page, $file_content);
					if ($_ck_num) {
				?>
				<script type="text/javascript">
					document.editFrm.app_page[<?=$_ck_num?>].focus();
				</script>
				<?php
					}
				?>
				</div>
			</td>
		</tr>
		<?php } ?>
		<?PHP
		if($code_type != 'n' && $code_type != 'bs' && $code_type != 'd') {
			if($code_type != "i" || $style_filter == 'rollv2') {
		?>
		<tr class="list_content_tr_scroll <?=($auto_scroll == "Y") ? "list_content_tr" : "list_content_tr_hid";?>">
			<th>스크롤 구문</th>
			<td>
			<?php
				if(!$scroll_content){
					if($code_type == "p"){
						$scroll_content="<div style=\"padding:0px;\">{{\$상품이미지(링크포함)}}</div>";
					}elseif($code_type == "c"){
						$scroll_content="<div>{{\$분류이미지(링크포함)}}</div>";
					}elseif($code_type == "b"){
						$scroll_content="<div style=\"width:70px; display:inline;\">{{\$글작성일}}</div><div style=\"width:200px; display:inline;\">{{\$글제목(링크포함)}}</div>";
					}
				}
			?>
				<textarea name="scroll_content" id="scroll_content" style="height:100px;" class="txta" onkeydown="editorKeyUp(this);"><?=htmlspecialchars($scroll_content)?></textarea>
				<?=codeIn(1)?>
			</td>
		</tr>
		<tr class="list_content_tr_common <?=($auto_scroll == "Y") ? "list_content_tr" : "list_content_tr";?>">
			<th>반복문 상단</th>
			<td>
				<textarea name="list_content[1]" id='list_content1' style="height:130px; width:100%;" class="txta" onkeydown="editorKeyUp(this);"><?=htmlspecialchars($file_content[1])?></textarea>
			</td>
		</tr>
		<tr class="list_content_tr_common <?=($auto_scroll == "Y") ? "list_content_tr_hid" : "list_content_tr";?>">
			<th>반복문 구문</th>
			<td>
				<textarea name="list_content[2]" id="edt_content" style="height:160px;; width:100%;" class="txta" onkeydown="editorKeyUp(this);"><?=htmlspecialchars($file_content[2])?></textarea>
				<?=codeIn()?>
			</td>
		</tr>
		<tr class="list_content_tr_common <?=($auto_scroll == "Y") ? "list_content_tr_hid" : "list_content_tr";?>">
			<th>반복문 하단</th>
			<td>
				<textarea name="list_content[3]" id='list_content3' style="height:130px;; width:100%;" class="txta" onkeydown="editorKeyUp(this);"><?=htmlspecialchars($file_content[3])?></textarea>
			</td>
		</tr>
		<tr class="list_content_tr_common <?=($auto_scroll == "Y") ? "list_content_tr_hid" : "list_content_tr";?>">
			<th>데이터 없음</th>
			<td>
				<textarea name="list_content[4]" id='list_content4' style="height:160px;; width:100%;" class="txta" onkeydown="editorKeyUp(this);"><?=htmlspecialchars($file_content[4])?></textarea>
			</td>
		</tr>
		<?php
				}
			}
		?>
		<?php if($code_type == 'n' || $code_type == 'bs') { ?>
		<tr>
			<th>HTML 구문</th>
			<td>
				<textarea name="list_content" id="edt_content" style="width:100%; height:300px; font-size:9pt;" class="txta" onkeydown="editorKeyUp(this);"><?=@htmlspecialchars($file_content)?></textarea>
				<?=codeIn(1)?>
			</td>
		</tr>
		<?php } ?>
		<?php if($code_type != 'is') { ?>
		<tr>
			<th>출력 분류 조건</th>
			<td>
				<input type="text" name="pr_ebig" value="<?=$pr_ebig?>" class="input input_full">
				<label><input type="checkbox" name="pr_ck_depth" value="Y" <?=checked($pr_ck_depth, 'Y')?>> 하위분류 적용</label>
				<ul class="list_msg">
					<li>현재 사용자 모듈이 출력될 카테고리 코드 조건을 입력해 주세요.</li>
					<li>여러개일 경우 콤마로 구분 가능합니다.</li>
				</ul>
			</td>
		</tr>
		<?php } ?>
		<tr>
			<th>출력 페이지 설정</th>
			<td style="padding-top:5px;">
				<input type="radio" name="page_type" value="a" id="page_type1" <?=checked($page_type, "a")?>>
				<label for="page_type1" class="p_cursor">모든페이지 출력</label>
				<input type="radio" name="page_type" value="p" id="page_type2" <?=checked($page_type, "p")?>>
				<label for="page_type2" class="p_cursor">일부페이지 출력</label>
				<input type="checkbox" name="all_page_chk" id="all_page_chk" onclick="checkAll(document.editFrm['page_list[]'], this.checked);"><label for="all_page_chk" class="p_cursor">전체 페이지 선택</label>
				<div style="width:100%; height:200px; overflow-y:auto; border:1px solid #D0D0D0; background-color:#FFFFFF;">
				<?PHP
					if(is_array($content_edit_list)) {
						$_edit_list['추가페이지'] = $content_edit_list;
					}

					$ii=0;
					foreach($_edit_list as $key=>$val){
						foreach($_edit_list[$key] as $key2=>$val2){
							// 정상적인 편집페이지가 아닐 경우 패스
							if(!preg_match("/\.{$_skin_ext['p']}$/", $key2) && strpos($key2, 'content_content_') === false) continue;
							$_ori_pg=oriPageUrl($key2);
				?>
				<input type="checkbox" name="page_list[]" value="<?=$key2?>" id="page_list<?=$ii?>"<?=(@strchr($page_list, "@".$key2."@")) ? " checked" : ""?>> <label for="page_list<?=$ii?>"><?=$val2?></label>
				<span class="p_color3">(<?=$_ori_pg?>)</span><br>
				<?php
							$ii++;
						}
					}
				?>
				<input type="checkbox" name="page_list[]" value="content_content.<?=$_skin_ext['p']?>" id="page_list<?=$ii?>"<?=(@strchr($page_list, "@content_content.".$_skin_ext['p']."@")) ? " checked" : ""?>> <label for="page_list<?=$ii?>">추가페이지 전체</label>
				<span class="p_color3">(<?=oriPageUrl("content_content.".$_skin_ext['p'])?>)</span><br>
				</div>
			</td>
		</tr>
	</table>
	</div>
	<div class="pop_bottom">
		<span class="box_btn blue"><input type="submit" value="저장하기"></span>
		<?php if ($user_code) { ?>
		<span class="box_btn gray"><input type="button" value="삭제" onclick="ucodeDel();"></span>
		<?php } ?>
		<span class="box_btn gray"><input type="button" value="창닫기" onclick="window.close();"></span>
	</div>
</form>
<form name="popFrm" action="./pop.php" method="get">
<input type="hidden" name="body" value="">
</form>
<script type="text/javascript">
	function insertCode(code){
		var fr;
		if(document.getElementById('frame_edt_content2')) {
			fr = document.getElementById('frame_edt_content2').contentWindow;
		} else {
			fr = document.getElementById('frame_edt_content').contentWindow;
		}

		var textarea = fr.document.getElementById('textarea');
		var nextfocus = textarea.selectionEnd + code.length;
		textarea.focus();
		fr.editArea.textareaFocused = true;
		textarea.value = textarea.value.substr(0, textarea.selectionStart)+code+textarea.value.substr(textarea.selectionEnd);

		if(textarea.setSelectionRange) {
			textarea.focus();
			textarea.setSelectionRange(nextfocus, nextfocus);
		} else if(textarea.createTextRange) {
			var range = textarea.createTextRange();
			range.collapse(true);
			range.moveEnd('character', nextfocus);
			range.moveStart('character', nextfocus);
			range.select();
		}
	}
	function userCodeEx(f){
		var Ex=Array();
		var Table=Array();
    	<?php if ($code_type == 'p') { ?>
		Table[1]='<table border="0" width="100%" cellpadding="0" cellspacing="0">\n\
		<tr>';
		Table[2]='	</tr>\n\
	</table>';
		Ex[1]='		<td valign="top" align="center">{{$기본상품박스}}</td>';
		Ex[2]='		<td align="center">\n\
			<table border="0" width="100%" cellpadding="0" cellspacing="0">\n\
				<tr>\n\
					<td><a href="{{$상품링크}}">{{$상품이미지}}</a></td>\n\
				</tr>\n\
				<tr>\n\
					<td><a href="{{$상품링크}}">{{$상품명}}</a></td>\n\
				</tr>\n\
			</table>\n\
			</td>';
		Ex[3]='		<td align="center">\n\
			<table border="0" width="{{$상품가로사이즈}}" cellpadding="0" cellspacing="0">\n\
				<tr>\n\
					<td><a href="{{$상품링크}}">{{$상품이미지}}</a></td>\n\
				</tr>\n\
				<tr>\n\
					<td><a href="{{$상품링크}}">{{$상품명}}</a> {{$상품아이콘}}</td>\n\
				</tr>\n\
				<tr>\n\
					<td><B>{{$상품가격}}</B></td>\n\
				</tr>\n\
			</table>\n\
			</td>';
	    <?php } else if ($code_type == "c") { ?>
		Table[1]='<table border="0" width="100%" cellpadding="0" cellspacing="0">';
		Table[2]='</table>';
		Ex[1]='	<tr>\n\
			<td><a href="{{$분류링크}}">{{$분류이미지}}</a></td>\n\
		</tr>';
		Ex[2]='	<tr>\n\
			<td><a href="{{$분류링크}}"><img src="{{$분류이미지경로}}" onmouseover="this.src=\'{{$분류오버이미지경로}}\';" onmouseout="this.src=\'{{$분류이미지경로}}\';"></a></td>\n\
		</tr>';
    	<?php } else if ($code_type == 'b') { ?>
		Table[1]='<table border="0" width="100%" cellpadding="0" cellspacing="0">';
		Table[2]='</table>';
		Ex[1]='	<tr>\n\
			<td><a href="{{$글링크}}">{{$글제목}}</a></td>\n\
		</tr>';
		Ex[2]='	<tr>\n\
			<td>\n\
			<table border="0" width="100%" cellpadding="0" cellspacing="0">\n\
				<tr>\n\
					<td><a href="{{$글링크}}">{{$글제목}}</a></td>\n\
					<td>{{$글작성자}}</td>\n\
					<td>{{$글작성일}}</td>\n\
				</tr>\n\
			</table>\n\
			</td>\n\
		</tr>';
    	<?php } ?>
		ex_num=f.ex.value;
		if(ex_num == ''){
			f['list_content[1]'].value=f['list_content[1]'].defaultValue;
			f['list_content[2]'].value=f['list_content[2]'].defaultValue;
			f['list_content[3]'].value=f['list_content[3]'].defaultValue;
			return;
		}
		if(!Ex[ex_num]) return;
		f['list_content[1]'].value=Table[1];
		f['list_content[2]'].value=Ex[ex_num];
		f['list_content[3]'].value=Table[2];
	}
	function editorKeyUp(w){
		if(event.keyCode == 9){
			(w.selection=document.selection.createRange()).text='\t';
			event.returnValue = false;
		}
	}
	function autoScroll(w){
		obj=document.getElementById('auto_scroll_detail');
        if (!obj) return false;
		if(w.checked == true){
			obj.style.display='block';
			filterSelect();
		}else{
			obj.style.display='none';
		}
		imgNumberChg();
	}
	function flashXMLuse(w, onload){
		obj=document.getElementById('flash_xml_detail');
		if(w.checked == true){
			obj.style.display='block';
		}else{
			obj.style.display='none';
		}
		if(!onload) imgNumberChg();
	}
	function imgNumberChg() {
		trobj=document.getElementsByTagName('tr');
		frm=document.editFrm;

		if(frm.auto_scroll.checked == true) {
			$('.list_content_tr_common').show();
			$('.list_content_tr_scroll').show();
		} else {
			$('.list_content_tr_common').show();
			$('.list_content_tr_scroll').hide();
		}
	}
	function filterSelect(){
		var w = document.editFrm.style_filter.value;
		$('.ascroll_trs').hide();

		if(w == '') return;

		switch(w) {
			case 'scroll' :
				$('#scroll_direction_tr').show();
			break;
			case 'rollv2' :
				$('#scroll_direction_tr').show();
				$('#pause_tr').show();
				$('#autostart_tr').show();
			break;
			case 'escroll' :
				$('#scroll_direction_tr').show();
				$('#scroll_time_tr').show();
				<?php if($code_type == "b"){ ?>
				$('#board_line_tr').show();
				<?php } ?>
			break;
			case 'revealtrans_22' :
			break;
			default :
				$('#scroll_time_tr').show();
				$('#duration_tr').show();

		}
	}

	function changeBoardType(obj) {
		$('.review_opt').attr('disabled', true);
		if(!obj) return;
		if(obj.value == 'prd:review') {
			$("[name=board_is_notice]>option").filter("[value=B]").prop('disabled', false);
			$('.review_opt').attr('disabled', false);
		} else {
			$("[name=board_is_notice]>option").filter("[value=B]").prop('disabled', true);
		}
		cate_show(obj.value, '<?=$board_cate?>');
	}
	changeBoardType(document.getElementsByName('editFrm')[0].board_name);

	function cate_show(db, selected_cate) {
		$.get("./index.php?body=design@board_cate_view.exe&db="+db+"&selected_cate="+selected_cate, function(r) {
			if(r) {
				$('#board_cate_view').html(r).show();
			}else {
				$('#board_cate_view').hide();
			}
		});
	}
	function onlyNumber(w){
		if(w && w.name == 'product_list_cols') return;
		if(event.keyCode != 13 && event.keyCode != 110 && event.keyCode != 190){
			if(event.keyCode != 8 && ((event.keyCode < 48) || (event.keyCode > 57))) event.returnValue=false;
		}
	}
	function ucodeDel(){
		if(!confirm('코드를 삭제하시겠습니까?')) return;
		f=document.editFrm;
		f.exec.value='delete';
		f.submit();
	}
	$(function() {
		$('body').css({'overflow-y':'scroll'});
		window.focus();
		checkTab();
        autoScroll(document.getElementById('auto_scroll'));

		$('.datepicker').datepicker({
			monthNamesShort: ['1월','2월','3월','4월','5월','6월','7월','8월','9월','10월','11월','12월'],
			dayNamesMin: ['일','월','화','수','목','금','토'],
			weekHeader: 'Wk',
			dateFormat: 'yy-mm-dd',
			autoSize: false,
			changeYear: true,
			changeMonth: true,
			showButtonPanel: true,
			currentText: '오늘 <?=date("Y-m-d", $now)?>',
			closeText: '닫기'
		});
	})

	function delUserImage(num){
		if(!confirm('저장된 이미지를 삭제하시겠습니까?')) return;
		f=document.editFrm;
		f.exec.value='uimage_delete';
		f.img_num.value=num;
		f.submit();
	}
	function imgFtpOpen(){
		window.open('about:blank','commonIMG','top=10,left=10,width=900,status=no,toolbars=no,scrollbars=yes,height=800');
		f=document.popFrm;
		f.body.value='design@common_img';
		f.target='commonIMG';
		f.submit();
		f.body.value=f.body.defaultValue;
	}

	new Clipboard('.clipboard').on('success', function(e) {
		window.alert('코드가 복사되었습니다.');
	});

	function checkTab() {
		var hash = location.href.split('#')[1];
		if(!hash) hash = 'basic';

		$('.box_tab').find('.active').removeClass('active');
		$('.box_tab').find('[href="#'+hash+'"]').addClass('active');

		$('.edit_area').not('#area_'+hash).hide();
		$('#area_'+hash).show();

		if(hash == 'source') {
            var textareas = new Array('edt_content', 'list_content1', 'list_content3', 'list_content4');
            for(var _idx in textareas) {
                if(document.getElementById(textareas[_idx])) {
                    editAreaLoader.init({
                        id: textareas[_idx]
                        ,start_highlight: true
                        ,allow_resize: "both"
                        ,allow_toggle: false
                        ,word_wrap: true
                        ,replace_tab_by_spaces: false
                        ,language: "kr"
                        ,syntax: "html"
                        ,font_family: 'dotum'
                    });
                }
            }
		}
	}

	$(window).on('ready hashchange', function() {
		checkTab();
	});

    $(function() {
        var ck1 = $(':checkbox[name=use_cate_info]');
        var ck2 = $(':checkbox[name=use_cate_info2]');
        var ck3 = $(':checkbox[name=use_cate_info2_child]');

        ck1.on('click', function() {ck2.prop('checked', false); ck3.prop('checked', false).prop('disabled', true); });
        ck2.on('click', function() {ck1.prop('checked', false); ck3.prop('disabled', false); });
        if (ck2.prop('checked') == false) {
            ck3.prop('disabled', true);
        }
    });
</script>
<script type="text/javascript" src="<?=$engine_url?>/_engine/common/edit_area/edit_area_full.js"></script>
<?php
	designValUnset();
?>