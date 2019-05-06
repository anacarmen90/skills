<?php


namespace SkillsBundle\Admin;

use Sonata\AdminBundle\Admin\AbstractAdmin;
use Sonata\AdminBundle\Datagrid\DatagridMapper;
use Sonata\AdminBundle\Datagrid\ListMapper;
use Sonata\AdminBundle\Form\FormMapper;

/**
 * Class EndorsementAdmin
 *
 * @package SkillsBundle\Admin
 */
class EndorsementAdmin extends AbstractAdmin
{

    /**
     * {@inheritdoc}
     */
    protected function configureFormFields(FormMapper $formMapper)
    {
        $formMapper
          ->add('endorser', 'sonata_type_model_autocomplete', ['property' => 'fullName'])
          ->add('endorsee', 'sonata_type_model_autocomplete', ['property' => 'fullName'])
          ->add('skill', 'sonata_type_model', ['property' => 'name']);
    }

    /**
     * {@inheritdoc}
     */
    protected function configureDatagridFilters(DatagridMapper $filter)
    {
        $filter
          ->add('endorser')
          ->add('endorsee')
          ->add('skill');
    }

    /**
     * {@inheritdoc}
     */
    protected function configureListFields(ListMapper $list)
    {
        $list
          ->addIdentifier('id')
          ->add('endorser.fullName')
          ->add('endorsee.fullName')
          ->add('skill.name');
    }
}