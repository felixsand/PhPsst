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
interface StorageInterface
{
    /**
     * @param Password $password
     * @param bool $allowOverwrite
     */
    public function store(Password $password, $allowOverwrite = false);

    /**
     * @param $key
     * @return Password|null
     */
    public function get($key);

    /**
     * @param Password $password
     * @return void
     */
    public function delete(Password $password);
}
