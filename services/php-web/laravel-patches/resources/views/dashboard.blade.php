@extends('layouts.app')

@section('content')
<div class="container pb-5">
  <div class="page-header mb-5 text-center">
    <h1 class="display-4 fw-bold mb-3">🌌 Космический дашборд</h1>
    <p class="lead text-muted">Мониторинг космических данных в реальном времени</p>
  </div>

  {{-- Краткие метрики --}}
  <div class="row g-4 mb-5">
    <div class="col-md-4">
      <div class="metric-card-large border rounded p-4 text-center h-100">
        <div class="metric-icon-large mb-3">🚀</div>
        <h3 class="h5 mb-3">Международная космическая станция</h3>
        <div class="mb-3">
          <div class="small text-muted">Скорость</div>
          <div class="fs-4 fw-bold">
            {{ isset(($iss['payload'] ?? [])['velocity']) ? number_format($iss['payload']['velocity'],0,'',' ') : '—' }} км/ч
          </div>
        </div>
        <div class="mb-3">
          <div class="small text-muted">Высота</div>
          <div class="fs-4 fw-bold">
            {{ isset(($iss['payload'] ?? [])['altitude']) ? number_format($iss['payload']['altitude'],0,'',' ') : '—' }} км
          </div>
        </div>
        <a href="/iss" class="btn btn-primary mt-3">
          <i class="bi bi-arrow-right"></i> Подробнее о МКС
        </a>
      </div>
    </div>

    <div class="col-md-4">
      <div class="metric-card-large border rounded p-4 text-center h-100">
        <div class="metric-icon-large mb-3">🔭</div>
        <h3 class="h5 mb-3">Телескоп Джеймса Уэбба</h3>
        <p class="text-muted mb-4">Последние изображения и наблюдения космического телескопа JWST</p>
        <a href="/jwst" class="btn btn-primary mt-3">
          <i class="bi bi-arrow-right"></i> Открыть галерею JWST
        </a>
          </div>
    </div>

    <div class="col-md-4">
      <div class="metric-card-large border rounded p-4 text-center h-100">
        <div class="metric-icon-large mb-3">📊</div>
        <h3 class="h5 mb-3">NASA OSDR</h3>
        <p class="text-muted mb-4">Данные из Open Science Data Repository NASA</p>
        <a href="/osdr" class="btn btn-primary mt-3">
          <i class="bi bi-arrow-right"></i> Просмотр данных OSDR
        </a>
        </div>
      </div>
    </div>

  {{-- Быстрый доступ --}}
  <div class="row">
    <div class="col-12">
      <div class="card shadow-sm">
        <div class="card-header bg-gradient-primary text-white">
          <h5 class="card-title m-0">⚡ Быстрый доступ</h5>
        </div>
        <div class="card-body">
          <div class="row g-3">
            <div class="col-md-3">
              <a href="/iss" class="quick-link-card d-block border rounded p-3 text-center text-decoration-none">
                <div class="quick-link-icon">🚀</div>
                <div class="fw-semibold">МКС</div>
                <div class="small text-muted">Отслеживание станции</div>
              </a>
              </div>
            <div class="col-md-3">
              <a href="/jwst" class="quick-link-card d-block border rounded p-3 text-center text-decoration-none">
                <div class="quick-link-icon">🔭</div>
                <div class="fw-semibold">JWST</div>
                <div class="small text-muted">Галерея изображений</div>
              </a>
              </div>
            <div class="col-md-3">
              <a href="/osdr" class="quick-link-card d-block border rounded p-3 text-center text-decoration-none">
                <div class="quick-link-icon">📊</div>
                <div class="fw-semibold">OSDR</div>
                <div class="small text-muted">Данные NASA</div>
              </a>
              </div>
            <div class="col-md-3">
              <div class="quick-link-card d-block border rounded p-3 text-center">
                <div class="quick-link-icon">🌌</div>
                <div class="fw-semibold">API</div>
                <div class="small text-muted">Документация</div>
              </div>
          </div>
          </div>
        </div>
      </div>
    </div>
  </div>
</div>

<style>
.page-header {
  animation: fadeInUp 0.6s ease-out;
}

.metric-card-large {
  background: linear-gradient(135deg, #ffffff 0%, #f8f9fa 100%);
  transition: all 0.3s ease;
  border: 2px solid transparent !important;
}

.metric-card-large:hover {
  transform: translateY(-8px);
  box-shadow: 0 12px 24px rgba(0,0,0,0.15) !important;
  border-color: #007bff !important;
}

.metric-icon-large {
  font-size: 4rem;
  animation: float 3s ease-in-out infinite;
}

.quick-link-card {
  transition: all 0.3s ease;
  color: inherit;
  background: #f8f9fa;
}

.quick-link-card:hover {
  transform: translateY(-5px);
  box-shadow: 0 8px 16px rgba(0,0,0,0.1);
  background: #fff;
  border-color: #007bff !important;
  color: inherit;
}

.quick-link-icon {
  font-size: 2.5rem;
  margin-bottom: 0.5rem;
}

.bg-gradient-primary {
  background: linear-gradient(135deg, #007bff 0%, #0056b3 100%);
}

@keyframes float {
  0%, 100% { transform: translateY(0px); }
  50% { transform: translateY(-15px); }
}
</style>
@endsection
