<?php

declare(strict_types=1);

namespace App\Presenters;

use Nette\Application\UI\Form;

class AdminPresenter extends BasePresenter
{
    public function __construct(private \App\Model\GrabTheLabModel $grabthelab, private \App\Model\UserModel $users, \App\Configurator $configurator) {
        parent::__construct($configurator);
    }

    public function isManager() {
        return $this->getUser()->isLoggedIn() && ($this->getUser()->isInRole("manager") || $this->getUser()->isInRole("admin"));
    }

    public function isAdministrator() {
        return $this->getUser()->isLoggedIn() && $this->getUser()->isInRole("admin");
    }

    public function startup() {
        parent::startup();

        if (!$this->getUser()->isLoggedIn()) {
            $this->flashMessage($this->translator->translate("main.generic.not_signed_in"), "error");
            $this->redirect("Sign:in");
        }

        if (!$this->isManager()) {
            $this->flashMessage($this->translator->translate("main.generic.insufficient_permissions"), "error");
            $this->redirect("Home:");
        }

        $this->template->isManager = $this->isManager();
        $this->template->isAdministrator = $this->isAdministrator();
    }

    public function actionGtlRound() {

        if (!$this->isAdministrator()) {
            $this->flashMessage('Na tohle nemáte dostatečná práva!', 'error');
            $this->redirect('Admin:');
        }

        $this->template->activeRound = $this->grabthelab->getActiveRound();
        $this->template->upcomingRound = $this->grabthelab->getUpcomingRound();
        $this->template->pastRounds = $this->grabthelab->getPastRounds()->order('id DESC');
    }

    public function actionGtlProposals() {

        $activeRound = $this->grabthelab->getActiveRound();

        $this->template->activeRound = $activeRound;
        $this->template->pastRounds = $this->grabthelab->getPastRounds()->order('id DESC');
    }

    public function actionGtlProposalsList($id) {
        $round = $this->grabthelab->getRoundById($id);

        $this->template->round = $round;
        if ($round) {
            $this->template->roundProjects = $this->grabthelab->getRoundProposedProjects($round->id);
        }
        else {
            $this->template->roundProjects = [];
        }
    }

    public function actionUserList() {
        $this->template->users = $this->users->getUsers();
    }

    private $detailsUserId = null;

    public function actionUserDetails($id) {
        if (!$this->isAdministrator()) {
            $this->redirect("Admin:");
        }

        if ($this->getUser()->getId() === $id) {
            $this->redirect("Admin:");
        }

        $this->detailsUserId = $id;
        $this->template->loadedUser = $this->users->getUserById($id);

        if (!$this->template->loadedUser) {
            $this->redirect("Admin:");
        }
    }

    public function handleChangeRole($role) {
        if (!$this->detailsUserId || !$this->isAdministrator() || $this->getUser()->getId() === $this->detailsUserId || $role === 'admin') {
            $this->terminate();
        }

        if ($role !== 'user' && $role !== 'manager') {
            $this->terminate();
        }

        $this->users->setUserRole($this->detailsUserId, $role);

        $this->redirect('this');
    }

    public function handleExportProjectPdf($project_id) {

        $proposed = $this->grabthelab->getProjectById($project_id);
        if (!$proposed) {
            $this->redirect('this');
            return;
        }

        $this->createPdfWithProject($proposed, false);
    }

    public function handleShowProjectPdf($project_id) {

        $proposed = $this->grabthelab->getProjectById($project_id);
        if (!$proposed) {
            $this->redirect('this');
            return;
        }

        $this->createPdfWithProject($proposed, true);
    }

    public function createComponentGtlRoundComponent() {
        return new \App\Components\GtlRoundComponent($this->grabthelab, null);
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
