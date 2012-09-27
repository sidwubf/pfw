<?php

class MARK {

    private $mark = array();

    public function __construct() {

    }

    public function mark($key) {
        $this->mark[$key] = microtime(TRUE);
    }

    public function diff($from, $to) {
        return $this->mark[$to] - $this->mark[$from];
    }

}