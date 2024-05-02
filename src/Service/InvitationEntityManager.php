<?php

namespace Eltharin\InvitationsBundle\Service;

use Eltharin\CommonAssetsBundle\Service\SendMailService;
use Eltharin\InvitationsBundle\Entity\Invitation;
use Eltharin\InvitationsBundle\Exception\TooEarlyException;
use Eltharin\InvitationsBundle\Repository\InvitationRepository;
use Symfony\Bridge\Twig\Mime\TemplatedEmail;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\Mailer\Transport\TransportInterface;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Component\DependencyInjection\ParameterBag\ContainerBagInterface;
use Symfony\Component\Validator\Constraints\DateTime;
use Symfony\Component\DependencyInjection\ServiceLocator;


class InvitationEntityManager
{
	private $invitation;

	public function __construct(
		private UrlGeneratorInterface $urlGenerator,
		private ServiceLocator $invitationLocator,
		private InvitationRepository $invitationRepository,
		private MailerInterface $mailer,
		private ContainerBagInterface $params
	)
	{
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
		if($this->invitation->getLastSendAt() != null)
		{
			$dateCanSend = $this->invitation->getLastSendAt()->add(new \DateInterval('PT' . ((int)$this->params->has('app.delayBeetween2Mails') ? $this->params->get('app.delayBeetween2Mails') : 10) . 'S'));
			if($dateCanSend >= new \DateTime())
			{
				throw new TooEarlyException('Vous devez attendre ' . ($dateCanSend->getTimeStamp() - (new \DateTime)->getTimestamp() + 1) . ' secondes pour renvoyer ce mail.');
			}
		}

		$classInvit = $this->invitationLocator->get($this->invitation->getType());

		$mail = (new TemplatedEmail())
					->addTo($this->invitation->getEmail())
					->context([
						'invitation_url' => $this->getResolvePath(true),
						'invitation' => $this->getInvitation()
					])
			;


		$classInvit->setMailContent($mail, $this);


		$this->mailer->send($mail);

		$this->invitation->setLastSendAt(new \DateTime());
		$this->invitationRepository->flush();
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

	public function getClassInvit()
	{
		return $this->invitationLocator->get($this->invitation->getType());
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
