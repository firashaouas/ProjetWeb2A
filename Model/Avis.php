<?php
class Avis {
    private $id_avis;
    private $id_passager;
    private $id_conducteur;
    private $note;
    private $commentaire;
    private $date_creation;
    private $date_modification;

    public function __construct(
        $id_avis,
        $id_passager,
        $id_conducteur,
        $note,
        $commentaire,
        $date_creation,
        $date_modification = null
    ) {
        $this->id_avis = $id_avis;
        $this->id_passager = $id_passager;
        $this->id_conducteur = $id_conducteur;
        $this->note = $note;
        $this->commentaire = $commentaire;
        $this->date_creation = $date_creation;
        $this->date_modification = $date_modification;
    }

    // Getters
    public function getIdAvis() {
        return $this->id_avis;
    }

    public function getIdPassager() {
        return $this->id_passager;
    }

    public function getIdConducteur() {
        return $this->id_conducteur;
    }

    public function getNote() {
        return $this->note;
    }

    public function getCommentaire() {
        return $this->commentaire;
    }

    public function getDateCreation() {
        return $this->date_creation;
    }

    public function getDateModification() {
        return $this->date_modification;
    }
}
?>