<?php
include '../system/plugins/blog/classes/blog.php';

$blog = new \YAWK\PLUGINS\BLOG\blog();
$blog->loadItemProperties($db, $_GET['blogid'], $_GET['itemid']);
$blog->sort++;

if (isset($_POST['save'])) {
    $blog->blogtitle = $db->quote($_POST['blogtitle']);
    $blog->subtitle = $db->quote($_POST['subtitle']);
    $blog->published = $db->quote($_POST['published']);
    $blog->itemid = $db->quote($_POST['itemid']);
    $blog->date_publish = $db->quote($_POST['date_publish']);
    $blog->date_unpublish = $db->quote($_POST['date_unpublish']);
    $blog->blogid = $db->quote($_POST['blogid']);
    $blog->teasertext = $db->quote($_POST['teasertext']);
    $blog->blogtext = $db->quote($_POST['blogtext']);
    $blog->pageid = $db->quote($_POST['pageid']);
    $blog->thumbnail = $db->quote($_POST['thumbnail']);
    $blog->youtubeUrl = $db->quote($_POST['youtubeUrl']);

    // tinyMCE \r\n bugfix
    $blog->text = stripslashes(str_replace('\r\n', '', ($blog->blogtext)));
    if ($blog->save($db)) {
        YAWK\alert::draw("success", "Hooray!", "Der Eintrag wurde erfolgreich gespeichert!", "", "800");
    }
    else
    {   // throw error
        YAWK\alert::draw("danger", "Fehler", "Das Blog ID " . $_GET['blogid'] . " " . $_POST['title'] . " - " . $_POST['subtitle'] . " konnte nicht gespeichert werden.","","3800");
    }
}
// path to cms
$dirprefix = YAWK\sys::getDirPrefix($db);
// get host URL
$host = YAWK\sys::getHost($db);

// TEMPLATE WRAPPER - HEADER & breadcrumbs
echo "
    <!-- Content Wrapper. Contains page content -->
    <div class=\"content-wrapper\" id=\"content-FX\">
    <!-- Content Header (Page header) -->
    <section class=\"content-header\">";
/* draw Title on top */
\YAWK\PLUGINS\BLOG\blog::getBlogTitle($blog->blogtitle, $lang['EDIT'], $blog->icon);
echo"<ol class=\"breadcrumb\">
            <li><a href=\"index.php\" title=\"Dashboard\"><i class=\"fa fa-dashboard\"></i> Dashboard</a></li>
            <li><a href=\"index.php?page=plugins\" title=\"Plugins\"> Plugins</a></li>
            <li class=\"active\"><a href=\"index.php?plugin=blog\" title=\"Blog\"> Blog</a></li>
         </ol>
    </section>
    <!-- Main content -->
    <section class=\"content\">";
/* page content start here */
?>
<link href="../system/engines/summernote/dist/summernote.css" rel="stylesheet">
<script src="../system/engines/summernote/dist/summernote.min.js"></script>
<script type="text/javascript">
    $(document).ready(function () {
        $('#summernote').summernote({
            height: 120,                 // set editor height
            minHeight: null,             // set minimum height of editor
            maxHeight: null,             // set maximum height of editor
            focus: true,                 // set focus to editable area after initializing summernote
            codemirror: { // codemirror options
                theme: 'monokai'
            }

        });
        $('#summernote2').summernote({
            height: 360,                 // set editor height
            minHeight: null,             // set minimum height of editor
            maxHeight: null,             // set maximum height of editor
            focus: false,                 // set focus to editable area after initializing summernote
            codemirror: { // codemirror options
                theme: 'monokai'
            }

        });
    });
</script>

<!-- bootstrap date-timepicker -->
<link type="text/css" href="../system/engines/datetimepicker/css/datetimepicker.min.css" rel="stylesheet"/>
<script type="text/javascript" src="../system/engines/datetimepicker/js/bootstrap-datetimepicker.min.js"></script>
<script type="text/javascript">
    /* start own JS bootstrap function
     ...on document ready... letsego! */
    $(document).ready(function () {
        $('#datetimepicker1').datetimepicker({
            format: 'yyyy-mm-dd hh:ii'
        });
        $('#datetimepicker2').datetimepicker({
            format: 'yyyy-mm-dd hh:ii'
        });
    });//]]>  /* END document.ready */
    /* ...end admin jQ controlls  */
</script>
<!-- end datetimepicker -->

<?php
$blog->icon = $blog->getBlogProperty($db, $blog->blogid, "icon");
$blog->name = $blog->getBlogProperty($db, $blog->blogid, "name");
/* draw Title on top */

?>

<form name="form" role="form" class="form-inline"
      action="index.php?plugin=blog&pluginpage=blog-edit&blogid=<?php print $blog->blogid; ?>&itemid=<?php print $blog->itemid; ?>"
      method="post">

