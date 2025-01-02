<?php

declare(strict_types=1);

namespace App\Presenters;

use Nette\Application\UI\Form;

final class GrabTheLabPresenter extends BasePresenter
{
    public function __construct(private \App\Model\GrabTheLabModel $grabthelab, \App\Configurator $configurator) {
        parent::__construct($configurator);
    }

    public function actionDefault() {
        $this->template->activeRound = $this->grabthelab->getActiveRound();
        $this->template->upcomingRound = $this->grabthelab->getUpcomingRound();
    }

    public function actionProposalCreate() {
        if (!$this->getUser()->isLoggedIn()) {
            $this->flashMessage($this->translator->translate('_main.generic.not_signed_in'), 'error');
            $this->redirect('Sign:in');
        }

        $cur = $this->grabthelab->getProjectDraft($this->getUser()->id);
        if ($cur) {
            $this->redirect('GrabTheLab:proposalEdit');
        }
    }

    public function actionProposalEdit($step = 1) {
        if (!$this->getUser()->isLoggedIn()) {
            $this->flashMessage($this->translator->translate('main.generic.not_signed_in'), 'error');
            $this->redirect('Sign:in');
        }

        $cur = $this->grabthelab->getProjectDraft($this->getUser()->id);
        if (!$cur) {
            $this->flashMessage($this->translator->translate('main.generic.no_proposal_active'), 'error');
            $this->redirect('GrabTheLab:proposalCreate');
        }

        $this->template->step = $step;
    }

    public function actionProposalComplete() {
        if (!$this->getUser()->isLoggedIn()) {
            $this->flashMessage($this->translator->translate('main.generic.not_signed_in'), 'error');
            $this->redirect('Sign:in');
        }

        $cur = $this->grabthelab->getProjectDraft($this->getUser()->id);
        if (!$cur) {
            $this->flashMessage($this->translator->translate('main.generic.no_proposal_active'), 'error');
            $this->redirect('GrabTheLab:proposalCreate');
        }

        $proposalData = json_decode($cur->data, true);

        $this->template->proposal = $cur;
        $this->template->proposalData = $proposalData;

        $validateResult = $this->validateProjectFields($proposalData);

        $this->template->missing = $validateResult;
        $this->template->isReady = empty($validateResult);
    }

    public function actionProposalSent() {
        if (!$this->getUser()->isLoggedIn()) {
            $this->flashMessage($this->translator->translate('main.generic.not_signed_in'), 'error');
            $this->redirect('Sign:in');
        }

        $cur = $this->grabthelab->getProjectProposed($this->getUser()->id);
        if (!$cur) {
            $this->flashMessage($this->translator->translate('main.generic.no_proposal_active'), 'error');
            $this->redirect('GrabTheLab:proposalCreate');
        }

        $proposalData = json_decode($cur->data, true);

        $this->template->proposal = $cur;
        $this->template->proposalData = $proposalData;
    }

    public function createComponentGtlRoundComponent() {
        return new \App\Components\GtlRoundComponent($this->grabthelab, $this->getUser()->id);
    }

    public function createComponentGtlProjectDisplayComponent() {
        return new \App\Components\GtlProjectDisplayComponent($this->grabthelab, $this->getUser()->id);
    }

    public function createComponentGtlProposalNav() {
        return new \App\Components\GtlProposalNav();
    }

    public function createComponentNewProjectForm() {
        $form = new Form();

        $form->addText('project_name', $this->translator->translate('main.grabthelab.form.project_name'))
            ->addRule(Form::MIN_LENGTH, $this->translator->translate('main.grabthelab.form.namelen_alert', ['min' => 12, 'max' => 64]), 12)
            ->addRule(Form::MAX_LENGTH, $this->translator->translate('main.grabthelab.form.namelen_alert', ['min' => 12, 'max' => 64]), 64)
            ->setRequired($this->translator->translate('main.grabthelab.form.mandatory'));

        $form->addSubmit('submit', $this->translator->translate('main.grabthelab.form.continue'));

        $form->onSuccess[] = [$this, 'newProjectFormSuccess'];

        return $form;
    }

