<?php

namespace Eltharin\InvitationsBundle\Service;

use Eltharin\InvitationsBundle\Entity\Invitation;
use Eltharin\InvitationsBundle\Exception\AlreadyExistsException;
use Eltharin\InvitationsBundle\Repository\InvitationRepository;
use Symfony\Bridge\Twig\Mime\TemplatedEmail;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\Mailer\Transport\TransportInterface;
use Symfony\Component\Translation\Exception\NotFoundResourceException;

abstract class AbstractInvitation
{
	abstract public function setMailContent(TemplatedEmail $email, InvitationEntityManager $invitation) : void;

	abstract public function getResolvePath(InvitationEntityManager $invitation) : array;

	abstract public function resolve(InvitationEntityManager $invitation) : bool;

	public function checkData(Invitation $invitation) : bool
	{
		return true;
	}

	public function canCreate(Invitation $invitation) : bool
	{
		return true;
	}

	public function canResolve(InvitationEntityManager $invitation) : bool
	{
		return true;
	}

	public function canDelete(Invitation $invitation) : bool
	{
		return true;
	}

	public function mustBeUnique() : bool
	{
		return true;
	}

	public function isDoublon(Invitation $invitation) : bool
	{
		return false;
	}
}
