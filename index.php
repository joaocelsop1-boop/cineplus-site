<?php
require __DIR__ . '/config.php';
$s = settings();
$plans = plans();
$minPrice = $plans ? min(array_map(fn($p) => (float)$p['price'], $plans)) : 0;
$brand = $s['brand_name'] ?? 'CinePlus';
$appUrl = app_url();
$minPlan = null;
foreach ($plans as $p) { if ($minPlan === null || (float)$p['price'] < (float)$minPlan['price']) $minPlan = $p; }
$waGeneric = wa_link($s['whatsapp'] ?? '', "Oi! Vim pelo site da $brand e quero saber mais.");
$cats = ['Lançamentos','Séries','Filmes clássicos','Desenhos','Documentários','Novelas'];
$plansJs = array_map(fn($p) => ['name' => $p['name'], 'screens' => (int)$p['screens'], 'price' => (float)$p['price'], 'period' => $p['period'], 'highlight' => (int)$p['highlight']], $plans);
?>
<!doctype html>
<html lang="pt-BR">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<title><?= e($brand) ?> — Filmes e séries. Assine agora</title>
<meta name="description" content="Filmes e séries em qualquer tela. Assine e comece a assistir.">
<meta property="og:title" content="<?= e($brand) ?> — Assine agora">
<meta property="og:description" content="Descubra seu plano ideal em 30 segundos e assine agora.">
<meta property="og:type" content="website">
<link rel="preconnect" href="https://fonts.googleapis.com">
<link href="https://fonts.googleapis.com/css2?family=Bebas+Neue&family=Barlow:wght@400;600;700&display=swap" rel="stylesheet">
<link rel="stylesheet" href="assets/style.css">
</head>
<body>
<div class="urgency"><span class="pulse">🔥</span> Assine hoje · Pague via Pix e comece a assistir</div>
<header class="top"><div class="wrap">
  <span class="brand display neon"><?= e($brand) ?></span>
  <a href="#quiz" class="btn btn-neon btn-sm cta-pulse">Assinar agora</a>
</div></header>

<section class="hero">
  <img src="assets/hero-streaming.jpg" alt="Filmes e séries em alta qualidade">
  <div class="fade"></div>
  <div class="wrap">
    <h1><?= e($s['headline'] ?? '') ?></h1>
    <p class="sub muted">Filmes e séries em qualquer tela.</p>
    <div class="giant-box"><p class="lbl">A partir de</p><p class="giant neon"><span class="cur">R$</span><?= money($minPrice) ?><span class="per">/<?= e($minPlan['period'] ?? 'mês') ?></span></p></div>
    <div class="cta">
      <a href="#quiz" class="btn btn-neon btn-xl cta-pulse">⚡ Quero assinar →</a>
      <p class="muted" style="font-size:14px"><b class="okc">✓</b> Pagamento via Pix · <b class="okc">✓</b> Sem fidelidade</p>
    </div>
  </div>
</section>

<section class="pad">
  <div class="wrap">
    <p class="eyebrow">Catálogo completo</p>
    <h2 class="title left">Tudo que você ama <span class="neon">num só lugar</span></h2>
    <p class="muted" style="max-width:560px;margin-top:10px">+20 mil filmes e séries para assistir quando quiser.</p>
  </div>
  <div class="posters"><div class="ptrack">
  <?php $posters=[['poster-acao.jpg','Lançamentos de ação','4K',false],['poster-serie.jpg','Séries do momento','NOVO',false],['poster-infantil.jpg','Desenhos e animações','KIDS',false]];
  foreach (array_merge($posters,$posters) as $p): ?>
    <div class="poster"><img src="assets/<?= $p[0] ?>" alt="<?= e($p[1]) ?>" loading="lazy"><div class="pf"></div>
      <span class="ptag <?= $p[3]?'live-tag':'' ?>"><?= $p[2] ?></span><p class="pt display"><?= e($p[1]) ?></p></div>
  <?php endforeach; ?>
  </div></div>
  <div class="wrap chips">
    <?php foreach (['Ação','Comédia','Drama','Terror','Romance','Ficção científica','Animação','Suspense','Aventura'] as $c): ?><span><?= $c ?></span><?php endforeach; ?>
  </div>
