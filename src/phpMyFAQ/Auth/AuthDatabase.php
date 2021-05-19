<?php

/**
 * Manages user authentication with databases.
 *
 * This Source Code Form is subject to the terms of the Mozilla Public License,
 * v. 2.0. If a copy of the MPL was not distributed with this file, You can
 * obtain one at http://mozilla.org/MPL/2.0/.
 *
 * @package   phpMyFAQ
 * @author    Lars Tiedemann <php@larstiedemann.de>
 * @author    Thorsten Rinne <thorsten@phpmyfaq.de>
 * @copyright 2005-2020 phpMyFAQ Team
 * @license   http://www.mozilla.org/MPL/2.0/ Mozilla Public License Version 2.0
 * @link      https://www.phpmyfaq.de
 * @since     2005-09-30
 */

namespace phpMyFAQ\Auth;

use phpMyFAQ\Auth;
use phpMyFAQ\Configuration;
use phpMyFAQ\Database;
use phpMyFAQ\User;

/**
 * Class AuthDatabase
 *
 * @package phpMyFAQ\Auth
 */
class AuthDatabase extends Auth implements AuthDriverInterface
{
    /**
     * Database connection.
     *
     * @var AuthDriverInterface
     */
    private $db = null;

    /**
     * Constructor.
     *
     * @param Configuration $config
     */
    public function __construct(Configuration $config)
    {
        parent::__construct($config);

        $this->db = $this->config->getDb();
    }

    /**
     * Adds a new user account to the faquserlogin table. Returns true on
     * success, otherwise false. Error messages are added to the array errors.
     *
     * @param  string $login
     * @param  string $pass
     * @param  string $domain
     * @return bool
     */
    public function add($login, $pass, $domain = ''): bool
    {
        if ($this->checkLogin($login) > 0) {
            $this->errors[] = User::ERROR_USER_ADD . User::ERROR_USER_LOGIN_NOT_UNIQUE;

            return false;
        }

        $add = sprintf(
            "
            INSERT INTO
                %sfaquserlogin
            (login, pass, domain)
                VALUES
            ('%s', '%s', '%s')",
            Database::getTablePrefix(),
            $this->db->escape($login),
            $this->db->escape($this->encContainer->setSalt($login)->encrypt($pass)),
            $this->db->escape($domain)
        );


        $add = $this->db->query($add);
        $error = $this->db->error();

        if (strlen($error) > 0) {
            $this->errors[] = User::ERROR_USER_ADD . 'error(): ' . $error;

            return false;
        }
        if (!$add) {
            $this->errors[] = User::ERROR_USER_ADD;

            return false;
        }

        return true;
    }

    /**
     * Checks the number of entries of given login name.
     *
     * @param string $login        Loginname
     * @param array  $optionalData Optional data
     *
     * @return int
     */
    public function checkLogin($login, array $optionalData = null): int
    {
        $check = sprintf(
            "
            SELECT
                login
            FROM
                %sfaquserlogin
            WHERE
                login = '%s'",
            Database::getTablePrefix(),
            $this->db->escape($login)
        );

        $check = $this->db->query($check);
        $error = $this->db->error();

        if (strlen($error) > 0) {
            $this->errors[] = $error;

            return 0;
        }

        return $this->db->numRows($check);
    }

    /**
     * Deletes the user account specified by login.
     *
     * Returns true on success, otherwise false.
     *
     * Error messages are added to the array errors.
     *
     * @param string $login Loginname
     *
     * @return bool
     */
    public function delete($login): bool
    {
        $delete = sprintf(
            "
            DELETE FROM
                %sfaquserlogin
            WHERE
                login = '%s'",
            Database::getTablePrefix(),
            $this->db->escape($login)
        );

        $delete = $this->db->query($delete);
        $error = $this->db->error();

        if (strlen($error) > 0) {
            $this->errors[] = User::ERROR_USER_DELETE . 'error(): ' . $error;

            return false;
        }
        if (!$delete) {
            $this->errors[] = User::ERROR_USER_DELETE;

            return false;
        }

        return true;
    }

    /**
     * checks the password for the given user account.
     *
     * Returns true if the given password for the user account specified by
     * is correct, otherwise false.
     * Error messages are added to the array errors.
     *
     * @param string $login        Loginname
     * @param string $password     Password
     * @param array  $optionalData Optional data
     *
     * @return bool
     */
    public function checkPassword($login, $password, array $optionalData = null): bool
    {
        $check = sprintf(
            "
            SELECT
                login, pass
            FROM
                %sfaquserlogin
            WHERE
                login = '%s'",
            Database::getTablePrefix(),
            $this->db->escape($login)
        );

        $check = $this->db->query($check);
        $error = $this->db->error();

        if (strlen($error) > 0) {
            $this->errors[] = User::ERROR_USER_NOT_FOUND . 'error(): ' . $error;

            return false;
        }

        $numRows = $this->db->numRows($check);
        if ($numRows < 1) {
            $this->errors[] = User::ERROR_USER_NOT_FOUND;

            return false;
        }

        // if login not unique, raise an error, but continue
        if ($numRows > 1) {
            $this->errors[] = User::ERROR_USER_LOGIN_NOT_UNIQUE;
        }

        // if multiple accounts are ok, just 1 valid required
        while ($user = $this->db->fetchArray($check)) {
            // Check password against old one
            if ($this->config->get('security.forcePasswordUpdate')) {
                if (
                    $this->checkEncryptedPassword($user['pass'], $password)
                    && $this->encContainer->setSalt($user['login'])->encrypt($password) !== $user['pass']
                ) {
                    return $this->changePassword($login, $password);
                }
            }

            if ($user['pass'] === $this->encContainer->setSalt($user['login'])->encrypt($password)) {
                return true;
                break;
            }
        }
        $this->errors[] = User::ERROR_USER_INCORRECT_PASSWORD;

        return false;
    }

    /**
     * Changes the password for the account specified by login.
     *
     * Returns true on success, otherwise false.
     *
     * Error messages are added to the array errors.
     *
     * @param string $login Loginname
     * @param string $pass  Password
     *
     * @return bool
     */
    public function changePassword($login, $pass): bool
    {
        $change = sprintf(
            "
            UPDATE
                %sfaquserlogin
            SET
                pass = '%s'
            WHERE
                login = '%s'",
            Database::getTablePrefix(),
            $this->db->escape($this->encContainer->setSalt($login)->encrypt($pass)),
            $this->db->escape($login)
        );

        $change = $this->db->query($change);
        $error = $this->db->error();

        if (strlen($error) > 0) {
            $this->errors[] = User::ERROR_USER_CHANGE . 'error(): ' . $error;

            return false;
        }
        if (!$change) {
            $this->errors[] = User::ERROR_USER_CHANGE;

            return false;
        }

        return true;
    }
}
