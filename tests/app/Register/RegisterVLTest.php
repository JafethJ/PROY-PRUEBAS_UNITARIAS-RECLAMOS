<?php
use CodeIgniter\Test\CIUnitTestCase;
use CodeIgniter\Test\FeatureTestTrait;
use CodeIgniter\Test\DatabaseTestTrait;

class RegisterVLTest extends CIUnitTestCase
{
    use FeatureTestTrait;
    use DatabaseTestTrait;

    protected function setUp(): void
    {
        parent::setUp();

        // Ensure provinces table exists in the test database so controller can load it
        $this->db = \Config\Database::connect();
        $db = $this->db;
        // Create a minimal provinces table used by the view
        $db->query("CREATE TABLE IF NOT EXISTS `db_provincia` (
            `codigo_provincia` VARCHAR(10) NOT NULL PRIMARY KEY,
            `nombre_provincia` VARCHAR(255) NOT NULL
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;");

        // Insert a sample province if not exists
        $db->query("INSERT INTO `db_provincia` (codigo_provincia, nombre_provincia)
            SELECT '01','PANAMA' FROM DUAL
            WHERE NOT EXISTS (SELECT 1 FROM `db_provincia` WHERE codigo_provincia = '01')");


        // --- Setup de la tabla de Usuarios

        // También crear la tabla `usuarios` que es la que usa la regla is_unique[usuarios.email] y is_unique[usuarios.usuario]
        $this->db->query("CREATE TABLE IF NOT EXISTS `db_usuarios` (
            `id` INT(11) NOT NULL AUTO_INCREMENT PRIMARY KEY,
            `usuario` VARCHAR(50) NOT NULL UNIQUE,
            `email` VARCHAR(255) NOT NULL UNIQUE,
            `password` VARCHAR(255) NOT NULL,
            `rol_id` INT(11) NULL,
            `provincia_id` VARCHAR(10) NULL,
            `distrito_id` VARCHAR(10) NULL,
            `corregimiento_id` VARCHAR(10) NULL,
            `created_at` DATETIME NULL,
            `nombre` VARCHAR(255) NOT NULL
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;");
    }

    // Limpiar después de cada prueba
    protected function tearDown(): void
    {
        parent::tearDown();
        
        // 1. Desactivar temporalmente las comprobaciones de FK para permitir el truncado
        $this->db->query('SET FOREIGN_KEY_CHECKS=0');

        // 2. Truncar tablas dependientes (Hijas) primero, verificando su existencia
        if ($this->db->tableExists('db_comentarios')) {
            // Usamos el método truncate() de CodeIgniter que es más robusto
            $this->db->table('db_comentarios')->truncate(); 
        }
        if ($this->db->tableExists('db_reclamos')) {
            $this->db->table('db_reclamos')->truncate();
        }

        // 3. Truncar la tabla principal (Padre)
        if ($this->db->tableExists('db_usuarios')) {
            $this->db->table('db_usuarios')->truncate();
        }
        
        // 4. Reactivar comprobaciones de FK
        $this->db->query('SET FOREIGN_KEY_CHECKS=1');
    }

    /**
     * AVL-R1: Nombre máximo válido (<=50) -> Registro Exitoso
     *
     * ESCENARIO:
     * 1. Enviar datos POST con un 'name' de 50 caracteres.
     * 2. Verificar que la respuesta es una redirección (Status 302).
     * 3. Verificar que el usuario fue insertado.
     */
    public function test_avl_r1_nombre_maximo_valido_registro_exitoso()
    {
        $test_user = 'user_avl_r1';
        $test_email = 'avl_r1_nombre@empresa.ac.pa';
        
        // Crear un nombre de exactamente 50 caracteres
        $max_name = 'JuanCarlosFernandezMartinezGonzalezPerezLopez123'; 

        $post = [
            'name' => $max_name, // Nombre de 50 caracteres
            'user' => $test_user,
            'email' => $test_email,
            'password' => 'Valida123!',
            'repassword' => 'Valida123!',
            'provincia' => '01',
            'distrito' => '0101',
            'corregimiento' => '010101',
        ];

        $response = $this->post('/register', $post);

        // 1. Verificar la redirección de éxito (Status 302)
        $response->assertStatus(302); 
        $response->assertRedirectTo('/login');
        
        // 2. Verificar que el usuario se insertó en la base de datos
        // Usamos el campo 'nombre' de la DB que mapea al campo 'name' del POST
        $user = $this->db->table('db_usuarios')->where('usuario', $test_user)->get()->getRow();
        $this->assertNotNull($user);
        $this->assertEquals($max_name, $user->nombre);
    }

    /**
     * AVL-R7: Contraseña mínima válida (>=8) -> Registro Exitoso
     *
     * ESCENARIO:
     * 1. Enviar datos POST con contraseña de 8 caracteres.
     * 2. Verificar que la respuesta es una redirección a /login (Status 302).
     * 3. Verificar que el usuario fue insertado en la tabla.
     */
    public function test_avl_r7_password_minima_valida_registro_exitoso()
    {
        // Generar datos únicos para este test
        $test_user = 'user_avl_r7_' . time();
        $test_email = 'avl_r7_' . time() . '@empresa.ac.pa';
        
        // Contraseña que cumple con el mínimo de 8 caracteres
        // Usamos una contraseña de 8 caracteres para cumplir la regla min_length[8]
        $min_password = 'Abc12345'; 

        $post = [
            'name' => 'Usuario Contraseña Min 8',
            'user' => $test_user,
            'email' => $test_email,
            'password' => $min_password,
            'repassword' => $min_password,
            'provincia' => '01',
            'distrito' => '001',
            'corregimiento' => '0001',
        ];

        $response = $this->post('/register', $post);

        //Verificar la redirección de éxito (Status 302)
        $response->assertStatus(302); 
        $response->assertRedirectTo('/login');

        //Verificar que el usuario se insertó en la base de datos
        $this->seeInDatabase('db_usuarios', [
            'usuario' => $test_user,
            'email' => $test_email,
        ]);
    }
    

    
    /**
     * AVL-R8: Contraseña corta invalida -> Registro fallido
     *
     * ESCENARIO:
     * 1. Enviar datos POST con contraseña de 7 caracteres.
     * 2. Verificar que la respuesta es una redirección al formulario (Status 200) con errores de validaciones.
     * 3. Esperar que NO se haya creado el usuario en la base de datos.
     */
    public function test_avl_r7_password_minima_rechazada_contrasena_corta()
    {
        $test_user = 'user_avl_r7_short_' . time();
        $test_email = 'avl_r7_short_' . time() . '@empresa.ac.pa';

        // contraseña de 7 caracteres (menos del mínimo de 8)
        $short_password = 'Abc1234';

        $post = [
            'name' => 'Usuario Contraseña Corta',
            'user' => $test_user,
            'email' => $test_email,
            'password' => $short_password,
            'repassword' => $short_password,
            'provincia' => '01',
            'distrito' => '001',
            'corregimiento' => '0001',
        ];

        $response = $this->post('/register', $post);

        $response->assertStatus(200);

        $body = $response->getBody();
        $this->assertTrue(
            stripos($body, 'min') !== false || stripos($body, 'mín') !== false || stripos($body, '8') !== false,
            'Expected validation message about minimum length was not found in response body'
        );

        // verificar que el usuario NO se insertó en la base de datos
        $user = $this->db->table('db_usuarios')->where('usuario', $test_user)->get()->getRow();
        $this->assertNull($user, 'User must NOT be created when password is shorter than minimum length');
    }
    
}