    protected function validateProjectFields($data) : array {

        $mandatoryFields = [
            'project_name' => 1,
            'scope' => 1, 
            'motivation' => 1, 
            'methods' => 1, 
            'anotation' => 1, 
            'length' => 1, 
            'contact_person_name' => 1, 
            'contact_person_email' => 1, 
            'member_name_0' => 2, 
            'member_name_1' => 2, 
            'finance_items' => 3, 
            'phases' => 4, 
            'outputs' => 5
        ];

        $missing = [];

        foreach ($mandatoryFields as $mf => $step) {
            if (!isset($data[$mf]) || empty($data[$mf])) {
                $missing[$mf] = [
                    'message' => $this->translator->translate('main.grabthelab.missing.'.$mf),
                    'step' => $step
                ];
            }
        }

        return $missing;
    }

    public function newProjectFormSuccess(Form $form) {
        $vals = $form->values;

        $this->grabthelab->createProject($this->getUser()->id, [
            'project_name' => $vals->project_name
        ]);

        $this->redirect('GrabTheLab:proposalEdit');
    }

    public function createComponentEditProject1Form() {
        $form = new Form();

        $proj = json_decode($this->grabthelab->getProjectDraft($this->getUser()->id)->data, true);
        $guard = function($fld, $default = "") use ($proj) {
            if (isset($proj[$fld])) {
                return $proj[$fld];
            }
            return $default;
        };

        $form->addText('project_name', $this->translator->translate('main.grabthelab.form.project_name'))
            ->addRule(Form::MIN_LENGTH, $this->translator->translate('main.grabthelab.form.namelen_alert', ['min' => 12, 'max' => 64]), 12)
            ->addRule(Form::MAX_LENGTH, $this->translator->translate('main.grabthelab.form.namelen_alert', ['min' => 12, 'max' => 64]), 64)
            ->setDefaultValue($guard('project_name'))
            ->setRequired($this->translator->translate('main.grabthelab.form.mandatory'));

        $form->addSelect('scope', $this->translator->translate('main.grabthelab.form.scope'),
            \App\Model\Enum\ProjectScope::getTranslatedEnum(function($str) { return $this->translator->translate($str); })
        )
        ->setDefaultValue($guard('scope', null));

        $form->addTextArea('motivation', $this->translator->translate('main.grabthelab.form.motivation'))
            ->setDefaultValue($guard('motivation'))
            ->setRequired($this->translator->translate('main.grabthelab.form.mandatory'));

        $form->addTextArea('methods', $this->translator->translate('main.grabthelab.form.methods'))
            ->setDefaultValue($guard('methods'))
            ->setRequired($this->translator->translate('main.grabthelab.form.mandatory'));

        $form->addTextArea('anotation', $this->translator->translate('main.grabthelab.form.anotation'))
            ->setDefaultValue($guard('anotation'))       
            ->setRequired($this->translator->translate('main.grabthelab.form.mandatory'));

        $form->addSelect('length', $this->translator->translate('main.grabthelab.form.length'),
            \App\Model\Enum\ProjectLength::getTranslatedEnum(function($str, $arr = []) { return $this->translator->translate($str, $arr); })
        )
        ->setDefaultValue($guard('length', 3));

        $form->addText('contact_person_name', $this->translator->translate('main.grabthelab.form.contact_person_name'))
            ->setDefaultValue($guard('contact_person_name'))
            ->setRequired($this->translator->translate('main.grabthelab.form.mandatory'));
        $form->addText('contact_person_email', $this->translator->translate('main.grabthelab.form.contact_person_email'))
            ->setDefaultValue($guard('contact_person_email'))
            ->addRule(Form::EMAIL, $this->translator->translate('main.generic.not_an_email'))
            ->setRequired($this->translator->translate('main.grabthelab.form.mandatory'));

        $form->addSubmit('save', $this->translator->translate('main.grabthelab.form.save'));
        $form->addSubmit('submit', $this->translator->translate('main.grabthelab.form.continue'));

        $form->onSuccess[] = [$this, 'editProject1FormSuccess'];

        return $form;
    }

