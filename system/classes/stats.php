<?php

namespace YAWK
{

    /**
     * Class stats
     * @package YAWK
     *
     */
    class stats
    {
        public $id;
        public $uid;
        public $gid;
        public $logged_in;
        public $acceptLanguage;
        public $remoteAddr;
        public $userAgent;
        public $device;
        public $deviceType;
        public $os;
        public $osVersion;
        public $browser;
        public $browserVersion;
        public $date_created;
        public $referer;
        public $page;


        // stats variables
        public $i_hits = 0;
        public $i_loggedUsers = 0;
        public $i_loggedUsersPercentage = 0;
        public $i_publicUsers = 0;
        public $i_publicUsersPercentage = 0;

        // os types
        public $i_osWindows = 0;
        public $i_osMac = 0;
        public $i_osLinux = 0;
        public $i_osAndroid = 0;
        public $i_iOS = 0;
        public $i_osUnknown = 0;

        // os versions
        public $i_windows8 = 0;
        public $i_windows7 = 0;
        public $i_windowsVista = 0;
        public $i_windowsServer = 0;
        public $i_windowsXP = 0;
        public $i_windows2000 = 0;
        public $i_windowsME = 0;
        public $i_windows98 = 0;
        public $i_windows95 = 0;
        public $i_windows311 = 0;
        public $i_macosX = 0;
        public $i_macos9 = 0;
        public $i_linux = 0;
        public $i_ubuntu = 0;
        public $i_iPhone = 0;
        public $i_iPod = 0;
        public $i_iPad = 0;
        public $i_android = 0;
        public $i_blackberry = 0;
        public $i_mobile = 0;
        public $i_others = 0;

        // devices
        public $i_desktop = 0;
        public $i_tablet = 0;
        public $i_phone = 0;


        function construct()
        {
            // ...
        }
        function setStats($db)
        {   /* @var $db \YAWK\db */
            // check if stats are enabled
            if (\YAWK\settings::getSetting($db, "statsEnable") === "1")
            {   // prepare user information that we can easily collect
                $this->prepareData();
                // insert statistics into database
                if ($this->insertData($db) === false)
                {   // insert stats failed, add syslog entry
                    \YAWK\sys::setSyslog($db, 12, "could not insert stats into database.", "", "", "","");
                }
            }
        }

        function prepareData()
        {
            // check if a session is set
            if (isset($_SESSION) && (!empty($_SESSION)))
            {   // prepare all session user data
                // user id (if logged in)
                $this->uid = $_SESSION['uid'];
                // user group id (if logged in)
                $this->gid = $_SESSION['gid'];
                // user logged in status (0|1)
                $this->logged_in = $_SESSION['logged_in'];
            }
            else
                {   // no session is set
                    // user is a guest - or obviously not logged in
                    $this->uid = 0;
                    $this->gid = 0;
                    $this->logged_in = 0;
                }
            // user IP address
            $this->remoteAddr = $_SERVER['REMOTE_ADDR'];
            // user client username
            // $this->remoteUser = $_SERVER['REMOTE_HOST'];
            // user agent information
            $this->userAgent = $_SERVER['HTTP_USER_AGENT'];
            // user browser

            // Include and instantiate the class.
            require_once 'system/engines/mobiledetect/Mobile_Detect.php';
            $detect = new \Mobile_Detect;

            $this->deviceType = ($detect->isMobile() ? ($detect->isTablet() ? 'Tablet' : 'Phone') : 'Desktop');

            // Any mobile device (phones or tablets).
            if ( $detect->isMobile() ) {
                // $this->deviceType = "Mobile";
                $browser = array();
                $browser = \YAWK\sys::getBrowser($this->userAgent);
                $this->browser = $browser['name'];
                $this->browserVersion = $browser['version'];
            }

            // Any tablet device.
            if( $detect->isTablet() ){
                // $this->deviceType = "Tablet";
                $browser = array();
                $browser = \YAWK\sys::getBrowser($this->userAgent);
                $this->browser = $browser['name'];
                $this->browserVersion = $browser['version'];
            }

            // No Mobile, no tablet - must be a computer
            if( !$detect->isMobile() && !$detect->isTablet() ){
                // $this->deviceType = "Computer";
                $browser = array();
                $browser = \YAWK\sys::getBrowser($this->userAgent);
                $this->browser = $browser['name'];
                $this->browserVersion = $browser['version'];
                $this->os = ucfirst($browser['platform']);
                $this->osVersion = \YAWK\sys::getOS($this->userAgent);
            }

            // check OS for iOS
            if( $detect->isiOS() ){
                $this->os = "iOS";
                // detect wheter its a phone, pad or pod
                if ( $detect->version('iPhone') ) {
                    $this->device = "iPhone";
                    $this->osVersion = $detect->version('iPhone');
                }
                if ( $detect->version('iPad') ) {
                    $this->device = "iPad";
                    $this->osVersion = $detect->version('iPad');
                }
                if ( $detect->version('iPod') ) {
                    $this->device = "iPod";
                    $this->osVersion = $detect->version('iPod');
                }
            }
            else
                {   // check OS for android
                    if( $detect->isAndroidOS() ){
                        $this->os = "Android";
                        $this->osVersion = $detect->version('Android');
                    }
                }

            // set remote user
            $this->acceptLanguage = $_SERVER['HTTP_ACCEPT_LANGUAGE'];

            // the referer page from which the user came
            if (!isset($_SERVER['HTTP_REFERER']) || (empty($_SERVER['HTTP_REFERER'])))
            {   // empty referer
                $this->referer = '';
            }
            else
                {   // set referer
                    $this->referer = $_SERVER['HTTP_REFERER'];
                }
            // check if include (page request) is set
            if (!isset($_GET['include']) || (empty($_GET['include'])))
            {   // if no page is set, take server variable
                $this->page = $_SERVER['REQUEST_URI'];
            }
            else
                {   // set requested page
                    $this->page = $_GET['include'];
                }

            // current datetime
            $this->date_created = \YAWK\sys::now();
        }


        static function countMessages($db)
        {   /* @var $db \YAWK\db */
            if ($res = $db->query("SELECT COUNT(*) FROM {plugin_msg}"))
            {   // fetch and return how many messages have been sent
                $messageCount = mysqli_fetch_row($res);
                return $messageCount[0];
            }
            else
                {
                    $messageCount = "db error: could not count messages";
                    return $messageCount;
                }
        }


