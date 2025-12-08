<?php
use CodeIgniter\Test\CIUnitTestCase;
use CodeIgniter\Test\FeatureTestTrait;

class RegisterTest extends CIUnitTestCase
{
    use FeatureTestTrait;

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

    


    /** SECCION DE JAFETH ESCENARIOS A PROBAR  ---- CLASES DE EQUIVALENCIA*/
    
    /**
     * CE-R2: Registro - Usuario vacío -> debe mostrar error de campo requerido
     */
    public function test_ce_r2_usuario_vacio_shows_required_error()
    {
        $post = [
            'name' => 'Juan Pérez',
            'user' => '', // usuario vacío
            'email' => 'juan_test@empresa.ac.pa',
            'password' => 'Correcta123!',
            'repassword' => 'Correcta123!',
            'provincia' => '01',
            'distrito' => '001',
            'corregimiento' => '0001',
        ];

        $response = $this->post('/register', $post);

        // Controller should re-render the form with validation errors (status 200)
        $response->assertStatus(200);

        // The view prints validation errors via $validation->listErrors(); check presence of "required" (English) or "requer" (spanish)
        $body = $response->getBody();
        $this->assertTrue(
            stripos($body, 'required') !== false || stripos($body, 'requer') !== false,
            'Validation error message not found in response body'
        );
    }

    /**
     * CE-R3: Registro - Correo vacío -> debe mostrar error de campo requerido
     */
    public function test_ce_r3_correo_vacio_shows_required_error()
    {
        $post = [
            'name' => 'Juan Pérez',
            'user' => 'juan_test',
            'email' => '', // correo vacío
            'password' => 'Correcta123!',
            'repassword' => 'Correcta123!',
            'provincia' => '01',
            'distrito' => '001',
            'corregimiento' => '0001',
        ];

        $response = $this->post('/register', $post);

        // Controller should re-render the form with validation errors (status 200)
        $response->assertStatus(200);

        // The view prints validation errors via $validation->listErrors(); check presence of "required" (English) or "requer" (spanish)
        $body = $response->getBody();
        $this->assertTrue(
            stripos($body, 'required') !== false || stripos($body, 'requer') !== false,
            'Validation error message not found in response body'
        );
    }

    /**
     * CE-R4: Registro - Contraseña vacía -> debe mostrar error de campo requerido
     *
     * ESCENARIO:
     * - Acción: POST a /register
     * - Datos: Todos los campos requeridos llenos, excepto 'password' y 'repassword' que están vacíos.
     * - Resultado Esperado: Status 200 y mensaje de error de campo requerido en el body.
     */
    public function test_ce_r4_password_vacia_shows_required_error()
    {
        $post = [
            'name' => 'Juan Pérez',
            'user' => 'juan_test',
            'email' => 'juan_test@empresa.ac.pa',
            'password' => '', // Contraseña vacía
            'repassword' => '', // Repetir contraseña vacía
            'provincia' => '01',
            'distrito' => '001',
            'corregimiento' => '0001',
        ];

        $response = $this->post('/register', $post);

        // 1. El controlador debería re-renderizar la vista con errores de validación (Status 200)
        $response->assertStatus(200);

        // 2. Verificar la presencia de un mensaje de error de validación de campo requerido en el body.
        $body = $response->getBody();
        $this->assertTrue(
            // Verifica si contiene 'required' (inglés) o alguna variante de 'requerido' (español)
            stripos($body, 'required') !== false || stripos($body, 'requer') !== false,
            'Validation error message for required field not found in response body'
        );

        // Opcional: Para ser más específico, podrías buscar la presencia de un mensaje relacionado específicamente
        // con el campo 'password' si tu sistema de validación lo permite. Por ejemplo, si el error es 'El campo Contraseña es requerido'.
    }


