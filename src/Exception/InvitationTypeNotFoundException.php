<?php

namespace Eltharin\InvitationsBundle\Exception;

use Symfony\Component\HttpKernel\Exception\HttpException;

class InvitationTypeNotFoundException extends HttpException
{
	public function __construct(string $message = '', int $statusCode = 404, \Throwable $previous = null, array $headers = [], int $code = 0)
	{
		parent::__construct($statusCode, $message, $previous, $headers, $code);
	}
}
