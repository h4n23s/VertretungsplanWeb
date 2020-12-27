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

namespace SP\Core\Providers;

use DateTime;
use Exception;
use SP\Core\Models\Announcement;
use SP\Core\Models\Entity;
use SP\Core\Models\GeneralCancellation;
use SP\Core\Models\Notice;
use SP\Core\Models\Room;
use SP\Core\Models\Subject;
use SP\Core\Models\Substitution;
use SP\Core\Models\Teacher;
use SP\Core\Models\Type;
use SP\Options\Configuration;

class WebUntisEntityProvider extends EntityProvider
{

    // TODO These should be translatable
    private static array $subject_replacements = [
        'BI'    => 'Biologie',
        'CH'    => 'Chemie',
        'D'     => 'Deutsch',
        'E'     => 'Englisch',
        'ER'    => 'Evangelische Religion',
        'EW'    => 'Erziehungswissenschaften',
        'F'     => 'Französisch',
        'EK'    => 'Geographie',
        'GE'    => 'Geschichte',
        'G'     => 'Griechisch',
        'H'     => 'Hebräisch',
        'IF'    => 'Informatik',
        'IU'    => 'Islamunterricht',
        'I'     => 'Italienisch',
        'KU'    => 'Kunst',
        'KR'    => 'Katholische Religion',
        'L'     => 'Latein',
        'M'     => 'Mathematik',
        'MU'    => 'Musik',
        'N'     => 'Niederländisch',
        'OR'    => 'Orthodoxe Religion',
        'PA'    => 'Pädagogik',
        'PL'    => 'Philosophie',
        'PH'    => 'Physik',
        'PP'    => 'Praktische Philosophie',
        'P'     => 'Philosophie',
        'S'     => 'Spanisch Alt',
        'S1'    => 'Spanisch Neu',
        'SW'    => 'Sozialwissenschaften',
        'SP'    => 'Sport',
        'TC'    => 'Technik',
        'WP'    => 'Wirtschaftslehre'
    ];

    private $plan_url;
    private $plan_params;
    private $user_agent;

    /**
     * WebUntisEntityProvider constructor.
     */
    public function __construct()
    {
        $configurations = Configuration::getInstance()->getConfigurations('provider.webuntis');

        $this->plan_url = $configurations['plan_url'];
        $this->plan_params = $configurations['plan_params'];
        $this->user_agent = $configurations['user_agent'];
    }

    /**
     * @param int $date_offset
     * @return Entity|null
     */
    protected function getLiveEntity(int $date_offset): ?Entity
    {
        $payload = json_decode($this->getOnlineContent($date_offset), true)['payload'];

        if($payload !== null)
        {
            $substitutions = [];

            foreach($payload['rows'] as $row)
            {
                $classes = [strip_tags($row['group'])];

                $lessons = array_map('intval', explode('-', strip_tags($row['data'][0])));
                sort($lessons);

                $subject_full_name = null;
                $subject_parts = explode(' ', strip_tags($row['data'][3]), 2);

                foreach(self::$subject_replacements as $subject_abbreviation => $subject_replacement)
                {
                    if($subject_parts[0] === $subject_abbreviation)
                    {
                        $subject_full_name = (count($subject_parts) > 1) ?
                            $subject_replacement . ' ' . $subject_parts[1] :
                            $subject_replacement;

                        break;
                    }
                }

                $subject = new Subject(strip_tags($row['data'][3]), $subject_full_name);
                $room = new Room(strip_tags($row['data'][4]));
                $teacher = new Teacher(strip_tags($row['data'][5]));
                $notice = new Notice(strip_tags($row['data'][6]));

                $type = match($row['cssClasses'][1])
                {
                    'wu-fg-changedElement' => Type::TYPE_ROOM_CHANGE(),
                    'wu-fg-cancelled' => Type::TYPE_CANCELLATION(),
                    'wu-fg-substitution' => Type::TYPE_SUBSTITUTION(),
                    'wu-fg-shift' => Type::TYPE_SHIFT(),

                    default => Type::TYPE_UNKNOWN()
                };

                array_push($substitutions, new Substitution($classes, $teacher, $room, $lessons, $subject, $type, $notice));
            }

            $general_cancellation = null;

            if(isset($payload['regularFreeData']))
            {
                $general_cancellation = new GeneralCancellation(
                    intval($payload['regularFreeData']['startTime']),
                    intval($payload['regularFreeData']['endTime']));
            }

            $announcements = [];

            if(isset($payload['messageData']['messages']))
            {
                foreach($payload['messageData']['messages'] as $message)
                {
                    $announcement = new Announcement(strip_tags($message['subject']), strip_tags($message['body']));
                    array_push($announcements, $announcement);
                }
            }

            try
            {
                $date = new DateTime((string) $payload['date']);
                $last_update = new DateTime($payload['lastUpdate']);

                return new Entity($date, $last_update, $substitutions, $general_cancellation, $announcements);

            } catch (Exception) {}
        }

        return null;
    }

    /**
     * Returns the current substitution data supplied by Untis.
     *
     * @param $date_offset
     * @return string
     */
    private function getOnlineContent($date_offset): string
    {
        $params_array = json_decode($this->plan_params, true);
        $params_array['date'] = date('Ymd');
        $params_array['dateOffset'] = $date_offset;

        $params_encoded = json_encode($params_array);

        $config = [

            CURLOPT_HEADER         => false,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_FOLLOWLOCATION => true,
            CURLOPT_AUTOREFERER    => true,
            CURLOPT_MAXREDIRS      => 3,
            CURLOPT_CUSTOMREQUEST  => 'POST',
            CURLOPT_POSTFIELDS     => $params_encoded,
            CURLOPT_USERAGENT      => $this->user_agent,
            CURLOPT_HTTPHEADER     => [
                'Content-Type: application/json',
                'Content-Length: ' . strlen($params_encoded)
            ]

        ];

        $curl = curl_init($this->plan_url);

        curl_setopt_array($curl, $config);
        $substitution_data = curl_exec($curl);
        curl_close($curl);

        return html_entity_decode($substitution_data);
    }

    /**
     * @return string
     */
    protected static function getType(): string
    {
        return 'webuntis';
    }

}