<?php

namespace Ml\PrestationBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * Prestation
 *
 * @ORM\MappedSuperclass
 * 
 */
abstract class Prestation
{

    /**
    * @ORM\ManyToOne(targetEntity="Ml\UserBundle\Entity\User",inversedBy="prestation")
    * @ORM\JoinColumn(nullable=false)
     */
    private $user;

    /**
     * @var integer
     *
     * @ORM\Column(name="id", type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    protected $id;

    /**
     * @var string
     *
     * @ORM\Column(name="titre", type="string", length=255)
     */
    private $titre;

    /**
     * @var \DateTime
     *
     * @ORM\Column(name="dateCreation", type="datetime")
     */
    private $dateCreation;

    /**
     * @var string
     *
     * @ORM\Column(name="commentaire", type="text")
     */
    private $commentaire;

    /**
     * @var integer
     *
     * @ORM\Column(name="signaler", type="integer")
     */
    private $signaler;

    

    /**
     * @var boolean
     *
     * @ORM\Column(name="visibilite", type="boolean")
     */
    private $visibilite;


    /**
     * Get id
     *
     * @return integer 
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * Set titre
     *
     * @param string $titre
     * @return Prestation
     */
    public function setTitre($titre)
    {
        $this->titre = $titre;

        return $this;
    }

    /**
     * Get titre
     *
     * @return string 
     */
    public function getTitre()
    {
        return $this->titre;
    }

    /**
     * Set dateCreation
     *
     * @param \DateTime $dateCreation
     * @return Prestation
     */
    public function setDateCreation($dateCreation)
    {
        $this->dateCreation = $dateCreation;

        return $this;
    }

    /**
     * Get dateCreation
     *
     * @return \DateTime 
     */
    public function getDateCreation()
    {
        return $this->dateCreation;
    }

    /**
     * Set commentaire
     *
     * @param string $commentaire
     * @return Prestation
     */
    public function setCommentaire($commentaire)
    {
        $this->commentaire = $commentaire;

        return $this;
    }

    /**
     * Get commentaire
     *
     * @return string 
     */
    public function getCommentaire()
    {
        return $this->commentaire;
    }

    /**
     * Set signaler
     *
     * @param integer $signaler
     * @return Prestation
     */
    public function setSignaler($signaler)
    {
        $this->signaler = $signaler;

        return $this;
    }

    /**
     * Get signaler
     *
     * @return integer 
     */
    public function getSignaler()
    {
        return $this->signaler;
    }

    /**
     * Set prix
     *
     * @param integer $prix
     * @return Prestation
     */
    public function setPrix($prix)
    {
        $this->prix = $prix;

        return $this;
    }

    /**
     * Get prix
     *
     * @return integer 
     */
    public function getPrix()
    {
        return $this->prix;
    }

    /**
     * Set visibilite
     *
     * @param boolean $visibilite
     * @return Prestation
     */
    public function setVisibilite($visibilite)
    {
        $this->visibilite = $visibilite;

        return $this;
    }

    /**
     * Get visibilite
     *
     * @return boolean 
     */
    public function getVisibilite()
    {
        return $this->visibilite;
    }
	
		// Getter et setter pour l'entité User
	  public function setUser(\Ml\UserBundle\Entity\User $user)
	  {
		$this->user = $user;
	  }
	  public function getUser()
	  {
		return $this->user;
	  }
}
