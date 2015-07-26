<!--
    Concerto Testing Platform,
    Web based adaptive testing platform utilizing R language for computing purposes.
    
    Copyright (C) 2011  Psychometrics Centre, Cambridge University

    This program is free software: you can redistribute it and/or modify
    it under the terms of the GNU General Public License as published by
    the Free Software Foundation, either version 3 of the License, or
    (at your option) any later version.

    This program is distributed in the hope that it will be useful,
    but WITHOUT ANY WARRANTY; without even the implied warranty of
    MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
    GNU General Public License for more details.

    You should have received a copy of the GNU General Public License
    along with this program.  If not, see <http://www.gnu.org/licenses/>.
-->
<?php
if (!isset($ini)) {
    require_once '../../model/Ini.php';
    $ini = new Ini();
}
?>
<script type="text/javascript">
	
    $(function(){
        $("#panelLogin").dialog({
            modal:true,
            width:200,
            resizable:false,
            buttons:
                {
                "<?= Language::string(74) ?>": function() { User.uiLogin(); },
                "<?= Language::string(75) ?>": function() { $(this).dialog("close"); }
            }
        });
    });
</script>

<div id="panelLogin" title="<?= Language::string(83) ?>">
    <form>
        <table>
            <tr>
                <td><label for="login"><?= Language::string(76) ?></label></td>
                <td><input type="text" id="login" name="login" /></td>
            </tr>
            <tr>
                <td><label for="password"><?= Language::string(77) ?></label></td>
                <td><input type="password" id="password" name="password" /></td>
            </tr>
        </table>
    </form>
</div>