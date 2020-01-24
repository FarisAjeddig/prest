<?php

namespace App\Controller;

use App\Entity\Article;
use App\Entity\Comment;
use App\Form\ArticleType;
use App\Form\CommentType;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;

/** @Route("/blog", name="blog_") */
class BlogController extends AbstractController
{
    /**
     * @Route("/", name="homepage")
     */
    public function indexAction()
    {
        $articles = $this->getDoctrine()->getRepository(Article::class)->findBy(['isPublished' => true]);
        return $this->render('blog/index.html.twig', [
            'articles' => array_reverse($articles),
        ]);
    }

    /**
     * @Route("/add/", name="add")
     */
    public function addAction(Request $request)
    {
        $article = new Article();

        $form = $this->createForm(ArticleType::class, $article);

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

                $article->setPicture($newFilename);
            }

            $article->setCreationDate(new \DateTime());

            $em = $this->getDoctrine()->getManager();
            $em->persist($article);
            $em->flush();

            return $this->redirectToRoute('blog_homepage');
        }

        return $this->render('blog/add.html.twig', [
            'form' => $form->createView()
        ]);
    }

    /**
     * @Route("/edit/{id}", name="edit")
     */
    public function editAction(Request $request, $id)
    {
        /** @var Article $article */
        $article = $this->getDoctrine()->getRepository(Article::class)->find($id);

        $form = $this->createForm(ArticleType::class, $article);

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

                $article->setPicture($newFilename);
            }

            $article->setLastEditDate(new \DateTime());

            $em = $this->getDoctrine()->getManager();
            $em->persist($article);
            $em->flush();

            return $this->redirectToRoute('blog_homepage');
        }

        return $this->render('blog/add.html.twig', [
            'form' => $form->createView()
        ]);
    }

    /**
     * @Route("/show/{id}", name="show")
     */
    public function showAction(Request $request, $id)
    {
        /** @var Article $article */
        $article = $this->getDoctrine()->getRepository(Article::class)->find($id);
        $recentArticles = $this->getDoctrine()->getRepository(Article::class)->findThreeLast();

        $comment = new Comment();

        $form = $this->createForm(CommentType::class, $comment);

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()){
            if ($form['Robots']->getData() != 4){
                $this->addFlash('danger', "Et non, c'était 4");
                return $this->render('blog/show.html.twig', [
                    'article' => $article,
                    'comments' => $article->getComments(),
                    'form' => $form->createView()
                ]);
            }
            $comment->setCreationDate(new \DateTime());
            $comment->setNumberDislike(0);
            $comment->setNumberLike(0);
            $comment->setArticle($article);

            $article->addComment($comment);

            $em = $this->getDoctrine()->getManager();
            $em->persist($comment);
            $em->flush();
            $this->addFlash('success', "Votre commentaire a bien été posté !");
        }

        return $this->render('blog/show.html.twig', [
            'article' => $article,
            'comments' => $article->getComments(),
            'form' => $form->createView(),
            'recentArticles' => $recentArticles
        ]);
    }

    /**
     * @Route("/admin", name="admin")
     */
    public function adminAction(Request $request){
        $articles = $this->getDoctrine()->getRepository(Article::class)->findAll();
        return $this->render('blog/admin.html.twig', [
            'articles' => array_reverse($articles),
        ]);
    }
}
