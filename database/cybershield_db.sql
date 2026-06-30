-- ============================================================
--  CyberShield — Cybersecurity Management System
--  Database Schema + Sample Data
--  Version: 1.0
--  Run this file in phpMyAdmin → SQL tab
-- ============================================================

CREATE DATABASE IF NOT EXISTS cybershield_db
  CHARACTER SET utf8mb4
  COLLATE utf8mb4_unicode_ci;

USE cybershield_db;

-- ============================================================
-- TABLE 1: users
-- ============================================================
CREATE TABLE IF NOT EXISTS users (
    UserID       INT AUTO_INCREMENT PRIMARY KEY,
    FirstName    VARCHAR(50)  NOT NULL,
    LastName     VARCHAR(50)  NOT NULL,
    Email        VARCHAR(100) NOT NULL UNIQUE,
    PasswordHash VARCHAR(255) NOT NULL,
    Role         ENUM('Admin','Analyst','Viewer') NOT NULL DEFAULT 'Analyst',
    Department   VARCHAR(100),
    Status       ENUM('Active','Inactive') NOT NULL DEFAULT 'Active',
    MFA_Enabled  TINYINT(1)   NOT NULL DEFAULT 0,
    LastLogin    DATETIME,
    CreatedAt    TIMESTAMP    NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB;

-- ============================================================
-- TABLE 2: assets
-- ============================================================
CREATE TABLE IF NOT EXISTS assets (
    AssetID          INT AUTO_INCREMENT PRIMARY KEY,
    AssetName        VARCHAR(100) NOT NULL,
    AssetType        ENUM('Server','Workstation','Network Device','Application','Database','Cloud Resource','IoT Device') NOT NULL,
    IPAddress        VARCHAR(45)  DEFAULT 'N/A',
    MACAddress       VARCHAR(17)  DEFAULT 'N/A',
    OperatingSystem  VARCHAR(100) DEFAULT 'Unknown',
    Owner_Department VARCHAR(100) DEFAULT 'Unassigned',
    RiskScore        TINYINT      NOT NULL DEFAULT 0 CHECK (RiskScore BETWEEN 0 AND 100),
    Status           ENUM('Active','Vulnerable','Patched','Offline','Decommissioned') NOT NULL DEFAULT 'Active',
    Notes            TEXT,
    LastScanned      DATE,
    CreatedAt        TIMESTAMP    NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB;

-- ============================================================
-- TABLE 3: threats
-- ============================================================
CREATE TABLE IF NOT EXISTS threats (
    ThreatID        INT AUTO_INCREMENT PRIMARY KEY,
    ThreatName      VARCHAR(150) NOT NULL,
    ThreatType      VARCHAR(100) NOT NULL,
    Severity        ENUM('Critical','High','Medium','Low') NOT NULL,
    Status          ENUM('Active','Investigating','Mitigated','Closed') NOT NULL DEFAULT 'Active',
    AffectedAsset   VARCHAR(100),
    SourceIndicator VARCHAR(200),
    Description     TEXT,
    AssignedTo      INT,
    DetectedDate    TIMESTAMP    NOT NULL DEFAULT CURRENT_TIMESTAMP,
    CONSTRAINT fk_threat_user
        FOREIGN KEY (AssignedTo) REFERENCES users(UserID)
        ON DELETE SET NULL ON UPDATE CASCADE
) ENGINE=InnoDB;

-- ============================================================
-- TABLE 4: incidents
-- ============================================================
CREATE TABLE IF NOT EXISTS incidents (
    IncidentID      INT AUTO_INCREMENT PRIMARY KEY,
    IncidentType    VARCHAR(100) NOT NULL,
    Priority        ENUM('Critical','High','Medium','Low') NOT NULL,
    AffectedSystem  VARCHAR(150) NOT NULL,
    Status          ENUM('Open','Investigating','Resolved','Closed') NOT NULL DEFAULT 'Open',
    Description     TEXT,
    ResolutionNotes TEXT,
    AssignedTo      INT,
    ReportedDate    TIMESTAMP    NOT NULL DEFAULT CURRENT_TIMESTAMP,
    ResolvedDate    DATETIME,
    CONSTRAINT fk_incident_user
        FOREIGN KEY (AssignedTo) REFERENCES users(UserID)
        ON DELETE SET NULL ON UPDATE CASCADE
) ENGINE=InnoDB;

-- ============================================================
-- TABLE 5: alerts
-- ============================================================
CREATE TABLE IF NOT EXISTS alerts (
    AlertID       INT AUTO_INCREMENT PRIMARY KEY,
    AlertType     VARCHAR(100) NOT NULL,
    Severity      ENUM('Critical','High','Medium','Low') NOT NULL,
    Message       TEXT,
    RelatedThreat INT,
    RelatedAsset  INT,
    Status        ENUM('New','Acknowledged','Dismissed') NOT NULL DEFAULT 'New',
    CreatedAt     TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    CONSTRAINT fk_alert_threat
        FOREIGN KEY (RelatedThreat) REFERENCES threats(ThreatID)
        ON DELETE SET NULL ON UPDATE CASCADE,
    CONSTRAINT fk_alert_asset
        FOREIGN KEY (RelatedAsset) REFERENCES assets(AssetID)
        ON DELETE SET NULL ON UPDATE CASCADE
) ENGINE=InnoDB;

-- ============================================================
-- TABLE 6: scans
-- ============================================================
CREATE TABLE IF NOT EXISTS scans (
    ScanID      INT AUTO_INCREMENT PRIMARY KEY,
    AssetID     INT,
    ScanType    VARCHAR(50)  DEFAULT 'Full Scan',
    Status      ENUM('Pending','Running','Completed','Failed') NOT NULL DEFAULT 'Pending',
    Findings    TEXT,
    InitiatedBy INT,
    StartedAt   TIMESTAMP    NOT NULL DEFAULT CURRENT_TIMESTAMP,
    CompletedAt DATETIME,
    CONSTRAINT fk_scan_asset
        FOREIGN KEY (AssetID) REFERENCES assets(AssetID)
        ON DELETE CASCADE ON UPDATE CASCADE,
    CONSTRAINT fk_scan_user
        FOREIGN KEY (InitiatedBy) REFERENCES users(UserID)
        ON DELETE SET NULL ON UPDATE CASCADE
) ENGINE=InnoDB;

-- ============================================================
-- TABLE 7: audit_log
-- ============================================================
CREATE TABLE IF NOT EXISTS audit_log (
    LogID      INT AUTO_INCREMENT PRIMARY KEY,
    UserID     INT,
    Action     VARCHAR(50)  NOT NULL,
    TableName  VARCHAR(50)  NOT NULL,
    RecordID   INT,
    Details    TEXT,
    IPAddress  VARCHAR(45),
    CreatedAt  TIMESTAMP    NOT NULL DEFAULT CURRENT_TIMESTAMP,
    CONSTRAINT fk_log_user
        FOREIGN KEY (UserID) REFERENCES users(UserID)
        ON DELETE SET NULL ON UPDATE CASCADE
) ENGINE=InnoDB;

-- ============================================================
-- SAMPLE DATA
-- Password for ALL users below = admin123
-- Hash generated with: password_hash('admin123', PASSWORD_DEFAULT)
-- ============================================================

INSERT INTO users (FirstName, LastName, Email, PasswordHash, Role, Department, Status, MFA_Enabled) VALUES
('John',   'Doe',      'admin@cybershield.sec',    '$2y$10$TKh8H1.PfQx37YgCzwiKb.KjNyWgaHb9cbcoQgdIUes22bKcWQxuy', 'Admin',   'Security Operations', 'Active', 1),
('Maria',  'Garcia',   'analyst@cybershield.sec',  '$2y$10$TKh8H1.PfQx37YgCzwiKb.KjNyWgaHb9cbcoQgdIUes22bKcWQxuy', 'Analyst', 'Incident Response',   'Active', 1),
('Samuel', 'Chen',     's.chen@cybershield.sec',   '$2y$10$TKh8H1.PfQx37YgCzwiKb.KjNyWgaHb9cbcoQgdIUes22bKcWQxuy', 'Analyst', 'Threat Intelligence',  'Active', 1),
('Rohan',  'Patel',    'r.patel@cybershield.sec',  '$2y$10$TKh8H1.PfQx37YgCzwiKb.KjNyWgaHb9cbcoQgdIUes22bKcWQxuy', 'Analyst', 'Security Operations', 'Active', 0),
('Tracy',  'Williams', 't.williams@cybershield.sec','$2y$10$TKh8H1.PfQx37YgCzwiKb.KjNyWgaHb9cbcoQgdIUes22bKcWQxuy', 'Analyst', 'Compliance',          'Active', 1),
('Karen',  'Lee',      'k.lee@cybershield.sec',    '$2y$10$TKh8H1.PfQx37YgCzwiKb.KjNyWgaHb9cbcoQgdIUes22bKcWQxuy', 'Viewer',  'Management',          'Active', 1),
('Jamie',  'Smith',    'j.smith@cybershield.sec',  '$2y$10$TKh8H1.PfQx37YgCzwiKb.KjNyWgaHb9cbcoQgdIUes22bKcWQxuy', 'Viewer',  'Management',          'Inactive',0);

INSERT INTO assets (AssetName, AssetType, IPAddress, MACAddress, OperatingSystem, Owner_Department, RiskScore, Status, Notes, LastScanned) VALUES
('DB-SERVER-01',    'Server',         '192.168.10.5',  'A1:B2:C3:D4:E5:F6', 'Ubuntu 22.04 LTS',  'IT Infrastructure', 88, 'Vulnerable',      'Primary database server. Critical CVE-2025-1234 unpatched.',  '2025-11-15'),
('WEB-SERVER-04',   'Server',         '192.168.10.12', 'A1:B2:C3:D4:E5:F7', 'CentOS 8',          'Web Team',          55, 'Active',          'Hosts main public website.',                                  '2025-11-14'),
('WORKSTATION-22',  'Workstation',    '192.168.1.122', 'A1:B2:C3:D4:E5:F8', 'Windows 11 Pro',    'Finance Dept',      91, 'Vulnerable',      'Ransomware quarantine in progress.',                          '2025-11-15'),
('CORE-SWITCH-01',  'Network Device', '192.168.0.1',   'A1:B2:C3:D4:E5:F9', 'Cisco IOS 17.x',   'Network Team',      22, 'Active',          'Core network backbone switch.',                               '2025-11-10'),
('ERP-APP',         'Application',    '192.168.20.50', 'N/A',               'SAP S/4HANA',       'Finance Dept',      45, 'Patched',         'ERP system. Last patch applied 2025-11-01.',                  '2025-11-13'),
('MAIL-SERVER-01',  'Server',         '192.168.10.20', 'A1:B2:C3:D4:E5:FA', 'Exchange 2019',     'IT Infrastructure', 60, 'Active',          'Corporate email gateway.',                                    '2025-11-12'),
('WORKSTATION-11',  'Workstation',    '192.168.1.111', 'A1:B2:C3:D4:E5:FB', 'Windows 10 Pro',    'HR Dept',           30, 'Active',          'HR manager workstation.',                                     '2025-11-11');

INSERT INTO threats (ThreatName, ThreatType, Severity, Status, AffectedAsset, SourceIndicator, Description, AssignedTo) VALUES
('SQL Injection on DB-SERVER-01', 'SQL Injection',   'Critical', 'Investigating', 'DB-SERVER-01',   '203.0.113.47',  'Multiple SQL injection attempts detected targeting the primary database server login endpoint.', 2),
('Ransomware on WORKSTATION-22',  'Ransomware',      'Critical', 'Active',        'WORKSTATION-22', '198.51.100.23', 'Ransomware signature detected. Files being encrypted. Quarantine initiated immediately.',         3),
('Brute Force on Mail Server',    'Brute Force',     'High',     'Active',        'MAIL-SERVER-01', '10.0.0.55',     'Over 500 failed login attempts detected on the mail server in a 10-minute window.',              2),
('Phishing Campaign Detected',    'Phishing',        'High',     'Investigating', 'Multiple',       'phish@evil.com','Coordinated phishing emails impersonating IT support sent to 34 employees.',                    4),
('DDoS Attack Vector Identified', 'DDoS',            'Critical', 'Mitigated',     'WEB-SERVER-04',  '104.21.0.0/16', 'Distributed denial of service attack. Mitigation rules applied at firewall level.',             2),
('Insider Privilege Escalation',  'Insider Threat',  'Medium',   'Closed',        'CORE-SWITCH-01', 'Internal',      'User jdoe attempted unauthorized privilege escalation. Access revoked and incident logged.',      4),
('Zero-Day on CentOS 8',          'Zero-Day Exploit','High',     'Investigating', 'WEB-SERVER-04',  'CVE-2025-9999', 'Unpatched zero-day vulnerability discovered affecting CentOS 8 kernel. Patch pending release.',  3);

INSERT INTO incidents (IncidentType, Priority, AffectedSystem, Status, Description, ResolutionNotes, AssignedTo) VALUES
('Data Exfiltration',   'Critical', 'DB-SERVER-01',   'Investigating', 'Unusual outbound traffic detected from DB-SERVER-01. Possible data exfiltration in progress. 2.3GB transferred to unknown IP.', NULL, 2),
('Brute Force',         'High',     'MAIL-SERVER-01', 'Open',          'Brute force attack on corporate email gateway. 500+ failed attempts in 10 minutes from IP 10.0.0.55.', NULL, 2),
('Phishing',            'Medium',   'HR Workstations','Resolved',      'Phishing email campaign targeting HR department. 3 users clicked malicious link. Credentials reset.', 'Passwords reset for affected users. Security awareness training scheduled.', 4),
('Malware Infection',   'Critical', 'WORKSTATION-22', 'Resolved',      'Ransomware detected and quarantined on Finance workstation. Files encrypted before isolation.', 'System reimaged. Files restored from backup. Endpoint protection updated.', 3),
('Unauthorized Access', 'High',     'CORE-SWITCH-01', 'Closed',        'Unauthorized access attempt to network switch admin panel. Access blocked by firewall rule.', 'Firewall rules updated. Admin credentials rotated. MFA enforced on all network devices.', 4),
('System Outage',       'High',     'WEB-SERVER-04',  'Resolved',      'Web server unresponsive for 47 minutes due to DDoS attack. Service restored after mitigation.', 'DDoS protection activated. CDN rules updated. Uptime monitoring alerts configured.', 2),
('Insider Threat',      'Medium',   'HR System',      'Closed',        'Employee accessed files outside their authorization scope. 14 confidential HR records viewed.', 'Employee access revoked. Incident documented. Legal notified. RBAC policies reviewed.', 3);

INSERT INTO alerts (AlertType, Severity, Message, RelatedThreat, RelatedAsset, Status) VALUES
('Intrusion Detection',  'Critical', 'SQL Injection pattern detected on DB-SERVER-01 login endpoint. Immediate action required.',              1, 1, 'New'),
('Malware Alert',        'Critical', 'Ransomware signature matched on WORKSTATION-22. System quarantined automatically.',                      2, 3, 'Acknowledged'),
('Brute Force Detected', 'High',     'Excessive failed login attempts on MAIL-SERVER-01. Source IP blocked.',                                  3, 6, 'New'),
('Phishing Detected',    'High',     'Malicious email campaign identified. 34 employees targeted. Email gateway rules updated.',               4, NULL,'Acknowledged'),
('DDoS Mitigation',      'Critical', 'DDoS attack detected and mitigated on WEB-SERVER-04. Traffic scrubbing active.',                         5, 2, 'Dismissed'),
('Privilege Escalation', 'Medium',   'Privilege escalation attempt by user jdoe on CORE-SWITCH-01. Access revoked.',                          6, 4, 'Dismissed');

INSERT INTO scans (AssetID, ScanType, Status, Findings, InitiatedBy, CompletedAt) VALUES
(1, 'Full Vulnerability Scan', 'Completed', 'CVE-2025-1234 (Critical) — Unpatched MySQL vulnerability. CVE-2024-5678 (High) — OpenSSL outdated.', 1, '2025-11-15 15:30:00'),
(2, 'Port Scan',               'Completed', 'Open ports: 22, 80, 443, 8080. Port 8080 should be closed — no service assigned.', 1, '2025-11-14 12:00:00'),
(3, 'Malware Scan',            'Completed', 'Ransomware detected: WannaCry variant. 347 files encrypted before quarantine.', 2, '2025-11-15 09:45:00'),
(4, 'Configuration Audit',     'Completed', 'Default credentials found on 2 VLAN interfaces. Firmware version outdated.', 1, '2025-11-10 16:00:00'),
(5, 'Patch Compliance Scan',   'Completed', 'All critical patches applied. System compliant with security baseline.', 3, '2025-11-13 11:00:00'),
(6, 'Full Vulnerability Scan', 'Completed', 'Exchange ProxyLogon patch missing. Recommend immediate patching.', 2, '2025-11-12 14:30:00'),
(7, 'Quick Scan',              'Completed', 'No threats detected. Windows Defender definitions up to date.', 4, '2025-11-11 10:15:00');

INSERT INTO audit_log (UserID, Action, TableName, RecordID, Details, IPAddress) VALUES
(1, 'CREATE', 'users',    2, 'Created analyst account for Maria Garcia',          '192.168.1.1'),
(1, 'CREATE', 'threats',  1, 'Logged new Critical threat: SQL Injection',         '192.168.1.1'),
(2, 'UPDATE', 'incidents',1, 'Updated incident status to Investigating',           '192.168.1.50'),
(3, 'CREATE', 'scans',    3, 'Initiated malware scan on WORKSTATION-22',          '192.168.1.51'),
(1, 'DELETE', 'alerts',   5, 'Dismissed DDoS alert after mitigation confirmed',   '192.168.1.1'),
(2, 'UPDATE', 'threats',  5, 'Updated DDoS threat status to Mitigated',           '192.168.1.50');
