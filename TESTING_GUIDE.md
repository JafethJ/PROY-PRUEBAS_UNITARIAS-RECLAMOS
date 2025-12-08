# Guía de Pruebas Unitarias - Reclamos y Sugerencias

## Resumen

Este proyecto utiliza **PHPUnit 10.5** para ejecutar pruebas unitarias y funcionales. Los tests se encuentran en la carpeta `tests/` y cubren controladores, modelos y validaciones.

---

## 📁 Estructura de Tests

```
tests/
├── app/
│   ├── login/
│   │   └── LoginTest.php          # Tests para login (GET /login, POST /login)
│   └── Register/
│       └── RegisterTest.php       # Tests para registro (GET /register, POST /register)
├── database/
├── session/
├── unit/
└── _support/                      # Helpers y fixtures para tests
```

---

## 🚀 Cómo Ejecutar los Tests

### Opción 1: Ejecutar TODOS los tests
```powershell
composer test
```

### Opción 2: Ejecutar un archivo de test específico
```powershell
vendor\bin\phpunit tests/app/login/LoginTest.php
```

```powershell
vendor\bin\phpunit tests/app/Register/RegisterTest.php
```

### Opción 3: Ejecutar una prueba específica
```powershell
vendor\bin\phpunit tests/app/login/LoginTest.php --filter test_show_login_page
```

---

## 📊 Reporte de Cobertura

PHPUnit genera automáticamente un reporte de cobertura de código en formato HTML:

```powershell
composer test
start build/html/index.html
```

Esto abre un navegador mostrando qué porcentaje del código está cubierto por tests.

---

## 🧪 Tests Disponibles

### LoginTest.php
Verifica la funcionalidad del controlador `Auth/Login`:

- ✅ `test_show_login_page()` - Verifica que GET /login devuelve 200 y contiene el campo username
- ✅ `test_validation_fails_when_missing_fields()` - Verifica validación cuando campos están vacíos

### RegisterTest.php
Verifica la funcionalidad del controlador `Auth/Register`:

- ✅ `test_show_register_page_loads()` - Verifica que GET /register carga correctamente
- ✅ `test_post_register_with_empty_data()` - Verifica que POST con datos vacíos no causa error 500
- ✅ `test_post_register_with_partial_data()` - Verifica que POST con datos parciales no causa error 500

---

## 📝 Agregar Nuevas Pruebas

### Estructura Básica

```php
<?php
use CodeIgniter\Test\CIUnitTestCase;
use CodeIgniter\Test\FeatureTestTrait;

class NuevoControladorTest extends CIUnitTestCase
{
    use FeatureTestTrait;

    public function test_ejemplo()
    {
        $response = $this->get('/ruta');
        $response->assertStatus(200);
    }
}
```

### Métodos Comunes para Testing

**Solicitudes HTTP:**
```php
$this->get('/ruta');                    // GET request
$this->post('/ruta', ['campo' => 'valor']);  // POST request
$this->put('/ruta', ['campo' => 'valor']);   // PUT request
$this->delete('/ruta');                 // DELETE request
```

**Aserciones en Respuesta:**
```php
$response->assertStatus(200);           // Verifica código HTTP
$response->assertSee('texto');          // Verifica que el texto está en respuesta
$response->assertRedirect('/ruta');     // Verifica redirección
$response->assertSessionHas('key');     // Verifica sesión
```

### Ejemplo: Test de Modelo

```php
<?php
use CodeIgniter\Test\CIUnitTestCase;
use App\Models\UsersModel;

class UsersModelTest extends CIUnitTestCase
{
    private $usersModel;

    protected function setUp(): void
    {
        parent::setUp();
        $this->usersModel = new UsersModel();
    }

    public function test_validate_user_with_correct_credentials()
    {
        // Este test requeriría datos en la BD
        $user = $this->usersModel->validateUser('admin', 'password123');
        $this->assertNotFalse($user);
    }
}
```

---

## ⚙️ Configuración

### phpunit.xml
Archivo de configuración central para todos los tests. Define:
- Entorno de testing
- Conexión a BD (`ci4_test` en `.env`)
- Rutas de tests
- Genera reportes de cobertura en `build/`

### .env (Configuración de Testing)
```ini
database.tests.hostname = localhost
database.tests.database = ci4_test
database.tests.username = d72025
database.tests.password = 1234
database.tests.DBDriver = MySQLi
database.tests.port = 3306
```

---

## 🔧 Notas Importantes

1. **Sin necesidad de BD completa**: Los tests usan `FeatureTestTrait` que simula solicitudes HTTP sin tocar la BD cuando es posible.

2. **Xdebug Timeout**: Los mensajes `"Xdebug: [Step Debug] Time-out..."` son normales si no tienes un cliente de depuración remota conectado. No afectan los tests.

3. **Cobertura Baja**: Es normal tener baja cobertura inicial. Se recomienda incrementarla escribiendo tests para:
   - Validaciones complejas
   - Lógica de negocio en controladores
   - Métodos de modelos críticos
   - Filtros de seguridad

4. **Aislar Tests**: Los tests deben ser independientes entre sí. No confíes en orden de ejecución.

---

## 📚 Referencias

- [Documentación PHPUnit](https://docs.phpunit.de/)
- [CodeIgniter 4 Testing Guide](https://codeigniter.com/user_guide/testing/overview.html)
- [FeatureTestTrait Documentation](https://codeigniter.com/user_guide/testing/feature.html)

---

## 🐛 Troubleshooting

### "Table 'ci4_test.tabla' doesn't exist"
**Solución**: Necesitas crear las tablas en la BD `ci4_test` ejecutando migraciones:
```powershell
php spark migrate --database tests
```

### "Can't find a route for 'GET: /ruta'"
**Solución**: Verifica que la ruta existe en `app/Config/Routes.php`

### "Class TestName cannot be found"
**Solución**: Asegúrate de que el archivo PHP comienza con `<?php` al inicio

---

**Última actualización**: 2025-12-08
