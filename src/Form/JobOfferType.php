<?php

namespace App\Form;

use App\Entity\JobOffer;
use App\Entity\Category;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints as Assert;

class JobOfferType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('title', TextType::class, [
                'label' => 'Job Title',
                'attr' => ['class' => 'block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm p-2 bg-gray-50'],
                'constraints' => [
                    new Assert\NotBlank(['message' => 'Please enter a job title']),
                    new Assert\Length(['min' => 3, 'max' => 255]),
                ],
            ])
            ->add('category', EntityType::class, [
                'class' => Category::class,
                'choice_label' => 'name',
                'placeholder' => 'Select a category',
                'label' => 'Job Category',
                'attr' => ['class' => 'block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm p-2 bg-gray-50'],
                'constraints' => [
                    new Assert\NotBlank(['message' => 'Please select a category']),
                ],
            ])
            ->add('type', ChoiceType::class, [
                'label' => 'Contract Type',
                'choices' => [
                    'CDI (Permanent)' => 'CDI',
                    'CDD (Fixed-term)' => 'CDD',
                    'Internship' => 'Stage',
                    'Freelance' => 'Freelance',
                ],
                'placeholder' => 'Select contract type',
                'attr' => ['class' => 'block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm p-2 bg-gray-50'],
                'constraints' => [
                    new Assert\NotBlank(['message' => 'Please select a contract type']),
                ],
            ])
            ->add('location', TextType::class, [
                'label' => 'Location (City, Country, or Remote)',
                'required' => false,
                'attr' => ['class' => 'block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm p-2 bg-gray-50'],
            ])
            ->add('description', TextareaType::class, [
                'label' => 'Job Description',
                'attr' => [
                    'class' => 'block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm p-2 bg-gray-50',
                    'rows' => 8
                ],
                'constraints' => [
                    new Assert\NotBlank(['message' => 'Please provide a job description']),
                ],
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => JobOffer::class,
        ]);
    }
}

