<?php

namespace Eltharin\InvitationsBundle\Service;


use Eltharin\InvitationsBundle\Entity\Invitation;
use Symfony\Bridge\Twig\Mime\TemplatedEmail;

abstract class AbstractInvitation
{
	abstract public function setMailContent(TemplatedEmail $email, Invitation $invitation);

	public function checkData(Invitation $invitation)
	{
		return true;
	}

	public function canResolve(Invitation $invitation)
	{
		return true;
	}

	public function canDelete(Invitation $invitation)
	{
		return true;
	}
}
