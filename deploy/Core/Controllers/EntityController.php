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

use SP\Core\Entities\Query;
use SP\Core\Entities\Request;
use SP\Core\Entities\Response;
use SP\Core\Providers\EntityProvider;
use SP\Core\Tools\Filter;
use SP\Options\Configuration;

class EntityController extends Controller
{

    /**
     * @param Request $request
     * @return Response
     */
    protected function get(Request $request): Response
    {
        $date_offset_query = Query::findQuery($request->getQueries(), 'date_offset');

        if(isset($date_offset_query))
        {
            $configurations = Configuration::getInstance()->getAllConfigurations();

            $date_offset = intval($date_offset_query->getValues()[0]);
            $classes = Query::findQuery($request->getQueries(), 'classes');

            if($date_offset >= $configurations['general']['forecast'])
            {
                $date_offset = $configurations['general']['forecast'] - 1;
            } elseif($date_offset < 0)
            {
                $date_offset = 0;
            }

            $entity = EntityProvider::getEntityProvider($configurations['providers']['type'])->getEntity($date_offset);

            if($entity == null)
            {
                return Response::getDefaultResponse(500);
            }

            if(isset($classes))
            {
                return new Response(200, [], Filter::filterSubstitutions($entity, ...$classes->getValues())->jsonSerialize());
            } else {
                return new Response(200, [], $entity->jsonSerialize());
            }
        } else {
            return Response::getDefaultResponse(400, 'date_offset (query) was not specified');
        }
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