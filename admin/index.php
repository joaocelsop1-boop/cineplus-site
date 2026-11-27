<?php
require __DIR__ . '/../config.php';
require __DIR__ . '/_layout.php';
require_admin();
$msg = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    csrf_check();
    $a = $_POST['action'] ?? '';
    if ($a === 'settings') {
        db()->prepare('UPDATE site_settings SET brand_name=?, whatsapp=?, headline=?, subheadline=?, app_url=? WHERE id=1')->execute([
            mb_substr(trim($_POST['brand_name']), 0, 100), substr(only_digits($_POST['whatsapp']), 0, 20),
            mb_substr(trim($_POST['headline']), 0, 255), mb_substr(trim($_POST['subheadline']), 0, 255),
            mb_substr(trim($_POST['app_url']), 0, 255),
        ]);
        $msg = 'Textos e WhatsApp salvos.';
    } elseif ($a === 'plan_save') {
        $vals = [mb_substr(trim($_POST['name']), 0, 100), max(1, (int)$_POST['screens']), (float)str_replace(',', '.', $_POST['price']),
                 mb_substr(trim($_POST['period'] ?: 'mês'), 0, 20), isset($_POST['highlight']) ? 1 : 0, (int)$_POST['sort_order']];
        if (!empty($_POST['id'])) {
            $vals[] = (int)$_POST['id'];
            db()->prepare('UPDATE plans SET name=?, screens=?, price=?, period=?, highlight=?, sort_order=? WHERE id=?')->execute($vals);
        } else {
            db()->prepare('INSERT INTO plans (name, screens, price, period, highlight, sort_order) VALUES (?,?,?,?,?,?)')->execute($vals);
        }
        $msg = 'Plano salvo.';
    } elseif ($a === 'plan_delete') {
        db()->prepare('DELETE FROM plans WHERE id=?')->execute([(int)$_POST['id']]);
        $msg = 'Plano removido.';
    } elseif ($a === 'lead_delete') {
        db()->prepare('DELETE FROM leads WHERE id=?')->execute([(int)$_POST['id']]);
        $msg = 'Contato removido.';
    }
}
$s = settings();
$plans = plans();
$total = (int)db()->query('SELECT COUNT(*) FROM leads')->fetchColumn();
$week = (int)db()->query('SELECT COUNT(*) FROM leads WHERE created_at > NOW() - INTERVAL 7 DAY')->fetchColumn();
$leads = db()->query('SELECT * FROM leads ORDER BY created_at DESC LIMIT 500')->fetchAll();
$t = csrf_token();
admin_head('Painel — ' . ($s['brand_name'] ?? ''));
?>
<div class="wrap admin">
  <div class="row"><h1 style="font-size:44px">Painel <span class="neon"><?= e($s['brand_name']) ?></span></h1>
    <div><a class="btn btn-sec btn-sm" href="../" target="_blank">Ver site</a> <a class="btn btn-sec btn-sm" href="logout.php">Sair</a></div></div>
  <?php if ($msg): ?><p class="ok" style="margin-top:16px"><?= e($msg) ?></p><?php endif; ?>

  <div class="grid g3" style="margin-top:20px">
    <div class="card"><p class="label muted">Contatos total</p><p class="big"><?= $total ?></p></div>
    <div class="card"><p class="label muted">Últimos 7 dias</p><p class="big"><?= $week ?></p></div>
  </div>

  <div class="card" style="margin-top:20px">
    <div class="row"><h2 style="font-size:32px">Contatos</h2><a class="btn btn-neon btn-sm" href="exportar.php">⬇ Exportar planilha (CSV)</a></div>
    <div class="tbl-wrap" style="margin-top:12px"><table>
      <tr><th>Data</th><th>Nome</th><th>WhatsApp</th><th>Aparelho</th><th>Pessoas</th><th>Interesses</th><th>Plano</th><th></th></tr>
      <?php foreach ($leads as $l): ?>
      <tr><td><?= date('d/m/Y H:i', strtotime($l['created_at'])) ?></td><td><?= e($l['name']) ?></td>
        <td><a href="https://wa.me/<?= e($l['whatsapp']) ?>" target="_blank" style="color:var(--green)"><?= e($l['whatsapp']) ?></a></td>
        <td><?= e($l['device']) ?></td><td><?= e($l['people']) ?></td><td><?= e($l['interests']) ?></td><td><?= e($l['plan_name']) ?></td>
        <td><form method="post" onsubmit="return confirm('Remover este contato?')"><input type="hidden" name="csrf" value="<?= $t ?>"><input type="hidden" name="action" value="lead_delete"><input type="hidden" name="id" value="<?= $l['id'] ?>"><button class="back" style="margin:0">🗑</button></form></td></tr>
      <?php endforeach; if (!$leads): ?><tr><td colspan="8" class="muted">Nenhum contato ainda.</td></tr><?php endif; ?>
    </table></div>
  </div>

  <div class="grid g2" style="margin-top:20px;align-items:start">
    <form method="post" class="card fld">
      <h2 style="font-size:32px">Site e WhatsApp</h2>
      <input type="hidden" name="csrf" value="<?= $t ?>"><input type="hidden" name="action" value="settings">
      <label>Nome da marca</label><input class="input" name="brand_name" value="<?= e($s['brand_name']) ?>" required>
      <label>WhatsApp (com DDI e DDD, ex: 5511999999999)</label><input class="input" name="whatsapp" value="<?= e($s['whatsapp']) ?>" required>
      <label>URL do app (CinePlus) — para onde vai o botão "Assinar agora"</label><input class="input" name="app_url" value="<?= e($s['app_url'] ?? '') ?>" placeholder="https://cineplus.shop">
      <p class="muted" style="font-size:13px;margin-top:4px">Deixe vazio para usar a URL do config.php. Troque para https://cineplus.shop quando o domínio estiver no ar.</p>
      <label>Título principal</label><input class="input" name="headline" value="<?= e($s['headline']) ?>">
      <label>Subtítulo</label><input class="input" name="subheadline" value="<?= e($s['subheadline']) ?>">
      <button class="btn btn-neon" style="margin-top:16px">Salvar</button>
    </form>

    <div class="card">
      <h2 style="font-size:32px">Planos e preços</h2>
      <?php foreach (array_merge($plans, [['id' => '', 'name' => '', 'screens' => 1, 'price' => '', 'period' => 'mês', 'highlight' => 0, 'sort_order' => count($plans) + 1]]) as $p): ?>
      <form method="post" class="fld" style="border-top:1px solid var(--border);margin-top:14px;padding-top:6px">
        <input type="hidden" name="csrf" value="<?= $t ?>"><input type="hidden" name="id" value="<?= e($p['id']) ?>">
        <?php if (!$p['id']): ?><p class="label" style="color:var(--cyan);margin-top:10px">+ Novo plano</p><?php endif; ?>
        <div class="grid" style="grid-template-columns:2fr 1fr 1fr 1fr;gap:8px">
          <div><label>Nome</label><input class="input" name="name" value="<?= e($p['name']) ?>" required></div>
          <div><label>Telas</label><input class="input" type="number" min="1" name="screens" value="<?= e($p['screens']) ?>"></div>
          <div><label>Preço R$</label><input class="input" name="price" value="<?= $p['price'] !== '' ? money($p['price']) : '' ?>" required></div>
          <div><label>Ordem</label><input class="input" type="number" name="sort_order" value="<?= e($p['sort_order']) ?>"></div>
        </div>
        <input type="hidden" name="period" value="<?= e($p['period']) ?>">
        <div class="row" style="margin-top:10px">
          <label style="margin:0"><input type="checkbox" name="highlight" <?= $p['highlight'] ? 'checked' : '' ?>> Mais vendido</label>
          <div>
            <button class="btn btn-neon btn-sm" name="action" value="plan_save">Salvar</button>
            <?php if ($p['id']): ?><button class="btn btn-sec btn-sm" name="action" value="plan_delete" onclick="return confirm('Remover plano?')">Remover</button><?php endif; ?>
          </div>
        </div>
      </form>
      <?php endforeach; ?>
    </div>
  </div>
</div></body></html>
