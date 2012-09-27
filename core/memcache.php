<?php

defined('MEMCACHE_COMPRESS_MAX_TIME') || define('MEMCACHE_COMPRESS_MAX_TIME', 86400);

class MCFactory {
    
    private static $_mcs = array();

    private static $_memcache = array();
    
    private function __construct() {}

    public static function get_memcache($memcache) {
        self::$_memcache = $memcache;
    }

    public static function get_instance($mc_name) {
        if (isset(self::$_mcs[$mc_name]) 
            && is_a(self::$_mcs[$mc_name], 'PFWMemcache')) {

            return self::$_mcs[$mc_name];
        }

        if (isset(self::$_memcache[$mc_name])) {
            $mc_config = self::$_memcache[$mc_name];
            $mc = new LibaMemcache($mc_config);
            self::$_mcs[$mc_name] = $mc;
            return $mc;
        } else {
            return false;
        }
    }

}

class PFWMemcache {
    private $mc;

    private $host;
    private $port;

    public function __construct($mc_config) {
        $this->host = $mc_config['host'];
        $this->port = $mc_config['port'];
    }

    public function __destruct() {

    }

    private function connect() {
        $this->mc = new Memcache;
        $this->mc->connect($this->host, $this->port);
    }

    private function close() {
        $this->mc->close();
    }

    public function set($key, $var, $expire=0) {
        if (!$expire || $expire > MEMCACHE_COMPRESS_MAX_TIME)
            $expire = MEMCACHE_COMPRESS_MAX_TIME;
        $this->connect();
        $this->mc->set($key, $var, MEMCACHE_COMPRESSED, $expire);
        $this->close();
    }

    public function get($key) {
        $this->connect();
        $var = $this->mc->get($key);
        $this->close();
        return $var;
    }

    public function delete($key) {
        $this->connect();
        $this->mc->delete($key);
        $this->close();
    }

    public function flush() {
        $this->connect();
        $this->mc->flush();
        $this->close();
    }

    public function increment($key, $value=1) {
        $this->connect();
        $this->mc->increment($key, $value);
        $this->close();
    }

    public function status() {
        $this->connect();
        $status = $this->mc->getExtendedStats();
        $this->close();
        return $status;
    }

}

