<?php

class PFW {

    private function __construct() {

    }

    public static function run() {
        Loader::core('PfwController');
        self::_run_controller();
    }

    private static function _run_controller() {
        $_controller = self::_get_controller();
        $_class = Loader::controller($_controller);
        $_function = self::_get_function();
        if (method_exists($_class, $_function)) {
            $_obj = new $_class();
            $_obj->$_function();
        } else {
            throw new Exception("no such function {$_function} of class {$_class}");
        }
    }

    private static function _get_controller() {
        return isset($_GET['c']) ? addslashes($_GET['c']) : 'Welcome';
    }

    private static function _get_function() {
        return isset($_GET['f']) ? addslashes($_GET['f']) : 'index';
    }

}
