<?php

class PFW {
    
    private $controller = 'welcome';

    private $function = 'index';

    public function __construct() {

    }

    public function get_controller() {
        $this->controller = isset($_GET['c']) ? strtolower(addslashes($_GET['c'])) : 'welcome';
        return $this->controller;
    }

    public function get_function() {
        $this->function = isset($_GET['f']) ? strtolower(addslashes($_GET['f'])) : 'index';
        return $this->function;
    }

}
