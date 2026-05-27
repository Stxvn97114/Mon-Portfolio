<?php
// ============================================================
//  envoyer.php — Réception du formulaire de contact
//  À placer dans le même dossier que contact.html
// ============================================================

// ── 1. Configuration base de données ────────────────────────
define('DB_HOST', 'localhost');
define('DB_NAME', 'portfolio_contact');
define('DB_USER', 'root');       // ← ton utilisateur phpMyAdmin
define('DB_PASS', '');           // ← ton mot de passe phpMyAdmin

// ── 2. Headers CORS / JSON ───────────────────────────────────
header('Content-Type: application/json; charset=utf-8');
header('Access-Control-Allow-Origin: *');

// ── 3. Seulement les requêtes POST ──────────────────────────
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['success' => false, 'message' => 'Méthode non autorisée.']);
    exit;
}

// ── 4. Récupération & nettoyage des champs ───────────────────
function clean(string $val): string {
    return htmlspecialchars(trim($val), ENT_QUOTES, 'UTF-8');
}

$nom     = clean($_POST['nom']     ?? '');
$prenom  = clean($_POST['prenom']  ?? '');
$email   = trim($_POST['email']    ?? '');
$sujet   = clean($_POST['sujet']   ?? '');
$message = clean($_POST['message'] ?? '');

// ── 5. Validation serveur ────────────────────────────────────
$errors = [];

if (strlen($nom) < 2)
    $errors[] = 'Nom trop court (min. 2 caractères).';

if (!filter_var($email, FILTER_VALIDATE_EMAIL))
    $errors[] = 'Adresse email invalide.';

$sujets_autorisés = ['collaboration', 'stage', 'question', 'autre'];
if (!in_array($sujet, $sujets_autorisés))
    $errors[] = 'Sujet invalide.';

if (strlen($message) < 10)
    $errors[] = 'Message trop court (min. 10 caractères).';

if (!empty($errors)) {
    http_response_code(422);
    echo json_encode(['success' => false, 'message' => implode(' ', $errors)]);
    exit;
}

// ── 6. Connexion à MySQL ─────────────────────────────────────
try {
    $pdo = new PDO(
        'mysql:host=' . DB_HOST . ';dbname=' . DB_NAME . ';charset=utf8mb4',
        DB_USER,
        DB_PASS,
        [
            PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            PDO::ATTR_EMULATE_PREPARES   => false,
        ]
    );
} catch (PDOException $e) {
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => 'Erreur de connexion à la base de données.']);
    exit;
}

// ── 7. Insertion en base ─────────────────────────────────────
try {
    $stmt = $pdo->prepare(
        'INSERT INTO messages (nom, prenom, email, sujet, message, ip, user_agent)
         VALUES (:nom, :prenom, :email, :sujet, :message, :ip, :user_agent)'
    );

    $stmt->execute([
        ':nom'        => $nom,
        ':prenom'     => $prenom ?: null,
        ':email'      => $email,
        ':sujet'      => $sujet,
        ':message'    => $message,
        ':ip'         => $_SERVER['REMOTE_ADDR'] ?? null,
        ':user_agent' => substr($_SERVER['HTTP_USER_AGENT'] ?? '', 0, 500),
    ]);

    echo json_encode([
        'success' => true,
        'message' => 'Message enregistré avec succès.',
        'id'      => $pdo->lastInsertId(),
    ]);

} catch (PDOException $e) {
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => 'Erreur lors de l\'enregistrement.']);
}
