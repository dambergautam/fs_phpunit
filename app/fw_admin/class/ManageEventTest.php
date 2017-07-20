<?php
$is_admin = true;
        
//Add Config file
require_once $root_dir.'/test/config/config.php';


//Class to be tested
require_once $root_dir.'/app/fw_admin/class/ManageEvent.php';

//Test ManageEvent
class ManageEventTest extends PHPUnit_Framework_TestCase{
    
    
    public function testRemoveEvent(){
        $objMngEvent = new ManageEvent();
        $event_id = 35;
        $this->assertTrue($objMngEvent->removeEvent($event_id));
    }


    
}