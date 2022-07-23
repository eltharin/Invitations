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

}
