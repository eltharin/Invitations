<?php

namespace Eltharin\InvitationsBundle\Service;

use Eltharin\InvitationsBundle\Entity\Invitation;
use Eltharin\InvitationsBundle\Repository\InvitationRepository;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\Mailer\Transport\TransportInterface;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Bridge\Twig\Mime\TemplatedEmail;


class InvitationEntityManager
{
	private $invitation;
	private $urlGenerator;
	private $invitationLocator;
	private $invitationRepository;
	private $mailer;
	private $transport;

	public function __construct(
		UrlGeneratorInterface $urlGenerator,
		InvitationLocator $invitationLocator,
		InvitationRepository $invitationRepository,
		MailerInterface $mailer,
		TransportInterface $transport
	)
	{
		$this->urlGenerator = $urlGenerator;
		$this->invitationLocator = $invitationLocator;
		$this->invitationRepository = $invitationRepository;
		$this->mailer = $mailer;
		$this->transport = $transport;
	}

	public function setInvitation(Invitation $invitation)
	{
		$iem = clone($this);
		$iem->invitation = $invitation;
		return $iem;
	}

	public function getInvitation() : Invitation
	{
		return $this->invitation;
	}


	public function __call(string $name, array $arguments)
	{
		return call_user_func_array([$this->invitation, $name], $arguments);
	}

	public function getResolvePath($absolute = false)
	{
		$classInvit = $this->invitationLocator->get($this->invitation->getType());
		$resolvePath = $classInvit->getResolvePath($this);

		return $this->urlGenerator->generate($resolvePath['path'], $resolvePath['args'], $absolute ? UrlGeneratorInterface::ABSOLUTE_URL : UrlGeneratorInterface::ABSOLUTE_PATH);
	}

	public function sendMail()
	{
		$classInvit = $this->invitationLocator->get($this->invitation->getType());

		$mail = new TemplatedEmail();
		$mail->from($this->transport->getUsername())
			->to($this->invitation->getEmail());

		$classInvit->setMailContent($mail, $this);
		$this->mailer->send($mail);
	}

	public function resolve($deleteAfterSuccess = true) :bool
	{
		$classInvit = $this->invitationLocator->get($this->invitation->getType());

		if(	$classInvit->canResolve($this))
		{
			if($classInvit->resolve($this) && $deleteAfterSuccess)
			{
				$this->delete();
				return true;
			}
		}

		return false;
	}

	public function delete()
	{
		$classInvit = $this->invitationLocator->get($this->invitation->getType());

		if(	$classInvit->canDelete($this->invitation))
		{
			$this->invitationRepository->remove($this->invitation, true);
		}
	}
}