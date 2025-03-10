<?php

namespace Eltharin\InvitationsBundle\Service;

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
use Symfony\Component\DependencyInjection\ServiceLocator;

class InvitationManager
{
	private $invitationLocator;
	private $invitationRepository;
	private $invitationEntityManager;


	public function __construct(ServiceLocator $invitationLocator,
								InvitationRepository $invitationRepository,
								InvitationEntityManager $invitationEntityManager
	)
	{
		$this->invitationLocator = $invitationLocator;
		$this->invitationRepository = $invitationRepository;
		$this->invitationEntityManager = $invitationEntityManager;

	}

	public function findBy($criteres, $filterByType=true, $indexByItemId = false)
	{
        if(isset($criteres['type']) && !is_array($criteres['type']))
        {
            $criteres['type'] = [$criteres['type']];
            $filterByType = false;
        }

        $data = $this->invitationRepository->findBy($criteres);
        $ret = [];

        if(isset($criteres['type']) && $filterByType == true)
        {
            foreach($criteres['type'] as $type)
            {
                $ret[$type] = [];
            }
        }

        foreach($data as $row)
        {
            $pointer = &$ret;

            if($filterByType)
            {
                if(!array_key_exists($row->getType(), $pointer))
                {
                    $pointer[$row->getType()] = [];
                }
                $pointer = &$pointer[$row->getType()];
            }

            if($indexByItemId)
            {
                if(!array_key_exists($row->getItemId(), $pointer))
                {
                    $pointer[$row->getItemId()] = [];
                }
                $pointer = &$pointer[$row->getItemId()];
            }

            $pointer[] = $this->invitationEntityManager->setInvitation($row);
        }

		return $ret;
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
		$invitation->setToken(vsprintf('%s%s-%s-%s-%s-%s%s%s', str_split(bin2hex(random_bytes(16)), 4)));

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
