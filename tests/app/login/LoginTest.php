<?php
use CodeIgniter\Test\CIUnitTestCase;
use CodeIgniter\Test\FeatureTestTrait;

class LoginTest extends CIUnitTestCase
{
    use FeatureTestTrait;

    public function test_show_login_page()
    {
        $result = $this->get('/login');

        $result->assertStatus(200);
        $result->assertSee('name="username"');
    }

    public function test_validation_fails_when_missing_fields()
    {
        // Post with empty data should trigger validation and re-render login view
        $result = $this->post('/login', []);

        $result->assertStatus(200);
        // The response should re-render the login view (contains the username input)
        $result->assertSee('name="username"');
    }
}