</section>

<?php $services=['Netflix Premium'=>59.9,'Disney+'=>43.9,'Max'=>34.9,'Prime Video'=>19.9,];
$total=array_sum($services); $save=max(0,($total-$minPrice)*12); ?>
<section class="pad band">
  <div class="wrap">
    <h2 class="title">Faça as contas e <span class="neon">pare de pagar caro</span></h2>
    <div class="grid g2" style="margin-top:48px">
      <div class="card dark">
        <p class="label muted">Assinando tudo separado</p>
        <ul class="svc"><?php foreach ($services as $n=>$v): ?><li><span><b class="x">✕</b> <?= $n ?></span><span class="muted">R$ <?= money($v) ?></span></li><?php endforeach; ?></ul>
        <div class="tot"><b>Total por mês</b><span class="display strike-red">R$ <?= money($total) ?></span></div>
      </div>
      <div class="card dark glow center-col">
        <span class="badge mid">Tudo isso incluso</span>
        <p class="label" style="color:var(--cyan)"><?= e($brand) ?></p>
        <p class="giant neon" style="font-size:130px"><span class="cur">R$</span><?= money($minPrice) ?></p>
        <p class="muted">por mês · todos os conteúdos</p>
        <div class="save"><small>Você economiza</small><p class="display">R$ <?= money($save) ?> por ano</p></div>
      </div>
    </div>
  </div>
</section>

<section class="pad"><div class="wrap">
  <h2 class="title">Comece em <span class="neon">2 minutos</span></h2>
  <div class="grid g3 steps" style="margin-top:48px">
    <?php foreach ([['📝','Responda o quiz','4 perguntas rápidas e descobrimos seu plano ideal.'],['💬','Pague via Pix','Crie sua conta no app e pague com Pix.'],['▶️','Dê o play','Acesso liberado após a confirmação do pagamento.']] as $i=>$st): ?>
    <div class="card center"><div class="sic"><?= $st[0] ?><span><?= $i+1 ?></span></div><h3 style="font-size:32px;margin-top:18px"><?= $st[1] ?></h3><p class="muted"><?= $st[2] ?></p></div>
    <?php endforeach; ?>
  </div>
</div></section>

