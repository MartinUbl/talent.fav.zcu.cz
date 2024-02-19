<?php

namespace App\Model\Enum;

class ProjectScope {
    const PHYSICS = 'physics';
    const GEOMATICS = 'geomatics';
    const INFORMATICS = 'informatics';
    const CYBERNETICS = 'cybernetics';
    const MATHEMATICS = 'mathematics';
    const MECHANICS = 'mechanics';
    const BUILDINGS = 'buildings';

    public static function getEnum() {
        return [
            self::PHYSICS,
            self::GEOMATICS,
            self::INFORMATICS,
            self::CYBERNETICS,
            self::MATHEMATICS,
            self::MECHANICS,
            self::BUILDINGS
        ];
    }

    public static function getNamedEnum() {
        $st = self::getEnum();

        $out = [];
        foreach ($st as $k) {
            $out[$k] = 'main.scope.'.$k;
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
