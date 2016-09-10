<?PHP
if (!isset($widget))
{   // create new widget object
    $widget = new \YAWK\widget();
}
// load properties for given widget
if (isset($_GET['widget']) && (!empty($_GET['widget'])) && (isset($_GET['published']) && (!empty($_GET['published']))))
{
    // if widget is set
    $_GET['widget'] = $db->quote($_GET['widget']);
    $_GET['published'] = $db->quote($_GET['published']);
    //    $widget->loadProperties($db, $_GET['widget']);

}
// set published status
if ($_GET['published'] === '1')
{   // if 1, set to zero
	$widget->published = 0;
}
else
{   // else set to one
    $widget->published = 1;
}
// now toggle it
if($widget->toggleOffline($db, $_GET['widget'], $widget->published))
  {   // all good, redirect to widget overview
      \YAWK\backend::setTimeout("index.php?page=widgets",0);
  }
  else
  {   // q failed, throw error
      print \YAWK\alert::draw("danger", "Error", "Could not toggle widget status.", "page=widgets","4800");
  }