<!-- QUIZ -->
<section id="quiz" class="pad" style="padding-left:20px;padding-right:20px">
  <div class="quiz card">
    <div id="qhead">
      <div class="bars"><div></div><div></div><div></div><div></div></div>
      <p class="label" style="color:var(--cyan)">Passo <span id="qn">1</span> de 4</p>
      <h2 class="step-title" id="qtitle"></h2>
    </div>
    <div data-step="0" class="opts">
      <button class="opt" data-dev="Smart TV"><i>📺</i>Smart TV</button>
      <button class="opt" data-dev="Celular"><i>📱</i>Celular</button>
      <button class="opt" data-dev="Computador"><i>💻</i>Computador</button>
      <button class="opt" data-dev="Tablet"><i>📲</i>Tablet</button>
    </div>
    <div data-step="1" class="opts three hidden">
      <button class="opt" data-people="Só eu" data-n="1"><i>🙋</i>Só eu</button>
      <button class="opt" data-people="Eu e mais 1" data-n="2"><i>💑</i>Eu e mais 1</button>
      <button class="opt" data-people="Família (3+)" data-n="3"><i>👨‍👩‍👧</i>Família (3+)</button>
    </div>
    <div data-step="2" class="hidden">
      <p class="muted" style="font-size:14px">Pode escolher mais de um.</p>
      <div class="opts">
          <button class="opt" data-int="Filmes"><i>🎬</i>Filmes</button>
        <button class="opt" data-int="Séries"><i>🍿</i>Séries</button>
        <button class="opt" data-int="Infantil"><i>🧸</i>Infantil</button>
      </div>
      <button class="btn btn-neon btn-xl" style="width:100%;margin-top:24px" id="toPlan">Ver meu plano →</button>
    </div>
    <form data-step="3" class="hidden" id="leadForm" style="margin-top:24px">
      <div class="card glow-m" style="background:var(--bg);padding:20px">
        <p class="label" style="color:var(--magenta)">Recomendado pra você</p>
        <p class="display" style="font-size:38px" id="recName"></p>
        <p style="font-size:18px" id="recPrice"></p>
      </div>
      <input class="input" id="fName" placeholder="Seu nome" maxlength="100" required>
      <input class="input" id="fPhone" placeholder="WhatsApp com DDD" inputmode="tel" maxlength="20" required>
      <p class="err hidden" id="fErr"></p>
      <button type="submit" class="btn btn-neon btn-xl" style="width:100%;margin-top:16px" id="fBtn">Assinar agora</button>
    </form>
    <div data-step="4" class="hidden" style="text-align:center;padding:24px 0">
      <div style="font-size:48px">✅</div>
      <h2 style="font-size:48px;margin-top:12px">Pronto, <span id="doneName"></span>!</h2>
      <p class="muted" style="margin-top:8px">Seu contato foi salvo. Você será levado para criar sua conta no app e pagar via Pix.</p>
      <p class="muted" style="margin-top:4px" id="doneCount"></p>
      <a class="btn btn-neon btn-xl" style="margin-top:24px" id="doneApp" href="#">Criar minha conta e pagar →</a>
      <p class="muted" style="margin-top:16px;font-size:14px">Prefere falar com a gente primeiro?</p>
      <a class="btn btn-sec btn-xl" style="margin-top:8px" id="doneWa" target="_blank" rel="noreferrer">💬 Abrir WhatsApp</a>
    </div>
    <button class="back hidden" id="back">← Voltar</button>
  </div>
</section>

<section class="pad" id="planos"><div class="wrap">
  <h2 class="title">Planos</h2>
  <div class="grid g3" style="margin-top:40px">
    <?php foreach ($plans as $p): ?>
    <div class="card plan <?= $p['highlight'] ? 'glow-m lift' : '' ?>">
      <?php if ($p['highlight']): ?><span class="badge">Mais vendido</span><?php endif; ?>
      <h3 style="font-size:32px"><?= e($p['name']) ?></h3>
      <p class="muted" style="font-size:14px"><?= (int)$p['screens'] ?> <?= $p['screens'] > 1 ? 'telas simultâneas' : 'tela' ?></p>
      <span class="eco">Economize R$ <?= money(max(0,($total-(float)$p['price'])*12)) ?>/ano</span>
      <p class="price <?= $p['highlight']?'neon':'' ?>" style="font-size:72px;margin-top:12px">R$ <?= money($p['price']) ?><span>/<?= e($p['period']) ?></span></p>
      <ul><li>Filmes e séries</li><li>Suporte no WhatsApp</li></ul>
      <a href="<?= e($appUrl . '/login?plano=' . rawurlencode($p['name'])) ?>" class="btn <?= $p['highlight'] ? 'btn-neon' : 'btn-sec' ?>" style="width:100%">Assinar agora</a>
    </div>
    <?php endforeach; ?>
  </div>
</div></section>

