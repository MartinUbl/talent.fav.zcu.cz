<?php

declare(strict_types=1);

namespace App\Components;

class GtlRoundComponent extends \Nette\Application\UI\Control {

    public function __construct(private \App\Model\GrabTheLabModel $grabthelab, private $userId) {
    }

    public function render($roundRecord) {

        if ($this->userId) {
            $hasDraft = $this->grabthelab->getProjectDraft($this->userId) ? true : false;
            $hasProposed = $this->grabthelab->getProjectProposed($this->userId) ? true : false;
        }
        else {
            $hasDraft = false;
            $hasProposed = false;
        }

        $this->template->round = $roundRecord;
        $this->template->hasDraft = $hasDraft;
        $this->template->hasProposed = $hasProposed;
	    $this->template->render(__DIR__ . '/GtlRoundComponent.latte');
    }

}
