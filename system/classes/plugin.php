<?php
namespace YAWK {
    /**
     * <b>Plugin class: handles some basic plugin functions</b>
     *
     * Handles plugin functions of YaWK. Plugins are bigger extensions than widgets.
     * A plugin can handle any data from a mysql database. This is useful if your project
     * needs more than just static pages, like a bulletin board, shop system, blog and so on.
     * There are many Plugins done yet.
     * See how the plugin system is organized:<ul>
     * <li>system/plugins/pluginname/ = <strong>Plugin folder</strong></li>
     * <li>system/plugins/pluginname/admin = <strong>Plugin Backend (view)  </strong></li>
     * <li>system/plugins/pluginname/classes = <strong>Plugin logic (controller) </strong></li>
     * <li>system/plugins/pluginname/pluginname.php = <strong>Plugin Frontend (view)</strong></li></ul>
     * <br>
     * If you need to build a custom extension, read the docs about the plugin system to understand how to
     * fit it into YaWK. If you follow the Plugin's MVC structure, it is easy to integrate your own
     * extensions into any project.
     *
     * <i>Example:</i>
     * <code><?php \YAWK\plugin::getPlugins(); ?></code>
     * gets a list of all plugins, like its used in the backend in "Plugin" Menu.
     * <p><i>This class covers backend functionality.
     * See Methods Summary for Details!</i></p>
     *
     * @package    YAWK
     * @author     Daniel Retzl <danielretzl@gmail.com>
     * @copyright  2009-2016 Daniel Retzl
     * @license    http://www.gnu.org/licenses/gpl-2.0  GNU/GPL 2.0
     * @version    1.0.0
     * @link       http://yawk.io
     * @annotation Handles the Plugin System.
     */
    class plugin
    {
        /** * @var int plugin ID */
        public $id;
        /** * @var string plugin name*/
        public $name;
        /** * @var string plugin description */
        public $description;
        /** * @var string plugin font awesome or glyph icon */
        public $icon;
        /** * @var int plugin status ID */
        public $activated;

        /**
         * plugin constructor.
         */
        function __construct()
        {
            // ...
        }

        /**
         * get and draw a list of all active plugins
         * @author     Daniel Retzl <danielretzl@gmail.com>
         * @copyright  2009-2016 Daniel Retzl
         * @license    http://www.gnu.org/licenses/gpl-2.0  GNU/GPL 2.0
         * @version    1.0.0
         * @param object $db database
         * @param array $lang language array
         * @param int $manage 0|1 if manage is 1, only active plugins will be shown
         * @return null|string html output
         */
        function getPlugins($db, $lang, $manage)
        {   /** @var $db \YAWK\db */
            if (isset($manage) && ($manage == 1))
            {
                $sqlcode = "WHERE activated='1'";
            }
            else
            {
                $sqlcode = "";
            }

            if (!$res = $db->query("SELECT * FROM {plugins} $sqlcode ORDER by name"))
            {
                \YAWK\sys::setSyslog($db, 5, "failed to select plugin ", 0, 0, 0, 0);
                print \YAWK\alert::draw("danger", "Fehler:", "Es tut mir leid, die Plugins konnten nicht aus der Datenbank abgerufen werden.", "","");
                $html = null;
            }
            else
            {
                /* TABLE HEADER */
                $html = '';
                /* TABLE CONTENT */
                while ($row = mysqli_fetch_array($res))
                {
                    $this->id = $row[0];
                    $this->name = $row[1];
                    $this->description = $row[2];
                    $this->icon = $row[3];
                    $this->activated = $row[4];


                    // set plugin folder
                    $folder = "../system/plugins/" . $this->name;
                    // & check it - only show data
                    if (file_exists($folder) === true)
                    {
                        $html .= "<tr>
                    <td style=\"text-align: center;\"><a href=\"index.php?plugin=$this->name\"><i class=\"$this->icon fa-4x\"></i></a></td>
                    <td><br><a href=\"index.php?plugin=$this->name\"><div>$this->name</div></a></td>
                    <td><br>$this->description</td>
                    <td style=\"text-align: center;\"><br>
                        <a class=\"fa fa-edit\" title=\"" . $lang['EDIT'] . "\" href=\"index.php?plugin=$this->name\"></a>&nbsp;
                    </td>
                  </tr>";
                    }
                    else
                    {
                        $name = ucfirst($this->name);
                        echo \YAWK\alert::draw("warning", "Fehler!", "Plugin: <b>\"$name\"</b> ist zwar registriert, aber offensichtlich nicht korrekt installiert. Ist der folder <b>system/plugins/$this->name/</b> &uuml;berhaupt vorhanden?","","4800");
                    }
                }
            }
            return $html;
            /* EOFunction getPlugins */
        }

