<?php
require __DIR__ . '/../config.php';
require __DIR__ . '/_layout.php';
$hasAdmin = (int)db()->query('SELECT COUNT(*) FROM admin_users')->fetchColumn() > 0;
$error = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    csrf_check();
    $email = strtolower(trim($_POST['email'] ?? ''));
    $pass = $_POST['password'] ?? '';
    if (!$hasAdmin) {
        // Primeiro acesso: cria a conta de administrador
        if (!filter_var($email, FILTER_VALIDATE_EMAIL) || strlen($pass) < 8) {
            $error = 'Use um e-mail válido e senha com pelo menos 8 caracteres.';
        } else {
            db()->prepare('INSERT INTO admin_users (email, password_hash) VALUES (?, ?)')->execute([$email, password_hash($pass, PASSWORD_DEFAULT)]);
            session_regenerate_id(true);
            $_SESSION['admin_id'] = (int)db()->lastInsertId();
            header('Location: index.php'); exit;
        }
    } else {
        $st = db()->prepare('SELECT * FROM admin_users WHERE email = ?');
        $st->execute([$email]);
        $u = $st->fetch();
        if ($u && password_verify($pass, $u['password_hash'])) {
            session_regenerate_id(true);
            $_SESSION['admin_id'] = (int)$u['id'];
            header('Location: index.php'); exit;
        }
        sleep(1);
        $error = 'E-mail ou senha incorretos.';
    }
}
admin_head('Entrar no painel');
?>
<div class="wrap" style="max-width:420px;padding-top:12vh">
  <form method="post" class="card fld">
    <h1 style="font-size:40px"><?= $hasAdmin ? 'Entrar no painel' : 'Criar conta de administrador' ?></h1>
    <?php if (!$hasAdmin): ?><p class="muted" style="font-size:14px">Primeiro acesso: esta será a única conta com acesso ao painel.</p><?php endif; ?>
    <?php if ($error): ?><p class="err"><?= e($error) ?></p><?php endif; ?>
    <input type="hidden" name="csrf" value="<?= csrf_token() ?>">
    <label>E-mail</label><input class="input" type="email" name="email" required>
    <label>Senha</label><input class="input" type="password" name="password" required minlength="<?= $hasAdmin ? 1 : 8 ?>">
    <button class="btn btn-neon" style="width:100%;margin-top:20px"><?= $hasAdmin ? 'Entrar' : 'Criar e entrar' ?></button>
  </form>
</div></body></html>
