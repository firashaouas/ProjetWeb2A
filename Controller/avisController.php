<?php
require_once __DIR__.'/../config.php';

class Avis {
    private $conn;

    public function __construct() {
        $this->conn = Config::getConnexion();
        if ($this->conn === null) {
            throw new Exception("DB connection failed");
        }
    }

    public function create($data) {
        $sql = "INSERT INTO avis (
            id_passager, id_conducteur, note, 
            commentaire, titre, auteur, date_creation
        ) VALUES (
            :id_passager, :id_conducteur, :note,
            :commentaire, :titre, :auteur, NOW()
        )";

        $stmt = $this->conn->prepare($sql);
        return $stmt->execute([
            ':id_passager' => $data['id_passager'],
            ':id_conducteur' => $data['id_conducteur'],
            ':note' => $data['note'],
            ':commentaire' => $data['commentaire'],
            ':titre' => $data['titre'],
            ':auteur' => $data['auteur']
        ]);
    }
    
    public function getAll() {
        $sql = "SELECT * FROM avis ORDER BY date_creation DESC";
        $stmt = $this->conn->prepare($sql);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
}
