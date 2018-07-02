<?php
namespace YAWK\WIDGETS\FACEBOOK\GALLERY
{
    /**
     * <b>Use Facebook Graph API to get any data from a Facebook Page. Require App ID and Access Token.</b>
     * <p>This is just an empty example widget for development and demo purpose!</p>
     *
     * <p>It is recommended to play around with the facebook graph explorer.
     * You can set any api call and fields you like to play around and explore the resulting array.
     * You can use this widget as base for your own facebook api projects.</p>
     *
     * @package    YAWK
     * @author     Daniel Retzl <danielretzl@gmail.com>
     * @copyright  2018 Daniel Retzl
     * @license    https://opensource.org/licenses/MIT
     * @version    1.0.0
     * @link       http://yawk.io
     * @annotation Facebook Graph API explorer widget - for demo and development purpose only!
     */
    class fbGallery
    {
        /** @var string your app ID (from developers.facebook.com) */
        public $fbGalleryAppId = '';
        /** @var string your page ID (http://facebook.com/{YOURPAGEID} */
        public $fbGalleryPageId = '';
        /** @var string your access token (secret word from developers.facebook.com) */
        public $fbGalleryAccessToken = '';
        /** @var string your graph request */
        public $fbGalleryGraphRequest = '/events/';
        /** @var string fields that should be selected from facebook graph */
        public $fbGalleryFields = 'id,name,description,place,start_time,cover,maybe_count,attending_count,is_canceled';
        /** @var string show events of this time range */
        public $fbGalleryYearRange = '1';
        /** @var string user defined start date */
        public $fbGalleryStartDate = '';
        /** @var string user defined end date */
        public $fbGalleryEndDate = '';
        /** @var string which events should be shown? future|past|all */
        public $fbGalleryType = 'future';
        /** @var string events since this date (used for calc) */
        public $sinceDate = '';
        /** @var string events until this date (used for calc) */
        public $untilDate = '';
        /** @var string true|false was the js SDK loaded? */
        public $jsSDKLoaded = 'false';
        /** @var object api result (as object) */
        public $apiObject;


        public function __construct($db)
        {
            // load this widget settings from db
            $widget = new \YAWK\widget();
            $settings = $widget->getWidgetSettingsArray($db);
            foreach ($settings as $property => $value)
            {
                $this->$property = $value;
            }
            // check if required settings are set
            $this->checkRequirements();
        }

        public function checkRequirements()
        {
            $this->checkAppId();
            $this->checkAccessToken();
            $this->checkPageId();
        }

        public function checkAppId()
        {
            if (isset($this->fbGalleryAppId) && (!empty($this->fbGalleryAppId)))
            {
                if (is_numeric($this->fbGalleryAppId))
                {
                    return true;
                }
                else
                {
                    die ("app ID is set, but not a numeric value! Please check your app ID - it should contain numbers only.");
                }
            }
            else
            {
                die ("app ID is not set. Please add your app ID. You can obtain it from http://developers.facebook.com");
            }
        }

        public function checkAccessToken()
        {
            if (isset($this->fbGalleryAccessToken) && (!empty($this->fbGalleryAccessToken)))
            {
                if (is_string($this->fbGalleryAccessToken))
                {
                    return true;
                }
                else
                {
                    die ("Access token is set, but not a string value! Please check your access token.");
                }
            }
            else
            {
                die ("Access token is not set. Please add your access token. You can obtain it from http://developers.facebook.com");
            }
        }
        public function checkPageId()
        {
            if (isset($this->fbGalleryPageId) && (!empty($this->fbGalleryPageId)))
            {
                if (is_string($this->fbGalleryPageId))
                {
                    return true;
                }
                else
                {
                    die ("Page ID is set, but not a string value! Please check your page ID.");
                }
            }
            else
            {
                die ("Page ID is not set. Please add your page ID. The Page ID is: https://www.facebook.com/{YOURPAGEID}");
            }
        }

        public function loadJSSDK()
        {   // check if fb JS SDK was loaded before
            if ($this->jsSDKLoaded == 'false')
            {   // check if app ID is set
                if ($this->checkAppId() == true)
                {
                    // include facebook SDK JS
                    echo "<script>
                window.fbAsyncInit = function() {
                    FB.init({
                    appId      : '" . $this->fbGalleryAppId . "',
                    xfbml      : true,
                    version    : 'v3.0'
                    });
                FB.AppEvents.logPageView();
                };
                
                (function(d, s, id){
                    var js, fjs = d.getElementsByTagName(s)[0];
                    if (d.getElementById(id)) {return;}
                    js = d.createElement(s); js.id = id;
                    js.src = \"https://connect.facebook.net/en_US/sdk.js\";
                    fjs.parentNode.insertBefore(js, fjs);
                }(document, 'script', 'facebook-jssdk'));
                </script>";
                    $this->jsSDKLoaded = 'true';
                }
                else
                {
                    die ("unable to include facebook js SDK - checkAppId failed. Please check your app ID in the widget settings!");
                }
            }
        }

