@extends('layouts.app')

@section('content')
<div class="container pb-5">
  {{-- верхние карточки --}}
  <div class="row g-3 mb-2">
    <div class="col-6 col-md-3"><div class="border rounded p-2 text-center">
      <div class="small text-muted">Скорость МКС</div>
      <div class="fs-4 metric-value" id="issSpeed">{{ isset(($iss['payload'] ?? [])['velocity']) ? number_format($iss['payload']['velocity'],0,'',' ') : '—' }}</div>
    </div></div>
    <div class="col-6 col-md-3"><div class="border rounded p-2 text-center">
      <div class="small text-muted">Высота МКС</div>
      <div class="fs-4 metric-value" id="issAlt">{{ isset(($iss['payload'] ?? [])['altitude']) ? number_format($iss['payload']['altitude'],0,'',' ') : '—' }}</div>
    </div></div>
  </div>

  <div class="row g-3">
    {{-- левая колонка: JWST наблюдение (как раньше было под APOD можно держать своим блоком) --}}
    <div class="col-lg-7">
      <div class="card shadow-sm h-100">
        <div class="card-body">
          <h5 class="card-title">JWST — выбранное наблюдение</h5>
          <div class="text-muted">Этот блок остаётся как был (JSON/сводка). Основная галерея ниже.</div>
        </div>
      </div>
    </div>

    {{-- правая колонка: карта МКС --}}
    <div class="col-lg-5">
      <div class="card shadow-sm h-100">
        <div class="card-body">
          <h5 class="card-title">МКС — положение и движение</h5>
          <div id="map" class="rounded mb-2 border" style="height:300px"></div>
          <div class="row g-2">
            <div class="col-6"><canvas id="issSpeedChart" height="110"></canvas></div>
            <div class="col-6"><canvas id="issAltChart"   height="110"></canvas></div>
          </div>
        </div>
      </div>
    </div>

    {{-- НИЖНЯЯ ПОЛОСА: НОВАЯ ГАЛЕРЕЯ JWST --}}
    <div class="col-12">
      <div class="card shadow-sm">
        <div class="card-body">
          <div class="d-flex justify-content-between align-items-center mb-2">
            <h5 class="card-title m-0">JWST — последние изображения</h5>
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

          <style>
            .jwst-slider{position:relative}
            .jwst-track{
              display:flex; gap:.75rem; overflow:auto; scroll-snap-type:x mandatory; padding:.25rem;
              scroll-behavior: smooth;
            }
            .jwst-item{flex:0 0 180px; scroll-snap-align:start}
            .jwst-item img{
              width:100%; height:180px; object-fit:cover; border-radius:.5rem;
              transition: transform 0.3s ease, opacity 0.3s ease;
            }
            .jwst-item img[loading="lazy"] {
              opacity: 0;
            }
            .jwst-item img.loaded {
              opacity: 1;
              animation: fadeIn 0.4s ease-out;
            }
            .jwst-cap{font-size:.85rem; margin-top:.25rem; transition: color 0.2s ease;}
            .jwst-nav{
              position:absolute; top:40%; transform:translateY(-50%); z-index:2;
              opacity: 0.7;
              transition: opacity 0.3s ease, transform 0.2s ease;
            }
            .jwst-nav:hover {
              opacity: 1;
              transform: translateY(-50%) scale(1.1);
            }
            .jwst-prev{left:-.25rem} .jwst-next{right:-.25rem}
            
            /* Скелетон загрузки */
            .loading-skeleton {
              display: flex;
              gap: 0.75rem;
              padding: 0.25rem;
            }
            .loading-skeleton-item {
              flex: 0 0 180px;
              height: 180px;
              background: linear-gradient(90deg, #f0f0f0 25%, #e0e0e0 50%, #f0f0f0 75%);
              background-size: 200% 100%;
              animation: shimmer 1.5s infinite;
              border-radius: 0.5rem;
            }
            
            /* Анимация для метрик */
            .metric-value {
              transition: transform 0.3s ease, color 0.3s ease;
            }
            .metric-value.updated {
              animation: pulse 0.5s ease-out;
            }
          </style>

          <div class="jwst-slider">
            <button class="btn btn-light border jwst-nav jwst-prev" type="button" aria-label="Prev">‹</button>
            <div id="jwstTrack" class="jwst-track border rounded"></div>
            <button class="btn btn-light border jwst-nav jwst-next" type="button" aria-label="Next">›</button>
          </div>

          <div id="jwstInfo" class="small text-muted mt-2"></div>
        </div>
      </div>
    </div>
  </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', async function () {
  // ====== карта и графики МКС (как раньше) ======
  if (typeof L !== 'undefined' && typeof Chart !== 'undefined') {
    const last = @json(($iss['payload'] ?? []));
    let lat0 = Number(last.latitude || 0), lon0 = Number(last.longitude || 0);
    const map = L.map('map', { attributionControl:false }).setView([lat0||0, lon0||0], lat0?3:2);
    L.tileLayer('https://{s}.tile.openstreetmap.de/{z}/{x}/{y}.png', { noWrap:true }).addTo(map);
    const trail  = L.polyline([], {weight:3}).addTo(map);
    const marker = L.marker([lat0||0, lon0||0]).addTo(map).bindPopup('МКС');

    const speedChart = new Chart(document.getElementById('issSpeedChart'), {
      type: 'line', 
      data: { 
        labels: [], 
        datasets: [{ 
          label: 'Скорость', 
          data: [],
          borderColor: 'rgb(75, 192, 192)',
          backgroundColor: 'rgba(75, 192, 192, 0.1)',
          tension: 0.4
        }] 
      },
      options: { 
        responsive: true, 
        animation: {
          duration: 1000,
          easing: 'easeInOutQuart'
        },
        scales: { x: { display: false } } 
      }
    });
    const altChart = new Chart(document.getElementById('issAltChart'), {
      type: 'line', 
      data: { 
        labels: [], 
        datasets: [{ 
          label: 'Высота', 
          data: [],
          borderColor: 'rgb(255, 99, 132)',
          backgroundColor: 'rgba(255, 99, 132, 0.1)',
          tension: 0.4
        }] 
      },
      options: { 
        responsive: true,
        animation: {
          duration: 1000,
          easing: 'easeInOutQuart'
        },
        scales: { x: { display: false } } 
      }
    });

    async function loadTrend() {
      try {
        const r = await fetch('/api/iss/trend?limit=240');
        const js = await r.json();
        const pts = Array.isArray(js.points) ? js.points.map(p => [p.lat, p.lon]) : [];
        if (pts.length) {
          trail.setLatLngs(pts);
          const lastPoint = pts[pts.length-1];
          marker.setLatLng(lastPoint);
          
          // Плавная анимация перемещения маркера
          marker.setLatLng(lastPoint, {animate: true, duration: 1.0});
        }
        const t = (js.points||[]).map(p => new Date(p.at).toLocaleTimeString());
        const velocities = (js.points||[]).map(p => p.velocity);
        const altitudes = (js.points||[]).map(p => p.altitude);
        
        // Обновляем графики с анимацией
        speedChart.data.labels = t;
        speedChart.data.datasets[0].data = velocities;
        speedChart.update('active');
        
        altChart.data.labels = t;
        altChart.data.datasets[0].data = altitudes;
        altChart.update('active');
        
        // Обновляем метрики с анимацией
        if (velocities.length > 0) {
          const speedEl = document.getElementById('issSpeed');
          const newSpeed = Math.round(velocities[velocities.length - 1]);
          if (speedEl && speedEl.textContent !== newSpeed.toLocaleString('ru-RU')) {
            speedEl.textContent = newSpeed.toLocaleString('ru-RU');
            speedEl.classList.add('updated');
            setTimeout(() => speedEl.classList.remove('updated'), 500);
          }
        }
        if (altitudes.length > 0) {
          const altEl = document.getElementById('issAlt');
          const newAlt = Math.round(altitudes[altitudes.length - 1]);
          if (altEl && altEl.textContent !== newAlt.toLocaleString('ru-RU')) {
            altEl.textContent = newAlt.toLocaleString('ru-RU');
            altEl.classList.add('updated');
            setTimeout(() => altEl.classList.remove('updated'), 500);
          }
        }
      } catch(e) {}
    }
    loadTrend();
    setInterval(loadTrend, 15000);
  }

  // ====== JWST ГАЛЕРЕЯ ======
  const track = document.getElementById('jwstTrack');
  const info  = document.getElementById('jwstInfo');
  const form  = document.getElementById('jwstFilter');
  const srcSel = document.getElementById('srcSel');
  const sfxInp = document.getElementById('suffixInp');
  const progInp= document.getElementById('progInp');

  function toggleInputs(){
    sfxInp.style.display  = (srcSel.value==='suffix')  ? '' : 'none';
    progInp.style.display = (srcSel.value==='program') ? '' : 'none';
  }
  srcSel.addEventListener('change', toggleInputs); toggleInputs();

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
          img.alt = 'JWST';
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
        info.textContent = `Источник: ${js.source} · Показано ${js.count||0}`;
        info.style.animation = 'fadeIn 0.4s ease-out';
      }, (js.items?.length || 0) * 50 + 100);
    }catch(e){
      track.innerHTML = '<div class="p-3 text-danger">Ошибка загрузки</div>';
    }
  }

  form.addEventListener('submit', function(ev){
    ev.preventDefault();
    const fd = new FormData(form);
    const q = Object.fromEntries(fd.entries());
    loadFeed(q);
  });

  // навигация
  document.querySelector('.jwst-prev').addEventListener('click', ()=> track.scrollBy({left:-600, behavior:'smooth'}));
  document.querySelector('.jwst-next').addEventListener('click', ()=> track.scrollBy({left: 600, behavior:'smooth'}));

  // стартовые данные
  loadFeed({source:'jpg', perPage:24});
});
</script>
@endsection

