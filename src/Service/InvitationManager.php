<?php

namespace Eltharin\InvitationsBundle\Service;

use Eltharin\CommonAssetsBundle\Service\Token;
use Eltharin\InvitationsBundle\Entity\Invitation;
use Eltharin\InvitationsBundle\Repository\InvitationRepository;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\Mailer\Transport\TransportInterface;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Component\Security\Core\Security;
use Symfony\Component\Translation\Exception\NotFoundResourceException;
use Eltharin\InvitationsBundle\Exception\AlreadyExistsException;
use Eltharin\InvitationsBundle\Exception\CantCreateInvitationException;
use Eltharin\InvitationsBundle\Service\InvitationEntityManager;

class InvitationManager
{
	private $invitationLocator;
	private $invitationRepository;
	private $invitationEntityManager;


	public function __construct(InvitationLocator $invitationLocator,
								InvitationRepository $invitationRepository,
								InvitationEntityManager $invitationEntityManager
	)
	{
		$this->invitationLocator = $invitationLocator;
		$this->invitationRepository = $invitationRepository;
		$this->invitationEntityManager = $invitationEntityManager;

	}

	public function findBy($criteres)
	{
		$data = $this->invitationRepository->findBy($criteres);

		if(isset($criteres['type']))
		{
			$ret = [];
			foreach($criteres['type'] as $type)
			{
				$ret[$type] = [];
			}

			foreach($data as $row)
			{
				$ret[$row->getType()][] = $this->invitationEntityManager->setInvitation($row);
			}
			$data = $ret;
		}

		return $data;
	}

	public function getInvit($invitationId, array $params) : ?InvitationEntityManager
	{
		$criteres = ['id' => $invitationId];

		if(isset($params['token']))
		{
			$criteres['token'] = $params['token'];
		}

		if(isset($params['class']))
		{
			$criteres['type'] = $params['class'];
		}

		if(isset($params['owner']))
		{
			$criteres['user'] = $params['owner'];
		}


		$invit = $this->invitationRepository->findOneBy($criteres);
		if($invit == null)
		{
			return null;
		}

		return $this->invitationEntityManager->setInvitation($invit);
	}

	public function create($classType, $userFrom, $email, $itemId, $data = [])
	{
		$classInvit = $this->invitationLocator->get($classType);

		$invitation = new Invitation();
		$invitation->setType($classType);
		$invitation->setUser($userFrom);
		$invitation->setEmail($email);
		$invitation->setItemId($itemId);
		$invitation->setData($data);

		if($classInvit->mustBeUnique())
		{
			if(!empty($this->invitationRepository->findBy([
					'type' => $classType,
					'email' => $email,
					'itemId' => $itemId
				]))
				|| $classInvit->isDoublon($invitation))
			{
				throw new AlreadyExistsException('Invitation déjà envoyée');
				return false;
			}
		}

		$invitation->setCreatedAt(new \DateTimeImmutable());
		$invitation->setToken(Token::generate());

		if(!$classInvit->canCreate($invitation))
		{
			unset($invitation);
			throw new CantCreateInvitationException('Invitation déjà envoyée');
			return false;
		}

		$this->invitationRepository->add($invitation, true);

		$invit = $this->invitationEntityManager->setInvitation($invitation);
		$invit->sendMail();

		return $invit;
	}
}
