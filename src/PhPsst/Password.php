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

    public function __construct(string $id, string $password, int $ttl, int $views)
    {
        $this->id = $id;
        $this->password = $password;
        $this->ttl = $ttl;
        $this->views = $views;
    }

    public function getId(): string
    {
        return $this->id;
    }

    public function getPassword(): string
    {
        return $this->password;
    }

    public function getTtl(): int
    {
        return $this->ttl;
    }

    public function getViews(): int
    {
        return $this->views;
    }

    public function decreaseViews(): void
    {
        if (($this->views - 1) >= 0) {
            $this->views--;
        } else {
            throw new \LogicException('Passwords with negative views should be deleted');
        }
    }

    public function getJson(): string
    {
        return json_encode([
            'id' => $this->getId(),
            'password' => $this->getPassword(),
            'ttl' => $this->getTtl(),
            'views' => $this->getViews(),
        ]);
    }
}