<div class="row">
    <div class="col-md-10">
    <!-- EDITOR -->
    <?php if ($blog->layout !== "3")
    {
        echo "
        <label for=\"summernote\">Teaser Text</label>
        <textarea
            id=\"summernote\"
            class=\"form-control\"
            style=\"margin-top:10px;\"
            name=\"teasertext\"
            cols=\"50\"
            rows=\"18\">
            $blog->teasertext
        </textarea>
    <br>";
    }
        ?>
    <!-- EDITOR -->
    <label for="summernote2">Blog Text</label>
    <textarea
        id="summernote2"
        class="form-control"
        style="margin-top:10px;"
        name="blogtext"
        cols="50"
        rows="18">
        <?php print $blog->blogtext; ?>
    </textarea>
    </div> <!-- end left col -->

    <div class="col-md-2">
    <!-- right col -->
        <!-- SAVE BUTTON -->
        <input id="savebutton"
               name="save"
               class="btn btn-success"
               style="float:right; margin-top:6px; margin-bottom:30px;"
               type="submit"
               value="&Auml;nderungen&nbsp;speichern"/>

        <!-- CANCEL BUTTON -->
        <a href="index.php?plugin=blog&pluginpage=blog-entries&blogid=<?php echo $blog->blogid; ?>" id="cancel" style="float:right; margin-top:6px; margin-bottom:30px;">
            <i class="btn btn-default">zur&uuml;ck</i></a>
        <br><br><br>
        <dl>
        <!-- TITLE -->
            <h4><i class="fa fa-file-text-o"></i>&nbsp;&nbsp;Einstellungen <small>Titel und Dateiname</small></h4>
            <dt> <label for="blogtitle"><?php print $lang['TITLE']; ?></label></dt>
            <dd> <input type="text"
                       class="form-control"
                       id="blogtitle"
                       name="blogtitle"
                       size="32"
                       maxlength="255"
                       value="<?php print $blog->blogtitle; ?>">
            </dd>
            <!-- SUBTITLE -->
            <dt><label for="subtitle"><?php print $lang['SUBTITLE']; ?></label></dt>
            <dd><input type="text"
                       class="form-control"
                       name="subtitle"
                       id="subtitle"
                       size="64"
                       maxlength="255"
                       value="<?php print $blog->subtitle; ?>">
            </dd>
              <hr>

            <h4><i class="fa fa-clock-o"></i>&nbsp;&nbsp;Ver&ouml;ffentlichung <small>Zeitpunkt & Privatsph&auml;re</small></h4>
            <dt>
                <!-- PUBLISH DATE -->
                <label for="datetimepicker1"><?php print $lang['START_PUBLISH']; ?></label>
            </dt>
            <dd>
                <input
                    class="form-control"
                    id="datetimepicker1"
                    data-date-format="yyyy-mm-dd hh:mm:ss"
                    type="text"
                    name="date_publish"
                    maxlength="19"
                    value="<?php print $blog->date_publish; ?>">
            </dd>
            <dt>
                <!-- UNPUBLISH DATE -->
                <label for="datetimepicker2"><?php print $lang['END_PUBLISH']; ?></label>
            </dt>
            <dd>
                <input
                    type="text"
                    class="form-control"
                    id="datetimepicker2"
                    name="date_unpublish"
                    data-date-format="yyyy-MM-dd hh:mm:ss"
                    maxlength="19"
                    value="<?php print $blog->date_unpublish; ?>">
            </dd>
            <dt>
            <!-- PAGE ON / OFF STATUS -->
                <label for="published"><?php print $lang['ENTRY'];print"&nbsp;";print $lang['ONLINE']; ?></label>
            </dt>
            <dd>
                <?php if($blog->published == 1){
                    $publishedHtml = "<option value=\"1\" selected=\"selected\">online</option>";
                    $publishedHtml .= "<option value=\"0\" >offline</option>";
                } else {
                    $publishedHtml = "<option value=\"0\" selected=\"selected\">offline</option>";
                    $publishedHtml .= "<option value=\"1\" >online</option>";
                } ?>
                <select id="published" name="published" class="form-control">
                    <?php echo $publishedHtml; ?>
                </select>
            </dd>
            <!-- blog thumbnail -->
            <hr>
            <h4><i class="fa fa-photo"></i>&nbsp;&nbsp;Vorschau <small>Foto im Teaser</small></h4>
            <dt><!-- THUMBNAIL IMAGE -->
                <label for="thumbnail"><?php print $lang['THUMBNAIL']; ?>:&nbsp;</label>
            </dt>
            <dd>
                <input
                    type="text"
                    class="form-control"
                    id="thumbnail"
                    name="thumbnail"
                    size="64"
                    maxlength="255"
                    placeholder="media/images/anyfile.jpg"
                    value="<?php print $blog->thumbnail; ?>">
            </dd>

            <dt>
                <label for="thumbnail">
                <i class="fa fa-youtube"></i> &nbsp;<?php print $lang['YOUTUBEURL']; ?>:&nbsp;</label>
            </dt>
            <dd> <!-- YouTube Link -->
                <input
                    type="text"
                    class="form-control"
                    id="youtubeUrl"
                    name="youtubeUrl"
                    size="64"
                    maxlength="255"
                    placeholder="https://www.youtube.com/embed/1A2B3C4D5E6F"
                    value="<?php print $blog->youtubeUrl; ?>">
            </dd>
        </dl>

    <!-- HIDDEN FIELDS -->
    <input type="hidden" name="blogid" value="<?php print $blog->blogid; ?>"/>
    <input type="hidden" name="itemid" value="<?php print $blog->itemid; ?>"/>
    <input type="hidden" name="pageid" value="<?php print $blog->pageid; ?>"/>

    </div>
</div>
</form>