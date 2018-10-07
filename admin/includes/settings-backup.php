<?php
/** @var $db \YAWK\db */
require_once '../system/classes/backup.php';        // backup methods and helpers
// check if backup obj is set
if (!isset($backup) || (empty($backup)))
{   // create new backup obj
    $backup = new \YAWK\BACKUP\backup();
}
// check if GET data is set
if (isset($_GET))
{   // check if delete is requested
    if (isset($_GET['deleteBackup']) == true)
    {   // check if backup folder and backup file are set and not empty
        if (isset($_GET['backupFolder']) && (!empty($_GET['backupFolder'])
        && (isset($_GET['backupFile']) && (!empty($_GET['backupFile'])))))
        {
            // delete this backup file
            if (is_dir($_GET['backupFolder']))
            {   // path (backup folder and file)
                $file = $_GET['backupFolder'].$_GET['backupFile'];
                // check if requested file is there
                if (is_file($file))
                {   // delete file
                    if (unlink($file))
                    {   // file deleted
                        \YAWK\alert::draw("success", "Backup geloescht!", "deleted: $_GET[backupFile]", "", 2600);
                    }
                    else
                        {   // failed to delete file
                            \YAWK\alert::draw("danger", "Backup nicht geloescht!", "Backup file NICHT geloescht", "", 4200);
                        }
                }
                else
                    {   // file not found - unable to delete
                        \YAWK\alert::draw("warning", "File nicht gefunden!", "$_GET[backupFolder]$_GET[backupFile] not found!", "", 6200);
                    }
            }
            else
                {   // backup folder not found
                    \YAWK\alert::draw("warning", "Folder nicht gefunden!", "$_GET[backupFolder] not found!", "", 6200);
                }
        }
        else
            {   // backup folder or file not set
                \YAWK\alert::draw("warning", "Folder or File not set!", "Folder or file is not set!", "", 6200);
            }
    }
    // check if delete archive folder is requested
    if (isset($_GET['deleteArchiveSubFolder']) === true)
    {   // check if backup folder and backup file are set and not empty
        if (isset($_GET['archiveSubFolder']) && (!empty($_GET['archiveSubFolder'])))
        {
            $backup->archiveBackupSubFolder = $_GET['archiveSubFolder'];
            // delete this backup file
            if (is_dir($backup->archiveBackupSubFolder))
            {   // path (backup folder and file)
                if (\YAWK\filemanager::recursiveRemoveDirectory($backup->archiveBackupSubFolder) === true)
                {
                    \YAWK\alert::draw("success", "DELETED", "$backup->archiveBackupSubFolder", "", 3200);
                }
            }
            else
            {   // archive sub folder not found
                \YAWK\alert::draw("warning", "Folder nicht gefunden!", "$_GET[backupFolder] not found!", "", 6200);
            }
        }
        else
        {   // backup folder or file not set
            \YAWK\alert::draw("warning", "Archive Subfolder not set!", "no folder set!", "", 6200);
        }
    }
}

