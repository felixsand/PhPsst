<?php
/**
 * PhPsst.
 *
 * @copyright Copyright (c) 2016 Felix Sandström
 * @license   MIT
 */

namespace PhPsst;

/**
 */
class FileStorage
{
    /**
     * @var string
     */
    protected $dir;

    /**
     * FileStorage constructor.
     * @param string $dir
     */
    public function __construct($dir)
    {
        if (empty($dir) || !is_dir($dir)) {
            throw new \RuntimeException('Invalid filepath');
        }
        $this->dir = $dir;
    }

    /**
     * @param Password $password
     */
    public function insert(Password $password)
    {
        if (!$force && file_exists($this->dir . $password->getId())) {
            throw new \RuntimeException('The ID already exists');
        }

        $this->writeFile($password);
    }

    /**
     * @param Password $password
     */
    public function update(Password $password)
    {
        $this->writeFile($password);
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
     * @return void
     */
    public function delete(Password $password)
    {
        unlink($this->dir . $password->getId());
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
            'views' => $password->getViews()
        ]);
        if (! file_put_contents($this->dir . $password->getId(), $jsonData)) {
            throw new \RuntimeException('Can not store Password');
        }
    }
}
