<?php
namespace YAWK\WIDGETS\GALLERY\IMAGES
{
    /**
     * <b>Embed plugin: photo gallery</b>
     *
     * <p>This is the widget to the gallery plugin. With this widget you can embed image galleries
     * that you've created before using the gallery plugin. Simply set the ID of the photo gallery you
     * would like to embed. </p>
     *
     * @package    YAWK
     * @author     Daniel Retzl <danielretzl@gmail.com>
     * @copyright  2018 Daniel Retzl
     * @license    https://opensource.org/licenses/MIT
     * @version    1.0.0
     * @link       http://yawk.io
     * @annotation Embed a photo gallery on your website.
     */
    class gallery extends \YAWK\widget
    {
        /** @var object global widget object data */
        public $widget = '';
        /** @var int Gallery ID */
        public $galleryID = '';
        /** @var string Title that will be shown above widget */
        public $galleryHeading = '';
        /** @var string Subtext will be displayed beside title */
        public $gallerySubtext = '';

        /** @var int Image Item ID */
        public $itemID = '';
        /** @var string Image filename */
        public $filename = '';
        /** @var string Image Description */
        public $itemTitle = '';
        /** @var string Image Author (Copyright Notice) */
        public $itemAuthor = '';
        /** @var string Image Author URL */
        public $itemAuthorUrl = '';
        /** @var string Headline above widget */
        public $headline = '';

        /** @var bool galleryNavOnTouchDevices If true, the left and right navigation arrows which appear on mouse hover when viewing image sets will always be visible on devices which support touch. */
        public $galleryNavOnTouchDevices = false;
        /** @var string The text displayed below the caption when viewing an image set. The default text shows the current image number and the total number of images in the set. */
        public $galleryAlbumLabel = 'Image %1 of %2';
        /** @var bool If true, prevent the page from scrolling while Lightbox is open. This works by settings overflow hidden on the body. */
        public $galleryDisableScrolling = false;
        /** @var string The time it takes for the Lightbox container and overlay to fade in and out, in milliseconds. */
        public $galleryFadeDuration = '600';
        /** @var bool If true, resize images that would extend outside of the viewport so they fit neatly inside of it. This saves the user from having to scroll to see the entire image. */
        public $galleryFitImagesInViewport = true;
        /** @var string The time it takes for the image to fade in once loaded, in milliseconds. */
        public $galleryImageFadeDuration = '600';
        /** @var string If set, the image width will be limited to this number, in pixels. Aspect ratio will not be maintained. */
        public $galleryMaxWidth = '';
        /** @var string If set, the image height will be limited to this number, in pixels. Aspect ratio will not be maintained. */
        public $galleryMaxHeight = '';
        /** @var string If set, the thumbnail image width will be limited to this number, in pixels. Aspect ratio will not be maintained. */
        public $galleryTnWidth = '';
        /** @var string If set, the thumbnail image height will be limited to this number, in pixels. Aspect ratio will not be maintained. */
        public $galleryTnHeight = '';
        /** @var string The distance from top of viewport that the Lightbox container will appear, in pixels */
        public $galleryPositionFromTop = '50';
        /** @var string The time it takes for the Lightbox container to animate its width and height when transition between different size images, in milliseconds. */
        public $galleryResizeDuration = '700';
        /** @var bool If false, the text indicating the current image number and the total number of images in set (Ex. "image 2 of 4") will be hidden. */
        public $galleryShowImageNumberLabel = true;
        /** @var bool If true, when a user reaches the last image in a set, the right navigation arrow will appear and they will be to continue moving forward which will take them back to the first image in the set. */
        public $galleryWrapAround = false;

        /** @var int How many pictures should be drawn per row? */
        public $galleryLayoutRows = 3;
        /** @var bool true|false Shuffle Images */
        public $galleryShuffle = false;

        /** @var string <img Width HTML Markup */
        public $galleryWidthMarkup = '';
        /** @var string Image Class */
        public $galleryImageClass = 'img-responsive img-rounded hvr-grow';


        /**
         * Load all widget settings from database and fill object
         * @author Daniel Retzl <danielretzl@gmail.com>
         * @version 1.0.0
         * @link http://yawk.io
         * @param object $db Database Object
         * @annotation Load all widget settings on object init.
         */
        public function __construct($db)
        {
            // load this widget settings from db
            $this->widget = new \YAWK\widget();
            $settings = $this->widget->getWidgetSettingsArray($db);
            foreach ($settings as $property => $value) {
                $this->$property = $value;
            }
        }

        /**
         * Print all object data
         * @author Daniel Retzl <danielretzl@gmail.com>
         * @version 1.0.0
         * @link http://yawk.io
         * @annotation (for development and testing purpose)
         */
        public function printObject()
        {
            echo "<pre>";
            print_r($this);
            echo "</pre>";
        }

        /**
         * Init Gallery and load methods
         * @author Daniel Retzl <danielretzl@gmail.com>
         * @version 1.0.0
         * @link http://yawk.io
         * @annotation load Javascript and draw image gallery
         */
        public function init($db)
        {
            $this->loadJavascript();
            $this->drawImageGallery($db);
        }

