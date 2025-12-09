@extends('layouts.app')

@section('content')
<div class="container pb-5">
  <div class="page-header mb-4">
    <h1 class="display-5 fw-bold">Международная космическая станция</h1>
    <p class="lead text-muted">Отслеживание положения и параметров МКС в реальном времени</p>
  </div>

  {{-- Метрики --}}
  <div class="row g-3 mb-4">
    <div class="col-6 col-md-3">
      <div class="metric-card border rounded p-3 text-center">
        <div class="metric-icon">🚀</div>
        <div class="small text-muted mb-2">Скорость МКС</div>
        <div class="fs-3 fw-bold metric-value" id="issSpeed">
          {{ isset(($iss['payload'] ?? [])['velocity']) ? number_format($iss['payload']['velocity'],0,'',' ') : '—' }}
        </div>
        <div class="small text-muted">км/ч</div>
      </div>
    </div>
    <div class="col-6 col-md-3">
      <div class="metric-card border rounded p-3 text-center">
        <div class="metric-icon">📡</div>
        <div class="small text-muted mb-2">Высота МКС</div>
        <div class="fs-3 fw-bold metric-value" id="issAlt">
          {{ isset(($iss['payload'] ?? [])['altitude']) ? number_format($iss['payload']['altitude'],0,'',' ') : '—' }}
        </div>
        <div class="small text-muted">км</div>
      </div>
    </div>
    <div class="col-6 col-md-3">
      <div class="metric-card border rounded p-3 text-center">
        <div class="metric-icon">🌍</div>
        <div class="small text-muted mb-2">Широта</div>
        <div class="fs-4 fw-bold" id="issLat">
          {{ isset(($iss['payload'] ?? [])['latitude']) ? number_format($iss['payload']['latitude'],2,'.',',') : '—' }}
        </div>
      </div>
    </div>
    <div class="col-6 col-md-3">
      <div class="metric-card border rounded p-3 text-center">
        <div class="metric-icon">🌐</div>
        <div class="small text-muted mb-2">Долгота</div>
        <div class="fs-4 fw-bold" id="issLon">
          {{ isset(($iss['payload'] ?? [])['longitude']) ? number_format($iss['payload']['longitude'],2,'.',',') : '—' }}
        </div>
      </div>
    </div>
  </div>

  <div class="row g-3">
    {{-- Карта МКС --}}
    <div class="col-lg-8">
      <div class="card shadow-sm h-100">
        <div class="card-header bg-primary text-white">
          <h5 class="card-title m-0">📍 Положение МКС на карте</h5>
        </div>
        <div class="card-body">
          <div id="map" class="rounded border" style="height:500px"></div>
          <div class="mt-3 small text-muted">
            <i class="bi bi-info-circle"></i> Обновление каждые 15 секунд
          </div>
        </div>
      </div>
    </div>

    {{-- Графики --}}
    <div class="col-lg-4">
      <div class="card shadow-sm mb-3">
        <div class="card-header bg-info text-white">
          <h6 class="card-title m-0">📊 Скорость</h6>
        </div>
        <div class="card-body">
          <canvas id="issSpeedChart" height="200"></canvas>
        </div>
      </div>
      <div class="card shadow-sm">
        <div class="card-header bg-success text-white">
          <h6 class="card-title m-0">📈 Высота</h6>
        </div>
        <div class="card-body">
          <canvas id="issAltChart" height="200"></canvas>
        </div>
      </div>
    </div>
  </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', async function () {
  if (typeof L !== 'undefined' && typeof Chart !== 'undefined') {
    const last = @json(($iss['payload'] ?? []));
    let lat0 = Number(last.latitude || 0), lon0 = Number(last.longitude || 0);
    
    // Инициализация карты
    const map = L.map('map', { 
      attributionControl: false,
      zoomControl: true 
    }).setView([lat0||0, lon0||0], lat0 ? 3 : 2);
    
    L.tileLayer('https://{s}.tile.openstreetmap.de/{z}/{x}/{y}.png', { 
      noWrap: true,
      maxZoom: 18
    }).addTo(map);
    
    const trail = L.polyline([], {
      weight: 4,
      color: '#007bff',
      opacity: 0.7
    }).addTo(map);
    
    const marker = L.marker([lat0||0, lon0||0], {
      icon: L.divIcon({
        className: 'iss-marker',
        html: '<div class="iss-marker-icon">🚀</div>',
        iconSize: [40, 40],
        iconAnchor: [20, 20]
      })
    }).addTo(map).bindPopup('<strong>МКС</strong><br>Международная космическая станция');

    // Графики
    const speedChart = new Chart(document.getElementById('issSpeedChart'), {
      type: 'line', 
      data: { 
        labels: [], 
        datasets: [{ 
          label: 'Скорость (км/ч)', 
          data: [],
          borderColor: 'rgb(75, 192, 192)',
          backgroundColor: 'rgba(75, 192, 192, 0.1)',
          tension: 0.4,
          fill: true
        }] 
      },
      options: { 
        responsive: true,
        maintainAspectRatio: false,
        animation: {
          duration: 1000,
          easing: 'easeInOutQuart'
        },
        plugins: {
          legend: { display: false }
        },
        scales: { 
          x: { display: false },
          y: { 
            beginAtZero: false,
            ticks: { 
              callback: function(value) { return value.toLocaleString('ru-RU'); }
            }
          }
        } 
      }
    });
    
    const altChart = new Chart(document.getElementById('issAltChart'), {
      type: 'line', 
      data: { 
        labels: [], 
        datasets: [{ 
          label: 'Высота (км)', 
          data: [],
          borderColor: 'rgb(40, 167, 69)',
          backgroundColor: 'rgba(40, 167, 69, 0.1)',
          tension: 0.4,
          fill: true
        }] 
      },
      options: { 
        responsive: true,
        maintainAspectRatio: false,
        animation: {
          duration: 1000,
          easing: 'easeInOutQuart'
        },
        plugins: {
          legend: { display: false }
        },
        scales: { 
          x: { display: false },
          y: { 
            beginAtZero: false,
            ticks: { 
              callback: function(value) { return value.toLocaleString('ru-RU'); }
            }
          }
        } 
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
          
          // Обновляем карту
          if (pts.length > 1) {
            const bounds = L.latLngBounds(pts);
            map.fitBounds(bounds, { padding: [50, 50] });
          }
        }
        
        const t = (js.points||[]).map(p => new Date(p.at).toLocaleTimeString('ru-RU'));
        const velocities = (js.points||[]).map(p => p.velocity);
        const altitudes = (js.points||[]).map(p => p.altitude);
        
        // Обновляем графики
        speedChart.data.labels = t;
        speedChart.data.datasets[0].data = velocities;
        speedChart.update('active');
        
        altChart.data.labels = t;
        altChart.data.datasets[0].data = altitudes;
        altChart.update('active');
        
        // Обновляем метрики
        if (velocities.length > 0) {
          const speedEl = document.getElementById('issSpeed');
          const newSpeed = Math.round(velocities[velocities.length - 1]);
          if (speedEl && speedEl.textContent.replace(/\s/g, '') !== newSpeed.toLocaleString('ru-RU').replace(/\s/g, '')) {
            speedEl.textContent = newSpeed.toLocaleString('ru-RU');
            speedEl.classList.add('updated');
            setTimeout(() => speedEl.classList.remove('updated'), 500);
          }
        }
        if (altitudes.length > 0) {
          const altEl = document.getElementById('issAlt');
          const newAlt = Math.round(altitudes[altitudes.length - 1]);
          if (altEl && altEl.textContent.replace(/\s/g, '') !== newAlt.toLocaleString('ru-RU').replace(/\s/g, '')) {
            altEl.textContent = newAlt.toLocaleString('ru-RU');
            altEl.classList.add('updated');
            setTimeout(() => altEl.classList.remove('updated'), 500);
          }
        }
        if (pts.length > 0) {
          const latEl = document.getElementById('issLat');
          const lonEl = document.getElementById('issLon');
          const lastPt = pts[pts.length - 1];
          if (latEl) latEl.textContent = lastPt[0].toFixed(2);
          if (lonEl) lonEl.textContent = lastPt[1].toFixed(2);
        }
      } catch(e) {
        console.error('Ошибка загрузки данных МКС:', e);
      }
    }
    
    loadTrend();
    setInterval(loadTrend, 15000);
  }
});
</script>

<style>
.metric-card {
  background: linear-gradient(135deg, #f8f9fa 0%, #ffffff 100%);
  transition: all 0.3s ease;
  border: 2px solid transparent !important;
}

.metric-card:hover {
  transform: translateY(-5px);
  box-shadow: 0 8px 20px rgba(0,0,0,0.15) !important;
  border-color: #007bff !important;
}

.metric-icon {
  font-size: 2rem;
  margin-bottom: 0.5rem;
  animation: float 3s ease-in-out infinite;
}

@keyframes float {
  0%, 100% { transform: translateY(0px); }
  50% { transform: translateY(-10px); }
}

.metric-value.updated {
  animation: pulse 0.5s ease-out;
  color: #007bff;
}

.iss-marker-icon {
  font-size: 2rem;
  animation: rotate 10s linear infinite;
  filter: drop-shadow(0 2px 4px rgba(0,0,0,0.3));
}

@keyframes rotate {
  from { transform: rotate(0deg); }
  to { transform: rotate(360deg); }
}

.card-header {
  border-bottom: 2px solid rgba(0,0,0,0.1);
}
</style>
@endsection
