<?php
require_once 'config.php';

$method = $_SERVER['REQUEST_METHOD'];

// ── GET ALL THREATS ────────────────────────────────────────
if ($method === 'GET') {
    $stmt = $pdo->query("
        SELECT t.*, CONCAT(u.FirstName,' ',u.LastName) AS AnalystName
        FROM threats t
        LEFT JOIN users u ON t.AssignedTo = u.UserID
        ORDER BY
            FIELD(t.Severity,'Critical','High','Medium','Low'),
            t.DetectedDate DESC
    ");
    $data = $stmt->fetchAll();
    echo json_encode(["success" => true, "data" => $data, "count" => count($data)]);
}

// ── CREATE THREAT ──────────────────────────────────────────
elseif ($method === 'POST') {
    $data = json_decode(file_get_contents("php://input"), true);

    if (empty($data['threatName']) || empty($data['threatType']) || empty($data['severity'])) {
        echo json_encode(["success" => false, "message" => "Name, type, and severity are required"]);
        exit();
    }

    $stmt = $pdo->prepare("INSERT INTO threats
        (ThreatName, ThreatType, Severity, Status, AffectedAsset, SourceIndicator, Description, AssignedTo)
        VALUES (?,?,?,?,?,?,?,?)");
    $stmt->execute([
        $data['threatName'],
        $data['threatType'],
        $data['severity'],
        $data['status']          ?? 'Active',
        $data['affectedAsset']   ?? '',
        $data['sourceIndicator'] ?? '',
        $data['description']     ?? '',
        !empty($data['assignedTo']) ? $data['assignedTo'] : null
    ]);

    echo json_encode(["success" => true, "message" => "Threat recorded successfully", "threatID" => $pdo->lastInsertId()]);
}

// ── UPDATE THREAT ──────────────────────────────────────────
elseif ($method === 'PUT') {
    $data = json_decode(file_get_contents("php://input"), true);

    if (empty($data['threatID'])) {
        echo json_encode(["success" => false, "message" => "Threat ID is required"]);
        exit();
    }

    $stmt = $pdo->prepare("UPDATE threats SET
        ThreatName=?, ThreatType=?, Severity=?, Status=?,
        AffectedAsset=?, SourceIndicator=?, Description=?, AssignedTo=?
        WHERE ThreatID=?");
    $stmt->execute([
        $data['threatName'],
        $data['threatType'],
        $data['severity'],
        $data['status'],
        $data['affectedAsset']   ?? '',
        $data['sourceIndicator'] ?? '',
        $data['description']     ?? '',
        !empty($data['assignedTo']) ? $data['assignedTo'] : null,
        $data['threatID']
    ]);

    echo json_encode(["success" => true, "message" => "Threat updated successfully"]);
}

// ── DELETE THREAT ──────────────────────────────────────────
elseif ($method === 'DELETE') {
    $id = $_GET['id'] ?? 0;

    if (!$id) {
        echo json_encode(["success" => false, "message" => "Threat ID is required"]);
        exit();
    }

    $stmt = $pdo->prepare("DELETE FROM threats WHERE ThreatID = ?");
    $stmt->execute([$id]);
    echo json_encode(["success" => true, "message" => "Threat deleted successfully"]);
}
