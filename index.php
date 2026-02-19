<?php
$__bd_config = require __DIR__ . '/config/sources.php';
$__bd_secret = $__bd_config['frontend_secret'] ?? '';
?>
<!doctype html>
<html lang="pt-BR">

<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>Bíblia Diária — Plano 365</title>
  <meta name="description" content="Plano de leitura bíblica em 365 dias. Projeto devocional, gratuito e sem anúncios.">
  <meta property="og:title" content="Bíblia Diária — Plano 365">
  <meta property="og:description" content="Leitura do plano bíblico em uma só página, sem login e sem anúncios.">
  <meta property="og:type" content="website">
  <meta property="og:url" content="https://bibliadiaria.myrotech.com">
  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
  <link href="https://fonts.googleapis.com/css2?family=DM+Sans:wght@400;500;600;700&family=Playfair+Display:ital,wght@0,400;0,700;1,400&display=swap" rel="stylesheet">
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" integrity="sha384-QWTKZyjpPEjISv5WaRU9OFeRpok6YctnYmDr5pNlyT2bRjXh0JMhjY6hW+ALEwIH" crossorigin="anonymous">
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css">
  <link rel="stylesheet" href="https://unpkg.com/aos@2.3.4/dist/aos.css">
  <link rel="stylesheet" href="css/style.css">

  <link rel="icon" type="image/png" href="images/favicon/favicon-96x96.png" sizes="96x96" />
  <link rel="icon" type="image/svg+xml" href="images/favicon/favicon.svg" />
  <link rel="shortcut icon" href="images/favicon/favicon.ico" />
  <link rel="apple-touch-icon" sizes="180x180" href="images/favicon/apple-touch-icon.png" />
  <meta name="apple-mobile-web-app-title" content="Bíblia Diária" />
  <link rel="manifest" href="images/favicon/site.webmanifest" />
</head>

