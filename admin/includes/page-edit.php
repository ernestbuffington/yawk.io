<?php
if (!isset($page))
{   // generate new page object
    $page = new YAWK\page();
}

/* page content start here */

if (isset($_GET['alias'])){
    $alias = $db->quote($_GET['alias']);
    $page->loadProperties($db,$alias);
}
if(isset($_POST['save'])){
    $page->deleteContent("../");
    $page->id = $db->quote($_POST['id']);
    $page->gid = $db->quote($_POST['gid']);
    $page->title = $db->quote($_POST['title']);
    $page->alias = $db->quote($_POST['alias']);
    $page->menu = $db->quote($_POST['menu']);
    $page->searchstring = $db->quote($_POST['searchstring']);
    $page->published = $db->quote($_POST['published']);
    $page->date_publish = $db->quote($_POST['date_publish']);
    $page->date_unpublish = $db->quote($_POST['date_unpublish']);
    $page->metadescription = $db->quote($_POST['metadescription']);
    $page->metakeywords = $db->quote($_POST['metakeywords']);
    $page->bgimage = $db->quote($_POST['bgimage']);
    // after preparing the vars, update db + write content
    if($page->save($db)) {
          // encode chars
        $_POST['content'] = \YAWK\sys::encodeChars($_POST['content']);
        // write content to file
        if ($page->writeContent("../", stripslashes(str_replace('\r\n', '', ($_POST['content']))))) {
            print YAWK\alert::draw("success", "Success!", "The page has been saved!","page=page-edit&alias=$page->alias&id=$page->id", "800");
            exit;
          }
          else {
              print YAWK\alert::draw("danger", "Error!", "File $page->alias could not be written. Check CHMOD!","page=page-edit&alias=$page->alias&id=$page->id", "4200");
              exit;
          }
    }
    else {
       print YAWK\alert::draw("danger", "Error!", "Could not store data of $page->alias into database!","page=page-edit&alias=$page->alias&id=$page->id", "4200");
       exit;
    }
}
// path to cms	
$dirprefix = YAWK\sys::getDirPrefix($db);
// path to cms	
$host = YAWK\sys::getHost($db);

/* alias string manipulation */
$alias = $page->alias;
$alias = mb_strtolower($alias); // lowercase
$alias = str_replace(" ", "-", $alias); // replace all ' ' with -
// special chars
$umlaute =array("/&auml;/","/&uuml;/","/&ouml;/","/&Auml;/","/&Uuml;/","/&Ouml;/","/&szlig;/"); // array of special chars
$ersetze = array("ae","ue","oe","Ae","Ue","Oe","ss"); // array of replacement chars
$alias = preg_replace($umlaute,$ersetze,$alias);	    // replace with preg
$page->alias = preg_replace("/[^a-z0-9\-\/]/i","",$alias); // final check: just numbers and chars are allowed

// make sure that user cannot change index' article name (index.php/html)
 if ($page->alias === "index") { $readonly = "readonly"; }
?>

<!-- bootstrap date-timepicker -->
<link type="text/css" href="../system/engines/datetimepicker/css/datetimepicker.min.css" rel="stylesheet" />
<script type="text/javascript" src="../system/engines/datetimepicker/js/bootstrap-datetimepicker.min.js"></script>
 	 
<!-- include summernote css/js-->
<!-- include codemirror (codemirror.css, codemirror.js, xml.js, formatting.js) -->
<link rel="stylesheet" type="text/css" href="//cdnjs.cloudflare.com/ajax/libs/codemirror/3.20.0/codemirror.min.css" />
<link rel="stylesheet" type="text/css" href="//cdnjs.cloudflare.com/ajax/libs/codemirror/3.20.0/theme/monokai.min.css" />
<!-- include codemirror (codemirror.css, codemirror.js, xml.js, formatting.js) -->
<script type="text/javascript" src="//cdnjs.cloudflare.com/ajax/libs/codemirror/3.20.0/codemirror.js"></script>
<script type="text/javascript" src="//cdnjs.cloudflare.com/ajax/libs/codemirror/3.20.0/mode/xml/xml.js"></script>
<script type="text/javascript" src="//cdnjs.cloudflare.com/ajax/libs/codemirror/2.36.0/formatting.js"></script>


