<?php

if (!defined('INHBBREPO')) {
  die('Hacking attempt?');
}

$config = array(
  'webroot' => '/hbb/', # This is the folder that this software APPEARS to be in, from web root.
);


$config['dbusername'] = 'hbb';
$config['dbpassword'] = 'homebrewbrowser'; // Note, this will give you read-only access to my repository
$config['dbhost'] = 'localhost';
$config['dbname'] = 'hbb';
$config['dbprefix'] = 'hbb_';

$config['fnmatch'] = "[^A-Za-z0-9_ -]";


$templ = file_get_contents('skins/moodswing/index.html');
$templ = str_replace('{$sitename}','lukegb\'s repo', $templ);



if (!isset($config['webroot'])) {
  // Okay, get foldername for $_SERVER['REQUEST_URI']:
  $slashpos = strrpos($_SERVER['SCRIPT_NAME'],'/');
  $config['webroot'] = substr($_SERVER['SCRIPT_NAME'],0,$slashpos+1);
  unset($slashpos);
}
$config['myfilename'] = urldecode(str_replace($config['webroot'],'',$_SERVER['REQUEST_URI']));


$a = file_get_contents('access.log');
$a.= date('r') . ' - '. $config['myfilename'] .'
';
file_put_contents('access.log',$a);

$config['dbcon'] = new mysqli($config['dbhost'], $config['dbusername'], $config['dbpassword'], $config['dbname']);
if ($config['dbcon']->connect_error || mysqli_connect_error()) { // Argh, stupid broken OO in PHP prior to 5.2.9/6.3.6
  die('There was a problem connecting to the database. Please try again later.');
}
