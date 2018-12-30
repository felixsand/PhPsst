<?php
/**
 * PhPsst.
 *
 * @copyright Copyright (c) 2018 Felix Sandström
 * @license   MIT
 */

namespace PhPsst;

/**
 * @author Felix Sandström <http://github.com/felixsand>
 */
class PhPsstException extends \RuntimeException
{
    public const NO_PASSWORD_WITH_ID_FOUND = 1;
    public const ID_IS_ALREADY_TAKEN       = 2;
}
