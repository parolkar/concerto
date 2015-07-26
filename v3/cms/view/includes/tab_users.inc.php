<?php
/*
  Concerto Platform - Online Adaptive Testing Platform
  Copyright (C) 2011-2013, The Psychometrics Centre, Cambridge University

  This program is free software; you can redistribute it and/or
  modify it under the terms of the GNU General Public License
  as published by the Free Software Foundation; version 2
  of the License, and not any of the later versions.

  This program is distributed in the hope that it will be useful,
  but WITHOUT ANY WARRANTY; without even the implied warranty of
  MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
  GNU General Public License for more details.

  You should have received a copy of the GNU General Public License
  along with this program; if not, write to the Free Software
  Foundation, Inc., 51 Franklin Street, Fifth Floor, Boston, MA  02110-1301, USA.
 */

if (!isset($ini))
{
    require_once'../../Ini.php';
    $ini = new Ini();
}

$logged_user = User::get_logged_user();
if ($logged_user == null) die(Language::string(81));

$class_names = array("User", "UserGroup", "UserType");
$class_labels = array(Language::string(89), Language::string(91), Language::string(90));
$readables = array($logged_user->is_module_accesible($class_names[0]), $logged_user->is_module_accesible($class_names[1]), $logged_user->is_module_accesible($class_names[2]));
$writeables = array($logged_user->is_module_writeable($class_names[0]), $logged_user->is_module_writeable($class_names[1]), $logged_user->is_module_writeable($class_names[2]));

if ($readables[0] || $readables[1] || $readables[2])
{
    ?>

    <script>
        $(function(){
           $("#divUserTabs").tabs(); 
        });
    </script>

    <div id="divUserTabs">
        <ul>
            <?php
            for ($i = 0; $i < 3; $i++)
            {
                if (!$readables[$i]) continue;
                ?>
                <li><a href="#divUserTabs<?= $class_names[$i] ?>"><?= $class_labels[$i] ?></a></li>
                <?php
            }
            ?>
        </ul>
        <?php
        for ($j = 0; $j < 3; $j++)
        {
            if (!$readables[$j]) continue;
            ?>
            <div id="divUserTabs<?= $class_names[$j] ?>">
                <?php
                $class_name = $class_names[$j];
                $class_label = $class_labels[$j];
                $readable = $readables[$j];
                $writeable = $writeables[$j];

                include Ini::$path_internal . "cms/view/includes/tab.inc.php";
                ?>
            </div>
            <?php
        }
        ?>
    </div>
    <?php
}
?>


