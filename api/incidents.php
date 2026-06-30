<?php
require_once 'config.php';

$method = $_SERVER['REQUEST_METHOD'];

// ── GET ALL INCIDENTS ──────────────────────────────────────
if ($method === 'GET') {
    $stmt = $pdo->query("
        SELECT i.*, CONCAT(u.FirstName,' ',u.LastName) AS AnalystName
        FROM incidents i
        LEFT JOIN users u ON i.AssignedTo = u.UserID
        ORDER BY
            FIELD(i.Priority,'Critical','High','Medium','Low'),
            i.ReportedDate DESC
    ");
    $data = $stmt->fetchAll();
    echo json_encode(["success" => true, "data" => $data, "count" => count($data)]);
}

// ── CREATE INCIDENT ────────────────────────────────────────
elseif ($method === 'POST') {
    $data = json_decode(file_get_contents("php://input"), true);

    if (empty($data['incidentType']) || empty($data['priority']) || empty($data['affectedSystem'])) {
        echo json_encode(["success" => false, "message" => "Type, priority, and affected system are required"]);
        exit();
    }

    $stmt = $pdo->prepare("INSERT INTO incidents
        (IncidentType, Priority, AffectedSystem, Status, Description, ResolutionNotes, AssignedTo)
        VALUES (?,?,?,?,?,?,?)");
    $stmt->execute([
        $data['incidentType'],
        $data['priority'],
        $data['affectedSystem'],
        $data['status']          ?? 'Open',
        $data['description']     ?? '',
        $data['resolutionNotes'] ?? '',
        !empty($data['assignedTo']) ? $data['assignedTo'] : null
    ]);

    echo json_encode(["success" => true, "message" => "Incident logged successfully", "incidentID" => $pdo->lastInsertId()]);
}

// ── UPDATE INCIDENT ────────────────────────────────────────
elseif ($method === 'PUT') {
    $data = json_decode(file_get_contents("php://input"), true);

    if (empty($data['incidentID'])) {
        echo json_encode(["success" => false, "message" => "Incident ID is required"]);
        exit();
    }

    // Set ResolvedDate if status is Resolved or Closed
    $resolvedDate = in_array($data['status'], ['Resolved', 'Closed']) ? date('Y-m-d H:i:s') : null;

    $stmt = $pdo->prepare("UPDATE incidents SET
        IncidentType=?, Priority=?, AffectedSystem=?, Status=?,
        Description=?, ResolutionNotes=?, AssignedTo=?, ResolvedDate=?
        WHERE IncidentID=?");
    $stmt->execute([
        $data['incidentType'],
        $data['priority'],
        $data['affectedSystem'],
        $data['status'],
        $data['description']     ?? '',
        $data['resolutionNotes'] ?? '',
        !empty($data['assignedTo']) ? $data['assignedTo'] : null,
        $resolvedDate,
        $data['incidentID']
    ]);

    echo json_encode(["success" => true, "message" => "Incident updated successfully"]);
}

// ── DELETE INCIDENT ────────────────────────────────────────
elseif ($method === 'DELETE') {
    $id = $_GET['id'] ?? 0;

    if (!$id) {
        echo json_encode(["success" => false, "message" => "Incident ID is required"]);
        exit();
    }

    $stmt = $pdo->prepare("DELETE FROM incidents WHERE IncidentID = ?");
    $stmt->execute([$id]);
    echo json_encode(["success" => true, "message" => "Incident deleted successfully"]);
}