        public function makeApiCall()
        {
            // WHICH EVENTS TO DISPLAY?
            // evaluation of event type select field
            if ($this->fbGalleryType == "all")
            {
                // ALL EVENTS (FUTURE + PAST)
                $this->sinceDate = date('Y-01-01', strtotime('-' . $this->fbGalleryYearRange . ' years'));
                $this->untilDate = date('Y-01-01', strtotime('+' . $this->fbGalleryYearRange . ' years'));
            }
            elseif ($this->fbGalleryType == "future")
            {
                // UPCOMING EVENTS
                $this->sinceDate = date('Y-m-d');
                $this->untilDate = date('Y-12-31', strtotime('+' . $this->fbGalleryYearRange . ' years'));
            }
            elseif ($this->fbGalleryType == "past")
            {
                // PAST EVENTS
                $this->sinceDate = date('Y-01-01', strtotime('-' . $this->fbGalleryYearRange . ' years'));
                $this->untilDate = date('Y-m-d');
            }
            else
            {   // IF NOT SET - use default:
                // UPCOMING EVENTS
                $this->sinceDate = date('Y-m-d');
                $this->untilDate = date('Y-12-31', strtotime('+' . $this->fbGalleryYearRange . ' years'));
            }

            // IF START + END DATE IS SET
            if (isset($this->fbGalleryStartDate) && (!empty($this->fbGalleryStartDate))
                && (isset($this->fbGalleryEndDate) && (!empty($this->fbGalleryEndDate))))
            {
                $this->sinceDate = date($this->fbGalleryStartDate);
                $this->untilDate = date($this->fbGalleryEndDate);
            }

            // unix timestamp years
            $since_unix_timestamp = strtotime($this->sinceDate);
            $until_unix_timestamp = strtotime($this->untilDate);
            // check if pageID is set
            if (isset($this->fbGalleryPageId) && (!empty($this->fbGalleryPageId)))
            {
                // set markup for pageID string
                $pageIdMarkup = "{$this->fbGalleryPageId}";
            }
            else
            {   // leave empty if no page id is given (to build custom graph string)
                $this->fbGalleryPageId = '';
            }
            // check if fields are set
            if (isset($this->fbGalleryFields) && (!empty($this->fbGalleryFields)))
            {   // set markup for api query string
                $fieldsMarkup = "&fields={$this->fbGalleryFields}";
                if (empty($this->fbGalleryPageId))
                {
                    $fieldsMarkup = "?fields={$this->fbGalleryFields}";
                }
            }
            else
            {   // no fields wanted, leave markup empty
                $fieldsMarkup = '';
            }

            // prepare API call
            // $json_link = "https://graph.facebook.com/v2.7/{$this->fbGalleryPageId}{$this->fbGalleryGraphRequest}?fields={$this->fields}&access_token={$this->fbGalleryAccessToken}";
            $json_link = "https://graph.facebook.com/v3.0/{$this->fbGalleryPageId}{$this->fbGalleryGraphRequest}?access_token={$this->fbGalleryAccessToken}&since={$since_unix_timestamp}&until={$until_unix_timestamp}" . $fieldsMarkup . "";
            // get json string
            $json = file_get_contents($json_link);

            // convert json to object
            return $this->apiObject = json_decode($json, true, 512, JSON_BIGINT_AS_STRING);
        }

        public function checkApiObjectData()
        {
            if (isset($this->apiObject['data']) && (!empty($this->apiObject['data'])))
            {
                return true;
            }
            else
            {
                return false;
            }
        }

        public function printApiObject()
        {
            $this->makeApiCall();
            if ($this->checkApiObjectData() === true)
            {
                echo "<pre>";
                print_r($this);
                echo "</pre>";
            }
            else
            {
                echo "<pre>";
                echo "Could not retrieve any data from Facebook. Please check your PageID, API request, field and date settings";
                echo "</pre>";
                exit;
            }
        }

        public function basicOutput()
        {
            $this->loadJSSDK();
            $this->makeApiCall();

            echo "Basic Output (test data)<hr>";
            foreach ($this->apiObject['data'] as $property => $value) {
                echo "<b>$property </b>: $value<br>";

                foreach ($value as $entry => $key) {

                    echo "$entry : $key<br>";

                    if (is_array($key)) {
                        foreach ($key as $p => $v) {
                            echo "&nbsp;&nbsp;&nbsp;&nbsp;$p : $v<br>";
                            if (is_array($v)) {
                                foreach ($v as $a => $b) {
                                    echo "&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;$a : $b<br>";
                                }
                            }
                        }
                    }
                }
                echo "<br>";
            }
        }

        public function drawGallery()
        {
            $this->loadJSSDK();
            $this->makeApiCall();
            if (isset($this->apiObject['data']) && (!empty($this->apiObject))) {
                echo "<h1>Facebook Photo Albums</h1>";

                foreach ($this->apiObject['data'] as $property => $value)
                {
                    if ($value['name'] != "// Profile Pictures"
                        && ($value['name'] != "// Cover Photos"))
                    {
                        $fn = $value['picture']['data']['url'];
                        echo "<div class=\"col-md-2 text-center\">
                <img src=\"$fn\" style=\"width:200px;\" class=\"img-responsive hvr-grow\"><h3>$value[name] 
                <small><i>($value[count])</i></small></h3><br>
                </div>";


                    }

                    /*
                    foreach ($value['images'] as $photo)
                    {
                        echo "<pre>";
                        echo "<img src=\"".$photo['source']."\" class=\"img-responsive\">";
                        echo "</pre>";
                    }
                    */
                    /*
                                        echo "<br><br>$property : $value<br>";

                                        if (is_array($value)) {

                                            foreach ($value as $entry => $key) {
                                                echo "$entry : $key <br>";
                                                if (is_array($key)) {
                                                    foreach ($key as $image) {
                                                        echo "$key : $image<br>";
                                                        if (is_array($key) || (is_array($image))) {
                                                            foreach ($key as $arrayKey => $arrayValue) {
                                                                echo "&nbsp;&nbsp;$arrayKey : $arrayValue<br>";
                                                                if (is_array($arrayValue)) {
                                                                    foreach ($arrayValue as $p => $v) {
                                                                        echo "&nbsp;&nbsp;$p : $v<br>";
                                                                    }
                                                                }
                                                            }
                                                        }
                                                    }
                                                }
                                            }
                                        }
                    */
                }
            }
        }
    }   // end class events
} // end namespace