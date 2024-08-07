<?PHP

namespace Wing\DB;

use PDO;
use PDOStatement;
use PDOException;
use Wing\common\ErrorReport;
use Wing\DB\PDOIterator;

Class PDODatabase
{

	private $pdo;
    private $dbinfo;
    private $charset;
    private $res;
	private $qry;
	private $errmsg;
	private $errcode;
	private $last_id;

	public function __construct($dbinfo, $charset = 'utf8')
    {
        $this->dbinfo = $dbinfo;
        $this->charset = $charset;
        $this->driver = $dbinfo['driver'];
        $options = array();

        switch($dbinfo['driver']) {
            case 'mysql' :
        		$dsn = $dbinfo['driver'].':host='.$dbinfo['host'].';dbname='.$dbinfo['db'].';';
                $options = array(
                    PDO::MYSQL_ATTR_INIT_COMMAND => 'SET NAMES '.$charset,
                );
                break;
            case 'oci' :
                $dsn = $dbinfo['driver'].':dbname='.$dbinfo['host'].'/'.$dbinfo['db'].';charset='.$charset;
                break;
            case 'pgsql' :
                $dsn = $dbinfo['driver'].':host='.$dbinfo['host'];
                if ($dbinfo['port']) $dsn .= ' port='.$dbinfo['port'];
                if ($dbinfo['db']) $dsn .= ' dbname='.$dbinfo['db'];
                if ($dbinfo['user']) $dsn .= ' user='.$dbinfo['user'];
                if ($dbinfo['password']) $dsn .= ' password='.$dbinfo['password'];
                break;
        }

		$username = $dbinfo['user'];
		$password = $dbinfo['password'];

		try {
			$this->pdo = new PDO($dsn, $username, $password, $options);
		} catch(PDOException $e) {
            switch($e->getCode()) {
                case '1049' :
                    $message = '(1049) 현재 운영이 중단된 쇼핑몰입니다.';
                break;
                default;
                    $message = 'DB connect Error : '.$e->getMessage();
            }
			exit($message);
		}
	}

    public function ping()
    {
        $this->row("select 1");
        if ($this->geterror()) {
            unset($this->pdo);
            $this->__construct($this->dbinfo, $this->charset);
        }
    }

	public function query($qry, $param = array())
    {
		$this->qry = $qry;
		$this->errcode = null;
		$this->errmsg = null;

        $this->writelog($qry, $param);

		try {
            $driver = (preg_match('/^select/i', trim($qry)) == true) ? array(PDO::ATTR_CURSOR => PDO::CURSOR_SCROLL) : array();
			$res = $this->pdo->prepare($this->qry, $driver);
			if ($res->execute($param) == false) {
				$err = $res->errorInfo();
				$this->errcode = $err[1];
				$this->errmsg = $err[2];

                $this->writelog('[error] '.$this->errmsg);

                throw new \Exception($this->errmsg, $this->errcode);

				return false;
			}

            if ($this->driver != 'oci') {
    			$this->last_id = $this->pdo->lastInsertId();
            }

			$this->res = $res;
        } catch(PDOException $e) {
            $exception = explode('\\', get_class($e));

            ErrorReport::__report(
                array_shift($exception),
                $e->getMessage(),
                $e->getCode(),
                $e->getTrace(),
                $this->qry
            );

            exit($e->getMessage());
        } catch (\Exception $e) {
            ErrorReport::__report(
                'PDOException',
                $e->getMessage(),
                $e->getCode(),
                $e->getTrace(),
                $this->qry
            );

            return false;
        }

		return $res;
	}

	public function iterator($qry, $param = array())
    {
		$res = $this->query($qry, $param);

		if ($res instanceof PDOStatement == true) {
			return new PDOIterator($res, PDO::FETCH_ASSOC);
		}
		return false;
	}

	public function object($qry, $param = array())
    {
		$res = $this->query($qry, $param);

		if ($res instanceof PDOStatement == true) {
			return $res->fetchAll(PDO::FETCH_OBJ);
		}
		return false;
	}

	public function assoc($qry, $param = array())
    {
		$res = $this->query($qry, $param);
		if (!$res) return false;

		$data = $res->fetch(PDO::FETCH_ASSOC);
		return $data;
	}

	public function fetch($qry, $param = array())
    {
		$res = $this->query($qry, $param);
		if (!$res) return false;

		$data = $res->fetch(PDO::FETCH_BOTH);
		return $data;
	}

	public function row($qry, $param = array())
    {
		$res = $this->query($qry, $param);
		if (!$res) return false;

		$data = $res->fetchColumn(0);

		return $data;
	}

	public function loop($fetch = PDO::FETCH_ASSOC)
    {
		if (is_object($this->res) == false) return false;

		return $this->res->fetch($fetch);
	}

	public function getError()
    {
		return $this->errmsg;
	}

	public function getQry($print = false)
    {
		return $this->qry;
	}

    public function lastInsertId() {
        return $this->last_id;
    }

    public function rowCount($qry, $param = array())
    {
        $res = $this->query($qry, $param);
        if ($res instanceof PDOStatement) {
            return $res->rowCount();
        }
        return false;
    }

    public function lastRowCount() {
        if (isset($this->res) == false || $this->res instanceof PDOStatement == false)
        {
            return 0;
        }
        return $this->res->rowCount();
    }

    private function writelog($log, $params = null)
    {
        global $log_instance;

        if (is_object($log_instance) == true) {
            if (is_array($params) == true && count($params) > 0) {
                $log .= "\n".print_r($params, true);
            }
            $log_instance->writeln(trim($log));
        }
    }

}

?>