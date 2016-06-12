<?php
/**
 * PhPsst.
 *
 * @copyright Copyright (c) 2016 Felix Sandström
 * @license   MIT
 */

namespace PhPsst;

use Illuminate\Encryption\Encrypter;
use PhPsst\Storage\StorageInterface;

/**
 * A PHP library for distributing (one time) passwords/secrets in a more secure way.
 *
 * @author Felix Sandström <http://github.com/felixsand>
 */
class PhPsst
{
    /**
     * @var StorageInterface
     */
    protected $storage;

    /**
     * @var string
     */
    protected $cipher;

    /**
     * @const string
     */
    const CIPHER_DEFAULT = 'AES-256-CBC';

    /**
     * PhPsst constructor.
     * @param StorageInterface $storage
     * @param string $cipher
     */
    public function __construct(StorageInterface $storage, $cipher = null)
    {
        $this->storage = $storage;
        if ($cipher) {
            $this->cipher = $cipher;
        } else {
            $this->cipher = self::CIPHER_DEFAULT;
        }
    }

    /**
     * @param string $password
     * @param int $ttl
     * @param int $views
     * @return string
     */
    public function store($password, $ttl = 3600, $views = 1)
    {
        if (empty($password)) {
            throw new \InvalidArgumentException('The password has to be set');
        }

        $ttl = (int) $ttl;
        if ($ttl < 1) {
            throw new \InvalidArgumentException('TTL has to be higher than 0');
        }

        $views = (int) $views;
        if ($views < 1) {
            throw new \InvalidArgumentException('Views has to be highter han 0');
        }

        $id = uniqid();
        $key = bin2hex(random_bytes(16));
        $encrypter = new Encrypter($key, $this->cipher);

        $this->storage->insert(new Password($id, $encrypter->encrypt($password), $ttl, $views));

        return $id . ';' . $key;
    }

    /**
     * @param $secret
     * @return string
     */
    public function retrieve($secret)
    {
        if (!($idKeyArray = explode(';', $secret)) || count($idKeyArray) != 2) {
            throw new \InvalidArgumentException('Invalid secret');
        }
        list($id, $key) = $idKeyArray;

        if (!($password = $this->storage->get($id))) {
            throw new PhPsstException('No password with that ID found', PhPsstException::NO_PASSWORD_WITH_ID_FOUND);
        }
        $encrypter = new Encrypter($key, $this->cipher);

        $password->decreaseViews();
        if ($password->getViews() > 0) {
            $this->storage->update($password);
        } else {
            $this->storage->delete($password);
        }

        return $encrypter->decrypt($password->getPassword());
    }
}
