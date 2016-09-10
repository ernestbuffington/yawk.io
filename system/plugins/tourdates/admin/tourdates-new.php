<!-- bootstrap date-timepicker -->
<link type="text/css" href="../system/engines/datetimepicker/css/datetimepicker.min.css" rel="stylesheet"/>
<script type="text/javascript" src="../system/engines/datetimepicker/js/bootstrap-datetimepicker.min.js"></script>
<!-- init datetimepicker -->
<script type="text/javascript">
    $(document).ready(function () {
        $('#datetimepicker1').datetimepicker({
            format: 'yyyy-mm-dd hh:ii'
        });
    });//]]>  /* END document.ready */
</script>
<?php
include '../system/plugins/tourdates/classes/tourdates.php';

/* if form is sent... */
if (isset($_POST['date'])) {
    $tourdates = new \YAWK\PLUGINS\TOURDATES\tourdates();
    $tourdates->date = filter_input(INPUT_POST, 'date');
    $tourdates->band = filter_input(INPUT_POST, 'band');
    $tourdates->venue = filter_input(INPUT_POST, 'venue');
    $tourdates->fburl = filter_input(INPUT_POST, 'fburl');

    if (!$res = $tourdates->create($db, $tourdates->date, $tourdates->band, $tourdates->venue, $tourdates->fburl))
    {   // q failed
        print \YAWK\alert::draw("danger", "Error", "Termin konnte nicht eingetragen werden.", "plugin=tourdates","4200");
        exit;
    }
    else
    {   //
        \YAWK\backend::setTimeout("index.php?plugin=tourdates", 0);
    }
}


// TEMPLATE WRAPPER - HEADER & breadcrumbs
echo "
    <!-- Content Wrapper. Contains page content -->
    <div class=\"content-wrapper\" id=\"content-FX\">
    <!-- Content Header (Page header) -->
    <section class=\"content-header\">";
/* draw Title on top */
echo \YAWK\backend::getTitle($lang['TOUR_DATES'], $lang['TOUR_DATES_ADD']);
echo"<ol class=\"breadcrumb\">
            <li><a href=\"index.php\" title=\"Dashboard\"><i class=\"fa fa-dashboard\"></i> Dashboard</a></li>
            <li><a href=\"index.php?page=plugins\" title=\"Plugins\"> Plugins</a></li>
            <li><a href=\"index.php?plugin=tourdates\" title=\"Tourdates\"> Tourdates</a></li>
            <li class=\"active\"><a href=\"index.php?plugin=tourdates&pluginpage=tourdates-new\" title=\"Add Date\"> Add</a></li>
         </ol>
    </section>
    <!-- Main content -->
    <section class=\"content\">";
/* page content start here */
?>

    <!-- FORM -->
    <form role="form" class="form" action="index.php?plugin=tourdates&pluginpage=tourdates-new&addgig=1" method="post">
        <!-- PUBLISH DATE -->
        <label for="datetimepicker1"><?php print $lang['DATE']; ?>:&nbsp;
            <input class="form-control" id="datetimepicker1" data-date-format="yyyy-mm-dd hh:mm:ss" type="text"
                   name="date" maxlength="19">
        </label>
        &nbsp;

        <!-- TEXT FIELD -->
        <label for="band"><?php print $lang['TOUR_BAND']; ?>&nbsp;
            <input type="text" id="band" size="28" name="band" class="form-control" maxlength="128"
                   placeholder="<?PHP print $lang['TOUR_BAND_INPUT']; ?>"/>
        </label>
        <!-- TEXT FIELD -->
        <label for="venue"><?php print $lang['TOUR_VENUE']; ?>&nbsp;
            <input type="text" id="venue" size="28" name="venue" class="form-control" maxlength="128"
                   placeholder="<?PHP print $lang['TOUR_VENUE_INPUT']; ?>"/>
        </label>
        <!-- TEXT FIELD -->
        <label for="fburl"><?php print $lang['TOUR_FBLINK']; ?>&nbsp;
            <input type="text" id="fblink" size="28" name="fburl" class="form-control" maxlength="255"
                   placeholder="<?PHP print $lang['TOUR_FBLINK']; ?>"/>
        </label>
        <!-- SUBMIT BUTTON -->
        <input type="submit" class="btn btn-success" value="<?PHP print $lang['TOUR_DATES_ADD']; ?>"/>
    </form>

<?php
// to render layout correctly, include the footer
\YAWK\backend::drawHtmlFooter();
?>