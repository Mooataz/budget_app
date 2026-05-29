<?php
// ============================================================
// src/core/Session.php — Gestion des sessions PHP sécurisées
// ============================================================

class Session {
    public static function start(): void {
        if (session_status() === PHP_SESSION_NONE) {
            session_name(SESSION_NAME);
            session_set_cookie_params([
                'lifetime' => SESSION_LIFETIME,
                'path'     => '/',
                'secure'   => false,   // true en HTTPS
                'httponly' => true,
                'samesite' => 'Lax',
            ]);
            session_start();
        }
    }

    public static function set(string $key, mixed $value): void {
        $_SESSION[$key] = $value;
    }

    public static function get(string $key): mixed {
        return $_SESSION[$key] ?? null;
    }

    public static function has(string $key): bool {
        return isset($_SESSION[$key]);
    }

    public static function remove(string $key): void {
        unset($_SESSION[$key]);
    }

    public static function destroy(): void {
        session_unset();
        session_destroy();
    }

    /** Vérification du token CSRF */
    public static function generateCsrf(): string {
        if (!self::has('csrf_token')) {
            self::set('csrf_token', bin2hex(random_bytes(TOKEN_LENGTH)));
        }
        return self::get('csrf_token');
    }

    public static function validateCsrf(string $token): bool {
        return hash_equals(self::get('csrf_token') ?? '', $token);
    }

    /** Vérifier si l'utilisateur est connecté */
    public static function isLogged(): bool {
        return self::has('user_id') && self::has('user_role');
    }

    public static function isAdmin(): bool {
        return self::get('user_role') === 'ADMINISTRATEUR';
    }

    public static function userId(): ?int {
        return self::get('user_id');
    }

    public static function userRole(): ?string {
        return self::get('user_role');
    }

    public static function flash(string $type, string $message): void {
        $_SESSION['flash'][] = ['type' => $type, 'message' => $message];
    }

    public static function getFlash(): array {
        $flash = $_SESSION['flash'] ?? [];
        unset($_SESSION['flash']);
        return $flash;
    }
}


// ============================================================
// src/core/Router.php — Routeur frontal simple
// ============================================================

class Router {
    private array $routes = [];

    public function get(string $path, callable $handler): void {
        $this->routes['GET'][$path] = $handler;
    }

    public function post(string $path, callable $handler): void {
        $this->routes['POST'][$path] = $handler;
    }

    public function dispatch(): void {
        $method = $_SERVER['REQUEST_METHOD'];
        $uri    = strtok($_SERVER['REQUEST_URI'], '?');
        // Enlever le préfixe du dossier public
        $base = dirname($_SERVER['SCRIPT_NAME']);
        if ($base !== '/') {
            $uri = str_replace($base, '', $uri);
        }
        $uri = '/' . trim($uri, '/');

        if (isset($this->routes[$method][$uri])) {
            call_user_func($this->routes[$method][$uri]);
            return;
        }

        // Routes dynamiques avec paramètre /{id}
        foreach ($this->routes[$method] ?? [] as $pattern => $handler) {
            $regex = preg_replace('/\{[^}]+\}/', '([^/]+)', $pattern);
            if (preg_match('#^' . $regex . '$#', $uri, $matches)) {
                array_shift($matches);
                call_user_func_array($handler, $matches);
                return;
            }
        }

        http_response_code(404);
        require_once __DIR__ . '/../../views/partials/404.php';
    }
}


// ============================================================
// src/core/Validator.php — Validation des entrées
// ============================================================

class Validator {
    private array $errors = [];

    public function required(string $field, mixed $value): self {
        if (empty(trim((string)$value))) {
            $this->errors[$field] = "Le champ « $field » est obligatoire.";
        }
        return $this;
    }

    public function email(string $field, string $value): self {
        if (!filter_var($value, FILTER_VALIDATE_EMAIL)) {
            $this->errors[$field] = "L'adresse email n'est pas valide.";
        }
        return $this;
    }

    public function minLength(string $field, string $value, int $min): self {
        if (strlen($value) < $min) {
            $this->errors[$field] = "Le champ « $field » doit contenir au moins $min caractères.";
        }
        return $this;
    }

    public function numeric(string $field, mixed $value): self {
        if (!is_numeric($value) || (float)$value < 0) {
            $this->errors[$field] = "Le champ « $field » doit être un nombre positif.";
        }
        return $this;
    }

    public function date(string $field, string $value): self {
        $d = DateTime::createFromFormat('Y-m-d', $value);
        if (!$d || $d->format('Y-m-d') !== $value) {
            $this->errors[$field] = "La date « $field » est invalide (format YYYY-MM-DD).";
        }
        return $this;
    }

    public function inArray(string $field, mixed $value, array $allowed): self {
        if (!in_array($value, $allowed, true)) {
            $this->errors[$field] = "Valeur non autorisée pour « $field ».";
        }
        return $this;
    }

    public function fails(): bool {
        return !empty($this->errors);
    }

    public function errors(): array {
        return $this->errors;
    }
}


// ============================================================
// src/core/Response.php — Helpers HTTP
// ============================================================

class Response {
    public static function json(mixed $data, int $code = 200): void {
        http_response_code($code);
        header('Content-Type: application/json; charset=utf-8');
        echo json_encode($data, JSON_UNESCAPED_UNICODE);
        exit;
    }

    public static function redirect(string $url): void {
        header("Location: $url");
        exit;
    }

    public static function forbidden(): void {
        http_response_code(403);
        die(json_encode(['error' => 'Accès refusé.']));
    }

    public static function requireAuth(): void {
        if (!Session::isLogged()) {
            self::redirect(BASE_URL . '/login');
        }
    }

    public static function requireAdmin(): void {
        self::requireAuth();
        if (!Session::isAdmin()) {
            self::forbidden();
        }
    }
}
