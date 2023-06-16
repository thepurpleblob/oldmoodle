<?php  /// Moodle Configuration File 

unset($CFG);
$CFG=new stdClass;

$CFG->dbtype    = 'mysql';
$CFG->dbhost    = 'db';
$CFG->dbname    = 'moodle';
$CFG->dbuser    = 'root';
$CFG->dbpass    = 'purple';
$CFG->dbpersist =  false;
$CFG->prefix    = 'mdl_';

$CFG->wwwroot   = 'http://bertha.local:8080';
$CFG->dirroot   = '/var/www/html';
$CFG->dataroot  = '/var/www/moodledata';
$CFG->admin     = 'admin';

$CFG->directorypermissions = 00777;  // try 02777 on a server in Safe Mode

$CFG->unicodedb = true;  // Database is utf8

require_once("$CFG->dirroot/lib/setup.php");
// MAKE SURE WHEN YOU EDIT THIS FILE THAT THERE ARE NO SPACES, BLANK LINES,
// RETURNS, OR ANYTHING ELSE AFTER THE TWO CHARACTERS ON THE NEXT LINE.
?>
