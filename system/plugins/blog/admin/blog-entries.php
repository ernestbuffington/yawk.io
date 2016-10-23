<?php
include '../system/plugins/blog/classes/blog.php';
if (!isset($blog)) { $blog = new \YAWK\PLUGINS\BLOG\blog(); }
// SET BLOG ITEM PROPERTIES
if (isset($_GET['blogid']))
    {   // pickup + store the blog ID from GET variable
        $blog->id = $_GET['blogid'];
    }
    else
        {   // default value:
            $blog->id = 1;
        }
// get BLOG properties
$blog->name = $blog->getBlogProperty($db, $blog->id, "name");
$blog->icon = $blog->getBlogProperty($db, $blog->id, "icon");
$blog->comments = $blog->getBlogProperty($db, $blog->id, "comments");

// TOGGLE ITEM
if (isset($_GET['toggle']))
{
    $published = $db->quote($_GET['published']);
    $blogid = $db->quote($_GET['blogid']);

// check status and toggle it
    if ($published === '1') {
        $published = 0;
        $status = "offline";
        $color = "danger";
    } else {
        $published = 1;
        $status = "online";
        $color = "success";
    }

    if ($blog->toggleItemOffline($db, $blogid, $published))
    {   //
        print \YAWK\alert::draw($color, "Page is now $status", "Toggle page status successful.", "", 800);
    }
    else
    {
        print \YAWK\alert::draw("danger", "Error", "Could not toggle status of this page.", "", 6800);
    }

}

// COPY ENTRY
if (isset($_GET['copy']))
{
    if (isset($_GET['blogid']) && (isset($_GET['itemid'])))
    {
        // new blog object
        $page = new \YAWK\page();
        $blog = new \YAWK\PLUGINS\BLOG\blog();
        $blog->id = $db->quote($_GET['blogid']);
        $blog->itemid = $db->quote($_GET['itemid']);
        $blog->itemgid = $db->quote($_GET['itemgid']);
        $blog->loadItemProperties($db, $blog->id, $blog->itemid);
        $blog->sort++;
        $blog->alias = $page->getProperty($db, $blog->pageid, "alias");
        // call copy method

        if($_GET['copy'] === "true")
        {
            if($blog->copyItem($db))
            {   // success
                \YAWK\alert::draw("success", "Erfolg!", "Der Blogeintrag ".$_GET['itemid']." wurde kopiert!","","2000");
            }
            else
            {   // copy failed, throw error
                \YAWK\alert::draw("danger", "Fehler!", "Der Blogeintrag konnte nicht kopiert werden.", "plugin=blog&pluginpage=blog-entries&blogid=$blog->id","3800");
            }
        }
    }
}

// DELETE ENTRY
if (isset($_GET['delete']) || ($_GET === "1"))
{
    // if an itemID is set, just delete THIS itemID
    if (isset($_GET['itemid'])) { $_GET['itemid'] = $db->quote($_GET['itemid']); }
    if (isset($_GET['blogid'])) { $_GET['blogid'] = $db->quote($_GET['blogid']); }
    if (isset($_GET['pageid'])) { $_GET['pageid'] = $db->quote($_GET['pageid']); }
    if (isset($_GET['title'])) { $_GET['title'] = $db->quote($_GET['title']); }
    if (isset($_GET['itemid']))
    {   // delete blog item
        if (!$blog->deleteItem($db, $_GET['blogid'], $_GET['itemid'], $_GET['pageid']))
        {   // delete item failed, throw error
            \YAWK\alert::draw("danger", "Could not delete blog page: #$_GET[itemid] / $_GET[title]", "Is the entry even still there? Please try again.", "", 5800);
        }
    }
}
// set blog object properties
if (isset($_GET['deletecomment']))
{
    // DELETE COMMENT
    if ($_GET['deletecomment'] === "true") {
        // delete comment
        if (isset($_GET['commentid']) && (isset($_GET['itemid']) && (isset($_GET['blogid'])))) {
            if ($blog->deleteComment($db, $_GET['blogid'], $_GET['itemid'], $_GET['commentid'])) {
                \YAWK\alert::draw("success", "Success! ", "Comment ID " . $_GET['id'] . " deleted.", "","1200");
            }
            else
            {
                \YAWK\alert::draw("danger", "Error: ", "Could not delete comment ID: " . $_GET['id'] . " ", "","3800");
            }
        }
    }
}
/* draw Title on top */

