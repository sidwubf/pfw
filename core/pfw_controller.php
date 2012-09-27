<?php

class PFW_CONTROLLER {

    public function __construct() {

    }

    public function load_model($model) {
        require_once PFW_MODEL_PATH . $model . EXT;
        $model = strtoupper($model);
        return new $model();
    }

    public function load_template() {
        return new PFWTemplate(PFW_VIEW_PATH);
    }

}