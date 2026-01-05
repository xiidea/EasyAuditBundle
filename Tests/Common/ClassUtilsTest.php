<?php

namespace Xiidea\EasyAuditBundle\Tests\Common;

use PHPUnit\Framework\TestCase;
use Xiidea\EasyAuditBundle\Common\ClassUtils;
use Doctrine\Persistence\Proxy;

class ClassUtilsTest extends TestCase
{
    public function testGetRealClassWithNonProxyClassName()
    {
        $className = 'My\Real\Class';
        $this->assertEquals($className, ClassUtils::getRealClass($className));
    }

    public function testGetRealClassWithProxyClassName()
    {
        $className = 'My\Proxy\Namespace\\' . Proxy::MARKER . '\My\Real\Class';
        $this->assertEquals('My\Real\Class', ClassUtils::getRealClass($className));
    }

    public function testGetClassWithNonProxyObject()
    {
        $object = new \stdClass();
        $this->assertEquals('stdClass', ClassUtils::getClass($object));
    }

    public function testGetClassWithProxyObject()
    {
        $proxy = new \My\Proxy\Namespace\__CG__\My\Real\RealClass();
        $this->assertEquals('My\Real\RealClass', ClassUtils::getClass($proxy));
    }
}

namespace My\Real;
class RealClass {}

namespace My\Proxy\Namespace\__CG__\My\Real;
class RealClass extends \My\Real\RealClass implements \Doctrine\Persistence\Proxy {
    public function __load(): void {}
    public function __isInitialized(): bool { return true; }
}
