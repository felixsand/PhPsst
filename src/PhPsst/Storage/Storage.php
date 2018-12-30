<?php
/**
 * PhPsst.
 *
 * @copyright Copyright (c) 2018 Felix Sandström
 * @license   MIT
 */

namespace PhPsst\Storage;

use PhPsst\Password;

/**
 * @author Felix Sandström <http://github.com/felixsand>
 */
abstract class Storage
{
    abstract public function store(Password $password, bool $allowOverwrite = false): void;
    abstract public function get(string $key): ?Password;
    abstract public function delete(Password $password): void;

    public function getPasswordFromJson(string $jsonData): ?Password
    {
        $password = null;
        $jsonObject = json_decode($jsonData);
        if ($jsonObject && $this->hasValidJsonData($jsonObject)) {
            $password = new Password($jsonObject->id, $jsonObject->password, $jsonObject->ttl, $jsonObject->views);
            if ($jsonObject->ttl < time()) {
                $this->delete($password);
                $password = null;
            }
        }

        return $password;
    }

    private function hasValidJsonData(\stdClass $jsonObject): bool
    {
        return !empty($jsonObject->id)
            && !empty($jsonObject->password)
            && !empty($jsonObject->ttl)
            && !empty($jsonObject->views);
    }
}
