<?php

namespace Eltharin\InvitationsBundle\Service;

use App\Invitation\AccessList;
use App\Invitation\ManageList;
use Eltharin\InvitationsBundle\Exception\InvitationTypeNotFoundException;
use Psr\Container\ContainerInterface;
use Symfony\Contracts\Service\ServiceSubscriberInterface;


class InvitationLocator implements ServiceSubscriberInterface
{

	private $locator;

	public function __construct(ContainerInterface $locator)
	{
		$this->locator = $locator;
	}

	public static function getSubscribedServices(): array
	{
		return [
		];
	}

	public function get($commandClass) : AbstractInvitation
	{
		if ($this->locator->has($commandClass))
		{
			$handler = $this->locator->get($commandClass);

			return $handler;
		}
		else
		{
			throw new InvitationTypeNotFoundException($commandClass . ' inconnu');
		}
	}

}