?>
<script type="text/javascript">
    $(document).ready(function () {
        $('#table-sort').dataTable({
            "bPaginate": false,
            "bLengthChange": false,
            "bFilter": true,
            "bSort": true,
            "bInfo": true,
            "bAutoWidth": false
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
        \YAWK\PLUGINS\BLOG\blog::getBlogTitle($blog->name, $lang['MANAGE'], $blog->icon);
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
<div class="box box-default">
    <div class="box-body">

<a class="btn btn-success" href="index.php?plugin=blog&pluginpage=blog-newitem&blogid=<?php echo $blog->id; ?>" style="float:right;">
    <i class="glyphicon glyphicon-plus"></i> &nbsp;<?php print $lang['BLOG+']; ?></a>

<a class="btn btn-default" href="index.php?plugin=blog" style="float:right;">
    <i class="glyphicon glyphicon-backward"></i> &nbsp;<?php print $lang['BACK']; ?></a>

<table width="100%" cellpadding="4" cellspacing="0" border="0" class="table table-striped table-hover" id="table-sort">
    <thead>
    <tr>
        <td width="3%"><strong>&nbsp;</strong></td>
        <td width="5%" class="text-center"><strong><?php print $lang['ID']; ?></strong></td>
        <td width="25%"><strong><?php print $lang['TITLE']; ?></strong></td>
        <td width="30%"><strong><?php print $lang['SUBTITLE']; ?></strong></td>
        <td width="5%" class="text-center"><strong><?php print $lang['AUTHOR']; ?></strong></td>
        <td width="5%" class="text-center"><strong><?php print $lang['GROUP']; ?></strong></td>
        <td width="10%" class="text-center"><strong><?php print $lang['START_PUBLISH']; ?></strong></td>
        <td width="7%" class="text-center"><strong><?php print $lang['COMMENTS']; ?></strong></td>
        <td width="10%" class="text-center"><strong><?php print $lang['ACTIONS']; ?></strong></td>
    </tr>
    </thead>
    <tbody>
    <?php

    if ($res = $db->query("SELECT ci.*, cu.username as username, cr.value as gid FROM {blog_items} as ci
                           INNER JOIN {users} as cu on ci.uid = cu.id
                           INNER JOIN {user_groups} as cr on ci.itemgid = cr.id
                           WHERE blogid = '" . $blog->id . "' ORDER BY date_publish"))
    {
        while ($row = mysqli_fetch_assoc($res))
        {
            $date_publish = $row['date_publish'];
            $date_unpublish = $row['date_unpublish'];
            $atm = date("Y-m-d H:i:s");

            // on / off badge logic
            switch ($row['published']) {
                case 0:
                    $pub = "danger";
                    $pubtext = "Off";
                    break;
                case 1:
                    $pub = "success";
                    $pubtext = "On";
                    break;
            }

            // group id badge logic
            switch ($row['itemgid']) {
                case 1:
                    $rcolor = "success";
                    break;
                case 2:
                    $rcolor = "warning";
                    break;
                default:
                    $rcolor = "danger";
                    break;
            }

          if ($atm < $date_publish) {
              $pub = "warning";
              $pubtext = "in queue";
          }
          if ($date_unpublish !== "0000-00-00 00:00:00"){
              if ($atm >= $date_unpublish) {
              $pub = "default";
              $pubtext = "xpired";
              }
          }

            // count comments
            $i_comments = \YAWK\PLUGINS\BLOG\blog::countActiveComments($db, $blog->id, $row['id']);
            if ($i_comments !== '0') {
                $commentIcon = "<a href=\"index.php?plugin=blog&pluginpage=blog-comments&itemid=".$row['id']."&blogid=".$blog->id."\"><i class=\"fa fa-comments-o\"></i></a>&nbsp;&nbsp;";
            }
            else {
                $commentIcon = '&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;';
                $i_comments = '';
            }
            // draw table and badges
            echo "<tr>
                    <td class=\"text-center\">
                    <a href=\"index.php?plugin=blog&pluginpage=blog-entries&toggle=1&published=" . $row['published'] . "&blogid=" . $blog->id . "&id=" . $row['id'] . "\">
                    <span class=\"label label-$pub\">$pubtext</span></a>&nbsp;</td>
                    <td class=\"text-center\">" . $row['id'] . "</td>
                    <td><a href=\"index.php?plugin=blog&pluginpage=blog-edit&itemid=" . $row['id'] . "&blogid=" . $blog->id . "\"><div style=\"width:100%\">" . $row['title'] . "</div></a></td>
                    <td><a href=\"index.php?plugin=blog&pluginpage=blog-edit&itemid=" . $row['id'] . "&blogid=" . $blog->id . "\" style=\"color: #7A7376;\"><div style=\"width:100%\">" . $row['subtitle'] . "</div></a></td>
                    <td class=\"text-center\">" . $row['username'] . "</td>

                    <td class=\"text-center\">
                    <a href=\"index.php?plugin=blog&pluginpage=blog-togglegroup&itemgid=" . $row['itemgid'] . "&blogid=" . $blog->id . "&itemid=" . $row['id'] . "\">
                    <span class=\"label label-$rcolor\">" . $row['gid'] . "</span></a></td>

                    <td class=\"text-center\">" . $date_publish . "</td>
                    <td class=\"text-center\">" . $i_comments . "</td>
                    <td class=\"text-center\">
                       ".$commentIcon."
                      <a class=\"fa fa-copy\" title=\"" . $lang['COPY'] . ": " . $row['title'] . "\" href=\"index.php?plugin=blog&pluginpage=blog-entries&copy=true&itemgid=" . $row['itemgid'] . "&itemid=".$row['id']."&blogid=" . $blog->id . "\"></a>&nbsp;

                     

                      <a class=\"fa fa-edit\" title=\"" . $lang['EDIT'] . ": " . $row['title'] . "\" href=\"index.php?plugin=blog&pluginpage=blog-edit&itemid=" . $row['id'] . "&blogid=" . $blog->id . "\"></a>&nbsp;
                      <a class=\"fa fa-trash-o\" role=\"dialog\" data-confirm=\"Soll der Eintrag &laquo;" . $row['id'] . " / " . $row['title'] . "&raquo; wirklich gel&ouml;scht werden?\"
                      title=\"" . $lang['DEL'] . "\" href=\"index.php?plugin=blog&pluginpage=blog-entries&title=".$row['title']."&pageid=" . $row['pageid'] . "&itemid=" . $row['id'] . "&blogid=" . $blog->id . "&delete=1\">
                      </a>
                    </td>
                  </tr>";
        }
    }
    else
    {
        \YAWK\alert::draw("warning", "Warning: ", "Could not fetch blog entries from database", "","3800");
    }
    ?>
    </tbody>
</table>

    </div>
</div>