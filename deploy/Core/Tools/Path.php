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

class Path
{

    private array $parts;

    /**
     * Path constructor.
     *
     * @param string[] $parts
     */
    private function __construct(array $parts)
    {
        $this->parts = $parts;
    }

    public static function parse(string $path)
    {
        $parts = preg_split('/(\/|\\\)+/', $path);
        $parts = array_filter($parts, 'trim');

        return new Path($parts);
    }

    public function join(Path $path): Path
    {
        return new Path(array_merge($this->parts, $path->parts));
    }

    public function __toString(): string
    {
        return implode(DIRECTORY_SEPARATOR, $this->parts);
    }

}