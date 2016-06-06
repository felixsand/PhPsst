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
     * @return void
     */
    public function insert(Password $password);

    /**
     * @param Password $password
     * @return void
     */
    public function update(Password $password);

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