<section class="pad band"><div class="wrap">
  <div class="center"><div class="stars">★★★★★</div><p><b>4.9/5 · Mais de 8.500 clientes ativos</b></p>
  <h2 class="title" style="margin-top:12px">Quem assinou, <span class="neon">ficou</span></h2></div>
  <div class="grid g3" style="margin-top:48px">
  <?php foreach ([['Carlos M.','São Paulo','Instalei na minha Smart TV LG e o filme de ontem rodou liso, sem travar nada 🔥','21:47'],['Juliana R.','Belo Horizonte','Cancelei Netflix e Disney, aqui tem tudo e meus filhos amam os desenhos 😍','14:12'],['Rafael S.','Recife','Atendimento muito rápido, em 5 minutos já tava assistindo. Recomendo demais!','09:30']] as $r): ?>
    <div class="wa-card"><div class="wa-h"><div class="av"><?= mb_substr($r[0],0,1) ?></div><div><b><?= $r[0] ?></b><small class="muted"><?= $r[1] ?> · online</small></div></div>
    <div class="wa-b"><div class="bubble"><?= $r[2] ?><small><?= $r[3] ?> <span style="color:var(--cyan)">✓✓</span></small></div></div></div>
  <?php endforeach; ?>
  </div>
</div></section>

<section class="pad"><div class="wrap">
  <h2 class="title">Funciona em <span class="neon">todas as telas</span></h2>
  <div class="dev-grid">
  <?php foreach ([['📺','Samsung','Smart TV Tizen'],['📺','LG','Smart TV webOS'],['🔥','Fire Stick','Amazon 4K'],['📦','Android TV','TV Box / Mi Box'],['📡','Chromecast','Google TV'],['📱','Celular','Android e iPhone'],['📲','Tablet','Android e iPad'],['💻','Computador','Windows e Mac']] as $d): ?>
    <div class="card center dev"><i><?= $d[0] ?></i><h3 style="font-size:26px"><?= $d[1] ?></h3><p class="muted" style="font-size:13px"><?= $d[2] ?></p></div>
  <?php endforeach; ?>
  </div>
</div></section>

<section class="pad"><div class="wrap" style="max-width:760px">
  <h2 class="title">Perguntas frequentes</h2>
  <div style="margin-top:32px">
    <details><summary>Como recebo o acesso?</summary><p>Crie sua conta no app e pague via Pix. Assim que o pagamento for confirmado, seu acesso é liberado.</p></details>
    <details><summary>Preciso de internet rápida?</summary><p>Recomendamos a partir de 10 Mega para assistir em HD.</p></details>
    <details><summary>Como pago?</summary><p>Pix, direto no app, na hora de assinar.</p></details>
  </div>
</div></section>

<div class="sticky-m"><a href="#planos" class="btn btn-neon cta-pulse" style="width:100%">Assinar · R$ <?= money($minPrice) ?></a></div>
<footer>© <?= date('Y') ?> <?= e($brand) ?></footer>

<a href="<?= e($waGeneric) ?>" target="_blank" rel="noreferrer" class="wa-float" aria-label="Falar no WhatsApp">
  <svg width="30" height="30" viewBox="0 0 24 24" fill="#fff"><path d="M12 2a10 10 0 0 0-8.6 15.1L2 22l5-1.3A10 10 0 1 0 12 2zm5.3 14.1c-.2.6-1.3 1.2-1.8 1.2-.5.1-1 .2-3.3-.7-2.8-1.1-4.5-3.9-4.7-4.1-.1-.2-1.1-1.5-1.1-2.9s.7-2 1-2.3c.2-.3.5-.3.7-.3h.5c.2 0 .4 0 .6.5l.9 2.1c.1.2.1.3 0 .5l-.3.5-.4.4c-.1.1-.3.3-.1.6.2.3.7 1.2 1.6 2 1.1.9 2 1.2 2.3 1.4.3.1.4.1.6-.1l.9-1c.2-.3.4-.2.6-.1l2 .9c.3.1.5.2.5.3.1.1.1.6-.1 1.1z"/></svg>
</a>

