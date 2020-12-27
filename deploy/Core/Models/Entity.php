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

use DateTime;
use JsonSerializable;

class Entity implements JsonSerializable
{

    private DateTime $date;
    private DateTime $last_updated;

    private array $substitutions;
    private ?GeneralCancellation $general_cancellation;

    private array $announcements;

    /**
     * SchoolDay constructor.
     *
     * @param DateTime $date
     * @param DateTime $last_updated
     * @param Substitution[] $substitutions
     * @param GeneralCancellation|null $general_cancellation
     * @param Announcement[] $announcements
     */
    public function __construct(DateTime $date, DateTime $last_updated, array $substitutions,
                                ?GeneralCancellation $general_cancellation, array $announcements)
    {
        $this->date = $date;
        $this->last_updated = $last_updated;
        $this->substitutions = $substitutions;
        $this->general_cancellation = $general_cancellation;
        $this->announcements = $announcements;
    }

    /**
     * @return DateTime
     */
    public function getDate(): DateTime
    {
        return $this->date;
    }

    /**
     * @return DateTime
     */
    public function getLastUpdated(): DateTime
    {
        return $this->last_updated;
    }

    /**
     * @return Substitution[]
     */
    public function getSubstitutions(): array
    {
        return $this->substitutions;
    }

    /**
     * @return GeneralCancellation|null
     */
    public function getGeneralCancellation(): ?GeneralCancellation
    {
        return $this->general_cancellation;
    }

    /**
     * @return Announcement[]
     */
    public function getAnnouncements(): array
    {
        return $this->announcements;
    }

    public function jsonSerialize()
    {
        return [
            'date'                 => $this->date->format(DATE_ISO8601),
            'last_updated'         => $this->last_updated->format(DATE_ISO8601),
            'substitutions'        => $this->substitutions,
            'general_cancellation' => $this->general_cancellation,
            'announcements'        => $this->announcements
        ];
    }

}