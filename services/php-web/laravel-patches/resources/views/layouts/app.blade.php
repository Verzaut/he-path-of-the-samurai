<!doctype html>
<html lang="ru">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>Space Dashboard</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
  <link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css"/>
  <style>
    #map{height:340px}
    
    /* Анимации появления */
    @keyframes fadeInUp {
      from {
        opacity: 0;
        transform: translateY(20px);
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
        transform: translateX(-20px);
      }
      to {
        opacity: 1;
        transform: translateX(0);
      }
    }
    
    @keyframes pulse {
      0%, 100% { opacity: 1; }
      50% { opacity: 0.5; }
    }
    
    @keyframes shimmer {
      0% { background-position: -1000px 0; }
      100% { background-position: 1000px 0; }
    }
    
    /* Анимация для карточек */
    .card {
      animation: fadeInUp 0.6s ease-out;
      transition: transform 0.3s ease, box-shadow 0.3s ease;
    }
    
    .card:hover {
      transform: translateY(-5px);
      box-shadow: 0 8px 16px rgba(0,0,0,0.15) !important;
    }
    
    /* Анимация для навигации */
    .navbar {
      animation: fadeIn 0.4s ease-out;
    }
    
    .nav-link {
      transition: color 0.3s ease, transform 0.2s ease;
    }
    
    .nav-link:hover {
      transform: scale(1.05);
    }
    
    /* Анимация для метрик */
    .border.rounded {
      animation: fadeInUp 0.5s ease-out;
      transition: background-color 0.3s ease, transform 0.2s ease;
    }
    
    .border.rounded:hover {
      background-color: #f8f9fa;
      transform: scale(1.02);
    }
    
    /* Анимация загрузки */
    .loading {
      animation: pulse 1.5s ease-in-out infinite;
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
    
    /* Анимация для кнопок */
    .btn {
      transition: all 0.3s ease;
    }
    
    .btn:hover {
      transform: translateY(-2px);
      box-shadow: 0 4px 8px rgba(0,0,0,0.2);
    }
    
    .btn:active {
      transform: translateY(0);
    }
    
    /* Анимация для таблиц */
    .table tbody tr {
      animation: fadeIn 0.4s ease-out;
      transition: background-color 0.2s ease;
    }
    
    .table tbody tr:hover {
      background-color: #f8f9fa;
    }
    
    /* Анимация для карты */
    #map {
      animation: fadeIn 0.8s ease-out;
      transition: opacity 0.3s ease;
    }
    
    /* Плавная прокрутка */
    html {
      scroll-behavior: smooth;
    }
    
    /* Анимация для графиков */
    canvas {
      animation: fadeIn 0.6s ease-out;
    }
    
    /* Скелетон загрузки */
    .skeleton {
      background: linear-gradient(90deg, #f0f0f0 25%, #e0e0e0 50%, #f0f0f0 75%);
      background-size: 200% 100%;
      animation: shimmer 1.5s infinite;
    }
  </style>
  <script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>
  <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
</head>
<body>
<nav class="navbar navbar-expand-lg bg-body-tertiary mb-3">
  <div class="container">
    <a class="navbar-brand" href="/dashboard">Dashboard</a>
    <a class="nav-link" href="/osdr">OSDR</a>
  </div>
</nav>
@yield('content')
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
