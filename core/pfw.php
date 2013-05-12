<?php

class PFW {

    private function __construct() {

    }

    public static function run() {
        $_view_path = self::_run_controller();
        self::_run_view($_view_path);
    }

    private static function _run_controller() {
        $_controller = self::_get_controller();
        $_class = Loader::controller($_controller);
        $_function = self::_get_function();
        if (method_exists($_class, $_function)) {
            $_obj = new $_class();
            $_view = $_obj->$_function();
            return $_view;
        } else {
            throw new Exception("no such function {$_function} of class {$_class}");
        }
    }

    private static function _run_view($view_path) {
        Loader::core('template');
        $_template = new Template($view_path);

    }

    private static function _get_controller() {
        return isset($_GET['c']) ? addslashes($_GET['c']) : 'Welcome';
    }

    private static function _get_function() {
        return isset($_GET['f']) ? addslashes($_GET['f']) : 'index';
    }

}
