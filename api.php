<?php
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');

$db = new PDO('sqlite:/tmp/panel.db');
$db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
$db->exec("CREATE TABLE IF NOT EXISTS licenses (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    key_code TEXT UNIQUE,
    hwid TEXT,
    status TEXT DEFAULT 'unused',
    duration_days INTEGER DEFAULT 30,
    max_devices INTEGER DEFAULT 1,
    expires_at DATETIME,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP
)");

$key = $_POST['key'] ?? $_GET['key'] ?? '';
$hwid = $_POST['hwid'] ?? $_GET['hwid'] ?? '';

if (empty($key) || empty($hwid)) {
    die(json_encode(["status" => false, "reason" => "Missing parameters"]));
}

$stmt = $db->prepare("SELECT * FROM licenses WHERE key_code = ?");
$stmt->execute([$key]);
$row = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$row) die(json_encode(["status" => false, "reason" => "Invalid key"]));
if ($row['status'] === 'banned') die(json_encode(["status" => false, "reason" => "Key banned"]));

$hwids = empty($row['hwid']) ? [] : json_decode($row['hwid'], true);
if (!is_array($hwids)) $hwids = [];

if ($row['status'] === 'unused') {
    $expires_at = ($row['duration_days'] > 0) ? date('Y-m-d H:i:s', strtotime("+{$row['duration_days']} days")) : null;
    $new_hwids = json_encode([$hwid]);
    $update = $db->prepare("UPDATE licenses SET hwid = ?, status = 'active', expires_at = ? WHERE id = ?");
    $update->execute([$new_hwids, $expires_at, $row['id']]);
    die(json_encode(["status" => true, "reason" => "Key activated"]));
}

if ($row['status'] === 'active') {
    if ($row['expires_at'] !== null && strtotime($row['expires_at']) < time()) {
        die(json_encode(["status" => false, "reason" => "Key expired"]));
    }
    
    if (!in_array($hwid, $hwids)) {
        if (count($hwids) >= $row['max_devices']) {
            die(json_encode(["status" => false, "reason" => "Max devices reached"]));
        }
        $hwids[] = $hwid;
        $update = $db->prepare("UPDATE licenses SET hwid = ? WHERE id = ?");
        $update->execute([json_encode($hwids), $row['id']]);
    }
    die(json_encode(["status" => true, "reason" => "Login success"]));
}
?>
