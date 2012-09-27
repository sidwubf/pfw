<?php

class WELCOME extends PFW_CONTROLLER {

    public function __construct() {
    }

    public function index() {
        $runtime = 111;
        $view = $this->load_template();
        $view->files('welcome');
        $view->set('runtime', $runtime);
        $view->output();
    }

}