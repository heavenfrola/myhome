<?PHP

	/* +----------------------------------------------------------------------------------------------+
	' |  재입고 알림 신청 내역
	' +----------------------------------------------------------------------------------------------+*/

	include_once $engine_dir."/_engine/include/common.lib.php";

	memberOnly(1,"");

	common_header();

?>
<script>
	function notify_restock_cancel(no) {
		if(confirm(_lang_pack.notify_restock_confirm_cancel)) {
			$.ajax({
				type: "POST"
				, url: "/main/exec.php"
				, data: {exec_file:"mypage/notify_restock.exe.php", exec:"ajax_cancel", no:no}
				, success: function(response) {
					res = JSON.parse(response);
					if(res.stat == 1) {
						alert(_lang_pack.notify_restock_cancel);
						location.reload();
					}
					if(res.stat == 0) alert(res.msg);
				}
				, error: function(response, status, error) {

				}
			})
		}
	}
</script>
<?
	// 디자인 버전 점검 & 페이지 출력
	include_once $engine_dir."/_engine/common/skin_index.php";
?>