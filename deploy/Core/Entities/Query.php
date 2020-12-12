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

namespace SP\Core\Entities;

class Query
{

    private $key;
    private $values;

    /**
     * Query constructor.
     * @param string $key
     * @param string[] $values
     */
    public function __construct(string $key, array $values)
    {
        $this->key = $key;
        $this->values = $values;
    }

    /**
     * @return string
     */
    public function getKey(): string
    {
        return $this->key;
    }

    /**
     * @return string[]
     */
    public function getValues(): array
    {
        return $this->values;
    }

    /**
     * @param Query[] $queries
     * @param string $key
     * @return Query
     */
    public static function findQuery(array $queries, string $key): ?Query
    {
        foreach ($queries as $query)
        {
            if($query->getKey() === $key)
            {
                return $query;
            }
        }

        return null;
    }

}