// check if post data is set
if (isset($_POST))
{
    // check if action is set
    if (isset($_POST['action']))
    {   // check which action is requested
        switch ($_POST['action'])
        {
            // start backup selected
            case "startBackup":
            {
                // CHECK POST SETTINGS AND SET BACKUP PROPERTIES

                // check if backup method is set
                if (isset($_POST['backupMethod']) && (!empty($_POST['backupMethod'])))
                {   // set backup method property depending on select field
                    $backup->backupMethod = $_POST['backupMethod'];
                }

                // check if zip is enabled
                if (isset($_POST['zipBackup']) && (!empty($_POST['zipBackup'])))
                {   // zip backup to true (checkbox is checked)
                    $backup->zipBackup = $_POST['zipBackup'];
                }
                else
                    {   // zip backup disabled - checkbox not checked
                        $backup->zipBackup = false;
                    }

                // should files be removed after backup (save .zip file only)
                if (isset($_POST['removeAfterZip']) && (!empty($_POST['removeAfterZip'])))
                {   // set backup method property depending on select field
                    $backup->removeAfterZip = $_POST['removeAfterZip'];
                }
                else
                {   // remove files after zip disabled
                    $backup->removeAfterZip = "false";
                }


                // check if overwrite backup is allowed
                if (isset($_POST['overwriteBackup']) && (!empty($_POST['overwriteBackup'])))
                {   // set backup method property depending on select field
                    if ($_POST['overwriteBackup'] == "true")
                    {   // set overwrite backup property
                        $backup->overwriteBackup = $_POST['overwriteBackup'];
                    }
                    else
                        {   // false means backup will be saved in archive
                            $backup->overwriteBackup = "false";
                        }
                }

                // initialize backup
                if ($backup->init($db) === true)
                {   // ok, backup successful
                    \YAWK\alert::draw("success", $lang['BACKUP_SUCCESSFUL'], $lang['BACKUP_SUCCESSFUL_TEXT'], "", 3400);
                }
                else
                    {   // backup failed
                        \YAWK\alert::draw("danger", $lang['BACKUP_FAILED'], $lang['BACKUP_FAILED_TEXT'], "", 6400);
                    }
            }
            break;

            // upload a backup archive
            case "upload":
            {
                // restore a backup from file
            }
            break;

            // move backup to archive
            case "moveToArchive":
            {
                // check if filename was sent
                if (isset($_POST['file']) && (!empty($_POST['file'])))
                {
                    // check if new folder was entered by user
                    if (isset($_POST['newFolder']) && (!empty($_POST['newFolder'])))
                    {
                        // create new archive sub folder path
                        $backup->archiveBackupSubFolder = $backup->archiveBackupFolder.$_POST['newFolder']."/";
                        // create new directory in archive
                        if (mkdir($backup->archiveBackupSubFolder))
                        {   // all good, new archive subfolder created
                            \YAWK\alert::draw("success", $_POST['file'], "$backup->archiveBackupSubFolder erstellt!", "", 6400);
                        }
                        else
                            {   // failed to create new archive subfolder
                                \YAWK\alert::draw("danger", $_POST['file'], "$backup->archiveBackupSubFolder NICHT erstellt!", "", 6400);
                            }
                    }
                    // check if existing folder was selected by user
                    else if (isset($_POST['selectFolder']) && (!empty($_POST['selectFolder'])))
                    {   // set archive sub foder path
                        $backup->archiveBackupSubFolder = $backup->archiveBackupFolder.$_POST['selectFolder']."/";
                        \YAWK\alert::draw("info", $_POST['file'], $_POST['selectFolder'], "", 6400);
                    }
                    else
                        {   // no folder was selected - throw error msg
                            \YAWK\alert::draw("danger", $_POST['file'], "no folder selected", "", 6400);
                        }

                    // prepare backup move to archive...
                    // old file
                    $backup->archiveBackupFile = $backup->currentBackupFolder.$_POST['file'];
                    // new file
                    $backup->archiveBackupNewFile = $backup->archiveBackupSubFolder.$_POST['file'];
                    // check if file can be moved
                    if (file_exists($backup->archiveBackupFile)
                    && ((!file_exists($backup->archiveBackupNewFile)) || is_writable($backup->archiveBackupNewFile)))
                    {
                        if (rename($backup->archiveBackupFile, $backup->archiveBackupNewFile))
                        {   // success
                            \YAWK\alert::draw("success", $_POST['file'], $backup->archiveBackupNewFile, "", 6400);
                        }
                        else
                        {   // error: throw msg
                            \YAWK\alert::draw("danger", $_POST['file'], "failed to move oldfile: $backup->archiveBackupFile to $backup->archiveBackupNewFile", "", 6400);
                        }
                    }
                    else
                        {
                            \YAWK\alert::draw("warning", $_POST['file'], "unable to move file. wheter old file does not exist or new backup file is not writeable.", "", 6400);
                        }

                }
                else
                    {
                        // no file was clicked - failed to select any file
                        \YAWK\alert::draw("danger", "NO FILE SELECTED", "error: unable to get filename", "", 6400);
                    }
            }
            break;
        }
    }
}
?>
<script type="text/javascript">
    $(document).ready(function()
    {
        // EXTENDED SETTINGS
        // to improve usability of 'custom backup', all required checkboxes are grouped.
        // this piece of js manage the toggle switches on custom backup form

        // CONTENT TOGGLE SWITCH
        $('#contentCheckAll').change(function() {
            // get all checkboxes of this group
            $('.checkbox-group-content').each(function() {
                // toggle checkbox group
                $(this).find('input[type="checkbox"]').each(function () { this.checked = !this.checked; });
            });
        });

        // MEDIA TOGGLE SWITCH
        $('#mediaCheckAll').change(function() {
            // get all checkboxes of this group
            $('.checkbox-group-media').each(function() {
                // toggle checkbox group
                $(this).find('input[type="checkbox"]').each(function () { this.checked = !this.checked; });
            });
        });

        // SYSTEM TOGGLE
        $('#systemFolderCheckAll').change(function() {
            // get all checkboxes of this group
            $('.checkbox-group-system').each(function() {
                // toggle checkbox group
                $(this).find('input[type="checkbox"]').each(function () { this.checked = !this.checked; });
            });
        });

        // DATABASE TOGGLE
        $('#databaseCheckAll').change(function() {
            // get all checkboxes of this group
            $('.checkbox-group-database').each(function() {
                // toggle checkbox group
                $(this).find('input[type="checkbox"]').each(function () { this.checked = !this.checked; });
            });
        });

        // check overwrite backup switch
        $('#overwriteBackup').on('change', function()
        {   // set switch + label vars
            var overwriteBackupSwitch = $('#overwriteBackup');
            var overwriteBackupLabel = $('#overwriteBackupLabel');
            var overwriteLabelTextOn = $(overwriteBackupSwitch).attr("data-overwriteOn");
            var overwriteLabelTextOff = $(overwriteBackupSwitch).attr("data-overwriteOff");
            var newFolder = $('#newFolder');
            var selectFolder = $('#selectFolder');

            // check if overwrite switch is on
            if ($(overwriteBackupSwitch).is(':checked'))
            {   // set ON label text (overwrite current backup)
                $(overwriteBackupSwitch).val('true');
                $(overwriteBackupLabel).text(overwriteLabelTextOn);
                $('#archiveGroup').fadeOut();
            }
            else
                {   // set OFF label text (save to archive)
                    $(overwriteBackupSwitch).val('false');
                    $(overwriteBackupLabel).text(overwriteLabelTextOff);
                    $('#archiveGroup').fadeIn().removeClass('hidden');

                    // if text input field has focus
                    $(newFolder).focus(function() {
                        // select first option (please select....) to improve usability
                        $(selectFolder).prop("selectedIndex", 0);
                    });

                    // if selectfolder changes...
                    $(selectFolder).on('change', function() {
                        // set newFolder text input field value to empty
                        $(newFolder).val('');
                    });
                }

        });

        // if zip is not allowed, remove after zip should be disabled
        $('#zipBackup').change(function() {
            // var remove after zip switch
            var removeAfterZipSwitch = $('#removeAfterZip');

            // check if zip is enabled or disabled
            if ($('#zipBackup').is(':checked'))
            {   // disable remove after zip switch
                $(removeAfterZipSwitch).bootstrapToggle('on');
                $(removeAfterZipSwitch).prop('disabled', false);
            }
            else
                {   // toggle + enable switch again
                    $(removeAfterZipSwitch).bootstrapToggle('off');
                    $(removeAfterZipSwitch).prop('disabled', true);
                }
        });

        // check if pulldown (select field) has changed (any item has been selected)
        // and display additional according backup settings
        $("#backupMethod").on('change', function() {
            // store backup method value as var
            var backupMethod = this.value;

            // user selected complete backup method
            if (backupMethod === "complete")
            {   // hide all other methods
                $("#customSettings").hide();
                $("#databaseMethods").hide();
                $("#fileMethods").hide();
                // display 'complete backup' settings
                $("#completeMethods").fadeIn().removeClass('hidden');
            }

            // user selected database backup method
            if (backupMethod === "database")
            {   // hide all other methods
                $("#customSettings").hide();
                $("#completeMethods").hide();
                $("#fileMethods").hide();
                // display 'database backup' settings
                $("#databaseMethods").fadeIn().removeClass('hidden');
            }

            // user selected file backup method
            if (backupMethod === "files")
            {
                // hide all other methods
                $("#customSettings").hide();
                $("#databaseMethods").hide();
                $("#completeMethods").hide();
                // display 'file backup' settings
                $("#fileMethods").fadeIn().removeClass('hidden');
            }

            // user selected file backup method
            if (backupMethod === "custom")
            {
                // hide all other methods
                $("#databaseMethods").hide();
                $("#completeMethods").hide();
                // display 'file backup' settings
                $("#customSettings").fadeIn().removeClass('hidden');
            }
        });

        // MODAL 'move to archive' WINDOW:
        // to archive a file, a modal window is used.
        // this checks, if modal window is currently shown
        $('#myModal').on('show.bs.modal', function(e) {
            // if so, get the according file by read the data-file value
            var file = e.relatedTarget.dataset.file;
            var newFolderModal = $("#newFolderModal");
            var selectFolderModal = $("#selectFolderModal");
            // update hidden field in modal window with current file value
            $("#file").val(file);
            
            // if text input field has focus
            $(newFolderModal).focus(function() {
                // select first option (please select....) to improve usability
                $(selectFolderModal).prop("selectedIndex", 0);
            });

            // if selectfolder changes...
            $(selectFolderModal).on('change', function() {
                // set newFolder text input field value to empty
                $(newFolderModal).val('');
            });
        });

    });
