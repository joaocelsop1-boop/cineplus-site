# CinePlus — Deploy do site no Oracle (Coolify)

O site (página de vendas) vai rodar no Oracle, no Coolify, com Docker — o
mesmo servidor onde já roda o app CinePlus (NOFLIX). O pacote antigo
(`cineplus-site.zip`) era para cPanel; **este é o pacote para o Oracle**.
Não suba este pacote no cPanel.

## ATENÇÃO — os domínios (leia antes de começar)

Um domínio só aponta para UM app no Coolify. Por isso a divisão é — tudo
no MESMO domínio `cineplus.shop`, por caminho:

| Endereço | O que é |
|---|---|
| `https://cineplus.shop` | **O SITE** (esta página de vendas) |
| `https://cineplus.shop/app` | **O APP** CinePlus (NOFLIX — onde o cliente cria a conta e assiste) |

O botão "Assinar agora" do site leva para o APP (`https://cineplus.shop/app`).
Ou seja, a URL do app **não pode ser só `cineplus.shop`** (senão o botão volta
para o próprio site) — tem que ser `https://cineplus.shop/app`.

E os bancos também são separados: o banco do SITE (tabelas `site_settings`,
`plans`, `leads`, `admin_users`) **não é** o banco do APP (NOFLIX). Não
misture as senhas nem os bancos dos dois.

---

## Passo 0 — o que você precisa ter em mãos

- Conta no GitHub (a mesma que você já usa para o NOFLIX).
- Acesso ao Coolify (`http://129.146.127.7:8000`).
- Duas senhas NOVAS que você vai inventar agora (só para o Coolify):
  - senha do usuário do banco do site (ex.: `DB_PASS`);
  - senha do root do MySQL (ex.: `DB_ROOT_PASSWORD`).
  - Anote as duas num lugar seguro. Elas NÃO são a senha do cPanel nem a
    senha do app NOFLIX.

## Passo 1 — subir o código para o GitHub

1. No GitHub, crie um repositório novo (ex.: `cineplus-site`).
2. Suba o CONTEÚDO deste zip (os arquivos, não o zip): `index.php`,
   `config.php`, `database.sql`, `Dockerfile`, `docker-compose.yml`,
   `.env.example`, `DEPLOY-ORACLE.md`, `admin/`, `api/`, `assets/`,
   `.htaccess`, `INSTRUCOES.txt`.
3. **Repositório PRIVADO (recomendado).** O `config.php` tem a senha antiga
   do banco do cPanel como fallback (para o código continuar rodando no
   cPanel). Num repositório público essa senha ficaria visível para qualquer
   um. Se fizer público, troque a senha do banco no cPanel depois — ou
   apague o fallback do `config.php` e use só variável de ambiente.
4. Se o repositório for privado, o Coolify precisa de acesso a ele: no
   Coolify, ao conectar o repositório, ele mostra uma chave de deploy
   (deploy key) — copie e cole no GitHub em Settings → Deploy keys →
   Add deploy key (marque "Allow write access" como NÃO marcado, só
   leitura basta).

**Verificar:** no GitHub, o repositório mostra os arquivos listados acima
e o `docker-compose.yml` na raiz.

## Passo 2 — criar o recurso no Coolify

1. No Coolify, abra o projeto (ou crie um novo, ex.: `CinePlus`).
2. Clique em **New Resource** → **Docker Compose**.
3. Escolha o repositório `cineplus-site` (e a branch, ex.: `main`).
4. Em "Compose file", deixe `docker-compose.yml` (está na raiz do repo).
5. NÃO preencha nada em "Ports" — o Coolify/Traefik roteia o domínio para
   a porta 80 do container sozinho. Não publique porta de host (nada de
   `8080:80`): porta de host já alocada quebra o deploy neste servidor.

**Verificar:** o recurso aparece com os dois serviços, `app` e `db`.

## Passo 3 — variáveis de ambiente

Na aba **Environment Variables** do recurso, preencha (uma por linha,
formato `NOME=valor`):

| Variável | Valor |
|---|---|
| `DB_NAME` | `cineplus_site` |
| `DB_USER` | `cineplus` |
| `DB_PASS` | a senha nova que você inventou no passo 0 |
| `DB_ROOT_PASSWORD` | a outra senha nova do passo 0 |
| `APP_URL` | `https://cineplus.shop/app` (o app fica no mesmo domínio, no caminho /app) |

`DB_HOST` NÃO precisa preencher: já está fixo como `db` no
`docker-compose.yml` (é o nome do serviço do banco dentro da rede Docker).

**Verificar:** as 5 variáveis aparecem na lista, sem erro de digitação.

## Passo 4 — Deploy