        /**
         * get requested plugin name for given plugin ID
         * @author     Daniel Retzl <danielretzl@gmail.com>
         * @copyright  2009-2016 Daniel Retzl
         * @license    http://www.gnu.org/licenses/gpl-2.0  GNU/GPL 2.0
         * @version    1.0.0
         * @param object $db database
         * @param int $pluginId affected plugin ID
         * @return string|bool returns the name or false
         */
        static function getNameById($db, $pluginId)
        {   /** @var $db \YAWK\db $res */
            if ($res = $db->query("SELECT name FROM {plugins} WHERE id = '" . $pluginId . "'"))
            {   // fetch data from db
                while ($row = mysqli_fetch_row($res))
                {   // return plugin name
                    return $row['0'];
                }
            }
            else
            {   // q failed
                \YAWK\sys::setSyslog($db, 5, "failed to get name of plugin $pluginId ", 0, 0, 0, 0);
                return false;
            }
            // something else happened
            return false;
        }

        /**
         * get requested plugin ID by given name
         * @author     Daniel Retzl <danielretzl@gmail.com>
         * @copyright  2009-2016 Daniel Retzl
         * @license    http://www.gnu.org/licenses/gpl-2.0  GNU/GPL 2.0
         * @version    1.0.0
         * @param object $db database
         * @param string $plugin plugin name
         * @return string|bool returns the plugin ID or false
         */
        static function getIdByName($db, $plugin)
        {   /** @var $db \YAWK\db $res */
            if ($res = $db->query("SELECT id FROM {plugins} WHERE name ='".$plugin."'"))
            {   // fetch data from db
                while ($row = mysqli_fetch_row($res))
                {   // return plugin ID
                    return $row['0'];
                }
            }
            else
            {   // q failed, throw error
                \YAWK\sys::setSyslog($db, 5, "failed to get id of plugin $pluginId ", 0, 0, 0, 0);
                return \YAWK\alert::draw("danger", "Error!", "Could not get id of plugin: ".$plugin."","page=plugins","4800");
            }
            return false;
        }

        /**
         * create a static page wich includes the according plugin content
         * @author     Daniel Retzl <danielretzl@gmail.com>
         * @copyright  2009-2016 Daniel Retzl
         * @license    http://www.gnu.org/licenses/gpl-2.0  GNU/GPL 2.0
         * @version    1.0.0
         * @param object $db database
         * @param string $alias filename of the static page
         * @param int $plugin plugin ID
         * @return bool
         */
        static function createPluginPage($db, $alias, $plugin)
        {   /** @var $db \YAWK\db */
        if (!isset($alias)){ $alias = ''; }
        if (!isset($plugin)){ $alias = ''; }
            if (!file_exists("../content/pages/$alias.php"))
            {   // frontend plugin file not found
                if (!isset($page))
                {   // check if page obj exists
                    $page = new \YAWK\page();
                }
                // create new frontend plugin page
                if ($page->create($db, $alias, 1, 0, 0, $plugin))
                {   // all good
                    \YAWK\sys::setSyslog($db, 9, "created page $alias for plugin $plugin", 0, 0, 0, 0);
                    return true;
                }
                else
                {   // could not create page
                    \YAWK\sys::setSyslog($db, 5, "failed to create page $alias for plugin $plugin", 0, 0, 0, 0);
                    return false;
                }
            }
            else
            {   // q failed
                return true;
            }
        }

    } // ./ class plugin
} // ./ namespace