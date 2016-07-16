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
class FileStorage extends Storage
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
     * @const string
     */
    const FILE_SUFFIX = '.phpsst';

    /**
     * FileStorage constructor.
     * @param string $dir
     * @param int $gcProbability
     */
    public function __construct($dir, $gcProbability)
    {
        $dir = rtrim($dir, '/') . '/';
        if (empty($dir) || !is_dir($dir)) {
            throw new \RuntimeException('Invalid directory path');
        }

        if ($gcProbability < 0) {
            throw new \LogicException('Invalid value for gcProbability');
        }

        $this->dir = $dir;
        $this->gcProbability = $gcProbability;
    }

    /**
     * @param Password $password
     * @param bool $allowOverwrite
     */
    public function store(Password $password, $allowOverwrite = false)
    {
        if (!$allowOverwrite && file_exists($this->getFileName($password))) {
            throw new PhPsstException('The ID already exists', PhPsstException::ID_IS_ALREADY_TAKEN);
        }

        $this->writeFile($password);
    }

    /**
     * @param $key
     * @return Password|null
     */
    public function get($key)
    {
        $password = null;
        if (file_exists($this->getFileNameFromKey($key))
            && ($passwordData = file_get_contents($this->getFileNameFromKey($key)))) {
            $password = $this->getPasswordFromJson($passwordData);
        }

        return $password;
    }

    /**
     * @param Password $password
     */
    public function delete(Password $password)
    {
        unlink($this->getFileName($password));
    }

    /**
     */
    protected function garbageCollection()
    {
        if (!$this->gcProbability || rand(1, $this->gcProbability) !== 1) {
            return;
        }

        $files = array_diff(scandir($this->dir), array('.', '..'));
        foreach ($files as $file) {
            if (($jsonData = json_decode(file_get_contents($this->dir . $file)))) {
                if ($jsonData->ttl < time()) {
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
        $jsonData = $password->getJson();

        $fileName = $this->getFileName($password);
        if (!is_writable(dirname($fileName)) || !file_put_contents($fileName, $jsonData)) {
            throw new \RuntimeException('Can not write file');
        }

        $this->garbageCollection();
    }

    /**
     * @param Password $password
     * @return string
     */
    protected function getFileName(Password $password)
    {
        return $this->getFileNameFromKey($password->getId());
    }

    /**
     * @param string $key
     * @return string
     */
    protected function getFileNameFromKey($key)
    {
        return $this->dir . $key . self::FILE_SUFFIX;
    }
}
