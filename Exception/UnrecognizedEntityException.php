<?php
/**
 * Created by PhpStorm.
 * User: Only For Me
 * Date: 28-10-2014
 * Time: 10:47 PM
 */

namespace Xiidea\EasyAuditBundle\Exception;


class UnrecognizedEntityException extends \Exception {

    protected $message = "Entity must extend Xiidea\\EasyAuditBundle\\Entity\\BaseAuditLog";
} 