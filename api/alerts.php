<?php
require_once 'config.php';

$method = $_SERVER['REQUEST_METHOD'];

// ── GET ALL ALERTS ─────────────────────────────────────────
if ($method === 'GET') {
    $stmt = $pdo->query("
        SELECT a.*,
               t.ThreatName  AS ThreatName,
               ast.AssetName AS AssetName
        FROM alerts a
        LEFT JOIN threats t   ON a.RelatedThreat = t.ThreatID
        LEFT JOIN assets  ast ON a.RelatedAsset  = ast.AssetID
        ORDER BY
            FIELD(a.Status,'New','Acknowledged','Dismissed'),
            FIELD(a.Severity,'Critical','High','Medium','Low'),
            a.CreatedAt DESC
    ");
    $data = $stmt->fetchAll();
    echo json_encode(["success" => true, "data" => $data, "count" => count($data)]);
}

// ── CREATE ALERT ───────────────────────────────────────────
elseif ($method === 'POST') {
    $data = json_decode(file_get_contents("php://input"), true);

    if (empty($data['alertType']) || empty($data['severity']) || empty($data['message'])) {
        echo json_encode(["success" => false, "message" => "Alert type, severity, and message are required"]);
        exit();
    }

    $stmt = $pdo->prepare("INSERT INTO alerts
        (AlertType, Severity, Message, RelatedThreat, RelatedAsset, Status)
        VALUES (?,?,?,?,?,?)");
    $stmt->execute([
        $data['alertType'],
        $data['severity'],
        $data['message'],
        !empty($data['relatedThreat']) ? $data['relatedThreat'] : null,
        !empty($data['relatedAsset'])  ? $data['relatedAsset']  : null,
        $data['status'] ?? 'New'
    ]);

    echo json_encode(["success" => true, "message" => "Alert created", "alertID" => $pdo->lastInsertId()]);
}

// ── UPDATE ALERT ───────────────────────────────────────────
elseif ($method === 'PUT') {
    $data = json_decode(file_get_contents("php://input"), true);

    if (empty($data['alertID'])) {
        echo json_encode(["success" => false, "message" => "Alert ID is required"]);
        exit();
    }

    $stmt = $pdo->prepare("UPDATE alerts SET
        AlertType=?, Severity=?, Message=?,
        RelatedThreat=?, RelatedAsset=?, Status=?
        WHERE AlertID=?");
    $stmt->execute([
        $data['alertType'],
        $data['severity'],
        $data['message'],
        !empty($data['relatedThreat']) ? $data['relatedThreat'] : null,
        !empty($data['relatedAsset'])  ? $data['relatedAsset']  : null,
        $data['status'] ?? 'New',
        $data['alertID']
    ]);

    echo json_encode(["success" => true, "message" => "Alert updated"]);
}

// ── DELETE ALERT ───────────────────────────────────────────
elseif ($method === 'DELETE') {
    $id = $_GET['id'] ?? 0;

    if (!$id) {
        echo json_encode(["success" => false, "message" => "Alert ID is required"]);
        exit();
    }

    $stmt = $pdo->prepare("DELETE FROM alerts WHERE AlertID = ?");
    $stmt->execute([$id]);
    echo json_encode(["success" => true, "message" => "Alert deleted"]);
}
