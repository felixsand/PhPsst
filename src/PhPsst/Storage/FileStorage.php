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
 * @author Felix Sandström <http://github.com/felixsand>
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

    public function __construct(string $dir, int $gcProbability)
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

    public function store(Password $password, bool $allowOverwrite = false): void
    {
        if (!$allowOverwrite && file_exists($this->getFileName($password))) {
            throw new PhPsstException('The ID already exists', PhPsstException::ID_IS_ALREADY_TAKEN);
        }

        $this->writeFile($password);
    }

    public function get(string $key): ?Password
    {
        $password = null;
        if (file_exists($this->getFileNameFromKey($key))
            && ($passwordData = file_get_contents($this->getFileNameFromKey($key)))) {
            $password = $this->getPasswordFromJson($passwordData);
        }

        return $password;
    }

    public function delete(Password $password): void
    {
        unlink($this->getFileName($password));
    }

    protected function garbageCollection(): void
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

    protected function writeFile(Password $password): void
    {
        $jsonData = $password->getJson();

        $fileName = $this->getFileName($password);
        if (!is_writable(dirname($fileName)) || !file_put_contents($fileName, $jsonData)) {
            throw new \RuntimeException('Can not write file');
        }

        $this->garbageCollection();
    }

    protected function getFileName(Password $password): string
    {
        return $this->getFileNameFromKey($password->getId());
    }

    protected function getFileNameFromKey(string $key): string
    {
        return $this->dir . $key . self::FILE_SUFFIX;
    }
}
