<?php
/**
 * Databasekonfigurasjon – trygg PDO-tilkobling
 * 
 * Denne versjonen bruker:
 *  - Miljøvariabler (.env) hvis tilgjengelig
 *  - Ekte feilhåndtering med logging
 *  - Egendefinert PDO-klasse for gjenbruk
 */

// --- 1. Last inn miljøvariabler (valgfritt men anbefalt) ---
if (file_exists(__DIR__ . '/.env')) {
    $lines = file(__DIR__ . '/.env', FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
    foreach ($lines as $line) {
        if (strpos(trim($line), '#') === 0) continue; // hopp over kommentarer
        list($name, $value) = array_map('trim', explode('=', $line, 2));
        $_ENV[$name] = $value;
    }
}

// --- 2. Konfigurasjon med fallback til standardverdier ---
$host = $_ENV['DB_HOST'] ?? 'localhost';
$db   = $_ENV['DB_NAME'] ?? 'min_app';
$user = $_ENV['DB_USER'] ?? 'root';
$pass = $_ENV['DB_PASS'] ?? '';
$charset = 'utf8mb4';

$dsn = "mysql:host=$host;dbname=$db;charset=$charset";

$options = [
    PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION, // kast exceptions
    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,       // returner assoc arrays
    PDO::ATTR_EMULATE_PREPARES   => false,                  // ekte prepared statements
];

// --- 3. Sikker tilkobling ---
try {
    $pdo = new PDO($dsn, $user, $pass, $options);
} catch (PDOException $e) {
    // Logg feil til fil uten å vise sensitiv info
    error_log("[" . date('Y-m-d H:i:s') . "] DB-feil: " . $e->getMessage() . PHP_EOL, 3, __DIR__ . '/logs/db_errors.log');

    // Vis trygg melding til brukeren
    exit("Noe gikk galt med databasen. Prøv igjen senere.");
}

// --- 4. (Valgfritt) Opprett global funksjon for tilgang ---
function db(): PDO {
    global $pdo;
    return $pdo;
}
?>
