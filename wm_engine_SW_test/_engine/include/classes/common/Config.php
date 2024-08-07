<?php

namespace Wing\common;

use Wing\DB\PDODatabase;

/**
 * Smartwing config class
 **/

class Config
{

    private $config = array();
    private $pdo;
    private $table;

    public function __construct(PDODatabase &$pdo, $table, $container)
    {
        $this->config = &$GLOBALS[$container];
        $this->pdo = $pdo;
        $this->table = $table;

		$cfgs = $this->pdo->iterator("select name, value from $table");
		foreach($cfgs as $cfgdata) {
			$this->config[$cfgdata['name']] = stripslashes($cfgdata['value']);
		}
    }

    /**
     * 설정 값을 가져온다
     *
     * @name string 가져올 설정값의 이름
     **/
    public function get($name)
    {
        return (isset($this->config[$name]) == true) ? $this->config[$name] : null;
    }

    /**
     * 설정 값이 맞는지 비교한다
     *
     * @name string 비교할 설정의 이름
     *       array 여러 개의 설정을 일괄 비교
     * @comp string 비교할 값. 없을 경우 값이 있는지 여부를 리턴
     **/
    public function comp($name, $comp = null)
    {
        if(is_array($name)) { // 인자가 배열일 경우 재귀호출로 일괄 비교
            foreach ($name as $key => $val) {
                if ($this->comp($key, $val) == false) return false;
            }
            return true;
        }

        $value = (isset($this->config[$name]) == true) ? $this->config[$name] : null;
        return (
            (is_null($comp) == false && $value === $comp) // 값이 일치하는지 확인
            || (is_null($comp) == true && isset($this->config[$name]) == true && empty($value) == false && count(func_get_args()) == 1) // 값이 있는지 확인
            || (is_array($comp) == true && in_array($value, $comp) == true) // or 조건으로 값이 일치하는지 확인
        ) ? true : false;
    }

    /**
     * 설정 값을 변경한다. (변경한 값은 임시로 적용 되며, 영구적으로 적용하려면 import 명령을 이용한다.)
     *
     * @name string 변경할 설정의 이름
     * @value string 변경할 설정값
     **/
    public function set($name, $value = null)
    {
        if(empty($value) == true) {
            unset($this->config[$name]);
            return true;
        }
        $this->config[$name] = $value;
        return true;
    }

    /**
     * 기본 값을 설정한다. 이미 값이 있을 경우 변경하지 않는다.
     *
     * @name string 변경할 설정의 이름
     * @value string 변경할 설정값
     **/
    public function def($name, $value = null)
    {
        if (is_array($name) == true) {
            foreach ($name as $_name => $_value) {
                $this->def($_name, $_value);
            }
        } else {
            if (isset($this->config[$name]) == false) {
                return $this->set($name, $value);
            }
            return false;
        }
    }

    /**
     * 배열로부터 값을 변경한다
     *
     * @name array 이름=>값 형태로 된 배열 데이터
     **/
    public function import($data)
    {
        $admin_id = (isset($GLOBALS['admin']['admin_id']) == true) ? $GLOBALS['admin']['admin_id'] : '';
        foreach ($data as $key => $val) {
            if(is_array($val) == true || $key == 'body' || $key == 'config_code' || $key == 'exec') continue;

            $val = addslashes($val);
            $param = array(
                ':key' => $key,
                ':val' => $val,
                ':admin_id' => $admin_id
            );
            $this->set($key, $val);

            if ($this->pdo->row("select count(*) from $this->table where name=:key", array(':key' => $key)) > 0) {
                $this->pdo->query("update $this->table set value=:val, edt_date=unix_timestamp(now()), admin_id=:admin_id where name=:key", $param);
            } else {
                if(is_null($val) == false) {
                    $this->pdo->query("
                        insert into $this->table (name, value, reg_date, edt_date, admin_id)
                        values (:key, :val, unix_timestamp(now()), unix_timestamp(now()), :admin_id)",
                        $param
                    );
                }
            }
        }
    }

    /**
     * 설정 데이터베이스에서 설정을 직접 삭제한다.
     *
     * @name string 삭제할 설정의 이름
     **/
    public function remove($name)
    {
        if(isset($this->config[$name]) == true) {
            unset($this->config[$name]);
            $this->pdo->query("delete from $this->table where name=:name", array(':name' => $name));
            return ($this->pdo->lastRowCount() > 0) ? true : false;
        }
        return false;
    }

    /**
     * 설정 내용을 배열로 내보낸다.
     **/
    public function export()
    {
        return $this->config;
    }

}

?>