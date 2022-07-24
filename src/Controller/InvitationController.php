<?php

namespace Eltharin\InvitationsBundle\Controller;

use App\Invitations\Test;
use Eltharin\InvitationsBundle\Entity\Invitation;

use Eltharin\InvitationsBundle\Service\InvitationManager;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class InvitationController extends AbstractController
{
	#[Route(path: '/testresolve/{token}', name: 'eltharin_invitation.resolve')]
	public function testresolve(InvitationManager $invitationManager, Invitation $invitation): Response
	{

		dd($invitation);
		//$invitationManager->create(Test::class, 'eltharin18@hotmail.fr', ['toto' => 'tutu']);


		return $this->render('home/home.html.twig');
	}
}
