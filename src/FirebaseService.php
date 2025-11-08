<?php
require_once __DIR__ . '/../vendor/autoload.php';

use Google\Cloud\Firestore\FirestoreClient;

class FirebaseService {
    public $db;

    public function __construct() {
        // Path to your Firebase service account JSON file
        $serviceAccountPath = __DIR__ . '/../serviceAccount.json';

        if (!file_exists($serviceAccountPath)) {
            throw new Exception("Firebase service account file not found at: " . $serviceAccountPath);
        }

        // Initialize Firestore connection
        $this->db = new FirestoreClient([
            'keyFilePath' => $serviceAccountPath,
            'projectId' => 'servicepaywallet', // change if your Firebase project ID differs
        ]);
    }

    /**
     * Create a new user document
     */
    public function createUser($userData) {
        try {
            $ref = $this->db->collection('users')->add($userData);
            return $ref->id();
        } catch (Exception $e) {
            error_log("Error creating user: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Get user by Firestore document ID
     */
    public function getUser($userId) {
        try {
            $doc = $this->db->collection('users')->document($userId)->snapshot();
            return $doc->exists() ? $doc->data() : null;
        } catch (Exception $e) {
            error_log("Error fetching user: " . $e->getMessage());
            return null;
        }
    }

    /**
     * Find a user by phone number
     */
    public function getUserByPhone($phone) {
        try {
            $query = $this->db->collection('users')->where('phone', '=', $phone)->documents();
            foreach ($query as $doc) {
                if ($doc->exists()) {
                    $data = $doc->data();
                    $data['id'] = $doc->id();
                    return $data;
                }
            }
            return null;
        } catch (Exception $e) {
            error_log("Error getting user by phone: " . $e->getMessage());
            return null;
        }
    }

    /**
     * Verify login credentials
     */
    public function verifyUser($phone, $password) {
        $user = $this->getUserByPhone($phone);
        if ($user && isset($user['password']) && password_verify($password, $user['password'])) {
            return $user;
        }
        return null;
    }

    /**
     * Get all transactions for a user
     */
    public function getUserTransactions($userId) {
        $transactions = [];
        try {
            $transDocs = $this->db
                ->collection('users')
                ->document($userId)
                ->collection('transactions')
                ->orderBy('created_at', 'DESC')
                ->documents();

            foreach ($transDocs as $doc) {
                if ($doc->exists()) {
                    $data = $doc->data();
                    $data['id'] = $doc->id();
                    $transactions[] = $data;
                }
            }
        } catch (Exception $e) {
            error_log("Error fetching transactions: " . $e->getMessage());
        }
        return $transactions;
    }
}
