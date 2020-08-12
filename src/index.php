<!DOCTYPE html>
<html lang="de">
<head>

    <meta http-equiv="content-type" content="text/html; charset=UTF-8">

    <meta name="description" content="Der offizielle Online-Vertretungsplan des Gymnasiums am Waldhof. Alle aktuellen Informationen auf einen Blick!">
    <meta name="keywords" content="GaW, Gymnasium am Waldhof, Waldhof, Vertretungsplan, VPlan, Web, Bielefeld, Untis">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="robots" content="nofollow">

    <meta name="msapplication-TileColor" content="#ffffff">
    <meta name="msapplication-TileImage" content="logo-mstile.png">

    <link rel="shortcut icon" href="favicon.ico">
    <link rel="icon" type="image/png" href="logo-32x32.png" sizes="32x32">
    <link rel="icon" type="image/png" href="logo-96x96.png" sizes="96x96">
    <link rel="apple-touch-icon" sizes="180x180" href="logo-apple-touch-icon.png">

    <link rel="stylesheet" href="https://fonts.googleapis.com/css?family=Roboto:300,400,500,700|Material+Icons">
    <link rel="stylesheet" href="assets/css/bootstrap-material-design.min.css">

    <title>GaW Online-Vertretungsplan</title>

</head>
<body class="bg-white">
    <div class="container">

        <?php

            // Copyright (C) 2020 Hannes Gehrold
            //
            // This program is free software: you can redistribute it and/or modify
            // it under the terms of the GNU General Public License as published by
            // the Free Software Foundation, either version 3 of the License, or
            // (at your option) any later version.
            //
            // This program is distributed in the hope that it will be useful,
            // but WITHOUT ANY WARRANTY; without even the implied warranty of
            // MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
            // GNU General Public License for more details.
            //
            // You should have received a copy of the GNU General Public License
            // along with this program.  If not, see <https://www.gnu.org/licenses/>.


            define("ENABLE_CACHING", true);
            define("REFRESH_TIME_SECONDS", 180);

            define("DATABASE_ADDRESS", "localhost");
            define("DATABASE_USER", "root");
            define("DATABASE_PASSWORD", "");
            define("DATABASE_NAME", "substitutions");

            define("USER_AGENT", "Mozilla/5.0 (compatible; WaldhofOnlineVertretungsplan/1.1; +http://gaw-vplan.de/)");

            define("MIN_CLASS_LEN", 2);

            $classes = array("05A", "05B", "05C", "05D", "05E",
                             "06A", "06B", "06C", "06D",
                             "07A", "07B", "07C", "07D",
                             "08A", "08B", "08C", "08D",
                             "09A", "09B", "09C", "09D",
                             "EF", "Q1", "Q2");

            $subject_abbreviations = array("BI", "CH", "D", "E", "ER", "EW", "F",
                                  "EK", "GE", "G", "H", "IF", "IU", "I",
                                  "KU", "KR", "L", "M", "MU", "N", "OR", "PA",
                                  "PL", "PH", "PP", "P", "S", "S1", "SW", "SP",
                                  "TC", "WP");

            $subjects_full_form = array("Biologie", "Chemie", "Deutsch", "Englisch",
                                     "Evangelische Religion", "Erziehungswissenschaften",
                                     "Französisch", "Geographie", "Geschichte", "Griechisch",
                                     "Hebräisch", "Informatik", "Islamunterricht", "Italienisch",
                                     "Kunst", "Katholische Religion", "Latein", "Mathematik",
                                     "Musik", "Niederländisch", "Orthodoxe Religion", "Pädagogik",
                                     "Philosophie", "Physik", "Praktische Philosophie",
                                     "Philosophie", "Spanisch Alt", "Spanisch Neu",
                                     "Sozialwissenschaften", "Sport", "Technik", "Wirtschaftslehre");

            $plan_url = "...";
            $plan_params = '...';

            $current_class = isset($_COOKIE['class']) ? trim($_COOKIE['class']) : $classes[0];
            $days_messages = array();

            if(!empty($current_class) && strlen($current_class) >= MIN_CLASS_LEN) {

                echo '<br><nav aria-label="breadcrumb"><ol class="breadcrumb">';
                echo '<li class="breadcrumb-item active" aria-current="page">Vertretungsplan</li>';
                echo '<li class="breadcrumb-item active" aria-current="page">' . ltrim($current_class, "0") . '</li>';
                echo '</ol></nav>';

                echo '<div class="card-group" style="align-items: flex-start;">';

                for($date_offset = 0; $date_offset<3; $date_offset++) {

                    echo '<div class="card text-white bg-dark">';

                    $substitution_data = json_decode(getCachedContent(), true);

                    $messages = $substitution_data['payload']['messageData']['messages'];

                    if(!empty($messages)) {

                        foreach($messages as $message) {

                            $days_messages[$date_offset] = $days_messages[$date_offset] . $message['body'] . '<br><br>';
                        }
                    }

                    // Show general substitutions if there are any.
                    $general_substitution = $substitution_data['payload']['regularFreeData'];

                    if(!empty($general_substitution)) {

                        echo '<div class="card-header bg-light text-dark text-center">Der Unterricht enfällt von der ' . $general_substitution['startTime'] . '. bis zur ' . $general_substitution['endTime'] . '. Stunde!</div>';
                    }

                    echo '<div class="card-body"><h5 class="card-title">' . $substitution_data['payload']['weekDay'];
                    echo '<button type="button" class="btn bmd-btn-icon float-right" onclick="onOpenMessage(' . ($date_offset + 1) . ')" data-toggle="modal" data-target="#messageDialog">';

                    if(empty($days_messages[$date_offset])) {

                        echo '<i class="material-icons">info</i></button></h5>';
                    } else {

                        echo '<i class="material-icons">new_releases</i></button></h5>';
                    }

                    // Parse results
                    $substitution_found = false;

                    if(!empty($substitution_data['payload']['rows']) && count($substitution_data['payload']['rows']) > 0) {

                        foreach($substitution_data['payload']['rows'] as $row) {

                            $hour = strip_tags($row['data'][0]);
                            $class = strip_tags($row['data'][2]);
                            $subject = strip_tags($row['data'][3]);
                            $room = strip_tags($row['data'][4]);
                            $teacher = strip_tags($row['data'][5]);
                            $comment = strip_tags($row['data'][6]);

                            if($class == $current_class) {

                                $substitution_found = true;

                                // Replace abbreviations
                                $subject_short = explode(" ", $subject)[0];

                                for($a = 0; $a < count($subject_abbreviations); $a++) {

                                    if($subject_abbreviations[$a] == $subject_short) {

                                        $subject = str_replace($subject_abbreviations[$a], $subjects_full_form[$a], $subject);
                                        break;

                                    }
                                }

                                // Format hours
                                $space_position = strpos(trim($hour), " ");

                                if($space_position !== false) {

                                    $hour = substr_replace($hour, ". ", $space_position, 1);
                                }

                                $hour = $hour . ". Stunde";

                                // Find correct color theme and comment
                                $theme = "#929292";

                                if(strtolower(substr($teacher, 0, 3)) == "eva") {

                                    $theme = "#43A047";
                                    $comment = "Eigenverantwortliches Arbeiten";

                                } else {

                                    switch(mb_strtolower(trim(strip_tags($comment)))) {

                                        case "raumänderung":
                                            $theme = "#00ACC1";
                                            $comment = "Raumänderung";
                                            break;

                                        case "entfall":
                                            $theme = "#D81B60";
                                            $comment = "Entfall";
                                            break;

                                        case "":
                                            $theme = "#FFB300";
                                            $comment = "Vertretung";
                                            break;

                                    }
                                }

                                if(empty(trim($comment))) {

                                    $comment = "Keine weiteren Anmerkungen.";
                                }

                                echo '<table>';
                                echo '<tbody><tr><td>';

                                echo '<span class="badge mr-1" style="font-size: 13px; font-weight: 300; background-color: ' . $theme . '">' . $teacher . '</span>';
                                echo '<span class="badge mr-1" style="font-size: 13px; font-weight: 300; background-color: ' . $theme . '">' . $subject . '</span>';
                                echo '<span class="badge mr-1" style="font-size: 13px; font-weight: 300; background-color: ' . $theme . '">' . $room . '</span>';

                                echo '</td></tr>';

                                echo '<tr><td colspan="3"><span style="font-size: 20px; font-weight: 200;">' . $hour . '</span></td></tr>';
                                echo '<tr><td colspan="3"><span style="font-size: 14px; font-weight: 300;">' . $comment . '</span></td></tr>';

                                echo '</tbody></table><hr>';

                            }
                        }
                    }

                    if(!$substitution_found) {

                        echo '<p class="card-text">Keine Vertretungen.</p>';
                    }

                    echo '<p class="card-text">';
                    echo '<small class="text-muted" style="color: #a2a5a8 !important;">Aktualisiert am ' . $substitution_data['payload']['lastUpdate'] . '</small>';
                    echo '</p>';

                    echo '</div></div>';

                }

                echo '</div>';
            }

            /*
             * Returns cached content from database, if available.
             */
            function getCachedContent() {

                global $date_offset;

                if(ENABLE_CACHING) {

                    $connection = new PDO("mysql:host=" . DATABASE_ADDRESS . "; dbname=" . DATABASE_NAME, DATABASE_USER, DATABASE_PASSWORD);

                    $query = $connection->prepare("SELECT `data`, `updated` FROM `cache` WHERE `date-offset`=? LIMIT 1");
                    $query->execute(array($date_offset));

                    $cached_content = $query->fetch(PDO::FETCH_ASSOC);

                    if(!empty($cached_content)) {

                        if(abs(time() - $cached_content['updated']) >= REFRESH_TIME_SECONDS) {

                            $substitution_data = getSubstitutionData();

                            $query = $connection->prepare("UPDATE `cache` SET `data`=?, `updated`=? WHERE `date-offset`=?");
                            $query->execute(array($substitution_data, time(), $date_offset));

                            return $substitution_data;

                        }

                        return $cached_content['data'];

                    } else {

                        $substitution_data = getSubstitutionData();

                        $query = $connection->prepare("INSERT INTO `cache` (`data`, `date-offset`, `updated`) VALUES (?, ?, ?)");
                        $query->execute(array($substitution_data, $date_offset, time()));

                        return $substitution_data;

                    }

                } else {

                    return getSubstitutionData();
                }
            }

            /*
             * Returns the current substitution data supplied by Untis.
             */
            function getSubstitutionData() {

                global $plan_params, $date_offset, $plan_url;

                $params_array = json_decode($plan_params, true);
                $params_array['date'] = date("Ymd");
                $params_array['dateOffset'] = $date_offset;

                $plan_params = json_encode($params_array);

                $config = array(

                    CURLOPT_RETURNTRANSFER => true,
                    CURLOPT_HEADER         => false,
                    CURLOPT_FOLLOWLOCATION => true,
                    CURLOPT_MAXREDIRS      => 3,
                    CURLOPT_USERAGENT      => USER_AGENT,
                    CURLOPT_AUTOREFERER    => true,
                    CURLOPT_CUSTOMREQUEST  => "POST",
                    CURLOPT_POSTFIELDS     => $plan_params,
                    CURLOPT_HTTPHEADER     => array('Content-Type: application/json', 'Content-Length: ' . strlen($plan_params))

                );

                $curl = curl_init($plan_url);

                curl_setopt_array($curl, $config);
                $substitution_data = curl_exec($curl);
                curl_close($curl);

                return $substitution_data;

            }

        ?>

        <div class="modal fade" id="messageDialog" tabindex="-1" role="dialog" aria-labelledby="messageDialogLabel" aria-hidden="true">
            <div class="modal-dialog modal-dialog-centered" role="document">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title" id="messageDialogLabel">Mitteilungen</h5>
                    </div>
                    <div class="modal-body">
                        <div id="day1" style="display: none;"><?php echo empty($days_messages[0]) ? "Keine Mitteilungen." : $days_messages[0]; ?></div>
                        <div id="day2" style="display: none;"><?php echo empty($days_messages[1]) ? "Keine Mitteilungen." : $days_messages[1]; ?></div>
                        <div id="day3" style="display: none;"><?php echo empty($days_messages[2]) ? "Keine Mitteilungen." : $days_messages[2]; ?></div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-primary" data-dismiss="modal">OK</button>
                    </div>
                </div>
            </div>
        </div>

        <div class="modal fade" id="chooseClassDialog" tabindex="-1" role="dialog" aria-labelledby="chooseClassDialogLabel" aria-hidden="true">
            <div class="modal-dialog modal-dialog-centered" role="document">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title" id="chooseClassDialogLabel">Einstellungen</h5>
                    </div>
                    <div class="modal-body">
                        <div class="form-group">
                            <label for="classChooser">Klasse auswählen</label>
                            <select class="form-control" id="classChooser">
                                <?php
                                    foreach($classes as $class) {

                                        if($class == $current_class) {
                                            echo '<option value="' . $class . '" selected>' . ltrim($class, "0") . '</option>';
                                        } else {
                                            echo '<option value="' . $class . '">' . ltrim($class, "0") . '</option>';
                                        }
                                    }
                                ?>
                            </select>
                        </div>

                        <div class="alert alert-danger" style="font-size: 13px;" role="alert">
                            Durch das Einstellen einer spezifischen Klasse wird ein Cookie auf Ihrem Endgerät gespeichert.
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-primary" data-dismiss="modal" onclick="setClassAsCookie()">OK</button>
                    </div>
                </div>
            </div>
        </div>

        <div class="modal fade" id="infoDialog" tabindex="-1" role="dialog" aria-labelledby="infoDialogLabel" aria-hidden="true">
            <div class="modal-dialog modal-dialog-centered" role="document">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title" id="infoDialogLabel">Informationen</h5>
                    </div>
                    <div class="modal-body">
                        <p>Alle Informationen werden ausdrücklich <b>ohne Gewähr</b>, folglich ohne jegliche Garantie auf ihre Richtigkeit angeboten.</p>
                        <p>Es wird keine Haftung für die Inhalte externer Links übernommen.</p>
                        <hr>
                        <p><b>Entwicklung, Layout und Programmierung</b> durch Hannes Gehrold.</p>
                        <hr>
                        <p>Aus technischen Gründen speichert und verarbeitet diese Website Kommunikationsdaten.</p>
                        <p>Es werden ansonsten keine weiteren Daten gespeichert.</p>
                        <p>Unter <a href="https://www.ionos.de/hilfe/datenschutz/datenverarbeitung-von-webseitenbesuchern-ihres-11-ionos-produktes/11-ionos-webhosting/">diesem Link</a> wird spezifischer erläutert, welche Daten der Webserver speichert und verarbeitet.</p>
                        <hr>
                        <p>Das Impressum finden Sie <a href="https://www.gaw-bielefeld.de/impressum.html">hier</a>.</p>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-primary" data-dismiss="modal">OK</button>
                    </div>
                </div>
            </div>
        </div>

        <div class="fixed-bottom">
            <div class="float-right mb-3 mr-3">
                <span class="btn-group-sm align-bottom">
                    <button type="button" class="btn btn-secondary bmd-btn-fab mr-2" data-toggle="modal" data-target="#infoDialog">
                        <i class="material-icons">help_outline</i>
                    </button>
                </span>
                <button type="button" class="btn btn-primary bmd-btn-fab" data-toggle="modal" data-target="#chooseClassDialog">
                    <i class="material-icons">settings</i>
                </button>
            </div>
        </div>

        <p class="text-center text-muted mt-3 mb-3" style="position: relative; font-size: 12px; z-index: 1040">
            Der <a href="https://github.com/h4n23s/VertretungsplanWeb" target="_blank">Quelltext</a> dieser Webseite ist unter der GNU GPL-3.0-Lizenz verfügbar.
        </p>

    </div>

    <script src="assets/js/jquery-3.2.1.slim.min.js"></script>
    <script src="assets/js/popper.js"></script>
    <script src="assets/js/bootstrap-material-design.js"></script>

    <script>
        $(document).ready(function() {
            $('body').bootstrapMaterialDesign();
        });
    </script>

    <script>
        /* Custom scripts */

        function setClassAsCookie() {
            document.cookie = "class=" + document.getElementById("classChooser").value;
            window.location.reload(true);
        }

        function onOpenMessage(day) {
            for (let i = 1; i < 4; i++) {
                document.getElementById("day" + i).style.display = (i === day) ? "inline-block" : "none";
            }
        }
    </script>

</body>
</html>
