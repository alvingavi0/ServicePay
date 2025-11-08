<?php
require __DIR__ . '/../vendor/autoload.php';

use Google\Cloud\Firestore\FirestoreClient;
use Kreait\Firebase\Factory;

class FirebaseService {
    public $db;

    public function __construct() {
        // Load Firebase credentials from environment variable
        $firebaseCredentialsJson = getenv('FIREBASE_CREDENTIALS');

        if (!$firebaseCredentialsJson) {
            throw new Exception("FIREBASE_CREDENTIALS environment variable not set.");
        }

        $factory = (new Factory)->withServiceAccount(json_decode($firebaseCredentialsJson, true));
        $this->db = $factory->createFirestore()->database();
    }

    public function getUserByPhone($phone) {
        $usersRef = $this->db->collection('users');
        $query = $usersRef->where('phone', '=', $phone)->limit(1)->documents();

        foreach ($query as $doc) {
            if ($doc->exists()) {
                $user = $doc->data();
                $user['id'] = $doc->id();
                return $user;
            }
        }
        return null;
    }

    public function createUser($data) {
        $usersRef = $this->db->collection('users');
        $newUserRef = $usersRef->add($data);
        return $newUserRef->id();
    }

    public function getTransactions($userId) {
        $transactions = [];
        $transDocs = $this->db->collection('users')
            ->document($userId)
            ->collection('transactions')
            ->orderBy('created_at', 'DESC')
            ->documents();

        foreach ($transDocs as $doc) {
            if ($doc->exists()) {
                $transactions[] = $doc->data();
            }
        }
        return $transactions;
    }
}