<body>
  <header class="app-header">
    <div class="container-reading">
      <a href="/" class="site-logo-link" data-aos="fade-down">
        <img src="images/logo.png" alt="Bíblia Diária" class="site-logo">
      </a>
      <div class="date-picker-wrapper" data-aos="fade-up" data-aos-delay="100">
        <button class="btn btn-sm btn-outline-secondary date-nav me-2" id="prev-day" aria-label="Dia anterior">&lt;</button>
        <label for="date-picker" class="date-display" id="date-label">
          <span id="today-text">Carregando data...</span>
          <i class="bi bi-chevron-down ms-1 date-arrow"></i>
        </label>
        <button class="btn btn-sm btn-outline-secondary date-nav ms-2" id="next-day" aria-label="Próximo dia">&gt;</button>
        <input type="date" id="date-picker" class="hidden-picker" aria-label="Selecionar data">
      </div>
    </div>
  </header>

  <main class="container-reading py-4">
    <div id="error-box" class="alert alert-warning d-none text-center" role="alert"></div>

    <div id="content-area">
      <div id="plan-view" class="fade-in">
        <div class="text-center mb-5">
          <span class="reading-label">Leitura de Hoje</span>
          <h2 class="h4 text-muted fw-normal" id="plan-progress">Dia 1 de 365</h2>
          <p class="plan-description text-muted small mt-2">
            Este plano cobre toda a Bíblia em um ano. <br>Exibimos as referências para reflexão.
          </p>
        </div>
        <div class="d-flex justify-content-end mb-3 font-controls">
          <button id="font-decrease" class="btn font-btn small" aria-label="Diminuir fonte">A-</button>
          <button id="font-reset" class="btn font-btn small" aria-label="Fonte original">A</button>
          <button id="font-increase" class="btn font-btn small" aria-label="Aumentar fonte">A+</button>
        </div>
        <div id="loader" class="text-center my-3 d-none">
          <div class="spinner-border text-secondary" role="status"><span class="visually-hidden">Carregando...</span></div>
        </div>
        <div id="plan-cards"></div>
      </div>
    </div>
  </main>

  <section class="container-reading py-4" aria-label="Links úteis">
    <div class="row g-3 links-row">
      <div class="col-12 col-md-6">
        <div class="reading-card link-card h-100">
          <div class="card-body">
            <h3 class="h6 text-uppercase text-muted mb-3">Liturgia Diária</h3>
            <div class="row">
              <div class="col-6">
                <a class="btn link-btn w-100" href="https://liturgia.cancaonova.com/pb/" target="_blank" aria-label="Ver Liturgia na Canção Nova">
                  <img class="link-logo" src="images/cancaonova.png" alt="Canção Nova logo">
                  <span class="link-text">Canção Nova</span>
                </a>
              </div>
              <div class="col-6">
                <a class="btn link-btn w-100" href="https://www.a12.com/reze-no-santuario/deus-conosco" target="_blank" aria-label="Ver Liturgia no A12">
                  <img class="link-logo" src="images/a12.png" alt="A12 logo">
                  <span class="link-text">A12</span>
                </a>
              </div>
            </div>
          </div>
        </div>
      </div>

      <div class="col-12 col-md-6">
        <div class="reading-card link-card h-100">
          <div class="card-body">
            <h3 class="h6 text-uppercase text-muted mb-3">Santo do Dia</h3>
            <div class="row">
              <div class="col-6">
                <a class="btn link-btn w-100" href="https://santo.cancaonova.com/" target="_blank" aria-label="Ver Santo do Dia na Canção Nova">
                  <img class="link-logo" src="images/cancaonova.png" alt="Canção Nova logo">
                  <span class="link-text">Canção Nova</span>
                </a>
              </div>
              <div class="col-6">
                <a class="btn link-btn w-100" href="https://www.a12.com/reze-no-santuario/santo-do-dia" target="_blank" aria-label="Ver Santo do Dia no A12">
                  <img class="link-logo" src="images/a12.png" alt="A12 logo">
                  <span class="link-text">A12</span>
                </a>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>
  </section>

  <footer class="app-footer">
    <div class="container-reading">
      <h3 class="h6 text-uppercase fw-bold mb-3" style="letter-spacing: 2px;">Bíblia Diária</h3>
      <p class="mb-4 text-muted" style="max-width: 400px; margin: 0 auto 2rem;">Projeto devocional sem fins lucrativos. Leve a Palavra onde quer que vá.</p>
      <div class="source-credits d-flex justify-content-center align-items-center" style="gap:0.75rem;">
        <a href="https://github.com/rodrigobahia/bibliadiaria" target="_blank" class="text-muted d-inline-flex align-items-center" title="Código-fonte no GitHub" aria-label="GitHub">
          <i class="bi bi-github" style="font-size:1.25rem;"></i>
        </a>
        <a href="https://myrotech.com" target="_blank" title="Desenvolvido por Myrotech" class="d-inline-flex align-items-center">
          <img src="https://myrotech.com/myrotech.png" alt="Myrotech" style="height:28px; opacity:0.95; margin-left:0.25rem;">
        </a>
      </div>
      <div class="footer-note text-muted small mt-2">
        Observação: este plano segue a ordem canônica das Escrituras — do Antigo Testamento (Gênesis) ao Novo Testamento (Apocalipse), capítulo a capítulo.
      </div>
      <div class="footer-credits text-muted small mt-2">
        Texto e tradução do conteúdo bíblico fornecidos por
        <a href="https://bible-api.com" target="_blank" rel="noopener" class="text-muted">bible-api.com</a>.
        Todos os direitos sobre o texto pertencem aos respectivos detentores e ao serviço citado.
      </div>
    </div>
  </footer>

  <script>
    window.BD_SECRET = '<?php echo htmlspecialchars($__bd_secret, ENT_QUOTES); ?>';
  </script>
  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
  <script src="https://unpkg.com/aos@2.3.4/dist/aos.js"></script>
  <script src="js/app.js" defer></script>
</body>

</html>