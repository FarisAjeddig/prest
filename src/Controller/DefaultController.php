<?php

namespace App\Controller;

use App\Entity\Article;
use App\Entity\CustomerReview;
use App\Entity\Realisation;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Form\Extension\Core\Type\EmailType;
use Symfony\Component\Form\Extension\Core\Type\HiddenType;
use Symfony\Component\Form\Extension\Core\Type\IntegerType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Validator\Constraints\NotBlank;

class DefaultController extends AbstractController
{
    /**
     * @Route("/", name="homepage")
     */
    public function index(Request $request, \Swift_Mailer $mailer)
    {
        $articles = $this->getDoctrine()->getRepository(Article::class)->findThreeLast();
        $customerReviews = $this->getDoctrine()->getRepository(CustomerReview::class)->findThreeLast();
        $realisations = $this->getDoctrine()->getRepository(Realisation::class)->findAll();

        $form = $this->createFormBuilder([])
            ->add('robots', TextType::class)
            ->add('name', TextType::class)
            ->add('email', EmailType::class)
            ->add('phoneNumber', TextType::class)
            ->add('message', TextareaType::class)
            ->add('Envoyer', SubmitType::class)
            ->getForm();

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid() && $this->captchaverify($request->get('g-recaptcha-response'))){

            $message = (new \Swift_Message('Message depuis la page d\'accueil'))
                ->setFrom('consulting.awalee@gmail.com')
                ->setTo('fajeddig@hotmail.fr')
                ->addTo('warenpaisley@gmail.com')
                ->setBody(
                    $this->renderView(
                        'emails/contact.html.twig',
                        ['contact' => $form->getData()]
                    ),
                    'text/html'
                );

            $mailer->send($message);

            $this->addFlash('success', "Message bien reçu, on vous répond rapidement!");


            return $this->render('default/index.html.twig', [
                'articles' => $articles,
                'reviews' => $customerReviews
            ]);

        }


        return $this->render('default/index.html.twig', [
            'articles' => $articles,
            'reviews' => $customerReviews,
            'realisations' => $realisations,
            'form' => $form->createView()
        ]);
    }

    function captchaverify($recaptcha){
        $url = "https://www.google.com/recaptcha/api/siteverify";
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_HEADER, 0);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, TRUE);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, array(
            "secret"=>"6LfledYUAAAAAF5JhpTgqYLJkVSQnAyYn7ANDar6","response"=>$recaptcha));
        $response = curl_exec($ch);
        curl_close($ch);
        $data = json_decode($response);

        return $data->success;
    }


//    /**
//     * @Route("/tarifs", name="tarifs")
//     */
//    public function tarifsAction(Request $request){
//        return $this->render('default/tarifs.html.twig');
//    }

    /**
     * @Route("/realisations", name="realisations")
     */
    public function realisationsAction(Request $request){
        $realisation = $this->getDoctrine()->getRepository(Realisation::class)->findAll();

        return $this->render('default/realisations.html.twig', [
            'realisations' => array_reverse($realisation)
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
            ->add('message', TextareaType::class, ['label' => 'Message'])
            ->add('robots', IntegerType::class, ['label' => "Combien font 2+3 ?"])
            ->add('Envoyer', SubmitType::class, ['attr' => ['class' => 'btn-block btn-primary']])
            ->getForm();

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()){
            if ($form['robots']->getData() != 5){
                $this->addFlash('danger', "Et non, c'était 5");
            } else {

                $message = (new \Swift_Message('Message depuis la page contact'))
                    ->setFrom('consulting.awalee@gmail.com')
                    ->setTo('fajeddig@hotmail.fr')
                    ->addTo('warenpaisley@gmail.com')
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

    public function blogFooterAction(){
        $articles = $this->getDoctrine()->getRepository(Article::class)->findThreeLast(2);

        return $this->render('default/blogFooter.html.twig', [
            'articles' => $articles
        ]);
    }

    /**
     * @Route("/payment/{amount}", name="payment")
     */
    public function paymentAction(Request $request, $amount){
        $form = $this->get('form.factory')
            ->createNamedBuilder('payment-form')
            ->add('token', HiddenType::class, [
                'constraints' => [new NotBlank()],
            ])
            ->add('submit', SubmitType::class)
            ->getForm();

        if ($request->isMethod('POST')) {
            $form->handleRequest($request);

            if ($form->isValid()) {
                // TODO: charge the card

                \Stripe\Stripe::setApiKey($_ENV['stripe_secret_key']);

                \Stripe\Charge::create([
                    "amount" => $amount * 100,
                    "currency" => "eur",
                    "source" => $request->request->get('payment-form')['token'],
                    "receipt_email" => 'fajeddig@hotmail.fr',
                    "description" => 'Facture Boucherie SAM',
                ]/*, [
                "idempotency_key" => "Qx9ynl4xdel41yIc",
            ]*/);

                $this->addFlash('success', "Paiement bien reçu, merci !");

            }
        }


        return $this->render('default/payment.html.twig', [
            'amount' => $amount,
            'form' => $form->createView(),
            'stripe_public_key' => $_ENV['stripe_public_key']
        ]);
    }

}
