<?php
define('INHBBREPO', 1);

define('MASTERNAME', 'lukegb');
define('MASTERPASS', 'password');

// Okay, set up my global config array
require_once 'config.php';



$locslash = strpos($config['myfilename'],'/');
$locslash = $locslash === FALSE?strlen($config['myfilename']):$locslash;
$action = ereg_replace($config['fnmatch'], "", substr($config['myfilename'],0,$locslash));
$fname = substr($config['myfilename'],$locslash+1);
$end = substr($config['myfilename'], -12);
if (substr($action, 0, 4) == 'list' || (substr($end, 0, 4) == 'list' && substr($end, -4) == '.txt')) {
  $action = 'getlist';
} elseif (substr($config['myfilename'], -8) == 'icon.png') {
  $soft = $action;
  $action = 'geticon';
} elseif (substr($config['myfilename'], -4) == '.zip') {
  $soft = $action;
  $action = 'getzip';
} elseif (substr($config['myfilename'], -4) == '.png') {
  $soft = substr($action,0,-3);
  $action = 'geticon';
}
session_start();
if (!isset($_SESSION['loggedin'])) $_SESSION['loggedin'] = false;
$config['loggedin'] = $_SESSION['loggedin'];
if (($_POST['uname'] == MASTERNAME && $_POST['pass'] == MASTERPASS) && $_POST['loggingin'] == true) {
  $config['loggedin'] = true;
  $_SESSION['loggedin'] = true;
  echo '<style type="text/css">body { color: white; }</style>Log-in successful!<script type="text/javascript">parent.document.location=\''.$config['webroot'].'loginsuccessful\';</script>';
  exit();
} elseif ($_POST['loggingin'] == true) {
  echo '<style type="text/css">body { color: white; } a { color: skyblue; }</style>Log-in failed.<br /><a href="?showlogin=true">Retry</a>';
  exit();
} elseif ($_GET['showlogin']) {
  exit('<style type="text/css">body { color: white; }</style>Please log in:<br /><form action="{$webroot}" method="POST">Username: <input type="text" name="uname" /><br />Password: <input type="password" name="pass" /><br /><input type="submit" name="loggingin" value="Log in" /></form>');
}
if ($_GET['logout']) {
  $config['loggedin'] = false;
  $_SESSION = array();
}
//echo 'ACTION: ', $action;
switch ($action) {
  case 'getlist':
    generatelist();
    break;
  case 'geticon':
    geticon(ereg_replace($config['fnmatch'], "", $soft));
    break;
  case 'getzip':
    getzip(ereg_replace($config['fnmatch'], "", $soft));
    break;
  case 'loginsuccessful':
  default:
    showwebinter($action);
}

function showwebinter($fname) {
  global $config, $templ;
  if ((empty($fname) || !file_exists('zips/'.$fname.'.zip')) && !($fname == 'loginsuccessful' && $config['loggedin'])) { $fname = 'index.html'; }
  $extralinks = '';

  if ($fname == 'index.html') {
    $title = 'Welcome';
    $content = 'The software currently hosted on this repository includes:<br /><br />';
    
    // Okay, here goes......
    $content .= "<table style='width: 100%;'>\r\n<tr>";
    $maxwidth = 3;
    $i = 1;
  
    $apps = scandir('zips');
    foreach ($apps as $file) {
      if ($file != "." && $file != ".." && substr($file,-4)=='.zip') {
        $afname = substr($file,0,-4);
        if ($i > $maxwidth) {
          $i = 1;
          $content .= "</tr>\r\n<tr>";
        }
        $content .= '<td style="text-align: center;width:30%;">';
        $content .= '<a href="{$webroot}'.$afname.'/">';
        $content .= '<img src="{$webroot}'.$afname.'/icon.png" style="border: none;" />';
        $content .= '</a>';
        $content .= '</td>';
        $i++;
      }
    }
    // Make up the rest of the table
    while ($i <= $maxwidth) {
      $content .= '<td style="color:white;width:30%;">yaha!</td>';
      $i++;
    }
    $content .= '</tr></table>';
  } elseif ($fname == 'loginsuccessful') {
    $title = 'Login Successful';
    $content = 'You have been successfully logged in.';
  } else {
    $dbid = getdbid($fname);
    $title = 'About '.getname($dbid).' ('.getversion($dbid).')';
    $content = nl2br(getdescription($dbid, 'long'));
    $content .= '<br /><br />';
    $content .= getname($dbid).' has had '.getdownloads($dbid).' download'.(getdownloads($dbid)!=1 ? 's' : '').', and has an average rating of '.getratings($dbid).' out of 5 stars.';
    $extralinks = '<a href="{$webroot}'.$fname.'/'.$fname.'.zip">Download zip manually</a>';
  } 


  $content .= '<br /><br /><br /><small>You are accessing: '.$fname.($config['loggedin']?' and are logged in!':'').'</small>';
  if ($config['loggedin']) $extralinks .= '<a href="{$webroot}?logout=true">Logout</a>';
  else $extralinks .= '<a href="{$webroot}?showlogin=true" rel="lightbox[inline 360 180]" title="">Login</a>';
//  else $extralinks .= '<a href="#mb_inline" rel="lightbox[inline 360 180]" title="">Login</a>';
  $templ = str_replace('{$title}',$title, $templ);
  $templ = str_replace('{$content}',$content, $templ);
  $templ = str_replace('{$extralinks}',$extralinks,$templ);
  $templ = str_replace('{$webroot}',$config['webroot'], $templ);
  echo $templ;

}

