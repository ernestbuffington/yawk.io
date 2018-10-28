<?php
namespace YAWK\FRAMEWORK\BOOTSTRAP4
{
    /**
     * <b>Bootstrap 4 CSS methods</b>
     * <p>Extends \YAWK\bootstrap - this class serves all bootstrap 4 component methods
     * and set this->cssCode variable that will be used by \YAWK\bootstrap </p>
     *
     * @author     Daniel Retzl <danielretzl@gmail.com>
     * @copyright  2009-2015 Daniel Retzl yawk.io
     * @license    https://opensource.org/licenses/MIT
     * @version    1.0.0
     * @link       http://yawk.io
     * @annotation Helper function to output custom (overriden) bootstrap 4 css (settings.css)
     */
    class bootstrap4 extends \YAWK\FRAMEWORK\cssFramework
    {
        /** @var string all the css as string */
        public $cssCode = '';

        public function init()
        {
            // set css tags for Bootstrap 4
            $this->bs4_Cards();

            // check if css code is set, not empty and correct type
            if (isset($this->cssCode) && (is_string($this->cssCode) && (!empty($this->cssCode))))
            {   // all good, return all generated code
                return $this->cssCode;
            }
            else
            {   // css code is not set properly
                return null;
            }
        }

        /**
         * Bootstrap 4: CARDS Component CSS Code
         * @author Daniel Retzl <danielretzl@gmail.com>
         * @version 1.0.0
         * @link http://yawk.io
         * @annotation add bootstrap 4 cards component to this css code string
         */
        public function bs4_Cards()
        {
            // cards css code
            $this->cssCode = "    
            /* CARDS */
            /* .... */
            ";
        }
    }
}