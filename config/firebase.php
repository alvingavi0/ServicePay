<?php
require __DIR__ . '/../vendor/autoload.php';

use Google\Cloud\Firestore\FirestoreClient;
use Kreait\Firebase\Factory;

$factory = (new Factory)
    ->withServiceAccount(__DIR__ . '/serviceAccountKey.json');

$firestore = $factory->createFirestore();
$db = $firestore->database();
?>
