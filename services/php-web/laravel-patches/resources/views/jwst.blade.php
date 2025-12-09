@extends('layouts.app')

@section('content')
<div class="container pb-5">
  <div class="page-header mb-4">
    <h1 class="display-5 fw-bold">🔭 Космический телескоп Джеймса Уэбба</h1>
    <p class="lead text-muted">Галерея последних изображений и наблюдений JWST</p>
  </div>

  <div class="card shadow-sm">
    <div class="card-header bg-dark text-white">
      <div class="d-flex justify-content-between align-items-center flex-wrap gap-3">
        <h5 class="card-title m-0">🖼️ Последние изображения JWST</h5>
        <form id="jwstFilter" class="row g-2 align-items-center">
          <div class="col-auto">
            <select class="form-select form-select-sm" name="source" id="srcSel">
              <option value="jpg" selected>Все JPG</option>
              <option value="suffix">По суффиксу</option>
              <option value="program">По программе</option>
            </select>
          </div>
          <div class="col-auto">
            <input type="text" class="form-control form-control-sm" name="suffix" id="suffixInp" placeholder="_cal / _thumb" style="width:140px;display:none">
            <input type="text" class="form-control form-control-sm" name="program" id="progInp" placeholder="2734" style="width:110px;display:none">
          </div>
          <div class="col-auto">
            <select class="form-select form-select-sm" name="instrument" style="width:130px">
              <option value="">Любой инструмент</option>
              <option>NIRCam</option><option>MIRI</option><option>NIRISS</option><option>NIRSpec</option><option>FGS</option>
            </select>
          </div>
          <div class="col-auto">
            <select class="form-select form-select-sm" name="perPage" style="width:90px">
              <option>12</option><option selected>24</option><option>36</option><option>48</option>
            </select>
          </div>
          <div class="col-auto">
            <button class="btn btn-sm btn-primary" type="submit">Показать</button>
          </div>
        </form>
      </div>
    </div>
    <div class="card-body">
      <div class="jwst-slider">
        <button class="btn btn-light border jwst-nav jwst-prev" type="button" aria-label="Prev">‹</button>
        <div id="jwstTrack" class="jwst-track border rounded"></div>
        <button class="btn btn-light border jwst-nav jwst-next" type="button" aria-label="Next">›</button>
      </div>
      <div id="jwstInfo" class="small text-muted mt-3"></div>
    </div>
  </div>
</div>

<style>
.jwst-slider {
  position: relative;
}

.jwst-track {
  display: flex;
  gap: 1rem;
  overflow-x: auto;
  overflow-y: hidden;
  scroll-snap-type: x mandatory;
  padding: 1rem;
  scroll-behavior: smooth;
  min-height: 220px;
}

.jwst-item {
  flex: 0 0 200px;
  scroll-snap-align: start;
  position: relative;
}

.jwst-item img {
  width: 100%;
  height: 200px;
  object-fit: cover;
  border-radius: 0.75rem;
  transition: transform 0.3s ease, opacity 0.3s ease, box-shadow 0.3s ease;
  border: 2px solid transparent;
}

.jwst-item img[loading="lazy"] {
  opacity: 0;
}

.jwst-item img.loaded {
  opacity: 1;
  animation: fadeIn 0.4s ease-out;
}

.jwst-item:hover img {
  transform: scale(1.08);
  box-shadow: 0 8px 24px rgba(0,0,0,0.3);
  border-color: #007bff;
  z-index: 10;
}

.jwst-cap {
  font-size: 0.85rem;
  margin-top: 0.5rem;
  color: #495057;
  transition: color 0.2s ease;
  text-align: center;
  line-height: 1.4;
}

.jwst-item:hover .jwst-cap {
  color: #007bff;
  font-weight: 500;
}

.jwst-nav {
  position: absolute;
  top: 50%;
  transform: translateY(-50%);
  z-index: 2;
  width: 40px;
  height: 40px;
  border-radius: 50%;
  opacity: 0.8;
  transition: opacity 0.3s ease, transform 0.2s ease, background-color 0.3s ease;
  font-size: 1.5rem;
  font-weight: bold;
  display: flex;
  align-items: center;
  justify-content: center;
}

