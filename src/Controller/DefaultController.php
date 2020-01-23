<?php

namespace App\Controller;

use App\Entity\Article;
use App\Entity\CustomerReview;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Form\Extension\Core\Type\EmailType;
use Symfony\Component\Form\Extension\Core\Type\IntegerType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;

class DefaultController extends AbstractController
{
    /**
     * @Route("/", name="homepage")
     */
    public function index()
    {
        $articles = $this->getDoctrine()->getRepository(Article::class)->findThreeLast();
        $customerReviews = $this->getDoctrine()->getRepository(CustomerReview::class)->findThreeLast();

        return $this->render('default/index.html.twig', [
            'articles' => $articles,
            'reviews' => $customerReviews
        ]);
    }

    /**
     * @Route("/contact", name="contact")
     */
    public function contactAction(Request $request, \Swift_Mailer $mailer){
        $form = $this->createFormBuilder([])
            ->add('name', TextType::class, ['label' => 'Votre nom'])
            ->add('email', EmailType::class, ['label' => 'Votre email'])
            ->add('phoneNumber', IntegerType::class, ['label' => 'Votre téléphone'])
            ->add('subject', TextType::class, ['label' => 'Sujet'])
            ->add('message', TextareaType::class, ['label' => 'Message'])
            ->add('robots', IntegerType::class, ['label' => "Combien font 2+3 ?"])
            ->add('Envoyer', SubmitType::class, ['attr' => ['class' => 'btn-block btn-primary']])
            ->getForm();

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()){
            if ($form['robots']->getData() != 5){
                $this->addFlash('danger', "Et non, c'était 5");
            } else {

                $message = (new \Swift_Message('Hello Email'))
                    ->setFrom('consulting.awalee@gmail.com')
                    ->setTo('fajeddig@hotmail.fr')
                    ->setBody(
                        $this->renderView(
                            'emails/contact.html.twig',
                            ['contact' => $form->getData()]
                        ),
                        'text/html'
                    );

                $mailer->send($message);

                $this->addFlash('success', "Message bien reçu, on vous répond rapidement!");

            }
        }

        return $this->render('default/contact.html.twig', [
            'form' => $form->createView()
        ]);
    }
}
