<?php

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

require_once 'Options/Configuration.php';
require_once 'Core/Tools/Path.php';

use SP\Core\Tools\Path;
use SP\Options\Configuration;

spl_autoload_register(function($class) {

    if(substr($class, 0, 2) === 'SP') {

        $class_directory = Path::parse(substr($class, strpos($class, '\\') + 1));
        $project_directory = Path::parse(Configuration::getInstance()->getConfigurations('general')['project_dir']);
        $document_root = Path::parse($_SERVER['DOCUMENT_ROOT']);

        require_once $document_root->join($project_directory)->join($class_directory) . '.php';

    }
});