</script>
<?php
// TEMPLATE WRAPPER - HEADER & breadcrumbs
echo "
    <!-- Content Wrapper. Contains page content -->
    <div class=\"content-wrapper\" id=\"content-FX\">
    <!-- Content Header (Page header) -->
    <section class=\"content-header\">";
/* draw Title on top */
echo \YAWK\backend::getTitle($lang['BACKUP'], $lang['BACKUP_SUBTEXT']);
echo"<ol class=\"breadcrumb\">
            <li><a href=\"index.php\" title=\"$lang[DASHBOARD]\"><i class=\"fa fa-dashboard\"></i> $lang[DASHBOARD]</a></li>
            <li class=\"active\"><a href=\"index.php?page=backup\" title=\"$lang[BACKUP]\"> $lang[BACKUP]</a></li>
         </ol>
    </section>
    <!-- Main content -->
    <section class=\"content\">";
/* page content start here */
?>
<div class="row">
<div class="col-md-6">
    <form name="backup" action="index.php?page=settings-backup&action=startBackup" method="POST">
    <div class="box">
        <div class="box-header">
            <h3 class="box-title">
                <?php echo $lang['BACKUP_CREATE']; ?>
            </h3>
        </div>
        <div class="box-body">
            <input type="hidden" name="action" value="startBackup">
            <button type="submit" class="btn btn-success pull-right" id="savebutton"><i class="fa fa-check" id="savebuttonIcon"></i> &nbsp;<?php echo $lang['BACKUP_CREATE']; ?></button>
            <br><br>
            <label for="backupMethod"><?php echo $lang['BACKUP_WHAT_TO_BAC8KUP']; ?></label>
            <select name="backupMethod" id="backupMethod" class="form-control">
                <optgroup label="<?php echo $lang['STANDARD']; ?>"></optgroup>
                    <option name="complete" value="complete">&nbsp;&nbsp;&nbsp;&nbsp;<?php echo $lang['BACKUP_FULL']; ?></option>
                    <option name="database" value="database">&nbsp;&nbsp;&nbsp;&nbsp;<?php echo $lang['BACKUP_DB_ONLY']; ?></option>
                    <option name="files" value="files">&nbsp;&nbsp;&nbsp;&nbsp;<?php echo $lang['BACKUP_FILES_ONLY']; ?></option>
                <optgroup label="<?php echo $lang['CUSTOM']; ?>"></optgroup>
                    <option name="custom" value="custom">&nbsp;&nbsp;&nbsp;&nbsp;<?php echo $lang['BACKUP_CUSTOM']; ?></option>
            </select>

            <div class="row">
                <div class="col-md-6">
                    <br><br>
                    <input type="hidden" class="hidden" name="overwriteBackup" id="overwriteBackupHidden" value="false">
                    <input type="checkbox" data-on="<i class='fa fa-refresh'>" data-off="<i class='fa fa-archive'>" data-overwriteOff="<?php echo $lang['BACKUP_OVERWRITE_OFF']; ?>" data-overwriteOn="<?php echo $lang['BACKUP_OVERWRITE_ON']; ?>" data-toggle="toggle" data-onstyle="success" data-offstyle="success" class="checkbox" name="overwriteBackup" id="overwriteBackup" value="true" checked>
                    &nbsp;&nbsp;<label for="overwriteBackup" id="overwriteBackupLabel"><?php echo $lang['BACKUP_OVERWRITE_ON']; ?>&nbsp;&nbsp;</label>
                    <br><br>
                    <div id="archiveGroup" class="hidden">
                        <?php
                        // archive backup folders
                        $backup->archiveBackupSubFolders = \YAWK\filemanager::getSubfoldersToArray($backup->archiveBackupFolder);
                        // if any folder is there, draw a select field to choose from
                        if (count($backup->archiveBackupSubFolders) > 0)
                        {
                            echo "
                                  <label for=\"selectFolder\">$lang[BACKUP_FOLDER_SELECT]</label>
                                  <select class=\"form-control\" name=\"selectFolder\" id=\"selectFolder\">
                                  <option label=\"$lang[BACKUP_PLEASE_SELECT]\"></option>";
                            foreach ($backup->archiveBackupSubFolders as $subFolder)
                            {
                                echo "<option value=\"$subFolder\">$subFolder</option>";
                            }

                            echo"</select>
                                <div class=\"text-center\"><br><i>$lang[OR]</i><br><br></div>";
                        }
                        // otherwise draw text input field to create a folder
                        ?>
                        <label for="newFolder"><?php echo $lang['BACKUP_FOLDER_NAME']; ?></label>
                        <input type="text" id="newFolder" name="newFolder" class="form-control">
                        <br>
                    </div>

                </div>
                <div class="col-md-6">
                <br><br>
                    <?php
                        // add zip switch only if zip archive class is available
                        if (class_exists('ZipArchive'))
                        {
                            echo "<input type=\"hidden\" class=\"hidden\" name=\"zipBackup\" id=\"zipBackupHidden\" value=\"false\">
                            <input type=\"checkbox\" data-on=\"<i class='fa fa-file-zip-o'></i>\" data-off=\"$lang[NO]\" data-toggle=\"toggle\" data-onstyle=\"success\" data-offstyle=\"danger\" class=\"checkbox\" name=\"zipBackup\" id=\"zipBackup\" value=\"true\" checked>
                            &nbsp;&nbsp;<label for=\"zipBackup\">$lang[BACKUP_ZIP_ALLOWED]&nbsp;&nbsp;</label>";
                        }
                    ?>

                </div>
            </div>
        </div>
    </div>
        <div class="box hidden" id="customSettings">
            <div class="box-header">
                <h3 class="box-title">
                    <?php echo $lang['EXTENDED_SETTINGS']; ?>
                </h3>
            </div>
            <div class="box-body">
                <div>
                    <h3>
                        <input type="checkbox" data-on="<i class='fa fa-file-o'></i>" data-off="<?php echo $lang['OFF']; ?>" data-toggle="toggle" data-onstyle="success" data-offstyle="danger" class="checkbox" name="contentCheckAll" id="contentCheckAll" value="true" checked>
                        <label for="contentCheckAll" id="contentCheckAllLabel"> <?php echo $lang['PAGES']; ?></label>
                    </h3>
                    <div class="checkbox-group-content">
                        <?php
                        // get content folder + subfolders into array
                        $contentFolderArray = \YAWK\filemanager::getSubfoldersToArray('../content/');
                        // walk through folders and draw checkboxes
                        foreach ($contentFolderArray as $folder)
                        {
                            echo "&nbsp;&nbsp;&nbsp;&nbsp;
                    <input type=\"checkbox\" data-name=\"$folder\" id=\"contentFolder-$folder\" name=\"contentFolder[]\" checked=\"checked\" value=\"$folder\">
                    <label id=\"contentFolderLabel-$folder\" for=\"contentFolder-$folder\">".ucfirst($folder)."</label><br>";
                        }
                        ?>
                    </div>

                    <h3>
                        <input type="checkbox" data-on="<i class='fa fa-folder-open-o'></i>" data-off="<?php echo $lang['OFF']; ?>" data-toggle="toggle" data-onstyle="success" data-offstyle="danger" class="checkbox" name="mediaCheckAll" id="mediaCheckAll" value="true" checked>
                        <label for="mediaCheckAll" id="mediaCheckAllLabel"> <?php echo $lang['BACKUP_MEDIA_FOLDER']; ?></label>
                    </h3>
                    <div class="checkbox-group-media">
                    <?php
                        // get media folder + subfolders into array
                        $mediaFolderArray = \YAWK\filemanager::getSubfoldersToArray('../media/');
                        // walk through folders and draw checkboxes
                        foreach ($mediaFolderArray as $folder)
                        {
                            echo "&nbsp;&nbsp;&nbsp;&nbsp;
                    <input type=\"checkbox\" data-name=\"$folder\" id=\"mediaFolder-$folder\" name=\"mediaFolder[]\" checked=\"checked\" value=\"$folder\">
                    <label id=\"mediaFolderLabel-$folder\" for=\"mediaFolder-$folder\">".ucfirst($folder)."</label><br>";
                        }
                    ?>
                    </div>
                    <h3>
                        <input type="checkbox" data-on="<i class='fa fa-gears'></i>" data-off="<?php echo $lang['OFF']; ?>" data-toggle="toggle" data-onstyle="success" data-offstyle="danger" class="checkbox" name="systemFolderCheckAll" id="systemFolderCheckAll" value="true" checked="checked">
                        <label for="systemFolderCheckAll" id="systemFolderCheckAllLabel"> <?php echo $lang['SYSTEM']; ?></label>
                    </h3>
                    <div class="checkbox-group-system">
                    <?php
                    // get database tables
                    $systemFolders = array('system/fonts', 'system/language', 'system/plugins', 'system/templates', 'system/widgets');
                    foreach ($systemFolders AS $folder)
                    {
                        echo "&nbsp;&nbsp;&nbsp;&nbsp;
                        <input type=\"checkbox\" id=\"systemFolder-$folder\" value=\"$folder\" name=\"systemFolder[]\" checked>
                        <label id=\"systemFolderLabel-$folder\" for=\"systemFolder-$folder\">$folder</label><br>";
                    }
                    ?>
                    </div>

                    <h3>
                        <input type="checkbox" data-on="<i class='fa fa-database'></i>" data-off="<?php echo $lang['OFF']; ?>" data-toggle="toggle" data-onstyle="success" data-offstyle="danger" class="checkbox" name="databaseCheckAll" id="databaseCheckAll" value="true" checked="checked">
                        <label for="databaseCheckAll" id="databaseCheckAllLabel"> <?php echo $lang['DATABASE']; ?></label>
                    </h3>
                    <div class="checkbox-group-database">
                    <?php
                        // get database tables
                        $dbTables = $db->get_tables();
                        foreach ($dbTables AS $id=>$table)
                        {
                            echo "&nbsp;&nbsp;&nbsp;&nbsp;
                        <input type=\"checkbox\" id=\"database-$table\" value=\"$table\" name=\"database[]\" checked>
                        <label id=\"databaseLabel-$table\" for=\"database-$table\">$table</label><br>";
                        }
                    ?>
                    </div>
                </div>
            </div>
        </div>
    </form>
