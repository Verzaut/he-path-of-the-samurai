<!doctype html>
<html lang="ru">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>Space Dashboard</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
  <link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css"/>
  <style>
    /* Глобальные стили */
    :root {
      --primary-color: #007bff;
      --secondary-color: #6c757d;
      --success-color: #28a745;
      --info-color: #17a2b8;
      --warning-color: #ffc107;
      --danger-color: #dc3545;
      --dark-color: #343a40;
      --light-color: #f8f9fa;
      --gradient-primary: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
      --gradient-success: linear-gradient(135deg, #11998e 0%, #38ef7d 100%);
      --gradient-info: linear-gradient(135deg, #3494E6 0%, #EC6EAD 100%);
      --shadow-sm: 0 2px 4px rgba(0,0,0,0.1);
      --shadow-md: 0 4px 8px rgba(0,0,0,0.15);
      --shadow-lg: 0 8px 16px rgba(0,0,0,0.2);
      --shadow-xl: 0 12px 24px rgba(0,0,0,0.25);
    }
    
    body {
      background: linear-gradient(135deg, #f5f7fa 0%, #c3cfe2 100%);
      min-height: 100vh;
      font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, 'Helvetica Neue', Arial, sans-serif;
    }
    
    .container {
      position: relative;
      z-index: 1;
    }
    
    #map{height:340px}
    
    /* Анимации появления */
    @keyframes fadeInUp {
      from {
        opacity: 0;
        transform: translateY(30px);
      }
      to {
        opacity: 1;
        transform: translateY(0);
      }
    }
    
    @keyframes fadeIn {
      from { opacity: 0; }
      to { opacity: 1; }
    }
    
    @keyframes slideIn {
      from {
        opacity: 0;
        transform: translateX(-30px);
      }
      to {
        opacity: 1;
        transform: translateX(0);
      }
    }
    
    @keyframes slideInRight {
      from {
        opacity: 0;
        transform: translateX(30px);
      }
      to {
        opacity: 1;
        transform: translateX(0);
      }
    }
    
    @keyframes pulse {
      0%, 100% { 
        opacity: 1;
        transform: scale(1);
      }
      50% { 
        opacity: 0.8;
        transform: scale(1.05);
      }
    }
    
    @keyframes shimmer {
      0% { background-position: -1000px 0; }
      100% { background-position: 1000px 0; }
    }
    
    @keyframes glow {
      0%, 100% {
        box-shadow: 0 0 5px rgba(0, 123, 255, 0.5);
      }
      50% {
        box-shadow: 0 0 20px rgba(0, 123, 255, 0.8);
      }
    }
    
    @keyframes rotate {
      from { transform: rotate(0deg); }
      to { transform: rotate(360deg); }
    }
    
    @keyframes float {
      0%, 100% { transform: translateY(0px); }
      50% { transform: translateY(-15px); }
    }
    
    @keyframes gradient {
      0% { background-position: 0% 50%; }
      50% { background-position: 100% 50%; }
      100% { background-position: 0% 50%; }
    }
    
    /* Улучшенная навигация */
    .navbar {
      animation: fadeIn 0.4s ease-out;
      background: var(--gradient-primary) !important;
      box-shadow: var(--shadow-md);
      border-bottom: 3px solid rgba(255,255,255,0.2);
    }
    
    .navbar-brand {
      font-size: 1.5rem;
      text-shadow: 2px 2px 4px rgba(0,0,0,0.2);
      transition: transform 0.3s ease;
    }
    
    .navbar-brand:hover {
      transform: scale(1.05);
    }
    
    .nav-link {
      transition: all 0.3s ease;
      border-radius: 5px;
      margin: 0 2px;
      padding: 8px 12px !important;
    }
    
    .nav-link:hover {
      background-color: rgba(255,255,255,0.1);
      transform: translateY(-2px);
      box-shadow: 0 4px 8px rgba(0,0,0,0.2);
    }
    
    /* Анимация для карточек */
    .card {
      animation: fadeInUp 0.6s ease-out;
      transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
      border: none;
      border-radius: 12px;
      overflow: hidden;
      background: #ffffff;
      box-shadow: var(--shadow-sm);
    }
    
    .card:hover {
      transform: translateY(-8px) scale(1.02);
      box-shadow: var(--shadow-xl) !important;
    }
    
    .card-header {
      border-bottom: 2px solid rgba(0,0,0,0.1);
      font-weight: 600;
      padding: 1rem 1.25rem;
    }
    
    .card-body {
      padding: 1.5rem;
    }
    
    /* Улучшенные метрики */
    .metric-card, .border.rounded {
      animation: fadeInUp 0.5s ease-out;
      transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
      background: linear-gradient(135deg, #ffffff 0%, #f8f9fa 100%);
      border: 2px solid transparent !important;
      position: relative;
      overflow: hidden;
    }
    
    .metric-card::before, .border.rounded::before {
      content: '';
      position: absolute;
      top: 0;
      left: -100%;
      width: 100%;
      height: 100%;
      background: linear-gradient(90deg, transparent, rgba(255,255,255,0.3), transparent);
      transition: left 0.5s;
    }
    
    .metric-card:hover::before, .border.rounded:hover::before {
      left: 100%;
    }
    
    .metric-card:hover, .border.rounded:hover {
      background: linear-gradient(135deg, #f8f9fa 0%, #ffffff 100%);
      transform: translateY(-5px) scale(1.03);
      box-shadow: var(--shadow-lg);
      border-color: var(--primary-color) !important;
    }
    
    /* Анимация загрузки */
    .loading {
      animation: pulse 1.5s ease-in-out infinite;
    }
    
    /* Улучшенные заголовки страниц */
    .page-header {
      background: linear-gradient(135deg, rgba(255,255,255,0.9) 0%, rgba(255,255,255,0.7) 100%);
      padding: 2rem;
      border-radius: 15px;
      box-shadow: var(--shadow-md);
      margin-bottom: 2rem;
      backdrop-filter: blur(10px);
    }
    
    .display-4, .display-5 {
      background: var(--gradient-primary);
      -webkit-background-clip: text;
      -webkit-text-fill-color: transparent;
      background-clip: text;
      font-weight: 700;
    }
    
    /* Анимация для галереи JWST */
    .jwst-item {
      animation: slideIn 0.4s ease-out;
      transition: transform 0.3s ease, opacity 0.3s ease;
      animation-fill-mode: both;
    }
    
    .jwst-item:nth-child(1) { animation-delay: 0.1s; }
    .jwst-item:nth-child(2) { animation-delay: 0.2s; }
    .jwst-item:nth-child(3) { animation-delay: 0.3s; }
    .jwst-item:nth-child(4) { animation-delay: 0.4s; }
    .jwst-item:nth-child(5) { animation-delay: 0.5s; }
    .jwst-item:nth-child(n+6) { animation-delay: 0.6s; }
    
    .jwst-item:hover {
      transform: scale(1.05);
      z-index: 10;
    }
    
    .jwst-item img {
      transition: transform 0.3s ease;
    }
    
    .jwst-item:hover img {
      transform: scale(1.1);
    }
    
    /* Улучшенные кнопки */
    .btn {
      transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
      border-radius: 8px;
      font-weight: 500;
      position: relative;
      overflow: hidden;
    }
    
    .btn::before {
      content: '';
      position: absolute;
      top: 50%;
      left: 50%;
      width: 0;
      height: 0;
      border-radius: 50%;
      background: rgba(255,255,255,0.3);
      transform: translate(-50%, -50%);
      transition: width 0.6s, height 0.6s;
    }
    
    .btn:hover::before {
      width: 300px;
      height: 300px;
    }
    
    .btn:hover {
      transform: translateY(-3px);
      box-shadow: var(--shadow-lg);
    }
    
    .btn:active {
      transform: translateY(-1px);
    }
    
    .btn-primary {
      background: var(--gradient-primary);
      border: none;
    }
    
    /* Анимация для таблиц */
    .table {
      border-radius: 8px;
      overflow: hidden;
    }
    
    .table thead {
      background: var(--gradient-primary);
      color: white;
    }
    
    .table tbody tr {
      animation: fadeIn 0.4s ease-out;
      transition: all 0.2s ease;
    }
    
    .table tbody tr:hover {
      background-color: #f8f9fa;
      transform: scale(1.01);
      box-shadow: var(--shadow-sm);
    }
    
    /* Анимация для карты */
    #map {
      animation: fadeIn 0.8s ease-out;
      transition: all 0.3s ease;
      border-radius: 12px;
      box-shadow: var(--shadow-md);
      overflow: hidden;
    }
    
    /* Плавная прокрутка */
    html {
      scroll-behavior: smooth;
    }
    
    /* Анимация для графиков */
    canvas {
      animation: fadeIn 0.6s ease-out;
      border-radius: 8px;
    }
    
    /* Скелетон загрузки */
    .skeleton {
      background: linear-gradient(90deg, #f0f0f0 25%, #e0e0e0 50%, #f0f0f0 75%);
      background-size: 200% 100%;
      animation: shimmer 1.5s infinite;
      border-radius: 8px;
    }
    
    /* Формы */
    .form-control, .form-select {
      border-radius: 8px;
      border: 2px solid #e0e0e0;
      transition: all 0.3s ease;
    }
    
    .form-control:focus, .form-select:focus {
      border-color: var(--primary-color);
      box-shadow: 0 0 0 0.2rem rgba(0, 123, 255, 0.25);
      transform: translateY(-2px);
    }
    
    /* Дополнительные эффекты */
    .shadow-sm { box-shadow: var(--shadow-sm) !important; }
    .shadow-md { box-shadow: var(--shadow-md) !important; }
    .shadow-lg { box-shadow: var(--shadow-lg) !important; }
    .shadow-xl { box-shadow: var(--shadow-xl) !important; }
    
    /* Адаптивность */
    @media (max-width: 768px) {
      .page-header {
        padding: 1.5rem;
      }
      
      .display-4, .display-5 {
        font-size: 2rem;
      }
    }
  </style>
  <script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>
  <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
</head>
<body>
<nav class="navbar navbar-expand-lg navbar-dark bg-primary mb-3 shadow-sm">
  <div class="container">
    <a class="navbar-brand fw-bold" href="/dashboard">
      <span class="me-2">🌌</span>Space Dashboard
    </a>
    <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
      <span class="navbar-toggler-icon"></span>
    </button>
    <div class="collapse navbar-collapse" id="navbarNav">
      <ul class="navbar-nav ms-auto">
        <li class="nav-item">
          <a class="nav-link" href="/dashboard">
            <span class="me-1">🏠</span>Главная
          </a>
        </li>
        <li class="nav-item">
          <a class="nav-link" href="/iss">
            <span class="me-1">🚀</span>МКС
          </a>
        </li>
        <li class="nav-item">
          <a class="nav-link" href="/jwst">
            <span class="me-1">🔭</span>JWST
          </a>
        </li>
        <li class="nav-item">
          <a class="nav-link" href="/osdr">
            <span class="me-1">📊</span>OSDR
          </a>
        </li>
      </ul>
    </div>
  </div>
</nav>
@yield('content')
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
