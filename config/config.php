<?php
//Salt
$FS = "fs02_";

//PHPUnit test project root
$root_dir = 'c:/wamp/www/fellowship';

//Setting base and admin url path as per http host url
$base_url = "http://fellowship.localhost";
$admin_url = "http://fellowship.localhost/fw_admin";   

//Load database properties file
$db_properties = array(
            "HOST_NAME"=> "localhost",
            "DB_USER"=> "root",
            "DB_PSWD" => "",
            "DB_NAME"=> "fellowship_del",
            "PROJECT_ENV" =>"local"
        );

//use in other conf paths (images, url etc)
$FS_DIR_LEVEL = $root_dir;


//Load default project configuration
$FS_CONFIG = (require_once $root_dir.'/config/env_config/config.default.php');

//Load session configuration
$FS_SESSION = (require_once $root_dir.'/config/session/session.conf.php');

//Load MAIL configuration properties
$FS_CONFIG['MAIL'] = (require_once $root_dir.'/config/mail.php');



//Overriding default settings with environment configurations and properties
switch ($db_properties['PROJECT_ENV']) {
    case 'prod':
        require_once $root_dir.'/config/env_config/config.prod.php'; break;   //Production server config
    case 'stg':
        require_once $root_dir.'/config/env_config/config.stg.php'; break;    //Staging server config
    case 'tst':
        require_once $root_dir.'/config/env_config/config.tst.php'; break;    //Test server config
    default:
       require_once $root_dir.'/config/env_config/config.local.php';          //Local server config
}


// Set default timezone
date_default_timezone_set($FS_CONFIG['DEFINE']['TIMEZONE']);


//Display all posible errors
error_reporting(E_ALL);
ini_set("display_errors", 1);


//Load vendor
//require_once $root_dir.'/vendor/autoload.php';


//Load other classes
if(isset($is_admin) && $is_admin == true){
    $CONF_ADMIN = true;
}else{
    $CONF_ADMIN = false;
}

spl_autoload_register(function($class_name){
    global $CONF_ADMIN;
    global $root_dir;

    //Applicant Path
    $dir = $root_dir."/app/class/";
    
    //Admin path
    if($CONF_ADMIN == true){ $dir = $root_dir."/app/fw_admin/class/"; }
    
    //loading local one
    if( strtolower($class_name) != 'mysql_class'){
    
        //include files
        if(file_exists($dir.$class_name.".php")){
            include_once($dir.$class_name.".php");

        // Make a string lowercase
        }else if(file_exists($dir.strtolower($class_name).".php")){
            include_once($dir.strtolower($class_name).".php");

        // Make a string's first character lowercase
        }else if(file_exists($dir.lcfirst($class_name).".php")){
            include_once($dir.lcfirst($class_name).".php");

        // Make a string's first character uppercase
        }else if(file_exists($dir.ucfirst($class_name).".php")){
            include_once($dir.ucfirst($class_name).".php");        
        }
    }
});    


//Load date and time class from applicant class folder for admin
if($CONF_ADMIN === true){
    include $root_dir.'/app/class/carbonDateTime.php';
}

//Mysql class
require_once $root_dir.'/test/Mysql_class.php';
require_once $root_dir.'/app/object/mysql_object.php';