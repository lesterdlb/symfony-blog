<?php

declare(strict_types=1);

namespace App\BlogApp\Application\Form;

use App\BlogApp\Application\Config\PostStatus;

use App\BlogApp\Domain\Entity\Post;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class StatusFormType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add(
                PostStatus::Published->name, SubmitType::class, [
                    'label' => PostStatus::Published->value,
                    'attr'  => ['class' => 'btn btn-success']
                ]
            )
            ->add(
                PostStatus::Rejected->name, SubmitType::class, [
                    'label' => PostStatus::Rejected->value,
                    'attr'  => ['class' => 'btn btn-danger']
                ]
            );

        if ($options['post']->getStatus() !== PostStatus::Draft->value) {
            $builder->remove(PostStatus::from($options['post']->getStatus())->name);
        }
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'post' => null,
            'data_class' => Post::class,
        ]);
    }
}
