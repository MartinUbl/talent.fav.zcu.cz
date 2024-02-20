<?php

declare(strict_types=1);

namespace App\Components;

class GtlProposalNav extends \Nette\Application\UI\Control {

    public function __construct() {
    }

    public function render($currentStep) {
        $this->template->currentStep = $currentStep;
	    $this->template->render(__DIR__ . '/GtlProposalNav.latte');
    }

}