</div>
<div class="col-md-6">

<!-- CURRENT BACKUP BOX -->
    <div class="box">
        <div class="box-header">
            <h3 class="box-title"><?php echo $lang['BACKUP_ONGOING']; ?> <small>system/backup/current/</small></h3>
        </div>
        <div class="box-body">
            <table class="table table-striped table-hover table-responsive">
            <?php
                // get all current files into array
                $currentFiles = $backup->getCurrentBackupFilesArray();
                foreach ($currentFiles as $file)
                {
                    // get current path (folder and file)
                    $currentFile = "$backup->currentBackupFolder$file";
                    // get date of current file
                    $currentFileDate = date("F d Y H:i", filemtime($currentFile));
                    // get month of current file
                    $month = date("F", filemtime($currentFile));
                    // get year of current file
                    $year = date("Y", filemtime($currentFile));
                    // calculate how long it is ago...
                    $ago = \YAWK\sys::time_ago($currentFileDate, $lang);
                    echo "
                <tr>
                    <td width=\"10%\" class=\"text-center\"><h4><i class=\"fa fa-file-zip-o\"></i><br><small>$month<br>$year</small></h4></td>
                    <td width=\"70%\"><h4><a href=\"$backup->currentBackupFolder$file\">$file</a><br><small><b>$currentFileDate</b><br><i>($ago)</i></small></h4></td>
                    <td width=\"20%\">
                      <h4><br>
                      
                        <a href=\"$backup->currentBackupFolder$file\" title=\"$lang[TO_DOWNLOAD]\"><i class=\"fa fa-download\"></i></a>&nbsp;&nbsp;&nbsp;&nbsp;
                        <a href=\"#\" title=\"$lang[BACKUP_RESTORE]\"><i class=\"fa fa-history\"></i></a>&nbsp;&nbsp;&nbsp;&nbsp;
                        <a href=\"#\" data-file=\"$file\" data-toggle=\"modal\" data-target=\"#myModal\" title=\"$lang[BACKUP_MOVE_TO_ARCHIVE]\"><i class=\"fa fa-archive\"></i></a>&nbsp;&nbsp;&nbsp;&nbsp;
                        <a class=\"fa fa-trash-o\" role=\"dialog\" data-confirm=\"$backup->currentBackupFolder$file ".$lang['DELETE']."? - $lang[BEWARE] $lang[UNDO_NOT_POSSIBLE]!\" title=\"$lang[ATTENTION] $lang[BACKUP] $lang[DELETE]\" href=\"index.php?page=settings-backup&deleteBackup=true&backupFolder=$backup->currentBackupFolder&backupFile=$file\">
                        </a>
                      </h4>
                    </td>
                </tr>";
                }
            ?>
            </table>
        </div>
    </div>

