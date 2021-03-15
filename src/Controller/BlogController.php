<?php

namespace App\Controller;

use App\Entity\Article;
use App\Entity\Comment;
use App\Form\ArticleFormType;
use App\Form\CommentFormType;
use Doctrine\ORM\EntityManager;
use App\Repository\ArticleRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

class BlogController extends AbstractController
{
    /**
     * @route("/", name="home")
     */

    public function home(): Response
    {
        return $this->render('blog/home.html.twig', [
            'title' => 'Bienvenue sur le blog Symfony',
            'age' => 25
        ]);
    }
    /** Méthode permetrtant d'afficher toute  la liste des articles stockeés en BDD
     * @Route("/blog", name="blog")
     */
    public function index(ArticleRepository $repo): Response
    {
        /*
            Pour selectionner des données dans une table SQL, nous devons absolument avoir accès à la classe Repository de l'entité correspondante 
            Un Repository est une classe permettant uniquement d'executer des requetes de selection en BDD (SELECT)
            Nous devons donc accéder au repository de l'netité Article au sein de notre controller  

            On appel l'ORM doctrine (getDoctrine()), puis on importe le repositoritory de la classe Article grace à la méthode getRepository()
            $repo est un objet issu de la classe ArticleRepository
            cet objet contient des méthodes permettant d'executer des requetes de selections
            findAll() : méthode issue de la classe ArticleRepository permettant de selectionner l'ensemble de la table SQL 'Article'
        */


        //$repo = $this->getDoctrine()->getRepository(Article::class); // on a plus besoin car onpasse articleRepository directement dans index
        //outil de debugage de Symfony (équivalent d'un var_dump en php)
        dump($repo);
        $articles = $repo->findALL();
        dump($articles);
        return $this->render('blog/index.html.twig', [
            'title' => 'Musique maestro ! Elle accompagne notre vie, souligne nos souvenirs',
            'articles' => $articles // on envoie sur le template, les articles selectionnés en BDD afin de pouvoir les afficher dynamiquement sur le template à l'aide du langage Twig

        ]);
    }
    /**
     * méthode permettant d'inserer et de modifier uun article
     * Il est possible de définir plusieurs route qui execute le même méthode dans le controller
     * {id} --> 5
     * @route("/blog/new", name="blog_create")
     * @route("/blog/{id}/edit", name="blog_edit")
     * 
     */

