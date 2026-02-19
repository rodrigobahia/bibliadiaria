AOS.init({
  duration: 800,
  once: true,
  offset: 50
});

const datePicker = document.getElementById('date-picker');
const todayText = document.getElementById('today-text');
const errorBox = document.getElementById('error-box');
const loader = document.getElementById('loader');

const todayISO = new Date().toISOString().slice(0, 10);

// Simple localStorage cache helpers
function cacheSet(key, value, ttlSeconds) {
  try {
    const payload = { v: value, e: Date.now() + (ttlSeconds * 1000) };
    localStorage.setItem(key, JSON.stringify(payload));
  } catch (e) { /* ignore quota errors */ }
}
function cacheGet(key) {
  try {
    const raw = localStorage.getItem(key);
    if (!raw) return null;
    const obj = JSON.parse(raw);
    if (!obj.e || Date.now() > obj.e) { localStorage.removeItem(key); return null; }
    return obj.v;
  } catch (e) { return null; }
}

function showLoader(show = true) {
  if (!loader) return;
  loader.classList.toggle('d-none', !show);
}

function init() {
  if (datePicker) datePicker.value = todayISO;
  updateDateDisplay(todayISO);
  fetchDaily(todayISO);

  if (datePicker) {
    datePicker.addEventListener('change', (e) => {
      const val = e.target.value || todayISO;
      updateDateDisplay(val);
      fetchDaily(val);
    });
  }

  // Allow clicking the visible label to open the native date picker.
  const dateLabel = document.querySelector('.date-display');
  const dateHelper = document.querySelector('.date-helper');
  function openDatePicker() {
    if (typeof datePicker.showPicker === 'function') {
      datePicker.showPicker();
    } else {
      datePicker.focus();
      datePicker.click();
    }
  }
  if (dateLabel) dateLabel.addEventListener('click', openDatePicker);
  if (dateHelper) dateHelper.addEventListener('click', openDatePicker);

  const prevBtn = document.getElementById('prev-day');
  const nextBtn = document.getElementById('next-day');
  function changeDay(offset) {
    const current = new Date(datePicker.value || todayISO);
    current.setDate(current.getDate() + offset);
    const newISO = current.toISOString().slice(0, 10);
    datePicker.value = newISO;
    updateDateDisplay(newISO);
    fetchDaily(newISO);
  }
  if (prevBtn) prevBtn.addEventListener('click', () => changeDay(-1));
  if (nextBtn) nextBtn.addEventListener('click', () => changeDay(1));

  // Font controls
  const btnDec = document.getElementById('font-decrease');
  const btnInc = document.getElementById('font-increase');
  const btnReset = document.getElementById('font-reset');
  const STORAGE_KEY = 'bd-font-scale';
  let scale = parseFloat(localStorage.getItem(STORAGE_KEY)) || 1.0;

  function applyScale() {
    document.documentElement.style.setProperty('--bible-font-scale', scale);
    localStorage.setItem(STORAGE_KEY, String(scale));
  }

  function changeScale(delta) {
    scale = Math.min(1.6, Math.max(0.7, +(scale + delta).toFixed(2)));
    applyScale();
  }
  if (btnDec) btnDec.addEventListener('click', () => changeScale(-0.1));
  if (btnInc) btnInc.addEventListener('click', () => changeScale(0.1));
  if (btnReset) btnReset.addEventListener('click', () => { scale = 1.0; applyScale(); });
  applyScale();
}

function updateDateDisplay(dateStr) {
  const date = new Date(dateStr + 'T12:00:00'); // Prevent timezone shift
  const options = { weekday: 'long', year: 'numeric', month: 'long', day: 'numeric' };
  const text = date.toLocaleDateString('pt-BR', options);
  if (todayText) todayText.textContent = text.charAt(0).toUpperCase() + text.slice(1);
}

