<?php
use PHPUnit\Framework\TestCase;

class CacheTest extends TestCase
{
    private $conn;
    private $fichier_cache = "cache_requete.txt";

    // Connect to database before each test
    protected function setUp(): void
    {
        $host = 'localhost';
        $db   = 'bayatni_db';
        $user = 'root';
        $pass = '';

        $this->conn = new mysqli($host, $user, $pass, $db);

        if ($this->conn->connect_error) {
            $this->fail('Erreur de connexion à la base de données: ' . $this->conn->connect_error);
        }
    }

    public function testCache()
    {
        $debut = microtime(true);

        if (file_exists($this->fichier_cache) && (time() - filemtime($this->fichier_cache)) < 3600) {
            $donnees = unserialize(file_get_contents($this->fichier_cache));
            echo "Données chargées depuis le cache.<br>";
        } else {
            $sql = "SELECT * FROM users WHERE status = 'active'";
            $resultat = mysqli_query($this->conn, $sql);

            if (!$resultat) {
                $this->fail('Erreur SQL : ' . mysqli_error($this->conn));
            }

            $donnees = mysqli_fetch_all($resultat, MYSQLI_ASSOC);

            file_put_contents($this->fichier_cache, serialize($donnees));

            echo "Données chargées depuis la base de données.<br>";
        }

        $fin = microtime(true);
        $temps_execution = $fin - $debut;

        echo "Temps d'exécution : " . $temps_execution . " secondes<br>";

        echo "<pre>";
        print_r($donnees);
        echo "</pre>";

        $this->assertIsArray($donnees, "Les données récupérées ne sont pas un tableau.");
    }

    // Close connection after all tests
    protected function tearDown(): void
    {
        if ($this->conn instanceof mysqli) {
            $this->conn->close();
        }
    }
}
?>
