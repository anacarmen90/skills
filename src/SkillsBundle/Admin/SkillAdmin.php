<?php

namespace SkillsBundle\Admin;

use Sonata\AdminBundle\Admin\AbstractAdmin;
use Sonata\AdminBundle\Datagrid\DatagridMapper;
use Sonata\AdminBundle\Datagrid\ListMapper;
use Sonata\AdminBundle\Form\FormMapper;
use Sonata\AdminBundle\Show\ShowMapper;
use Sonata\CoreBundle\Form\Type\CollectionType;

class SkillAdmin extends AbstractAdmin
{

    /**
     * {@inheritdoc}
     */
    protected function configureFormFields(FormMapper $formMapper)
    {
        $formMapper
          ->add('name')
          ->add(
            'description',
            null,
            ['help' => 'Description/keywords for this skill, to help find it']
          )
          ->add(
            'tips',
            null,
            ['help' => 'Tips for self-evaluating the expertise level']
          )
          ->add(
            'categories',
            'sonata_type_model',
            [
              'property' => 'name',
              'required' => false,
              'multiple' => true,
//              'minimum_input_length' => 1,
            ]
          );
    }

    /**
     * {@inheritdoc}
     */
    protected function configureShowFields(ShowMapper $show)
    {
        $show
          ->add('name')
          ->add('description')
          ->add(
            'categories',
            'entity',
            ['class' => 'SkillsBundle:Category']
          );
    }

    /**
     * {@inheritdoc}
     */
    protected function configureDatagridFilters(DatagridMapper $filter)
    {
        $filter
          ->add('name')
          ->add('description');
    }

    /**
     * {@inheritdoc}
     */
    protected function configureListFields(ListMapper $list)
    {
        $list
          ->addIdentifier('name')
          ->add('description')
          ->add('categories', null, ['associated_property' => 'name']);
    }
}