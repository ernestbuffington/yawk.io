<script src="https://cdnjs.cloudflare.com/ajax/libs/Chart.js/2.4.0/Chart.min.js"></script>
<?php

use YAWK\backend;
use YAWK\db;
use YAWK\language;
use YAWK\sys;

/** @var $db db */
/** @var $lang language */

// TEMPLATE WRAPPER - HEADER & breadcrumbs
echo "
    <!-- Content Wrapper. Contains page content -->
    <div class=\"content-wrapper\" id=\"content-FX\">
    <!-- Content Header (Page header) -->
    <section class=\"content-header\">";
/* draw Title on top */
echo backend::getTitle($lang['STATS'], $lang['STATS_SUBTEXT']);
echo"<ol class=\"breadcrumb\">
            <li><a href=\"index.php\" title=\"Dashboard\"><i class=\"fa fa-dashboard\"></i> Dashboard</a></li>
            <li class=\"active\"><a href=\"index.php?page=yawk-stats\" title=\"Pages\"> Statistics</a></li>
         </ol>
    </section>
    <!-- Main content -->
    <section class=\"content\">";
/* page content start here */
?>
<h3>Seitenaufrufe</h3>
<div class="col-md-8">
    <div class="box">
        <div class="box-header"><h3 class="box-title">Month</h3></div>
        <div class="box-body">
            <canvas id="myChart"></canvas>
        </div>
    </div>
</div>
<div class="col-md-4">
    <?php
    if (sys::isBrowscapSet($_SERVER['HTTP_USER_AGENT']) === false)
    {
      echo "Your Browser: <b>". sys::getBrowserName($_SERVER['HTTP_USER_AGENT'])."</b>";
    }
    $useragent = sys::getBrowser('');
    echo "<h4>Browser Statistik </h4>Your browser: "."<b>". $useragent['name'] . " " . $useragent['version'] . " on " .$useragent['platform'] ."</b><br><br>";

    echo "<h4>User Statistik</h4>Referer: ".$_SERVER['HTTP_REFERER']."<br>";
    echo "Current: ".$_SERVER['REQUEST_URI']."<br>";
    echo "accept language: ".$_SERVER['HTTP_ACCEPT_LANGUAGE']."<br><br>";

    echo "<h4>Quellcode Statistik</h4>";
    echo "YaWK Version: ".\YAWK\settings::getSetting($db, "yawkversion");
    echo " <small>";echo \YAWK\settings::getSettingDescription($db, "yawkversion");echo"</small>";

    // SET VARS
    $FILE_PATH = "../"; // full path
    $data = sys::countCodeLines($FILE_PATH, '.php');

    echo"<br>YaWK ($FILE_PATH) umfasst insgesamt <b>$data[files]</b> $data[type] files mit exakt <b>$data[lines]</b> Zeilen $data[type] Code</p><br>";

    echo "<h4>Server Statistik</h4>";
    if (sys::checkZlib() === true)
    {   // output
        echo "<p>...zlib found!</p>";
    }
    else
    {   // output
        echo "<p class=\"text-danger\">...zlib not found!</p>";
    }


    /*
     $total=$anz_lines + $anz_lines1;
     $total = number_format($total);
     echo "und <b>$anz_lines</b> Zeilen .html-Code.</b><br> Insgesamt z&auml;hlt das Projekt: <b>$total</b> Zeilen Quellcode.";
     */
    ?>
</div>




<script>
    var ctx = document.getElementById('myChart').getContext('2d');
    var chart = new Chart(ctx, {
        // The type of chart we want to create
        type: 'bar',

        // The data for our dataset
        data: {
            labels: ["01", "02", "03", "04", "05", "06", "07", "08", "09", "10", "11", "12", "13", "14", "15",
                "16", "17", "18", "19", "20", "21", "22", "23", "24", "25", "26", "27", "28", "29", "30", "31"],
            datasets: [{
                label: "Hits this day",
                borderColor: 'rgb(51, 122, 183)',
                backgroundColor: 'rgb(51, 122, 183)',
                data: [11, 10, 5, 2, 20, 30, 45, 16, 54, 34, 46, 67, 55, 46, 46, 47, 26, 11, 10, 5, 2, 20, 30,
                    45, 16, 64, 58, 46, 67, 55, 46]
            }]
        },

        // Configuration options go here
        options: {}
    });
</script>
