<?php
/*
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
 */

if (!isset($ini)) {
    require_once '../../model/Ini.php';
    $ini = new Ini();
}
$user = User::get_logged_user();
if ($user == null)
    die(Language::string(85));
?>

<script>
    $(function () {
        $('#form<?= $class_name ?>FileUploadForm').fileUploadUI({
            uploadTable: $('#form<?= $class_name ?>UploadingFiles'),
            downloadTable: $('#form<?= $class_name ?>UploadedFiles'),
            buildUploadRow: function (files, index) {
                return $('<tr><td class="ui-widget-content ui-corner-all">' + files[index].name + '<\/td>' +
                    '<td class="file_upload_progress"><div><\/div><\/td>' +
                    '<td class="file_upload_cancel">' +
                    '<button class="ui-state-default ui-corner-all" title="Cancel">' +
                    '<span class="ui-icon ui-icon-cancel">Cancel<\/span>' +
                    '<\/button><\/td><\/tr>');
            },
            buildDownloadRow: function (file) 
            {
                switch(file.code)
                {
                    case "-1": Methods.alert("Max files limit reached. Delete current files to upload new ones.","alert");
                        break;
                }
<?= $class_name ?>.uiFilesList();
                if(<?= $class_name ?>.extraFileUploadCallback) <?= $class_name ?>.extraFileUploadCallback();
                return $('');
            }
        });
    });

</script>

<form id="form<?= $class_name ?>FileUploadForm" action="query/file_upload.php" method="POST" enctype="multipart/form-data">
    <input type="hidden" name="Module_id" value="<?= DS_Module::from_value($class_name)->id ?>" />
    <input type="hidden" name="object_id" value="<?= $oid ?>" />
    <input type="hidden" name="temp_id" value="<?= $temp_id ?>" />
    <input type="file" name="file">
    <button>Upload</button>
    <div class="ui-widget-header ui-corner-all">Upload file</div>
</form>
<table class="fullWidth ui-widget-content ui-corner-all" id="form<?= $class_name ?>UploadingFiles">
</table>

<table class="fullWidth ui-widget-content ui-corner-all" id="form<?= $class_name ?>UploadedFilesTable">
    <thead>
        <tr>
            <th class="ui-widget-header ui-corner-all">id</th>
            <th class="ui-widget-header ui-corner-all">name</th>
            <th class="fullWidth ui-widget-header ui-corner-all">stored as</th>
            <th class="ui-widget-header ui-corner-all">size</th>
            <th class="ui-widget-header ui-corner-all">md5</th>
            <th class="ui-widget-header ui-corner-all">actions</th>
        </tr>
    </thead>
    <tbody id="form<?= $class_name ?>UploadedFiles">
        <?php include Setting::$path_internal . 'view/files_list.php'; ?>
    </tbody>
</table>