<script>
const BRAND = <?= json_encode($brand) ?>;
const APP_URL = <?= json_encode($appUrl) ?>;
const WA = <?= json_encode(only_digits($s['whatsapp'] ?? '')) ?>;
const PLANS = <?= json_encode($plansJs) ?>;
const TITLES = ['Onde você vai assistir?','Quantas pessoas vão usar?','O que você mais curte?','Seu plano está pronto!'];
const st = {step:0, device:'', people:'', n:1, interests:[], name:''};
const $ = s => document.querySelector(s), $$ = s => document.querySelectorAll(s);
const fmt = n => n.toLocaleString('pt-BR',{minimumFractionDigits:2,maximumFractionDigits:2});
const waLink = m => 'https://wa.me/' + WA + '?text=' + encodeURIComponent(m);
function plan(){ const s=[...PLANS].sort((a,b)=>a.price-b.price); return s.find(p=>p.highlight) || s[Math.floor(s.length/2)] || s[0]; }
function appLink(p){ return APP_URL + '/login?plano=' + encodeURIComponent(p ? p.name : ''); }
function go(n){
  st.step=n;
  $$('[data-step]').forEach(el=>el.classList.toggle('hidden', +el.dataset.step!==n));
  $('#qhead').classList.toggle('hidden', n===4);
  $('#back').classList.toggle('hidden', n===0||n===4);
  if(n<4){ $('#qn').textContent=n+1; $('#qtitle').textContent=TITLES[n]; $$('.bars div').forEach((b,i)=>b.classList.toggle('on',i<=n)); }
  if(n===3){ const p=plan(); if(p){ $('#recName').textContent=p.name+' · '+p.screens+(p.screens>1?' telas':' tela'); $('#recPrice').innerHTML='R$ '+fmt(p.price)+'<span class="muted">/'+p.period+'</span>'; } }
}
$$('[data-dev]').forEach(b=>b.onclick=()=>{ $$('[data-dev]').forEach(x=>x.classList.remove('on')); b.classList.add('on'); st.device=b.dataset.dev; go(1); });
$$('[data-people]').forEach(b=>b.onclick=()=>{ $$('[data-people]').forEach(x=>x.classList.remove('on')); b.classList.add('on'); st.people=b.dataset.people; st.n=+b.dataset.n; go(2); });
$$('[data-int]').forEach(b=>b.onclick=()=>{ const v=b.dataset.int; const i=st.interests.indexOf(v); i>=0?st.interests.splice(i,1):st.interests.push(v); b.classList.toggle('on'); });
$('#toPlan').onclick=()=>go(3);
$('#back').onclick=()=>go(st.step-1);
$('#leadForm').onsubmit=async e=>{
  e.preventDefault();
  const name=$('#fName').value.trim(), digits=$('#fPhone').value.replace(/\D/g,''), err=$('#fErr');
  err.classList.add('hidden');
  const fail=m=>{err.textContent=m;err.classList.remove('hidden');};
  if(name.length<2) return fail('Digite seu nome');
  if(digits.length<10||digits.length>13) return fail('Digite seu WhatsApp com DDD');
  const p=plan(), btn=$('#fBtn'); btn.disabled=true; btn.textContent='Enviando...';
  try{
    const r=await fetch('api/lead.php',{method:'POST',headers:{'Content-Type':'application/json'},body:JSON.stringify({name,whatsapp:digits,device:st.device,people:st.people,interests:st.interests.join(', '),plan_name:p?p.name:null})});
    const j=await r.json();
    if(!j.ok) throw new Error(j.error||'');
  }catch(x){ btn.disabled=false; btn.textContent='Assinar agora'; return fail(x.message||'Não foi possível enviar. Tente de novo.'); }
  const msg=`Oi! Sou ${name} e quero assinar o plano ${p?p.name:'-'} da ${BRAND}.\nAparelho: ${st.device}\nGosto de: ${st.interests.join(', ')||'-'}`;
  const link=appLink(p);
  $('#doneName').textContent=name.split(' ')[0];
  $('#doneApp').href=link;
  $('#doneWa').href=waLink(msg);
  go(4);
  let n=3;
  const tick=()=>{ const el=$('#doneCount'); if(!el) return; if(n<=0){ window.location.href=link; return; } el.textContent='Redirecionando para o app em '+n+'s...'; n--; setTimeout(tick,1000); };
  tick();
};
go(0);
</script>
</body>
</html>
