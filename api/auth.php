<?php
require __DIR__ . '/bootstrap.php';
require __DIR__ . '/../app/routes.php';
method('POST');
allowed_fields($_GET, []);
allowed_fields($_POST, ['action', 'email', 'password', 'return', '_csrf']);
$action = text($_POST, 'action', 20);
if ($action === 'login') {
    if (auth_user()) fail('Log out before signing in with another account.', 409, 'already_authenticated');
    verify_csrf();
    $email = mb_strtolower(text($_POST, 'email', 200, false), 'UTF-8');
    $user = login_account($email, password_input($_POST));
    json_response(['authenticated' => true, 'user' => public_identity($user), 'csrf' => $_SESSION['csrf'], 'redirect' => safe_return($_POST['return'] ?? '', $user)]);
}
if ($action === 'logout') {
    require_user(); verify_csrf(); logout_account();
    json_response(['authenticated' => false, 'redirect' => app_config()['base_path'] . 'login.html']);
}
fail('Invalid authentication action.', 400, 'invalid_request');
