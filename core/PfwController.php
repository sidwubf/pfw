<?php

class PfwController {

    public function __construct() {

    }

    protected $tpl = null;
    protected function get_tpl() {
        if ($this->tpl == null
            || ($this->tpl instanceof Tpl) == false) {
            Loader::core('Tpl');
            Tpl::$tpl_dir = PFW_VIEW_PATH;
            Tpl::$cache_dir = PFW_TMP_PATH;
            Tpl::$php_enabled = true;
            Tpl::$base_url  = 'http://' . $_SERVER['SERVER_NAME'] . '/';
            $this->tpl = new Tpl();
        }
        return $this->tpl;
    }

}