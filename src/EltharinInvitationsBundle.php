<?php

namespace Eltharin\InvitationsBundle;

use Eltharin\InvitationsBundle\Repository\InvitationRepository;
use Eltharin\InvitationsBundle\Service\InvitationEntityManager;
use Eltharin\InvitationsBundle\Service\InvitationLocator;
use Eltharin\InvitationsBundle\Service\InvitationManager;
use Eltharin\CommonAssetsBundle\Service\SendMailService;
use Symfony\Component\Config\FileLocator;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator;
use Symfony\Component\DependencyInjection\Loader\YamlFileLoader;
use Symfony\Component\HttpKernel\Bundle\AbstractBundle;
use Symfony\Component\HttpKernel\Bundle\Bundle;
use Symfony\Component\Routing\Loader\Configurator\RouteConfigurator;
use Symfony\Component\Yaml\Parser;

use function Symfony\Component\DependencyInjection\Loader\Configurator\service;
use function Symfony\Component\DependencyInjection\Loader\Configurator\service_locator;
use function Symfony\Component\DependencyInjection\Loader\Configurator\tagged_locator;


class EltharinInvitationsBundle extends AbstractBundle
{
	public function loadExtension(array $config, ContainerConfigurator $container, ContainerBuilder $builder): void
	{
		$yamlParser = new Parser();
		$doctrineConfig = $yamlParser->parse(file_get_contents(__DIR__ . '/../config/packages/doctrine.yaml'));
		$builder->prependExtensionConfig('doctrine', $doctrineConfig['doctrine']);

		$container->parameters()->set('app.delayBeetween2Mails', '%env(DELAY_BEETWEEN_2_INVITATION_MAILS)%');
		$container->parameters()->set('env(DELAY_BEETWEEN_2_INVITATION_MAILS)', '300');

		//$container->import(__DIR__.'/../config/services.yaml');

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
	}
}