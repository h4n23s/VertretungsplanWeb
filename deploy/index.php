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

require_once 'autoload.php';

use SP\Core\Providers\EntityProvider;
use SP\Core\Tools\Filter;
use SP\Options\Configuration;
use SP\Options\Translations;

$configurations = Configuration::getInstance()->getAllConfigurations();
$translations = Translations::getTranslations($configurations['general']['default_language']);

$selected_class = isset($_COOKIE['class']) ?
    $_COOKIE['class'] :
    $configurations['general']['classes'][0];

$entity_provider = EntityProvider::getEntityProvider($configurations['providers']['type']);
$entities = [];

for($date_offset = 0; $date_offset < $configurations['general']['forecast']; $date_offset++)
{
    $entity = $entity_provider->getEntity($date_offset);

    if($entity === null)
    {
        die('<div style="font-family: Arial,serif; font-size: 20px;">' . $translations['no_entity_provided'] . '</div>');
    }

    array_push($entities, Filter::filterSubstitutions($entity, $selected_class));
}

?>

<!DOCTYPE html>
<html lang="<?php echo $configurations['general']['default_language']; ?>">
<head>

    <meta http-equiv="content-type" content="text/html; charset=UTF-8">

    <meta name="description" content="<?php echo $configurations['general']['description']; ?>">
    <meta name="keywords" content="<?php echo implode(',', $configurations['general']['keywords']); ?>">
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

    <title><?php echo $configurations['general']['app_name']; ?> | <?php echo $translations['substitution_plan']; ?></title>

</head>
<body class="bg-white">
<div class="container-fluid">

<br>

<nav aria-label="breadcrumb">
    <ol class="breadcrumb">
        <li class="breadcrumb-item active" aria-current="page"><?php echo $translations['substitution_plan']; ?></li>
        <li class="breadcrumb-item active" aria-current="page"><?php echo $selected_class ?></li>
    </ol>
</nav>

<div class="card-group" style="align-items: flex-start;">
    <?php

    foreach($entities as $entity)
    {
        echo '<div class="card text-white bg-dark">';

        if($entity->getGeneralCancellation() != null)
        {
            echo '<div class="card-header bg-light text-dark text-center">';

            echo sprintf($translations['general_cancellation_message'],
                $entity->getGeneralCancellation()->getStart(),
                $entity->getGeneralCancellation()->getEnd()
            );

            echo '</div>';
        }

        echo '<div class="card-body"><h5 class="card-title">' . $entity->getDate()->format('l');
        echo '<button type="button" class="btn bmd-btn-icon float-right" onclick="onOpenMessage(' . $entity->getDate()->format('j') . ')" data-toggle="modal" data-target="#messageDialog">';

        if(empty($entity->getAnnouncements()))
        {
            echo '<i class="material-icons">info</i></button></h5>';
        } else {
            echo '<i class="material-icons">new_releases</i></button></h5>';
        }

        if(!empty($entity->getSubstitutions()))
        {
            foreach($entity->getSubstitutions() as $substitution)
            {
                echo '<table>';
                echo '<tbody><tr><td>';

                echo '<span class="badge mr-1" style="font-size: 13px; font-weight: 300; background-color: ' . $substitution->getType()->getTheme() . '">' . $substitution->getTeacher()->getShortenedName() . '</span>';
                echo '<span class="badge mr-1" style="font-size: 13px; font-weight: 300; background-color: ' . $substitution->getType()->getTheme() . '">' . $substitution->getSubject()->getFullName() . '</span>';
                echo '<span class="badge mr-1" style="font-size: 13px; font-weight: 300; background-color: ' . $substitution->getType()->getTheme() . '">' . $substitution->getRoom()->getName() . '</span>';

                echo '</td></tr>';

                $lessons = $substitution->getLessons();
                $lessons_formatted = (count($lessons) > 1 ? $lessons[0] . '. - ' : '') . end($lessons) . '. ' . $translations['lesson'];

                echo '<tr><td><span style="font-size: 20px; font-weight: 200;">' . $lessons_formatted . '</span></td></tr>';
                echo '<tr><td><span style="font-size: 14px; font-weight: 300;">' . (empty($substitution->getNotice()->getBody()) ? $translations['no_notices'] : $substitution->getNotice()->getBody()) . '</span></td></tr>';

                echo '</tbody></table><hr>';
            }
        } else {

            echo '<p class="card-text">' . $translations['no_substitutions'] . '</p>';
        }

        $last_updated = sprintf($translations['last_updated'],
            $entity->getLastUpdated()->format($configurations['general']['default_date_format']),
            $entity->getLastUpdated()->format($configurations['general']['default_time_format']));

        echo '<p class="card-text">';
        echo '<small class="text-muted" style="color: #a2a5a8 !important;">' . $last_updated . '</small>';
        echo '</p>';

        echo '</div></div>';
    }

    ?>
