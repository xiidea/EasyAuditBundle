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

use PHPUnit\Framework\TestCase;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\Yaml\Parser;
use Xiidea\EasyAuditBundle\DependencyInjection\XiideaEasyAuditExtension;

class XiideaEasyAuditExtensionTest extends TestCase {

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

        $this->assertHasDefinition('xiidea.easy_audit.logger.service');
        $this->assertHasDefinition('xiidea.easy_audit.logger_factory');
        $this->assertHasDefinition('xiidea.easy_audit.default_event_resolver');
        $this->assertNotHasDefinition('xiidea.easy_audit.default_doctrine_event_resolver');
        $this->assertHasDefinition('xiidea.easy_audit.event_resolver_factory');
        $this->assertHasDefinition('xiidea.easy_audit.event_listener');
        $this->assertHasDefinition('xiidea.easy_audit.doctrine_subscriber');
    }

    public function testLoadDefaultEntityEventResolverOnlyIfDoctrineLoaded()
    {
        $loader = new XiideaEasyAuditExtension();

        $config = $this->getRequiredConfig();
        $config['doctrine_event_resolver'] = 'xiidea.easy_audit.default_doctrine_event_resolver';
        $loader->load(array($config), $this->container);
        $this->assertHasDefinition('xiidea.easy_audit.default_doctrine_event_resolver');
    }

    public function testPrependEntityEventResolverValueOnlyIfDoctrineLoaded()
    {
        $loader = new XiideaEasyAuditExtension();
        $config = $this->getRequiredConfig();
        $this->container->prependExtensionConfig('doctrine', []);
        $this->container->prependExtensionConfig($loader->getAlias(), $config);

        $loader->prepend($this->container);
        $loader->load($this->container->getExtensionConfig($loader->getAlias()), $this->container);
        $this->assertHasDefinition('xiidea.easy_audit.default_doctrine_event_resolver');
    }

    /**
     * @expectedException \Symfony\Component\Config\Definition\Exception\InvalidConfigurationException
     */
    public function testEasyAuditLoadThrowsExceptionUnlessEntityClassSet()
    {
        $loader = new XiideaEasyAuditExtension();
        $config = $this->getRequiredConfig();
        unset($config['audit_log_class']);

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
    public function testEasyAuditLoadThrowsExceptionForInvalidLoggerChannelDefinition()
    {
        $loader = new XiideaEasyAuditExtension();
        $config = $this->getRequiredConfig();
        $config['logger_channel']  = array('foo.logger' => array("info", "!debug"));

        $loader->load(array($config), new ContainerBuilder());
    }

    public function testDoctrineEventSubscriberLoadedWithTrueParameter()
    {
        $loader = new XiideaEasyAuditExtension();
        $config = $this->getRequiredConfig();
        $config['doctrine_objects'] = true;

        $loader->load(array($config), $this->container);
        $this->assertHasDefinition('xiidea.easy_audit.doctrine_subscriber');
    }

    public function testLoggerChanelProvidedAsString()
    {
        $loader = new XiideaEasyAuditExtension();
        $config = $this->getRequiredConfig();
        $config['logger_channel']  = array('foo.logger' => "!info");

        $channel = array(
            'foo.logger' => array(
                'type' => 'exclusive',
                'elements' => array('info')
            ),
        );

        $loader->load(array($config), $this->container);
        $this->assertEquals($channel, $this->container->getParameter('xiidea.easy_audit.logger_channel'));
    }

    public function testDisableDoctrineEvents()
    {
        $loader = new XiideaEasyAuditExtension();
        $config = $this->getRequiredConfig();
        $config['doctrine_objects'] = false;

        $loader->load(array($config), $this->container);
        $this->assertNotHasDefinition('xiidea.easy_audit.doctrine_subscriber');
    }

    public function testDisableDefaultLogger()
    {
        $loader = new XiideaEasyAuditExtension();
        $config = $this->getRequiredConfig();
        $config['default_logger'] = false;

        $loader->load(array($config), $this->container);
        $this->assertNotHasDefinition('xiidea.easy_audit.logger.service');
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
        $config['audit_log_class'] = 'foo.entity';

        $loader->load(array($config), $this->container);

        $this->assertEquals('foo.entity', $this->container->getParameter('xiidea.easy_audit.audit_log_class'));
    }

    public function testOverwriteEntityEventResolver()
    {
        $loader = new XiideaEasyAuditExtension();
        $config = $this->getRequiredConfig();
        $config['doctrine_event_resolver'] = 'foo.resolver';

        $loader->load(array($config), $this->container);

        $this->assertNotHasDefinition('xiidea.easy_audit.default_doctrine_event_resolver');

        $this->assertEquals('foo.resolver', $this->container->getParameter('xiidea.easy_audit.doctrine_event_resolver'));
    }

    public function testFullConfiguration()
    {
        $loader = new XiideaEasyAuditExtension();
        $config = $this->getFullConfig();

        $loader->load(array($config), $this->container);

        $channel = array(
            'xiidea.easy_audit.logger.service' => array(
                'type' => 'inclusive',
                'elements' => array('info', 'debug')
            ),
            'file.logger' => array(
                'type' => 'exclusive',
                'elements' => array('info', 'debug')
            ),
        );

        $this->assertNotFalse($this->container->getParameter('xiidea.easy_audit.doctrine_objects'));
        $this->assertNotFalse($this->container->getParameter('xiidea.easy_audit.events'));
        $this->assertCount(2, $this->container->getParameter('xiidea.easy_audit.doctrine_objects'));
        $this->assertCount(2, $this->container->getParameter('xiidea.easy_audit.events'));
        $this->assertCount(2, $this->container->getParameter('xiidea.easy_audit.logger_channel'));
        $this->assertEquals($channel, $this->container->getParameter('xiidea.easy_audit.logger_channel'));
    }

    public function testOldConfigValue()
    {
        $loader = new XiideaEasyAuditExtension();
        $config = $this->getOldConfig();

        $this->container->prependExtensionConfig('doctrine', []);
        $this->container->prependExtensionConfig($loader->getAlias(), $config);

        $loader->prepend($this->container);
        $loader->load($this->container->getExtensionConfig($loader->getAlias()), $this->container);

        $this->assertNotFalse($this->container->getParameter('xiidea.easy_audit.doctrine_objects'));
        $this->assertNotFalse($this->container->getParameter('xiidea.easy_audit.events'));
        $this->assertCount(2, $this->container->getParameter('xiidea.easy_audit.doctrine_objects'));
    }

    /**
     * getRequiredConfig
     *
     * @return array
     */
    protected function getRequiredConfig()
    {
        $yaml = <<<EOF
audit_log_class : MyProject\Bundle\MyBundle\Entity\AuditLog                     #Required
user_property : ~ # or username                                              #Required
EOF;
        return $this->getArrayFromYaml($yaml);
    }

    /**
     * getRequiredConfig
     *
     * @return array
     */
    protected function getOldConfig()
    {
        $yaml = <<<EOF
entity_class : MyProject\Bundle\MyBundle\Entity\AuditLog                     #Required
entity_event_resolver : doctrine_event_resolver                                              #Required
doctrine_entities : 
     MyProject\Bundle\MyBundle\Entity\MyEntity : [created, updated, deleted]
     MyProject\Bundle\MyBundle\Entity\MyEntity2 : ~
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
audit_log_class : MyProject\Bundle\MyBundle\Entity\AuditLog                     #Required
doctrine_event_resolver : xiidea.easy_audit.default_doctrine_event_resolver      #Optional

#user property to use as actor of an event
#valid value will be any valid property of your user class ~
user_property : ~ # or username                                               #Required

#List of doctrine entity:event you wish to track
doctrine_objects :                                                          #Optional
     MyProject\Bundle\MyBundle\Entity\MyEntity : [created, updated, deleted]
     MyProject\Bundle\MyBundle\Entity\MyEntity2 : ~

#List all events you want to track  (Optional from v1.2.1 you can now use subscriber to define it)
events :                                                                      #Optional
    - security.interactive_login
    - security.authentication.failure : user.event_resolver

logger_channel:
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