1. Clique em **Deploy**.
2. Acompanhe o log. O que tem que aparecer:
   - o `db` sobe e o healthcheck fica verde (`healthy`);
   - o `app` faz o build da imagem PHP e sobe;
   - no log do `db`, na PRIMEIRA subida, aparece algo como
     `.../docker-entrypoint-initdb.d/01-database.sql` sendo executado
     (é a criação das tabelas).
3. Se o log mostrar erro de build, leia a última linha do erro antes de
   tentar de novo — na maioria das vezes é variável de ambiente faltando
   (o compose reclama `Defina DB_PASS...` ou `Defina DB_ROOT_PASSWORD...`).

**Verificar:** os dois containers aparecem como `Running` (o `db` com
`healthy`).

## Passo 5 — conferir o banco (e importar à mão se estiver vazio)

Na primeira subida o MySQL já importa o `database.sql` sozinho. Confira:

1. No Coolify, abra o terminal do container **`db`** (aba Terminal do
   serviço db).
2. Rode:
   ```bash
   mysql -u root -p"$MYSQL_ROOT_PASSWORD" -e "SHOW TABLES;" cineplus_site
   ```
   (digite a senha do root se ele pedir; o comando já passa a variável.)
3. Tem que aparecer as 4 tabelas: `admin_users`, `leads`, `plans`,
   `site_settings`. Se aparecerem, **não faça mais nada** — o banco está
   pronto. Pule para o passo 6.
4. Se NÃO aparecer nenhuma tabela (banco vazio), importe à mão no mesmo
   terminal:
   ```bash
   mysql -u root -p"$MYSQL_ROOT_PASSWORD" cineplus_site < /docker-entrypoint-initdb.d/01-database.sql
   ```
   Depois rode o `SHOW TABLES` de novo para confirmar as 4 tabelas.
5. **NÃO rode a importação duas vezes**: o `INSERT INTO plans` do
   `database.sql` duplicaria os planos (Quinzenal/Mensal/Anual duas vezes).
   Se duplicar, apague os duplicados no painel do site (Planos e preços →
   Remover) ou me chame.

## Passo 6 — domínios

### 6a. O site em `cineplus.shop`
1. No Coolify, no recurso do site, abra a aba **Domains** do serviço
   **`app`** (não do `db`).
2. Adicione o domínio `cineplus.shop` e salve.
3. Clique em **Deploy** de novo.
4. O Coolify emite o certificado HTTPS sozinho (Let's Encrypt).

**Verificar:** abra `https://cineplus.shop` — tem que abrir o site
CinePlus, com os planos Quinzenal R$ 15 / Mensal R$ 25 / Anual R$ 70 e o
botão "Assinar agora".

### 6b. O app em `cineplus.shop/app` (já está assim — não mexa)

O app (NOFLIX) já está no mesmo domínio, no caminho `/app` (deploy do
tudo8/tudo9). **NÃO crie subdomínio `app` e NÃO mude o domínio do app.**
O botão "Assinar agora" do site aponta para `https://cineplus.shop/app`.

**Verificar:** `https://cineplus.shop/app` abre o app CinePlus (tela de
login/criar conta).

## Passo 7 — finalizar no painel do site

1. Abra `https://cineplus.shop/admin/` — no primeiro acesso, crie a conta
   de administrador (e-mail + senha de pelo menos 8 caracteres). Essa é a
   única conta do painel do site.
2. Em **Site e WhatsApp**, confira:
   - Nome da marca: `CinePlus`;
   - WhatsApp: o número de atendimento;
   - **URL do app (CinePlus)**: `https://cineplus.shop/app`.
3. Em **Planos e preços**, confira se os preços são os mesmos do app
   (Quinzenal R$ 15, Mensal R$ 25, Anual R$ 70). Se mudar preço/nome de
   plano no app, mude igual aqui.

## Teste final (do ponto de vista do cliente)

1. Abra `https://cineplus.shop` (aba anônima é melhor).
2. Responda o quiz e clique em **Assinar agora**.
3. Tem que abrir a página de criar conta do APP (em `https://cineplus.shop/app`),
   com o plano escolhido.
4. Crie a conta, pague o Pix e confira se o acesso libera.

## O que NÃO fazer

- NÃO publique porta de host no compose (`ports: - 8080:80`): quebra o
  deploy neste servidor compartilhado.
- NÃO use o banco do app NOFLIX para o site (nem a senha dele): são dois
  bancos e dois MySQL separados.
- NÃO importe o `database.sql` duas vezes (duplica os planos).
- NÃO coloque `cineplus.shop` como `APP_URL`: o botão "Assinar agora"
  voltaria para o próprio site.
- NÃO suba este pacote no cPanel: ele é só para o Coolify (Docker). O
  pacote de cPanel continua sendo o `cineplus-site.zip`.
- NÃO commite um arquivo `.env` preenchido no repositório: as senhas vão
  só nas variáveis de ambiente do Coolify.
