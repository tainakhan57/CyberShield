<?php
require_once 'config.php';

$method = $_SERVER['REQUEST_METHOD'];

// ── GET ALL ASSETS ─────────────────────────────────────────
if ($method === 'GET') {
    $stmt = $pdo->query("SELECT * FROM assets ORDER BY AssetID DESC");
    $data = $stmt->fetchAll();
    echo json_encode(["success" => true, "data" => $data, "count" => count($data)]);
}

// ── CREATE ASSET ───────────────────────────────────────────
elseif ($method === 'POST') {
    $data = json_decode(file_get_contents("php://input"), true);

    if (empty($data['assetName']) || empty($data['assetType'])) {
        echo json_encode(["success" => false, "message" => "Asset name and type are required"]);
        exit();
    }

    $stmt = $pdo->prepare("INSERT INTO assets
        (AssetName, AssetType, IPAddress, MACAddress, OperatingSystem, Owner_Department, RiskScore, Status, Notes, LastScanned)
        VALUES (?,?,?,?,?,?,?,?,?,?)");
    $stmt->execute([
        $data['assetName'],
        $data['assetType'],
        $data['ipAddress']        ?? 'N/A',
        $data['macAddress']       ?? 'N/A',
        $data['operatingSystem']  ?? 'Unknown',
        $data['ownerDepartment']  ?? 'Unassigned',
        $data['riskScore']        ?? 0,
        $data['status']           ?? 'Active',
        $data['notes']            ?? '',
        date('Y-m-d')
    ]);

    $newID = $pdo->lastInsertId();
    echo json_encode(["success" => true, "message" => "Asset registered successfully", "assetID" => $newID]);
}

// ── UPDATE ASSET ───────────────────────────────────────────
elseif ($method === 'PUT') {
    $data = json_decode(file_get_contents("php://input"), true);

    if (empty($data['assetID'])) {
        echo json_encode(["success" => false, "message" => "Asset ID is required"]);
        exit();
    }

    $stmt = $pdo->prepare("UPDATE assets SET
        AssetName=?, AssetType=?, IPAddress=?, MACAddress=?,
        OperatingSystem=?, Owner_Department=?, RiskScore=?, Status=?, Notes=?
        WHERE AssetID=?");
    $stmt->execute([
        $data['assetName'],
        $data['assetType'],
        $data['ipAddress']       ?? 'N/A',
        $data['macAddress']      ?? 'N/A',
        $data['operatingSystem'] ?? 'Unknown',
        $data['ownerDepartment'] ?? 'Unassigned',
        $data['riskScore']       ?? 0,
        $data['status']          ?? 'Active',
        $data['notes']           ?? '',
        $data['assetID']
    ]);

    echo json_encode(["success" => true, "message" => "Asset updated successfully"]);
}

// ── SCAN ASSET (update LastScanned) ───────────────────────
elseif ($method === 'PATCH') {
    $data = json_decode(file_get_contents("php://input"), true);
    $id   = $data['assetID'] ?? 0;

    $stmt = $pdo->prepare("UPDATE assets SET LastScanned = CURDATE() WHERE AssetID = ?");
    $stmt->execute([$id]);

    // Create a scan record
    $scan = $pdo->prepare("INSERT INTO scans (AssetID, ScanType, Status, Findings, CompletedAt) VALUES (?,?,?,?,NOW())");
    $scan->execute([$id, 'Quick Scan', 'Completed', 'Automated scan completed. Review findings manually.']);

    echo json_encode(["success" => true, "message" => "Scan completed", "scanID" => $pdo->lastInsertId()]);
}

// ── DELETE ASSET ───────────────────────────────────────────
elseif ($method === 'DELETE') {
    $id = $_GET['id'] ?? 0;

    if (!$id) {
        echo json_encode(["success" => false, "message" => "Asset ID is required"]);
        exit();
    }

    $stmt = $pdo->prepare("DELETE FROM assets WHERE AssetID = ?");
    $stmt->execute([$id]);

    echo json_encode(["success" => true, "message" => "Asset deleted successfully"]);
}
