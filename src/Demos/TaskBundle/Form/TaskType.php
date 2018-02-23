<?php

namespace Demos\TaskBundle\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;

use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\DateType;
use Symfony\Component\Form\Extension\Core\Type\DateTimeType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\FileType;
use Symfony\Component\Form\Extension\Core\Type\CollectionType;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\HttpFoundation\File\File;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;

use Symfony\Component\OptionsResolver\OptionsResolver;

class TaskType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('title', TextType::class, array(
                'required' => true,
            ))
            ->add('task', TextareaType::class, array(
                'attr' => array('class' => 'tinymce textarea-big'),
                'required' => true,
            ))
            ->add('type', ChoiceType::class, array(
                'choices'  => array(
                    'new' => 'new',
                    'bug' => 'bug',
                    'edit' => 'edit',
                    'feature' => 'feature',
                ),
                'placeholder' => 'Choose task of type',
                'required' => true,
            ))
            ->add('priority', ChoiceType::class, array(
                'choices'  => array(
                    'immediatly' => 'immediatly',
                    'normal' => 'normal',
                    'not important' => 'not important',
                ),
                'placeholder' => 'Choose task of priority',
                'required' => true,
            ))
            ->add('attachment', FileType::class, array(
                'required' => false,
            ))
            ->add('createdDate', DateType::class, array(
                'widget' => 'choice',
                'format' => \IntlDateFormatter::LONG,
            ))
            ->add('send', SubmitType::class, array('label' => 'Add task'))
            ->getForm();
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults(array(
            'data_class' => 'Demos\TaskBundle\Entity\Task',
        ));
    }
}