function generatelist() {
  global $config;

  $gentemplate = 'return "$foldername $timestamp $iconsize $dolelfsize $dolelf $dlsize $downloads $rating $controllers $foldercreate $foldernodelete $filenooverwrite
$appname
$author
$version
$foldersize
$shortdesc
$longdesc
";';
  $tehapps = array();

  // Okay, now get the app data
  $apps = scandir('zips');
  foreach ($apps as $file) {
    if ($file != "." && $file != ".." && substr($file,-4)=='.zip') {
      // Okay, seems to be a file in the zips folder
      // Let's assume the REPO manager knows what they're doing
      // The folder name is the zip name without .zip
      $fname = substr($file,0,(strpos($file,'.zip')));
      // Does the folder exist unpacked?
      if (!file_exists('raw/'.$fname)) {
        // Okay, continue
        continue;
      }
      $foldername = $fname;
      $timestamp = filemtime('zips/'.$fname.'.zip');
      if (!file_exists('raw/'.$fname.'/apps/'.$fname.'/boot.dol')) {
        // I r elf!
        $dolelf = 'elf';
      } else {
        $dolelf = 'dol';
      }
      $iconsize = file_exists('raw/'.$fname.'/apps/'.$fname.'/icon.png') ? filesize('raw/'.$fname.'/apps/'.$fname.'/icon.png') : filesize('raw/'.$fname.'/apps/'.$fname.'/'.$fname.'.png');
      $dolelfsize = filesize('raw/'.$fname.'/apps/'.$fname.'/boot.'.$dolelf);
      $dlsize = filesize('zips/'.$fname.'.zip') + filesize('raw/'.$fname.'/apps/'.$fname.'/icon.png');
      $foldersize = getrecursivefoldersize('raw/'.$fname);
      $dbid = (int)getdbid($fname);
      $downloads = getdownloads($dbid);
      $rating = getratings($dbid);
      $controllers = getcontrollers($dbid);
      $foldercreate = getfoldercreate($dbid);
      $foldernodelete = getfoldernodelete($dbid);
      $filenooverwrite = getfilenooverwrite($dbid);
      // OKAY! XML tiem :D
      $appname = getname($dbid);
      $author = getcoder($dbid);
      $version = getversion($dbid);
      $shortdesc = getdescription($dbid, 'short');
      $longdesc = getdescription($dbid, 'long');
      $category = getcategory($dbid);

      // Truncate short desc
      $shortdesc = substr($shortdesc,0,30);
      if (empty($shortdesc)) {
        $shortdesc = 'No description available';
      }
      // Truncate long desc
      $longdesc = explode("\n",$longdesc);
      if (!empty($longdesc[0])) {
        $longdesc = $longdesc[0];
      } elseif (!empty($longdesc[1])) {
        $longdesc = $longdesc[1];
      } else {
        $longdesc = $shortdesc;
      }
      $tehapps[$category][$fname] = eval($gentemplate);
    }
  }
  
//print_r($tehapps);
  echo 'Homebrew 0 0 .'."\r\n";
  $reqcats = array('Games','Emulators','Media','Utilities','Demos');
  foreach ($reqcats as $catname) {
    $catinside = isset($tehapps[$catname]) ? $tehapps[$catname] : array();
    foreach ($catinside as $appdets) {
      echo $appdets;
    }
    echo '='.$catname.'='."\r\n";
  }
}