<!-- ARCHIVE BACKUP BOX -->
    <div class="box">
        <div class="box-header">
            <h3 class="box-title"><?php echo $lang['BACKUP_ARCHIVE']; ?> <small>system/backup/archive/</small></h3>
        </div>
        <div class="box-body">

            <table class="table table-responsive">
                <?php
                // get all archive backup files into array
                $backup->archiveBackupFiles = \YAWK\filemanager::ritit($backup->archiveBackupFolder);
                // walk through archive folder
                foreach ($backup->archiveBackupFiles as $folder => $files)
                {
                    // set current archive subfolder
                    $backup->archiveBackupSubFolder = "$backup->archiveBackupFolder$folder/";
                    // last change of archive subfolder (month)
                    $month = date("F", filemtime($backup->archiveBackupSubFolder));
                    // last change of archive subfolder (year)
                    $year = date("Y", filemtime($backup->archiveBackupSubFolder));

                    echo "
                <tr>
                    <td width=\"10%\" class=\"text-center\"><h4><i class=\"fa fa-archive\"></i><br><small>$month<br>$year</small></h4></td>
                    <td width=\"90%\">
                    
                        <table class=\"table table-striped table-hover table-responsive\">
                        <thead>
                            <h4><i class=\"fa fa-folder-open-o\"></i> $folder<small>
                            <a class=\"fa fa-trash-o fa-2x text-gray pull-right\" role=\"dialog\" data-confirm=\"$folder ".$lang['DELETE']."? - $lang[BEWARE] $lang[UNDO_NOT_POSSIBLE]!\" title=\"$lang[ATTENTION] $lang[BACKUP_ARCHIVE] $lang[DELETE]\" href=\"index.php?page=settings-backup&deleteArchiveSubFolder=true&archiveSubFolder=$backup->archiveBackupSubFolder\"></a>
                            </h4>
                        </thead>";

                    // walk through archive subfolder files
                    foreach($files as $file => $value)
                    {
                        // set current archive/subfolder/file path
                        $archiveFile = $backup->archiveBackupSubFolder.$value;
                        // get date of current archive file
                        $archiveFileDate = date("F d Y H:i", filemtime($archiveFile));
                        // calculate how long it is ago...
                        $ago = \YAWK\sys::time_ago($archiveFileDate, $lang);

                        echo "<tr>
                                <td width=\"80%\">
                                    &nbsp;&nbsp;&nbsp;&nbsp;<small><b><a href=\"$archiveFile\">$value</a> <small>$archiveFileDate</small></b></small>
                                </td>

                                <td width=\"20%\">
                                    <a href=\"$archiveFile\" title=\"$lang[TO_DOWNLOAD]\"><i class=\"fa fa-download\"></i></a>&nbsp;&nbsp;&nbsp;&nbsp;
                                    <a href=\"#\" title=\"$lang[BACKUP_RESTORE]\"><i class=\"fa fa-history\"></i></a>&nbsp;&nbsp;&nbsp;&nbsp;
                                    <a class=\"fa fa-trash-o\" role=\"dialog\" data-confirm=\"$archiveFile ".$lang['DELETE']."? - $lang[BEWARE] $lang[UNDO_NOT_POSSIBLE]!\" title=\"$lang[ATTENTION] $lang[BACKUP] $lang[DELETE]\" href=\"index.php?page=settings-backup&deleteBackup=true&backupFolder=$backup->archiveBackupSubFolder&backupFile=$value\">
                                </td>
                                </tr>";
                    }
                        echo"</table>        
                    </td>
                </tr>";
                } // end foreach files
                ?>
            </table>
        </div>
    </div>

    <!-- UPLOAD / RESTORE BACKUP -->
    <div class="box">
        <div class="box-header">
            <h3 class="box-title">
                <?php echo $lang['BACKUP_RESTORE']." <small>".$lang['TO_UPLOAD']."</small>"; ?>
            </h3>
        </div>
        <div class="box-body">
            <form enctype="multipart/form-data" class="dropzone text-center" action="index.php?page=settings-backup&action=upload" method="POST">
                <input type="hidden" name="MAX_FILE_SIZE" value="">
                <input type="hidden" name="upload" value="sent">
                <input type="hidden" name="action" value="upload">
                <br>
                <button class="btn btn-success" type="submit"><i class="fa fa-upload"></i>&nbsp;&nbsp;&nbsp;<?php echo $lang['UPLOAD']; ?></button>
            </form>
            <span class="pull-right">asdasd</span>
            <br><br>
        </div>
    </div>
