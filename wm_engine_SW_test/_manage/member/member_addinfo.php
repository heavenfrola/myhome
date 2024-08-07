<?PHP

	/* +----------------------------------------------------------------------------------------------+
	' |  가입추가항목 설정
	' +----------------------------------------------------------------------------------------------+*/

	include_once $engine_dir."/_engine/include/member.lib.php";

	$_mbr_add_info=array();
	if(@file_exists($root_dir."/_config/member.php")){
		include_once $root_dir."/_config/member.php";
	}

	$total=count($_mbr_add_info);
	$no = numberOnly($_GET['no']);

?>
<form name="frm" method="post" action="<?=$_SERVER['PHP_SELF']?>" target="hidden<?=$now?>" onSubmit="return ckFrm(this)" enctype="multipart/form-data">
	<input type="hidden" name="body" value="member@member_addinfo.exe">
	<input type="hidden" name="no" value="<?=$no?>">
	<div class="box_title first">
		<h2 class="title">추가항목 등록/수정</h2>
	</div>
	<?PHP
		if($no) $no--;
		$data=$_mbr_add_info[$no];
		if(!$data['type']) $data['type']="radio";
		if(!$data['ncs']) $data['ncs']="N";
	?>
	<table class="tbl_row">
		<caption class="hidden">추가항목 등록/수정</caption>
		<colgroup>
			<col style="width:15%">
			<col>
		</colgroup>
		<tr>
			<th scope="row"><strong>항목명</strong></th>
			<td><input type="text" name="name" class="input" value="<?=inputText($data['name'])?>"></td>
		</tr>
		<tr>
			<th scope="row">분류명</th>
			<td><input type="text" name="cate" class="input" value="<?=inputText($data['cate'])?>"></td>
		</tr>
		<tr>
			<th scope="row">속성</th>
			<td>
				<input type="radio" name="type" id="type_1" value="radio" <?=checked($data['type'],"radio").checked($data['type'],"")?> onclick="showCK(1);"> <label for="type_1" class="p_cursor">라디오(단일)</label>
				<input type="radio" name="type" id="type_2" value="checkbox" <?=checked($data['type'],"checkbox")?> onclick="showCK(1);"> <label for="type_2" class="p_cursor">체크박스(복수)</label>
				<input type="radio" name="type" id="type_3" value="text" <?=checked($data['type'],"text")?> onclick="showCK(2);"> <label for="type_3" class="p_cursor">텍스트(입력)</label>
				<input type="radio" name="type" id="type_4" value="selectarray" <?=checked($data['type'],"selectarray")?> onclick="showCK(3);"> <label for="type_4" class="p_cursor">달력(날짜선택)</label>
                <input type="radio" name="type" id="type_5" value="file" <?=checked($data['type'], 'file')?> onclick="showCK(5);"> <label for="type_5" class="p_cursor">첨부파일</label>
			</td>
		</tr>
		<tr>
			<th scope="row">선택사항</th>
			<td>
				<input type="radio" name="ncs" id="ncs_2" value="N" <?=checked($data['ncs'],"N")?>> <label for="ncs_2" class="p_cursor">필수아님</label>
				<input type="radio" name="ncs" id="ncs_1" value="Y" <?=checked($data['ncs'],"Y")?>> <label for="ncs_1" class="p_cursor">필수</label>
				<div class="explain">(주의) 스킨이 수정이 되지 않은 상태에서 필수로 선택한 경우 가입 오류 현상이 발생될 수 있습니다.</div>
			</td>
		</tr>
		<tr id="stext">
			<th scope="row"><strong>선택항목</strong></th>
			<td>
				<input type="text" name="text" class="input input_full" value="<?=(is_array($data['text'])) ? implode(",",$data['text']) : "";?>"> <font class="help">, 로 구분하여 입력 (예:검색,소개,광고)</font>
			</td>
		</tr>
		<tr id="sext">
			<th scope="row"><strong>확장자</strong></th>
			<td>
				<input type="text" name="ext" class="input input_full" value="<?=(is_array($data['ext'])) ? implode(",",$data['ext']) : "";?>">
                <ul class="list_info">
                    <li>콤마(,)로 구분하여 입력해주세요. (예:jpg,png)</li>
                    <li>미입력시 모든 확장자가 허용됩니다.</li>
                </ul>
			</td>
		</tr>
		<tr id="sdesign" style="display:none;">
			<th scope="row">디자인</th>
			<td>
				사이즈(입력폼 길이) : <input type="text" name="size" class="input" size="5" value="<?=$data['size']?>"> &nbsp;
				CSS클래스명 : <input type="text" name="class" class="input" size="8" value="<?=$data['class']?>">
			</td>
		</tr>
		<tr>
			<th scope="row">상품후기 검색연동</th>
			<td>
				<label><input type="checkbox" name="review_link" value="Y" <?=checked($data['review_link'], 'Y')?>> 작성자의 가입추가항목 입력정보로 상품후기를 검색할수 있습니다.</label>
			</td>
		</tr>
		<tr>
			<th scope="row">항목 이미지</th>
			<td>
				<input type="file" name="upfile1" class="input input_full">
				<?php
					if($data['updir'] && $data['upfile1']){
						echo "<span class=\"box_btn_s\"><a href=\"".$root_url."/".$data['updir']."/".$data['upfile1']."\" target=\"_blank\">기존이미지 보기</a></span>";
						echo "<br><label class=\"p_cursor\"><input type=\"checkbox\" name=\"dell_img\"> 기존이미지 삭제 (새 이미지 업로드시 덮어씁니다)</label>";
					}
				?>
			</td>
		</tr>
	</table>
	<div class="box_middle2 left">
		<ul class="list_msg">
			<li>해당 기능을 관리하기 위해서는 <u>스킨 수정</u>이 필요합니다 추가항목 생성이나 수정이 되지 않을 경우 <a href="#" onclick="goMywisa('?body=customer@cs_reg'); return false;">[고객센터]</a> 문의 글로 접수 바랍니다.</li>
			<li>라디오나 체크박스의 속성으로 등록된 항목은 <a href="./?body=member@member_analysis" target="_blank">[회원분석]</a> 페이지에서 통계를 확인할 수 있습니다.</li>
		</ul>
	</div>
	<div class="box_bottom">
		<span class="box_btn blue"><input type="submit" value="확인"></span>
		<?php if ($data['name']) { ?>
		<span class="box_btn gray"><input type="button" value="취소" onclick="location.href='?body=<?=$_GET['body']?>'"></span>
		<?php } ?>
	</div>