    public function editProject1FormSuccess(Form $form) {

        $subAction = $form->isSubmitted() ? $form->isSubmitted()->name : 'submit';

        $vals = $form->values;

        $existing = $this->getUser()->isLoggedIn() ? $this->grabthelab->getProjectDraft($this->getUser()->id) : null;
        if (!$existing) {
            $this->redirect('GrabTheLab:');
        }

        $data = json_decode($existing->data, true);

        $data['project_name'] = $vals->project_name;
        $data['scope'] = $vals->scope;
        $data['motivation'] = $vals->motivation;
        $data['methods'] = $vals->methods;
        $data['anotation'] = $vals->anotation;
        $data['length'] = $vals->length;
        $data['contact_person_name'] = $vals->contact_person_name;
        $data['contact_person_email'] = $vals->contact_person_email;

        $this->grabthelab->updateProject($existing->id, $data);

        if ($subAction === 'save')
            $this->redirect('this');
        else
            $this->redirect('GrabTheLab:proposalEdit', ['step' => 2]);
    }

    public function createComponentEditProject2Form() {
        $form = new Form();

        $proj = json_decode($this->grabthelab->getProjectDraft($this->getUser()->id)->data, true);
        $guard = function($fld, $default = "") use ($proj) {
            if (isset($proj[$fld])) {
                return $proj[$fld];
            }
            return $default;
        };

        for ($i = 0; $i < 5; $i++) {

            $nameField = $form->addText('member_name_'.$i, $this->translator->translate('main.grabthelab.form.member_name'))
                ->setDefaultValue($guard('member_name_'.$i));
            $schoolField = $form->addText('member_school_'.$i, $this->translator->translate('main.grabthelab.form.member_school'))
                ->setDefaultValue($guard('member_school_'.$i));
            $classField = $form->addText('member_class_'.$i, $this->translator->translate('main.grabthelab.form.member_class'))
                ->setDefaultValue($guard('member_class_'.$i));

            if ($i == 0 || $i == 1) {
                $nameField->setRequired($this->translator->translate('main.grabthelab.form.mandatory'));
                $schoolField->setRequired($this->translator->translate('main.grabthelab.form.mandatory'));
                $classField->setRequired($this->translator->translate('main.grabthelab.form.mandatory'));
            }

            $schoolField->addConditionOn($nameField, Form::FILLED)
                        ->addRule(Form::FILLED, $this->translator->translate('main.grabthelab.form.mandatory'));
            $classField->addConditionOn($nameField, Form::FILLED)
                       ->addRule(Form::FILLED, $this->translator->translate('main.grabthelab.form.mandatory'));

        }

        $form->addSubmit('save', $this->translator->translate('main.grabthelab.form.save'));
        $form->addSubmit('submit', $this->translator->translate('main.grabthelab.form.continue'));

        $form->onSuccess[] = [$this, 'editProject2FormSuccess'];

        return $form;
    }

    public function editProject2FormSuccess(Form $form) {

        $subAction = $form->isSubmitted() ? $form->isSubmitted()->name : 'submit';

        $vals = $form->values;

        $existing = $this->getUser()->isLoggedIn() ? $this->grabthelab->getProjectDraft($this->getUser()->id) : null;
        if (!$existing) {
            $this->redirect('GrabTheLab:');
        }

        $data = json_decode($existing->data, true);

        for ($i = 0; $i < 5; $i++) {
            if (!empty($vals->{"member_name_$i"})) {
                $data['member_name_'.$i] = $vals->{"member_name_$i"};
                $data['member_school_'.$i] = $vals->{"member_school_$i"};
                $data['member_class_'.$i] = $vals->{"member_class_$i"};
            }
            else {
                $data['member_name_'.$i] = "";
                $data['member_school_'.$i] = "";
                $data['member_class_'.$i] = "";
            }
        }

        $this->grabthelab->updateProject($existing->id, $data);

        if ($subAction === 'save')
            $this->redirect('this');
        else
            $this->redirect('GrabTheLab:proposalEdit', ['step' => 3]);
    }

