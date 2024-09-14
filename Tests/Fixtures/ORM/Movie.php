<?php

/*
 * This file is part of the XiideaEasyAuditBundle package.
 *
 * (c) Xiidea <http://www.xiidea.net>
 *
 * This source file is subject to the MIT license that is bundled
 * with this source code in the file LICENSE.
 */

namespace Xiidea\EasyAuditBundle\Tests\Fixtures\ORM;

use Doctrine\ORM\Mapping as ORM;
use Xiidea\EasyAuditBundle\Attribute\SubscribeDoctrineEvents;

/**
 * @ORM\Entity
 */
#[SubscribeDoctrineEvents([])]
#[ORM\Entity]
class Movie
{
    /**
     * @ORM\Id
     * @ORM\Column(type="integer")
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    #[ORM\Id]
    #[ORM\Column(type: 'integer')]
    #[ORM\GeneratedValue(strategy: "AUTO")]
    protected $id;

    /**
     * @ORM\Column(type="string")
     */
    #[ORM\Column(type: 'string')]
    protected $name;

    public function __construct($id = 1, $name = 'car')
    {
        $this->id = $id;
        $this->name = $name;
    }

    public function getName()
    {
        return $this->name;
    }

    public function setName($name)
    {
        $this->name = $name;
    }

    /**
     * @return mixed
     */
    public function getId()
    {
        return $this->id;
    }
}