<link href="../system/engines/summernote/dist/summernote.css" rel="stylesheet">
<script src="../system/engines/summernote/dist/summernote.min.js"></script>
<script type="text/javascript">
$(document).ready(function() {
$('#summernote').summernote({
  height: 420,                 // set editor height
  minHeight: null,             // set minimum height of editor
  maxHeight: null,             // set maximum height of editor
  focus: true,                 // set focus to editable area after initializing summernote

  codemirror: { // codemirror options
  theme: 'monokai'
  }

});
});
</script>

<script type="text/javascript" >
$(document).ready(function() {
$('#datetimepicker1').datetimepicker({
    format: 'yyyy-mm-dd hh:ii:ss'
});

$('#datetimepicker2').datetimepicker({
    format: 'yyyy-mm-dd hh:ii:ss'
});
});//]]>  /* END document.ready */
/* ...end admin jQ controlls  */
</script>

<?php
// TEMPLATE WRAPPER - HEADER & breadcrumbs
echo "
<!-- Content Wrapper. Contains page content -->
<div class=\"content-wrapper\" id=\"content-FX\">
    <!-- Content Header (Page header) -->
    <section class=\"content-header\">";
        /* draw Title on top */
        echo \YAWK\backend::getTitle($page->title, $lang['PAGE_EDIT']);
        echo"<ol class=\"breadcrumb\">
            <li><a href=\"index.php\" title=\"Dashboard\"><i class=\"fa fa-dashboard\"></i> Dashboard</a></li>
            <li><a href=\"index.php?page=pages\" title=\"Pages\"> Pages</a></li>
            <li class=\"active\"><a href=\"index.php?page=page-edit\" title=\"Edit Page\"> Edit Page</a></li>
        </ol>
    </section>
    <!-- Main content -->
    <section class=\"content\">";
?>



  <form name="form" role="form" class="form-inline" action="index.php?page=page-edit&site=<?php print $page->alias; ?>" method="post">

      <div class="row">
          <div class="col-md-8">
    <!-- EDITOR -->
    <label for="summernote"></label>
  	<textarea id="summernote" name="content"><?php print $page->readContent("../"); ?> </textarea>

    <!-- SAVE BUTTON -->
    <button type="submit" id="savebutton" name="save" class="btn btn-danger" style="float:right; margin-top:6px;">
      <i class="fa fa-check"></i> &nbsp;<?php print $lang['SAVE_CHANGES']; ?>
    </button>

  	<!-- SAVEBUTTON OLD (w/o icon)
 	<input id="savebutton" name="save" class="btn btn-danger" style="float:right; margin-top:10px;" type="submit" value="<?php print $lang['SAVE_CHANGES']; ?>" />
    -->
	<input type="hidden" name="searchstring" value="<?php print $page->alias; ?>.html" >
	<input type="hidden" name="id" value="<?php print $_GET['id']; ?>" >

