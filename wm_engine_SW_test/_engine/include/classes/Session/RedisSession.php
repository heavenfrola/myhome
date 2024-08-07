<?php

namespace Wing\Session;

/*
* Redis DB 세션 핸들러 클래스
**/

class RedisSession extends WingSession
{
    private $db;
    private $session_name;

    public function __construct($config)
    {
        $config = explode(':', $config);
        if (count($config) == 3) {
            $config[0] = $config[0].':'.$config[1];
            $config[1] = $config[2];
        }
        $this->db = new \Redis();
        $this->db->connect($config[0], $config[1]);

        $this->init();
    }

    public function open($savePath, $sessionName)
    {
        return true;
    }

    public function close() {
        return true;
    }

    public function read($id)
    {
        $this->id = $id;
        $data = $this->parse($id);
        return (string) $this->db->get($id);
    }

    public function write($id, $data)
    {
        return $this->db->setex($id, (int) ini_get('session.gc_maxlifetime'), $data);
    }

    public function destroy($id) {}

    public function gc($maxlifetime) {}

    public function exists($id) {
        return $this->db->exists($id);
    }

    public function parse($id) {
        $data = $this->db->get($id);
        return $this->unserialize($data);
    }

    public function setDuplicate($var)
    {
        $data = $this->parse($this->id);
        $this->write($var.'_'.$data['admin_no'], $this->id);

        // redis 미지원
        return true;
    }

    public function checkDuplicate($var)
    {
        $data = $this->parse($this->id);
        $check = $this->db->get($var.'_'.$data['admin_no']);

        if ($check && $this->id != $check) {
            unset($_SESSION[$var]);
            return false;
        }

        // redis 미지원
        return true;
    }
}

?>