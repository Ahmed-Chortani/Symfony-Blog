<?php

namespace App\Controller;

use App\Entity\Article;
use App\Entity\Comment;
use App\Entity\Category;
use App\Form\ArticleType;
use App\Form\CommentType;
use App\Form\CategoryType;
use App\Repository\ArticleRepository;
use App\Repository\CategoryRepository;
use Symfony\Component\HttpFoundation\Request;
use Doctrine\Common\Persistence\ObjectManager;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

class BlogController extends AbstractController
{
    /**
     * @Route("/blog", name="blog")
     */
    public function index(ArticleRepository $repo)
    {   
        $articles=$repo->findAll();

        return $this->render('blog/index.html.twig', [
            'controller_name' => 'BlogController',
            'articles'=>$articles,
        ]);
    }

    /**
     * @Route("/", name="home")
     */
    public function home(){
        return $this->render('blog/home.html.twig');
    }

    /**
     * @Route("/blog/new", name="blog_create")
     * @Route("blog/{id}/edit", name="blog_edit")
     */
        public function create(Article $article=null, Request $request, ObjectManager $manager){
        if(!$article){
            $article=new Article();

        }
        // $form=$this->createFormBuilder($article)
        //             ->add('title', TextType::class, [
        //                 'attr' => [
        //                     'placeholder'=>"Titre de l'article"
        //                 ]
        //             ])
        //             ->add('content',  TextareaType::class, [
        //                 'attr' => [
        //                     'placeholder'=>"Contenu de l'article"
        //                     ]
        //                 ])
        //             ->add('image', TextType::class, [
        //                 'attr' => [
        //                     'placeholder'=>"Image de l'article"
        //                     ]
        //                 ])
        //             ->add('save', SubmitType::class, [
        //                 'label'=>'Enregister'
        //             ])
        //             ->getForm();

        // $form=$this->createFormBuilder($article)
        //             ->add('title')
        //             ->add('content')
        //             ->add('image')
        //             ->getForm();

        $form=$this->createForm(ArticleType::class, $article);
        $form->handleRequest($request);
        
        if($form->isSubmitted() && $form->isValid()){
            if(!$article->getId()){
                $article->setCreatedAt(new \Datetime());

            }

            $manager->persist($article);
            $manager->flush();
            
            return $this->redirectToRoute('blog_show', [
                'id'=>$article->getId()
            ]);
        }
        return $this->render('blog/create.html.twig', [
            'formArticle' => $form->createView(),
             'editMode'=>$article->getId()!==null
        ]);
    }

    /**
     * @Route("/blog/article/{id}", name="blog_show")
     */
    public function show(Article $article,Request $request, ObjectManager $manager){
        $comment=new Comment();
        $form=$this->createForm(CommentType::class, $comment);
        $form->handleRequest($request);
        if($form->isSubmitted() && $form->isValid()){
            $comment->setCreatedAt(new \DateTime())
                    ->setArticle($article);

            $manager->persist($comment);
            $manager->flush();

            return $this->redirectToRoute('blog_show', [
                'id'=> $article->getId()
            ]);
        }
        return $this->render('blog/show.html.twig',[
            'article'=>$article,
            'commentForm'=>$form->createView()
        ]);
    }

      /**
     * @Route("/blog/ajouter_category", name="ajouter_category")
     */
    public function addCategory(Request $request, ObjectManager $manager){
        $category=new Category();
        $form=$this->createForm(CategoryType::class, $category);
        $form->handleRequest($request);
        if($form->isSubmitted() && $form->isValid()){

            $manager->persist($category);
            $manager->flush();

            return $this->redirectToRoute('category');
        }
        return $this->render('blog/create_category.html.twig',[
            'formCategory'=>$form->createView()
        ]);
    }

        /**
     * @Route("/blog/{id}/supprimer_category", name="supprimer_category")
     */
    public function supprimerCategory(Category $id){
        $entityManager=$this->getDoctrine()->getManager();
        $entityManager->remove($id);
        $entityManager->flush();

        return $this->redirectToRoute('category');
        
    }

    /**
     * @Route("/blog/category", name="category")
     */
    public function Category(CategoryRepository $repo, ObjectManager $manager){
        $categories=$repo->findAll();

        
        return $this->render('blog/supprimer_category.html.twig',[
            'categories'=>$categories
        ]);
    }
}