    public function createComponentEditProject3Form() {
        $form = new Form();

        $proj = json_decode($this->grabthelab->getProjectDraft($this->getUser()->id)->data, true);
        $guard = function($fld, $default = "") use ($proj) {
            if (isset($proj[$fld])) {
                return $proj[$fld];
            }
            return $default;
        };

        $mlItems = $guard('finance_items', null);

        $multiplier = $form->addMultiplier('finance_items', function (\Nette\Forms\Container $container, Form $form) {
            $container->addText('name', $this->translator->translate('main.grabthelab.form.finance_name'));
            $container->addSelect('category', $this->translator->translate('main.grabthelab.form.finance_category'),
                \App\Model\Enum\FinanceCategory::getTranslatedEnum(function($str) { return $this->translator->translate($str); }));
            $container->addText('price', $this->translator->translate('main.grabthelab.form.finance_price'))
                ->addRule(Form::INTEGER, $this->translator->translate('main.generic.not_a_number'))
                ->addRule(Form::MIN, $this->translator->translate('main.generic.invalid_price'), 1);
        }, 1, 20);

        if ($mlItems) {
            $multiplier->setValues($mlItems);
        }

        $multiplier->addCreateButton($this->translator->translate('main.grabthelab.form.finance_item_add'));
        $multiplier->addRemoveButton($this->translator->translate('main.grabthelab.form.finance_item_remove'));

        $form->addSubmit('save', $this->translator->translate('main.grabthelab.form.save'));
        $form->addSubmit('submit', $this->translator->translate('main.grabthelab.form.continue'));

        $form->onSuccess[] = [$this, 'editProject3FormSuccess'];

        return $form;
    }

    public function editProject3FormSuccess(Form $form) {

        $subAction = $form->isSubmitted() ? $form->isSubmitted()->name : 'submit';

        $vals = $form->values;

        $existing = $this->getUser()->isLoggedIn() ? $this->grabthelab->getProjectDraft($this->getUser()->id) : null;
        if (!$existing) {
            $this->redirect('GrabTheLab:');
        }

        $data = json_decode($existing->data, true);

        $finItems = [];
        foreach ($vals->finance_items as $fi) {
            $finItems[] = [
                'name' => $fi->name,
                'category' => $fi->category,
                'price' => $fi->price,
            ];
        }

        $data['finance_items'] = $finItems;

        $this->grabthelab->updateProject($existing->id, $data);

        if ($subAction === 'save')
            $this->redirect('this');
        else
            $this->redirect('GrabTheLab:proposalEdit', ['step' => 4]);
    }

    public function createComponentEditProject4Form() {
        $form = new Form();

        $proj = json_decode($this->grabthelab->getProjectDraft($this->getUser()->id)->data, true);
        $guard = function($fld, $default = "") use ($proj) {
            if (isset($proj[$fld])) {
                return $proj[$fld];
            }
            return $default;
        };

        $mlItems = $guard('phases', null);

        $multiplier = $form->addMultiplier('phase_items', function (\Nette\Forms\Container $container, Form $form) {
            $container->addText('name', $this->translator->translate('main.grabthelab.form.phase_name'));
            $container->addText('description', $this->translator->translate('main.grabthelab.form.phase_description'));
        }, 1, 6);

        if ($mlItems) {
            $multiplier->setValues($mlItems);
        }

        $multiplier->addCreateButton($this->translator->translate('main.grabthelab.form.phase_item_add'));
        $multiplier->addRemoveButton($this->translator->translate('main.grabthelab.form.phase_item_remove'));

        $form->addSubmit('save', $this->translator->translate('main.grabthelab.form.save'));
        $form->addSubmit('submit', $this->translator->translate('main.grabthelab.form.continue'));

        $form->onSuccess[] = [$this, 'editProject4FormSuccess'];

        return $form;
    }

