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

/**
 */
class FileStorage implements StorageInterface
{
    /**
     * @var string
     */
    protected $dir;

    /**
     * @var int
     */
    protected $gcProbability;

    /**
     * FileStorage constructor.
     * @param string $dir
     * @param int $gcProbability
     */
    public function __construct($dir, $gcProbability = 10)
    {
        if (empty($dir) || !is_dir($dir)) {
            throw new \RuntimeException('Invalid directory path');
        }

        $this->dir = $dir;
        $this->gcProbability = $gcProbability;
    }

    /**
     * @param Password $password
     */
    public function insert(Password $password)
    {
        if (file_exists($this->dir . $password->getId())) {
            throw new PhPsstException('The ID already exists', PhPsstException::ID_IS_ALREADY_TAKEN);
        }

        $this->writeFile($password);
        $this->garbageCollection();
    }

    /**
     * @param Password $password
     */
    public function update(Password $password)
    {
        if (!file_exists($this->dir . $password->getId())) {
            throw new PhPsstException('No such ID exists', PhPsstException::NO_PASSWORD_WITH_ID_FOUND);
        }

        $this->writeFile($password);
        $this->garbageCollection();
    }

    /**
     * @param $key
     * @return Password|null
     */
    public function get($key)
    {
        $password = null;
        if (($jsonData = json_decode(file_get_contents($this->dir . $key)))) {
            if (!empty($jsonData['id'])
                && !empty($jsonData['password'])
                && !empty($jsonData['ttl'])
                && !empty($jsonData['views'])) {
            }
            $password = new Password($jsonData['id'], $jsonData['password'], $jsonData['ttl'], $jsonData['views']);
        }

        return $password;
    }

    /**
     * @param Password $password
     */
    public function delete(Password $password)
    {
        unlink($this->dir . $password->getId());
    }

    /**
     */
    protected function garbageCollection()
    {
        if (rand(1, $this->gcProbability) !== 1) {
            return;
        }

        $files = array_diff(scandir($this->dir), array('.', '..'));
        foreach ($files as $file) {
            if (($jsonData = json_decode(file_get_contents($this->dir . $file)))) {
                if ($jsonData['ttlTime'] < time()) {
                    unlink($this->dir . $file);
                }
            }
        }
    }

    /**
     * @param Password $password
     */
    protected function writeFile(Password $password)
    {
        $jsonData = json_encode([
            'id' => $password->getId(),
            'password' => $password->getPassword(),
            'ttl' => $password->getTtl(),
            'ttlTime' => time() + $password->getTtl(),
            'views' => $password->getViews(),
        ]);
        if (! file_put_contents($this->dir . $password->getId(), $jsonData)) {
            throw new \RuntimeException('Can not store Password');
        }
    }
}