    public function create(Article $articleCreate = null, Request $request, EntityManagerInterface $manager): Response
    {
        //****************modifier les article ******** */
        // Ici nous renseignons le setter de l'objet et Symfony est capable automatiquement d'envoyer les valeurs de l'entité directement dans les attributs 'value' du formulaire, étant donné que l'entité $articleCreate est relié au formulaire
        //$articleCreate = new Article;
        // ->setContent("contenu edité")


        if (!$articleCreate) {
            $articleCreate = new Article;
        }

        // la classe Request de Symfony permet de véhiculer les données des superglobales PHP ($_POST, $_FILES, $_COOKIE, $_SESSION)
        // $request est un objet issu de la classe Request injecté en dependance de la méthode create()

        // dump($request);
        //****************premiere méthode de génerer les formulaire ******** */
        // $request permet de stocker les données des superglobales, la propriété $request->request permet de stocker les données véhiculées par un formulaire ($_POST), ici on compte si il y a données qui ont été saisie dans la formulaire

        // if($request->request->count()>0) {
        //     // Pour insérer dans la table Article, nous devons instancier un objet issu de la classe entité Article, qui est lié à la table SQL Article
        //     // On rensigne tout les setteurs de l'objet avec en arguments les données du formulaire ($_POST)

        //     $articleCreate = new Article;
        //     $articleCreate->setTitle($request->request->get('title'))
        //                   ->setContent($request->request->get('content'))
        //                   ->setImage($request->request->get('image'))
        //                   ->setCreatedAt(new \DateTime);
        // dump($articleCreate);
        // // on observe que l'objet entité Article $articleCreate, les propriétés contiennent bien les données du formaulaire


        // $manager->persist($articleCreate);
        // $manager->flush();
        // // On fait appel au manager afin de pouvoir executer une insertion en BDD
        // // on prépare et garde en mémoire l'insertion
        // // on execute l'insertion
        // return $this->redirectToRoute('blog_show', [
        //     'id' => $articleCreate->getId()
        // ]);
        // // Après l'insertion, on redirige l'internaute vers le détail de l'article qui vient d'être inséré en BDD
        // // Cela correspond à la route 'blog_show', mais c'est une route paramétrée qui attend un ID dans l'URL
        // // En 2ème argument de redirectToRoute, nous transmettons l'ID de l'article qui vient d'être inséré en BDD



        // }

        //****************une autre méthode de génerer les formulaire ******** */

        //$articleCreate = new Article;
        // $form= $this->createFormBuilder($articleCreate)
        //            ->add('title')
        //            ->add('content')
        //            ->add('image')
        //            ->getForm();//permet de valider le formulaire
        //   

        //handleRequest() permet de récuperer chaque données saisies dans le form($request) les bindées et les transmettre directement dans les bon setteurs de mon entité $articleCreate"
        //*********************une autre méthode de génerer les formulaire ******** */

        //directement vvia la commande php bin/console make:form

        // Nous avons créer une classe qui permet de générer le formulaire d'ajout d'article, il faut dans le controller importer cette classe ArticleFormType et relier le formulaire à notre entité Article $articleCreate

        $form = $this->createForm(ArticleFormType::class, $articleCreate);
        $form->handleRequest($request);
        dump($articleCreate);

        if ($form->isSubmitted() && $form->isValid()) {
            //on appelle le setteur de la date puisqu'il y a pas de champs date dans le formulaire

            if (!$articleCreate->getId()) {
                $articleCreate->setCreatedAt(new \DateTime);
            }

            $manager->persist($articleCreate);
            $manager->flush();

            return $this->redirectToRoute('blog_show', [
                'id' => $articleCreate->getId()
            ]);
        }

        return $this->render('blog/create.html.twig', [
            'formArticle' => $form->createView(),
            'editMode' => $articleCreate->getId()
        ]);
    }

    /** Méthode permetrtant d'afficher le detail d'un article 
     * on définit ici une route paramétrée définit avec un ID d'un article dans url
     * /blog/9 --> {id} --> $id = 9
     * @Route("/blog/{id}", name="blog_show")
     */
    public function show(Article $article, Request $request,  EntityManagerInterface $manager): Response
    {
        // $repoArticle est un objet issu de la classe ArticleRepository
        // La méthode find() permet de selectionner en BDD un article par son ID


        //$repoArticle = $this->getDoctrine()->getRepository(Article::class);

        // La méthode find() permet de selectionner en BDD un article par son ID

        //$article = $repoArticle->find($id);
        //dump($article);
        $comment = new Comment;
        $form = $this->createForm(CommentFormType::class, $comment);
        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {

            $comment->setCreatedAt(new \DateTime)
                ->setArticle($article);
            $manager->persist($comment);
            $manager->flush();
            // message de validation en session grace à la méthode addFlash()
            //1. success :identifiant du message
            //2. le message
            $this->addFlash('success', "le commentaire a bien été posté ! ");

            return $this->redirectToRoute('blog_show', [
                'id' => $article->getId()
            ]);
        }


        return $this->render('blog/show.html.twig', [
            'articleTwig' => $article, //on envoie sur le template les données selectionnées en BDD c'est à dire les informations d'une article en fonction de l'id transmit dans 'lURL
            'formComment' => $form->createView()
        ]);
        /*
        En fonction de la route paramétrée {id} et de l'injection de dépendance $article, Symfony voit que l'on besoin d'un article de la BDD par rapport à l'ID transmit dans l'URL, il est donc capable de recupérer l'ID et de selectionner en BDD l'article correspondant et de l'envoyer directement en argument de la méthode show(Article $article)
        Tout ça grace à des ParamConverter qui appel des convertisseurs pour convertir les paramètres de l'objet. Ces objets sont stockés en tant qu'attribut de requete et peuvent donc être injectés an tant qu'argument de méthode de controller
    */
    }
}