</div>
</div>

<!-- Modal --MOVE TO ARCHIVE-- -->
<div class="modal fade" id="myModal" tabindex="-1" role="dialog" aria-labelledby="myModal2Label" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <form enctype="multipart/form-data" action="index.php?page=settings-backup&moveToArchive=true" method="POST">
                <div class="modal-header">
                    <!-- modal header with close controls -->
                    <button type="button" class="close" data-dismiss="modal" aria-hidden="true"><i class="fa fa-times"></i> </button>
                    <br>
                    <div class="col-md-1"><h3 class="modal-title"><i class="fa fa-archive"></i></h3></div>
                    <div class="col-md-11"><h3 class="modal-title"><?php echo $lang['BACKUP_MOVE_TO_ARCHIVE']; ?></h3></div>
                </div>

                <!-- modal body -->
                <div class="modal-body">
                    <!-- SELECT BACKUP ARCHIVE SUB FOLDER -->
                    <?php if (isset($_GET['file'])) { echo $_GET['file']; } ?>
                    <h4><b><?php echo $lang['BACKUP_ADD_ARCHIVE_SUBFOLDER']; ?></b></h4>
                    <?php
                            $backup->archiveBackupSubFolders = \YAWK\filemanager::getSubfoldersToArray($backup->archiveBackupFolder);
                            if (count($backup->archiveBackupSubFolders) > 0)
                            {
                                echo "
                                  <label for=\"selectFolder\">$lang[BACKUP_FOLDER_SELECT]</label>
                                  <select class=\"form-control\" name=\"selectFolder\" id=\"selectFolderModal\">
                                  <option label=\"$lang[BACKUP_PLEASE_SELECT]\" id=\"pleaseSelect\"></option>";
                                foreach ($backup->archiveBackupSubFolders as $subFolder)
                                {
                                    echo "<option value=\"$subFolder\">$subFolder</option>";
                                }

                                echo"</select>
                                <div class=\"text-center\"><br><i>$lang[OR]</i></div>";
                            }
                    ?>
                    <label for="newFolder"><?php echo $lang['BACKUP_FOLDER_NAME']; ?></label>
                    <input type="text" class="form-control" id="newFolderModal" name="newFolder" placeholder="<?php echo $lang['BACKUP_FOLDER_NAME_PH']; ?>">
                    <input type="hidden" name="file" id="file" value=""> <!-- gets filled via JS -->
                    <input type="hidden" name="action" id="action" value="moveToArchive"> <!-- gets filled via JS -->

                </div>

                <!-- modal footer /w submit btn -->
                <div class="modal-footer">
                    <input class="btn btn-large btn-default" data-dismiss="modal" aria-hidden="true" type="submit" value="<?php echo $lang['CANCEL']; ?>">
                    <button class="btn btn-large btn-success" type="submit"><i class="fa fa-check"></i>&nbsp; <?php echo $lang['BACKUP_ARCHIVE_THIS']; ?></button>
                    <br><br>
                </div>
            </form>
        </div> <!-- modal content -->
    </div> <!-- modal dialog -->
</div>

<!-- Bootstrap toggle css -->
<link rel="stylesheet" href="../system/engines/bootstrap-toggle/css/bootstrap-toggle.css">
<!-- Bootstrap toggle js -->
<script type="text/javascript" src="../system/engines/bootstrap-toggle/js/bootstrap-toggle.min.js"></script>
<!-- DROPZONE JS -->
<script src="../system/engines/jquery/dropzone/dropzone.js"></script>
<!-- DROPZONE CSS -->
<link href="../system/engines/jquery/dropzone/dropzone.css" rel="stylesheet">