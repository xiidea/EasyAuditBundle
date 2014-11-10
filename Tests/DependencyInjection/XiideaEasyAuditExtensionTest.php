<?php

/*
 * This file is part of the XiideaEasyAuditBundle package.
 *
 * (c) Xiidea <http://www.xiidea.net>
 *
 * This source file is subject to the MIT license that is bundled
 * with this source code in the file LICENSE.
 */

namespace Xiidea\EasyAuditBundle\Tests\DependencyInjection;

use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\Yaml\Parser;
use Xiidea\EasyAuditBundle\DependencyInjection\XiideaEasyAuditExtension;

class XiideaEasyAuditExtensionTest extends \PHPUnit_Framework_TestCase {

    /** @var ContainerBuilder */
    protected $container;

    protected function setUp()
    {
        $this->container = new ContainerBuilder();
    }

    public function testDefault()
    {
        $loader = new XiideaEasyAuditExtension();
        $loader->load(array($this->getRequiredConfig()), $this->container);

        $this->assertHasDefinition('xiidea.easy_audit.audit_log_repository');
        $this->assertHasDefinition('xiidea.easy_audit.logger.service');
        $this->assertHasDefinition('xiidea.easy_audit.logger_factory');
        $this->assertHasDefinition('xiidea.easy_audit.default_event_resolver');
        $this->assertHasDefinition('xiidea.easy_audit.default_entity_event_resolver');
        $this->assertHasDefinition('xiidea.easy_audit.event_resolver_factory');
        $this->assertHasDefinition('xiidea.easy_audit.event_listener');
        $this->assertHasDefinition('xiidea.easy_audit.doctrine_subscriber');
    }

    /**
     * @expectedException \Symfony\Component\Config\Definition\Exception\InvalidConfigurationException
     */
    public function testEasyAuditLoadThrowsExceptionUnlessEntityClassSet()
    {
        $loader = new XiideaEasyAuditExtension();
        $config = $this->getRequiredConfig();
        unset($config['entity_class']);

        $loader->load(array($config), new ContainerBuilder());
    }

    /**
     * @expectedException \Symfony\Component\Config\Definition\Exception\InvalidConfigurationException
     */
    public function testEasyAuditLoadThrowsExceptionUnlessUserPropertySet()
    {
        $loader = new XiideaEasyAuditExtension();
        $config = $this->getRequiredConfig();
        unset($config['user_property']);

        $loader->load(array($config), new ContainerBuilder());
    }

    /**
     * @expectedException \Symfony\Component\Config\Definition\Exception\InvalidConfigurationException
     */
    public function testEasyAuditLoadThrowsExceptionForInvalidLoggerChanelDefinition()
    {
        $loader = new XiideaEasyAuditExtension();
        $config = $this->getRequiredConfig();
        $config['logger_chanel']  = array('foo.logger' => array("info", "!debug"));

        $loader->load(array($config), new ContainerBuilder());
    }

    public function testDoctrineEventSubscriberLoadedWithTrueParameter()
    {
        $loader = new XiideaEasyAuditExtension();
        $config = $this->getRequiredConfig();
        $config['doctrine_entities'] = true;

        $loader->load(array($config), $this->container);
        $this->assertHasDefinition('xiidea.easy_audit.doctrine_subscriber');
    }

    public function testDisableDoctrineEvents()
    {
        $loader = new XiideaEasyAuditExtension();
        $config = $this->getRequiredConfig();
        $config['doctrine_entities'] = false;

        $loader->load(array($config), $this->container);
        $this->assertNotHasDefinition('xiidea.easy_audit.doctrine_subscriber');
    }

    public function testOverwriteResolver()
    {
        $loader = new XiideaEasyAuditExtension();
        $config = $this->getRequiredConfig();
        $config['resolver'] = 'foo.default_event_resolver';

        $loader->load(array($config), $this->container);

        $this->assertNotHasDefinition('xiidea.easy_audit.default_event_resolver');

        $this->assertEquals('foo.default_event_resolver', $this->container->getParameter('xiidea.easy_audit.resolver'));
    }

