<?php

namespace Eltharin\InvitationsBundle\Routing;

use Symfony\Component\Config\Loader\Loader;

class RouteLoader extends Loader
{
	public function load($resource, string $type = null)
	{
		$routes = new RouteCollection();

		$resource = '@EltharinInvitations/Resources/config/routes.yaml';
		$type = 'yaml';

		$importedRoutes = $this->import($resource, $type);

		$routes->addCollection($importedRoutes);

		return $routes;
	}

	public function supports($resource, string $type = null)
	{
		return 'advanced_extra' === $type;
	}
}
