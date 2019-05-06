<?php

namespace AppBundle\Admin;

use Sonata\AdminBundle\Admin\AbstractAdmin;
use Sonata\AdminBundle\Datagrid\ListMapper;
use Sonata\AdminBundle\Datagrid\DatagridMapper;
use Sonata\AdminBundle\Form\FormMapper;
use Sonata\AdminBundle\Route\RouteCollection;

class ZUserAdmin extends AbstractAdmin
{
    /**
     * (@inheritdoc)
     */
    public function configureRoutes(RouteCollection $collection)
    {
        $collection->remove('create');
    }

    /**
     * (@inheritdoc)
     */
    protected function configureFormFields(FormMapper $formMapper)
    {
        $options = ['property' => 'name', 'multiple' => true, 'required' => false];
        $formMapper
          ->add('username', null, ['disabled' => true])
          ->add('fullName', null, ['disabled' => true])
          ->add('projects', 'sonata_type_model', $options)
        ;
    }

    /**
     * (@inheritdoc)
     */
    protected function configureDatagridFilters(DatagridMapper $datagridMapper)
    {
        $datagridMapper->add('fullName');
    }

    /**
     * (@inheritdoc)
     */
    protected function configureListFields(ListMapper $listMapper)
    {
        $listMapper
          ->addIdentifier('username')
          ->add('projects', null, ['associated_property' => 'name'])
        ;
    }
}
