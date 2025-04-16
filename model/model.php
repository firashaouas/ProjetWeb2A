<?php

class sponsor{
    private int $id_sponsor;
    private string $nom_entreprise;
    private string $evenement;
    private string $email ; 
    private int $telephone ; 
    private float $montant ;
    private string $duree ;
    private string $avantage;
    private string $status;
    public function __construct(
        string $nom_entreprise,
        string $evenement,
        string $email,
        int $telephone,
        float $montant,
        string $duree,
        string $avantage,
        string $status
    ) {
        $this->nom_entreprise = $nom_entreprise;
        $this->evenement = $evenement;
        $this->email = $email;
        $this->telephone = $telephone;
        $this->montant = $montant;
        $this->duree = $duree;
        $this->avantage = $avantage;
        $this->status = $status;
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
     * Get the value of evenement
     */ 
    public function getEvenement()
    {
        return $this->evenement;
    }

    /**
     * Set the value of evenement
     *
     * @return  self
     */ 
    public function setEvenement($evenement)
    {
        $this->evenement = $evenement;

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
}

?>