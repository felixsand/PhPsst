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
use SQLite3;

/**
 * @group failing
 */
class SqLiteStorage extends Storage
{
    /**
     * @var int
     */
    protected $gcProbability;

    /**
     * @var SQLite3
     */
    protected $db;

    /**
     * FileStorage constructor.
     * @param SQLite3 $db
     * @param int $gcProbability
     */
    public function __construct(SQLite3 $db, $gcProbability)
    {
        if ($gcProbability < 0) {
            throw new \LogicException('Invalid value for gcProbability');
        }
        $this->gcProbability = $gcProbability;

        $this->db = $db;
        $this->db->enableExceptions(true);
        $sql = <<<'SQL'
CREATE TABLE IF NOT EXISTS `phPsst` (
  id VARCHAR(13) PRIMARY KEY NOT NULL,
  password TEXT NOT NULL,
  ttl INT NOT NULL,
  views INT NOT NULL
);
SQL;
        $this->db->exec($sql);
    }

    /**
     * @param Password $password
     * @param bool $allowOverwrite
     */
    public function store(Password $password, $allowOverwrite = false)
    {
        if ($this->get($password->getId())) {
            if (! $allowOverwrite) {
                throw new PhPsstException('The ID already exists', PhPsstException::ID_IS_ALREADY_TAKEN);
            } else {
                $this->delete($password);
            }
        }

        $stmt = $this->db->prepare('INSERT INTO phPsst VALUES (:id, :password, :ttl, :views)');
        $stmt->bindValue(':id', $password->getId(), SQLITE3_TEXT);
        $stmt->bindValue(':password', $password->getPassword(), SQLITE3_TEXT);
        $stmt->bindValue(':ttl', $password->getTtl(), SQLITE3_INTEGER);
        $stmt->bindValue(':views', $password->getViews(), SQLITE3_INTEGER);
        $stmt->execute();

        $this->garbageCollection();
    }

    /**
     * @param $key
     * @return Password|null
     */
    public function get($key)
    {
        $password = null;

        $stmt = $this->db->prepare('SELECT * FROM phPsst WHERE ID = :id');
        $stmt->bindValue(':id', $key, SQLITE3_TEXT);
        $result = $stmt->execute();
        if (($row = $result->fetchArray())) {
            $password = new Password($row['id'], $row['password'], $row['ttl'], $row['views']);
            if (($row['ttl'] < time())) {
                $this->delete($password);
                $password = null;
            }
        }

        return $password;
    }

    /**
     * @param Password $password
     */
    public function delete(Password $password)
    {
        $stmt = $this->db->prepare('DELETE FROM phPsst WHERE ID = :id');
        $stmt->bindValue(':id', $password->getId(), SQLITE3_TEXT);
        $stmt->execute();

        $this->garbageCollection();
    }

    /**
     */
    protected function garbageCollection()
    {
        if (!$this->gcProbability || rand(1, $this->gcProbability) !== 1) {
            return;
        }

        $stmt = $this->db->prepare('DELETE FROM phPsst WHERE ttl < :time');
        $stmt->bindValue(':time', time(), SQLITE3_INTEGER);
        $stmt->execute();
    }
}
