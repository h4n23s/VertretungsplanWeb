<?php

declare(strict_types=1);

// Copyright (C) 2020 Hannes Gehrold
//
// This program is free software: you can redistribute it and/or modify
// it under the terms of the GNU Affero General Public License as published
// by the Free Software Foundation, either version 3 of the License, or
// (at your option) any later version.
//
// This program is distributed in the hope that it will be useful,
// but WITHOUT ANY WARRANTY; without even the implied warranty of
// MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
// GNU Affero General Public License for more details.
//
// You should have received a copy of the GNU Affero General Public License
// along with this program.  If not, see <https://www.gnu.org/licenses/>.

namespace SP\Core\Cache;

use PDO;
use SP\Core\Models\Entity;

class MysqlEntityCache extends EntityCache
{

    private $pdo;

    /**
     * MysqlEntityCache constructor.
     */
    public function __construct()
    {
        parent::__construct();

        $this->pdo = new PDO('mysql:host=' . $this->configurations['cache.mysql']['host'] . ';dbname=' . $this->configurations['cache.mysql']['dbname'],
            $this->configurations['cache.mysql']['username'],
            $this->configurations['cache.mysql']['password']);

        $this->pdo->exec('create table if not exists cache
                                (
                                    `date-offset` tinyint not null primary key unique,
                                    `updated`     bigint  not null,
                                    `data`        blob    not null
                                ) charset = utf8');
    }

    public function updateEntity(Entity $entity, int $date_offset): void
    {
        $statement = $this->pdo->prepare("INSERT INTO cache (`date-offset`, `updated`, `data`) VALUES (:date_offset, :updated, :data) ON DUPLICATE KEY UPDATE `updated`=:updated,`data`=:data");

        $statement->bindValue(':date_offset', $date_offset, PDO::PARAM_INT);
        $statement->bindValue(':updated', time(), PDO::PARAM_INT);
        $statement->bindValue(':data', serialize($entity), PDO::PARAM_LOB);

        $statement->execute();
    }

    /**
     * @param int $date_offset
     * @return Entity|null {@code null} if entity was not set yet
     */
    public function getEntity(int $date_offset): ?Entity
    {
        $statement = $this->pdo->prepare("SELECT `data` FROM cache WHERE `date-offset`=? LIMIT 1");
        $statement->execute(array($date_offset));

        $result = $statement->fetch(PDO::FETCH_ASSOC);

        if(!empty($result))
        {
            return unserialize($result['data']);
        }

        return null;
    }

    /**
     * @param int $date_offset
     * @return int
     */
    protected function getLastUpdated(int $date_offset): int
    {
        $statement = $this->pdo->prepare("SELECT `updated` FROM cache WHERE `date-offset`=? LIMIT 1");
        $statement->execute(array($date_offset));

        $result = $statement->fetch(PDO::FETCH_ASSOC);

        if(!empty($result))
        {
            return intval($result['updated']);
        }

        return -1;
    }

    protected static function getType(): string
    {
        return 'mysql';
    }

}