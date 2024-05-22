<?php

declare(strict_types=1);

namespace App\Presenters;

use Nette, Nette\Application\UI\Form;

final class SignPresenter extends BasePresenter
{
    private $googleClient;
    
    public function __construct(protected \App\Configurator $configurator, private \App\Model\UserModel $users) {
        parent::__construct($configurator);
    }
    
    private function setupGoogleAuth() {
        $this->template->googleClientId = $this->configurator->googleClientId;
        
        $this->googleClient = new \Google\Client();
        $this->googleClient->setClientId($this->configurator->googleClientId);
        $this->googleClient->setClientSecret($this->configurator->googleClientSecret);
        $this->googleClient->setRedirectUri($this->link('//Sign:googleIn'));
        $this->googleClient->addScope("email");
        $this->googleClient->addScope("profile");
        
        $this->template->googleAuthLink = $this->googleClient->createAuthUrl();
    }
    
    public function actionIn() {
        $this->setupGoogleAuth();
    }
    
    public function actionUp() {
        $this->setupGoogleAuth();
    }

    public function actionAture() {
        if (!$this->getUser()->isLoggedIn()) {
            $this->redirect('Sign:in');
        }
    }
    
    public function actionGoogleIn() {
        if (!isset($_GET['code'])) {
            $this->flashMessage('Invalid google sign in request', 'error');
            $this->redirect('Sign:up');
        }
        
        $this->setupGoogleAuth();
        
        $code = $_GET['code'];
        
        $token = $this->googleClient->fetchAccessTokenWithAuthCode($code);

        if (!$token || !isset($token['access_token'])) {
            $this->flashMessage('Login request expired, please, try again.', 'error');
            $this->redirect('Sign:up');
        }

        $this->googleClient->setAccessToken($token['access_token']);

        $oauth = new \Google\Service\Oauth2($this->googleClient);
        $profile = $oauth->userinfo->get();
        $email = $profile->email;
        $name = $profile->name;
        $googleId = $profile->id;

        $alreadyRegistered = false;

        $existing = $this->users->getUserByGoogleId($googleId);
        if ($existing) {
            if (strcasecmp($existing->email, $email) === 0) {
                $alreadyRegistered = true;
            }
            else {
                $this->flashMessage('User identifier and e-mail mismatch, please, use a different method', 'error');
                $this->redirect('Sign:up');
            }
        }

        $existing = $this->users->getUserByEmail($email);
        if (!$alreadyRegistered && $existing) {
            if ($existing->google_id !== $googleId) {
                $this->flashMessage('User identifier and e-mail mismatch, please, use a different method', 'error');
                $this->redirect('Sign:up');
            }
            else {
                $alreadyRegistered = true;
            }
        }

        if (!$alreadyRegistered) {
            $this->users->createUserWithGoogle($name, $email, $googleId);
        }
        
        $relogin = false;

        try {
            $this->user->login($email, null, $googleId);
        }
        catch (\Exception $e) {
            $this->flashMessage('Could not log in at the moment. Please, try again later', 'error');
            $relogin = true;
        }

        if ($relogin) {
            $this->redirect('Sign:in');
        }
        else {
            $this->redirect('Home:');
        }
    }

    public function createComponentSignInForm() {
        $form = new Form();

        $form->addText('email', $this->translator->translate('main.signin.email'))
                ->setHtmlAttribute('placeholder', $this->translator->translate('main.signin.email_placeholder'))
                ->addRule(Form::EMAIL, $this->translator->translate('main.signin.email_invalid'))
                ->setRequired($this->translator->translate('main.signin.email_required'));
        $form->addPassword('password', $this->translator->translate('main.signin.password'))
                ->setHtmlAttribute('placeholder', $this->translator->translate('main.signin.password_placeholder'))
                ->setRequired($this->translator->translate('main.signin.password_required'));
        $form->addSubmit('submit', $this->translator->translate('main.signin.submit'));

        $form->onSuccess[] = [$this,'signInFormSuccess'];

        return $form;
    }

    public function signInFormSuccess(Form $form) {
        $vals = $form->values;

        $success = false;

        try {
            $this->getUser()->login($vals->email, $vals->password, null);
            $success = true;
        }
        catch (\Nette\Security\AuthenticationException $e) {
            if ($e->getCode() == \Nette\Security\IAuthenticator::IDENTITY_NOT_FOUND || $e->getCode() == \Nette\Security\IAuthenticator::INVALID_CREDENTIAL) {
                $form->addError($this->translator->translate('main.signin.invalid_credentials'));
            }
            else {
                $form->addError($this->translator->translate('main.signin.unknown_error'));
            }
        }
        catch (\Exception $e) {
            $this->flashMessage($this->translator->translate('main.signin.unknown_error'), 'error');
        }

        if ($success) {
            $this->flashMessage($this->translator->translate('main.signin.success'), 'success');
            $this->redirect('Home:');
        }
    }

