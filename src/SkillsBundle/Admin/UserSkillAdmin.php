<?php

namespace SkillsBundle\Admin;

use Sonata\AdminBundle\Admin\AbstractAdmin;
use Sonata\AdminBundle\Datagrid\DatagridMapper;
use Sonata\AdminBundle\Datagrid\ListMapper;
use Sonata\AdminBundle\Form\FormMapper;

/**
 * Class UserSkill
 *
 * @Audit\Auditable()
 *
 * @package SkillsBundle\Admin
 */
class UserSkillAdmin extends AbstractAdmin
{

    /**
     * {@inheritdoc}
     */
    protected function configureFormFields(FormMapper $formMapper)
    {
        $formMapper
          ->add('user' )
          ->add('skill', 'sonata_type_model_autocomplete', ['property' => 'name'])
          ->add('level');
    }

    /**
     * {@inheritdoc}
     */
    protected function configureDatagridFilters(DatagridMapper $filter)
    {
        $filter
          ->add('user')
          ->add('skill')
          ->add('level')
        ;
    }

    /**
     * {@inheritdoc}
     */
    protected function configureListFields(ListMapper $list)
    {
        $list
          ->addIdentifier('id')
          ->add('user')
          ->add('skill.name')
          ->add('level')
        ;
    }
}