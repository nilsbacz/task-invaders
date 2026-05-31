<?php

declare(strict_types=1);

namespace App\Form;

use App\Board\Domain\BoardRow;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

final class BoardRowType extends AbstractType
{
    #[\Override]
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $titleMaxLength = $options['title_max_length'];
        $titleOptions = [
                         'label'      => 'Row title',
                         'required'   => true,
                         'empty_data' => '',
                         'attr'       => ['maxlength' => $titleMaxLength],
                        ];

        $builder->add('title', TextType::class, $titleOptions);
    }

    #[\Override]
    public function configureOptions(OptionsResolver $resolver): void
    {
        $defaults = [
                     'data_class'       => BoardRow::class,
                     'title_max_length' => 32,
                    ];

        $resolver->setDefaults($defaults);
        $resolver->setAllowedTypes('title_max_length', 'int');
    }
}
