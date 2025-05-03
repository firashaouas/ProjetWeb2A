<?php
require_once 'vendor/autoload.php'; // Inclure l'autoloader de Composer

class Config {
    private static $pdo = null;
    private static $stripe = null;

    // Configuration de la base de données
    public static function getConnexion() {
        if (!isset(self::$pdo)) {
            try {
                self::$pdo = new PDO(
                    'mysql:host=localhost;dbname=projet web',
                    'root',
                    '',
                    [
                        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                        PDO::ATTR_EMULATE_PREPARES => false
                    ]
                );
            } catch (PDOException $e) {
                die('Erreur de connexion : ' . $e->getMessage());
            }
        }
        return self::$pdo;
    }

    // Configuration de Stripe
    public static function getStripe() {
        if (!isset(self::$stripe)) {
            try {
                \Stripe\Stripe::setApiKey('sk_test_51RJL0sQabomDz0BaC9s0L184katdXI601GgaBipHtVd6KF39QhKgTN1sMfUNN0OwPgPE2kN5h1WHR3Y1Ke7Ml61U00xkJSFImX');
                self::$stripe = new \Stripe\StripeClient('sk_test_51RJL0sQabomDz0BaC9s0L184katdXI601GgaBipHtVd6KF39QhKgTN1sMfUNN0OwPgPE2kN5h1WHR3Y1Ke7Ml61U00xkJSFImX');
            } catch (Exception $e) {
                die('Erreur de configuration Stripe : ' . $e->getMessage());
            }
        }
        return self::$stripe;
    }

    // Clé publique Stripe
    public static function getStripePublicKey() {
        return 'pk_test_51RJL0sQabomDz0Baxnej9s98KKxxRyqRzsJUCLkHaveIB3FGVPF2rhdi8jLPCydMO4lF95QJotwMvL0QjOXjAud200GjafxVKG';
    }
}
?>