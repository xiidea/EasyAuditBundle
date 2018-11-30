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

class XiideaEasyAuditExtensionTest extends TestCase
{
    /** @var ContainerBuilder */
    protected $container;

    protected function setUp()
    {
        $this->container = new ContainerBuilder();
    }

    public function testDefault()
    {
        $loader = new XiideaEasyAuditExtension();
        $loader->load([$this->getRequiredConfig()], $this->container);

        $this->assertHasDefinition('xiidea.easy_audit.logger.service');
        $this->assertHasDefinition('xiidea.easy_audit.logger_factory');
        $this->assertHasDefinition('xiidea.easy_audit.default_event_resolver');
        $this->assertNotHasDefinition('xiidea.easy_audit.default_document_event_resolver');
        $this->assertHasDefinition('xiidea.easy_audit.event_resolver_factory');
        $this->assertHasDefinition('xiidea.easy_audit.event_listener');
        $this->assertHasDefinition('xiidea.easy_audit.doctrine_subscriber');
    }

    public function testLoadDefaultDocumentEventResolverOnlyIfDoctrineLoaded()
    {
        $loader = new XiideaEasyAuditExtension();

        $this->container->addAliases(['doctrine_mongodb' => 'doctrine1']);
        $loader->load([$this->getRequiredConfig()], $this->container);
        $this->assertHasDefinition('xiidea.easy_audit.default_document_event_resolver');
    }

    /**
     * @expectedException \Symfony\Component\Config\Definition\Exception\InvalidConfigurationException
     */
    public function testEasyAuditLoadThrowsExceptionUnlessDocumentClassSet()
    {
        $loader = new XiideaEasyAuditExtension();
        $config = $this->getRequiredConfig();
        unset($config['document_class']);

        $loader->load([$config], new ContainerBuilder());
    }

    /**
     * @expectedException \Symfony\Component\Config\Definition\Exception\InvalidConfigurationException
     */
    public function testEasyAuditLoadThrowsExceptionUnlessUserPropertySet()
    {
        $loader = new XiideaEasyAuditExtension();
        $config = $this->getRequiredConfig();
        unset($config['user_property']);

        $loader->load([$config], new ContainerBuilder());
    }

    /**
     * @expectedException \Symfony\Component\Config\Definition\Exception\InvalidConfigurationException
     */
    public function testEasyAuditLoadThrowsExceptionForInvalidLoggerChannelDefinition()
    {
        $loader = new XiideaEasyAuditExtension();
        $config = $this->getRequiredConfig();
        $config['logger_channel'] = ['foo.logger' => ['info', '!debug']];

        $loader->load([$config], new ContainerBuilder());
    }

    public function testDoctrineEventSubscriberLoadedWithTrueParameter()
    {
        $loader = new XiideaEasyAuditExtension();
        $config = $this->getRequiredConfig();
        $config['doctrine_documents'] = true;

        $loader->load([$config], $this->container);
        $this->assertHasDefinition('xiidea.easy_audit.doctrine_subscriber');
    }

    public function testLoggerChanelProvidedAsString()
    {
        $loader = new XiideaEasyAuditExtension();
        $config = $this->getRequiredConfig();
        $config['logger_channel'] = ['foo.logger' => '!info'];

        $channel = [
            'foo.logger' => [
                'type'     => 'exclusive',
                'elements' => ['info']
            ],
        ];

        $loader->load([$config], $this->container);
        $this->assertEquals($channel, $this->container->getParameter('xiidea.easy_audit.logger_channel'));
    }

    public function testDisableDoctrineEvents()
    {
        $loader = new XiideaEasyAuditExtension();
        $config = $this->getRequiredConfig();
        $config['doctrine_documents'] = false;

        $loader->load([$config], $this->container);
        $this->assertNotHasDefinition('xiidea.easy_audit.doctrine_subscriber');
    }

    public function testDisableDefaultLogger()
    {
        $loader = new XiideaEasyAuditExtension();
        $config = $this->getRequiredConfig();
        $config['default_logger'] = false;

        $loader->load([$config], $this->container);
        $this->assertNotHasDefinition('xiidea.easy_audit.logger.service');
    }

    public function testOverwriteResolver()
    {
        $loader = new XiideaEasyAuditExtension();
        $config = $this->getRequiredConfig();
        $config['resolver'] = 'foo.default_event_resolver';

        $loader->load([$config], $this->container);

        $this->assertNotHasDefinition('xiidea.easy_audit.default_event_resolver');

        $this->assertEquals('foo.default_event_resolver', $this->container->getParameter('xiidea.easy_audit.resolver'));
    }

    public function testDefineDocumentClass()
    {
        $loader = new XiideaEasyAuditExtension();
        $config = $this->getRequiredConfig();
        $config['document_class'] = 'foo.document';

        $loader->load([$config], $this->container);

        $this->assertEquals('foo.document', $this->container->getParameter('xiidea.easy_audit.document_class'));
    }

    public function testOverwriteDocumentEventResolver()
    {
        $loader = new XiideaEasyAuditExtension();
        $config = $this->getRequiredConfig();
        $config['document_event_resolver'] = 'foo.resolver';

        $loader->load([$config], $this->container);

        $this->assertNotHasDefinition('xiidea.easy_audit.default_document_event_resolver');

        $this->assertEquals(
            'foo.resolver',
            $this->container->getParameter('xiidea.easy_audit.document_event_resolver')
        );
    }

    public function testFullConfiguration()
    {
        $loader = new XiideaEasyAuditExtension();
        $config = $this->getFullConfig();

        $loader->load([$config], $this->container);

        $channel = [
            'xiidea.easy_audit.logger.service' => [
                'type'     => 'inclusive',
                'elements' => ['info', 'debug']
            ],
            'file.logger'                      => [
                'type'     => 'exclusive',
                'elements' => ['info', 'debug']
            ],
        ];

        $this->assertNotFalse($this->container->getParameter('xiidea.easy_audit.doctrine_documents'));
        $this->assertNotFalse($this->container->getParameter('xiidea.easy_audit.events'));
        $this->assertCount(2, $this->container->getParameter('xiidea.easy_audit.doctrine_documents'));
        $this->assertCount(2, $this->container->getParameter('xiidea.easy_audit.events'));
        $this->assertCount(2, $this->container->getParameter('xiidea.easy_audit.logger_channel'));
        $this->assertEquals($channel, $this->container->getParameter('xiidea.easy_audit.logger_channel'));
    }

    /**
     * getRequiredConfig
     *
     * @return array
     */
    protected function getRequiredConfig()
    {
        $yaml = <<<EOF
document_class : MyProject\Bundle\MyBundle\Document\AuditLog                     #Required
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
document_class : MyProject\Bundle\MyBundle\Document\AuditLog                     #Required
document_event_resolver : xiidea.easy_audit.default_document_event_resolver      #Optional

#user property to use as actor of an event
#valid value will be any valid property of your user class ~
user_property : ~ # or username                                               #Required

#List of doctrine document:event you wish to track
doctrine_documents :                                                          #Optional
     MyProject\Bundle\MyBundle\Document\MyDocument : [created, updated, deleted]
     MyProject\Bundle\MyBundle\Document\MyDocument2 : ~

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
