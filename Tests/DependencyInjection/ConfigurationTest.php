<?php

namespace Xiidea\EasyAuditBundle\Tests\DependencyInjection;


use PHPUnit\Framework\TestCase;
use Xiidea\EasyAuditBundle\DependencyInjection\Configuration;
use Xiidea\EasyAuditBundle\Tests\Fixtures\Common\TestableConfiguration;

class TreeBuilderOld {

    public function root()
    {
        return 'ROOT_METHOD';
    }
}

class TreeBuilderNew extends TreeBuilderOld {

    public function getRootNode()
    {
        return 'GET_ROOT_NODE_METHOD';
    }
}

class ConfigurationTest extends TestCase
{

    public function testGetRootNodeByCallingGetRootNodeMethodOfBuilder()
    {
        $configuration = new TestableConfiguration();


        $result = $configuration->getRootNodeOfBuilder(new TreeBuilderNew());

        $this->assertEquals('GET_ROOT_NODE_METHOD', $result);

    }

    public function testGetRootNodeByCallingRootMethodOfBuilder()
    {
        $configuration = new TestableConfiguration();


        $result = $configuration->getRootNodeOfBuilder(new TreeBuilderOld());

        $this->assertEquals('ROOT_METHOD', $result);

    }
}
