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

namespace SP\Core\Tools;

use SP\Core\Models\Entity;

class Filter
{

    /**
     * @param Entity $entity
     * @param string ...$target_classes
     * @return Entity
     */
    public static function filterSubstitutions(Entity $entity, ...$target_classes): Entity
    {
        $filtered = [];

        foreach($entity->getSubstitutions() as $substitution)
        {
            foreach($substitution->getClasses() as $class)
            {
                foreach($target_classes as $target_class)
                {
                    if($class == $target_class)
                    {
                        array_push($filtered, $substitution);
                        break;
                    }
                }
            }
        }

        return new Entity($entity->getDate(), $entity->getLastUpdated(), $filtered, $entity->getGeneralCancellation(), $entity->getAnnouncements());
    }

}