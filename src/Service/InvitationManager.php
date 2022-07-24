<?php

namespace Eltharin\InvitationsBundle\Service;

use Eltharin\InvitationsBundle\Entity\Invitation;
use Eltharin\InvitationsBundle\Repository\InvitationRepository;
use Symfony\Bridge\Twig\Mime\TemplatedEmail;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\Mailer\Transport\TransportInterface;
use Symfony\Component\Security\Core\Security;

class InvitationManager
{
	private $invitationRepository;
	private $mailer;
	private $transport;

	public function __construct(InvitationRepository $invitationRepository, MailerInterface $mailer, TransportInterface $transport)
	{
		$this->invitationRepository = $invitationRepository;
		$this->mailer = $mailer;
		$this->transport = $transport;
	}

	public function create($class, $userFrom, $email, $data)
	{
		dump('create invitaiton');
		$invit = new Invitation();
		$invit->setType($class);
		$invit->setUser($userFrom);
		$invit->setEmail($email);
		$invit->setItemId(1);
		$invit->setData($data);

		$invit->setCreatedAt(new \DateTimeImmutable());
		$invit->setToken('123456-45987-549654-654654');

		$this->invitationRepository->add($invit, true);
		//$this->sendMail($invit);
	}

	protected function sendMail(Invitation $invitation)
	{
		$class = new ($invitation->getType());
		dump($class);

		$mail = new TemplatedEmail();
		$mail->from($this->transport->getUsername())
			->to($invitation->getEmail());

		$class->setMailContent($mail, $invitation);

		$this->mailer->send($mail);
	}

	public function reSendMail($invitationId)
	{

	}

	public function delete($invitationId)
	{

	}

	public function resolve($invitationId)
	{

	}


	/*
	private static function getOrDie($invitId)
	{
		$invit = (new \Specs\Tables\Invitation())->findWithRel()
			->where(['Invitation.INV_INVITATION' => $invitId])
			->first();

		if($invit == null)
		{
			throw new HTTPException('Invitation inconnue',403);
		}

		return $invit;
	}

	public static function getInvitation($email, $token)
	{
		$invit = (new \Specs\Tables\Invitation())
			->findWithRel()
			->where(['Invitation.INV_EMAIL' => $email])
			->where(['Invitation.INV_TOKEN' => $token])
			->first();

		return $invit;
	}

	public static function getAllInvitationFromEquipe($equipe, $type )
	{
		$invit = (new \Specs\Tables\Invitation())
			->findWithRel()
			->ijoin('collaborateur', 'COL_COLLABORATEUR = INV_ITEM_ID')
			->where(['Invitation.INV_TYPE' => $type])
			->where(['COL_EQUIPE' => $equipe])
			->select('COL_COLLABORATEUR', false,true)
			->fetchMode(\PDO::FETCH_GROUP | \PDO::FETCH_UNIQUE | \PDO::FETCH_OBJ)->all();


		return $invit;
	}

	public static function getAllInvitationFromType($itemId, $type )
	{
		$invit = (new \Specs\Tables\Invitation())
			->findWithRel()
			->where(['Invitation.INV_TYPE' => $type])
			->where(['Invitation.INV_ITEM_ID' => $itemId]);

		return $invit;
	}

	public static function getClassInvitType($type) : ?AbstractInvitationType
	{
		switch($type)
		{
			case 'JOINEQUIPE'      : return new JoinEquipe();break;
			case 'ACTIVEUSER'      : return new ActiveUser();break;
			case 'RESETPASSWORD'   : return new ResetPassword();break;
			case 'SETMANAGER'      : return new SetManager();break;
			case 'SETGESTIONNAIRE' : return new SetGestionnaire();break;
			//case '' : return ;break;
			default : 	trigger_error ('Type Invitation ' . $type . ' non géré', E_USER_ERROR);
				throw new HttpException('Type non géré.', 500);
				return null;
				break;
		}
	}

	public static function creer(string $type, array $data)
	{
		$invit = self::getClassInvitType($type)->creer($data);
		if($invit != null)
		{
			self::sendMail(Invitation::getOrDie($invit->id));
		}
	}

	public static function sendMail($invit)
	{
		$mail = new Mailer();
		$mail->addAddress ($invit->email);

		$link = $_SERVER['REQUEST_SCHEME'] . '://' . $_SERVER['SERVER_NAME'] . '/invitation/accept/' . base64_encode($invit->email) . '/' . $invit->token;
		self::getClassInvitType($invit->type)->setMailInfos($mail, $link, $invit);
		$mail->send();
	}

	public static function acceptInvit($invit) : Response
	{
		return self::getClassInvitType($invit->type)->accept($invit);
	}

	public static function deleteInvit(Entity $invit)
	{
		(new \Specs\Tables\Invitation())->DBDelete($invit);
	}

	public static function findFor(string $string, $idEquipe)
	{
	}

	public static function canDelete($invit)
	{
		return self::getClassInvitType($invit->type)->canDelete($invit);
	}*/
}
