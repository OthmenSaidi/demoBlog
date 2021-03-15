<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;
use App\Repository\UserRepository;
use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;

/**
 * @ORM\Entity(repositoryClass=UserRepository::class)^
 * @UniqueEntity(
 *      fields = {"email"},
 *      message = "Un compte est déjà existant à cette adresse Email !!"
 * 
 * )
 */
class User implements UserInterface

{
    /**
     * @ORM\Id
     * @ORM\GeneratedValue
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @ORM\Column(type="string", length=255)
     * @Assert\NotBlank(
     *      message = "Veuillez renseigner une adresse Email ! "
     * )
     * @Assert\Email(
     *      message = "veuillez saisir une adresse Email Valide ! "
     * )
     */
    private $email;

    /**
     * @ORM\Column(type="string", length=255)
     * @Assert\NotBlank(
     *      message = "Veuillez renseigner un nom utlisateur ! "
     * )
     * @Assert\Length(
     *      min=2,
     *      max=50,
     *      minMessage = "Nom d'utlisateur trop court",
     *      maxMessage = "Nom d'utlisateur trop long"
     * )
     */
    private $userName;

    /**
     * @ORM\Column(type="string", length=255)
     * @Assert\EqualTo( propertyPath = "confirm_password", message = "Les mots de passes ne correspondent pas !")
     * @Assert\NotBlank(
     *      message = "Veuillez saisir votre mot de passe ! "
     * )
     * 
     */
    private $password;
    /**
     * cette prop receptionne une valeur mais pas stocké donc pas d'annontation ORM
     * @Assert\EqualTo(propertyPath = "password", message = "Les mots de passes ne correspondent pas !")
     * @Assert\NotBlank(
     *      message = "Veuillez saisir la confirmation de votre mot de passe! "
     * )
     */
    public $confirm_password;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getEmail(): ?string
    {
        return $this->email;
    }

    public function setEmail(string $email): self
    {
        $this->email = $email;

        return $this;
    }

    public function getUserName(): ?string
    {
        return $this->userName;
    }

    public function setUserName(string $userName): self
    {
        $this->userName = $userName;

        return $this;
    }

    public function getPassword(): ?string
    {
        return $this->password;
    }

    public function setPassword(string $password): self
    {
        $this->password = $password;

        return $this;
    }

    // Pour encoder le mot de passe l'entité USEr doit implémenter(similaire à l'héritage) l'interface UserInterface
    // cette interface contitent des méthodes abstraites que nous somme obligé de déclarer 
    // méthodes obligatoires : getUsername(), getPassword(), eraseCredentials(), getSalt() et getRoles()

    // Cette méthode est uniquement destinée à nettoyer les mots de passe en texte brut éventuellement stockés

    public function eraseCredentials()
    {
    }
    // Renvoi la chaine de caractère non encodé que l'utilisateur a saisi, qui est utilisé à l'origine pour, encoder le mot de passe

    public function getSalt()
    {
    }

    // Cette fonction renvoi un tableau de chaine de caractères

    // Renvoi les rôles accordés à l'utilisateur

    public function getRoles()
    {
        return ["ROLE_USER"];
    }
}
