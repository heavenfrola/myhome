<?PHP

	/* +----------------------------------------------------------------------------------------------+
	' |  주문추가항목 설정
	' +----------------------------------------------------------------------------------------------+*/

	include_once $engine_dir."/_engine/include/member.lib.php";

	if(!$_SESSION['member_addinfo_done']){
		$template_contents=@file_get_contents($root_dir."/_template/member/join_frm.php");
		$_SESSION['member_addinfo_done']=(!@strchr($template_contents,"ADDINFO_DONE")) ? "N" : "Y";
	}
	$lower=($_SESSION['member_addinfo_done'] == "Y") ? 0 : 1;
	$_ord_add_info=array();
	if(@file_exists($root_dir."/_config/order.php")){
		include_once $root_dir."/_config/order.php";
	}

	$total=count($_ord_add_info);
	$no = numberOnly($_GET['no']);

?>
<form name="frm" method="post" action="<?=$_SERVER['PHP_SELF']?>" target="hidden<?=$now?>" onSubmit="return ckFrm(this)">
	<input type="hidden" name="body" value="config@order_addinfo.exe">
	<input type="hidden" name="no" value="<?=$no?>">
	<div class="box_title first">
		<h2 class="title">주문 추가항목 등록/수정</h2>
	</div>
	<table class="tbl_row">
		<caption class="hidden">주문 추가항목 등록/수정</caption>
		<colgroup>
			<col style="width:15%">
			<col>
		</colgroup>
		<?PHP

			if($no) $no--;
			$data=$_ord_add_info[$no];
			if(!$data['type']) $data['type']="radio";
			if(!$data['ncs']) $data['ncs']="N";

		?>
		<tr>
			<th scope="row"><strong>항목명</strong></th>
			<td>
				<input type="text" name="name" class="input" value="<?=inputText($data['name'])?>">
			</td>
		</tr>
		<tr>
			<th scope="row">속성</th>
			<td>
				<input type="radio" name="type" id="type_1" value="radio" <?=checked($data['type'],"radio").checked($data['type'],"")?> onclick="showCK(1);"> <label for="type_1" class="p_cursor">라디오(단일)</label>
				<input type="radio" name="type" id="type_2" value="checkbox" <?=checked($data['type'],"checkbox")?> onclick="showCK(1);"> <label for="type_2" class="p_cursor">체크박스(복수)</label>
				<input type="radio" name="type" id="type_3" value="text" <?=checked($data['type'],"text")?> onclick="showCK(2);"> <label for="type_3" class="p_cursor">텍스트(입력)</label>
				<input type="radio" name="type" id="type_4" value="date" <?=checked($data['type'],"date")?> onclick="showCK(3);"> <label for="type_4" class="p_cursor">날짜</label>
			</td>
		</tr>
		<tr>
			<th scope="row">선택사항</th>
			<td>
				<input type="radio" name="ncs" id="ncs_2" value="N" <?=checked($data['ncs'],"N")?>> <label for="ncs_2" class="p_cursor">선택</label>
				<input type="radio" name="ncs" id="ncs_1" value="Y" <?=checked($data['ncs'],"Y")?>> <label for="ncs_1" class="p_cursor">필수</label><br>
			</td>
		</tr>
		<tr id="stext">
			<th scope="row"><strong>선택항목</strong></th>
			<td>
				<input type="text" name="text" class="input" size="50" value="<?=(is_array($data['text'])) ? inputText(implode(",", $data['text'])) : "";?>">
				<ul class="list_info">
					<li>여러 개의 항목을 추가 시 콤마(,)로 구분하여 등록할 수 있습니다.</li>
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
		<tr id="sformat" style="display:none;">
			<th scope="row">표시항목</th>
			<td>
				<input type="radio" name="format" id="format_1" value="1" <?=checked($data['format'],"1").checked($data['format'],"")?>> <label for="format_1" class="p_cursor">날짜</label> &nbsp;
				<input type="radio" name="format" id="format_2" value="2" <?=checked($data['format'],"2")?>> <label for="format_2" class="p_cursor">날짜+시간</label>
			</td>
		</tr>
	</table>
	<div class="box_bottom">
		<span class="box_btn blue"><input type="submit" value="확인"></span>
		<?php if ($data['name']) { ?>
		<span class="box_btn"><input type="button" value="취소" onclick="location.href='?body=<?=$_GET['body']?>'"></span>
		<?php } ?>
	</div>
</form>
<form>
	<div class="box_title">
		<h2 class="title">주문 추가항목 관리</h2>
	</div>
	<table class="tbl_col">
		<caption class="hidden">주문 추가항목 관리</caption>
		<colgroup>
			<col style="width:70px;">
			<col>
			<col style="width:100px;">
			<col style="width:100px;">
			<col style="width:100px;">
			<col style="width:100px;">
		</colgroup>
		<thead>
			<tr>
				<th scope="col">번호</th>
				<th scope="col">항목명</th>
				<th scope="col">속성</th>
				<th scope="col">선택사항</th>
				<th scope="col">수정</th>
				<th scope="col">삭제</th>
			</tr>
		</thead>
		<tbody>
			<?php
				$count=1;
				$_type=array("radio"=>"라디오", "checkbox"=>"체크박스", "text"=>"텍스트", "date"=>"날짜");
				foreach($_ord_add_info as $key=>$val){
					$rclass=($count%2==1) ? "tcol2" : "tcol3";
			?>
			<tr>
				<td><?=$count?></td>
				<td class="left"><?=stripslashes($_ord_add_info[$key]['name'])?></td>
				<td><?=$_type[$_ord_add_info[$key]['type']]?></td>
				<td><?=($_ord_add_info[$key]['ncs'] == "Y") ? "필수" : "선택";?></td>
				<td><span class="box_btn_s"><input type="button" value="수정" onclick="location.href='./?body=<?=$body?>&no=<?=$key+1;?>'"></span></td>
				<td><span class="box_btn_s"><input type="button" value="삭제" onclick="afdel(<?=($key+1)?>);"></span></td>
			</tr>
			<?php
				$count++;
				}
			?>
		</tbody>
	</table>
</form>
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
        printLoading();
	}
	function showCK(w){
		var stext=document.getElementById('stext');
		var sdesign=document.getElementById('sdesign');
		var sformat=document.getElementById('sformat');
		if(w == 1){
			sdesign.style.display='none';
			sformat.style.display='none';
			stext.style.display='';
		}else if(w == 2){
			stext.style.display='none';
			sformat.style.display='none';
			sdesign.style.display='';
		}
		else{
			stext.style.display='none';
			sdesign.style.display='none';
			sformat.style.display='';
		}
	}

	function afdel(no) {
		if(confirm('해당 항목을 삭제하시겠습니까?')) {
            printLoading();

            $.get('./index.php', {'body': 'config@order_addinfo.exe', 'exec':'delete', 'no': no}, function() {
                location.reload();
            });
		}
	}
	<?php
		if($data['type']){
			if($data['type'] == "text") echo "showCK(2);\n";
			else if($data['type'] == "date")	echo "showCK(3);\n";
			else echo "showCK(1);\n";
		}
	?>
</script>