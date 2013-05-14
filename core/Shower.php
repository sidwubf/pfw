<?php

class Shower {

    private function __contruct() {

    }

    public static function pre($something) {
        echo "<pre>";
        print_r($something);
        echo "</pre>";
        exit;
    }

    public static function vd($something) {
        echo "<pre>";
        var_dump($something);
        echo "<pre>";
        exit;
    }

}