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

class Type implements JsonSerializable
{

    private string $identifier;
    private string $theme;

    /**
     * Type constructor.
     * @param string $identifier
     * @param string $theme
     */
    private function __construct(string $identifier, string $theme)
    {
        $this->identifier = $identifier;
        $this->theme = $theme;
    }

    /**
     * @return string
     */
    public function getIdentifier(): string
    {
        return $this->identifier;
    }

    /**
     * @return string
     */
    public function getTheme(): string
    {
        return $this->theme;
    }

    public static function TYPE_SUBSTITUTION(): Type { return new Type('substitution', '#FFB300'); }
    public static function TYPE_CANCELLATION(): Type { return new Type('cancellation', '#D81B60'); }
    public static function TYPE_SHIFT(): Type { return new Type('shift', '#1BD893'); }
    public static function TYPE_AUTONOMOUS_WORK(): Type { return new Type('autonomous_work', '#43A047'); }
    public static function TYPE_ROOM_CHANGE(): Type { return new Type('room_change', '#00ACC1'); }
    public static function TYPE_UNKNOWN(): Type { return new Type('unknown', '#929292'); }

    /**
     * @return mixed
     */
    public function jsonSerialize()
    {
        return [
            'identifier' => $this->identifier,
            'theme' => $this->theme
        ];
    }

}