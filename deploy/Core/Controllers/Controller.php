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

abstract class Controller
{

    public function __construct(Request $request)
    {
        switch($_SERVER['REQUEST_METHOD'])
        {

            case 'GET':
                $response = $this->get($request);
                break;

            case 'POST':
                $response = $this->post($request);
                break;

            case 'DELETE':
                $response = $this->delete($request);
                break;

            case 'PUT':
                $response = $this->put($request);
                break;

            default:
                $response = Response::getDefaultResponse(405);

        }

        foreach($response->getHeaders() as $header)
        {
            header($header->getKey() . ':' . $header->getValue(), true);
        }

        http_response_code($response->getCode());
        header("Content-Type: application/json", true);

        echo json_encode($response);
    }

    protected abstract function get(Request $request): Response;
    protected abstract function post(Request $request): Response;
    protected abstract function delete(Request $request): Response;
    protected abstract function put(Request $request): Response;

}