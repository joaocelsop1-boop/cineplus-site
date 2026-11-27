<?php
require __DIR__ . '/../config.php';
header('Content-Type: application/json; charset=utf-8');
if ($_SERVER['REQUEST_METHOD'] !== 'POST') { http_response_code(405); echo json_encode(['ok' => false]); exit; }

$in = json_decode(file_get_contents('php://input'), true) ?: [];
$name = trim((string)($in['name'] ?? ''));
$wa = only_digits((string)($in['whatsapp'] ?? ''));
$cut = fn($k, $n) => isset($in[$k]) && $in[$k] !== '' ? mb_substr(trim((string)$in[$k]), 0, $n) : null;

if (mb_strlen($name) < 2 || mb_strlen($name) > 100) { http_response_code(422); echo json_encode(['ok' => false, 'error' => 'Digite seu nome']); exit; }
if (!preg_match('/^[0-9]{10,13}$/', $wa)) { http_response_code(422); echo json_encode(['ok' => false, 'error' => 'Digite seu WhatsApp com DDD']); exit; }

// Anti-spam simples: no máximo 5 envios por IP a cada 10 minutos
$ip = $_SERVER['REMOTE_ADDR'] ?? '';
$st = db()->prepare('SELECT COUNT(*) FROM leads WHERE ip = ? AND created_at > (NOW() - INTERVAL 10 MINUTE)');
$st->execute([$ip]);
if ($st->fetchColumn() >= 5) { http_response_code(429); echo json_encode(['ok' => false, 'error' => 'Muitos envios. Tente mais tarde.']); exit; }

$st = db()->prepare('INSERT INTO leads (name, whatsapp, device, people, interests, plan_name, ip) VALUES (?,?,?,?,?,?,?)');
$st->execute([$name, $wa, $cut('device', 50), $cut('people', 50), $cut('interests', 200), $cut('plan_name', 100), $ip]);
echo json_encode(['ok' => true]);
