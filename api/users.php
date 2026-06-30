<?php
require_once 'config.php';

$method = $_SERVER['REQUEST_METHOD'];

// ── GET ALL USERS ──────────────────────────────────────────
if ($method === 'GET') {
    $stmt = $pdo->query("
        SELECT UserID, FirstName, LastName, Email, Role,
               Department, Status, MFA_Enabled, LastLogin, CreatedAt
        FROM users
        ORDER BY UserID ASC
    ");
    $data = $stmt->fetchAll();
    echo json_encode(["success" => true, "data" => $data, "count" => count($data)]);
}

// ── CREATE USER ────────────────────────────────────────────
elseif ($method === 'POST') {
    $data = json_decode(file_get_contents("php://input"), true);

    if (empty($data['firstName']) || empty($data['lastName']) || empty($data['email']) || empty($data['password'])) {
        echo json_encode(["success" => false, "message" => "First name, last name, email, and password are required"]);
        exit();
    }

    // Check duplicate email
    $check = $pdo->prepare("SELECT UserID FROM users WHERE Email = ?");
    $check->execute([$data['email']]);
    if ($check->fetch()) {
        echo json_encode(["success" => false, "message" => "Email already exists"]);
        exit();
    }

    $hash = password_hash($data['password'], PASSWORD_DEFAULT);
    $stmt = $pdo->prepare("INSERT INTO users
        (FirstName, LastName, Email, PasswordHash, Role, Department, Status, MFA_Enabled)
        VALUES (?,?,?,?,?,?,?,?)");
    $stmt->execute([
        $data['firstName'],
        $data['lastName'],
        $data['email'],
        $hash,
        $data['role']       ?? 'Analyst',
        $data['department'] ?? '',
        $data['status']     ?? 'Active',
        isset($data['mfaEnabled']) && $data['mfaEnabled'] ? 1 : 0
    ]);

    echo json_encode(["success" => true, "message" => "User created successfully", "userID" => $pdo->lastInsertId()]);
}

// ── UPDATE USER ────────────────────────────────────────────
elseif ($method === 'PUT') {
    $data = json_decode(file_get_contents("php://input"), true);

    if (empty($data['userID'])) {
        echo json_encode(["success" => false, "message" => "User ID is required"]);
        exit();
    }

    $stmt = $pdo->prepare("UPDATE users SET
        FirstName=?, LastName=?, Email=?, Role=?,
        Department=?, Status=?, MFA_Enabled=?
        WHERE UserID=?");
    $stmt->execute([
        $data['firstName'],
        $data['lastName'],
        $data['email'],
        $data['role'],
        $data['department'] ?? '',
        $data['status']     ?? 'Active',
        isset($data['mfaEnabled']) && $data['mfaEnabled'] ? 1 : 0,
        $data['userID']
    ]);

    echo json_encode(["success" => true, "message" => "User updated successfully"]);
}

// ── DEACTIVATE USER (soft delete) ──────────────────────────
elseif ($method === 'DELETE') {
    $id = $_GET['id'] ?? 0;

    if (!$id) {
        echo json_encode(["success" => false, "message" => "User ID is required"]);
        exit();
    }

    $stmt = $pdo->prepare("UPDATE users SET Status='Inactive' WHERE UserID=?");
    $stmt->execute([$id]);
    echo json_encode(["success" => true, "message" => "User deactivated successfully"]);
}
