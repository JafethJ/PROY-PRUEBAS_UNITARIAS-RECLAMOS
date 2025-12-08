# Todos tus tests deben extender:
use CodeIgniter\Test\CIUnitTestCase;

class LoginTest extends CIUnitTestCase
{
    //
}


# Para pruebas que llamen controladores:
use CodeIgniter\Test\FeatureTestTrait;

class LoginTest extends CIUnitTestCase
{
    use FeatureTestTrait;
}


