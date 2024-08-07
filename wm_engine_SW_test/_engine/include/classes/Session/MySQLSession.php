<?php

namespace Wing\Session;

use Wing\DB\PDODatabase;

/*
* MySQL DB 세션 핸들러 클래스
**/

class MySQLSession extends WingSession
{
    public $db;

    public function __construct($charset = 'utf8')
    {
        global $cfg, $con_info, $pdo;

        if (isset($cfg['session_host']) == true) {
            $session_db = new PDODatabase(array(
                'driver' => 'mysql',
                'host' => $cfg['session_host'],
                'user' => $con_info[2],
                'password' => $con_info[3],
                'db' => $con_info[4],
            ), $charset);
        } else {
            $session_db = $pdo;
        }
        $this->db = $session_db;

        $this->init();
    }

    public function open($savePath, $sessionName)
    {
        return true;
    }

    public function close() {
        return true;
    }

    public function read($id, $field = 'data')
    {
        $session = $this->db->row("select $field from wm_session where session_id=?", array(
            $id
        ));
        if ($session == false) $session = '';

        return $session;
    }

    public function write($id, $data)
    {
        $now = time();
        $page = addslashes($_SERVER['REQUEST_URI']);
        $serialized = $this->unserialize($data);
        $member_no = (int) $serialized['member_no'];
        $admin_no = (int) $serialized['admin_no'];

        $this->db->ping();

        if ($this->db->row("select count(*) from wm_session where session_id='$id'") > 0)
        {
            $result = $this->db->query("
                update wm_session set
                    accesstime='$now', data=:data, page='$page', admin_no='$admin_no', member_no='$member_no'
                    where session_id='$id'
            ", array(
                ':data' => $data
            ));
        } else {
            $result = $this->db->query("
                insert into wm_session (session_id, data, remote_addr, page, admin_no, member_no, regdate, accesstime)
                values ('$id', :data, '$_SERVER[REMOTE_ADDR]', '$page', '$admin_no', '$member_no', '$now', '$now')"
            , array(
                ':data' => $data
            ));
        }
        return ($result != null);
    }

    public function destroy($id)
    {
        $result = @$this->db->query("delete from wm_session where session_id='$id'");
        return $result;
    }

    public function gc($maxlifetime)
    {
        $expire_time = time()-$maxlifetime;
        $result = @$this->db->query("delete from wm_session where accesstime < '$expire_time'");
        return $result;
    }

    public function exists($id) {
        $r = $this->db->row("select count(*) from wm_session where session_id=:id", array(
            ':id' => $id
        ));
        return ($r > 0);
    }

    public function parse($id) {
        $data = $this->db->row("select data from wm_session where session_id=:id", array(
            ':id' => $id
        ));
        return $this->unserialize($data);
    }

    public function setDuplicate($var)
    {
        $var = addslashes($var);
        if ($var != 'admin_no' && $var != 'member_no') {
            return false;
        }
        $no = $_SESSION[$var];
        $this->db->query("update wm_session set `$var`=0 where session_id!=? and `$var`=?", array(
            session_id(), $no
        ));
        return $this->db->lastRowCount();
    }

    public function checkDuplicate($var)
    {
        $var = addslashes($var);
        if ($var != 'admin_no' && $var != 'member_no') {
            return false;
        }

        $admin_no = $this->db->row("select `$var` from wm_session where session_id=:id", array(
            ':id' => session_id()
        ));
        if ($admin_no != $_SESSION[$var]) {
            unset($_SESSION[$var]);
            return false;
        }
        return true;
    }

}

?>