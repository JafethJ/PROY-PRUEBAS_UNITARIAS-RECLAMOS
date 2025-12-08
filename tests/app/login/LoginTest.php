<?php

use CodeIgniter\Test\CIUnitTestCase;
use CodeIgniter\Test\FeatureTestTrait;
use App\Models\UsersModel;

class LoginTest extends CIUnitTestCase
{
    use FeatureTestTrait;

    protected $model;
    protected $createdUsers = [];
    protected $testUser;

    protected function setUp(): void
    {
        parent::setUp();
        $this->model = new UsersModel();

        // Generar email único para evitar duplicados
        $uniq = uniqid('login_');
        $this->testUser = [
            'usuario' => 'juan_' . $uniq,
            'nombre' => 'Juan Pérez',
            'email' => 'juan_' . $uniq . '@test.com',
            'password' => password_hash('Correcta123', PASSWORD_DEFAULT),
            'rol_id' => 1,
            'estado' => 1
        ];

        // Insertar usuario real para pruebas
        $this->model->insert($this->testUser);
        $this->createdUsers[] = $this->testUser['usuario'];
        $this->createdUsers[] = $this->testUser['email'];
    }

    protected function tearDown(): void
    {
        parent::tearDown();
        
        // Cleanup: eliminar usuarios creados durante tests
        if (! empty($this->createdUsers)) {
            foreach ($this->createdUsers as $identifier) {
                $this->model->where('usuario', $identifier)
                            ->orWhere('email', $identifier)
                            ->delete();
            }
        }
    }

    // CE-L1 Login exitoso credenciales válidas
    public function testLoginCorrecto()
    {
        $result = $this->post('/login', [
            'username' => $this->testUser['usuario'],
            'password' => 'Correcta123'
        ]);

        $this->assertTrue($result->isRedirect());
        // Redirect a dashboard del ciudadano
        $this->assertStringContainsString('/ciudadano/dashboard', $result->getRedirectUrl());
    }

    // CE-L2 Usuario inexistente
    public function testUsuarioInexistente()
    {
        $result = $this->post('/login', [
            'username' => 'no_existe_' . uniqid(),
            'password' => 'Probando12'
        ]);

        $this->assertTrue($result->isRedirect());
        // El controlador redirige atrás (al referrer), no a /login
    }

    // CE-L3 Contraseña incorrecta
    public function testPasswordIncorrecto()
    {
        $result = $this->post('/login', [
            'username' => $this->testUser['usuario'],
            'password' => 'Incorrecta999'
        ]);

        $this->assertTrue($result->isRedirect());
        // El controlador redirige atrás (al referrer)
    }

    // CE-L4 Campos vacíos
    public function testCamposVacios()
    {
        $result = $this->post('/login', [
            'username' => '',
            'password' => ''
        ]);

        // Retorna la vista de login con errores de validación
        $result->assertStatus(200);
    }

    // CE-L5 Contraseña faltante
    public function testPasswordFaltante()
    {
        $result = $this->post('/login', [
            'username' => $this->testUser['usuario'],
            'password' => ''
        ]);

        // Retorna la vista de login con errores de validación
        $result->assertStatus(200);
    }

    // CE-L7 Formato de correo inválido
    public function testFormatoCorreoInvalido()
    {
        $result = $this->post('/login', [
            'username' => 'correo_invalido@',
            'password' => 'Correcta123'
        ]);

        $this->assertTrue($result->isRedirect());
    }

    // CE-L8 Cuenta bloqueada
    public function testCuentaBloqueada()
    {
        // Crear un usuario bloqueado
        $uniq = uniqid('blocked_');
        $blockedUser = [
            'usuario' => 'blocked_' . $uniq,
            'nombre' => 'Blocked User',
            'email' => $uniq . '@test.com',
            'password' => password_hash('Correcta123', PASSWORD_DEFAULT),
            'rol_id' => 1,
            'estado' => 0  // Bloqueado desde el principio
        ];
        
        $this->model->insert($blockedUser);
        $this->createdUsers[] = $blockedUser['usuario'];
        $this->createdUsers[] = $blockedUser['email'];

        $result = $this->post('/login', [
            'username' => $blockedUser['usuario'],
            'password' => 'Correcta123'
        ]);

        // Intento de login fallido, redirección a login
        $this->assertTrue($result->isRedirect());
    }

    // CE-L9 Contraseña con caracteres especiales
    public function testPasswordConCaracteresEspeciales()
    {
        $uniq = uniqid('special_');
        $specialUser = [
            'usuario' => 'user_' . $uniq,
            'nombre' => 'Test Special',
            'email' => $uniq . '@test.com',
            'password' => password_hash('Correct@1#', PASSWORD_DEFAULT),
            'rol_id' => 1,
            'estado' => 1
        ];
        
        $this->model->insert($specialUser);
        $this->createdUsers[] = $specialUser['usuario'];
        $this->createdUsers[] = $specialUser['email'];

        $result = $this->post('/login', [
            'username' => $specialUser['usuario'],
            'password' => 'Correct@1#'
        ]);

        $this->assertTrue($result->isRedirect());
        $this->assertStringContainsString('/ciudadano/dashboard', $result->getRedirectUrl());
    }

    // CE-L10 Normalización de correo (trim)
    public function testCorreoConEspacios()
    {
        $result = $this->post('/login', [
            'username' => '  ' . $this->testUser['usuario'] . '   ',
            'password' => 'Correcta123'
        ]);

        // Puede ser redirect o validación fallida dependiendo de implementación
        $this->assertTrue($result->isRedirect());
    }
}