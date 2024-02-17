<?php

declare(strict_types=1);

namespace App\Presenters;

use Nette;

final class SignPresenter extends BasePresenter
{
    private $googleClient;
    
    public function __construct(private \App\Configurator $configurator) {
        parent::__construct();
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
    
    public function actionGoogleIn() {
        if (!isset($_GET['code'])) {
            $this->flashMessage('Invalid google sign in request', 'error');
            $this->redirect('Sign:up');
        }
        
        $this->setupGoogleAuth();
        
        $code = $_GET['code'];
        
        $token = $this->googleClient->fetchAccessTokenWithAuthCode($code);
        $this->googleClient->setAccessToken($token['access_token']);

        $google_oauth = new \Google\Service\Oauth2($this->googleClient);
        $google_account_info = $google_oauth->userinfo->get();
        $email =  $google_account_info->email;
        $name =  $google_account_info->name;
        
        dump($google_account_info);
    }
}
