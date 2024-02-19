<?php

namespace App\Model\Enum;

class FinanceCategory {
    const ELECTRONICS = 'electronics';
    const COURSES = 'courses';
    const MINOR_TANGIBLE = 'minor_tangible';
    const LICENCES = 'licences';
    const OTHERS = 'others';

    public static function getEnum() {
        return [
            self::ELECTRONICS,
            self::COURSES,
            self::MINOR_TANGIBLE,
            self::LICENCES,
            self::OTHERS
        ];
    }

    public static function getNamedEnum() {
        $st = self::getEnum();

        $out = [];
        foreach ($st as $k) {
            $out[$k] = 'main.finance_category.'.$k;
        }

        return $out;
    }

    public static function getTranslatedEnum($translateFnc) {
        $st = self::getNamedEnum();

        foreach ($st as $k => $val) {
            $out[$k] = $translateFnc($val);
        }

        return $out;
    }
}
