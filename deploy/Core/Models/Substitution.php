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

class Substitution implements JsonSerializable
{

    private array $classes;
    private Teacher $teacher;
    private Room $room;
    private array $lessons;
    private Subject $subject;
    private Type $type;
    private Notice $notice;

    /**
     * Substitution constructor.
     *
     * @param string[] $classes
     * @param Teacher $teacher
     * @param Room $room
     * @param int[] $lessons
     * @param Subject $subject
     * @param Type $type
     * @param Notice $notice
     */
    public function __construct(array $classes, Teacher $teacher, Room $room, array $lessons, Subject $subject, Type $type, Notice $notice)
    {
        $this->classes = $classes;
        $this->teacher = $teacher;
        $this->room = $room;
        $this->lessons = $lessons;
        $this->subject = $subject;
        $this->type = $type;
        $this->notice = $notice;
    }

    /**
     * @return string[]
     */
    public function getClasses(): array
    {
        return $this->classes;
    }

    /**
     * @return Teacher
     */
    public function getTeacher(): Teacher
    {
        return $this->teacher;
    }

    /**
     * @return Room
     */
    public function getRoom(): Room
    {
        return $this->room;
    }

    /**
     * @return int[]
     */
    public function getLessons(): array
    {
        return $this->lessons;
    }

    /**
     * @return Subject
     */
    public function getSubject(): Subject
    {
        return $this->subject;
    }

    /**
     * @return Type
     */
    public function getType(): Type
    {
        return $this->type;
    }

    /**
     * @return Notice
     */
    public function getNotice(): Notice
    {
        return $this->notice;
    }

    public function jsonSerialize()
    {
        return [
            'classes' => $this->classes,
            'teacher' => $this->teacher,
            'room'    => $this->room,
            'lessons' => $this->lessons,
            'subject' => $this->subject,
            'type'    => $this->type,
            'notice'  => $this->notice
        ];
    }

}