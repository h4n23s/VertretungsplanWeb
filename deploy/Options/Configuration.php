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

namespace SP\Options;

class Configuration
{

    private static Configuration $instance;
    private array $all_configurations;

    private function __construct()
    {
        $this->all_configurations = parse_ini_file('config.ini', true, INI_SCANNER_TYPED);
    }

    /**
     * @param string $topic
     * @return array
     */
    public function getConfigurations(string $topic): array
    {
        return $this->all_configurations[$topic];
    }

    /**
     * @return array
     */
    public function getAllConfigurations(): array
    {
        return $this->all_configurations;
    }

    public static function getInstance(): Configuration
    {
        if(!isset(self::$instance))
        {
            self::$instance = new self();
        }

        return self::$instance;
    }

}