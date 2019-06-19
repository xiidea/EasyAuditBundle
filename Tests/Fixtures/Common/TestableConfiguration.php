<?php


namespace Xiidea\EasyAuditBundle\Tests\Fixtures\Common;


use Xiidea\EasyAuditBundle\DependencyInjection\Configuration;

class TestableConfiguration extends Configuration
{
    public function getRootNodeOfBuilder($builder)
    {
        return $this->getRootNode($builder);
    }
}
