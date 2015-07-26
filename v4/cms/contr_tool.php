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
if (!isset($ini)) {
    require_once'../Ini.php';
    $ini = new Ini();
}
?>
<h2>./dictionary/dictionary.xml</h2>
<h3>unused strings</h3>
<?php
$path = Ini::$path_internal . "cms/*";
foreach (Language::$dictionary as $k => $v) {
    $search = array('dictionary["s' . $k . '"]', "Language::string(" . $k . ")");

    $found = false;
    foreach ($search as $s) {
        $result = `grep -r -l -F '$s' $path`;
        if (trim($result) != "") {
            $found = true;
            break;
        }
    }
    if (!$found) {
        ?>
        <div style="margin-left:5px;">#<?= $k ?> not found!</div>
        <?php
    }
}
?>