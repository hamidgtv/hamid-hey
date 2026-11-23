<?php
declare(strict_types=1);
session_start();

const DB_HOST = '127.0.0.1';
const DB_NAME = 'exam_pro';
const DB_USER = 'root';
const DB_PASS = '';

function db(): PDO {
    static $pdo = null;
    if ($pdo === null) {
        $pdo = new PDO(
            'mysql:host='.DB_HOST.';dbname='.DB_NAME.';charset=utf8mb4',
            DB_USER, DB_PASS,
            [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
             PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
             PDO::ATTR_EMULATE_PREPARES => false]
        );
    }
    return $pdo;
}
function e($v): string { return htmlspecialchars((string)$v, ENT_QUOTES, 'UTF-8'); }
function redirect(string $to): never { header('Location: '.$to); exit; }
function csrf_token(): string {
    if (empty($_SESSION['csrf'])) $_SESSION['csrf'] = bin2hex(random_bytes(32));
    return $_SESSION['csrf'];
}
function csrf_check(): void {
    if ($_SERVER['REQUEST_METHOD'] === 'POST' &&
        !hash_equals($_SESSION['csrf'] ?? '', $_POST['csrf'] ?? '')) {
        http_response_code(419); exit('درخواست نامعتبر است. صفحه را تازه‌سازی کنید.');
    }
}
function current_user(): ?array { return $_SESSION['user'] ?? null; }
function require_login(): array {
    $u = current_user();
    if (!$u) redirect('index.php');
    // Re-check account status on every authenticated request so a disabled account
    // cannot keep using an already-open session.
    $st = db()->prepare('SELECT id,national_code,full_name,role,city,class_name,is_active FROM users WHERE id=? LIMIT 1');
    $st->execute([(int)$u['id']]);
    $fresh = $st->fetch();
    if (!$fresh || (int)$fresh['is_active'] !== 1) {
        $_SESSION = [];
        if (ini_get('session.use_cookies')) {
            $params = session_get_cookie_params();
            setcookie(session_name(), '', time()-42000, $params['path'], $params['domain'], $params['secure'], $params['httponly']);
        }
        session_destroy();
        redirect('index.php');
    }
    $_SESSION['user'] = $fresh;
    return $fresh;
}
function require_role(string $role): array {
    $u = require_login();
    if ($u['role'] !== $role) redirect($u['role'] === 'teacher' ? 'teacher.php' : 'student.php');
    return $u;
}
function flash(string $msg, string $type='success'): void { $_SESSION['flash'] = [$msg,$type]; }
function get_flash(): ?array { $f=$_SESSION['flash']??null; unset($_SESSION['flash']); return $f; }

function normalize_digits(string $value): string {
    return strtr($value, ['۰'=>'0','۱'=>'1','۲'=>'2','۳'=>'3','۴'=>'4','۵'=>'5','۶'=>'6','۷'=>'7','۸'=>'8','۹'=>'9','٠'=>'0','١'=>'1','٢'=>'2','٣'=>'3','٤'=>'4','٥'=>'5','٦'=>'6','٧'=>'7','٨'=>'8','٩'=>'9']);
}
function valid_national_code(string $code): bool {
    if (!preg_match('/^[0-9]{10}$/', $code) || preg_match('/^(.)\1{9}$/', $code)) return false;
    $sum=0; for($i=0;$i<9;$i++) $sum+=(int)$code[$i]*(10-$i);
    $remainder=$sum%11; $check=(int)$code[9];
    return ($remainder<2 && $check===$remainder) || ($remainder>=2 && $check===11-$remainder);
}
