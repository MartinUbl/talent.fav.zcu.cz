<?php

declare(strict_types=1);

namespace App\Components;

class GtlRoundComponent extends \Nette\Application\UI\Control {

    public function __construct() {
    }

    public function render($roundRecord) {
        $this->template->round = $roundRecord;
	    $this->template->render(__DIR__ . '/GtlRoundComponent.latte');
    }

}