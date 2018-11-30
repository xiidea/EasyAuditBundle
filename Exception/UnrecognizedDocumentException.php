<?php

/*
 * This file is part of the XiideaEasyAuditBundle package.
 *
 * (c) Xiidea <http://www.xiidea.net>
 *
 * This source file is subject to the MIT license that is bundled
 * with this source code in the file LICENSE.
 */

namespace Xiidea\EasyAuditBundle\Exception;

class UnrecognizedDocumentException extends \Exception
{
    protected $message = "Document must extend Xiidea\\EasyAuditBundle\\Document\\BaseAuditLog";
}
