<?php

declare(strict_types=1);

namespace App\Presenters;

use Nette\Application\UI\Form;

final class GrabTheLabPresenter extends BasePresenter
{
    public function __construct(private \App\Model\GrabTheLabModel $grabthelab) {
        parent::__construct();
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

    public function createComponentGtlRoundComponent() {
        return new \App\Components\GtlRoundComponent();
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

        $this->grabthelab->updateProject($this->getUser()->id, $data);

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

        $this->grabthelab->updateProject($this->getUser()->id, $data);

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

        $multiplier = $form->addMultiplier('finance_items', function (\Nette\Forms\Container $container, Form $form) {
            $container->addText('fin_name', $this->translator->translate('main.grabthelab.form.finance_name'));
            //$container->addSelect('fin_category', $this->translator->translate('main.grabthelab.form.finance_category'), [1,2]);
                //\App\Model\Enum\FinanceCategory::getTranslatedEnum(function($str) { return $this->translator->translate($str); }));
            $container->addInteger('fin_price', $this->translator->translate('main.grabthelab.form.finance_price'));
        }, 1, 10);

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

        dump($vals->finance_items[0]->text);
        die;

        $existing = $this->getUser()->isLoggedIn() ? $this->grabthelab->getProjectDraft($this->getUser()->id) : null;
        if (!$existing) {
            $this->redirect('GrabTheLab:');
        }

        $data = json_decode($existing->data, true);

        //

        $this->grabthelab->updateProject($this->getUser()->id, $data);

        if ($subAction === 'save')
            $this->redirect('this');
        else
            $this->redirect('GrabTheLab:proposalEdit', ['step' => 4]);
    }
}
