<?php

namespace App\Model;

use Nette;

class UserModel extends BaseModel {

    public $implicitTable = 'users';

    /** @var \Nette\Security\Passwords */
    private $passwords;

    public function __construct(Nette\Database\Context $database, Nette\DI\Container $c, \Nette\Security\Passwords $passwords) {
        parent::__construct($database, $c);

        $this->passwords = $passwords;
    }

    public function getUserById($id) {
        return $this->getTable()->where('id', $id)->fetch();
    }

    public function getUserByGoogleId($googleId) {
        return $this->getTable()->where('google_id', $googleId)->fetch();
    }

    public function getUserByEmail($email) {
        return $this->getTable()->where('email', $email)->fetch();
    }

    public function createUserWithPassword($name, $email, $password) {

        if ($this->getUserByEmail($email)) {
            return false;
        }

        $this->getTable()->insert([
            'fullname' => $name,
            'email' => $email,
            'role' => 'user',
            'password' => $this->passwords->hash($password)
        ]);

        return true;
    }

    public function createUserWithGoogle($name, $email, $googleId) {

        if ($this->getUserByEmail($email)) {
            return false;
        }

        $this->getTable()->insert([
            'fullname' => $name,
            'email'=> $email,
            'role' => 'user',
            'google_id'=> $googleId
        ]);

        return true;
    }

};