        static function getJsonBrowsers($db, $browsers)
        {   /* @var $db \YAWK\db */
            // check if browsers are set
            if (!isset($browsers) || (empty($browsers)))
            {   // nope, get them from db
                $browsers = self::countBrowsers($db, '', 200);
            }
            $jsonData = "[";
            foreach ($browsers AS $browser => $value)
            {
                // init textcolor
                $textcolor = '';
                // set different colors for each browser
                if ($browser === "Chrome") { $textcolor = "#f56954"; }
                if ($browser === "IE") { $textcolor = "#00a65a"; }
                if ($browser === "Firefox") { $textcolor = "#f39c12"; }
                if ($browser === "Safari") { $textcolor = "#00c0ef"; }
                if ($browser === "Opera") { $textcolor = "#3c8dbc"; }
                if ($browser === "Netscape") { $textcolor = "#d2d6de"; }
                if ($browser === "Others") { $textcolor = "#cccccc"; }

                // only browsers, not the total value
                if ($browser !== ("Total"))
                {
                    $jsonData .= "
                            {
                                value: $value,
                                color: \"$textcolor\",
                                highlight: \"$textcolor\",
                                label: \"$browser\"
                            },";
                }
            }

            $jsonData .= "]";
            echo $jsonData;
        }


        static function getJsonOS($db, $oss)
        {   /* @var $db \YAWK\db */
            // check if browsers are set
            if (!isset($oss) || (empty($oss)))
            {   // nope, get them from db
                $oss = self::countOS($db, '', 200);
            }
            $jsonData = "[";
            foreach ($oss AS $os => $value)
            {
                // init textcolor
                $textcolor = '';
                // set different colors for each browser
                if ($os === "Windows") { $textcolor = "#00c0ef"; }
                if ($os === "Linux") { $textcolor = "#f56954"; }
                if ($os === "Mac") { $textcolor = "#f39c12"; }
                if ($os === "Android") { $textcolor = "#00a65a"; }
                if ($os === "iOS") { $textcolor = "#000000"; }
                if ($os === "Unknown") { $textcolor = "#cccccc"; }

                // only browsers, not the total value
                if ($os !== ("Total"))
                {
                    $jsonData .= "
                            {
                                value: $value,
                                color: \"$textcolor\",
                                highlight: \"$textcolor\",
                                label: \"$os\"
                            },";
                }
            }

            $jsonData .= "]";
            echo $jsonData;
        }

        static function getJsonOSVersions($db, $osVersions)
        {   /* @var $db \YAWK\db */
            // check if browsers are set
            if (!isset($osVersions) || (empty($osVersions)))
            {   // nope, get them from db
                $osVersions = self::countOSVersions($db, '', 200);
            }
            $jsonData = "[";
            foreach ($osVersions AS $osVersion => $value)
            {
                // init textcolor
                $textcolor = '';
                // set different colors for each OS version
                if ($osVersion === "Windows 8") { $textcolor = "#00c0ef"; }
                if ($osVersion === "Windows 7") { $textcolor = "#00A0C7"; }
                if ($osVersion === "Windows Vista") { $textcolor = "#00B5E1"; }
                if ($osVersion === "Windows Server") { $textcolor = "#004E61"; }
                if ($osVersion === "Windows 2000") { $textcolor = "#005A7F"; }
                if ($osVersion === "Windows XP") { $textcolor = "#00B5FF"; }
                if ($osVersion === "Windows ME") { $textcolor = "#0090C9"; }
                if ($osVersion === "Windows 98") { $textcolor = "#00A5E5"; }
                if ($osVersion === "Windows 95") { $textcolor = "#0089BF"; }
                if ($osVersion === "Windows 3.11") { $textcolor = "#00ACBF"; }
                if ($osVersion === "Mac OS X") { $textcolor = "#f39c12"; }
                if ($osVersion === "Mac OS 9") { $textcolor = "#BD7A0E"; }
                if ($osVersion === "Linux") { $textcolor = "#f56954"; }
                if ($osVersion === "Ubuntu") { $textcolor = "#BF5242"; }
                if ($osVersion === "iPhone") { $textcolor = "#212121"; }
                if ($osVersion === "iPad") { $textcolor = "#131313"; }
                if ($osVersion === "iPod") { $textcolor = "#212121"; }
                if ($osVersion === "Android") { $textcolor = "#6FF576"; }
                if ($osVersion === "Blackberry") { $textcolor = "#187521"; }
                if ($osVersion === "Mobile") { $textcolor = "#437540"; }
                if ($osVersion === "Unknown") { $textcolor = "#6B756D"; }

                // only browsers, not the total value
                if ($osVersion !== ("Total"))
                {
                    $jsonData .= "
                            {
                                value: $value,
                                color: \"$textcolor\",
                                highlight: \"$textcolor\",
                                label: \"$osVersion\"
                            },";
                }
            }

            $jsonData .= "]";
            echo $jsonData;
        }


        public function getJsonDeviceTypes($db, $deviceTypes)
        {   /* @var $db \YAWK\db */
            // check if device types are set
            if (!isset($deviceTypes) || (empty($deviceTypes)))
            {   // nope, get them from db
                $deviceTypes = self::countDeviceTypes($db, '', 200);
            }

            $jsonData = "labels: ['Desktop', 'Phone', 'Tablet'],
            datasets: [
                {
                  label: 'Hits',
                  fillColor: ['#f39c12', '#00a65a', '#00c0ef'],
                  strokeColor: 'rgba(210, 214, 222, 1)',
                  pointColor: 'rgba(210, 214, 222, 1)',
                  pointStrokeColor: '#c1c7d1',
                  pointHighlightFill: '#fff',
                  pointHighlightStroke: 'rgba(220,220,220,1)',
                  data: [$this->i_desktop, $this->i_phone, $this->i_tablet]
                }
            ]";


            /* pie data
            $jsonData = "[";
            foreach ($deviceTypes AS $deviceType => $value)
            {
                // init textcolor
                $textcolor = '';
                // set different colors for each browser
                if ($deviceType === "Desktop") { $textcolor = "#00c0ef"; }
                if ($deviceType === "Phone") { $textcolor = "#f56954"; }
                if ($deviceType === "Tablet") { $textcolor = "#f39c12"; }

                // only browsers, not the total value
                if ($deviceType !== ("Total"))
                {
                    $jsonData .= "
                            {
                                value: $value,
                                color: \"$textcolor\",
                                highlight: \"$textcolor\",
                                label: \"$deviceType\"
                            },";
                }
            }

            $jsonData .= "]";
            */
            echo $jsonData;
        }




