<?PHP

	include_once $engine_dir."/_engine/include/common.lib.php";

	if($member[level]<10) {
		msg(__lang_member_info_alreadyLogin__, 'close', '');
	}

	common_header();

?>
<script type="text/javascript" src="<?=$engine_url?>/_engine/common/member.js"></script>
<?PHP

	include_once $engine_dir."/_engine/common/skin_index.php";

?>