<?php

namespace SkillsBundle\Admin;

use Sonata\AdminBundle\Admin\AbstractAdmin;
use Sonata\AdminBundle\Datagrid\DatagridMapper;
use Sonata\AdminBundle\Datagrid\ListMapper;
use Sonata\AdminBundle\Form\FormMapper;

class CategoryAdmin extends AbstractAdmin
{

    /**
     * (@inheritdoc)
     */
    protected function configureFormFields(FormMapper $formMapper)
    {
        $formMapper
            ->add('name');
    }

    /**
     * (@inheritdoc)
     */
    protected function configureDatagridFilters(DatagridMapper $filter)
    {
        $filter
            ->add('name');
    }

    /**
     * (@inheritdoc)
     */
    protected function configureListFields(ListMapper $list)
    {
        $list
            ->addIdentifier('name');
    }
}
