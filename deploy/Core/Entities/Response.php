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

use JsonSerializable;

class Response implements JsonSerializable
{

    private static $http_messages = [
        200 => 'Ok',
        404 => 'Not Found',
        500 => 'Internal Server Error',
        501 => 'Not Implemented'
    ];

    private $code;
    private $headers;
    private $body;

    /**
     * Response constructor.
     *
     * @param int $code
     * @param Header[] $headers
     * @param array $body
     */
    public function __construct(int $code, array $headers, array $body)
    {
        $this->code = $code;
        $this->headers = $headers;
        $this->body = $body;
    }

    /**
     * @return int
     */
    public function getCode(): int
    {
        return $this->code;
    }

    /**
     * @return Header[]
     */
    public function getHeaders(): array
    {
        return $this->headers;
    }

    /**
     * @return array
     */
    public function getBody(): array
    {
        return $this->body;
    }

    public static function getDefaultResponse(int $code, string $reason = ''): Response
    {
        $message = array_key_exists($code, Response::$http_messages) ? Response::$http_messages[$code] : '';

        return new Response($code, [], [
            'code' => $code,
            'message' => $message,
            'reason' => $reason
        ]);
    }

    public function jsonSerialize(): array
    {
        return $this->body;
    }

}