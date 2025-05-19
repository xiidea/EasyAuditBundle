<?php

namespace Xiidea\EasyAuditBundle\Tests\Common;

use Doctrine\Persistence\Proxy;
use PHPUnit\Framework\TestCase;
use Xiidea\EasyAuditBundle\Common\ClassUtils;

class DummyClass
{
}

class DummyProxyClass extends DummyClass implements Proxy
{
    public function __load()
    {
    }

    public function __isInitialized()
    {
        return true;
    }
}

class ClassUtilTest extends TestCase
{
    public function testGetClassWithNormalObject()
    {
        $object = new DummyClass();
        $result = ClassUtils::getClass($object);
        $this->assertSame(DummyClass::class, $result);
    }

    public function testGetClassWithProxyObject()
    {
        $proxy = new DummyProxyClass();
        $result = ClassUtils::getClass($proxy);
        $this->assertSame(DummyClass::class, $result);
    }
}