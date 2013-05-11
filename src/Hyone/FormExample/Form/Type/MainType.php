<?php

namespace Hyone\FormExample\Form\Type;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Validator\Constraints as Assert;


class MainType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder->add('name', 'text', array(
            'constraints' => new Assert\NotBlank(),
            'label'       => 'name',
        ));
        $builder->add('born', 'date', array(
            'input'  => 'string',
            // 'input'  => 'datetime',
            'widget' => 'choice',
            // 'widget' => 'single_text',
            'years'  => range(2000, 2013),
            'format' => 'yyyyMMdd',
            'label'  => 'born',
        ));
        $builder->add('mail', 'text', array(
            'constraints' => array(
                new Assert\NotBlank(),
                new Assert\Email(),
            ),
            'label'       => 'mail',
        ));
        $builder->add('sex', 'choice', array(
            'choices'     => array(1 => 'male', 2 => 'female'),
            'expanded'    => true,
            'constraints' => array(
                new Assert\NotBlank(),
                new Assert\Choice(array(1, 2)),
            ),
            'label'       => 'sex',
        ));
    }

    public function getName()
    {
        return 'form1_main';
    }
}