</form>
<div class="box_title">
	<h2 class="title">추가항목 관리</h2>
</div>
<table class="tbl_col tbl_col_bottom">
	<caption class="hidden">추가항목 관리</caption>
	<colgroup>
		<col style="width:50px">
		<col>
		<col style="width:120px">
		<col style="width:120px">
		<col style="width:120px">
		<col style="width:120px">
		<col style="width:100px">
		<col style="width:100px">
	</colgroup>
	<thead>
		<tr>
			<th scope="col">번호</th>
			<th scope="col">항목명</th>
			<th scope="col">분류명</th>
			<th scope="col">속성</th>
			<th scope="col">선택사항</th>
			<th scope="col">미리보기</th>
			<th scope="col">수정</th>
			<th scope="col">삭제</th>
		</tr>
	</thead>
	<tbody>
		<?php
			$count=1;
			$_type=array('radio'=>"라디오",'checkbox'=>"체크박스",'text'=>"텍스트",'selectarray'=>"달력(날짜선택)");
			foreach($_mbr_add_info as $key=>$val){
				$rclass=($count%2==1) ? "tcol2" : "tcol3";
				$fd_img = $_mbr_add_info[$key]['upfile1'] ? "<img src=".$root_url."/".$_mbr_add_info[$key]['updir']."/".$_mbr_add_info[$key]['upfile1'].">" : "";
		?>
		<tr>
			<td><?=$count?></td>
			<td class="left"><?=$_mbr_add_info[$key]['name']?></td>
			<td class="left"><?=$_mbr_add_info[$key]['cate']?></td>
			<td><?=$_type[$_mbr_add_info[$key]['type']]?></td>
			<td><?=($_mbr_add_info[$key]['ncs'] == "Y") ? "필수" : "필수아님";?></td>
			<td><?=$fd_img?></td>
			<td><span class="box_btn_s"><input type="button" value="수정" onclick="location.href='./?body=<?=$body?>&no=<?=$key+1;?>'"></span></td>
			<td><span class="box_btn_s"><input type="button" value="삭제" onclick="afdel(<?=($key+1)?>);"></span></td>
		</tr>
		<?php
			$count++;
			}
		?>
	</tbody>
</table>
<form name="testFrm" method="post" action="<?=$root_url?>/member/join_step2.php" target="_blank">
	<input type="hidden" name="agree" value="Y">
	<input type="hidden" name="member_type" value="">
</form>

<script type="text/javascript">
	function ckFrm(f){
		if(!checkBlank(f.name,"항목명을 입력해주세요.")) return false;
		if(f.type[0].checked || f.type[1].checked){
			if(!checkBlank(f.text,"선택항목을 입력해주세요.")) return false;
		}
	}

	function showCK(w){
		var stext=document.getElementById('stext');
		var sdesign=document.getElementById('sdesign');
		var sext=document.getElementById('sext');

        stext.style.display = 'none';
        sdesign.style.display = 'none';
        sext.style.display = 'none';

		if (w == 1) {
			stext.style.display='';
		} else if (w == 2) {
			sdesign.style.display='';
		} else if (w == 5) {
			sext.style.display='';
		}
	}

	function afdel(no) {
		if(confirm('해당 항목을 삭제하시겠습니까?')) {
			$.post('./?body=member@member_addinfo.exe', {'exec':'delete', 'no':no}, function() {
				location.reload();
			});
		}
	}
	<?php
		if($data['type']){
			if($data['type'] == "text") echo "showCK(2);\n";
			else if($data['type'] == "file") echo "showCK(5);\n";
			else echo "showCK(1);\n";
		}
	?>

    var review_link = function() {
        var type = $(':checked[name=type]').val();
		if(type == 'radio' || type == 'checkbox') {
			$(':checkbox[name=review_link]').prop('disabled', false);
		} else {
			$(':checkbox[name=review_link]').prop('disabled', true);
		}
    }
    review_link();

	$(':radio[name=type]').change(function() {
        review_link();
	});
</script>