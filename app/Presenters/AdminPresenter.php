<?php

declare(strict_types=1);

namespace App\Presenters;

use Nette\Application\UI\Form;

class AdminPresenter extends BasePresenter
{
    public function __construct(private \App\Model\GrabTheLabModel $grabthelab) {
        parent::__construct();
    }

    public function startup() {
        parent::startup();

        if (!$this->getUser()->isLoggedIn()) {
            $this->flashMessage($this->translator->translate("main.generic.not_signed_in"), "error");
            $this->redirect("Sign:in");
        }

        if (!$this->getUser()->isInRole("admin")) {
            $this->flashMessage($this->translator->translate("main.generic.insufficient_permissions"), "error");
            $this->redirect("Home:");
        }
    }

    public function actionGtlRound() {
        $this->template->activeRound = $this->grabthelab->getActiveRound();
        $this->template->upcomingRound = $this->grabthelab->getUpcomingRound();
        $this->template->pastRounds = $this->grabthelab->getPastRounds();
    }

    public function createComponentGtlRoundComponent() {
        return new \App\Components\GtlRoundComponent();
    }

    public function createComponentRoundEdit() {
        $upcomingRound = $this->grabthelab->getUpcomingRound();

        $form = new Form();

        $form->addDateTime('start', 'Návrhy zasílat od', false)
            ->setDefaultValue($upcomingRound ? $upcomingRound->proposal_start : null)
            ->setRequired('Toto pole je vyžadováno');
        $form->addDateTime('end', 'Návrhy zasílat do', false)
            ->setDefaultValue($upcomingRound ? $upcomingRound->proposal_end : null)
            ->setRequired('Toto pole je vyžadováno');
        $form->addInteger('max', 'Maximální počet návrhů')
            ->setDefaultValue($upcomingRound ? $upcomingRound->max_proposals : null)
            ->addRule(Form::MIN, 'Zadejte číslo od 0', 0)
            ->addRule(Form::INTEGER, 'Zadejte celé číslo');

        $form->addSubmit('submit', 'Nastavit');

        $form->onSuccess[] = [$this, 'roundEditFormSuccess'];

        return $form;
    }

    public function roundEditFormSuccess(Form $form) {
        $vals = $form->values;

        $upcomingRound = $this->grabthelab->getUpcomingRound();

        if ($upcomingRound) {
            $this->grabthelab->updateRound($upcomingRound->id, $vals->start, $vals->end, $vals->max);
        }
        else {
            $this->grabthelab->createRound($vals->start, $vals->end, $vals->max);
        }

        $this->redirect('this');
    }
}
