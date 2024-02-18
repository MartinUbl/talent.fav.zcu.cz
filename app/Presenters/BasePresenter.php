<?php

declare(strict_types=1);

namespace App\Presenters;

use Nette;

class BasePresenter extends Nette\Application\UI\Presenter
{
    /** @var \Contributte\Translation\Translator @inject */
    public $translator;

    /** @var \Contributte\Translation\LocalesResolvers\Session @inject */
	public $translatorSessionResolver;

    public function startup() {
        parent::startup();

        $this->template->lang = $this->translator->getLocale();
    }

	public function handleChangeLocale(string $lang): void
	{
		$this->translatorSessionResolver->setLocale($lang);
		$this->redirect('this');
	}

    public function handleLogout() {
        if (!$this->getUser()->isLoggedIn()) {
            $this->redirect("this");
        }

        $this->getUser()->logout();
        $this->redirect("this");
    }
}
