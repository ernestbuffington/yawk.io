<!-- JS includes -->
<!-- jvectormap -->
<link rel="stylesheet" href="../system/engines/AdminLTE/plugins/jvectormap/jquery-jvectormap-1.2.2.css">
<!-- jvectormap -->
<script src="../system/engines/AdminLTE/plugins/jvectormap/jquery-jvectormap-1.2.2.min.js"></script>
<script src="../system/engines/AdminLTE/plugins/jvectormap/jquery-jvectormap-world-mill-en.js"></script>
<!-- SlimScroll 1.3.0 -->
<script src="../system/engines/AdminLTE/plugins/slimScroll/jquery.slimscroll.min.js"></script>
<!-- ChartJS 1.0.1 -->
<script src="../system/engines/AdminLTE/plugins/chartjs/Chart.min.js"></script>
<?php
// TEMPLATE WRAPPER - HEADER & breadcrumbs
echo "
    <!-- Content Wrapper. Contains page content -->
    <div class=\"content-wrapper\" id=\"content-FX\">
    <!-- Content Header (Page header) -->
    <section class=\"content-header\">";
/* draw Title on top */
echo \YAWK\backend::getTitle($lang['STATS'], $lang['STATS_SUBTEXT']);
echo"<ol class=\"breadcrumb\">
            <li><a href=\"index.php\" title=\"Dashboard\"><i class=\"fa fa-dashboard\"></i> Dashboard</a></li>
            <li class=\"active\"><a href=\"index.php?page=yawk-stats\" title=\"Pages\"> Statistics</a></li>
         </ol>
    </section>
    <!-- Main content -->
    <section class=\"content\">";
/* page content start here */
?>

<?php
// include stats class
    require_once '../system/classes/stats.php';
// check if stats object is set...
    if (!isset($stats))
    {   // if not, create new
        $stats = new \YAWK\stats();
    }
// load stats data into an array that every box will use, this saves performance
    $data = $stats->getStatsArray($db);

?>

<div class="row">
    <div class="col-md-8">

        <!-- box -->
        <div class="box">
            <div class="box-header with-border">
                Statistics / Hits and Users
            </div>
            <div class="box-body">
                hits overall, % of users (guests), % of logged in users, average group id
            </div>
        </div>
        <!-- / box -->

        <!-- box -->
        <div class="box">
            <div class="box-header with-border">
                Device Types
            </div>
            <div class="box-body">
                desktop, phone, tablet
            </div>
        </div>
        <!-- / box -->

        <!-- OS box -->
        <div class="row">
        <div class="col-md-6"><?php $stats->drawOsBox($db, $data, 100); ?></div>
        <div class="col-md-6"><?php $stats->drawOsVersionBox($db, $data, 100); ?></div>
        </div>

        <!-- / box -->

        <!-- box -->
        <?php $stats->drawBrowserBox($db, $data, 100); ?>

        <!-- box -->
        <div class="box">
            <div class="box-header with-border">
                Languages
            </div>
            <div class="box-body">
                all users languages
            </div>
        </div>
        <!-- / box -->

    </div>
    <div class="col-md-4">

        <!-- box -->
        <div class="box">
            <div class="box-header with-border">
                Statistics / Pages
            </div>
            <div class="box-body">
                current visited page, referrer
            </div>
        </div>

        <?php // $stats->calculateStatsFromArray($db, $data); ?>
        <!-- / box -->

    </div>
</div>

<div class="row">
    <div class="col-md-4">spalte 1</div>
    <div class="col-md-4">spalte 2</div>
    <div class="col-md-4">spalte 3</div>
</div>
