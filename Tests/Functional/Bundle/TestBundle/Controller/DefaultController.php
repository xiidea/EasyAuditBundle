<?php

namespace Xiidea\EasyAuditBundle\Tests\Functional\Bundle\TestBundle\Controller;

use Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Xiidea\EasyAuditBundle\Tests\Fixtures\Event\Basic;

class DefaultController
{
    const RESPONSE_BOUNDARY = '======';

    public function index(EventDispatcherInterface $dispatcher, ParameterBagInterface $parameterBag, $event)
    {
        $dispatcher->dispatch(new Basic(), $event);

        $logFile = $parameterBag->get('kernel.cache_dir').DIRECTORY_SEPARATOR.'audit.log';

        return new Response(self::RESPONSE_BOUNDARY.file_get_contents($logFile).self::RESPONSE_BOUNDARY);
    }

    public function secure(EventDispatcherInterface $dispatcher, ParameterBagInterface $parameterBag, $event, Request $request)
    {
        $dispatcher->dispatch(new Basic(), $event);

        $logFile = $parameterBag->get('kernel.cache_dir').DIRECTORY_SEPARATOR.'audit.log';

        return new Response(self::RESPONSE_BOUNDARY.file_get_contents($logFile).self::RESPONSE_BOUNDARY);
    }

    public function secureNoEvent()
    {
        return new Response('ok');
    }
}
