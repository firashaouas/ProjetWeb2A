<?php

require_once __DIR__ . '/../config.php'; // Ensure this path points to the file containing the Database class

class User {
    private $id_user;
    private $full_name;
    private $email;
    private $password;
    private $date_inscription;
    private $role;
    private $num_user;
    private $facebook_id;
    private $google_id;
    private $profile_picture; 

    public function __construct($id_user = null, $full_name = null, $email = null, 
                                $password = null, $date_inscription = null, 
                                $role = 'user', $num_user = null, $facebook_id = null, $google_id = null , $profile_picture = null) {
        $this->id_user = $id_user;
        $this->full_name = $full_name;
        $this->email = $email;
        $this->password = $password;
        $this->date_inscription = $date_inscription;
        $this->role = $role;
        $this->num_user = $num_user;
        $this->facebook_id = $facebook_id;
        $this->google_id = $google_id;
        $this->profile_picture = $profile_picture;
    }

    public function addUser($fullName, $email, $password, $dateInscription, $role, $numUser, $facebookId, $googleId, $profilePicture) {
        $db = config::getConnexion();
        $sql = "INSERT INTO users (full_name, email, password, date_inscription, role, num_user, facebook_id, google_id, profile_picture)
                VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)";

        $stmt = $db->prepare($sql);
        $stmt->execute([$fullName, $email, $password, $dateInscription, $role, $numUser, $facebookId, $googleId, $profilePicture]);
    }
      

    public static function deleteUser($db, $id) {
        $stmt = $db->prepare("SELECT * FROM user WHERE id = ?");
        $stmt->execute([$id]);
        $user = $stmt->fetch();
        
        if ($user) {
            // L'utilisateur existe, procéder à la suppression
            $delete = User::deleteById($db, $id);
            if ($delete) {
                echo "Utilisateur supprimé avec succès.";
            } else {
                echo "Erreur lors de la suppression de l'utilisateur.";
            }
        } else {
            echo "Utilisateur introuvable.";
        }
    }

    public static function deleteById($db, $id) {
        $query = "DELETE FROM users WHERE id_user = :id";
        $stmt = $db->prepare($query);
        $stmt->bindParam(':id', $id, PDO::PARAM_INT);
        return $stmt->execute();  // Returns true if deletion was successful
    }
    
      

    // Getter pour la photo de profil
    public function getProfilePicture() {
        return $this->profile_picture;
    }
    
    public function setProfilePicture($profile_picture) {
        $this->profile_picture = $profile_picture;
    }


    // Getters and Setters
    public function getIdUser() {
        return $this->id_user;
    }

    public function setIdUser($idUser) {
        $this->id_user = $idUser;
    }

    public function getFullName() {
        return $this->full_name;
    }

    public function setFullName($fullName) {
        $this->full_name = $fullName;
    }

    public function getEmail() {
        return $this->email;
    }

    public function setEmail($email) {
        $this->email = $email;
    }

    public function getPassword() {
        return $this->password;
    }

    public function setPassword($password) {
        $this->password = $password;
    }

    public function getNumUser() {
        return $this->num_user;
    }

    public function setNumUser($numUser) {
        $this->num_user = $numUser;
    }

    public function getRole() {
        return $this->role;
    }

    public function setRole($role) {
        $this->role = $role;
    }

    public function getFacebookId() {
        return $this->facebook_id;
    }

    public function setFacebookId($facebookId) {
        $this->facebook_id = $facebookId;
    }

    public function getGoogleId() {
        return $this->google_id;
    }

    public function setGoogleId($googleId) {
        $this->google_id = $googleId;
    }

    public function save($db) {
        if ($this->id_user) {
            // Mise à jour
            $stmt = $db->prepare("UPDATE users SET 
                full_name = ?,
                email = ?,
                password = ?,
                num_user = ?,
                role = ?,
                facebook_id = ?,
                google_id = ?,
                profile_picture = ?
                WHERE id_user = ?");
                
            return $stmt->execute([
                $this->full_name,
                $this->email,
                $this->password,
                $this->num_user,
                $this->role,
                $this->facebook_id,
                $this->google_id,
                $this->profile_picture,
                $this->id_user
            ]);
        } else {
            // Insertion
            $stmt = $db->prepare("INSERT INTO users 
                (full_name, email, password, date_inscription, role, num_user, facebook_id, google_id, profile_picture) 
                VALUES (?, ?, ?, NOW(), ?, ?, ?, ?, ?)");
                
            return $stmt->execute([
                $this->full_name,
                $this->email,
                $this->password,
                $this->role,
                $this->num_user,
                $this->facebook_id,
                $this->google_id,
                $this->profile_picture
            ]);
        }
    }

    public function updateProfilePicture($db) {
        $stmt = $db->prepare("UPDATE user SET profile_picture = ? WHERE id_user = ?");
        return $stmt->execute([$this->profile_picture, $this->id_user]);
    }

    public static function findByFacebookId($db, $facebookId) {
        $stmt = $db->prepare("SELECT * FROM user WHERE facebook_id = ?");
        $stmt->execute([$facebookId]);
        $data = $stmt->fetch(PDO::FETCH_ASSOC);
        if ($data) {
            return new User(
                $data['id_user'],
                $data['full_name'],
                $data['email'],
                $data['password'],
                $data['date_inscription'],
                $data['role'],
                $data['num_user'],
                $data['facebook_id'],
                $data['google_id']
            );
        }
        return null;
    }

    public static function findByGoogleId($db, $googleId) {
        $stmt = $db->prepare("SELECT * FROM user WHERE google_id = ?");
        $stmt->execute([$googleId]);
        $data = $stmt->fetch(PDO::FETCH_ASSOC);
        if ($data) {
            return new User(
                $data['id_user'],
                $data['full_name'],
                $data['email'],
                $data['password'],
                $data['date_inscription'],
                $data['role'],
                $data['num_user'],
                $data['facebook_id'],
                $data['google_id']
            );
        }
        return null;
    }

    public static function findByEmail($db, $email) {
        $stmt = $db->prepare("SELECT * FROM user WHERE email = ?");
        $stmt->execute([$email]);
        $data = $stmt->fetch(PDO::FETCH_ASSOC);
        if ($data) {
            return new User(
                $data['id_user'],
                $data['full_name'],
                $data['email'],
                $data['password'],
                $data['date_inscription'],
                $data['role'],
                $data['num_user'],
                $data['facebook_id'],
                $data['google_id'],
                $data['profile_picture'] // Added this line
            );
        }
        return null;
    }

    public static function getAll($db) {
        $stmt = $db->query("SELECT * FROM user");
        $users = [];
        while ($data = $stmt->fetch(PDO::FETCH_ASSOC)) {
            $users[] = new User(
                $data['id_user'],
                $data['full_name'],
                $data['email'],
                $data['password'],
                $data['date_inscription'],
                $data['role'],
                $data['num_user'],
                $data['facebook_id'],
                $data['google_id'],
                $data['profile_picture'] // Added this line
            );
        }
        return $users;
    }
    public static function findAll($db) {
        $query = $db->query("SELECT * FROM users");
        return $query->fetchAll(PDO::FETCH_ASSOC);
    }

    public static function verifyPassword($db, $email, $password) {
        $stmt = $db->prepare("SELECT password FROM user WHERE email = ?");
        $stmt->execute([$email]);
        $user = $stmt->fetch(PDO::FETCH_ASSOC);
        return $user && password_verify($password, $user['password']);
    }
    
    public static function deleteByEmail($db, $email) {
        $stmt = $db->prepare("DELETE FROM user WHERE email = ?");
        return $stmt->execute([$email]);
    }



    
// Dans ton contrôleur ou modèle (User.php)
// Ensure this logic is implemented in a controller or a method, not directly in the class body.
public static function updateSessionDateInscription($userModel, $session) {
    $user = $userModel->getUserById($session['user']['id_user']);
    $session['user']['date_inscription'] = $user['date_inscription'];
}

public function updateUserInfo($db) {
    $sql = "UPDATE user SET full_name = ?, email = ?, num_user = ?, password = ?, profile_picture = ? WHERE id_user = ?";
    $stmt = $db->prepare($sql);
    return $stmt->execute([
        $this->full_name,
        $this->email,
        $this->num_user,
        $this->password,
        $this->profile_picture,
        $this->id_user
    ]);
}


public static function emailExists($db, $email) {
    $stmt = $db->prepare("SELECT id_user FROM utilisateur WHERE email = ?");
    $stmt->execute([$email]);
    return $stmt->fetch() !== false;
}




    /**
     * Get the value of date_inscription
     */
    public function getDateInscription()
    {
        return $this->date_inscription;
    }

    /**
     * Set the value of date_inscription
     */
    public function setDateInscription($date_inscription): self
    {
        $this->date_inscription = $date_inscription;

        return $this;
    }





}   