    /**
     * CE-R6: Registro - Usuario duplicado -> debe mostrar error de "usuario ya existe"
     *
     * ESCENARIO:
     * 1. Insertar un usuario con usuario = 'juan22' en la tabla `db_usuario`.
     * 2. Intentar registrar un nuevo usuario con 'user' = 'juan22'.
     * 3. Verificar que la respuesta contiene el mensaje de error de duplicidad para el usuario.
     */
    public function test_ce_r6_usuario_duplicado_shows_exists_error()
    {
        $existing_user = 'juan22';
        $existing_email = 'juan22_email@mail.com';

           // Insertar el usuario duplicado en la tabla `db_usuarios` (la que usan las reglas is_unique)
           // Insertemos exactamente el usuario que vamos a probar: $existing_user
           $this->db->query("INSERT INTO `db_usuarios` (`usuario`, `email`, `nombre`, `password`)
               VALUES (?, ?, 'Usuario Dup Test', 'PassSecure123!')", [$existing_user, $existing_email]);

        // Paso 2: Intentar registrar con el mismo usuario
        // Nota: Asegúrate de que el campo en el formulario POST sea 'user' o 'usuario', 
        // según lo que el controlador de CodeIgniter espera recibir.
        $post = [
            'name' => 'Usuario Nuevo',
            // Enviar el mismo valor de usuario que insertamos arriba para provocar la validación de unicidad
            'user' => $existing_user,
            'email' => 'nuevo_email_unico@mail.com',
            'password' => 'Correcta123!',
            'repassword' => 'Correcta123!',
            'provincia' => '01',
            'distrito' => '001',
            'corregimiento' => '0001',
        ];

        $response = $this->post('/register', $post);

        // El controlador debería re-renderizar la vista con errores (Status 200)
        $response->assertStatus(200);

        //Verificar que el mensaje de error de "usuario ya existe" se muestra
        $body = $response->getBody();

        // Use a boolean assertion so PHPUnit does not dump the full HTML on failure
        $found = stripos($body, 'user field must contain a unique value') !== false
            || stripos($body, 'The user field must contain a unique value') !== false
            || stripos($body, 'usuario') !== false && stripos($body, 'unique') !== false;

        $this->assertTrue(
            $found,
            'Validation error message for duplicate usuario not found in response body'
        );
    }

    /**
     * CE-R7: Registro - Correo duplicado -> debe mostrar error de "email ya existe"
     *
     * ESCENARIO:
     * 1. Insertar un usuario con un email específico ('email_dup@mail.com') en la tabla `db_usuarios`.
     * 2. Intentar registrar un nuevo usuario con el mismo 'email'.
     * 3. Verificar que la respuesta contiene el mensaje de error de unicidad para el email.
     */
    public function test_ce_r5_email_duplicado_shows_exists_error()
    {
        $existing_user = 'user_unico_test';
        $existing_email = 'Ok@mail.com';

        // Paso 1: Insertar el email duplicado en la tabla `db_usuarios` (Setup)
        // Usamos un usuario único, pero el email es el que queremos duplicar.
        $this->db->query("INSERT INTO `db_usuarios` (`usuario`, `email`, `nombre`, `password`) 
                            VALUES (?, ?, 'Email Dup Test', 'PassSecure123!')", 
                            [$existing_user, $existing_email]);

        // Paso 2: Datos de registro intentando usar el email existente
        $post = [
            'name' => 'Usuario Duplicado',
            'user' => 'nuevo_user_valido', // Este usuario debe ser único y válido
            'email' => $existing_email,     // ¡El email duplicado!
            'password' => 'Correcta123!',
            'repassword' => 'Correcta123!',
            'provincia' => '01',
            'distrito' => '001',
            'corregimiento' => '0001',
        ];

        $response = $this->post('/register', $post);

        // El controlador debe re-renderizar la vista con errores de validación (Status 200)
        $response->assertStatus(200);

        // Paso 3: Verificar que el mensaje de error de unicidad para el email se muestra
        $body = $response->getBody();
        
        // Buscamos mensajes comunes de validación de unicidad para el campo 'email'
        $found = stripos($body, 'email field must contain a unique value') !== false
            || stripos($body, 'The email field must contain a unique value') !== false
            || stripos($body, 'correo') !== false && stripos($body, 'existe') !== false
            || stripos($body, 'email') !== false && stripos($body, 'unico') !== false;

        $this->assertTrue(
            $found,
            'Validation error message for duplicate email not found in response body'
        );
    }
}