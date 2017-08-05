<?php
/**
 * PhPsst.
 *
 * @copyright Copyright (c) 2016 Felix Sandström
 * @license   MIT
 */

namespace PhPsst;

/**
 * @author Felix Sandström <http://github.com/felixsand>
 */
class PhPsstException extends \RuntimeException
{
    /**
     * @const int
     */
    const NO_PASSWORD_WITH_ID_FOUND = 1;

    /**
     * @const int
     */
    const ID_IS_ALREADY_TAKEN = 2;
}