    public function editProject4FormSuccess(Form $form) {

        $subAction = $form->isSubmitted() ? $form->isSubmitted()->name : 'submit';

        $vals = $form->values;

        $existing = $this->getUser()->isLoggedIn() ? $this->grabthelab->getProjectDraft($this->getUser()->id) : null;
        if (!$existing) {
            $this->redirect('GrabTheLab:');
        }

        $data = json_decode($existing->data, true);

        $phases = [];
        foreach ($vals->phase_items as $ph) {
            $phases[] = [
                'name' => $ph->name,
                'description' => $ph->description,
            ];
        }

        $data['phases'] = $phases;

        $this->grabthelab->updateProject($existing->id, $data);

        if ($subAction === 'save')
            $this->redirect('this');
        else
            $this->redirect('GrabTheLab:proposalEdit', ['step' => 5]);
    }

    public function createComponentEditProject5Form() {
        $form = new Form();

        $proj = json_decode($this->grabthelab->getProjectDraft($this->getUser()->id)->data, true);
        $guard = function($fld, $default = "") use ($proj) {
            if (isset($proj[$fld])) {
                return $proj[$fld];
            }
            return $default;
        };

        $mlItems = $guard('outputs', null);

        $multiplier = $form->addMultiplier('output_items', function (\Nette\Forms\Container $container, Form $form) {
            $container->addText('name', $this->translator->translate('main.grabthelab.form.output_name'));
            $container->addSelect('type', $this->translator->translate('main.grabthelab.form.output_type'),
                \App\Model\Enum\OutputType::getTranslatedEnum(function($str) { return $this->translator->translate($str); }));
            $container->addText('description', $this->translator->translate('main.grabthelab.form.output_description'));
        }, 1, 10);

        if ($mlItems) {
            $multiplier->setValues($mlItems);
        }

        $multiplier->addCreateButton($this->translator->translate('main.grabthelab.form.output_item_add'));
        $multiplier->addRemoveButton($this->translator->translate('main.grabthelab.form.output_item_remove'));

        $form->addSubmit('save', $this->translator->translate('main.grabthelab.form.save'));
        $form->addSubmit('submit', $this->translator->translate('main.grabthelab.form.continue'));

        $form->onSuccess[] = [$this, 'editProject5FormSuccess'];

        return $form;
    }

    public function editProject5FormSuccess(Form $form) {

        $subAction = $form->isSubmitted() ? $form->isSubmitted()->name : 'submit';

        $vals = $form->values;

        $existing = $this->getUser()->isLoggedIn() ? $this->grabthelab->getProjectDraft($this->getUser()->id) : null;
        if (!$existing) {
            $this->redirect('GrabTheLab:');
        }

        $data = json_decode($existing->data, true);

        $outputs = [];
        foreach ($vals->output_items as $op) {
            $outputs[] = [
                'name' => $op->name,
                'type' => $op->type,
                'description' => $op->description,
            ];
        }

        $data['outputs'] = $outputs;

        $this->grabthelab->updateProject($existing->id, $data);

        if ($subAction === 'save')
            $this->redirect('this');
        else
            $this->redirect('GrabTheLab:proposalComplete');
    }

    public function handleExportDraftPdf() {

        $draft = $this->grabthelab->getProjectDraft($this->getUser()->id);
        if (!$draft) {
            $this->redirect('this');
            return;
        }

        $this->createPdfWithProject($draft);
    }

    public function handleExportProposedPdf() {

        $proposed = $this->grabthelab->getProjectProposed($this->getUser()->id);
        if (!$proposed) {
            $this->redirect('this');
            return;
        }

        $this->createPdfWithProject($proposed);
    }

    public function handleProposeProject() {

        $draft = $this->grabthelab->getProjectDraft($this->getUser()->id);
        if (!$draft) {
            $this->redirect('this');
            return;
        }

        $round = $this->grabthelab->getActiveRound();
        if (!$round) {
            $this->redirect('this');
            return;
        }

        $this->grabthelab->proposeProject($draft->id, $round->id);
        $this->redirect("GrabTheLab:proposalSent");
    }
}
