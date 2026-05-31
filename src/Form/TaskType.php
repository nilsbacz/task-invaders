<?php

declare(strict_types=1);

namespace App\Form;

use App\Entity\Task;
use App\Enum\TaskRiskLevel;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\EnumType;
use Symfony\Component\Form\Extension\Core\Type\IntegerType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

final class TaskType extends AbstractType
{
    #[\Override]
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $titleMaxLength = $options['title_max_length'];

        $builder
            ->add(
                'title',
                TextType::class,
                [
                 'label'      => 'Task title',
                 'required'   => true,
                 'empty_data' => '',
                 'attr'       => ['maxlength' => $titleMaxLength],
                ]
            )
            ->add(
                'riskLevel',
                EnumType::class,
                [
                 'class'        => TaskRiskLevel::class,
                 'choice_label' => static fn (TaskRiskLevel $riskLevel): string => ucfirst(
                     strtolower($riskLevel->value)
                 ),
                 'label'        => 'Risk',
                 'required'     => true,
                ]
            )
            ->add('respawnsIn', IntegerType::class, $this->minutesOptions('Respawns in'))
            ->add('spawnsEvery', IntegerType::class, $this->minutesOptions('Spawns every'))
            ->add('reachesBaseIn', IntegerType::class, $this->minutesOptions('Reaches base in'))
            ->add(
                'hasShield',
                CheckboxType::class,
                [
                 'label'    => 'Shield',
                 'required' => false,
                ]
            )
            ->add(
                'respawnImmediatelyAfterDeath',
                CheckboxType::class,
                [
                 'label'    => 'Respawn after shot',
                 'required' => false,
                ]
            )
            ->add(
                'speedFactor',
                IntegerType::class,
                [
                 'label'      => 'Speed factor',
                 'required'   => true,
                 'empty_data' => '0',
                 'attr'       => ['min' => 0],
                ]
            );
    }

    #[\Override]
    public function configureOptions(OptionsResolver $resolver): void
    {
        $defaults = [
                     'data_class'       => Task::class,
                     'title_max_length' => 32,
                    ];

        $resolver->setDefaults($defaults);
        $resolver->setAllowedTypes('title_max_length', 'int');
    }

    /**
     * @return array{label: string, required: true, empty_data: string, attr: array{min: int}}
     */
    private function minutesOptions(string $label): array
    {
        return [
                'label'      => $label . ' (minutes)',
                'required'   => true,
                'empty_data' => '0',
                'attr'       => ['min' => 0],
               ];
    }
}
