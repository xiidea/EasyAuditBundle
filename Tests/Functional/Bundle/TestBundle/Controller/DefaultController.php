<?php

namespace Xiidea\EasyAuditBundle\Tests\Functional\Bundle\TestBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Response;
use Xiidea\EasyAuditBundle\Tests\Fixtures\Event\Basic;

class DefaultController extends Controller
{
    const RESPONSE_BOUNDARY = '======';

    public function indexAction($event)
    {
        $this->get('event_dispatcher')->dispatch(
            $event,
            new Basic($event)
        );

        $logFile = $this->container->getParameter('kernel.cache_dir') . DIRECTORY_SEPARATOR . 'audit.log';

        return new Response(self::RESPONSE_BOUNDARY . file_get_contents($logFile) . self::RESPONSE_BOUNDARY);
    }

    public function secureAction($event)
    {
        $this->get('event_dispatcher')->dispatch(
            $event,
            new Basic($event)
        );

        $logFile = $this->container->getParameter('kernel.cache_dir') . DIRECTORY_SEPARATOR . 'audit.log';

        return new Response(self::RESPONSE_BOUNDARY . file_get_contents($logFile) . self::RESPONSE_BOUNDARY);
    }

    public function secureNoEventAction()
    {
        return new Response('ok');
    }
}
