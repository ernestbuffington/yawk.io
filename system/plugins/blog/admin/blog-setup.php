<!-- iconpicker css + JS -->
<link href="../system/engines/iconpicker/css/fontawesome-iconpicker.min.css" rel="stylesheet">
<script src="../system/engines/iconpicker/js/fontawesome-iconpicker.min.js"></script>
<script type="text/javascript">
    $(document).ready(function () {
        $('#icon').iconpicker();
    });
</script>
<?php
include '../system/plugins/blog/classes/blog.php';
// check if blog object is set
if (!isset($blog)) { $blog = new \YAWK\PLUGINS\BLOG\blog(); }
// check if language is set
if (!isset($language) || (!isset($lang)))
{   // inject (add) language tags to core $lang array
    $lang = \YAWK\language::inject(@$lang, "../system/plugins/blog/language/");
}
YAWK\backend::getTitle($lang['BLOG'], $lang['BLOG_SETUP']);

if (isset($_GET['blogid'])) { // if blog is set,
    $blog->blogid = $_GET['blogid']; // load id to object
}

// and get blog settings
$blog->name = $blog->getBlogProperty($db, $blog->blogid, "name");
$blog->description = $blog->getBlogProperty($db, $blog->blogid, "description");
$blog->icon = $blog->getBlogProperty($db, $blog->blogid, "icon");
// if form is sent, prepare data
if (isset($_POST['setup']))
{
    $blog->blogid = $_POST['blogid'];
    $blog->name = $db->quote($_POST['name']);
    $blog->description = $db->quote($_POST['description']);
    $blog->icon = $db->quote($_POST['icon']);
    $blog->gid = $db->quote($_POST['gid']);

    // set frontend settings
    $blog->showTitle = $_POST['showTitle'];
    $blog->showDesc = $_POST['showDesc'];
    $blog->showDate = $_POST['showDate'];
    $blog->showAuthor = $_POST['showAuthor'];
    $blog->permaLink = $_POST['permaLink'];
    $blog->preview = $_POST['preview'];
    $blog->voting = $_POST['voting'];
    $blog->spacer = $_POST['spacer'];
    $blog->frontendIcon = $_POST['frontendIcon'];

    // set layout setting
    if (!isset($_POST['layout'])) {
        $blog->layout = 0;
    } else {
        $blog->layout = $_POST['layout'];
    }

    // sequence tells how it needs2be sorted. (by name or by date)
    if (!isset($_POST['sequence'])) {
        $blog->sequence = "0";
    } else {
        $blog->sequence = $_POST['sequence'];
    }
    if (!isset($_POST['sortation'])) {
        $blog->sortation = "0";
    } else {
        $blog->sortation = $_POST['sortation'];
    }

    // comments in frontend?
    if (!isset($_POST['comments'])) {
        $blog->comments = "0";
    } else {
        $blog->comments = $_POST['comments'];
    }

    // finally: save blog settings
    if ($blog->setup($db, $blog))
    {   // success
        echo YAWK\alert::draw("success", "$lang[SUCCESS]", "$lang[SETTINGS_SAVED]","", 1200);
    }
    else
    {   // setup failed, throw error
        echo YAWK\alert::draw("danger", "$lang[ERROR] ", "$lang[SETTINGS] $lang[BLOG] $lang[ID] " . $_POST['blogid'] . " " . $blog->name . " - " . $blog->description . " $lang[NOT_SAVED]","plugin=blog","3800");
    }

}
else
    {
        // get blog settings
        $blog->showTitle = $blog->getBlogProperty($db, $blog->blogid, "showtitle");
        $blog->showDesc = $blog->getBlogProperty($db, $blog->blogid, "showdesc");
        $blog->showDate = $blog->getBlogProperty($db, $blog->blogid, "showdate");
        $blog->showAuthor = $blog->getBlogProperty($db, $blog->blogid, "showauthor");
        $blog->sequence = $blog->getBlogProperty($db, $blog->blogid, "sequence");
        $blog->sortation = $blog->getBlogProperty($db, $blog->blogid, "sortation");
        $blog->comments = $blog->getBlogProperty($db, $blog->blogid, "comments");
        $blog->permaLink = $blog->getBlogProperty($db, $blog->blogid, "permaLink");
        $blog->layout = $blog->getBlogProperty($db, $blog->blogid, "layout");
        $blog->gid = $blog->getBlogProperty($db, $blog->blogid, "gid");
        $blog->preview = $blog->getBlogProperty($db, $blog->blogid, "preview");
        $blog->voting = $blog->getBlogProperty($db, $blog->blogid, "voting");
        $blog->spacer = $blog->getBlogProperty($db, $blog->blogid, "spacer");
        $blog->frontendIcon = $blog->getBlogProperty($db, $blog->blogid, "frontendIcon");
    }


