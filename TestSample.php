<?php

## SAMPLE

### Compare result
$this->assertEquals($expected, $obj->testMethod());

### Check is test result is true
$this->assertTrue($obj->returnTrue());

### Check is result is an array
$this->assertTrue(is_array($obj->eventList()));

### Check custom condition
$this->assertTrue(count($my_array) > 2);

### Setup default method
function setUp() {        
    $this->objMngEvent = new ManageEvent();        
}





