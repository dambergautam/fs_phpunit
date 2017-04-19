<?php
//Add Config file
require_once 'c:/wamp/www/fellowship/test/config/config.php';


//Class to be tested
require_once $root_dir.'/app/fw_admin/class/ManageEvent.php';

//Test ManageEvent
class ManageEventTest extends PHPUnit_Framework_TestCase{
    
    public $objMngEvent;

    public function setUp() {
        
        $this->objMngEvent = new ManageEvent();
        
    }
    
    public function testCountEvent(){
        $num = $this->objMngEvent->countEvent();
        //echo $num; die();
        $this->assertEquals(40, $num);
    }
    
}