    public function createComponentSignUpForm() {
        $form = new Form();

        $form->addText('fullname', $this->translator->translate('main.signup.fullname'))
                ->setHtmlAttribute('placeholder', $this->translator->translate('main.signup.fullname_placeholder'))
                ->setRequired($this->translator->translate('main.signup.fullname_required'));
        $form->addText('email', $this->translator->translate('main.signup.email'))
                ->setHtmlAttribute('placeholder', $this->translator->translate('main.signup.email_placeholder'))
                ->addRule(Form::EMAIL, $this->translator->translate('main.signup.email_invalid'))
                ->setRequired($this->translator->translate('main.signup.email_required'));
        $form->addPassword('password', $this->translator->translate('main.signup.password'))
                ->setHtmlAttribute('placeholder', $this->translator->translate('main.signup.password_placeholder'))
                ->addRule(Form::MIN_LENGTH, $this->translator->translate('main.signup.password_min_length'), 8)
                ->setRequired($this->translator->translate('main.signup.password_required'));
        $form->addPassword('password_again', $this->translator->translate('main.signup.password_again'))
                ->setHtmlAttribute('placeholder', $this->translator->translate('main.signup.password_again_placeholder'))
                ->addRule(Form::EQUAL, $this->translator->translate('main.signup.passwords_must_match'), $form['password']);
        $form->addSubmit('submit', $this->translator->translate('main.signup.submit'));

        $form->onSuccess[] = [$this,'signUpFormSuccess'];

        return $form;
    }

    public function signUpFormSuccess(Form $form) {
        $vals = $form->values;

        $existing = $this->users->getUserByEmail($vals->email);
        if ($existing) {
            $form->addError($this->translator->translate('main.signup.user_already_exists'));
            return;
        }

        $success = $this->users->createUserWithPassword($vals->fullname, $vals->email, $vals->password);
        if ($success) {
            $this->flashMessage($this->translator->translate('main.signup.success'), 'success');
            try {
                $this->getUser()->login($vals->email, $vals->password, null);
            }
            catch (\Exception $e) {
                $this->redirect('Sign:in');
                return;
            }

            $this->redirect("Home:");
        }
        else {
            $form->addError($this->translator->translate('main.signup.unknown_error'));
            return;
        }
    }

    public function createComponentUserEditForm() {
        $form = new Form();

        $form->addText('fullname', $this->translator->translate('main.signup.fullname'))
                ->setHtmlAttribute('placeholder', $this->translator->translate('main.signup.fullname_placeholder'))
                ->setRequired($this->translator->translate('main.signup.fullname_required'))
                ->setDefaultValue($this->getUser()->getIdentity()->fullname);
        $form->addSubmit('submit', $this->translator->translate('main.signature.details_submit'));

        $form->onSuccess[] = [$this,'userEditFormSuccess'];

        return $form;
    }

    public function userEditFormSuccess(Form $form) {
        $vals = $form->values;

        $this->users->changeUserProfile($this->getUser()->id, $vals->fullname);

        $this->flashMessage($this->translator->translate('main.signature.success_change'), 'success');
        $this->redirect('this');
    }

    public function createComponentChangePasswordForm() {
        $form = new Form();

        $form->addPassword('old_password', $this->translator->translate('main.signature.old_password'))
            ->setRequired($this->translator->translate('main.signature.old_pass_mandatory'));
        $form->addPassword('password', $this->translator->translate('main.signature.new_password'))
            ->setHtmlAttribute('placeholder', $this->translator->translate('main.signup.password_placeholder'))
            ->addRule(Form::MIN_LENGTH, $this->translator->translate('main.signup.password_min_length'), 8);
        $form->addPassword('password_again', $this->translator->translate('main.signature.new_password_again'))
            ->setHtmlAttribute('placeholder', $this->translator->translate('main.signup.password_again_placeholder'))
            ->addRule(Form::EQUAL, $this->translator->translate('main.signup.passwords_must_match'), $form['password']);

        $form->addSubmit('submit', $this->translator->translate('main.signature.password_submit'));

        $form->onSuccess[] = [$this,'changePasswordFormSuccess'];

        return $form;
    }

    public function changePasswordFormSuccess(Form $form) {
        $vals = $form->values;

        if ($this->users->changeUserPassword($this->getUser()->id, $vals->old_password, $vals->password)) {
            $this->flashMessage($this->translator->translate('main.signature.pass_changed_ok'), 'success');
            $this->redirect('this');
        }

        $this->flashMessage($this->translator->translate('main.signature.pass_changed_fail'), 'error');
        $this->redirect('this');
    }

}
