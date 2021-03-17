<?php

namespace App\Controller;

use App\Entity\Article;
use App\Entity\Comment;
use App\Entity\Categorie;
use App\Form\ArticleFormType;
use App\Form\CategorieFormType;
use Doctrine\ORM\EntityManager;
use Doctrine\ORM\EntityRepository;
use App\Repository\ArticleRepository;
use App\Repository\CommentRepository;
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
     * methode permettant de modifiant un article existant dans le backoffice
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
    public function adminCategorie(EntityManagerInterface $manager, CategorieRepository $categorieRepo, Categorie $categorie = null): Response
    {
        $colonnes = $manager->getClassMetadata(Categorie::class)->getFieldNames();

        $categoriesBdd = $categorieRepo->findAll();

        if ($categorie) {
            // Nous avons une relation entre la table Article et Category et une contrainte d'intégrité en RESTRICT
            // Donc ne pourrons pas supprimer la catégorie si 1 article lui est toujours associé
            // getArticles() de l'entité Category retourne tout les articles associés à la catégorie (relation bi-drirectionnelle)
            // Si getArticles() retourne un résultat vide, cela veut dire qu'il n'y a plus aucun article associé à la catégorie, nous pouvons dcon la supprimer

            if ($categorie->getArticles()->isEmpty()) {
                $manager->remove($categorie);
                $manager->flush();
            } else { // Sinon dans tout les autres cas, des articles sont toujours associés à la catégorie, on affiche un message erreur utilisateur
                $this->addFlash('danger', "Il n'est pas possible de supprimer la catégorie car des articles y sont toujours associés ");
            }
            return $this->redirectToRoute('admin_categorie');
        }

        return $this->render('admin/admin_categorie.html.twig', [
            'categoriesBdd' => $categoriesBdd,
            'colonnes' => $colonnes
        ]);
    }


    /**
     * @Route("/admin/categorie/new", name="admin_new_categorie")
     * @Route("/admin/categorie/{id}/edit", name="admin_edit_categorie")
     */
    public function adminFormCategorie(Request $request, EntityManagerInterface $manager, Categorie $categorie = null, CategorieRepository $categorieRepo): Response
    {
        /*
            Insertion d'une categorie en BDD : 
            1. Créer une classe permettant de générer un forumlaire correspondant à l'entité Category (make:form)
            2. dans le controller, faites en sorte d'importer et de créer le formulaire, en le reliant à l'entité
            3. Envoyé le formulaire sur le template (render) et l'afficher en front 
            4. Récupérer et envoyer les données de $_POST dans le bonne entité à la valodation du formulaire (handleRequest + $request)
            5. Générer et executer la requete d'insertion à la validation du formulaire ($manager + persist + flush)
        */

        // Si l'objet entité $category ne possède pas d'id, cela veut dire que nous sommes sur la route '/admin/category/new', que nous souhaitons créer une nouvelle catégorie, alors on entre dans la condition IF
        // Si l'objet entité $category possède un id, cela veut dire que nous sommes sur la route "/admin/category/{id}/edit", l'id envoyé dans l'URL a été selctionné en BDD, nous souhaitons modifier la catégorie existante

        if (!$categorie) {
            $categorie = new Categorie;
        }
        $formCategorie = $this->createForm(CategorieFormType::class, $categorie);
        $formCategorie->handleRequest($request);

        //dump($request);

        if ($formCategorie->isSubmitted() && $formCategorie->isValid()) {

            $manager->persist($categorie);
            $manager->flush();
            $this->addFlash('success', "Le catégorie " . $categorie->getId() . " a bien été ajoute");
            return $this->redirectToRoute('admin_categorie');
        }


        return $this->render('admin/admin_form_categorie.html.twig', [

            'formCategorie' => $formCategorie->createView()
        ]);
    }

    /**
     * Methode permettant d'afficher tout les commentaires des articles stockés en BDD
     * Méthode permettatn de supprimer un commentaire en BDD
     * @Route("/admin/comments", name="admin_comments")
     * @Route("/admin/comment/{id}/remove", name="admin_remove_comment")
     */
    public function adminComment(EntityManagerInterface $manager, CommentRepository $commentRepo, Comment $comment = null): Response
    {
        /*
            1. Faites en sorte de récupérer les métadonnée de la table Comment afin de récupérer le nom des champs/colonne de la table SQL comment et les transmettre au template
            2. Afficher le nom des champs/colonne sous forme de tableau HTML
            3. Dans le controller, seelctionner tout les commentaires stockés en BDD et les transmettre au template
            4. Afficher tout les commentaires de la BDD sous forme de tableau HTML dans le template
            5. Prévoir 2 liens (modification / suppression) pour chaque commentaire 
            6. Réaliser le traitement permettant de supprimer un commentaire dans la BDD
         */
        $colonnes = $manager->getClassMetadata(Comment::class)->getFieldNames();

        $commentBdd = $commentRepo->findAll();

        if ($comment) {
            $id = $comment->getId();
            $manager->remove($comment);
            $manager->flush();

            $this->addFlash('success', "Le commentaire n°$id a bien été supprimé !");
            return $this->redirectToRoute('admin_comments');
        }





        return $this->render('admin/admin_comments.html.twig', [
            'comments' => $commentBdd,
            'colonnes' => $colonnes
        ]);
    }

    /**
     * methode permettatn de modifier un commentairene bdd
     * @Route("/admin/comment/{id}/edit", name="admin_edit_comment")
     */
    public function editComment(): Response
    {
        return $this->render('admin/admin_edit_comments.html.twig');
    }
}
