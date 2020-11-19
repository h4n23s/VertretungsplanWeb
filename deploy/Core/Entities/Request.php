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

class Request
{

    private $ip;
    private $user_agent;
    private $headers;
    private $cookies;
    private $queries;

    /**
     * Request constructor.
     *
     * @param string $ip
     * @param string $user_agent
     * @param Header[] $headers
     * @param Cookie[] $cookies
     * @param Query[] $queries
     */
    public function __construct(string $ip, string $user_agent, array $headers, array $cookies, array $queries)
    {
        $this->ip = $ip;
        $this->user_agent = $user_agent;
        $this->headers = $headers;
        $this->cookies = $cookies;
        $this->queries = $queries;
    }

    /**
     * @return string
     */
    public function getIp(): string
    {
        return $this->ip;
    }

    /**
     * @return string
     */
    public function getUserAgent(): string
    {
        return $this->user_agent;
    }

    /**
     * @return Header[]
     */
    public function getHeaders(): array
    {
        return $this->headers;
    }

    /**
     * @return Cookie[]
     */
    public function getCookies(): array
    {
        return $this->cookies;
    }

    /**
     * @return Query[]
     */
    public function getQueries(): array
    {
        return $this->queries;
    }

    public static function create(): Request
    {
        $headers = [];
        $cookies = [];
        $queries = [];

        foreach(getallheaders() as $key => $value)
        {
            array_push($headers, new Header($key, $value));
        }

        foreach($_COOKIE as $name => $value)
        {
            array_push($cookies, new Cookie($name, $value));
        }

        if(isset($_SERVER['QUERY_STRING']))
        {
            parse_str($_SERVER['QUERY_STRING'], $temp_queries);

            foreach($temp_queries as $key => $values)
            {
                array_push($queries, new Query($key, is_array($values) ? $values : [$values]));
            }
        }

        return new Request($_SERVER['REMOTE_ADDR'], $_SERVER['HTTP_USER_AGENT'], $headers, $cookies, $queries);
    }

}