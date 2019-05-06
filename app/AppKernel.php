<?php

use Symfony\Component\HttpKernel\Kernel;
use Symfony\Component\Config\Loader\LoaderInterface;

class AppKernel extends Kernel
{

    public function registerBundles()
    {
        $bundles = [
          new Symfony\Bundle\FrameworkBundle\FrameworkBundle(),
          new Symfony\Bundle\SecurityBundle\SecurityBundle(),
          new Symfony\Bundle\TwigBundle\TwigBundle(),
          new Symfony\Bundle\MonologBundle\MonologBundle(),
          new Symfony\Bundle\SwiftmailerBundle\SwiftmailerBundle(),
          new Doctrine\Bundle\DoctrineBundle\DoctrineBundle(),
          new Sensio\Bundle\FrameworkExtraBundle\SensioFrameworkExtraBundle(),
          new FOS\UserBundle\FOSUserBundle(),
          new FOS\ElasticaBundle\FOSElasticaBundle(),
          new Sonata\CoreBundle\SonataCoreBundle(),
          new Sonata\BlockBundle\SonataBlockBundle(),
          new Knp\Bundle\MenuBundle\KnpMenuBundle(),
          new SimpleThings\EntityAudit\SimpleThingsEntityAuditBundle(),
          new Sonata\AdminBundle\SonataAdminBundle(),
          new Sonata\DoctrineORMAdminBundle\SonataDoctrineORMAdminBundle(),
          new FR3D\LdapBundle\FR3DLdapBundle(),
          new AppBundle\AppBundle(),
          new SkillsBundle\SkillsBundle(),
          new JMS\SerializerBundle\JMSSerializerBundle(),
          new AnalyticsBundle\AnalyticsBundle(),
        ];

        if (in_array($this->getEnvironment(), ['dev', 'test'], true)) {
            $bundles[] = new Symfony\Bundle\DebugBundle\DebugBundle();
            $bundles[] = new Symfony\Bundle\WebProfilerBundle\WebProfilerBundle();
            $bundles[] = new Sensio\Bundle\DistributionBundle\SensioDistributionBundle();

            if ('dev' === $this->getEnvironment()) {
                $bundles[] = new Sensio\Bundle\GeneratorBundle\SensioGeneratorBundle();
                $bundles[] = new Symfony\Bundle\WebServerBundle\WebServerBundle();
            }
        }

        return $bundles;
    }

    public function getRootDir()
    {
        return __DIR__;
    }

    public function getCacheDir()
    {
        return dirname(__DIR__).'/var/cache/'.$this->getEnvironment();
    }

    public function getLogDir()
    {
        return dirname(__DIR__).'/var/logs';
    }

    public function registerContainerConfiguration(LoaderInterface $loader)
    {
        $loader->load(
          $this->getRootDir().'/config/config_'.$this->getEnvironment().'.yml'
        );
    }
}
