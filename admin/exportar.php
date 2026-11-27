<?php
require __DIR__ . '/../config.php';
require_admin();
$rows = db()->query('SELECT name, whatsapp, device, people, interests, plan_name, created_at FROM leads ORDER BY created_at DESC')->fetchAll();
header('Content-Type: text/csv; charset=utf-8');
header('Content-Disposition: attachment; filename="contatos-' . date('Y-m-d') . '.csv"');
$out = fopen('php://output', 'w');
fwrite($out, "\xEF\xBB\xBF"); // acentos no Excel
fputcsv($out, ['Nome', 'WhatsApp', 'Aparelho', 'Pessoas', 'Interesses', 'Plano', 'Data'], ';');
foreach ($rows as $r) {
    $r['created_at'] = date('d/m/Y H:i', strtotime($r['created_at']));
    // evita fórmulas maliciosas no Excel
    $r = array_map(fn($v) => preg_match('/^[=+\-@]/', (string)$v) ? "'" . $v : $v, $r);
    fputcsv($out, array_values($r), ';');
}
fclose($out);
