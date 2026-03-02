<?php
use PHPUnit\Framework\TestCase;
use App\Core\App;

class AppTest extends TestCase
{
    public function testAppInstance()
    {
        $app = App::getInstance();
        $this->assertNotNull($app);
    }
}
?>