        static function getBrowserColors($browser)
        {
            switch ($browser) {
                case "Chrome":
                    $textcolor = "text-red";
                    break;
                case "Google Chrome":
                    $textcolor = "text-red";
                    break;
                case "IE":
                    $textcolor = "text-green";
                    break;
                case "Internet Explorer":
                    $textcolor = "text-green";
                    break;
                case "Firefox":
                    $textcolor = "text-yellow";
                    break;
                case "Mozilla Firefox":
                    $textcolor = "text-yellow";
                    break;
                case "Safari":
                    $textcolor = "text-aqua";
                    break;
                case "Apple Safari":
                    $textcolor = "text-aqua";
                    break;
                case "Opera":
                    $textcolor = "text-light-blue";
                    break;
                case "Netscape":
                    $textcolor = "text-grey";
                    break;
                case "Navigator":
                    $textcolor = "text-grey";
                    break;
                default:
                    $textcolor = "text-black";
            }
            return $textcolor;
        }


        static function getOsColors($os)
        {
            switch ($os) {
                case "Windows":
                    $textcolor = "text-blue";
                    break;
                case "Linux":
                    $textcolor = "text-red";
                    break;
                case "Mac":
                    $textcolor = "text-orange";
                    break;
                case "Android":
                    $textcolor = "text-green";
                    break;
                case "Unknown":
                    $textcolor = "text-grey";
                    break;
                default:
                    $textcolor = "text-black";
            }
            return $textcolor;
        }


        static function getDeviceTypeColors($deviceType)
        {
            switch ($deviceType) {
                case "Desktop":
                    $textcolor = "text-orange";
                    break;
                case "Phone":
                    $textcolor = "text-green";
                    break;
                case "Tablet":
                    $textcolor = "text-blue";
                    break;
                default:
                    $textcolor = "text-black";
            }
            return $textcolor;
        }


        static function getOsVersionsColors($osVersions)
        {
            switch ($osVersions) {
                case "Windows 8":
                    $textcolor = "text-blue";
                    break;
                case "Windows 7":
                    $textcolor = "text-blue";
                    break;
                case "Windows Vista":
                    $textcolor = "text-blue";
                    break;
                case "Windows Server":
                    $textcolor = "text-blue";
                    break;
                case "Windows 2000":
                    $textcolor = "text-blue";
                    break;
                case "Windows XP":
                    $textcolor = "text-blue";
                    break;
                case "Windows ME":
                    $textcolor = "text-blue";
                    break;
                case "Windows 98":
                    $textcolor = "text-blue";
                    break;
                case "Windows 95":
                    $textcolor = "text-blue";
                    break;
                case "Windows 3.11":
                    $textcolor = "text-blue";
                    break;
                case "Windows 311":
                    $textcolor = "text-blue";
                    break;
                case "Mac OS X":
                    $textcolor = "text-orange";
                    break;
                case "Mac OS 9":
                    $textcolor = "text-orange";
                    break;
                case "Linux":
                    $textcolor = "text-red";
                    break;
                case "Ubuntu":
                    $textcolor = "text-red";
                    break;
                case "iPhone":
                    $textcolor = "text-black";
                    break;
                case "iPad":
                    $textcolor = "text-black";
                    break;
                case "iPod":
                    $textcolor = "text-black";
                    break;
                case "Android":
                    $textcolor = "text-green";
                    break;
                case "Blackberry":
                    $textcolor = "text-green";
                    break;
                case "Mobile":
                    $textcolor = "text-green";
                    break;
                case "Unknown":
                    $textcolor = "text-grey";
                    break;
                default:
                    $textcolor = "text-black";
            }
            return $textcolor;
        }


        static function countBrowsers($db, $data, $limit)
        {   /* @var $db \YAWK\db */

            // check if limit (i) is set
            if (!isset($limit) || (empty($limit)))
            {   // set default value
                $limit = 100;
            }

            // this vars stores the counting for each browser
            $n_msie = 0;
            $n_chrome = 0;
            $n_firefox = 0;
            $n_opera = 0;
            $n_safari = 0;
            $n_netscape = 0;
            $n_others = 0;
            $total = 0;


            // check if data array is set, if not load data from db
            if (!isset($data) || (empty($data) || (!is_array($data))))
            {   // data is not set or in false format, try to get it from database
                \YAWK\alert::draw("warning", "database needed", "need to get browser data - array not set, empty or not an array.", "", 0);
                if ($res = $db->query("SELECT browser FROM {stats} ORDER BY id DESC LIMIT $limit"))
                {   // create array
                    $data = array();
                    while ($row = mysqli_fetch_assoc($res))
                    {   // add data to array
                        $data[] = $row;
                        $total++;
                    }
                }
                else
                    {   // data array not set and unable to get data from db
                        return false;
                    }
            }

            // LIMIT the data to x entries
            if (isset($limit) && (!empty($limit)))
            {   // if limit is set, cut array to limited range
                $data = array_slice($data, 0, $limit, true);
            }

            // count browsers
            foreach ($data AS $item => $browser) {   // add +1 for each found
                switch ($browser['browser']) {
                    case "Google Chrome":
                        $n_chrome++;
                        break;
                    case "Internet Explorer":
                        $n_msie++;
                        break;
                    case "Mozilla Firefox":
                        $n_firefox++;
                        break;
                    case "Apple Safari":
                        $n_safari++;
                        break;
                    case "Opera":
                        $n_opera++;
                        break;
                    case "Netscape":
                        $n_netscape++;
                        break;
                    default:
                    $n_others++;
                    }
            }
            // get the sum of all detected browsers
            $total = $n_chrome+$n_msie+$n_firefox+$n_safari+$n_opera+$n_netscape+$n_others;

            // build an array, cointaining the browsers and the number how often it's been found
            $browsers = array(
                "Chrome" => $n_chrome,
                "IE" => $n_msie,
                "Firefox" => $n_firefox,
                "Safari" => $n_safari,
                "Opera" => $n_opera,
                "Netscape" => $n_netscape,
                "Others" => $n_others,
                "Total" => $total
            );

            // return browser data array
            return $browsers;
        }


