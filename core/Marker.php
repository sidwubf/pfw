<?php

class Marker {

    private static $_mark = array();

    private function __construct() {

    }

    public static function mark($key) {
        self::$_mark[$key][] = microtime(true);
    }

    public static function get($key) {
        return isset(self::$_mark[$key]) ? self::$_mark[$key] : array();
    }

}