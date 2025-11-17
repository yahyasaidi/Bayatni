<?php
use PHPUnit\Framework\TestCase;

class User
{
    private $mysqli;

    public function __construct(mysqli $mysqli)
    {
        $this->mysqli = $mysqli;
    }

    public function login(string $email, string $password)
    {

        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            return false;
        }

        $stmt = $this->mysqli->prepare("SELECT id, email, password FROM users WHERE email = ?");
        if (!$stmt) {
            return false;
        }

        $stmt->bind_param("s", $email);
        $stmt->execute();
        $result = $stmt->get_result();

        if ($result->num_rows === 1) {
            $user = $result->fetch_assoc();
            if (password_verify($password, $user['password'])) {

                return [
                    'id' => $user['id'],
                    'email' => $user['email'],
                    'status' => 'authenticated'
                ];
            }
        }

        return false;
    }
}

class SQLInjectionTest extends TestCase
{
    private $user;
    private $mysqli;

    protected function setUp(): void
    {
        $this->mysqli = new mysqli('localhost', 'root', '', 'bayatni_db');
        if ($this->mysqli->connect_error) {
            $this->fail("Database connection failed: " . $this->mysqli->connect_error);
        }
        
        $this->user = new User($this->mysqli);
        
        // Ensure we have a test user
        $this->createTestUser();
    }

    protected function tearDown(): void
    {
        // Clean up test user
        $this->mysqli->query("DELETE FROM users WHERE email = 'testuser@example.com'");
        $this->mysqli->close();
    }

    private function createTestUser()
    {
        $password = password_hash('validpassword', PASSWORD_BCRYPT);
        $query = "INSERT INTO users (firstname, lastname, fullname, birthday, email, status, password, card_number, card_name, card_expire, card_cvc) 
                  VALUES ('Test', 'User', 'Test User', '2000-01-01', 'testuser@example.com', 'active', '$password', '0000000000000000', 'Test User', '12/25', '123')";
        $this->mysqli->query($query);
    }

    public function testValidLogin()
    {
        $result = $this->user->login('testuser@example.com', 'validpassword');
        $this->assertIsArray($result, "Valid login should return user array");
        $this->assertEquals('testuser@example.com', $result['email'], "Should return correct user");
    }

    public function testSQLInjectionAttempts()
    {
        // Test : Basic SQL injection
        $result1 = $this->user->login("admin' OR '1'='1", 'anything');
        $this->assertFalse($result1, "Basic SQL injection should fail");

         // Injection avec LIKE
    $result7 = $this->user->login("' OR email LIKE '%", 'anything');
    $this->assertFalse($result7, "Échec: Injection LIKE a fonctionné");

    //  Injection avec échappement de caractères
    $result8 = $this->user->login("\\' OR \\'1\\'=\\'1", 'anything');
    $this->assertFalse($result8, "Échec: Injection avec échappement a fonctionné");
        
    }

    public function testNonExistentUser()
    {
        $result = $this->user->login('nonexistent@example.com', 'password');
        $this->assertFalse($result, "Non-existent user should return false");
    }
}