    public function testDefineEntityClass()
    {
        $loader = new XiideaEasyAuditExtension();
        $config = $this->getRequiredConfig();
        $config['entity_class'] = 'foo.entity';

        $loader->load(array($config), $this->container);

        $this->assertEquals('foo.entity', $this->container->getParameter('xiidea.easy_audit.entity_class'));
    }

    public function testOverwriteEntityEventResolver()
    {
        $loader = new XiideaEasyAuditExtension();
        $config = $this->getRequiredConfig();
        $config['entity_event_resolver'] = 'foo.resolver';

        $loader->load(array($config), $this->container);

        $this->assertNotHasDefinition('xiidea.easy_audit.default_entity_event_resolver');

        $this->assertEquals('foo.resolver', $this->container->getParameter('xiidea.easy_audit.entity_event_resolver'));
    }

    public function testFullConfiguration()
    {
        $loader = new XiideaEasyAuditExtension();
        $config = $this->getFullConfig();

        $loader->load(array($config), $this->container);

        $chanel = array(
            'xiidea.easy_audit.logger.service' => array(
                'type' => 'inclusive',
                'elements' => array('info', 'debug')
            ),
            'file.logger' => array(
                'type' => 'exclusive',
                'elements' => array('info', 'debug')
            ),
        );

        $this->assertNotFalse($this->container->getParameter('xiidea.easy_audit.doctrine_entities'));
        $this->assertNotFalse($this->container->getParameter('xiidea.easy_audit.events'));
        $this->assertCount(2, $this->container->getParameter('xiidea.easy_audit.doctrine_entities'));
        $this->assertCount(2, $this->container->getParameter('xiidea.easy_audit.events'));
        $this->assertCount(2, $this->container->getParameter('xiidea.easy_audit.logger_chanel'));
        $this->assertEquals($chanel, $this->container->getParameter('xiidea.easy_audit.logger_chanel'));
    }

    /**
     * getRequiredConfig
     *
     * @return array
     */
    protected function getRequiredConfig()
    {
        $yaml = <<<EOF
entity_class : MyProject\Bundle\MyBundle\Entity\AuditLog                     #Required
user_property : ~ # or username                                              #Required
EOF;
        return $this->getArrayFromYaml($yaml);
    }

    /**
     * getFullConfig
     *
     * @return array
     */
    protected function getFullConfig()
    {
        $yaml = <<<EOF
resolver: xiidea.easy_audit.default_event_resolver                           #Optional
entity_class : MyProject\Bundle\MyBundle\Entity\AuditLog                     #Required
entity_event_resolver : xiidea.easy_audit.default_entity_event_resolver      #Optional

#user property to use as actor of an event
#valid value will be any valid property of your user class ~
user_property : ~ # or username                                               #Required

#List of doctrine entity:event you wish to track
doctrine_entities :                                                          #Optional
     MyProject\Bundle\MyBundle\Entity\MyEntity : [created, updated, deleted]
     MyProject\Bundle\MyBundle\Entity\MyEntity2 : ~

#List all events you want to track  (Optional from v1.2.1 you can now use subscriber to define it)
events :                                                                      #Optional
    - security.interactive_login
    - security.authentication.failure : user.event_resolver

logger_chanel:
    xiidea.easy_audit.logger.service: ["info", "debug"]
    file.logger: ["!info", "!debug"]
EOF;
        return $this->getArrayFromYaml($yaml);
    }

    /**
     * @param string $id
     */
    private function assertHasDefinition($id)
    {
        $this->assertTrue(($this->container->hasDefinition($id) ?: $this->container->hasAlias($id)));
    }

    /**
     * @param string $id
     */
    private function assertNotHasDefinition($id)
    {
        $this->assertFalse(($this->container->hasDefinition($id) ?: $this->container->hasAlias($id)));
    }

    /**
     * @param $yaml
     * @return mixed
     */
    protected function getArrayFromYaml($yaml)
    {
        $parser = new Parser();

        return $parser->parse($yaml);
    }

}
