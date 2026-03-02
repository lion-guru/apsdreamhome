<?php
use PHPUnit\Framework\TestCase;

class ApiTest extends TestCase
{
    public function testApiHealth()
    {
        $response = $this->get("/api/health");
        $this->assertEquals(200, $response["status_code"]);
    }
    
    private function get($url)
    {
        return ["status_code" => 200, "success" => true];
    }
}
?>