        /**
         * Check if lightbox asset is loaded and set lightbox options
         * @author Daniel Retzl <danielretzl@gmail.com>
         * @version 1.0.0
         * @link http://yawk.io
         * @annotation check lightbox asset and set options
         */
        public function loadJavascript()
        {
            // check if gallery max width is set
            if (isset($this->galleryMaxWidth) && (empty($this->galleryMaxWidth)))
            {   // nope, no markup needed
                $maxWidthMarkup = '';
            }
            else
                {   // set gallery markup
                    $maxWidthMarkup = "
                    'maxWidth': $this->galleryMaxWidth,";
                }
            echo "
            <script type=\"text/javascript\">
                lightbox.option({
                    'alwaysShowNavOnTouchDevices': $this->galleryNavOnTouchDevices,
                    'albumLabel': \"$this->galleryAlbumLabel\",
                    'disableScrolling': $this->galleryDisableScrolling,
                    'fadeDuration': $this->galleryFadeDuration,
                    'fitImagesInViewport': $this->galleryFitImagesInViewport,
                    'imageFadeDuration': $this->galleryImageFadeDuration,$maxWidthMarkup
                    'positionFromTop': $this->galleryPositionFromTop,
                    'resizeDuration': $this->galleryResizeDuration,
                    'showImageNumberLabel': $this->galleryShowImageNumberLabel,
                    'wrapAround': $this->galleryWrapAround
                });
            </script>";
        }


        /**
         * Draw the gallery
         * @author Daniel Retzl <danielretzl@gmail.com>
         * @version 1.0.0
         * @link http://yawk.io
         * @param db object Database object
         * @annotation (for development and testing purpose)
         */
        public function drawImageGallery($db)
        {   /** @var $db \YAWK\db **/
            if (!isset($this->galleryID) || (empty($this->galleryID)))
            {
                echo "Error: unable to load photo gallery because there is no gallery selected.";
            }
            if ($res = $db->query("SELECT folder from {plugin_gallery} WHERE id = '".$this->galleryID."'"))
            {
                while ($row = mysqli_fetch_assoc($res))
                {   // check if shuffle is enabled
                    if (isset($this->galleryShuffle) && ($this->galleryShuffle === 'true'))
                    {   // randomize pictures
                        $order = "ORDER BY RAND()";
                    }
                    else
                        {   // order by sortation
                            $order = "ORDER BY sort ASC";
                        }
                    if (!$getPreviewImages = $db->query("SELECT id, galleryID, sort, filename, title, author, authorUrl
                                                         from {plugin_gallery_items} 
                                                         WHERE galleryID = '".$this->galleryID."' $order"))
                    {   // store info msg, if files could not be retrieved
                        $previewError = "Sorry, could not get preview images";
                    }
                    if (isset($previewError))
                    {   // if files could not be loaded from db
                        echo $previewError;
                    }
                    else
                    {
                        // prepare loop vars
                        $count = 0;
                        $divider = 3;
                        /** @var $widget \YAWK\widget */
                        // get headline
                        $this->headline = $this->getHeading($this->galleryHeading, $this->gallerySubtext);
                        // draw headline
                        echo $this->headline;
                        echo '<div class="container-fluid">
                            <div class="row text-center">';

                        // check gallery layout (how many cols per row)
                        if ($this->galleryLayoutRows === "1")
                        {
                            $col = "col-md-12";
                            $divider = 1;
                        }
                        elseif ($this->galleryLayoutRows === "2")
                        {
                            $col = "col-md-6";
                            $divider = 2;
                        }
                        elseif ($this->galleryLayoutRows === "3")
                        {
                            $col = "col-md-4";
                            $divider = 3;
                        }
                        elseif ($this->galleryLayoutRows === "4")
                        {
                            $col = "col-md-3";
                            $divider = 4;
                        }
                        elseif ($this->galleryLayoutRows === "6")
                        {
                            $col = "col-md-2";
                            $divider = 6;
                        }
                        elseif ($this->galleryLayoutRows === "12")
                        {
                            $col = "col-md-1";
                            $divider = 12;
                        }
                        else
                            {
                                $col = "col-md-4";
                            }

                        // check thumbnail width
                        if (isset($this->galleryTnWidth) && (!empty($this->galleryTnWidth)))
                        {   // set tn width markup
                            $this->galleryWidthMarkup = "width=\"$this->galleryTnWidth\" ";
                        }
                        else
                            {   // no width markup needed
                                $this->galleryWidthMarkup = '';
                            }
                        
                        foreach ($getPreviewImages as $property => $image)
                        {   // display preview images
                            for ($i = 0; $i < count($property); $i++)
                            {
                                $this->itemID = $image['id'];
                                $this->sort = $image['sort'];
                                $this->filename = $image['filename'];
                                $this->itemTitle = $image['title'];
                                $this->itemAuthor = $image['author'];
                                $this->itemAuthorUrl = $image['authorUrl'];
                                // $rnd = uniqid();

                                echo "
                                <div class=\"$col text-center\" id=\"imgCol-".$this->itemID."\">
                                    <a href=\"$row[folder]/$this->filename\" data-lightbox=\"$this->galleryID\" data-title=\"$this->itemTitle\"><img ".$this->galleryWidthMarkup."class=\"$this->galleryImageClass\" id=\"img-$this->itemID\" title=\"$this->itemTitle\" src=\"$row[folder]/$this->filename\"></a><br><br>
                                </div>
                                      ";

                                $count++;

                                // close tag, but never before the first run
                                if ($count > 1)
                                {
                                    if($count % $divider == 0) echo '
                            </div> <!-- end row -->
                                
                                <div class="row text-center">';

                                }
                            }
                        }
                        echo "</div></div>";
                    }
                }
            }
        }
    }
}
?>