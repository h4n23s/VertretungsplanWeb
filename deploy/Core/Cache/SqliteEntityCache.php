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

use SP\Core\Models\Entity;
use SQLite3;

class SqliteEntityCache extends EntityCache
{

    private SQLite3 $sqlite;

    public function __construct()
    {
        parent::__construct();

        $this->sqlite = new SQLite3($this->configurations['cache.sqlite']['file']);
        $this->sqlite->exec('create table if not exists cache
                            (
                                `date-offset` tinyint not null primary key unique,
                                `updated`     bigint  not null,
                                `data`        blob    not null
                            )');
    }

    /**
     * @param Entity $entity
     * @param int $date_offset
     */
    public function updateEntity(Entity $entity, int $date_offset): void
    {
        $statement = $this->sqlite->prepare("INSERT OR REPLACE INTO cache (`date-offset`, `updated`, `data`) VALUES (:date_offset, :updated, :data)");

        $statement->bindValue(':date_offset', $date_offset, SQLITE3_INTEGER);
        $statement->bindValue(':updated', time(), SQLITE3_INTEGER);
        $statement->bindValue(':data', serialize($entity), SQLITE3_BLOB);

        $statement->execute();
    }

    /**
     * @param int $date_offset
     * @return Entity|null
     */
    public function getEntity(int $date_offset): ?Entity
    {
        $statement = $this->sqlite->prepare("SELECT `data` FROM cache WHERE `date-offset`=:date_offset LIMIT 1");
        $statement->bindValue(':date_offset', $date_offset);

        $result = $statement->execute()->fetchArray(SQLITE3_ASSOC);

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
        $statement = $this->sqlite->prepare("SELECT `updated` FROM cache WHERE `date-offset`=:date_offset LIMIT 1");
        $statement->bindValue(':date_offset', $date_offset);

        $result = $statement->execute()->fetchArray(SQLITE3_ASSOC);

        if(!empty($result))
        {
            return intval($result['updated']);
        }

        return -1;
    }

    /**
     * @return string
     */
    protected static function getType(): string
    {
        return 'sqlite';
    }

}