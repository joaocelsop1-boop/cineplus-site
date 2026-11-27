-- CinePlus - importe este arquivo no phpMyAdmin
SET NAMES utf8mb4;
CREATE TABLE IF NOT EXISTS site_settings (
  id INT PRIMARY KEY DEFAULT 1,
  brand_name VARCHAR(100) NOT NULL DEFAULT 'CinePlus',
  whatsapp VARCHAR(20) NOT NULL DEFAULT '5511999999999',
  app_url VARCHAR(255) NOT NULL DEFAULT '',
  headline VARCHAR(255) NOT NULL DEFAULT 'Tudo que você ama assistir. Num só lugar.',
  subheadline VARCHAR(255) NOT NULL DEFAULT 'Canais ao vivo, futebol, filmes, séries e desenhos em qualquer tela.',
  updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
INSERT IGNORE INTO site_settings (id) VALUES (1);

CREATE TABLE IF NOT EXISTS plans (
  id INT AUTO_INCREMENT PRIMARY KEY,
  name VARCHAR(100) NOT NULL,
  screens INT NOT NULL DEFAULT 1,
  price DECIMAL(10,2) NOT NULL,
  period VARCHAR(20) NOT NULL DEFAULT 'mês',
  highlight TINYINT(1) NOT NULL DEFAULT 0,
  sort_order INT NOT NULL DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
-- Planos iguais aos do app CinePlus (NOFLIX), para o cliente não ver preço
-- diferente no site e no app. Se mudar o preço no app, mude aqui também
-- (ou no painel do site, em Planos e preços).
INSERT INTO plans (name, screens, price, period, highlight, sort_order) VALUES
('Quinzenal', 1, 15.00, '15 dias', 0, 1),
('Mensal', 1, 25.00, '30 dias', 1, 2),
('Anual', 1, 70.00, '12 meses', 0, 3);

CREATE TABLE IF NOT EXISTS leads (
  id INT AUTO_INCREMENT PRIMARY KEY,
  name VARCHAR(100) NOT NULL,
  whatsapp VARCHAR(13) NOT NULL,
  device VARCHAR(50) NULL,
  people VARCHAR(50) NULL,
  interests VARCHAR(200) NULL,
  plan_name VARCHAR(100) NULL,
  ip VARCHAR(45) NULL,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  INDEX (created_at)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS admin_users (
  id INT AUTO_INCREMENT PRIMARY KEY,
  email VARCHAR(150) NOT NULL UNIQUE,
  password_hash VARCHAR(255) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
-- O primeiro admin é criado na tela admin/login.php (primeiro acesso).

-- =====================================================================
-- Banco JÁ existente (o site já estava no ar no cPanel): o CREATE TABLE
-- acima NÃO adiciona a coluna nova numa tabela que já existe. Nesse caso,
-- rode SÓ este ALTER na aba SQL do phpMyAdmin (ele não apaga nada e não
-- duplica os planos). Numa instalação NOVA (Docker/Coolify) ele NÃO é
-- necessário, porque o CREATE TABLE acima já cria a coluna app_url — por
-- isso ele está comentado aqui: o MySQL 8.0 (Docker) NÃO aceita a sintaxe
-- "ADD COLUMN IF NOT EXISTS" e a importação automática falharia.
-- ALTER TABLE `site_settings` ADD COLUMN `app_url` VARCHAR(255) NOT NULL DEFAULT '';
-- (Se der erro "Duplicate column", é porque a coluna já existe — pode ignorar.)
