<?php
/**
 * PhPsst.
 *
 * @copyright Copyright (c) 2016 Felix Sandström
 * @license   MIT
 */

namespace PhPsst\Storage;

use PhPsst\Password;

/**
 */
abstract class Storage
{
    /**
     * @param Password $password
     * @param bool $allowOverwrite
     */
    abstract public function store(Password $password, $allowOverwrite = false);

    /**
     * @param $key
     * @return Password|null
     */
    abstract public function get($key);

    /**
     * @param Password $password
     */
    abstract public function delete(Password $password);

    /**
     * @param string $jsonData
     * @return Password
     */
    public function getPasswordFromJson($jsonData)
    {
        $password = null;
        if (($jsonObject = json_decode($jsonData))
            && !empty($jsonObject->id)
            && !empty($jsonObject->password)
            && !empty($jsonObject->ttl)
            && !empty($jsonObject->views)
        ) {
            $password = new Password($jsonObject->id, $jsonObject->password, $jsonObject->ttl, $jsonObject->views);
            if ($jsonObject->ttlTime < time()) {
                $this->delete($password);
                $password = null;
            }
        }

        return $password;
    }
}
