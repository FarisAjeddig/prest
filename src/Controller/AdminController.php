<?php

namespace App\Controller;

use App\Entity\CustomerReview;
use App\Form\CustomerReviewType;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;

/** @Route("/admin", name="admin_") */
class AdminController extends AbstractController
{
    /**
     * @Route("/", name="homepage")
     */
    public function index()
    {
        return $this->render('admin/index.html.twig', [
            'controller_name' => 'AdminController',
        ]);
    }

    /**
     * @Route("/customerReviews", name="customer_reviews")
     */
    public function customerReviewsAction(Request $request){
        $customerReviews = $this->getDoctrine()->getRepository(CustomerReview::class)->findAll();

        return $this->render('admin/customerReview/customerReviews.html.twig', [
            'reviews' => array_reverse($customerReviews)
        ]);
    }

    /**
     * @Route("/customerReviews/add", name="customer_reviews_add")
     */
    public function customerReviewsAddAction(Request $request){
        $customerReview = new CustomerReview();

        $form = $this->createForm(CustomerReviewType::class, $customerReview);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()){

            $pictureFile = $form['pictureFile']->getData();

            if ($pictureFile) {
                $originalFilename = pathinfo($pictureFile->getClientOriginalName(), PATHINFO_FILENAME);
                $safeFilename = transliterator_transliterate('Any-Latin; Latin-ASCII; [^A-Za-z0-9_] remove; Lower()', $originalFilename);
                $newFilename = $safeFilename.'-'.uniqid().'.'.$pictureFile->guessExtension();

                try {
                    $pictureFile->move(
                        $this->getParameter('pictures_directory'),
                        $newFilename
                    );
                } catch (FileException $e) {
                    // ... handle exception if something happens during file upload
                }

                $customerReview->setPicture($newFilename);
            }

            $em = $this->getDoctrine()->getManager();
            $em->persist($customerReview);
            $em->flush();

            $this->addFlash('success', "Le témoignage a bien été enregistré.");
            return $this->redirectToRoute('admin_customer_reviews');
        }

        return $this->render('admin/customerReview/customerReviewsAdd.html.twig', [
            'form' => $form->createView()
        ]);
    }

    /**
     * @Route("/customerReviews/edit/{id}", name="customer_reviews_edit")
     */
    public function customerReviewsEditAction(Request $request, $id){
        $customerReview = $this->getDoctrine()->getRepository(CustomerReview::class)->find($id);

        $form = $this->createForm(CustomerReviewType::class, $customerReview);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()){

            $pictureFile = $form['pictureFile']->getData();

            if ($pictureFile) {
                $originalFilename = pathinfo($pictureFile->getClientOriginalName(), PATHINFO_FILENAME);
                $safeFilename = transliterator_transliterate('Any-Latin; Latin-ASCII; [^A-Za-z0-9_] remove; Lower()', $originalFilename);
                $newFilename = $safeFilename.'-'.uniqid().'.'.$pictureFile->guessExtension();

                try {
                    $pictureFile->move(
                        $this->getParameter('pictures_directory'),
                        $newFilename
                    );
                } catch (FileException $e) {
                    // ... handle exception if something happens during file upload
                }

                $customerReview->setPicture($newFilename);
            }

            $em = $this->getDoctrine()->getManager();
            $em->persist($customerReview);
            $em->flush();

            $this->addFlash('success', "Le témoignage a bien été modifié.");
            return $this->redirectToRoute('admin_customer_reviews');
        }

        return $this->render('admin/customerReview/customerReviewsAdd.html.twig', [
            'form' => $form->createView()
        ]);
    }

}
