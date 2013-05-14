<?php

class Welcome extends PfwController {

    public function __construct() {

    }

    public function index() {
        $_tpl = $this->get_tpl();
        $_tpl->assign('hello', 'hello world!');
        $_tpl->draw('Welcome');
    }

}