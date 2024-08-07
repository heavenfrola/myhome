<?php

/**
 * 카카오페이 결제 폼
 **/

if ($_SESSION['browser_type']=='mobile') {
	$style = "style=\"display: none;width:100%;\"";
} else {
	$style = "style=\"display: none;\"";
}

?>
<div id="kakaopay_layer" <?=$style?>>
	<iframe name="kakaopay_Frame" id="kakaopay_Frame" src="" style="z-index: 1001; position: absolute; width: 426px; height: 550px; left: 726.5px; top: 903.5px;"></iframe>
</div>