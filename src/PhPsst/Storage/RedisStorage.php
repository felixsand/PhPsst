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
 * @author Felix Sandström <http://github.com/felixsand>
 */
class RedisStorage extends Storage
{
    /**
     * @var Client
     */
    protected $client;

    public function __construct(Client $client)
    {
        $this->client = $client;
    }

    public function store(Password $password, bool $allowOverwrite = false): void
    {
        if (!$allowOverwrite && $this->get($password->getId())) {
            throw new PhPsstException('The ID already exists', PhPsstException::ID_IS_ALREADY_TAKEN);
        }
        $this->client->set($password->getId(), $password->getJson());
        $this->client->expireat($password->getId(), $password->getTtl());
    }

    public function get(string $key): ?Password
    {
        $password = null;
        if (($passwordData = $this->client->get($key))) {
            $password = $this->getPasswordFromJson($passwordData);
        }

        return $password;
    }

    public function delete(Password $password): void
    {
        $this->client->del([$password->getId()]);
    }
}
