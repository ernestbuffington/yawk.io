<?php
namespace YAWK
{

    use YAWK\PLUGINS\MESSAGES\messages;

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
        public $i_publicUsers = 0;

        // os types
        public $i_osWindows = 0;
        public $i_osMac = 0;
        public $i_osLinux = 0;
        public $i_osAndroid = 0;
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
                $browsers = self::countBrowsers($db, 200);
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


        static function countBrowsers($db, $limit)
        {   /* @var $db \YAWK\db */

            // check if limit (i) is set
            if (!isset($limit) || (empty($limit)))
            {   // set default value
                $limit = 100;
            }

            // this vars stores the counting for each browser
            $msie = 0;
            $chrome = 0;
            $firefox = 0;
            $opera = 0;
            $safari = 0;
            $netscape = 0;
            $others = 0;
            $total = 0;

            // get browsers from db
            if ($res = $db->query("SELECT browser FROM {stats} ORDER BY id DESC LIMIT $limit"))
            {   // create array
                $browserlist = array();
                while ($row = mysqli_fetch_assoc($res))
                {   // add to array
                    $browserlist[] = $row;
                    $total++;
                }

                // count browsers
                foreach ($browserlist AS $browser) {   // add +1 for each found
                    switch ($browser['browser']) {
                        case "Google Chrome":
                            $chrome++;
                            break;
                        case "Internet Explorer":
                            $msie++;
                            break;
                        case "Mozilla Firefox":
                            $firefox++;
                            break;
                        case "Apple Safari":
                            $safari++;
                            break;
                        case "Opera":
                            $opera++;
                            break;
                        case "Netscape":
                            $netscape++;
                            break;
                        default:
                            $others++;
                    }
                }
                // build an array, cointaining the browsers and the number how often it's been found
                $browsers = array(
                    "Chrome" => $chrome,
                    "IE" => $msie,
                    "Firefox" => $firefox,
                    "Safari" => $safari,
                    "Opera" => $opera,
                    "Netscape" => $netscape,
                    "Others" => $others,
                    "Total" => $total
                );
                return $browsers;
            }
            else
                {
                    return false;
                }
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
        public static function getStatsArray($db) // get all settings from db like property
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
            return $statsArray;
        }

        public function calculateStats($db)
        {   // get stats data
            $data = \YAWK\stats::getStatsArray($db);
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

                // count Operating Systems
                switch ($item['os'])
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
                    default: $this->i_osUnknown++;
                }

                // count Operating Systems Versions
                switch ($item['osVersion'])
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
                    case "Android";
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

                // count device types
                switch ($item['deviceType'])
                {
                    case "Desktop";
                        $this->i_desktop++;
                        break;
                    case "Tablet";
                        $this->i_tablet++;
                        break;
                    case "Phone";
                        $this->i_phone++;
                        break;
                }


            }
            echo "Total hits: ".$this->i_hits."<br>";
            echo "davon Phone: ".$this->i_phone."<br>";
            echo "davon Tablet: ".$this->i_tablet."<br>";
            echo "davon Desktop: ".$this->i_desktop."<br>";
            echo "<pre>";
            print_r($data);
            echo "</pre>";
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
    }
}