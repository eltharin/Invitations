Symfony Invitaitons Bundle
==========================

[![Latest Stable Version](http://poser.pugx.org/eltharin/invitations/v)](https://packagist.org/packages/eltharin/invitations) 
[![Total Downloads](http://poser.pugx.org/eltharin/invitations/downloads)](https://packagist.org/packages/eltharin/invitations) 
[![Latest Unstable Version](http://poser.pugx.org/eltharin/invitations/v/unstable)](https://packagist.org/packages/eltharin/invitations) 
[![License](http://poser.pugx.org/eltharin/invitations/license)](https://packagist.org/packages/eltharin/invitations)

Installation
------------

* Require the bundle with composer:

``` bash
composer require eltharin/invitaitons

php bin/console make:migration
php bin/console d:m:m 

to create invitaitons table
```



What is Invitations Bundle?
---------------------------
This bundle will help you to work with chained mail actions.

When you have to send a mail for allowing an action this bundle will helps you.



How to ? 
-----------

For the example we will set the forget-password workflow: 

- User go to page /forget-password
- type his mail
- receive a mail a clic on the link inside
- set his new password
- enjoy


no changes is required on User entity just a new table for all invitations.

create a FormType for reset password with just an email asked and one other for the new password : 

```php
class ForgetPasswordType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('email', EmailType::class, [
				'label' => 'Type your email'
            ])
        ;
    }
}

class ResetPasswordType extends AbstractType
{
	public function buildForm(FormBuilderInterface $builder, array $options): void
	{
		$builder
			->add('password', PasswordType::class, [
				'label' => 'Type the new password',
				'help_html' => true,
			])
		;
	}
}

```

now we will create an "Invitation":
Class must extends from AbstractInvitation and should be tagged with app.invitation

```php
#[Autoconfigure(tags: ['app.invitation'])]
class ResetPassword extends AbstractInvitation
{
	public function setMailContent(TemplatedEmail $email, InvitationEntityManager $invitationEntityManagerManager): void
	{
		$email->setSubject('Reset password')
			->setTemplatedBody('mail/invitations/resetpassword.html.twig');
	}

	public function resolve(InvitationEntityManager $invitationEntityManagerManager): bool
	{
	    // nothing here all is treated in controller
		return true;
	}

	public function getResolvePath(InvitationEntityManager $invitationEntityManagerManager) : array
	{
		return [
			'path' => 'app_reset_password',
			'args' => ['id' => $invitationEntityManagerManager->getInvitation()->getId(), 'token' => $invitationEntityManagerManager->getInvitation()->getToken()],
		];
	}

	public function resendMailIfAlreadyExists(): bool
	{
		return true;
	}
}
```

the mail template will contains the link : 

```twig
Reset your password : 
<a href="{{ include(template_from_string(invitation_url)) }}">Link</a>
```

now the first route in security controller for show the form and send the mail : 

```php
	#[Route(path: '/forget-password', name: 'app_forget_password')]
	public function forgetPassword(
		Request $request,
		UserRepository $userRepository,
		InvitationManager $invitationManager
	): Response
	{
		$form = $this->createForm(ForgetPasswordType::class);
		$form->handleRequest($request);

		if($form->isSubmitted() && $form->isValid())
		{
			$user = $userRepository->findOneByEmail($form->get('email')->getData());

			if($user != null)
			{
				$invitationManager->create(ResetPassword::class, $user, $form->get('email')->getData(),0);
			}

			return $this->render('message.html.twig', [
				'message' => 'If you mail is on our base, we sent you a message for reset your password',
			]);
		}

		return $this->render('security/reset_password/reset_password_request.html.twig', [
			'form' => $form->createView()
		]);
	}

```

Now the route call when link inside mail is clicked : 

```php
	#[Route(path: '/reset-password', name: 'app_reset_password')]
	public function resetPassword(
		Request $request,
		InvitationManager $invitationManager,
		EntityManagerInterface $em,
		UserPasswordHasherInterface $passwordHasher
	): Response
	{
		$invitation = $invitationManager->getInvit($request->query->get('id'), ['type' => [ResetPassword::class], 'token' => $request->query->get('token')]);
		if($invitation == null)
		{
			throw new ItemNotFoundException('Unkwnon Invitation');
		}

		$user = $invitation->getInvitation()->getUser();
		$form = $this->createForm(ResetPasswordType::class, $user, ['action' => $invitation->getResolvePath(true)]);
		$form->handleRequest($request);

		if($form->isSubmitted() && $form->isValid())
		{
			$user->setPassword($passwordHasher->hashPassword($user,$user->getPassword()));
			$em->flush();

			$invitation->resolve(true);

			$this->addFlash('success', 'Your password has been changed.');
			return $this->redirectToRoute('app_login');
		}

		return $this->render('security/reset_password/reset_password_request.html.twig', [
			'form' => $form->createView()
		]);
	}
```

As you can see you have to write code for the logic of the action you want but nothing for send a mail, get the token, reteive for witch user, all is done for you.