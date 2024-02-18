<?php

declare(strict_types=1);

namespace App\Presenters;

use Nette;

class BasePresenter extends Nette\Application\UI\Presenter
{
    /** @var Nette\Localization\ITranslator @inject */
    public $translator;

    public function handleLogout() {
        if (!$this->getUser()->isLoggedIn()) {
            $this->redirect("this");
        }

        $this->getUser()->logout();
        $this->redirect("this");
    }
}