// prepare HTML HELPER VARS, check if checkboxes needs to be checked (:
// frontend settings checkboxes
if ($blog->showTitle === '1') {
    $titleChecked = "checked";
    $titleCheckedValue = 1;
} else {
    $titleChecked = "";
    $blog->showTitle = 0;
    $titleCheckedValue = 0;
}
if ($blog->showDesc === '1') {
    $descCheckbox = "checked";
} else {
    $blog->showDesc = 0;
    $descCheckbox = "";
}
if ($blog->showDate === '1') {
    $dateChecked = "checked";
} else {
    $blog->showDate= 0;
    $dateChecked = "";
}
if ($blog->showAuthor === '1') {
    $authorChecked = "checked";
} else {
    $blog->showAuthor = 0;
    $authorChecked = "";
}
if ($blog->permaLink === '1') {
    $permaLinkChecked = "checked";
} else {
    $blog->permaLink = 0;
    $permaLinkChecked = "";
}
if ($blog->preview === '1') {
    $previewChecked = "checked";
} else {
    $blog->preview = 0;
    $previewChecked = "";
}
if ($blog->spacer === '1') {
    $spacerChecked = "checked";
} else {
    $blog->spacer = 0;
    $spacerChecked = "";
}
if ($blog->frontendIcon === '1') {
    $frontendIconChecked = "checked";
} else {
    $blog->frontendIcon = 0;
    $frontendIconChecked = "";
}

// layout radio buttons
if ($blog->layout === '0') {
    $layout0Checked = "checked";
} else {
    $layout0Checked = "";
}
if ($blog->layout === '1') {
    $layout1Checked = "checked";
} else {
    $layout1Checked = "";
}
if ($blog->layout === '2') {
    $layout2Checked = "checked";
} else {
    $layout2Checked = "";
}
if ($blog->layout === '3') {
    $layout3Checked = "checked";
} else {
    $layout3Checked = "";
}
if ($blog->layout === '4') {
    $layout4Checked = "checked";
} else {
    $layout4Checked = "";
}

// sortation radio buttons
if ($blog->sequence === '0') {
    $sequenceDateChecked = "checked";
} else {
    $sequenceDateChecked = "";
}
if ($blog->sequence === '1') {
    $sequenceTitleChecked = "checked";
} else {
    $sequenceTitleChecked = "";
}
if ($blog->sortation === '0') {
    $ascChecked = "checked";
} else {
    $ascChecked = "";
}
if ($blog->sortation === '1') {
    $descChecked = "checked";
} else {
    $descChecked = "";
}

// comments radio buttons
if ($blog->comments === '0') {
    $commentsOffChecked = "checked";
} else {
    $commentsOffChecked = "";
}
if ($blog->comments === '1') {
    $commentsOnChecked = "checked";
} else {
    $commentsOnChecked = "";
}

// voting radio buttons
if ($blog->voting === '0') {
    $votingOffChecked = "checked";
} else {
    $votingOffChecked = "";
}
if ($blog->voting === '1') {
    $votingOnChecked = "checked";
} else {
    $votingOnChecked = "";
}
// GO ON WITH THE PLAIN HTML FORM...
?>

<?php
// TEMPLATE WRAPPER - HEADER & breadcrumbs
echo "
<!-- Content Wrapper. Contains page content -->
<div class=\"content-wrapper\" id=\"content-FX\">
    <!-- Content Header (Page header) -->
    <section class=\"content-header\">";
        /* draw Title on top */
        echo \YAWK\backend::getTitle($lang['BLOG'], $lang['BLOG_SETUP']);
        echo"<ol class=\"breadcrumb\">
            <li><a href=\"index.php\" title=\"$lang[DASHBOARD]\"><i class=\"fa fa-dashboard\"></i> $lang[DASHBOARD]</a></li>
            <li><a href=\"index.php?page=plugins\" title=\"$lang[PLUGINS]\"> $lang[PLUGINS]</a></li>
            <li><a href=\"index.php?plugin=blog\" title=\"$lang[BLOG]\"> $lang[BLOG]</a></li>
            <li class=\"active\"><a href=\"index.php?plugin=blog&pluginpage=blog-setup\" title=\"$lang[SETUP]\"> $lang[SETUP]</a></li>
        </ol>
    </section>
    <!-- Main content -->
    <section class=\"content\">";
        /* page content start here */
