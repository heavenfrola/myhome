<?PHP $wec = new weagleEyeClient($_we, 'account'); $return = $wec->call('setPG', array_merge($_POST, array('root_url'=>$root_url, 'host'=>$_SERVER['SERVER_ADDR']))); if($return != 'true') { alert(php2java($return)); exit; } ?>