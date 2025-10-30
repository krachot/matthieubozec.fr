<?php

declare(strict_types=1);

namespace App\Http\Form;

use App\Contact\Enum\BudgetEnum;
use App\Contact\Enum\PrestationTypeEnum;
use App\Contact\Model\ContactMessage;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\EmailType;
use Symfony\Component\Form\Extension\Core\Type\EnumType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\OptionsResolver\OptionsResolver;

/**
 * @extends AbstractType<ContactMessage>
 */
class ContactMessageType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('name', TextType::class, [
                'label' => 'Vous êtes :',
                'required' => true,
                'attr' => [
                    'placeholder' => 'Votre nom et prénom',
                ],
            ])
            ->add('email', EmailType::class, [
                'label' => 'Adresse mail :',
                'required' => true,
                'attr' => [
                    'placeholder' => 'exemple@entreprise.fr',
                ],
            ])
            ->add('prestationType', EnumType::class, [
                'label' => 'Type de prestation :',
                'class' => PrestationTypeEnum::class,
                'choice_label' => fn (PrestationTypeEnum $choice) => $choice->label(),
                'placeholder' => 'Choisissez une prestation',
                'expanded' => false,
                'multiple' => false,
                'required' => false,
            ])
            ->add('budget', EnumType::class, [
                'label' => 'Votre budget :',
                'class' => BudgetEnum::class,
                'choice_label' => fn (BudgetEnum $choice) => $choice->label(),
                'placeholder' => 'Choisissez une tranche indicative',
                'expanded' => false,
                'multiple' => false,
                'required' => false,
            ])
            ->add('message', TextareaType::class, [
                'label' => 'Votre message :',
                'required' => true,
                'attr' => [
                    'placeholder' => 'Décrivez brièvement votre projet ou vos besoins…',
                ],
            ])
            ->add('nickname', TextType::class, [
                'label' => false,
                'required' => false,
                'attr' => [
                    'autocomplete' => 'off',
                    'tabindex' => '-1',
                ],
                'row_attr' => [
                    'style' => 'display: none;',
                ],
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => ContactMessage::class,
            'csrf_token_id' => 'submit',
            'method' => Request::METHOD_POST,
        ]);
    }
}
