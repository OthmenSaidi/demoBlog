<?php

namespace App\Controller;

use App\Entity\Article;
use App\Entity\Categorie;
use App\Form\ArticleFormType;
use Doctrine\ORM\EntityManager;
use App\Repository\ArticleRepository;
use App\Repository\CategorieRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

class AdminController extends AbstractController
{
    /**
     * @Route("/admin", name="admin")
     *
     */
    public function index(): Response
    {
        return $this->render('admin/index.html.twig', [
            'controller_name' => 'AdminController',
        ]);
    }

    /**
     * methode permet d'afficher toute la liste des articles sous formes tableau html dans backoffice
     * @Route("/admin/articles", name="admin_articles")
     * @Route("/admin/{id}/remove", name="admin_remove_article")
     */
    public function adminArticles(EntityManagerInterface $manager, ArticleRepository $articleRepo, Article $article = null): Response

    {
        // Le manager permet de manipuler le BDD, on execute la méthode getClassMetadata() afin de selectionner les méta données des colonnes (primary key, not nul, type, taille etc...)
        // getFieldNames() permet de selectionner le noms des champs/colonne de la table Article de la bdd

        $colonnes = $manager->getClassMetadata(Article::class)->getFieldNames();

        dump($colonnes);

        //selection de tout les article en BDD à l'aide de artcileRepository
        $articles = $articleRepo->findAll();
        dump($articles);

        if ($article) {

            $id = $article->getId();
            $manager->remove($article);
            $manager->flush();

            $this->addFlash('success', "L'article n°$id a bien été supprimé !");
            return $this->redirectToRoute('admin_articles');
        }

        return $this->render('admin/admin_articles.html.twig', [

            'colonnes' => $colonnes,
            'articlesBdd' => $articles // on transmet à la méthode render les articles selectionnés en BDD au template afin de pouvoir les afficher

        ]);
    }
    /**
     * methode permettant de modifaint un article existant dans le backoffice
     * @Route("/admin/{id}/edit-article", name="admin_edit_article")
     * 
     */
    public function adminEditArticle(Article $article, Request $request, EntityManagerInterface $manager)
    {
        // creation du modif du form 
        $formArticle = $this->createForm(ArticleFormType::class, $article);

        dump($request);
        $formArticle->handleRequest($request);
        if ($formArticle->isSubmitted() && $formArticle->isValid()) {

            $manager->persist($article);
            $manager->flush();
            $this->addFlash('success', "L'article " . $article->getId() . " a bien été modifié");
            return $this->redirectToRoute('admin_articles');
        }

        return $this->render('admin/admin_edit_article.html.twig', [

            'iDarticle' => $article->getId(),
            'formArticle' => $formArticle->createView()
        ]);
    }
    /**
     * methode permettan d'afficher sous forme de tableau HTML les catégories stockées en BDD
     * @Route("/admin/categories", name="admin_categorie")
     * @Route("/admin/categorie/{id}/remove", name="admin_remove_categorie")
     */
    public function adminCategorie(EntityManagerInterface $manager, CategorieRepository $categorieRepo): Response
    {
        $colonnes = $manager->getClassMetadata(Categorie::class)->getFieldNames();

        $categories = $categorieRepo->findAll();

        return $this->render('admin/admin_categorie.html.twig', [
            'categoriesBdd' => $categories,
            'colonnes' => $colonnes
        ]);
    }

    /**
     * @Route("/admin/categorie/new", name="admin_new_categorie")
     * @Route("/admin/categorie/{id}/edit", name="admin_edit_categorie")
     */
    public function adminFormCategorie()
    {
        return $this->render('admin/admin_form_categorie.html.twig');
    }
}
