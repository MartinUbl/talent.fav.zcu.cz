<?php

namespace App\Model\Enum;

class ProjectLength {

    public static function getEnum() {
        return [3, 4, 6, 8];
    }

    public static function getNamedEnum() {
        $st = self::getEnum();

        $out = [];
        foreach ($st as $k) {
            $out[$k] = 'main.grabthelab.projectlength';
        }

        return $out;
    }

    public static function getTranslatedEnum($translateFnc) {
        $st = self::getNamedEnum();

        foreach ($st as $k => $val) {
            $out[$k] = $translateFnc($val, $k);
        }

        return $out;
    }
}