</div>

<div class="modal fade" id="messageDialog" tabindex="-1" role="dialog" aria-labelledby="messageDialogLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="messageDialogLabel"><?php echo $translations['announcements']; ?></h5>
            </div>
            <div class="modal-body">
                <?php

                foreach ($entities as $entity)
                {
                    if(!empty($entity->getAnnouncements()))
                    {
                        $announcements_formatted = '';

                        foreach($entity->getAnnouncements() as $announcement)
                        {
                            $announcements_formatted .= $announcement->getBody() . '<br><br>';
                        }
                    } else {

                        $announcements_formatted = $translations['no_announcements'];
                    }

                    echo '<div id="day' . $entity->getDate()->format('j') . '" style="display: none;">' . $announcements_formatted . '</div>';
                }

                ?>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-primary" data-dismiss="modal"><?php echo $translations['ok']; ?></button>
            </div>
        </div>
    </div>
</div>

<div class="modal fade" id="chooseClassDialog" tabindex="-1" role="dialog" aria-labelledby="chooseClassDialogLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="chooseClassDialogLabel"><?php echo $translations['preferences']; ?></h5>
            </div>
            <div class="modal-body">
                <div class="form-group">
                    <label for="classChooser"><?php echo $translations['choose_class']; ?></label>
                    <select class="form-control" id="classChooser">
                        <?php

                        foreach($configurations['general']['classes'] as $class)
                        {
                            if($class == $selected_class)
                            {
                                echo '<option value="' . $class . '" selected>' . $class . '</option>';
                            } else {
                                echo '<option value="' . $class . '">' . $class . '</option>';
                            }
                        }

                        ?>
                    </select>
                </div>

                <div class="alert alert-danger" style="font-size: 13px;" role="alert">
                    <?php echo $translations['cookies_warning']; ?>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-primary" data-dismiss="modal" onclick="setClassAsCookie()"><?php echo $translations['ok']; ?></button>
            </div>
        </div>
    </div>
</div>

<div class="modal fade" id="infoDialog" tabindex="-1" role="dialog" aria-labelledby="infoDialogLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="infoDialogLabel"><?php echo $translations['information']; ?></h5>
            </div>
            <div class="modal-body">
                <?php echo $translations['info_text']; ?>
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
    Der <a href="https://github.com/h4n23s/VertretungsplanWeb" target="_blank">Quelltext</a> dieser Webseite ist unter der GNU AGPL-3.0-Lizenz verfügbar.
</p>

</div>

<script src="assets/js/jquery-3.2.1.slim.min.js"></script>
<script src="assets/js/popper.js"></script>
<script src="assets/js/bootstrap-material-design.js"></script>

<script>
$(document).ready(function() {
    $('body').bootstrapMaterialDesign();
});

function setClassAsCookie() {
    document.cookie = 'class=' + document.getElementById('classChooser').value;
    window.location.reload(true);
}

let days = [
    <?php

    $days = [];

    foreach ($entities as $entity)
    {
        array_push($days, $entity->getDate()->format('j'));
    }

    echo implode(',', $days);

    ?>
];

function onOpenMessage(current_day) {
    for (const day of days) {
        document.getElementById('day' + day).style.display = (day === current_day) ? 'inline-block' : 'none';
    }
}
</script>

</body>
</html>
