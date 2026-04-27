<?php
require_once __DIR__ . '/../layouts/functions.php';
require_login();
require_permission($pdo, 'view_reports');

// Fetch logs
$logs_stmt = $pdo->prepare("SELECT l.created_at, l.action, l.details, u.full_name
                     FROM logs l
                     LEFT JOIN users u ON l.user_id = u.id
                     ORDER BY l.created_at DESC LIMIT 100");
$logs_stmt->execute();
$logs = $logs_stmt->fetchAll();

// Set headers to trigger file download
header('Content-Type: text/csv; charset=utf-8');
header('Content-Disposition: attachment; filename="system_reports_' . date('Ymd_His') . '.csv"');
header('Pragma: no-cache');
header('Expires: 0');

// Open a file pointer directly to standard output
$output = fopen('php://output', 'w');

// Output BOM for UTF-8 (helps Excel open properly)
fprintf($output, chr(0xEF).chr(0xBB).chr(0xBF));

// Output column headings
fputcsv($output, array('Timestamp', 'User', 'Action', 'Details'));

// Output data rows
foreach ($logs as $row) {
    fputcsv($output, array(
        date('M d, Y h:i A', strtotime($row->created_at)),
        $row->full_name ?? 'System',
        strtoupper($row->action),
        $row->details ?? '-'
    ));
}

fclose($output);
exit();