.jwst-nav:hover {
  opacity: 1;
  transform: translateY(-50%) scale(1.15);
  background-color: #fff !important;
  box-shadow: 0 4px 12px rgba(0,0,0,0.2);
}

.jwst-prev {
  left: 10px;
}

.jwst-next {
  right: 10px;
}

.loading-skeleton {
  display: flex;
  gap: 1rem;
  padding: 1rem;
}

.loading-skeleton-item {
  flex: 0 0 200px;
  height: 200px;
  background: linear-gradient(90deg, #f0f0f0 25%, #e0e0e0 50%, #f0f0f0 75%);
  background-size: 200% 100%;
  animation: shimmer 1.5s infinite;
  border-radius: 0.75rem;
}

@keyframes shimmer {
  0% { background-position: -1000px 0; }
  100% { background-position: 1000px 0; }
}

.page-header {
  animation: fadeInUp 0.6s ease-out;
}
</style>

<script>
document.addEventListener('DOMContentLoaded', async function () {
  const track = document.getElementById('jwstTrack');
  const info  = document.getElementById('jwstInfo');
  const form  = document.getElementById('jwstFilter');
  const srcSel = document.getElementById('srcSel');
  const sfxInp = document.getElementById('suffixInp');
  const progInp = document.getElementById('progInp');

  function toggleInputs(){
    sfxInp.style.display  = (srcSel.value==='suffix')  ? '' : 'none';
    progInp.style.display = (srcSel.value==='program') ? '' : 'none';
  }
  srcSel.addEventListener('change', toggleInputs); 
  toggleInputs();

  async function loadFeed(qs){
    // Показываем скелетон загрузки
    track.innerHTML = '<div class="loading-skeleton">' + 
      Array(6).fill(0).map(() => '<div class="loading-skeleton-item"></div>').join('') + 
      '</div>';
    info.textContent= '';
    
    try{
      const url = '/api/jwst/feed?'+new URLSearchParams(qs).toString();
      const r = await fetch(url);
      const js = await r.json();
      track.innerHTML = '';
      
      // Анимация появления элементов с задержкой
      (js.items||[]).forEach((it, idx)=>{
        setTimeout(() => {
          const fig = document.createElement('figure');
          fig.className = 'jwst-item m-0';
          
          const img = document.createElement('img');
          img.loading = 'lazy';
          img.src = it.url;
          img.alt = 'JWST Image';
          img.onload = function() {
            this.classList.add('loaded');
          };
          
          const link = document.createElement('a');
          link.href = it.link || it.url;
          link.target = '_blank';
          link.rel = 'noreferrer';
          link.appendChild(img);
          
          const caption = document.createElement('figcaption');
          caption.className = 'jwst-cap';
          caption.textContent = (it.caption || '').replace(/</g, '&lt;');
          
          fig.appendChild(link);
          fig.appendChild(caption);
          track.appendChild(fig);
        }, idx * 50); // Задержка для каскадной анимации
      });
      
      // Показываем информацию с анимацией
      setTimeout(() => {
        info.innerHTML = `<strong>Источник:</strong> ${js.source} · <strong>Показано:</strong> ${js.count||0} изображений`;
        info.style.animation = 'fadeIn 0.4s ease-out';
      }, (js.items?.length || 0) * 50 + 100);
    }catch(e){
      track.innerHTML = '<div class="p-4 text-center text-danger"><i class="bi bi-exclamation-triangle"></i> Ошибка загрузки данных</div>';
      console.error('Ошибка загрузки JWST:', e);
    }
  }

  form.addEventListener('submit', function(ev){
    ev.preventDefault();
    const fd = new FormData(form);
    const q = Object.fromEntries(fd.entries());
    loadFeed(q);
  });

  // Навигация
  document.querySelector('.jwst-prev')?.addEventListener('click', () => {
    track.scrollBy({left: -600, behavior: 'smooth'});
  });
  document.querySelector('.jwst-next')?.addEventListener('click', () => {
    track.scrollBy({left: 600, behavior: 'smooth'});
  });

  // Стартовые данные
  loadFeed({source:'jpg', perPage:24});
});
</script>
@endsection