<br><br>

          </div>
          <div class="col-md-4">
          <!-- 2nd col -->
              <dl>
                  <h4><i class="fa fa-file-text-o"></i>&nbsp;&nbsp;Einstellungen <small>Titel und Dateiname</small></h4>
                  <dt> <!-- TITLE -->
                      <label for="title"><?php print $lang['TITLE']; ?></label>
                  </dt>
                  <dd>
                      <input id="title" type="text" class="form-control" name="title" size="64" maxlength="255" value="<?php print $page->title; ?>" />
                  </dd>
                  <dt> <!-- ALIAS -->
                      <label for="alias">Filename</label>
                  </dt>
                  <dd>
                      <input id="alias" type="text" class="form-control" name="alias" size="64" maxlength="255"
                      <?php if (isset($readonly)) { print $readonly; } ?> value="<?php print $page->alias; ?>">
                  </dd>
                    <hr>
                  <h4><i class="fa fa-clock-o"></i>&nbsp;&nbsp;Ver&ouml;ffentlichung <small>Zeitpunkt & Privatsph&auml;re</small></h4>
                  <dt> <!-- PUBLISH DATE -->
                      <label for="datetimepicker1"><?php print $lang['START_PUBLISH']; ?>
                  </dt>
                  <dd>
                      <input class="form-control" id="datetimepicker1" data-date-format="yyyy-mm-dd hh:mm:ss" type="text" name="date_publish" maxlength="19" value="<?php print $page->date_publish; ?>">
                  </dd>
                  <dt> <!-- END PUBLISH DATE -->
                      <label for="datetimepicker2"><?php print $lang['END_PUBLISH']; ?></label>
                  </dt>
                  <dd>
                      <input type="text" class="form-control" id="datetimepicker2" name="date_unpublish" data-date-format="yyyy-mm-dd hh:mm:ss"maxlength="19" value="<?php print $page->date_unpublish; ?>">
                  </dd>
                  <dt> <!-- ACCESS RIGHTS - gid select field -->
                      <label for="gidselect"> <?php print $lang['PAGE_VISIBLE']; ?></label>
                  </dt>
                  <dd>
                      <select id="gidselect" name="gid" class="form-control">
                          <option value="<?php print \YAWK\sys::getGroupId($db, $page->id, "pages"); ?>" selected><?php print \YAWK\user::getGroupNameFromID($db, $page->gid); ?></option>
                          <?php
                          foreach(YAWK\sys::getGroups($db, "pages") as $role) {
                              print "<option value=\"".$role['id']."\"";
                              if (isset($_POST['gid'])) {
                                  if($_POST['gid'] === $role['id']) {
                                      print " selected=\"selected\"";
                                  }
                                  else if($page->gid === $role['id'] && !$_POST['gid']) {
                                      print " selected=\"selected\"";
                                  }
                              }
                              print ">".$role['value']."</option>";
                          }
                          ?>
                      </select>
                  </dd>

                  <dt>
                      <!-- PAGE ON / OFF STATUS -->
                      <label for="published"><?php print $lang['PAGE_STATUS']; ?></label>
                  </dt>
                  <dd>
                      <?php if($page->published === '1'){
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
              </dl>

              <!-- META TAGS -->
              <hr>
              <h4><i class="fa fa-google"></i>&nbsp;&nbsp;Meta Tags <small>Suchmaschinenoptimierung f&uuml;r diese Seite.</small></h4>
              <!-- LOCAL META SITE DESCRIPTION -->
              <label for="metadescription">Meta Description</label>
              <input type="text" size="64" id="metadescription" class="form-control" maxlength="255" placeholder="Page Description shown on Google" name="metadescription" value="<?php print $page->getMetaTags($db, $page->id, "description");  ?>">
              <!-- LOCAL META SITE KEYWORDS -->
              <label for="metakeywords">Meta Keywords</label>
              <input type="text" size="64" id="metakeywords" class="form-control" placeholder="The most important keywords of this site. Use , to seperate the words." name="metakeywords" value="<?php print $page->getMetaTags($db, $page->id, "keywords");  ?>">
              <hr>
              <!-- ENDE META TAGS -->

              <!-- BACKGROUND IMAGE -->
              <h4><i class="fa fa-photo"></i>&nbsp;&nbsp;Hintergrundbild <small>spezifisch für diese Seite</small></h4>
              <!-- SITE BG IMAGE -->
              Background Image
              <input type="text" size="64" class="form-control" placeholder="media/images/background.jpg" name="bgimage" value="<?php print $page->bgimage;  ?>">

              <hr>
              <!-- ENDE META TAGS -->
              <h4><i class="fa fa-bars"></i>&nbsp;&nbsp;SubMen&uuml; <small>zusätzliches Men&uuml; auf dieser Seite</small></h4>

              <!-- SUB MENU SELECTOR -->
              <label for="menu">SubMen&uuml;
                  <select name="menu" class="form-control">
                      <option value="<? print \YAWK\sys::getSubMenu($db, $page->id); ?>"><?php print \YAWK\sys::getMenuItem($db, $page->id); ?></option>
                      <option value="0">-- Kein Men&uuml; --</option>
                      <?php
                      foreach(YAWK\sys::getMenus($db) as $menue){
                          print "<option value=\"".$menue['id']."\"";

                          if (isset($_POST['menu'])) {
                              if($_POST['menu'] === $menue['id']){
                                  print " selected=\"selected\"";
                              }
                              else if($page->menu === $menue['id'] && !$_POST['menu']){
                                  print " selected=\"selected\"";
                              }
                          }
                          print ">".$menue['name']."</option>";
                      }
                      ?>
                  </select>
              </label>
          </div>
      </div>

      </form>

