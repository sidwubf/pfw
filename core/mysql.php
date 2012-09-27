<?php

defined('IS_SLAVE') || define('IS_SLAVE', TRUE);

class PFWMysqlException extends PFWException {
    public function __construct($msg , $code) {
        parent::__construct($msg, $code);
    }
}

class DBFactory {
    
    private static $_dbs = array();

    private static $_database = array();
    
    private function __construct() {}

    public static function set_database($database) {
        self::$_database = $database;
    }

    public static function get_instance($db_name, $is_slave=false) {
        if ($is_slave) {
            if (isset(self::$_dbs['slave'][$db_name]) 
                && is_a(self::$_dbs['slave'][$db_name], 'PFWMysql')) {

                return self::$_dbs['slave'][$db_name];
            }
            if (isset(self::$_database['slave'][$db_name])) {
                $db_config = self::$_database['slave'][$db_name];
                $db = new PFWMysql($db_config);
                self::$_dbs['slave'][$db_name] = $db;
                return $db;
            } else {
                return false;
            }
        } else {
            if (isset(self::$_dbs['master'][$db_name]) 
                && is_a(self::$_dbs['master'][$db_name], 'PFWMysql')) {

                return self::$_dbs['master'][$db_name];
            }
            if (isset(self::$_database['master'][$db_name])) {
                $db_config = self::$_database['master'][$db_name];
                $db = new PFWMysql($db_config);
                self::$_dbs['master'][$db_name] = $db;
                return $db;
            } else {
                return false;
            }
        }
    }

}

class PFWMysql {
    private $db;
    private $result = '';

    private $host;
    private $username;
    private $passwd;
    private $dbname;
    private $port;
    private $charset;

    public function __construct($db_config) {
        $this->host = $db_config['host'];
        $this->username = $db_config['user'];
        $this->passwd = $db_config['pass'];
        $this->dbname = $db_config['name'];
        $this->port = $db_config['port'];
        $this->charset = isset($db_config['char']) ? $db_config['char'] : '';
    }

    public function query($query, $result_mode='default', $paramter='') {
        $this->db = new mysqli($this->host, $this->username, $this->passwd, 
                               $this->dbname, $this->port);
        if ($this->db->connect_error) {
            $this->error($query);
            $this->db = new mysqli($this->host, $this->username, $this->passwd, 
                                   $this->dbname, $this->port);
            if ($this->db->connect_error) {
                $this->error($query);
                throw new PFWMysqlException('数据库维护中，请稍后再试', $this->db->errno);
            }
        }
        $this->result = '';
        if ($this->charset)
            $this->db->set_charset($this->charset); 
        $res = $this->db->query($query);
        if ($res === FALSE) {
            throw new PFWMysqlException('数据库维护中，请稍后再试', $this->db->errno);
        }
        switch ($result_mode) {
            case '1':
                $this->result = $res->fetch_row();
                if ($this->result)
                    $this->result = $this->result[0];
                break;
            case 'array':
                $this->result = $res->fetch_array(MYSQLI_ASSOC);
                break;
            case 'row':
                $this->result = $res->fetch_row();
                break;
            case 'all':
                while ($row = $res->fetch_array(MYSQLI_ASSOC))
                    $this->result[] = $row;
                break;
            case 'param':
                while ($row = $res->fetch_array(MYSQLI_ASSOC))
                    $this->result[] = $row[$paramter];
                break;
            case 'id':
                $this->result = $this->db->insert_id;
                break;
            default:
                $this->result = $res;
                break;
        }
        $this->close();
        return $this->result;
    }

    private function close() {
        $this->db->close();
    }

    private function error($sql) {
        return ;
        $log_name = ROOT_PATH . "log/db_error/{$this->host}_" . date('Y-m-d') . '.log';
        $fp = fopen($log_name, "a+");

        $content = date("Y-m-d H:i:s") . " ";
        $content .= $this->host . ":" . $this->port . " ";
        $content .= getenv("REQUEST_URI") . " ";
        $content .= $sql . "\n";

        fwrite($fp, $content);
        fclose($fp);
    }

}