async function fetchDaily(date) {
  if (errorBox) errorBox.classList.add('d-none');
  showLoader(true);

  // Try cached daily plan (short TTL)
  const cached = cacheGet(`bd-daily:${date}`);
  if (cached) {
    renderPlan(cached.reading_plan);
    showLoader(false);
    return;
  }

  try {
    const headers = {};
    if (window.BD_SECRET) headers['X-BD-SECRET'] = window.BD_SECRET;
    const res = await fetch(`api/daily.php?date=${date}`, { headers });
    const data = await res.json();
    if (!data.success) throw new Error(data.message || 'API error');
    // Cache for 1 day
    cacheSet(`bd-daily:${date}`, data, 60 * 60 * 24);
    renderPlan(data.reading_plan);
  } catch (err) {
    console.error(err);
    if (errorBox) {
      errorBox.textContent = "Não foi possível carregar o conteúdo. Verifique sua conexão.";
      errorBox.classList.remove('d-none');
    }
  } finally {
    showLoader(false);
  }
}

async function renderPlan(data) {
  const container = document.getElementById('plan-cards');
  const progress = document.getElementById('plan-progress');
  if (!container) return;
  container.innerHTML = '';
  if (!data) return;
  if (progress) progress.textContent = data.day_index ? `Dia ${data.day_index} de 365` : 'Leitura Extra';

  if (data.segments) {
    data.segments.forEach((seg, i) => {
      const card = document.createElement('div');
      card.className = 'reading-card';
      card.style.animationDelay = `${i * 100}ms`;
      card.id = `seg-${i}`;
      const isRange = seg.from !== seg.to;
      const ref = isRange ? `${seg.book} ${seg.from}–${seg.to}` : `${seg.book} ${seg.from}`;

      card.innerHTML = `
        <div class="d-flex align-items-center mb-2">
          <span class="reading-label text-muted me-auto">Capítulos</span>
        </div>
        <h3 class="bible-ref mb-3">${ref}</h3>
        <div class="bible-text-content position-relative">
          <div class="placeholder-glow">
            <span class="placeholder col-7"></span>
            <span class="placeholder col-4"></span>
            <span class="placeholder col-4"></span>
            <span class="placeholder col-6"></span>
            <span class="placeholder col-8"></span>
          </div>
        </div>
      `;
      container.appendChild(card);

      // Fetch chapter text with caching
      fetchBibleText(seg.book, seg.from).then(text => {
        const contentDiv = card.querySelector('.bible-text-content');
        if (text) {
          contentDiv.innerHTML = `<div class="bible-text">${text.replace(/\n/g, '<br>')}</div>`;
          const link = document.createElement('a');
          link.href = `https://www.bibliaonline.com.br/acf/${kebabCase(seg.book)}/${seg.from}`;
          link.target = '_blank';
          link.className = 'd-block mt-3 small text-muted text-uppercase fw-bold';
          link.innerHTML = 'Ler capítulo completo <i class="bi bi-box-arrow-up-right ms-1"></i>';
          contentDiv.appendChild(link);
        } else {
          contentDiv.innerHTML = '<div class="text-muted small fst-italic">Texto não disponível na API pública (provavelmente livro Deuterocanônico).<br>Consulte sua Bíblia física.</div>';
        }
      });
    });
  }
}

const deuterocanonical = ['Tobias', 'Judite', '1 Macabeus', '2 Macabeus', 'Sabedoria', 'Eclesiástico', 'Baruc', 'Baruque'];

async function fetchBibleText(book, chapter) {
  const norm = book.normalize("NFD").replace(/[\u0300-\u036f]/g, "");
  if (deuterocanonical.some(d => d.normalize("NFD").replace(/[\u0300-\u036f]/g, "") === norm)) return null;

  const key = `bd-chapter:${book}:${chapter}`;
  const cached = cacheGet(key);
  if (cached) return cached;

  try {
    // Use server-side cached endpoint
    try {
      const headers = {};
      if (window.BD_SECRET) headers['X-BD-SECRET'] = window.BD_SECRET;
      const res = await fetch(`api/bible.php?book=${encodeURIComponent(book)}&chapter=${encodeURIComponent(chapter)}`, { headers });
      const json = await res.json();
      if (!json.success) return null;
      const text = json.text || null;
      if (text) cacheSet(key, text, 60 * 60 * 24 * 7); // mirror client cache
      return text;
    } catch (e) {
      console.error(e);
      return null;
    }
  } catch (e) {
    console.error(e);
    return null;
  }
}

function kebabCase(str) {
  return str.normalize("NFD").replace(/[\u0300-\u036f]/g, "").toLowerCase().replace(/\s+/g, '-');
}

// Boot
init();
