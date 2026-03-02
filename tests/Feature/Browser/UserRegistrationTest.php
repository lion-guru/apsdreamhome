<?php
namespace Tests\Feature\Browser;

use Tests\TestCase;
use Facebook\WebDriver\Chrome\ChromeOptions;
use Facebook\WebDriver\Remote\DesiredCapabilities;
use Facebook\WebDriver\Remote\RemoteWebDriver;

class UserRegistrationTest extends TestCase
{
    protected $driver;
    
    protected function setUp(): void
    {
        parent::setUp();
        
        // Setup Chrome driver
        $options = new ChromeOptions();
        $options->addArguments([
            '--headless',
            '--no-sandbox',
            '--disable-dev-shm-usage',
            '--disable-gpu'
        ]);
        
        $capabilities = DesiredCapabilities::chrome();
        $capabilities->setCapability(ChromeOptions::CAPABILITY, $options);
        
        $this->driver = RemoteWebDriver::create(
            'http://localhost:9515', // ChromeDriver URL
            $capabilities
        );
    }
    
    protected function tearDown(): void
    {
        $this->driver->quit();
        parent::tearDown();
    }
    
    public function testCanRegisterUserThroughUI()
    {
        $this->driver->get(BASE_URL . '/register');
        
        // Fill registration form
        $this->driver->findElement(\Facebook\WebDriver\WebDriverBy::name('name'))->sendKeys('John Doe');
        $this->driver->findElement(\Facebook\WebDriver\WebDriverBy::name('email'))->sendKeys('john@example.com');
        $this->driver->findElement(\Facebook\WebDriver\WebDriverBy::name('password'))->sendKeys('password123');
        $this->driver->findElement(\Facebook\WebDriver\WebDriverBy::name('password_confirmation'))->sendKeys('password123');
        
        // Submit form
        $this->driver->findElement(\Facebook\WebDriver\WebDriverBy::cssSelector('button[type="submit"]'))->click();
        
        // Wait for redirect
        $this->driver->wait(10)->until(
            \Facebook\WebDriver\WebDriverExpectedCondition::urlContains('/dashboard')
        );
        
        // Assert success message
        $successMessage = $this->driver->findElement(\Facebook\WebDriver\WebDriverBy::cssSelector('.alert-success'))->getText();
        $this->assertStringContains('Registration successful', $successMessage);
        
        // Assert user is in database
        $this->assertDatabaseHas('users', [
            'name' => 'John Doe',
            'email' => 'john@example.com'
        ]);
    }
    
    public function testCannotRegisterWithDuplicateEmail()
    {
        $this->createTestUser(['email' => 'test@example.com']);
        
        $this->driver->get(BASE_URL . '/register');
        
        // Fill registration form with duplicate email
        $this->driver->findElement(\Facebook\WebDriver\WebDriverBy::name('name'))->sendKeys('Jane Doe');
        $this->driver->findElement(\Facebook\WebDriver\WebDriverBy::name('email'))->sendKeys('test@example.com');
        $this->driver->findElement(\Facebook\WebDriver\WebDriverBy::name('password'))->sendKeys('password123');
        $this->driver->findElement(\Facebook\WebDriver\WebDriverBy::name('password_confirmation'))->sendKeys('password123');
        
        // Submit form
        $this->driver->findElement(\Facebook\WebDriver\WebDriverBy::cssSelector('button[type="submit"]'))->click();
        
        // Wait for error message
        $this->driver->wait(10)->until(
            \Facebook\WebDriver\WebDriverExpectedCondition::visibilityOfElementLocated(
                \Facebook\WebDriver\WebDriverBy::cssSelector('.alert-danger')
            )
        );
        
        // Assert error message
        $errorMessage = $this->driver->findElement(\Facebook\WebDriver\WebDriverBy::cssSelector('.alert-danger'))->getText();
        $this->assertStringContains('Email already exists', $errorMessage);
    }
    
    public function testCanLoginThroughUI()
    {
        $password = 'password123';
        $this->createTestUser([
            'email' => 'test@example.com',
            'password' => password_hash($password, PASSWORD_DEFAULT)
        ]);
        
        $this->driver->get(BASE_URL . '/login');
        
        // Fill login form
        $this->driver->findElement(\Facebook\WebDriver\WebDriverBy::name('email'))->sendKeys('test@example.com');
        $this->driver->findElement(\Facebook\WebDriver\WebDriverBy::name('password'))->sendKeys($password);
        
        // Submit form
        $this->driver->findElement(\Facebook\WebDriver\WebDriverBy::cssSelector('button[type="submit"]'))->click();
        
        // Wait for redirect
        $this->driver->wait(10)->until(
            \Facebook\WebDriver\WebDriverExpectedCondition::urlContains('/dashboard')
        );
        
        // Assert user is logged in
        $welcomeMessage = $this->driver->findElement(\Facebook\WebDriver\WebDriverBy::cssSelector('.welcome-message'))->getText();
        $this->assertStringContains('Welcome', $welcomeMessage);
    }
    
    public function testCannotLoginWithInvalidCredentials()
    {
        $this->createTestUser(['email' => 'test@example.com']);
        
        $this->driver->get(BASE_URL . '/login');
        
        // Fill login form with wrong password
        $this->driver->findElement(\Facebook\WebDriver\WebDriverBy::name('email'))->sendKeys('test@example.com');
        $this->driver->findElement(\Facebook\WebDriver\WebDriverBy::name('password'))->sendKeys('wrongpassword');
        
        // Submit form
        $this->driver->findElement(\Facebook\WebDriver\WebDriverBy::cssSelector('button[type="submit"]'))->click();
        
        // Wait for error message
        $this->driver->wait(10)->until(
            \Facebook\WebDriver\WebDriverExpectedCondition::visibilityOfElementLocated(
                \Facebook\WebDriver\WebDriverBy::cssSelector('.alert-danger')
            )
        );
        
        // Assert error message
        $errorMessage = $this->driver->findElement(\Facebook\WebDriver\WebDriverBy::cssSelector('.alert-danger'))->getText();
        $this->assertStringContains('Invalid credentials', $errorMessage);
    }
}
