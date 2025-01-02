<?php

declare(strict_types=1);

namespace App\Components;

class GtlProjectDisplayComponent extends \Nette\Application\UI\Control {

    public function __construct(private \App\Model\GrabTheLabModel $grabthelab, private $userId) {
    }

    public function render($name, $desc, $team, $supuntil) {
        $this->template->name = $name;
        $this->template->desc = $desc;
        $this->template->team = $team;
        $this->template->supuntil = $supuntil;
	    $this->template->render(__DIR__ . '/GtlProjectDisplayComponent.latte');
    }

}
