<?php
// handlers/auth/register.php
// POST /api/register
// Body: { first_name, last_name, email, password, password_confirmation }
// Returns: { token, user }

$body  = getBody();
$first = trim($body['first_name'] ?? '');
$last  = trim($body['last_name']  ?? '');
$email = trim($body['email']      ?? '');
$pass  = $body['password']              ?? '';
$conf  = $body['password_confirmation'] ?? '';

// Validate
$errors = [];
if (!$first)               $errors['first_name'][] = 'First name is required.';
if (!$last)                $errors['last_name'][]  = 'Last name is required.';
if (!filter_var($email, FILTER_VALIDATE_EMAIL))
                           $errors['email'][]       = 'A valid email is required.';
if (strlen($pass) < 6)    $errors['password'][]    = 'Password must be at least 6 characters.';
if ($pass !== $conf)       $errors['password'][]    = 'Passwords do not match.';

if ($errors) jsonError('Validation failed.', 422, $errors);

// Check duplicate email
$chk = $conn->prepare("SELECT id FROM users WHERE email = ?");
$chk->bind_param('s', $email);
$chk->execute();
if ($chk->get_result()->num_rows > 0) {
    jsonError('An account with that email already exists.', 409,
        ['email' => ['Email is already taken.']]);
}

// Create user
$hash = password_hash($pass, PASSWORD_DEFAULT);
$ins  = $conn->prepare(
    "INSERT INTO users (first_name, last_name, email, password) VALUES (?,?,?,?)"
);
$ins->bind_param('ssss', $first, $last, $email, $hash);
$ins->execute();
$uid = $conn->insert_id;

// Generate token
$token = bin2hex(random_bytes(40));
$tok   = $conn->prepare(
    "INSERT INTO personal_access_tokens (user_id, token) VALUES (?,?)"
);
$tok->bind_param('is', $uid, $token);
$tok->execute();

$user = [
    'id'                  => $uid,
    'first_name'          => $first,
    'last_name'           => $last,
    'email'               => $email,
    'email_notifications' => true,
    'daily_summary'       => false,
    'budget_alerts'       => true,
];

jsonOk(['token' => $token, 'user' => $user], 201);
