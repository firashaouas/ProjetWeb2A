<?php

class sponsor{
    private int $id_sponsor;
    private string $nom_entreprise;
    private string $email ; 
    private int $telephone ; 
    private float $montant ;
    private string $duree ;
    private string $avantage;
    private string $status;
    private int $id_offre;  

    public function __construct(
        string $nom_entreprise,
        string $email,
        int $telephone,
        float $montant,
        string $duree,
        string $avantage,
        string $status,
        int $id_offre  
    ) {
        $this->nom_entreprise = $nom_entreprise;
        $this->email = $email;
        $this->telephone = $telephone;
        $this->montant = $montant;
        $this->duree = $duree;
        $this->avantage = $avantage;
        $this->status = $status;
        $this->id_offre = $id_offre;
    }
    
    

    /**
     * Get the value of id_sponsor
     */ 
    public function getId_sponsor()
    {
        return $this->id_sponsor;
    }

    /**
     * Set the value of id_sponsor
     *
     * @return  self
     */ 
    public function setId_sponsor($id_sponsor)
    {
        $this->id_sponsor = $id_sponsor;

        return $this;
    }

    /**
     * Get the value of nom_entreprise
     */ 
    public function getNom_entreprise()
    {
        return $this->nom_entreprise;
    }

    /**
     * Set the value of nom_entreprise
     *
     * @return  self
     */ 
    public function setNom_entreprise($nom_entreprise)
    {
        $this->nom_entreprise = $nom_entreprise;

        return $this;
    }

    /**
     * Get the value of email
     */ 
    public function getEmail()
    {
        return $this->email;
    }

    /**
     * Set the value of email
     *
     * @return  self
     */ 
    public function setEmail($email)
    {
        $this->email = $email;

        return $this;
    }

    /**
     * Get the value of telephone
     */ 
    public function getTelephone()
    {
        return $this->telephone;
    }

    /**
     * Set the value of telephone
     *
     * @return  self
     */ 
    public function setTelephone($telephone)
    {
        $this->telephone = $telephone;

        return $this;
    }

   

    /**
     * Get the value of montant
     */ 
    public function getMontant()
    {
        return $this->montant;
    }

    /**
     * Set the value of montant
     *
     * @return  self
     */ 
    public function setMontant($montant)
    {
        $this->montant = $montant;

        return $this;
    }

    /**
     * Get the value of duree
     */ 
    public function getDuree()
    {
        return $this->duree;
    }

    /**
     * Set the value of duree
     *
     * @return  self
     */ 
    public function setDuree($duree)
    {
        $this->duree = $duree;

        return $this;
    }

    /**
     * Get the value of avantage
     */ 
    public function getAvantage()
    {
        return $this->avantage;
    }

    /**
     * Set the value of avantage
     *
     * @return  self
     */ 
    public function setAvantage($avantage)
    {
        $this->avantage = $avantage;

        return $this;
    }

        /**
         * Get the value of status
         */ 
        public function getStatus()
        {
                return $this->status;
        }

        /**
         * Set the value of status
         *
         * @return  self
         */ 
        public function setStatus($status)
        {
                $this->status = $status;

                return $this;
        }

        /**
         * Get the value of id_offre
         */
        public function getId_offre()
        {
                return $this->id_offre;
        }

        /**
         * Set the value of id_offre
         *
         * @return self
         */
        public function setId_offre($id_offre)
        {
                $this->id_offre = $id_offre;

                return $this;
        }
}

class Offre {
    private int $id_offre;
    private string $titre_offre;
    private string $description_offre;
    private string $evenement;
    private float $montant_offre;
    private string $status;
<<<<<<< HEAD
    private ?string $image; 
=======
    private ?string $image; // New property for image filename
>>>>>>> a2fab5823e23d27e778495a421b0f3aaed219ca1

    public function __construct(
        string $titre_offre,
        string $description_offre,
        string $evenement,
        float $montant_offre,
        string $status = 'libre',
        ?string $image = null
    ) {
        $this->titre_offre = $titre_offre;
        $this->description_offre = $description_offre;
        $this->evenement = $evenement;
        $this->montant_offre = $montant_offre;
        $this->status = $status;
        $this->image = $image;
    }

    public function getId_offre()
    {
        return $this->id_offre;
    }

    public function setId_offre($id_offre)
    {
        $this->id_offre = $id_offre;
        return $this;
    }

    public function getTitre_offre()
    {
        return $this->titre_offre;
    }

    public function setTitre_offre($titre_offre)
    {
        $this->titre_offre = $titre_offre;
        return $this;
    }

    public function getDescription_offre()
    {
        return $this->description_offre;
    }

    public function setDescription_offre($description_offre)
    {
        $this->description_offre = $description_offre;
        return $this;
    }

    public function getEvenement()
    {
        return $this->evenement;
    }

    public function setEvenement($evenement)
    {
        $this->evenement = $evenement;
        return $this;
    }

    public function getMontant_offre()
    {
        return $this->montant_offre;
    }

    public function setMontant_offre($montant_offre)
    {
        $this->montant_offre = $montant_offre;
        return $this;
    }

    public function getStatus()
    {
        return $this->status;
    }

    public function setStatus($status)
    {
        $this->status = $status;
        return $this;
    }

    public function getImage()
    {
        return $this->image;
    }

    public function setImage(?string $image)
    {
        $this->image = $image;
        return $this;
    }
}

?>