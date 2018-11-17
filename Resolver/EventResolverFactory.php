<?php

/*
 * This file is part of the XiideaEasyAuditBundle package.
 *
 * (c) Xiidea <http://www.xiidea.net>
 *
 * This source file is subject to the MIT license that is bundled
 * with this source code in the file LICENSE.
 */

namespace Xiidea\EasyAuditBundle\Resolver;

use Symfony\Component\EventDispatcher\Event;
use Symfony\Component\PropertyAccess\Exception\NoSuchPropertyException;
use Symfony\Component\PropertyAccess\PropertyAccess;
use Xiidea\EasyAuditBundle\Common\UserAwareComponent;
use Xiidea\EasyAuditBundle\Entity\BaseAuditLog;
use Xiidea\EasyAuditBundle\Events\DoctrineEvents;
use Xiidea\EasyAuditBundle\Exception\InvalidServiceException;
use Xiidea\EasyAuditBundle\Exception\UnrecognizedEntityException;
use Xiidea\EasyAuditBundle\Exception\UnrecognizedEventInfoException;

class EventResolverFactory extends UserAwareComponent
{
    private $customResolvers = array();
    private $commonResolver;

    /**
     * @var EventResolverInterface
     */
    private $entityEventResolver;

    private $resolverEventMap = array();

    private $debug = false;
    private $userProperty;
    private $entityClass;

    /**
     * EventResolverFactory constructor.
     *
     * @param array $resolverEventMap
     * @param $userProperty
     * @param $entityClass
     */
    public function __construct(array $resolverEventMap = array(), $userProperty = 'username', $entityClass = BaseAuditLog::class)
    {
        $this->resolverEventMap = $resolverEventMap;
        $this->userProperty = $userProperty;
        $this->entityClass = $entityClass;
    }

    /**
     * @param Event $event
     * @param string $eventName
     * @return null|BaseAuditLog
     * @throws UnrecognizedEventInfoException
     * @throws \Exception
     */
    public function getEventLog(Event $event, $eventName)
    {
        $eventLog = $this->getEventLogObject($this->getEventLogInfo($event, $eventName));

        if ($eventLog === null) {
            return null;
        }

        $eventLog->setTypeId($eventName);
        $eventLog->setIp($this->getClientIp());
        $eventLog->setEventTime(new \DateTime());
        $this->setUser($eventLog);

        return $eventLog;
    }

    /**
     * @param $eventInfo
     *
     * @return null|BaseAuditLog
     * @throws UnrecognizedEventInfoException
     * @throws \Exception
     */
    protected function getEventLogObject($eventInfo)
    {
        if (empty($eventInfo)) {
            return null;
        }

        if ($eventInfo instanceof BaseAuditLog) {
            return $eventInfo;
        }

        return $this->createEventObjectFromArray($eventInfo);
    }

    /**
     * @param string $eventName
     *
     * @return EventResolverInterface
     */
    protected function getResolver($eventName)
    {

        if ($this->isEntityEvent($eventName)) {
            return $this->entityEventResolver;
        }


        if (isset($this->resolverEventMap[$eventName]) && isset($this->customResolvers[$this->resolverEventMap[$eventName]])) {
            return $this->customResolvers[$this->resolverEventMap[$eventName]];
        }

        return $this->commonResolver;
    }

    /**
     * @param string $eventName
     * @return bool
     */
    protected function isEntityEvent($eventName)
    {
        return in_array($eventName, DoctrineEvents::getConstants());
    }

    /**
     * @param Event $event
     * @param string $eventName
     * @return null
     * @throws InvalidServiceException
     */
    protected function getEventLogInfo(Event $event, $eventName)
    {
        if ($event instanceof EmbeddedEventResolverInterface) {
            return $event->getEventLogInfo($eventName);
        }

        if (null === $eventResolver = $this->getResolver($eventName)) {
            return null;
        }

        return $eventResolver->getEventLogInfo($event, $eventName);
    }

    /**
     * @param BaseAuditLog $entity
     * @throws \Exception
     */
    protected function setUser(BaseAuditLog $entity)
    {
        if (null === $user = $this->getUser()) {
            $entity->setUser($this->getAnonymousUserName());
            return;
        }

        $entity->setUser($this->getSettablePropertyValue($this->userProperty, $user));

        $this->setImpersonatingUser($entity, $this->userProperty);
    }


    /**
     * @return string
     */
    protected function getClientIp()
    {
        $request = $this->getRequest();

        if ($request) {
            return $request->getClientIp();
        }

        return "";
    }

    /**
     * @param $id
     * @param EventResolverInterface $resolver
     *
     * @throws \Exception|InvalidServiceException
     */
    public function addCustomResolver($id, $resolver)
    {
        if (!$resolver instanceof EventResolverInterface) {
            $this->handleException(new InvalidServiceException(
                'Resolver Service must implement' . EventResolverInterface::class
            ));

            return;
        }

        $this->customResolvers[$id] = $resolver;
    }

    /**
     * @param mixed $resolver
     *
     * @throws \Exception
     */
    public function setCommonResolver($resolver)
    {
        if (!$resolver instanceof EventResolverInterface) {
            $this->commonResolver = $this->handleException(new InvalidServiceException(
                'Resolver Service must implement' . EventResolverInterface::class
            ));

            return;
        }

        $this->commonResolver = $resolver;
    }


    /**
     * @param \Exception $e
     * @throws \Exception
     * @return null
     */
    protected function handleException(\Exception $e)
    {
        if ($this->isDebug()) {
            throw $e;
        }

        return null;
    }

    /**
     * @param $eventInfo
     * @return null|BaseAuditLog
     * @throws \Exception
     */
    protected function createEventObjectFromArray($eventInfo)
    {
        if (!is_array($eventInfo)) {
            return $this->handleException(new UnrecognizedEventInfoException());
        }

        $auditLogClass = $this->entityClass;
        $eventObject = new $auditLogClass();

        if (!$eventObject instanceof BaseAuditLog) {
            return $this->handleException(new UnrecognizedEntityException());
        }

        return $eventObject->fromArray($eventInfo);
    }

    /**
     * @param $userProperty
     * @param $user
     * @return mixed
     */
    protected function getSettablePropertyValue($userProperty, $user)
    {
        if (empty($userProperty)) {
            return $user;
        }

        try {
            $propertyAccessor = PropertyAccess::createPropertyAccessor();
            return $propertyAccessor->getValue($user, $userProperty);
        } catch (NoSuchPropertyException $e) {
            return $this->handleException($e);
        }
    }

    /**
     * @param BaseAuditLog $entity
     * @param $userProperty
     */
    protected function setImpersonatingUser(BaseAuditLog $entity, $userProperty)
    {
        if (null !== $user = $this->getImpersonatingUser()) {
            $entity->setImpersonatingUser($this->getSettablePropertyValue($userProperty, $user));
        }
    }

    /**
     * @param mixed $debug
     */
    public function setDebug($debug)
    {
        $this->debug = $debug;
    }

    /**
     * @param EventResolverInterface $entityEventResolver
     */
    public function setEntityEventResolver($entityEventResolver)
    {
        $this->entityEventResolver = $entityEventResolver;
    }

    private function isDebug()
    {
        return $this->debug;
    }
}
