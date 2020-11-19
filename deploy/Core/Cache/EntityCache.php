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

use InvalidArgumentException;
use SP\Core\Models\Entity;
use SP\Options\Configuration;

abstract class EntityCache
{

    protected $configurations;
    private $cache_timeout;

    /**
     * EntityCache constructor.
     */
    public function __construct()
    {
        $this->configurations = Configuration::getInstance()->getAllConfigurations();
        $this->cache_timeout = $this->configurations['caches']['timeout'];
    }


    /**
     * Inserts a provided {@code Entity} for a given date offset into the cache.
     *
     * @param Entity $entity
     * @param int $date_offset
     */
    public abstract function updateEntity(Entity $entity, int $date_offset): void;

    /**
     * Returns an {@code Entity} for a given date offset.
     * If the requested {@code Entity} doesnt exist, {@code null} is returned instead.
     *
     * @param int $date_offset
     * @return Entity|null
     */
    public abstract function getEntity(int $date_offset): ?Entity;

    /**
     * Returns the UNIX timestamp at which an entity was updated or {@code -1}
     * in case the {@code Entity} doesn't exist (yet).
     *
     * @param int $date_offset
     * @return int
     */
    protected abstract function getLastUpdated(int $date_offset): int;

    /**
     * Returns whether the cached {@code Entity} for a given date offset is
     * expired, calculated by the {@code timeout}-value specified in the configuration.
     *
     * @param int $date_offset
     * @return bool
     */
    public function isCacheExpired(int $date_offset): bool
    {
        $last_updated = $this->getLastUpdated($date_offset);

        if($last_updated > 0)
        {
            return (time() - $last_updated) >= $this->cache_timeout;
        }

        return true;
    }

    protected abstract static function getType(): string;

    public static function getEntityCache(string $type): EntityCache
    {
        switch($type)
        {

            case MysqlEntityCache::getType():
                return new MysqlEntityCache();

            case SqliteEntityCache::getType():
                return new SqliteEntityCache();

        }

        throw new InvalidArgumentException();
    }

}