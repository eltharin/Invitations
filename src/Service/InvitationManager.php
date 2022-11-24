<?php

namespace Eltharin\InvitationsBundle\Service;

use Eltharin\CommonAssetsBundle\Service\Token;
use Eltharin\InvitationsBundle\Entity\Invitation;
use Eltharin\InvitationsBundle\Repository\InvitationRepository;
use phpDocumentor\Reflection\DocBlock\Tags\Deprecated;
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

	public function findBy($criteres, $filterByType=true)
	{
		$data = $this->invitationRepository->findBy($criteres);

		if(isset($criteres['type']) && $filterByType == true)
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
		else
		{
			$data = array_map(function ($invit) {return $this->invitationEntityManager->setInvitation($invit);}, $data);
		}

		return $data;
	}

	public function getInvit($invitationId, array $params = []) : ?InvitationEntityManager
	{
		$criteres = ['id' => $invitationId];

		if(isset($params['token']))
		{
			$criteres['token'] = $params['token'];
		}

		if(isset($params['class']))
		{
			@trigger_error('use type instead', \E_USER_DEPRECATED);
			$criteres['type'] = $params['class'];
		}

		if(isset($params['type']))
		{
			$criteres['type'] = $params['type'];
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
			if(!empty($oldInvit = $this->invitationRepository->findBy([
					'type' => $classType,
					'email' => $email,
					'itemId' => $itemId
				]))
				|| $classInvit->isDoublon($invitation))
			{
				if($classInvit->resendMailIfAlreadyExists())
				{
					$invit = $this->invitationEntityManager->setInvitation($oldInvit[0])->sendMail();
					return $invit;
				}
				else
				{
					throw new AlreadyExistsException('Invitation déjà envoyée');
					return false;
				}
			}
		}

		$invitation->setCreatedAt(new \DateTimeImmutable());
		$invitation->setToken(Token::generate());

		if(!$classInvit->canCreate($invitation))
		{
			unset($invitation);
			throw new CantCreateInvitationException('Impossible de créér l\'Invitation');
			return false;
		}

		$this->invitationRepository->add($invitation, true);

		$invit = $this->invitationEntityManager->setInvitation($invitation);
		$invit->sendMail();

		return $invit;
	}
}
