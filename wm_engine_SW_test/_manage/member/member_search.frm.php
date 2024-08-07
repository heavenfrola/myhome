<?PHP

	$ww=($pop) ? "750px" : "100%";
	function wCk($type="radio", $arr, $name="", $ck="", $num=5, $none=0){
		$ii=1;
		if(is_array($arr)){
			foreach($arr as $key=>$val){
?>
<input type="<?=$type?>" name="<?=$name?><?=($type == "checkbox") ? "[]" : "";?>" value="<?=$key?>" id="<?=$name?><?=$key?>"
<?
	if($type == "checkbox") {
		if(strchr($ck, "@".$key."@")) echo " checked";
	}elseif($type == "radio") {
		if($ck == $key) echo " checked";
	}
?>
>
<label for="<?=$name?><?=$key?>" class="p_cursor"><?=$val?></label>
<?
	if($ii%$num == 0 && count($arr)+$none > $ii) echo "<br>";
	$ii++;
			}
		}else{
?>
<input type="<?=$type?>" name="<?=$name?>" value="<?=$ck?>" class="input" onkeypress="onlyNumber();">
<?
		}
	}
	$_search_fd=array(name=>"이름", member_id=>"아이디", email=>"이메일", addr1=>"주소", addr2=>"상세 주소", phone=>"전화번호", cell=>"휴대폰");
	$_set=mySearchSet("membersearch");

?>
<style type="text/css" title="">
body {background:#fff;}
</style>
<form name="searchFrm" method="post" action="./" target="hidden<?=$now?>" onsubmit="return ckSearchFrm(this);" style="width:<?=$ww?>;">
	<input type="hidden" name="body" value="member@member_search.exe">
	<div class="box_title first">
		<h2 class="title">회원 검색 세부 설정</h2>
	</div>
	<div class="box_middle left">
		해당 기능을 사용하여 데이터 검색시 데이터의 범위를 설정하여 좀 더 세부적으로 검색을 할 수 있으며 <u>관리자별로 설정이 가능</u>합니다.
	</div>
	<table class="tbl_row">
		<caption class="hidden">운영자 정보</caption>
		<colgroup>
			<col style="width:15%">
		</colgroup>
		<tr>
			<th scope="row">적립금</th>
			<td>
				단위 : <?=wCk("text", "", "milage_up", $_set[milage_up]);?> &nbsp;
				제한 : <?=wCk("text", "", "milage_limit", $_set[milage_limit]);?>
				<br>
				<p class="explain">검색시 적립금의 검색범위를 설정하실 수 있습니다.</p>
			</td>
		</tr>
		<tr>
			<th scope="row">접속횟수</th>
			<td>
				단위 : <?=wCk("text", "", "visit_up", $_set[visit_up]);?> &nbsp;
				제한 : <?=wCk("text", "", "visit_limit", $_set[visit_limit]);?>
				<br>
				<p class="explain">검색시 접속횟수의 검색범위를 설정하실 수 있습니다.</p>
			</td>
		</tr>
		<tr>
			<th scope="row">주문횟수</th>
			<td>
				단위 : <?=wCk("text", "", "order_up", $_set[order_up]);?> &nbsp;
				제한 : <?=wCk("text", "", "order_limit", $_set[order_limit]);?>
				<br>
				<p class="explain">검색시 주문횟수의 검색범위를 설정하실 수 있습니다.</p>
			</td>
		</tr>
		<tr>
			<th scope="row">구매금액</th>
			<td>
				단위 : <?=wCk("text", "", "prc_up", $_set[prc_up]);?> &nbsp;
				제한 : <?=wCk("text", "", "prc_limit", $_set[prc_limit]);?>
				<br>
				<p class="explain">검색시 구매금액의 검색범위를 설정하실 수 있습니다.</p>
			</td>
		</tr>
		<tr>
			<th scope="row">검색필드</th>
			<td>
				<?=wCk("radio", $_search_fd, "search", $_set[search], 5);?>
				<br>
				<p class="explain">검색시 기본으로 선택되어 있기를 원하시는 필드를 선택하시기 바랍니다.</p>
			</td>
		</tr>
	</table>
	<div class="box_bottom">
		<span class="box_btn_s blue"><input type="submit" value="확인"></span> <?=$close_btn?>
	</div>
</form>

<script language="JavaScript">
	function onlyNumber(){
		if(event.keyCode != 13 && event.keyCode != 110 && event.keyCode != 190){
			if(event.keyCode != 8 && ((event.keyCode < 48) || (event.keyCode > 57))) event.returnValue=false;
		}
	}
	function ckSearchFrm(f){
		if(!checkBlank(f.milage_up, '적립금 단위를 입력해주세요.')) return false;
		if(!checkBlank(f.milage_limit, '적립금 제한범위를 입력해주세요.')) return false;
		if(!checkBlank(f.visit_up, '접속횟수 단위를 입력해주세요.')) return false;
		if(!checkBlank(f.visit_limit, '접속횟수 제한범위를 입력해주세요.')) return false;
		if(!checkBlank(f.order_up, '주문횟수 단위를 입력해주세요.')) return false;
		if(!checkBlank(f.order_limit, '주문횟수 제한범위를 입력해주세요.')) return false;
		if(!checkBlank(f.prc_up, '구매금액 단위를 입력해주세요.')) return false;
		if(!checkBlank(f.prc_limit, '구매금액 제한범위를 입력해주세요.')) return false;
	}
</script>