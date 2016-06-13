<?php
/**
 * PhPsst.
 *
 * @copyright Copyright (c) 2016 Felix Sandström
 * @license   MIT
 */

namespace PhPsst\Storage;

use PhPsst\Password;
use PhPsst\PhPsstException;
use Predis\Client;

/**
 */
class RedisStorage extends Storage
{
    /**
     * @var Client
     */
    protected $client;

    /**
     * RedisStorage constructor.
     * @param Client $client
     */
    public function __construct(Client $client)
    {
        $this->client = $client;
    }

    /**
     * @param Password $password
     * @param bool $allowOverwrite
     */
    public function store(Password $password, $allowOverwrite = false)
    {
        if (!$allowOverwrite && $this->get($password->getId())) {
            throw new PhPsstException('The ID already exists', PhPsstException::ID_IS_ALREADY_TAKEN);
        }
        $this->client->set($password->getId(), $this->getJsonFromPassword($password));
    }

    /**
     * @param $key
     * @return Password|null
     */
    public function get($key)
    {
        $password = null;
        if (($passwordData = $this->client->get($key))) {
            $password = $this->getPasswordFromJson($passwordData);
        }

        return $password;
    }

    /**
     * @param Password $password
     */
    public function delete(Password $password)
    {
        $this->client->del($password->getId());
    }
}
