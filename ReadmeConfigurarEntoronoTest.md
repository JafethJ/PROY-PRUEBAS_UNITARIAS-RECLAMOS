# -------------------------CONFIGURACIÓN DE VS CODE-------------------------
<!-- 1.  Configuración de VS Code (PHP Debug Extension)
Este paso asegura que VS Code pueda comunicarse con Xdebug.
1.	Instalar la Extensión:
o	Presiona $Ctrl+Shift+X$ (o $Cmd+Shift+X$) para abrir la vista de extensiones.
o	Busca PHP Debug (creada por Felix Becker/Xdebug).
o	Haz clic en Instalar.
2.	Crear el archivo launch.json:
o	Ve a la vista Ejecutar y Depurar (el icono del bicho 🐞, o presiona $Ctrl+Shift+D$).
o	Haz clic en el engranaje o en "crear un archivo launch.json".
o	Selecciona el entorno PHP.
o	Esto creará el archivo .vscode/launch.json con la configuración predeterminada Listen for Xdebug, usando el puerto 9003. -->


# ---------------------Configuración de PHP (php.ini)----------------------
<!-- 2. 📝 Configuración de PHP (php.ini)
CONFIGURACION DE XDEBUG
Instalar las extensiones de xdebug desde el mismo entorno de VSCODE
Usar la guía que muestra VSCODE
Ejecutar php versión para ver que version de Xdebug descargar
Pedirselo a un asistente que cual recomienda,
Descargar el archivo y guardar en la ruta de xampp/php/ext dentro de xampp

Configurar php.ini Poniendo al final esto y cambiarle el nombre del archivo (Archivo descargado) cuando se pone la ruta
	
[XDebug]
; 1. RUTA OBLIGATORIA (DEBE SER zend_extension)
zend_extension = C:\xampp\php\ext\php_xdebug-3.5.0-8.2-ts-vs16-x86_64.dll

; 2. MODOS REQUERIDOS
;    coverage: para PHPUnit
;    debug: para VS Code
xdebug.mode = debug,coverage

; 3. PUERTO PARA VS CODE
xdebug.client_port = 9003
xdebug.start_with_request = yes

reiniciar php, ejecutar php –version, y revisar si ya esta habilitado
 -->


# ----------------------------Configurar phpUnit en codeIgniter 4 (ya el framework hace la mayoría)-------------------
1. Cómo configurar PHPUnit en CodeIgniter 4 (paso a paso)
Revisar si composer.json ya incluye PHPUnit:
"require-dev": {
    "fakerphp/faker": "^1.9",
    "mikey179/vfsstream": "^1.6",
    "phpunit/phpunit": "^10.5.16"
}
<!-- 1.1 Instalar dependencias
En la raíz del proyecto:
composer install

1.2 Ejecutar pruebas
composer test
o
./vendor/bin/phpunit

1.3 Archivo de configuración
CodeIgniter ya trae su phpunit.xml.dist.
Solo cópialo y renómbralo:
cp phpunit.xml.dist phpunit.xml

1.4 Configurar base de datos de pruebas (IMPORTANTE)  ---LEER ReadmeEnviroment.md---------------
o Asegurate de descomentar esto (para trabajar con pruebas Automatizadas)

En tu .env, habilita esto:
database.tests.hostname = 127.0.0.1
database.tests.database = ci4_test
database.tests.username = d72025
database.tests.password = 1234
database.tests.DBDriver = MySQLi
database.tests.port = 3306

Luego crea la BD o Importa la bd que estará en app/scriptBD
CREATE DATABASE ci4_test; -->
