<?php
/**
 * <b>Index.php - the main entry point (controller) of the website's frontend</b>
 *
 * First of all, the session gets started, error reporting is set, followed by
 * including all required classes (because its faster than SPLautoload).
 * Afterwards, a handful of objects will be created:
 * <ul>
 * <li>\YAWK\db</li>
 * <li>\YAWK\template</li>
 * <li>\YAWK\user</li>
 * <li>\YAWK\page</li>
 * <li>\YAWK\controller</li>
 * <li>\YAWK\stats</li>
 * </ul>
 * <p>Those objects are holding all data used by the frontend. Additional
 * you can use any of that many static methods from other classes. See class
 * overview for details about how YaWK is organized.</p>
 *
 * @category   CMS
 * @package    System
 * @author     Daniel Retzl <danielretzl@gmail.com>
 * @copyright  2016 Daniel Retzl http://yawk.website
 * @license    http://www.gnu.org/licenses/gpl-3.0  GNU/GPL 3.0
 * @version    1.0.0
 * @link       http://yawk.website
 * @since      File available since Release 1.0.0
 * @annotation Index.php - the main entry point (controller) of the website's frontend
 *
 */
session_start();
/* Error Reporting - this is for DEVELOPMENT PURPOSE ONLY! */
error_reporting(E_ALL ^ E_STRICT);
ini_set('display_errors', 1);
error_reporting(1);
/* include core files */
require_once('system/classes/db.php');               // database connection
require_once('system/classes/settings.php');         // get/set settings from settings db
require_once('system/classes/alert.php');            // draw fancy JS-notification alert class
require_once('system/classes/email.php');            // email functions
require_once('system/classes/user.php');             // all get/set/handle user functions
require_once('system/classes/page.php');             // all get/set/handle page functions
require_once('system/classes/menu.php');             // all get/set/handle menu functions
require_once('system/classes/widget.php');           // all get/set/handle widget functions
require_once('system/classes/template.php');         // all template functions, including get/set template settings
require_once('system/classes/sys.php');              // basic i/o and helper functions
require_once('system/classes/controller.php');       // frontEnd init and filename filter controller
require_once('system/classes/stats.php');            // statistics functions
/* set database object */
if (!isset($db)) {
    $db = new \YAWK\db();
}
/* set template object */
if (!isset($template)) {
    $template = new \YAWK\template();
}
/* set user object */
if (!isset($user)) {
    $user = new \YAWK\user();
}
/* set page object */
if (!isset($page)) {
    $currentpage = new \YAWK\page();
}
/* set controller object */
if (!isset($controller)) {
    $controller = new \YAWK\controller();
}
/* set stats object */
if (!isset($stats)) {
    $stats = new \YAWK\stats();
    $stats->setStats($db);
}
// lets go with the frontEnd...
// \YAWK\controller::frontEndInit($db, $currentpage, $user, $template);
if (\YAWK\sys::isOffline($db)) {   // backend-users (admins) can see the frontend,
    // while the site is still offline to guests & no-admins
    \YAWK\sys::drawOfflineMessage($db);
    exit;
}
// check if user wants to register (signUp)
if (isset($_GET['signup']) && ($_GET['signup']) == 1) {
    include('system/plugins/signup/classes/signup.php');
    $signup = new \YAWK\PLUGINS\SIGNUP\signup();
    echo $signup->sayHello($db);
}
// URL controller - this loads the properties of each page */
if (isset($_GET['include']) && (!empty($_GET['include'])))
{
    // LOGOUT SENT VIA GET (yourdomain.com/logout)
    if ($_GET['include'] === "logout")
    {   // start logout procedure
        if ($user->logout($db) === true)
        {   // redirect user to index page
            \YAWK\sys::setTimeout("index.html", 0);
            exit;
        }
    }
    // user filled out login form
    if (isset($_POST['login']))
    {   // check given vars
        if (isset($_POST['user']) && (isset($_POST['password'])))
        {
            if ($user->login($db, $_POST['user'], $_POST['password']) === true)
            {
                $_GET['include'] = "index";
            }
        }
    }
    // URL is set and not empty - lets go, load properties for given page
    $currentpage->loadProperties($db, $db->quote($_GET['include']));

    // different GET controller actions can be done here...
}
else
{   // if no page is given, set index as default page
    $_GET['include'] = "index";
    // and load properties for it
    $currentpage->loadProperties($db, $db->quote($_GET['include']));
}

// get global selected template ID
$selectedTemplate = \YAWK\settings::getSetting($db, "selectedTemplate");
// call template controller
if (\YAWK\user::isAnybodyThere())
{   // user seems to be logged in...
    // load template name from {users}
    $user->loadProperties($db, $_SESSION['username']);
    // check if user is allowed to overrule selectedTemplate
    if ($user->overrideTemplate == true)
    {   // set user template ID to session
        $_SESSION['userTemplateID'] = $user->templateID;
        // get template by user templateID
        $templateName = \YAWK\template::getTemplateNameById($db, $user->templateID);
        // include page, based on user templateID
        if(!@include("system/templates/$templateName/index.php"))
        {   // if template not exists, show selectedTemplate
            $templateName = \YAWK\template::getTemplateNameById($db, $selectedTemplate);
            include("system/templates/$templateName/index.php");
        }
    }
    else
        {   // user is not allowed to overrule template, show global default (selectedTemplate) instead.
            $templateName = \YAWK\template::getTemplateNameById($db, $selectedTemplate);
            if(!include("system/templates/$templateName/index.php"))
            {
                die("Unable to include template. Either database config is faulty or YaWK is not correctly installed.");
            }


        }
}
else
    {   // user is NOT logged in, load default template (selectedTemplate) from settings db
        $templateName = \YAWK\template::getTemplateNameById($db, $selectedTemplate);
        if(!include("system/templates/$templateName/index.php"))
        {
            die("Unable to include template. Either database config is faulty or YaWK is not correctly installed.");
        }
    }