?>
<div class="box box-default">
    <div class="box-body">
        <form action="index.php?plugin=blog&pluginpage=blog-setup&blogid=<?php echo $blog->blogid; ?>" class="form-inline" role="form" method="POST">
        <div class="row">
            <div class="col-md-6">
                <h4><?php echo $lang['BLOG']."&nbsp;".$lang['SETTINGS'].":&nbsp;<b>".$blog->name."</b>"; ?></h4>
            </div>
            <div class="col-md-6">
                <!-- SAVE BUTTON -->
                <button class="btn btn-success pull-right" type="submit" name="create" id="savebutton"><i class="fa fa-save"></i> &nbsp;<?php echo $lang['SAVE_SETTINGS']; ?></button>
                &nbsp;

                <!-- BACK BUTTON -->
                <a class="btn btn-default pull-right" href="index.php?plugin=blog"><i class="glyphicon glyphicon-backward"></i> &nbsp;<?php echo $lang['BACK']; ?></a><br>
                <input name="setup"
                       value="blog-create"
                       type="hidden">
                <input name="blogid"
                       value="<?php echo $_GET['blogid']; ?>"
                       type="hidden">
            </div>
        </div>
    </div>
</div>

<div class="box box-default">
    <div class="box-body">
        <ul class="nav nav-tabs">
            <li class="active"><a data-toggle="tab" href="#blog"><i class="fa fa-wordpress"></i> &nbsp;<?php echo $lang['BLOG']; ?></a></li>
            <li><a data-toggle="tab" href="#layout"><i class="fa fa-object-group"></i> &nbsp;<?php echo $lang['LAYOUT']; ?></a></li>
            <li><a data-toggle="tab" href="#sortation"><i class="fa fa-sort"></i> &nbsp;<?php echo $lang['SORTATION']; ?></a></li>
            <li><a data-toggle="tab" href="#comments"><i class="fa fa-comment-o"></i> &nbsp;<?php echo $lang['COMMENTS']; ?></a></li>
        </ul>

        <div class="tab-content">
            <div id="blog" class="tab-pane fade in active">
                <div class="row">
                <div class="col-md-6">
                    <h3><i class="fa fa-wordpress"></i> <?php echo $lang['BLOG']; ?></h3>
                    <label for="name"><?php echo $lang['BLOG']; ?></label><br>
                    <input type="text"
                           class="form-control"
                           size="64"
                           placeholder="<?php echo $lang['BLOG_NAME']; ?>"
                           id="name"
                           name="name"
                           value="<?php echo $blog->name; ?>"><br><br>

                    <label for="description"><?php echo $lang['DESCRIPTION']; ?></label><br>
                    <textarea class="form-control"
                              cols="64"
                              rows="3"
                              style="margin-top: 6px;"
                              placeholder="<?php echo $lang['BLOG_DESCRIPTION']; ?>"
                              id="description"
                              name="description"><?php echo $blog->description; ?></textarea><br><br>

                    <label for="icon"><?php echo $lang['ICON'] ?></label><br>
                    <input type="text"
                           class="form-control icp icp-auto iconpicker-element iconpicker-input"
                           size="50"
                           style="margin-top: 6px;"
                           placeholder="<?php echo $lang['BLOG_ICON']; ?>"
                           id="icon"
                           name="icon"
                           value="<?php echo $blog->icon; ?>"><br><br>
                </div>
                <div class="col-md-6">
                    <!-- FRONTEND SETTINGS -->
                    <h3><i class="fa fa-television"></i> <?php echo $lang['FRONTEND']."&nbsp;".$lang['SETTINGS']; ?></h3>
                    <input type="hidden" name="frontendIcon" value="0">
                    <label for="frontendIcon">
                        <input type="checkbox"
                               class="form-inline"
                               id="frontendIcon"
                               name="frontendIcon"
                               value="1" <?php echo $frontendIconChecked; ?>> <?php echo $lang['SHOW_ICON_IN_FRONTEND']; ?>
                    </label><br>

                    <input type="hidden" name="showTitle" value="0">
                    <label for="showTitle">
                        <input type="checkbox"
                               class="form-inline"
                               id="showTitle"
                               name="showTitle"
                               value="1" <?php echo $titleChecked; ?>> <?php echo $lang['SHOW_TITLE_IN_FRONTEND']; ?>
                    </label><br>

                    <input type="hidden" name="showDesc" value="0">
                        <label for="showDesc">
                            <input type="checkbox"
                                   class="form-inline"
                                   id="showDesc"
                                   name="showDesc"
                                   value="1"
                                <?php echo $descCheckbox; ?>> <?php echo $lang['SHOW_DESC_IN_FRONTEND']; ?>
                        </label><br>

                    <input type="hidden" name="showDate" value="0">
                        <label for="showDate">
                            <input type="checkbox"
                                   class="form-inline"
                                   id="showDate"
                                   name="showDate"
                                   value="1"
                                <?php echo $dateChecked; ?>> <?php echo $lang['SHOW_DATE_IN_FRONTEND']; ?>
                        </label><br>

                    <input type="hidden" name="showAuthor" value="0">
                        <label for="showAuthor">
                            <input type="checkbox"
                                   class="form-inline"
                                   id="showAuthor"
                                   name="showAuthor"
                                   value="1"
                            <?php echo $authorChecked; ?>> <?php echo $lang['SHOW_AUTHOR_IN_FRONTEND']; ?>
                        </label><br>

                    <input type="hidden" name="permaLink" value="0">
                        <label for="permaLink">
                            <input type="checkbox"
                                   class="form-inline"
                                   id="permaLink"
                                   name="permaLink"
                                   value="1"
                            <?php echo $permaLinkChecked; ?>> <?php echo $lang['SHOW_PERMALINK_IN_FRONTEND']; ?>
                        </label><br>

                    <input type="hidden" name="spacer" value="0">
                    <label for="spacer">
                        <input type="checkbox"
                               class="form-inline"
                               id="spacer"
                               name="spacer"
                               value="1"
                            <?php echo $spacerChecked; ?>> <?php echo $lang['SHOW_HR_SEPERATOR']; ?>
                    </label><br>

                    <input type="hidden" name="preview" value="0">
                        <label for="preview">
                            <input type="checkbox"
                                   class="form-inline"
                                   id="preview"
                                   name="preview"
                                   value="1"
                            <?php echo $previewChecked; ?>> <?php echo $lang['HIDE_SHOW_MORE_BTN']; ?>
                        </label><br>

                    </div>
                </div>
            </div>


            <div id="layout" class="tab-pane fade in">
                <div class="row">
                <div class="col-md-6">
                    <!-- BLOG LAYOUT -->
                    <h3><i class="fa fa-object-group"></i> <?php echo $lang['LAYOUT']; ?></h3>
                    <div class="radio">
                        <label for="layout0">
                            <input type="radio" name="layout" id="layout0" value="0" <?php echo $layout0Checked; ?>>
                            <img src="http://placehold.it/120x80">&nbsp;&nbsp; <?php echo $lang['BLOG_LAYOUT_1COL_TEXTBLOG']; ?>
                        </label><br><br>
                        <label for="layout1">
                            <input type="radio" name="layout" id="layout1" value="1" <?php echo $layout1Checked; ?>>
                            <img src="http://placehold.it/120x80">&nbsp;&nbsp; <?php echo $lang['BLOG_LAYOUT_2COL_TEASER_L']; ?>
                        </label><br><br>
                        <label for="layout2">
                            <input type="radio" name="layout" id="layout2" value="2" <?php echo $layout2Checked; ?>>
                            <img src="http://placehold.it/120x80">&nbsp;&nbsp; <?php echo $lang['BLOG_LAYOUT_2COL_TEASER_R']; ?>
                        </label><br><br>
                        <label for="layout3">
                            <input type="radio" name="layout" id="layout3" value="3" <?php echo $layout3Checked; ?>>
                            <img src="http://placehold.it/120x80">&nbsp;&nbsp; <?php echo $lang['BLOG_LAYOUT_3COL_NEWSPAPER']; ?>
                        </label><br><br>
                        <label for="layout4">
                            <input type="radio" name="layout" id="layout4" value="4" <?php echo $layout4Checked; ?>>
                            <img src="http://placehold.it/120x80">&nbsp;&nbsp; <?php echo $lang['BLOG_LAYOUT_1COL_YOUTUBE']; ?>
                        </label><br><br>
                    </div>
                </div>
                <div class="col-md-6">
                    layout xtras
                </div>
                </div>
            </div>


            <div id="sortation" class="tab-pane fade in">
                <div class="row">
                <div class="col-md-6">
                    <!-- BLOG ENTRY SORTATION -->
                    <h3><i class="fa fa-sort-alpha-asc"></i> <?php echo $lang['SORTATION']; ?></h3>

                    <div class="col-md-6">
                        <div class="radio">
                            <label for="sequenceDate">
                                <input type="radio" name="sequence" id="sequenceDate" value="0" <?php echo $sequenceDateChecked; ?>>
                                <?php echo $lang['SORTATION_CHRONOLOGIC']; ?>
                            </label><br><br>
                            <label for="sequenceTitle">
                                <input type="radio" name="sequence" id="sequenceTitle" value="1" <?php echo $sequenceTitleChecked; ?>>
                                <?php echo $lang['SORTATION_ALPHABETICAL']; ?>
                            </label>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="radio">
                            <label for="asc">
                                <input type="radio" name="sortation" id="asc" value="0" <?php echo $ascChecked; ?>>
                                <?php echo $lang['ASC']; ?>
                            </label><br><br>
                            <label for="desc">
                                <input type="radio" name="sortation" id="desc" value="1" <?php echo $descChecked; ?>>
                                <?php echo $lang['DESC']; ?>
                            </label>
                        </div>
                    </div>
                </div>

                <div class="col-md-6">
                    <!-- ACCESS CONTROL / PRIVACY -->
                    <h3><i class="fa fa-eye"></i> <?php echo $lang['PRIVACY']; ?></h3>
                    <select name="gid" class="form-control">
                        <option value="<?php print \YAWK\sys::getRoleId($db, $blog->gid, "blog"); ?>"><?php print \YAWK\sys::getRole($db, $blog->gid, "blog"); ?></option>

                        <?php
                        /*
                        foreach(YAWK\sys::getRoles("blog") as $gid) {
                            print "<option value=\"".$gid['id']."\"";
                            if (isset($blog->gid)) {
                                if($blog->gid === $gid['id']) {
                                    print " selected=\"selected\"";
                                }
                                else if($blog->gid === $gid['id'] && !$_POST['gid']) {
                                    print " selected=\"selected\"";
                                }
                            }
                            print ">".$gid['value']."</option>";
                        }
                        */
                        ?>
                    </select>
                </div>
                <div class="col-md-6">
                    ...
                </div>
            </div>
            </div>


            <div id="comments" class="tab-pane fade">
                <!-- COMMENTS -->
                <div class="row">
                    <div class="col-md-6">
                    <h3><i class="fa fa-comment-o"></i> <?php echo $lang['COMMENTS']; ?></h3>

                    <div class="radio">
                        <label for="comment_off">
                            <input type="radio" name="comments" id="comment_off" value="0" <?php echo $commentsOffChecked; ?>>
                            <?php echo $lang['COMMENTS']."&nbsp;".$lang['ALLOWED']; ?>
                        </label><br><br>
                        <label for="comment_on">
                            <input type="radio" name="comments" id="comment_on" value="1" <?php echo $commentsOnChecked; ?>>
                            <?php echo $lang['COMMENTS']."&nbsp;".$lang['FORBIDDEN']; ?>
                        </label>
                    </div>

                    <!-- VOTING -->
                    <h3><i class="fa fa-thumbs-o-up"></i> <?php echo $lang['VOTING']; ?></h3>

                    <div class="radio">
                        <label for="voting_off">
                            <input type="radio" name="voting" id="voting_off" value="0" <?php echo $votingOffChecked; ?>>
                            <?php echo $lang['VOTING']."&nbsp;".$lang['ALLOWED']; ?>
                        </label><br><br>
                        <label for="voting_on">
                            <input type="radio" name="voting" id="voting_on" value="1" <?php echo $votingOnChecked; ?>>
                            <?php echo $lang['VOTING']."&nbsp;".$lang['FORBIDDEN']; ?>
                        </label>
                    </div>

                    </div>

                    <div class="col-md-6">
                        ...
                    </div>
                </div>
            </div>


        </form>
    </div>
</div>
