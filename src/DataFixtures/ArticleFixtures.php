<?php

namespace App\DataFixtures;

use App\Entity\Article;
use App\Entity\Comment;
use App\Entity\Categorie;
use Doctrine\Persistence\ObjectManager;
use Doctrine\Bundle\FixturesBundle\Fixture;

class ArticleFixtures extends Fixture
{
    public function load(ObjectManager $manager)
    {
           //********************entrée données  à l'aide d'une libraire exsitante faker******
        //import librairie FAKer
        $faker = \Faker\Factory::create('fr_FR');
        //creation 3 categorie
        for ($i = 1; $i <= 3; $i++) {
            $categorie = new Categorie;
            $categorie->setDescription($faker->paragraph())
                ->setTitle($faker->sentence());

            $manager->persist($categorie);
            // creation de 4 a 6 article
            for ($j = 1; $j <= mt_rand(4, 6); $j++) {
                $article = new Article;
                $content = '<p>' . join($faker->paragraphs(5), '</p><p>') . '</p>';
                $article->setTitle($faker->sentence())
                    ->setContent($content)
                    ->setImage('https://picsum.photos/id/'.$i.'/600/400')
                    ->setCreatedAt($faker->dateTimeBetween('-6 months'))
                    ->setCategorie($categorie);

                $manager->persist($article);
                //création de 4 à 10 commentaires pour chaque article
                for ($k = 1; $k <= mt_rand(4, 10); $k++) {
                    $comment = new Comment;
                    $content = '<p>' . join($faker->paragraphs(2), '</p><p>') . '</p>';

                    $now = new \dateTime;
                    $interval = $now->diff($article->getCreatedAt()); // retoune un timestam(en secondes) entre la date de creation des articles et aujourd'hui
                    $days = $interval->days; // transformer la date du secondes en jours
                    $minimum = "-$days days";/* -100 days le but est d'avoir des commentaires qui à l'interval de la création des articles, des commentaires de - de 6 mois à aujourd'hui */

                    $comment->setAuthor($faker->name)
                        ->setContent($content)
                        ->setCreatedAt($faker->dateTimeBetween($minimum))
                        ->setArticle($article);

                    $manager->persist($comment);
                }
            }
        }
 
        $manager->flush();

        //********************entrée données manuellement******
        //     // $product = new Product();
        //     // $manager->persist($product);
        //     // Pour pouvoir insérer dans la table SQL 'Article', nous devons instancier un objet issu de cette classe
        //     // L'entité 'Article' reflète la table SQL 'Article'
        //     // Nous avons besoin de rensigner tout les setteurs et tout les objets $article afin de pouvoir générer les insertions en BDD

        //     for($i = 1; $i <= 10; $i++) {
        //         // On remplit les objets articles grace au setteurs

        //         $article = new Article;// ctrl + alt + i (pc) pour importer la classe (PHP namespace Resolver)
        //         $article->setTitle("Titre de l'article n° $i")
        //                 ->setContent("<p>Contenu de l'article $i</p> ")
        //                 ->setImage("https://picsum.photos/600/400")
        //                 ->setCreatedAt(new \DateTime);

        //                 // En Symfony, nous avons besoin d'un manager qui permet de manipuler les lignes de la BDD (insertion, modification, suppression)


        //         $manager->persist($article);  // persist() est une méthode issue de la classe ObjectManager qui permet de garder en mémoire les objets ârticle crées et préparer les requetes d'insertion (INSERT INTO)


        //     }
        //     $manager->flush();// flush() est une méthode issue de la classe ObjectManager qui permet véritablement d'executer les insertions en BDD (similaire à execute() en PHP)

        //     // une fois les fixtures réaliseés, il faut les charger en BDD grace à doctrine (ORM) par la commande : 
        //     // php bin/console doctrine:fixtures:load

        // }
    }
}
