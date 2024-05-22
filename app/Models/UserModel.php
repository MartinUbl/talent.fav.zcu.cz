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

    public function getUsers() {
        return $this->getTable();
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

        $role = 'user';
        if (strtolower($email) === 'ublm@gapps.zcu.cz') {
            $role = 'admin';
        }

        $this->getTable()->insert([
            'fullname' => $name,
            'email' => $email,
            'role' => $role,
            'password' => $this->passwords->hash($password)
        ]);

        return true;
    }

    public function createUserWithGoogle($name, $email, $googleId) {

        if ($this->getUserByEmail($email)) {
            return false;
        }

        $role = 'user';
        if (strtolower($email) === 'ublm@gapps.zcu.cz') {
            $role = 'admin';
        }

        $this->getTable()->insert([
            'fullname' => $name,
            'email'=> $email,
            'role' => $role,
            'google_id'=> $googleId
        ]);

        return true;
    }

    public function changeUserProfile($id, $fullname) {
        $this->getTable()->where('id', $id)->update([
            'fullname' => $fullname
        ]);
    }

    public function changeUserPassword($id, $oldPassword, $newPassword) {
        $user = $this->getUserById($id);
        if (!$user) {
            return false;
        }

        if (!$this->passwords->verify($oldPassword, $user->password)) {
            return false;
        }
        
        $this->getTable()->where('id', $id)->update([
            'password' => $this->passwords->hash($newPassword)
        ]);

        return true;
    }

    public function setUserRole($id, $role) {
        $this->getTable()->where('id', $id)->update([
            'role' => $role
        ]);
    }

};
