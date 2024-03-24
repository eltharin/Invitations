<?php

namespace Eltharin\InvitationsBundle;

use Eltharin\InvitationsBundle\Entity\InvitationUserInterface;
use Eltharin\InvitationsBundle\Repository\InvitationRepository;
use Eltharin\InvitationsBundle\Service\InvitationEntityManager;
use Eltharin\InvitationsBundle\Service\InvitationLocator;
use Eltharin\InvitationsBundle\Service\InvitationManager;
use Eltharin\CommonAssetsBundle\Service\SendMailService;
use Symfony\Component\Config\Definition\Configurator\DefinitionConfigurator;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator;
use Symfony\Component\HttpKernel\Bundle\AbstractBundle;

use function Symfony\Component\DependencyInjection\Loader\Configurator\service;
use function Symfony\Component\DependencyInjection\Loader\Configurator\tagged_locator;


class EltharinInvitationsBundle extends AbstractBundle
{
	public function prependExtension(ContainerConfigurator $container, ContainerBuilder $builder): void
	{
		$builder->prependExtensionConfig('doctrine',[
			'orm' => [
				'resolve_target_entities' => [
					'Eltharin\\InvitationsBundle\\Entity\\InvitationUserInterface' => 'App\\Entity\\User'
				]
			]
		]);

		$container->extension('doctrine',[
			'orm' => [
				'mappings' => [
					'EltharinInvitationsBundle' => [
					'is_bundle' => true,
					'prefix' => 'Eltharin\\InvitationsBundle\\Entity',
					'alias' => 'EltharinInvitations',
					]
				],
			]
		]);
	}
	
	public function loadExtension(array $config, ContainerConfigurator $container, ContainerBuilder $builder): void
	{
		$container->services()
			->set(InvitationRepository::class)
			->args([service('doctrine')])
			->tag('doctrine.repository_service')
		;

		$container->services()
			->set(InvitationEntityManager::class)
			->args([
				service('Symfony\Component\Routing\Generator\UrlGeneratorInterface'),
				service(InvitationLocator::class),
				service(InvitationRepository::class),
				service('Symfony\Component\Mailer\MailerInterface'),
				service('Symfony\Component\Mailer\Transport\TransportInterface'),
				service('Symfony\Component\DependencyInjection\ParameterBag\ContainerBagInterface'),
				service(SendMailService::class),
			])
		;

		$container->services()
			->set(InvitationLocator::class)
			->args([
				tagged_locator('app.invitation', 'key'),
			])
		;

		$container->services()
			->set(InvitationManager::class)
			->args([
				service(InvitationLocator::class),
				service(InvitationRepository::class),
				service(InvitationEntityManager::class),
			])
		;

		$container->services()
			->set(InvitationUserInterface::class)
		;
	}
}