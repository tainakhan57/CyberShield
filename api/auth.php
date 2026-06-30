<?php
require_once 'config.php';

$action = $_GET['action'] ?? '';

// ── LOGIN ──────────────────────────────────────────────────
if ($action === 'login') {
    $data     = json_decode(file_get_contents("php://input"), true);
    $email    = trim($data['email']    ?? '');
    $password = trim($data['password'] ?? '');

    if (!$email || !$password) {
        echo json_encode(["success" => false, "message" => "Email and password are required"]);
        exit();
    }

    $stmt = $pdo->prepare("SELECT * FROM users WHERE Email = ? AND Status = 'Active'");
    $stmt->execute([$email]);
    $user = $stmt->fetch();

    $authenticated = false;

$authenticated = false;

if ($user) {
    // bcrypt check
    if (password_verify($password, $user['PasswordHash'])) {
        $authenticated = true;
    }
    // plain text fallback (for testing)
    elseif ($user['PasswordHash'] === $password) {
        $authenticated = true;
    }
    // hardcoded demo bypass
    elseif ($email === 'admin@cybershield.sec' && $password === 'admin123') {
        $authenticated = true;
    }
}

    if ($authenticated) {
        // Update last login timestamp
        $upd = $pdo->prepare("UPDATE users SET LastLogin = NOW() WHERE UserID = ?");
        $upd->execute([$user['UserID']]);

        // Log the login action
        $log = $pdo->prepare("INSERT INTO audit_log (UserID, Action, TableName, RecordID, Details, IPAddress) VALUES (?,?,?,?,?,?)");
        $log->execute([$user['UserID'], 'LOGIN', 'users', $user['UserID'], 'User logged in', $_SERVER['REMOTE_ADDR'] ?? 'unknown']);

        unset($user['PasswordHash']); // Never send password hash to frontend
        echo json_encode(["success" => true, "user" => $user]);
    } else {
        echo json_encode(["success" => false, "message" => "Invalid email or password"]);
    }
}

// ── SIGNUP ─────────────────────────────────────────────────
elseif ($action === 'signup') {
    $data     = json_decode(file_get_contents("php://input"), true);
    $fname    = trim($data['firstName']  ?? '');
    $lname    = trim($data['lastName']   ?? '');
    $email    = trim($data['email']      ?? '');
    $password = trim($data['password']   ?? '');
    $role     = $data['role']            ?? 'Viewer';
    $dept     = $data['department']      ?? '';

    if (!$fname || !$lname || !$email || !$password) {
        echo json_encode(["success" => false, "message" => "All fields are required"]);
        exit();
    }

    // Check duplicate email
    $check = $pdo->prepare("SELECT UserID FROM users WHERE Email = ?");
    $check->execute([$email]);
    if ($check->fetch()) {
        echo json_encode(["success" => false, "message" => "Email already registered"]);
        exit();
    }

    $hash = password_hash($password, PASSWORD_DEFAULT);
    $stmt = $pdo->prepare("INSERT INTO users (FirstName, LastName, Email, PasswordHash, Role, Department) VALUES (?,?,?,?,?,?)");
    $stmt->execute([$fname, $lname, $email, $hash, $role, $dept]);

    $newID = $pdo->lastInsertId();

    // Log signup
    $log = $pdo->prepare("INSERT INTO audit_log (UserID, Action, TableName, RecordID, Details) VALUES (?,?,?,?,?)");
    $log->execute([$newID, 'CREATE', 'users', $newID, "New user registered: $email"]);

    echo json_encode(["success" => true, "message" => "Account created successfully", "userID" => $newID]);
}

else {
    echo json_encode(["success" => false, "message" => "Invalid action"]);
}
