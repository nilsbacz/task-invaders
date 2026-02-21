<?php

declare(strict_types=1);

namespace App\Form;

use App\Entity\Board;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

final class BoardType extends AbstractType
{
    #[\Override]
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $titleMaxLength = $options['title_max_length'];
        $titleOptions = [
                         'label'      => 'Title',
                         'required'   => true,
                         'empty_data' => '',
                         'attr'       => ['maxlength' => $titleMaxLength],
                        ];

        $turretOptions = [
                          'label'    => 'Turret mode',
                          'required' => false,
                         ];

        $builder
            ->add('title', TextType::class, $titleOptions)
            ->add('isTurretMode', CheckboxType::class, $turretOptions);
    }

    #[\Override]
    public function configureOptions(OptionsResolver $resolver): void
    {
        $defaults = [
                     'data_class'       => Board::class,
                     'title_max_length' => 255,
                    ];

        $resolver->setDefaults($defaults);
        $resolver->setAllowedTypes('title_max_length', 'int');
    }
}
