<?PHP

	/* +----------------------------------------------------------------------------------------------+
	' |  Crema
	' +----------------------------------------------------------------------------------------------+*/

	$site_type = $_SESSION['browser_type'] == 'mobile' ? 'm' : 'd';

	$_infos = parse_url($GLOBALS[root_url]);
	if(preg_match("/www./is", $_infos['host'])) {
		$_c_domain = explode("www.", $_infos['host']);
		$c_domain = $_c_domain[1];
	}else {
		$c_domain = $_infos['host'];
	}

    $isResponsiveSkin = ($scfg->get('crema_responsive_skin') && $scfg->comp('crema_responsive_skin', 'Y') == true) ? 'Y' : 'N';
    $tracker = "";
    if ($isResponsiveSkin == 'Y') { // 반응형 스킨
        $tracker = "var md = new MobileDetect(window.navigator.userAgent); if (md.mobile()) { (function(i,s,o,g,r,a,m){if(s.getElementById(g)){return};a=s.createElement(o),m=s.getElementsByTagName(o)[0];a.id=g;a.async=1;a.src=r;m.parentNode.insertBefore(a,m)})(window,document,'script','crema-jssdk','//widgets.cre.ma/$c_domain/mobile/init.js'); } else { (function(i,s,o,g,r,a,m){if(s.getElementById(g)){return};a=s.createElement(o),m=s.getElementsByTagName(o)[0];a.id=g;a.async=1;a.src=r;m.parentNode.insertBefore(a,m)})(window,document,'script','crema-jssdk','//widgets.cre.ma/$c_domain/init.js'); }";
    } else {
        $arr_page = array('main_index.php','shop_detail.php','shop_product_review_list.php','mypage_order_detail.php','mypage_review_list.php');
        if($site_type=='d') {//pc
            if(1 || in_array($GLOBALS['_file_name'], $arr_page)) {
                $tracker = "(function(i,s,o,g,r,a,m){if(s.getElementById(g)){return};a=s.createElement(o),m=s.getElementsByTagName(o)[0];a.id=g;a.async=1;a.src=r;m.parentNode.insertBefore(a,m)})(window,document, 'script', 'crema-jssdk', '//widgets.cre.ma/$c_domain/init.js');";

            }
        }else {//mobile
            if(1 || in_array($GLOBALS['_file_name'], $arr_page)) {
                if(preg_match("/m./is", $c_domain)) {
                    $_c_domain1 = explode(".", $c_domain);
                    $c_domain = "";
                    for($rii=0;$rii<count($_c_domain1);$rii++) {
                        if($rii>0) {
                            if($rii!=count($_c_domain1)-1) {
                                $c_domain .= $_c_domain1[$rii].".";
                            }else {
                                $c_domain .= $_c_domain1[$rii];
                            }
                        }
                    }
                }
                $tracker = "(function(i,s,o,g,r,a,m){if(s.getElementById(g)){return};a=s.createElement(o),m=s.getElementsByTagName(o)[0];a.id=g;a.async=1;a.src=r;m.parentNode.insertBefore(a,m)})(window,document,'script','crema-jssdk','//widgets.cre.ma/$c_domain/mobile/init.js');";
            }
        }
    }


?>
<!-- crema -->
<?if($tracker) {?>
    <?if($isResponsiveSkin == 'Y') {?>
        <script src="//cdn.jsdelivr.net/npm/mobile-detect@1.4.5/mobile-detect.min.js"></script>
    <?}?>
<script type='text/javascript'>
	<?echo $tracker;?>
</script>
<?}?>
<!-- crema -->