        public function countDeviceTypes($db, $data, $limit)
        {   /* @var $db \YAWK\db */

            // check if limit (i) is set
            if (!isset($limit) || (empty($limit)))
            {   // set default value
                $limit = 100;
            }

            // check if data array is set, if not load data from db
            if (!isset($data) || (empty($data) || (!is_array($data))))
            {   // data is not set or in false format, try to get it from database
                if ($res = $db->query("SELECT deviceType FROM {stats} ORDER BY id DESC LIMIT $limit"))
                {   // create array
                    $data = array();
                    while ($row = mysqli_fetch_assoc($res))
                    {   // add data to array
                        $data[] = $row;
                    }
                }
                else
                {   // data array not set and unable to get data from db
                    return false;
                }
            }

            // LIMIT the data to x entries
            if (isset($limit) && (!empty($limit)))
            {   // if limit is set, cut array to limited range
                $data = array_slice($data, 0, $limit, true);
            }
            foreach ($data as $deviceType => $value)
            {
                // count device types
                switch ($value['deviceType'])
                {
                    case "Desktop":
                        $this->i_desktop++;
                        break;
                    case "Tablet":
                        $this->i_tablet++;
                        break;
                    case "Phone":
                        $this->i_phone++;
                        break;
                }
            }

            // count device types
            $total = $this->i_desktop+$this->i_tablet+$this->i_phone;
            // build an array, cointaining the device types and the number how often it's been found
            $deviceTypes = array(
                "Desktop" => $this->i_desktop,
                "Tablet" => $this->i_tablet,
                "Phone" => $this->i_phone,
                "Total" => $total
            );

            // return OS data array
            return $deviceTypes;
        }


        public function countOS($db, $data, $limit)
        {   /* @var $db \YAWK\db */

            // check if limit (i) is set
            if (!isset($limit) || (empty($limit)))
            {   // set default value
                $limit = 100;
            }

            // check if data array is set, if not load data from db
            if (!isset($data) || (empty($data) || (!is_array($data))))
            {   // data is not set or in false format, try to get it from database
                \YAWK\alert::draw("warning", "database needed", "need to get browser data - array not set, empty or not an array.", "", 0);
                if ($res = $db->query("SELECT os FROM {stats} ORDER BY id DESC LIMIT $limit"))
                {   // create array
                    $data = array();
                    while ($row = mysqli_fetch_assoc($res))
                    {   // add data to array
                        $data[] = $row;
                    }
                }
                else
                {   // data array not set and unable to get data from db
                    return false;
                }
            }

            // LIMIT the data to x entries
            if (isset($limit) && (!empty($limit)))
            {   // if limit is set, cut array to limited range
                $data = array_slice($data, 0, $limit, true);
            }
            foreach ($data as $os => $value)
            {
                // count Operating Systems
                switch ($value['os'])
                {
                    case "Windows";
                        $this->i_osWindows++;
                        break;
                    case "Linux";
                        $this->i_osLinux++;
                        break;
                    case "Mac";
                        $this->i_osMac++;
                        break;
                    case "Android";
                        $this->i_osAndroid++;
                        break;
                    case "iOS";
                        $this->i_iOS++;
                        break;
                    default: $this->i_osUnknown++;
                }
            }

            // count Operating Systems
            $total = $this->i_osWindows+$this->i_osLinux+$this->i_osMac+$this->i_osAndroid+$this->i_iOS+$this->i_osUnknown;
            // build an array, cointaining the browsers and the number how often it's been found
            $os = array(
                "Windows" => $this->i_osWindows,
                "Linux" => $this->i_osLinux,
                "Mac" => $this->i_osMac,
                "Android" => $this->i_osAndroid,
                "iOS" => $this->i_iOS,
                "Unknown" => $this->i_osUnknown,
                "Total" => $total
            );

            // return OS data array
            return $os;
        }


        public function countOSVersions($db, $data, $limit)
        {   /* @var $db \YAWK\db */

            // check if limit (i) is set
            if (!isset($limit) || (empty($limit)))
            {   // set default value
                $limit = 100;
            }

            // check if data array is set, if not load data from db
            if (!isset($data) || (empty($data) || (!is_array($data))))
            {   // data is not set or in false format, try to get it from database
                \YAWK\alert::draw("warning", "database needed", "need to get browser data - array not set, empty or not an array.", "", 0);
                if ($res = $db->query("SELECT osVersion FROM {stats} ORDER BY id DESC LIMIT $limit"))
                {   // create array
                    $data = array();
                    while ($row = mysqli_fetch_assoc($res))
                    {   // add data to array
                        $data[] = $row;
                    }
                }
                else
                {   // data array not set and unable to get data from db
                    return false;
                }
            }

            // LIMIT the data to x entries
            if (isset($limit) && (!empty($limit)))
            {   // if limit is set, cut array to limited range
                $data = array_slice($data, 0, $limit, true);
            }

            // count browsers
            foreach ($data AS $item => $osVersion) {   // add +1 for each found
                // count Operating Systems Versions
                if ($osVersion['os'] === "Android")
                {
                    $osVersion['osVersion'] .= "Android ".$osVersion['osVersion'];
                }
                switch ($osVersion['osVersion'])
                {
                    case "Windows 8";
                        $this->i_windows8++;
                        break;
                    case "Windows 7";
                        $this->i_windows7++;
                        break;
                    case "Windows Vista";
                        $this->i_windowsVista++;
                        break;
                    case "Windows Server 2003/XP x64";
                        $this->i_windowsServer++;
                        break;
                    case "Windows XP";
                        $this->i_windowsXP++;
                        break;
                    case "Windows 2000";
                        $this->i_windows2000++;
                        break;
                    case "Windows ME";
                        $this->i_windowsME++;
                        break;
                    case "Windows 98";
                        $this->i_windows98++;
                        break;
                    case "Windows 95";
                        $this->i_windows95++;
                        break;
                    case "Windows 3.11";
                        $this->i_windows311++;
                        break;
                    case "Max OS X";
                        $this->i_macosX++;
                        break;
                    case "Max OS 9";
                        $this->i_macos9++;
                        break;
                    case "Linux";
                        $this->i_linux++;
                        break;
                    case "Ubuntu";
                        $this->i_ubuntu++;
                        break;
                    case "iPhone";
                        $this->i_iPhone++;
                        break;
                    case "iPad";
                        $this->i_iPad++;
                        break;
                    case "iPod";
                        $this->i_iPod++;
                        break;
                    case "Android":
                        $this->i_android++;
                        break;
                    case "BlackBerry";
                        $this->i_blackberry++;
                        break;
                    case "Mobile";
                        $this->i_mobile++;
                        break;

                    // could not detect OS Version
                    default:
                        $this->i_others++;
                }
            }

            // count OS Versions
            $total = $this->i_windows8
                    +$this->i_windows7
                    +$this->i_windowsVista
                    +$this->i_windowsServer
                    +$this->i_windows2000
                    +$this->i_windowsXP
                    +$this->i_windowsME
                    +$this->i_windows98
                    +$this->i_windows95
                    +$this->i_windows311
                    +$this->i_macosX
                    +$this->i_macos9
                    +$this->i_linux
                    +$this->i_ubuntu
                    +$this->i_iPhone
                    +$this->i_iPad
                    +$this->i_iPod
                    +$this->i_android
                    +$this->i_blackberry
                    +$this->i_mobile
                    +$this->i_others;
            // build an array, cointaining the counted OS Versions and the sum overall
            $osVersions = array(
                "Windows 8" => $this->i_windows8,
                "Windows 7" => $this->i_windows7,
                "Windows Vista" => $this->i_windowsVista,
                "Windows Server" => $this->i_windowsServer,
                "Windows 2000" => $this->i_windows2000,
                "Windows XP" => $this->i_windowsXP,
                "Windows ME" => $this->i_windowsME,
                "Windows 98" => $this->i_windows98,
                "Windows 95" => $this->i_windows95,
                "Windows 3.11" => $this->i_windows311,
                "Mac OS X" => $this->i_macosX,
                "Mac OS 9" => $this->i_macos9,
                "Linux" => $this->i_linux,
                "Ubuntu" => $this->i_ubuntu,
                "iPhone" => $this->i_iPhone,
                "iPad" => $this->i_iPad,
                "iPod" => $this->i_iPod,
                "Android" => $this->i_android,
                "Blackberry" => $this->i_blackberry,
                "Mobile" => $this->i_mobile,
                "Unknown" => $this->i_others,
                "Total" => $total
            );

            // return OS data array
            return $osVersions;
        }

