<?php

/**
 * The main phpMyFAQ instances class for instance clients.
 *
 * This Source Code Form is subject to the terms of the Mozilla Public License,
 * v. 2.0. If a copy of the MPL was not distributed with this file, You can
 * obtain one at http://mozilla.org/MPL/2.0/.
 *
 * @package   phpMyFAQ
 * @author    Thorsten Rinne <thorsten@phpmyfaq.de>
 * @copyright 2012-2020 phpMyFAQ Team
 * @license   http://www.mozilla.org/MPL/2.0/ Mozilla Public License Version 2.0
 * @link      https://www.phpmyfaq.de
 * @since     2012-03-31
 */

namespace phpMyFAQ\Instance;

use phpMyFAQ\Configuration;
use phpMyFAQ\Database;
use phpMyFAQ\Exception;
use phpMyFAQ\Filesystem;
use phpMyFAQ\Instance;
use phpMyFAQ\Instance\Database as InstanceDatabase;

/**
 * Class Client
 *
 * @package phpMyFAQ\Instance
 */
class Client extends Instance
{
    /**
     * @var Filesystem
     */
    private $fileSystem;

    /**
     * URL of the client.
     *
     * @var string
     */
    private $clientUrl;

    /**
     * Constructor.
     *
     * @param Configuration $config
     */
    public function __construct(Configuration $config)
    {
        parent::__construct($config);
    }

    /**
     * @param Instance $instance
     */
    public function createClient(Instance $instance)
    {
        $instance->addConfig('isMaster', 'false');
    }

    /**
     * Adds a new folder named by the given hostname in /path/to/faq/multisite/.
     *
     * @param string $hostname Hostname of the client instance
     *
     * @return bool
     */
    public function createClientFolder($hostname)
    {
        $clientDir = PMF_ROOT_DIR . '/multisite/';

        if (!$this->fileSystem instanceof Filesystem) {
            $this->fileSystem = new Filesystem();
        }

        if (!is_writeable($clientDir)) {
            return false;
        }

        return $this->fileSystem->mkdir($clientDir . $hostname);
    }

    /**
     * Creates all tables with the given table prefix from the master tables.
     *
     * @param  string $prefix SQL table prefix
     * @return void
     */
    public function createClientTables($prefix)
    {
        try {
            // First, create the client tables
            $instanceDatabase = InstanceDatabase::factory($this->config, Database::getType());
            $instanceDatabase->createTables($prefix);

            // Then, copy data from the tables "faqconfig" , "faqright" and "faquser_right"
            $this->config->getDb()->query(
                sprintf(
                    'INSERT INTO %sfaqconfig SELECT * FROM %sfaqconfig',
                    $prefix,
                    Database::getTablePrefix()
                )
            );
            $this->config->getDb()->query(
                sprintf(
                    "UPDATE %sfaqconfig SET config_value = '%s' WHERE config_name = 'main.referenceURL'",
                    $prefix,
                    $this->clientUrl
                )
            );
            $this->config->getDb()->query(
                sprintf(
                    'INSERT INTO %sfaqright SELECT * FROM %sfaqright',
                    $prefix,
                    Database::getTablePrefix()
                )
            );
            $this->config->getDb()->query(
                sprintf(
                    'INSERT INTO %sfaquser_right SELECT * FROM %sfaquser_right WHERE user_id = 1',
                    $prefix,
                    Database::getTablePrefix()
                )
            );
        } catch (Exception $exception) {
        }
    }

    /**
     * Sets the Filesystem.
     *
     * @param Filesystem $fileSystem
     */
    public function setFileSystem(Filesystem $fileSystem)
    {
        $this->fileSystem = $fileSystem;
    }

    /**
     * Copies the config/constants.php file to a new client instance.
     *
     * @param  string $dest Destination file
     * @throws
     * @return bool
     * @throws Exception
     */
    public function copyConstantsFile($dest)
    {
        return $this->fileSystem->copy(
            $this->fileSystem->getRootPath() . '/config/constants.php',
            $dest
        );
    }

    /**
     * Copies a defined template folder to a new client instance, by default
     * the default template located at ./assets/themes/default/ will be copied.
     *
     * @param string $dest        Destination folder
     * @param string $templateDir Template folder
     *
     * @return void
     */
    public function copyTemplateFolder($dest, $templateDir = 'default')
    {
        $sourceTpl = $this->fileSystem->getRootPath() . '/assets/themes/' . $templateDir;
        $destTpl = $dest . '/assets/themes/';

        $this->fileSystem->recursiveCopy($sourceTpl, $destTpl);
    }

    /**
     * Sets client URL.
     *
     * @param string $clientUrl
     */
    public function setClientUrl($clientUrl)
    {
        $this->clientUrl = $clientUrl;
    }
}
