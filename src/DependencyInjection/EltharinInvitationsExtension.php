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
	}
	
	public function prepend(ContainerBuilder $container)
	{
		$yamlParser = new Parser();
		$doctrineConfig = $yamlParser->parse(file_get_contents(__DIR__ . '/../Resources/config/doctrine.yaml'));
		$container->prependExtensionConfig('doctrine', $doctrineConfig['doctrine']);
	}
}
