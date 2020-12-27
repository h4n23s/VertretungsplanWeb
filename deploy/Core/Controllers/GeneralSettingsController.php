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

namespace SP\Core\Controllers;

use SP\Core\Entities\Request;
use SP\Core\Entities\Response;
use SP\Options\Configuration;

class GeneralSettingsController extends Controller
{

    /**
     * @param Request $request
     * @return Response
     */
    protected function get(Request $request): Response
    {
        return new Response(200, [], Configuration::getInstance()->getConfigurations('general'));
    }

    /**
     * @param Request $request
     * @return Response
     */
    protected function post(Request $request): Response
    {
        // Method not supported
        return Response::getDefaultResponse(405);
    }

    /**
     * @param Request $request
     * @return Response
     */
    protected function delete(Request $request): Response
    {
        // Method not supported
        return Response::getDefaultResponse(405);
    }

    /**
     * @param Request $request
     * @return Response
     */
    protected function put(Request $request): Response
    {
        // Method not supported
        return Response::getDefaultResponse(405);
    }

}