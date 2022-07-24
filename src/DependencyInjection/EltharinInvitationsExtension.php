<?php

namespace Eltharin\InvitationsBundle\DependencyInjection;

use Symfony\Component\Config\FileLocator;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Extension\Extension;
use Symfony\Component\DependencyInjection\Extension\PrependExtensionInterface;
use Symfony\Component\DependencyInjection\Loader\YamlFileLoader;
use Symfony\Component\Yaml\Parser;

class EltharinInvitationsExtension extends Extension implements PrependExtensionInterface
{
	public function load(array $configs, ContainerBuilder $container)
	{
		/*$yamlParser = new Parser();
		$doctrineConfig = $yamlParser->parse(file_get_contents(__DIR__ . '/../Resources/config/services.yaml'));
		$container->prependExtensionConfig('twig', $doctrineConfig['twig']);
		*/
		dd($container->getCompilerPassConfig());


		$loader = new YamlFileLoader(
			$container,
			new FileLocator(__DIR__.'/../Resources/config')
		);
		$loader->load('services.yaml');

		/*$routeImporter = new RouteImporter($container);
		$routeImporter->addObjectResource($this);
		$routeImporter->import('@EltharinInvitations/Resources/config/routing/routing.yml', 'frontend');*/
/*
		$container->register()
			->setPublic(false)
			->addTag()
*/
	}

	public function prepend(ContainerBuilder $container)
	{
		//$yamlParser = new Parser();
		//$config = $yamlParser->parse(file_get_contents(__DIR__ . '/../Resources/config/routes.yaml'));

		//dump($container->getParameter('routes'));
		//$container->prependExtensionConfig('routes', $config);
	}
}
