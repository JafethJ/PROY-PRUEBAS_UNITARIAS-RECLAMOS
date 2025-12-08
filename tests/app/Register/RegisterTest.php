<?php
use CodeIgniter\Test\CIUnitTestCase;
use CodeIgniter\Test\FeatureTestTrait;

class RegisterTest extends CIUnitTestCase
{
    use FeatureTestTrait;

    /**
     * Test: Verifica que la página de registro se carga correctamente.
     * Solo verifica que el controlador responde con status 200.
     */
    public function test_show_register_page_loads()
    {
        $response = $this->get('/register');

        $response->assertStatus(200);
    }

    /**
     * Test: Verifica que POST a /register con datos vacíos no causa error 500.
     * El controlador debería retornar la vista nuevamente con errores de validación.
     */
    public function test_post_register_with_empty_data()
    {
        $response = $this->post('/register', []);

        // Solo verificar que no hay error 500. El status podría ser 200, 302, etc.
        $this->assertNotEquals(500, $response->getStatusCode());
    }

    /**
     * Test: Verifica que POST a /register con datos parciales no causa error 500.
     */
    public function test_post_register_with_partial_data()
    {
        $response = $this->post('/register', [
            'name' => 'Juan Test',
            'user' => 'juan_test',
            'email' => 'jafeth@gmail.com',
            'password' => 'Pass123!',
            'repassword' => 'Pass123!',
        ]);

        // Solo verificar que no hay error 500
        $this->assertNotEquals(500, $response->getStatusCode());
    }

}


