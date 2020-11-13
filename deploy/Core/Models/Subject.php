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

namespace SP\Core\Models;

use JsonSerializable;

class Subject implements JsonSerializable
{

    private $shortened_name;
    private $full_name;

    /**
     * Subject constructor.
     *
     * @param string $shortened_name
     * @param string|null $full_name
     */
    public function __construct(string $shortened_name, ?string $full_name = null)
    {
        $this->shortened_name = $shortened_name;
        $this->full_name = $full_name;
    }

    /**
     * @return string
     */
    public function getShortenedName(): string
    {
        return $this->shortened_name;
    }

    /**
     * @return string|null
     */
    public function getFullName(): ?string
    {
        return $this->full_name;
    }

    public function jsonSerialize()
    {
        return [
            'shortened_name' => $this->shortened_name,
            'full_name'      => $this->full_name
        ];
    }

}