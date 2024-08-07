<?PHP

	if($GLOBALS['_this_pop_up']) return;

	$amember = $GLOBALS['member'];
	$m_id = $amember['member_id'];
	$m_name = stripslashes($amember['name']);
	$m_cell = $amember['cell'];
	$m_email = $amember['email'];

	$easemob_btn_use = ($GLOBALS['cfg']['easemob_btn_use'] == 'Y') ? 'false' : 'true';
	$use_easemob_lang = ($GLOBALS['cfg']['use_easemob_lang'] == 'EN') ? '/en-US' : '';

?>
<!-- Easemob Plugin Scripts -->
<script type="text/javascript">
window.easemobim = window.easemobi || {};
easemobim.config = {
	configId: '<?=$cfg['easemob_plugin_id']?>',
    hide: <?=$easemob_btn_use?>,
    autoConnect: true,
    visitor: {
        trueName: '<?=$m_name?>',
        phone: '<?=$m_cell?>',
        userNickname: '<?=$m_id?>',
        email: '<?=$m_email?>'
    },
};

function callEasemobim() {
	easemobim.bind({configId: "<?=$cfg['easemob_plugin_id']?>"});
}
</script>
<script src='//kefu.easemob.com/webim<?=$use_easemob_lang?>/easemob.js'></script>
<!-- End Easemob Plugin -->