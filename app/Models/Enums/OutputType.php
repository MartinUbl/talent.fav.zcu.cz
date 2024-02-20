<?php

namespace App\Model\Enum;

class OutputType {
    const SOFTWARE = 'software';
    const HARDWARE = 'hardware';
    const METHOD = 'method';
    const PROCEDURE = 'procedure';
    const LITERATURE = 'literature';
    const OTHERS = 'others';

    public static function getEnum() {
        return [
            self::SOFTWARE,
            self::HARDWARE,
            self::METHOD,
            self::PROCEDURE,
            self::LITERATURE,
            self::OTHERS
        ];
    }

    public static function getNamedEnum() {
        $st = self::getEnum();

        $out = [];
        foreach ($st as $k) {
            $out[$k] = 'main.output_type.'.$k;
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