function getrecursivefoldersize($foldname) {
  return dorecfoldersize(''.$foldname.'/');
}

function dorecfoldersize($fn) {
  $a = scandir($fn);
  $sz = 0;
  foreach ($a as $n) {
    if ($n != '.' && $n != '..') {
      if (is_dir($n)) {
        $sz += dorecfoldersize($fn.'/'.$n);
      } else {
        $sz += filesize($fn.'/'.$n);
      }
    }
  }
  return $sz;
}

function getdbid($fname) {
  global $config;
  $sql = 'SELECT * FROM '.$config['dbprefix'].'software WHERE fname="'.$config['dbcon']->real_escape_string($fname).'"';
  $d = $config['dbcon']->query($sql);
  if ($d->num_rows == 0) {
    $sql = 'INSERT INTO '.$config['dbprefix'].'software (fname, downloads, rating, controllers, folders_create, folders_no_delete, files_no_overwrite, category) VALUES ("'.$config['dbcon']->real_escape_string($fname).'", 0, 0, "w", "", "", "", "Utilities")';
//    echo $sql;exit();
    $config['dbcon']->query($sql);
    return getdbid($fname);
  }
  $a = $d->fetch_assoc();
  $config['dbdetails'][$a['hb_id']] = $a;

  $getfromxml = array('name','coder','version','short_description','long_description');
  $sxmlobj = simplexml_load_file('raw/'.$fname.'/apps/'.$fname.'/meta.xml');
  foreach ($getfromxml as $what) {
    $config['dbdetails'][$a['hb_id']][$what] = $sxmlobj->$what;
  } 


  return $a['hb_id'];
}

function getname($dbid) {
  global $config;
  return $config['dbdetails'][$dbid]['name'];
}

function getcoder($dbid) {
  global $config;
  return $config['dbdetails'][$dbid]['coder'];
}

function getversion($dbid) {
  global $config;
  return $config['dbdetails'][$dbid]['version'];
}

function getdescription($dbid, $type = 'short') {
  global $config;
  if ($type == 'long')
    return $config['dbdetails'][$dbid]['long_description'];
  else
    return $config['dbdetails'][$dbid]['short_description'];
}




function getdownloads($dbid) {
  global $config;
  return $config['dbdetails'][$dbid]['downloads'];
}

function incrdownloads($fname) {
  global $config;
  return $config['dbcon']->query('UPDATE '.$config['dbprefix'].'software SET downloads=downloads+1 WHERE fname="'.$config['dbcon']->real_escape_string($fname).'"');
}

function getratings($dbid) {
  global $config;
  return $config['dbdetails'][$dbid]['rating'];
}

function getcontrollers($dbid) {
  global $config;
  return $config['dbdetails'][$dbid]['controllers'];
}

function getfoldercreate($dbid) {
  global $config;
  return empty($config['dbdetails'][$dbid]['folders_create']) ? '.' : $config['dbdetails'][$dbid]['folders_create'];
}

function getfoldernodelete($dbid) {
  global $config;
  return empty($config['dbdetails'][$dbid]['folders_no_delete']) ? '.' : $config['dbdetails'][$dbid]['folders_no_delete'];
}

function getfilenooverwrite($dbid) {
  global $config;
  return empty($config['dbdetails'][$dbid]['files_no_overwrite']) ? '.' : $config['dbdetails'][$dbid]['files_no_overwrite'];
}

function getcategory($dbid) {
  global $config;
  return $config['dbdetails'][$dbid]['category'];
}

function geticon($fname) {
  header('Content-type: icon/png');
  readfile('raw/'.$fname.'/apps/'.$fname.'/icon.png');
}

function getzip($fname) {
  header('Content-type: application/zip');
  incrdownloads($fname);
  readfile('zips/'.$fname.'.zip');
}
?>
