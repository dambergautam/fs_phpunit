<?php

//Config
require_once 'c:/wamp/www/fellowship/test/config/config.php';

//Database
//require_once $root_dir.'/test/Mysql_class.php';

require_once $root_dir.'/test/Mysql_class.php';
require_once $root_dir.'/app/object/mysql_object.php';

//Class to be tested
require_once $root_dir.'/app/fw_admin/class/ManageEvent.php';

class GeneralTest extends PHPUnit_Framework_TestCase {
    public $objMngEvent;

    public function testMethod() {
        $a = 6;
        $b = '64';
        $this->assertEquals($a, $b);
    }
    
    public function setUp() {
        
        $this->objMngEvent = new ManageEvent();
        
    }
    
    public function testIsNum(){
        $this->assertEquals(true, $this->objMngEvent->isNum(5));
    }
    
    public function testCountEvent(){
        $num = $this->objMngEvent->countEvent();
        echo $num; die();
        $this->assertEquals(39, $num);
    }
    
    public function testInsertEvent(){
        $data = array(
                        'event_name' =>'PHPUnit',
                        'organization' => 'PUT',
                        'city'  => 'Idaho',
                        'event_location' => 'US'
                    );
        $last_id = $this->objMngEvent->insertEvent($data);
        echo $last_id;
        die();
    }
    
}