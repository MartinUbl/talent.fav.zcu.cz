<?php

declare(strict_types=1);

namespace App\Presenters;

use Nette;

final class MentoringPresenter extends BasePresenter
{
    public function createComponentDisplayBlockComponent() {
        return new \App\Components\DisplayBlockComponent($this->translator);
    }
}