        /**
         * Returns an array with all stats, ordered by date_created.
         * @author Daniel Retzl <danielretzl@gmail.com>
         * @version 1.0.0
         * @link http://yawk.io
         * @param object $db Database Object
         * @param string $property
         * @return mixed
         */
        public function getStatsArray($db) // get all settings from db like property
        {
            /* @var $db \YAWK\db */
            if ($res = $db->query("SELECT * FROM {stats} ORDER BY date_created DESC"))
            {
                $statsArray = array();
                while ($row = $res->fetch_assoc())
                {   // fill array
                    $statsArray[] = $row;
                }
            }
            else
            {   // q failed, throw error
                \YAWK\sys::setSyslog($db, 5, "failed to get stats from database.", 0, 0, 0, 0);
                \YAWK\alert::draw("warning", "Warning!", "Fetch database error: getStatsArray failed.","","4800");
                return false;
            }

            $this->calculateStatsFromArray($db, $statsArray);
            return $statsArray;
        }

        public function calculateStatsFromArray($db, $data)
        {   // get stats data
            if (!isset($data) || (empty($data)))
            {
                // get statistics into array
                $data = \YAWK\stats::getStatsArray($db);
            }
            // count and analyze the stats data in a loop
            foreach ($data as $value => $item)
            {
                // count hits
                $this->i_hits++;

                // count how many users were logged in
                if ($item['logged_in'] === "1")
                {
                    $this->i_loggedUsers++;
                }

                // count how many users were guests (or not logged in)
                if ($item['logged_in'] === "0")
                {
                    $this->i_publicUsers++;
                }

                // calculate percentage of guests vs logged in users
                $percentage = 100 / $this->i_hits;
                $this->i_loggedUsersPercentage = $this->i_loggedUsers * $percentage;
                $this->i_publicUsersPercentage = $this->i_publicUsers * $percentage;
                $this->i_loggedUsersPercentage = round($this->i_loggedUsersPercentage, 1, PHP_ROUND_HALF_UP);
                $this->i_publicUsersPercentage = round($this->i_publicUsersPercentage, 1, PHP_ROUND_HALF_UP);

            }

            /*
            echo "Total hits: ".$this->i_hits."<br>";
            echo "davon Phone: ".$this->i_phone."<br>";
            echo "davon Tablet: ".$this->i_tablet."<br>";
            echo "davon Desktop: ".$this->i_desktop." Win: $this->i_osWindows Mac: $this->i_osMac Linux: $this->i_osLinux<br>";
            echo "<pre>";
            print_r($data);
            echo "</pre>";
            */
        }


