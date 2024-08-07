<?PHP

	/* +----------------------------------------------------------------------------------------------+
	' |  내월간근태
	' +----------------------------------------------------------------------------------------------+*/
	include_once $engine_dir."/_manage/main/main_box.php";

?>
<div id="intra_calendar"></div>

<script language="JavaScript">
	function getCalContent(addq){
		if(!addq) addq='';
		$.get('./?body=intra@calendar_inc.exe&mno=<?=$admin[no]?>&db=my_attend'+addq, function(r) {
			$('#intra_calendar').html(r);
		});
	}
	getCalContent();
</script>