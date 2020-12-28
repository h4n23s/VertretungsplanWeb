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

namespace SP\Core\Providers;

use InvalidArgumentException;
use SP\Core\Cache\EntityCache;
use SP\Core\Models\Entity;
use SP\Options\Configuration;

abstract class EntityProvider
{

    /**
     * @param int $date_offset
     * @return Entity|null
     */
    public function getEntity(int $date_offset): ?Entity
    {
        $configurations = Configuration::getInstance()->getConfigurations('caches');

        if($configurations['enabled'])
        {
            $entity_cache = EntityCache::getEntityCache($configurations['type']);

            if($entity_cache->isCacheExpired($date_offset))
            {
                $entity = $this->getLiveEntity($date_offset);

                if($entity === null)
                {
                    // Replace entity with already existing one in order to reset cache timeout.
                    $entity = $entity_cache->getEntity($date_offset);
                }

                $entity_cache->updateEntity($entity, $date_offset);
                return $entity;
            }

            return $entity_cache->getEntity($date_offset);
        } else {

            return $this->getLiveEntity($date_offset);
        }
    }

    /**
     * @param int $date_offset
     * @return Entity|null Returns {@code null} in case the entity was not retrieved successfully.
     */
    protected abstract function getLiveEntity(int $date_offset): ?Entity;
    protected abstract static function getType(): string;

    public static function getEntityProvider(string $identifier): EntityProvider
    {
        switch($identifier)
        {

            case WebUntisEntityProvider::getType():
                return new WebUntisEntityProvider();

        }

        throw new InvalidArgumentException();
    }

}