        function insertData($db)
        {   /* @var $db \YAWK\db */
            if ($db->query("INSERT INTO {stats} 
                                    (uid, 
                                     gid, 
                                     logged_in, 
                                     acceptLanguage, 
                                     remoteAddr, 
                                     userAgent, 
                                     device,  
                                     deviceType, 
                                     os,
                                     osVersion,
                                     browser, 
                                     browserVersion, 
                                     date_created, 
                                     referer, 
                                     page)
                            VALUES ('".$this->uid."', 
                                   '".$this->gid."', 
                                   '".$this->logged_in."', 
                                   '".$this->acceptLanguage."', 
                                   '".$this->remoteAddr."',
                                   '".$this->userAgent."', 
                                   '".$this->device."', 
                                   '".$this->deviceType."', 
                                   '".$this->os."', 
                                   '".$this->osVersion."', 
                                   '".$this->browser."', 
                                   '".$this->browserVersion."', 
                                   '".$this->date_created."', 
                                   '".$this->referer."', 
                                   '".$this->page."')"))
            {
                return true;
            }
            else
                {
                    return false;
                }
        }


        function drawBrowserBox($db, $data, $limit)
        {   /** @var $db \YAWK\db */
            // get data for this box
            $browsers = \YAWK\stats::countBrowsers($db, $data, $limit);

            echo "<!-- donut box:  -->
        <div class=\"box box-default\">
            <div class=\"box-header with-border\">
                <h3 class=\"box-title\">Browser Usage</h3>

                <div class=\"box-tools pull-right\">
                    <button type=\"button\" class=\"btn btn-box-tool\" data-widget=\"collapse\"><i class=\"fa fa-minus\"></i>
                    </button>
                    <button type=\"button\" class=\"btn btn-box-tool\" data-widget=\"remove\"><i class=\"fa fa-times\"></i></button>
                </div>
            </div>
            <!-- /.box-header -->
            <div class=\"box-body\">
                <div class=\"row\">
                    <div class=\"col-md-8\">
                        <div class=\"chart-responsive\">
                            <canvas id=\"pieChartBrowser\" height=\"150\"></canvas>
                        </div>
                        <!-- ./chart-responsive -->
                    </div>
                    <!-- /.col -->
                    <div class=\"col-md-4\">
                        <ul class=\"chart-legend clearfix\">

                            <script> //-------------
                                //- PIE CHART -
                                //-------------

                                // Get context with jQuery - using jQuery's .get() method.
                                var pieChartCanvas = $('#pieChartBrowser').get(0).getContext('2d');
                                var pieChart = new Chart(pieChartCanvas);
                                // get browsers array
                                // output js data with php function getJsonBrowsers
                                var PieData = "; self::getJsonBrowsers($db, $browsers);
                                echo"
                                var pieOptions = {
                                    //Boolean - Whether we should show a stroke on each segment
                                    segmentShowStroke: true,
                                    //String - The colour of each segment stroke
                                    segmentStrokeColor: '#fff',
                                    //Number - The width of each segment stroke
                                    segmentStrokeWidth: 1,
                                    //Number - The percentage of the chart that we cut out of the middle
                                    percentageInnerCutout: 50, // This is 0 for Pie charts
                                    //Number - Amount of animation steps
                                    animationSteps: 100,
                                    //String - Animation easing effect
                                    animationEasing: 'easeOutBounce',
                                    //Boolean - Whether we animate the rotation of the Doughnut
                                    animateRotate: true,
                                    //Boolean - Whether we animate scaling the Doughnut from the centre
                                    animateScale: false,
                                    //Boolean - whether to make the chart responsive to window resizing
                                    responsive: true,
                                    // Boolean - whether to maintain the starting aspect ratio or not when responsive, if set to false, will take up entire container
                                    maintainAspectRatio: false,
                                    //String - A legend template
                                    legendTemplate: '<ul class=\"<%=name.toLowerCase() %>-legend\"><% for (var i=0; i<segments.length; i++){%><li><span style=\"background-color:<%=segments[i].fillColor %>\"></span><%if(segments[i].label){%><%=segments[i].label%><%}%></li><%}%></ul>',
                                    //String - A tooltip template
                                    tooltipTemplate: '<%=value %> <%=label%> users'
                                };
                                //Create pie or douhnut chart
                                // You can switch between pie and douhnut using the method below.
                                pieChart.Doughnut(PieData, pieOptions);
                                //-----------------
                                //- END PIE CHART -
                                //-----------------</script>";

            // walk through array and draw data beneath pie chart
            foreach ($browsers AS $browser => $value)
            {   // get text colors
                $textcolor = self::getBrowserColors($browser);
                // show browsers their value is greater than zero and exclude totals
                if ($value > 0 && ($browser !== "Total"))
                {   // 1 line for every browser
                    echo "<li><i class=\"fa fa-circle-o $textcolor\"></i> <b>$value</b> $browser</li>";
                }
                // show totals
                if ($browser === "Total")
                {   // of how many visits
                    echo "<li class=\"small\">latest $value visitors</li>";
                }
            }
            echo"
                        </ul>
                    </div>
                    <!-- /.col -->
                </div>
                <!-- /.row -->
            </div>
            <!-- /.box-body -->
            <div class=\"box-footer no-padding\">
                <ul class=\"nav nav-pills nav-stacked\">";

            // sort array by value high to low to display most browsers first
            $browsers[] = arsort($browsers);
            // walk through array and display browsers as nav pills
            foreach ($browsers as $browser => $value)
            {   // show only items where browser got a value
                if ($value !== 0 && $browser !== 0)
                {   // get different textcolors
                    $textcolor = self::getBrowserColors($browser);
                    echo "<li><a href=\"#\" class=\"$textcolor\">$browser
        <span class=\"pull-right $textcolor\" ><i class=\"fa fa-angle-down\"></i>$value</span></a></li>";
                }
            }

            echo "</ul>
            </div>
            <!-- /.footer -->
        </div>
        <!-- /.box -->";
        }



        public function drawOsBox($db, $data, $limit)
        {   /** @var $db \YAWK\db */
            // get data for this box
            $oss = \YAWK\stats::countOS($db, $data, $limit);

            echo "<!-- donut box:  -->
        <div class=\"box box-default\">
            <div class=\"box-header with-border\">
                <h3 class=\"box-title\">Operating Systems <small>(experimental)</small></h3>

                <div class=\"box-tools pull-right\">
                    <button type=\"button\" class=\"btn btn-box-tool\" data-widget=\"collapse\"><i class=\"fa fa-minus\"></i>
                    </button>
                    <button type=\"button\" class=\"btn btn-box-tool\" data-widget=\"remove\"><i class=\"fa fa-times\"></i></button>
                </div>
            </div>
            <!-- /.box-header -->
            <div class=\"box-body\">
                <div class=\"row\">
                    <div class=\"col-md-8\">
                        <div class=\"chart-responsive\">
                            <canvas id=\"pieChartOS\" height=\"150\"></canvas>
                        </div>
                        <!-- ./chart-responsive -->
                    </div>
                    <!-- /.col -->
                    <div class=\"col-md-4\">
                        <ul class=\"chart-legend clearfix\">

                            <script> //-------------
                                //- PIE CHART -
                                //-------------

                                // Get context with jQuery - using jQuery's .get() method.
                                var pieChartCanvas = $('#pieChartOS').get(0).getContext('2d');
                                var pieChart = new Chart(pieChartCanvas);
                                // get browsers array
                                // output js data with php function getJsonBrowsers
                                var PieData = "; self::getJsonOS($db, $oss);
                                echo"
                                var pieOptions = {
                                    //Boolean - Whether we should show a stroke on each segment
                                    segmentShowStroke: true,
                                    //String - The colour of each segment stroke
                                    segmentStrokeColor: '#fff',
                                    //Number - The width of each segment stroke
                                    segmentStrokeWidth: 1,
                                    //Number - The percentage of the chart that we cut out of the middle
                                    percentageInnerCutout: 50, // This is 0 for Pie charts
                                    //Number - Amount of animation steps
                                    animationSteps: 100,
                                    //String - Animation easing effect
                                    animationEasing: 'easeOutBounce',
                                    //Boolean - Whether we animate the rotation of the Doughnut
                                    animateRotate: true,
                                    //Boolean - Whether we animate scaling the Doughnut from the centre
                                    animateScale: false,
                                    //Boolean - whether to make the chart responsive to window resizing
                                    responsive: true,
                                    // Boolean - whether to maintain the starting aspect ratio or not when responsive, if set to false, will take up entire container
                                    maintainAspectRatio: false,
                                    //String - A legend template
                                    legendTemplate: '<ul class=\"<%=name.toLowerCase() %>-legend\"><% for (var i=0; i<segments.length; i++){%><li><span style=\"background-color:<%=segments[i].fillColor %>\"></span><%if(segments[i].label){%><%=segments[i].label%><%}%></li><%}%></ul>',
                                    //String - A tooltip template
                                    tooltipTemplate: '<%=value %> <%=label%>'
                                };
                                //Create pie or douhnut chart
                                // You can switch between pie and douhnut using the method below.
                                pieChart.Doughnut(PieData, pieOptions);
                                //-----------------
                                //- END PIE CHART -
                                //-----------------</script>";

            // walk through array and draw data beneath pie chart
            foreach ($oss AS $os => $value)
            {   // get text colors
                $textcolor = self::getOsColors($os);
                // show browsers their value is greater than zero and exclude totals
                if ($value > 0 && ($os !== "Total"))
                {   // 1 line for every browser
                    echo "<li><i class=\"fa fa-circle-o $textcolor\"></i> <b>$value</b> $os</li>";
                }
                // show totals
                if ($os === "Total")
                {   // of how many visits
                    echo "<li class=\"small\">latest $value users</li>";
                }
            }
            echo"
                        </ul>
                    </div>
                    <!-- /.col -->
                </div>
                <!-- /.row -->
            </div>
            <!-- /.box-body -->
            <div class=\"box-footer no-padding\">
                <ul class=\"nav nav-pills nav-stacked\">";

            // sort array by value high to low to display most browsers first
            $oss[] = arsort($oss);
            // walk through array and display browsers as nav pills
            foreach ($oss as $os => $value)
            {   // show only items where browser got a value
                if ($value !== 0 && $os !== 0)
                {   // get different textcolors
                    $textcolor = self::getOsColors($os);
                    echo "<li><a href=\"#\" class=\"$textcolor\">$os
                          <span class=\"pull-right $textcolor\" ><i class=\"fa fa-angle-down\"></i>$value</span></a></li>";
                }
            }

            echo "</ul>
            </div>
            <!-- /.footer -->
        </div>
        <!-- /.box -->";
        }


        public function drawOsVersionBox($db, $data, $limit)
        {   /** @var $db \YAWK\db */
            // get data for this box
            $osVersions = \YAWK\stats::countOSVersions($db, $data, $limit);

            echo "<!-- donut box:  -->
        <div class=\"box box-default\">
            <div class=\"box-header with-border\">
                <h3 class=\"box-title\">OS Versions <small>(experimental)</small></h3>

                <div class=\"box-tools pull-right\">
                    <button type=\"button\" class=\"btn btn-box-tool\" data-widget=\"collapse\"><i class=\"fa fa-minus\"></i>
                    </button>
                    <button type=\"button\" class=\"btn btn-box-tool\" data-widget=\"remove\"><i class=\"fa fa-times\"></i></button>
                </div>
            </div>
            <!-- /.box-header -->
            <div class=\"box-body\">
                <div class=\"row\">
                    <div class=\"col-md-8\">
                        <div class=\"chart-responsive\">
                            <canvas id=\"pieChartOSVersion\" height=\"150\"></canvas>
                        </div>
                        <!-- ./chart-responsive -->
                    </div>
                    <!-- /.col -->
                    <div class=\"col-md-4\">
                        <ul class=\"chart-legend clearfix\">

                            <script> //-------------
                                //- PIE CHART -
                                //-------------

                                // Get context with jQuery - using jQuery's .get() method.
                                var pieChartCanvas = $('#pieChartOSVersion').get(0).getContext('2d');
                                var pieChart = new Chart(pieChartCanvas);
                                // get browsers array
                                // output js data with php function getJsonBrowsers
                                var PieData = "; self::getJsonOSVersions($db, $osVersions);
                                echo"
                                var pieOptions = {
                                    //Boolean - Whether we should show a stroke on each segment
                                    segmentShowStroke: true,
                                    //String - The colour of each segment stroke
                                    segmentStrokeColor: '#fff',
                                    //Number - The width of each segment stroke
                                    segmentStrokeWidth: 1,
                                    //Number - The percentage of the chart that we cut out of the middle
                                    percentageInnerCutout: 50, // This is 0 for Pie charts
                                    //Number - Amount of animation steps
                                    animationSteps: 100,
                                    //String - Animation easing effect
                                    animationEasing: 'easeOutBounce',
                                    //Boolean - Whether we animate the rotation of the Doughnut
                                    animateRotate: true,
                                    //Boolean - Whether we animate scaling the Doughnut from the centre
                                    animateScale: false,
                                    //Boolean - whether to make the chart responsive to window resizing
                                    responsive: true,
                                    // Boolean - whether to maintain the starting aspect ratio or not when responsive, if set to false, will take up entire container
                                    maintainAspectRatio: false,
                                    //String - A legend template
                                    legendTemplate: '<ul class=\"<%=name.toLowerCase() %>-legend\"><% for (var i=0; i<segments.length; i++){%><li><span style=\"background-color:<%=segments[i].fillColor %>\"></span><%if(segments[i].label){%><%=segments[i].label%><%}%></li><%}%></ul>',
                                    //String - A tooltip template
                                    tooltipTemplate: '<%=value %> <%=label%>'
                                };
                                //Create pie or douhnut chart
                                // You can switch between pie and douhnut using the method below.
                                pieChart.Doughnut(PieData, pieOptions);
                                //-----------------
                                //- END PIE CHART -
                                //-----------------</script>";

            // walk through array and draw data beneath pie chart
            foreach ($osVersions AS $osVersion => $value)
            {   // get text colors
                $textcolor = self::getOsVersionsColors($osVersion);
                // show browsers their value is greater than zero and exclude totals
                if ($value > 0 && ($osVersion !== "Total"))
                {   // 1 line for every browser
                    echo "<li><i class=\"fa fa-circle-o $textcolor\"></i> <b>$value</b> $osVersion</li>";
                }
                // show totals
                if ($osVersion === "Total")
                {   // of how many visits
                    echo "<li class=\"small\">latest $value OSes</li>";
                }
            }
            echo"
                        </ul>
                    </div>
                    <!-- /.col -->
                </div>
                <!-- /.row -->
            </div>
            <!-- /.box-body -->
            <div class=\"box-footer no-padding\">
                <ul class=\"nav nav-pills nav-stacked\">";

            // sort array by value high to low to display most browsers first
            $osVersions[] = arsort($osVersions);
            // walk through array and display browsers as nav pills
            foreach ($osVersions as $osVersion => $value)
            {   // show only items where browser got a value
                if ($value !== 0 && $osVersion !== 0)
                {   // get different textcolors
                    $textcolor = self::getOsVersionsColors($osVersion);
                    echo "<li><a href=\"#\" class=\"$textcolor\">$osVersion
                          <span class=\"pull-right $textcolor\" ><i class=\"fa fa-angle-down\"></i>$value</span></a></li>";
                }
            }

            echo "</ul>
            </div>
            <!-- /.footer -->
        </div>
        <!-- /.box -->";
        }

        public function drawDeviceTypeBox($db, $data, $limit)
        {   /** @var $db \YAWK\db */
            // get data for this box
            $deviceTypes = \YAWK\stats::countDeviceTypes($db, $data, $limit);

            echo "<!-- donut box:  -->
        <div class=\"box box-default\">
            <div class=\"box-header with-border\">
                <h3 class=\"box-title\">Device Type <small>(desktop, mobile or tablet)</small></h3>

                <div class=\"box-tools pull-right\">
                    <button type=\"button\" class=\"btn btn-box-tool\" data-widget=\"collapse\"><i class=\"fa fa-minus\"></i>
                    </button>
                    <button type=\"button\" class=\"btn btn-box-tool\" data-widget=\"remove\"><i class=\"fa fa-times\"></i></button>
                </div>
            </div>
            <!-- /.box-header -->
            <div class=\"box-body\">
                <div class=\"row\">
                    <div class=\"col-md-8\">
                        <div class=\"chart-responsive\">
                            <canvas id=\"barChartDeviceType\" height=\"150\"></canvas>
                        </div>
                        <!-- ./chart-responsive -->
                    </div>
                    <!-- /.col -->
                    <div class=\"col-md-4\">
                        <ul class=\"chart-legend clearfix\">

                        <script>    
                            //-------------
                            //- BAR CHART -
                            //-------------
                            
                            var barChartData = {
                              ";
                                    $this->getJsonDeviceTypes($db, $deviceTypes);
                            echo "};
                        
                            var barChartCanvas = $('#barChartDeviceType').get(0).getContext('2d');
                            var barChart = new Chart(barChartCanvas);
                            barChartData.datasets.fillColor = '#00a65a';
                            barChartData.datasets.strokeColor = '#00a65a';
                            barChartData.datasets.pointColor = '#00a65a';
                            var barChartOptions = {
                              //Boolean - Whether the scale should start at zero, or an order of magnitude down from the lowest value
                              scaleBeginAtZero: true,
                              //Boolean - Whether grid lines are shown across the chart
                              scaleShowGridLines: true,
                              //String - Colour of the grid lines
                              scaleGridLineColor: 'rgba(0,0,0,.05)',
                              //Number - Width of the grid lines
                              scaleGridLineWidth: 1,
                              //Boolean - Whether to show horizontal lines (except X axis)
                              scaleShowHorizontalLines: true,
                              //Boolean - Whether to show vertical lines (except Y axis)
                              scaleShowVerticalLines: true,
                              //Boolean - If there is a stroke on each bar
                              barShowStroke: true,
                              //Number - Pixel width of the bar stroke
                              barStrokeWidth: 2,
                              //Number - Spacing between each of the X value sets
                              barValueSpacing: 5,
                              //Number - Spacing between data sets within X values
                              barDatasetSpacing: 1,
                              //String - A legend template
                              legendTemplate: '<ul class=\"<%=name.toLowerCase()%>-legend\"><% for (var i=0; i<datasets.length; i++){%><li><span style=\"background-color:<%=datasets.fillColor %>\"></span><%if(datasets[i].label){%><%=datasets.label%><%}%></li><%}%></ul>',
                              //Boolean - whether to make the chart responsive
                              responsive: true,
                              maintainAspectRatio: true
                            };
                        
                            barChartOptions.datasetFill = false;
                            barChart.Bar(barChartData, barChartOptions);
                        </script>";

            // walk through array and draw data beneath pie chart
            foreach ($deviceTypes AS $deviceType => $value)
            {   // get text colors
                $textcolor = self::getDeviceTypeColors($deviceType);
                // show browsers their value is greater than zero and exclude totals
                if ($value > 0 && ($deviceType !== "Total"))
                {   // 1 line for every browser
                    echo "<li><i class=\"fa fa-circle-o $textcolor\"></i> <b>$value</b> $deviceType</li>";
                }
                // show totals
                if ($deviceType === "Total")
                {   // of how many visits
                    echo "<li class=\"small\">latest $value Users</li>";
                }
            }
            echo"
                        </ul>
                    </div>
                    <!-- /.col -->
                </div>
                <!-- /.row -->
            </div>
            <!-- /.box-body -->
            <div class=\"box-footer no-padding\">
                <ul class=\"nav nav-pills nav-stacked\">";

            // sort array by value high to low to display most browsers first
            $deviceTypes[] = arsort($deviceTypes);
            // walk through array and display browsers as nav pills
            foreach ($deviceTypes as $deviceType => $value)
            {   // show only items where browser got a value
                if ($value !== 0 && $deviceType !== 0)
                {   // get different textcolors
                    $textcolor = self::getDeviceTypeColors($deviceType);
                    echo "<li><a href=\"#\" class=\"$textcolor\">$deviceType
                          <span class=\"pull-right $textcolor\" ><i class=\"fa fa-angle-down\"></i>$value</span></a></li>";
                }
            }

            echo "</ul>
            </div>
            <!-- /.footer -->
        </div>
        <!-- /.box -->";
        }

    }
}