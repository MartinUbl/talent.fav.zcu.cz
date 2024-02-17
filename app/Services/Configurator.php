<?php

namespace App;

class Configurator
{  
    public function __construct(
        public string $googleClientId,
        public string $googleClientSecret
    ) {}
}