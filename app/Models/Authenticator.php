<?php

namespace App\Model;

use Nette,
    Nette\Security\Passwords;

/**
 * Users management.
 */
class Authenticator implements Nette\Security\IAuthenticator
{
    const
        TABLE_NAME = 'users',
        COLUMN_ID = 'id',
        COLUMN_NAME = 'email',
        COLUMN_PASSWORD_HASH = 'password',
        COLUMN_GOOGLE_ID = 'google_id',
        COLUMN_ROLE = 'role';

    /** @var Nette\Database\Context */
    private $database;

    public function __construct(Nette\Database\Context $database, private Passwords $passwords)
    {
        $this->database = $database;
    }

    /**
     * Performs an authentication.
     * @return Nette\Security\Identity
     * @throws Nette\Security\AuthenticationException
     */
    public function authenticate(array $credentials)
    {
        list($email, $password, $googleId) = $credentials;
        $row = $this->database->table(self::TABLE_NAME)->where(self::COLUMN_NAME, $email)->fetch();

        $googleIdSet = isset($googleId) && !empty($googleId);
        $passwordSet = isset($password) && !empty($password);

        if ($googleIdSet && $passwordSet) {
            throw new Nette\Security\AuthenticationException('Invalid use of authenticator', self::IDENTITY_NOT_FOUND);
        }
        if (!$googleIdSet && !$passwordSet) {
            throw new Nette\Security\AuthenticationException('No credentials given', self::IDENTITY_NOT_FOUND);
        }

        // no user
        if (!$row)
            throw new Nette\Security\AuthenticationException('The username is incorrect.', self::IDENTITY_NOT_FOUND);
        // googleId set but does not match the e-mail
        elseif ($googleIdSet && $row[self::COLUMN_GOOGLE_ID] != $googleId)
            throw new Nette\Security\AuthenticationException('The google login does not match', self::INVALID_CREDENTIAL);
        // password set but does not verify properly
        elseif ($passwordSet) {
            if (!$this->passwords->verify($password, $row[self::COLUMN_PASSWORD_HASH]))
                throw new Nette\Security\AuthenticationException('The password is incorrect.', self::INVALID_CREDENTIAL);

            if ($this->passwords->needsRehash($row[self::COLUMN_PASSWORD_HASH]))
            {
                $row->update(array(
                    self::COLUMN_PASSWORD_HASH => $this->passwords->hash($password),
                ));
            }
        }

        $arr = $row->toArray();

        // avoid override
        $arr['main_role'] = $row[self::COLUMN_ROLE];

        unset($arr[self::COLUMN_PASSWORD_HASH]);
        return new Nette\Security\Identity($row[self::COLUMN_ID], $row[self::COLUMN_ROLE], $arr);
    }
}
