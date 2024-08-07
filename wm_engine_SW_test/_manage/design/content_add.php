<?PHP

	/* +----------------------------------------------------------------------------------------------+
	' |  추가 페이지 편집
	' +----------------------------------------------------------------------------------------------+*/

	$_content_add_info = $_content_cate = array();
	$cont_edit_file = $root_dir."/_config/content_add.php";
	if(file_exists($cont_edit_file) == true) {
		include_once $cont_edit_file;
	}

    $res = $cates = array();
    foreach ($_content_add_info as $key => $val) {
        if ($val['cate']) {
			if (in_array($val['cate'], $_content_cate) == false) {
				$_content_cate[] = $val['cate'];
			}
        } else {
			$val['cate'] = "미지정";
			if (!in_array("미지정", $_content_cate)) $_content_cate[]  = "미지정";
        }
        $res[$val['cate']][$key] = $val;

        if ($_GET['no'] && $_GET['no'] == $key) {
            $data = $val;
            $data['no'] = $_GET['no'];
        }
    }
    if (count($_content_cate) == 0) {
        $_content_cate[] = '미지정';
    }
    if (isset($data['use_m_content']) == false) {
        $data['use_m_content'] = 'N';
    }

	$group = getGroupName();

    function parseCont(&$res) {
		if (is_array($res) == false) {
            return false;
        }
        $data = current($res);
        if ($data == false) return false;

        $data['key'] = key($res);
        if (strlen($data['mgroup']) > 0) {
            $data['mauth'] = "<span class='p_color2 explain'>[접근제한]</span>";
        }

        next($res);
        return $data;
    }

?>
<form name="frm" method="post" action="<?=$_SERVER['PHP_SELF']?>?type=<?=$_GET['type']?>" target="hidden<?=$now?>" onSubmit="return ckFrm(this)">
    <input type="hidden" name="body" value="design@content_add.exe">
    <input type="hidden" name="no" value="<?=$no?>">
	<div class="box_title first">
		<h2 class="title">추가 페이지 편집</h2>
	</div>
	<table class="tbl_row">
		<caption class="hidden">추가 페이지 편집</caption>
		<colgroup>
			<col style="width:15%">
		</colgroup>
		<tr>
			<th scope="row">그룹명</th>
			<td>
				<input type="text" name="cate" class="input" value="<?=$data['cate']?>">
				<span class="explain">(페이지가 많은 경우 입력하시면 관리가 편합니다)</span>
			</td>
		</tr>
		<tr>
			<th scope="row"><strong>페이지 명</strong></th>
			<td>
				<input type="text" name="name" class="input" value="<?=$data['name']?>">
				<span class="explain">(페이지 제목/설명)</span>
			</td>
		</tr>
		<tr>
			<th scope="row">모바일페이지 사용</th>
			<td>
                <label><input type="radio" name="use_m_content" value="Y" <?=checked($data['use_m_content'], 'Y')?>> 사용함</label>
                <label><input type="radio" name="use_m_content" value="N" <?=checked($data['use_m_content'], 'N')?>> 사용안함</label>
			</td>
		</tr>
		<tr>
			<th scope="row">접근 권한</th>
			<td>
				<label class="p_cursor"><input type="checkbox" name="gck" value="all" onclick="ckgroup(this.form,this)" <?=$data['mgroup'] ? " checked" : "";?>> <b>제한</b></label><br>
				<?php foreach($group as $key => $val) { $_mgroup = explode('@', trim($data['mgroup'], '@'));?>
				<label class="p_cursor">
                    <input type="checkbox" name="mgroup[]" class="level_<?=$key?>" value="<?=$key?>" <?=checked(in_array($key, $_mgroup), true)?>> <?=$val?>
                </label>
				<?php }?>
			</td>
		</tr>
		<tr>
			<th scope="row">파일 명</th>
			<td>
				<input type="text" name="pg_name" class="input" value="<?=$data['pg_name']?>"<?=($data['no']) ? " style=\"background-color:#E3E3E3;\" readonly" : "";?>>
				<span class="explain">(확장자 포함하여 입력해주시기 바랍니다 .php - 미입력시 자동생성됩니다)</span>
			</td>
		</tr>
	</table>
	<div class="box_bottom">
		<span class="box_btn blue"><input type="submit" value="<?=($data['no']) ? '수정' : '추가'?>"></span>
		<?php if($data['no']){?>
		<span class="box_btn gray"><input type="button" value="취소" onclick="location.href='./?body=<?=$body?>&type=<?=$_GET['type']?>';"></span>
		<?php } ?>
	</div>
</form>
<div class="box_title">
	<h2 class="title">추가 페이지</h2>
</div>
<table class="tbl_col">
	<caption class="hidden">추가 페이지 편집</caption>
	<colgroup>
		<col span="2">
		<col style="width:80px">
		<col style="width:100px">
		<col style="width:100px">
		<col style="width:80px">
	</colgroup>
	<thead>
		<tr>
			<th scope="col">페이지명</th>
			<th scope="col">파일명</th>
			<th scope="col">모바일</th>
			<th scope="col">내용편집</th>
			<th scope="col">속성수정</th>
			<th scope="col">삭제</th>
		</tr>
	</thead>
	<tbody>
		<?php foreach ($_content_cate as $key2 => $val2) { ?>
		<tr>
			<td colspan="6" class="left"><strong class="p_color2"><?=$val2?></strong></td>
		</tr>
        <?php while($cont = parseCont($res[$val2])) {?>
		<tr>
			<td class="left"><?=$cont['name']?></td>
			<td class="left">
                <a href="<?=$root_url?>/content/content.php?cont=<?=$cont['key']?>" target="_blank">
                    <?=$cont['pg_name']?>
                </a>
                <?=$cont['mauth']?>
            </td>
            <td><?=$cont['use_m_content']?></td>
			<td><span class="box_btn_s"><input type="button" value="내용편집" onclick="location.href='./?body=<?=$_inc[0]?>@content&type=mobile&content_add=1&cont_no=<?=$cont['key']?>';"></span></td>
			<td><span class="box_btn_s"><input type="button" value="속성수정" onclick="location.href='./?body=<?=$body?>&type=<?=$_GET['type']?>&no=<?=$cont['key']?>';"></span></td>
			<td><span class="box_btn_s"><input type="button" value="삭제" onclick="delAddPage('<?=$cont['key']?>');"></span></td>
		</tr>
		<?php }} ?>
	</tbody>
</table>

<script type="text/javascript">
	function ckFrm(f){
		if(!checkBlank(f.name,"페이지명을 입력해주세요.")) return false;

        printLoading();
	}
	function ckgroup(f,o){
        $(':checkbox[name="mgroup[]"]', f).prop('disabled', (o.checked == true) ? false : true);
		$('.level_1').prop('disabled', true);
	}
	function delAddPage(no){
		if(!confirm('삭제시 복구가 불가능합니다. 삭제하시겠습니까?')) return;
		window.frames[hid_frame].location.href='./?body=design@content_add.exe&exec=delete&no='+no;
	}

    $(function() {
        ckgroup(document.frm, document.frm.gck)
		$('.level_1').prop('checked', true);
		$('.level_1').prop('disabled', true);
    });
</script>