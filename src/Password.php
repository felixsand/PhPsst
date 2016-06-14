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
class Password
{
    /**
     * @var string
     */
    protected $id;

    /**
     * @var int
     */
    protected $ttl;

    /**
     * @var int
     */
    protected $views;

    /**
     * @var string
     */
    protected $password;

    /**
     * Password constructor.
     * @param string $id
     * @param string $password
     * @param int $ttl
     * @param int $views
     */
    public function __construct($id, $password, $ttl, $views)
    {
        $this->id = $id;
        $this->password = $password;
        $this->ttl = $ttl;
        $this->views = $views;
    }

    /**
     * @return string
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * @return string
     */
    public function getPassword()
    {
        return $this->password;
    }

    /**
     * @return int
     */
    public function getTtl()
    {
        return $this->ttl;
    }

    /**
     * @return int
     */
    public function getViews()
    {
        return $this->views;
    }

    /**
     */
    public function decreaseViews()
    {
        if (($this->views - 1) >= 0) {
            $this->views--;
        } else {
            throw new \LogicException('Passwords with negative views should be deleted');
        }
    }

    /**
     * @return string
     */
    public function getJson()
    {
        return json_encode([
            'id' => $this->getId(),
            'password' => $this->getPassword(),
            'ttl' => $this->getTtl(),
            'ttlTime' => time() + $this->getTtl(),
            'views' => $this->getViews(),
        ]);
    }
}
