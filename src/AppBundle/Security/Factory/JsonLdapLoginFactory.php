<?php

namespace AppBundle\Security\Factory;

use Symfony\Bundle\SecurityBundle\DependencyInjection\Security\Factory\JsonLoginFactory;
use Symfony\Component\DependencyInjection\ChildDefinition;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Reference;

/**
 * Class JsonLdapLoginFactory
 *
 * @package AppBundle\Security\Factory
 */
class JsonLdapLoginFactory extends JsonLoginFactory
{

    /**
     * {@inheritdoc}
     */
    public function getKey()
    {
        return 'json-ldap-login';
    }

    protected function createAuthProvider(ContainerBuilder $container, $id, $config, $userProviderId)
    {
        $provider = 'fr3d_ldap.security.authentication.provider';
        $providerId = $provider . '.' . $id;

        $container
          ->setDefinition($providerId, new ChildDefinition($provider))
          ->replaceArgument(1, $id) // Provider Key
          ->replaceArgument(2, new Reference($userProviderId)) // User Provider
        ;

        return $providerId;
    }
}