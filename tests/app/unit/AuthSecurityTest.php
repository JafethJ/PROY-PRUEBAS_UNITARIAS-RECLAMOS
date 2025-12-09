<?php

namespace Tests\Unit;

use CodeIgniter\Test\CIUnitTestCase;
use CodeIgniter\Test\FeatureTestTrait;

/**
 * Security Tests
 * Tests for SQL Injection, XSS, invalid characters, and malicious payloads
 * WITHOUT modifying the original application code.
 */
class AuthSecurityTest extends CIUnitTestCase
{
    use FeatureTestTrait;

    protected $namespace = 'App';
    protected $refresh = true;
    protected $createdUsers = [];

    protected function tearDown(): void
    {
        parent::tearDown();

        if (! empty($this->createdUsers)) {
            $userModel = new \App\Models\UsersModel();
            foreach ($this->createdUsers as $u) {
                $userModel->where('usuario', $u)->orWhere('email', $u)->delete();
            }
        }
    }

    private function makeValidData(array $overrides = [])
    {
        $uniq = uniqid('sec');
        $data = [
            'user' => 'user_' . $uniq,
            'password' => 'Abc123!!',
            'repassword' => 'Abc123!!',
            'name' => 'Juan Perez',
            'email' => 'user_' . $uniq . '@example.test',
            'provincia' => '01',
            'distrito' => '0001',
            'corregimiento' => '000001',
        ];

        return array_merge($data, $overrides);
    }

    /**
     * SEG-1: SQL Injection in usuario field
     * Expected: System rejects malicious payload
     */
    public function testSEG1SQLInjectionEnUsuario()
    {
        $sqlInjection = "admin' OR '1'='1";
        $data = $this->makeValidData(['user' => $sqlInjection]);

        $result = $this->post('/register', $data);

        // Should either reject (200) or accept but safely (sanitized)
        // Verify no SQL error occurs and proper validation happens
        $result->assertStatus(200); // Expect rejection due to format
        $result->assertSee('alert-danger');
    }

    /**
     * SEG-2: SQL Injection in correo field
     * Expected: System rejects malicious payload
     */
    public function testSEG2SQLInjectionEnCorreo()
    {
        $sqlInjection = "test@example.com' OR '1'='1";
        $data = $this->makeValidData(['email' => $sqlInjection]);

        $result = $this->post('/register', $data);

        // Should reject invalid email format
        $result->assertStatus(200);
        $result->assertSee('alert-danger');
    }

    /**
     * SEG-3: Caracteres prohibidos en nombre
     * Expected: System indicates "caracteres no permitidos"
     */
    public function testSEG3CaracteresProhibidos()
    {
        $invalidChars = "Juan<>!@#$%^&*()";
        $data = $this->makeValidData(['name' => $invalidChars]);

        $result = $this->post('/register', $data);

        $result->assertStatus(200);
        $result->assertSee('alert-danger');
    }

    /**
     * SEG-4: POST vacío
     * Expected: System returns validation error
     */
    public function testSEG4POSTVacio()
    {
        $result = $this->post('/register', []);

        $result->assertStatus(200);
        $result->assertSee('alert-danger');
    }

    /**
     * SEG-5: Email con emojis no permitidos
     * Expected: System rejects email with emoji
     */
    public function testSEG5EmailNoPermitido()
    {
        $emojiEmail = "user😊@example.com";
        $data = $this->makeValidData(['email' => $emojiEmail]);

        $result = $this->post('/register', $data);

        $result->assertStatus(200);
        $result->assertSee('alert-danger');
    }

    /**
     * SEG-6: Script injection en nombre
     * Expected: System blocks script tag
     */
    public function testSEG6ScriptInjectionEnNombre()
    {
        $scriptPayload = "<script>alert('xss')</script>";
        $data = $this->makeValidData(['name' => $scriptPayload]);

        $result = $this->post('/register', $data);

        $result->assertStatus(200);
        $result->assertSee('alert-danger');
    }

    /**
     * SEG-7: Petición duplicada simultánea
     * Expected: One request should fail due to duplicate user
     */
    public function testSEG7PeticionDuplicadaSimultanea()
    {
        $uniq = uniqid('dup');
        $data = [
            'user' => 'user_' . $uniq,
            'password' => 'Abc123!!',
            'repassword' => 'Abc123!!',
            'name' => 'Juan Perez',
            'email' => 'user_' . $uniq . '@example.test',
            'provincia' => '01',
            'distrito' => '0001',
            'corregimiento' => '000001',
        ];

        // First request - should succeed
        $result1 = $this->post('/register', $data);
        $result1->assertStatus(302);

        // Track created user for cleanup
        $this->createdUsers[] = $data['user'];
        $this->createdUsers[] = $data['email'];

        // Second request with same data - should fail (duplicate)
        $result2 = $this->post('/register', $data);
        $result2->assertStatus(200);
        $result2->assertSee('alert-danger');
    }

    /**
     * SEG-8: SQL Injection con UNION
     * Expected: System safely handles or rejects
     */
    public function testSEG8SQLInjectionUnion()
    {
        $sqlUnion = "test' UNION SELECT * FROM usuarios--";
        $data = $this->makeValidData(['user' => $sqlUnion]);

        $result = $this->post('/register', $data);

        $result->assertStatus(200);
        $result->assertSee('alert-danger');
    }

    /**
     * SEG-9: XSS en atributo HTML
     * Expected: System escapes or rejects payload
     */
    public function testSEG9XSSEnAtributo()
    {
        $xssPayload = 'Juan" onload="alert(1)';
        $data = $this->makeValidData(['name' => $xssPayload]);

        $result = $this->post('/register', $data);

        $result->assertStatus(200);
        $result->assertSee('alert-danger');
    }

    /**
     * SEG-10: Null Byte Injection
     * Expected: System handles or rejects safely
     */
    public function testSEG10NullByteInjection()
    {
        $nullByte = "user\x00test";
        $data = $this->makeValidData(['user' => $nullByte]);

        $result = $this->post('/register', $data);

        // Should reject or safely handle
        $result->assertStatus(200);
        $result->assertSee('alert-danger');
    }
}
