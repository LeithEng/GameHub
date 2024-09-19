<?php
namespace App\Form;

use App\Entity\Game;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\DateType;
use Symfony\Component\Form\Extension\Core\Type\MoneyType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class GameType extends AbstractType
{
public function buildForm(FormBuilderInterface $builder, array $options): void
{
$builder
->add('title', TextType::class)
->add('releasedate', DateType::class, [
'widget' => 'single_text',
'format' => 'yyyy-MM-dd',
])
->add('publisher', TextType::class)
->add('genre', TextType::class)
->add('price', MoneyType::class, [
'currency' => 'USD',
]);
}

public function configureOptions(OptionsResolver $resolver): void
{
$resolver->setDefaults([
'data_class' => Game::class,
]);
}
}
