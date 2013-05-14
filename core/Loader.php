<?php

class Loader {

    const separator_underscore = '_';
    const separator_slash = '/';
    const php_ext = '.php';
    const html_ext = '.html';

    private function __construct() {

    }

    private static $_loader_array = array();

    public static function get() {
        return self::$_loader_array;
    }

    public static function core($path) {
        $_key = PFW_CORE_PATH . $path;
        if (isset(self::$_loader_array[$_key])
            && self::$_loader_array[$_key]) {
            return self::$_loader_array[$_key];
        } else {
            $_class = self::_include($path, PFW_CORE_PATH, self::php_ext);
            self::$_loader_array[$_key] = $_class;
            return $_class;
        }
    }

    public static function controller($path) {
        $_key = PFW_CONTROLLER_PATH . $path;
        if (isset(self::$_loader_array[$_key])
            && self::$_loader_array[$_key]) {
            return self::$_loader_array[$_key];
        } else {
            $_class = self::_include($path, PFW_CONTROLLER_PATH, self::php_ext);
            self::$_loader_array[$_key] = $_class;
            return $_class;
        }
    }

    public static function view($path) {
        $_key = PFW_VIEW_PATH . $path;
        if (isset(self::$_loader_array[$_key])
            && self::$_loader_array[$_key]) {
            return self::$_loader_array[$_key];
        } else {
            $_class = self::_include($path, PFW_VIEW_PATH, self::html_ext);
            self::$_loader_array[$_key] = $_class;
        }
    }

    private static function _include($path, $pre_path, $ext) {
        if (!$path) {
            throw new Exception('path cannot be null!');
        }

        $_path_array = explode(self::separator_underscore, $path);
        $_path_count = count($_path_array);
        if (is_array($_path_array) == false
            || $_path_count <= 0) {
            throw new Exception("no such path as {$path}!");
        }

        $file = implode(self::separator_slash, $_path_array);
        include($pre_path . $file . $ext);

        $_class_name_key = $_path_count - 1;
        if (isset($_path_array[$_class_name_key])
            && $_path_array[$_class_name_key]) {
            return $_path_array[$_class_name_key];
        } else {
            throw new Exception("no such class as {$path}!");
        }
    }

}