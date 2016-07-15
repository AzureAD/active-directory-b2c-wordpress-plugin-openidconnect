<?php
error_reporting(E_ALL);

use PHPUnit\Framework\TestCase;

include 'EndpointHandler.php';

class EndpointHandlerTest extends TestCase
{
	
    public function testConstructor()
    {
        $endpointHandler = new EndpointHandler("random policy");
    }
	
}

?>