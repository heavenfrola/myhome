<?PHP

	/* +----------------------------------------------------------------------------------------------+
	' |  내정보수정
	' +----------------------------------------------------------------------------------------------+*/

	$_team=getIntraTeam();
	$_team1=array();
	foreach($_team as $key=>$val){
		if(!$_team[$key][ref]){
			$_team1[$key]=$_team[$key][name];
		}
	}
	$data=$admin;

?>
<form id="staffFrm" name="frm" method="post" action="./" target="hidden<?=$now?>" onSubmit="return checkFrm(this)">
	<input type="hidden" name="body" value="intra@my_info.exe">
	<div class="box_title first">
		<h2 class="title">내정보수정</h2>
	</div>
	<?include $engine_dir."/_manage/intra/staffs_frm.php";?>
</form>

<script language="JavaScript">
	function checkFrm(f){
		if (!checkBlank(f.admin_id,"성명을 입력해주세요.")) return false;
	}
</script>