<?php
Class erpAPI extends WmAPI {

	var $bendor = null;
	var $api_key;
	var $timing;

	var $file_url;

	function __construct() {
        $this->erpApi();
	}

    function erpApi() {
        global $scfg;

		$this->account = $GLOBALS['wec']->config['wm_key_code'];
		$this->api_key = md5(crypt($this->account, $this->bendor));
		$this->WmAPI($this->api_key);

        if ($scfg->comp('use_erpApi', 'Y') == false) {
            global  $_we, $root_url;

            $wec = new weagleEyeClient($_we, 'Etc');
            $wec->call('setExternalService', array(
                'service_name' => 'erpAPI',
                'use_yn' => 'Y',
                'root_url' => $root_url,
				'extradata' => $this->api_key
            ));
            $scfg->import(array(
                'use_erpApi' => 'Y'
            ));
        }
    }

}

if($cfg['erp_bendor']) {
	include_once $engine_dir."/_engine/api/class/erp/{$cfg['erp_bendor']}.api.php";
	eval("\$erpAPI = new {$cfg['erp_bendor']}('');");
}
?>