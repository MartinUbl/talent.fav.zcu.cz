<?php

declare(strict_types=1);

namespace App\Presenters;

use Nette;

final class HomePresenter extends BasePresenter
{
    public function __construct(private \App\Model\GrabTheLabModel $grabthelab) {
        parent::__construct();
    }

    public function actionDefault() {
        $this->template->gtlActiveRound = $this->grabthelab->getActiveRound();
    }
}
