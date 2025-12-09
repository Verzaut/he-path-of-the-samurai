@extends('layouts.app')

@section('content')
<div class="container pb-5">
  <div class="page-header mb-4">
    <h1 class="display-5 fw-bold">📊 NASA OSDR</h1>
    <p class="lead text-muted">Open Science Data Repository — открытый репозиторий научных данных NASA</p>
    <div class="small text-muted">
      <i class="bi bi-info-circle"></i> Источник: {{ $src }}
    </div>
  </div>

  {{-- Панель фильтров и поиска --}}
  <div class="card shadow-sm mb-4 border-primary">
    <div class="card-header bg-gradient-primary text-white">
      <h5 class="card-title m-0">
        <i class="bi bi-funnel"></i> Фильтры и сортировка
      </h5>
    </div>
    <div class="card-body">
      <div class="row g-3 align-items-end">
        <div class="col-md-4">
          <label class="form-label fw-semibold">
            <i class="bi bi-search"></i> Поиск по ключевым словам
          </label>
          <div class="input-group">
            <span class="input-group-text"><i class="bi bi-search"></i></span>
            <input type="text" id="searchInput" class="form-control" placeholder="Поиск по названию, ID, URL...">
          </div>
        </div>
        <div class="col-md-2">
          <label class="form-label fw-semibold">
            <i class="bi bi-calendar-event"></i> Дата от
          </label>
          <input type="date" id="dateFrom" class="form-control">
        </div>
        <div class="col-md-2">
          <label class="form-label fw-semibold">
            <i class="bi bi-calendar-check"></i> Дата до
          </label>
          <input type="date" id="dateTo" class="form-control">
        </div>
        <div class="col-md-2">
          <label class="form-label fw-semibold">
            <i class="bi bi-sort-down"></i> Сортировка по
          </label>
          <select id="sortColumn" class="form-select">
            <option value="">Выберите столбец</option>
            <option value="id">ID</option>
            <option value="dataset_id">Dataset ID</option>
            <option value="title">Название</option>
            <option value="updated_at" selected>Дата обновления</option>
            <option value="inserted_at">Дата добавления</option>
          </select>
        </div>
        <div class="col-md-2">
          <label class="form-label fw-semibold">
            <i class="bi bi-arrow-up-down"></i> Порядок
          </label>
          <select id="sortOrder" class="form-select">
            <option value="asc">По возрастанию ↑</option>
            <option value="desc" selected>По убыванию ↓</option>
          </select>
        </div>
      </div>
      <div class="row mt-3 pt-3 border-top">
        <div class="col-12 d-flex align-items-center justify-content-between">
          <div>
            <button id="resetFilters" class="btn btn-outline-secondary btn-sm">
              <i class="bi bi-arrow-counterclockwise"></i> Сбросить фильтры
            </button>
          </div>
          <div>
            <span id="resultsCount" class="badge bg-primary fs-6"></span>
          </div>
        </div>
      </div>
    </div>
  </div>

  <div class="card shadow-sm">
    <div class="card-header bg-gradient-primary text-white">
      <h5 class="card-title m-0">📋 Список наборов данных</h5>
    </div>
    <div class="card-body p-0">
      <div class="table-responsive">
        <table id="osdrTable" class="table table-hover align-middle mb-0">
          <thead>
            <tr>
              <th class="sortable" data-column="id">
                # <span class="sort-indicator"></span>
              </th>
              <th class="sortable" data-column="dataset_id">
                Dataset ID <span class="sort-indicator"></span>
              </th>
              <th class="sortable" data-column="title">
                Название <span class="sort-indicator"></span>
              </th>
              <th>REST URL</th>
              <th class="sortable" data-column="updated_at">
                Обновлено <span class="sort-indicator"></span>
              </th>
              <th class="sortable" data-column="inserted_at">
                Добавлено <span class="sort-indicator"></span>
              </th>
              <th>Действия</th>
            </tr>
          </thead>
          <tbody id="tableBody">
          @forelse($items as $row)
            <tr data-row-id="{{ $row['id'] }}" 
                data-dataset-id="{{ strtolower($row['dataset_id'] ?? '') }}"
                data-title="{{ strtolower($row['title'] ?? '') }}"
                data-rest-url="{{ strtolower($row['rest_url'] ?? '') }}"
                data-updated-at="{{ $row['updated_at'] ?? '' }}"
                data-inserted-at="{{ $row['inserted_at'] ?? '' }}">
              <td class="fw-bold">{{ $row['id'] }}</td>
              <td><code class="small">{{ $row['dataset_id'] ?? '—' }}</code></td>
              <td style="max-width:420px;overflow:hidden;text-overflow:ellipsis;white-space:nowrap" title="{{ $row['title'] ?? '—' }}">
                {{ $row['title'] ?? '—' }}
              </td>
              <td>
                @if(!empty($row['rest_url']))
                  <a href="{{ $row['rest_url'] }}" target="_blank" rel="noopener" class="btn btn-sm btn-outline-primary">
                    <i class="bi bi-box-arrow-up-right"></i> Открыть
                  </a>
                @else 
                  <span class="text-muted">—</span>
                @endif
              </td>
              <td class="small" data-date="{{ $row['updated_at'] ?? '' }}">{{ $row['updated_at'] ?? '—' }}</td>
              <td class="small" data-date="{{ $row['inserted_at'] ?? '' }}">{{ $row['inserted_at'] ?? '—' }}</td>
              <td>
                <button class="btn btn-sm btn-outline-info" data-bs-toggle="collapse" data-bs-target="#raw-{{ $row['id'] }}-{{ md5($row['dataset_id'] ?? (string)$row['id']) }}" aria-expanded="false">
                  <i class="bi bi-code-square"></i> JSON
                </button>
              </td>
            </tr>
            <tr class="collapse" id="raw-{{ $row['id'] }}-{{ md5($row['dataset_id'] ?? (string)$row['id']) }}">
              <td colspan="7" class="bg-light">
                <div class="p-3">
                  <h6 class="mb-2">Raw JSON Data:</h6>
                  <pre class="mb-0 bg-white p-3 rounded border" style="max-height:300px;overflow:auto;font-size:0.85rem;">{{ json_encode($row['raw'] ?? [], JSON_PRETTY_PRINT|JSON_UNESCAPED_SLASHES|JSON_UNESCAPED_UNICODE) }}</pre>
                </div>
              </td>
            </tr>
          @empty
            <tr>
              <td colspan="7" class="text-center text-muted py-5">
                <div class="py-4">
                  <i class="bi bi-inbox" style="font-size: 3rem; opacity: 0.3;"></i>
                  <p class="mt-3 mb-0">Нет данных для отображения</p>
                </div>
              </td>
            </tr>
          @endforelse
          </tbody>
        </table>
      </div>
    </div>
  </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
  const tableBody = document.getElementById('tableBody');
  const searchInput = document.getElementById('searchInput');
  const dateFrom = document.getElementById('dateFrom');
  const dateTo = document.getElementById('dateTo');
  const sortColumn = document.getElementById('sortColumn');
  const sortOrder = document.getElementById('sortOrder');
  const resetBtn = document.getElementById('resetFilters');
  const resultsCount = document.getElementById('resultsCount');
  const sortableHeaders = document.querySelectorAll('.sortable');
  
  // Сохраняем все строки данных
  let allDataRows = Array.from(tableBody.querySelectorAll('tr[data-row-id]'));
  
  // Сохраняем связанные collapse-строки (они находятся в tbody после основных строк)
  const allTableRows = Array.from(tableBody.querySelectorAll('tr'));
  const collapseRowsMap = new Map();
  
  allDataRows.forEach((row, index) => {
    const rowId = row.getAttribute('data-row-id');
    // Ищем следующую строку после текущей
    let nextIndex = index + 1;
    while (nextIndex < allTableRows.length) {
      const nextRow = allTableRows[nextIndex];
      if (nextRow.classList.contains('collapse')) {
        // Проверяем, что это правильная collapse-строка для этой строки
        const button = row.querySelector('button[data-bs-toggle="collapse"]');
        if (button) {
          const targetId = button.getAttribute('data-bs-target')?.replace('#', '');
          if (targetId && nextRow.id === targetId) {
            collapseRowsMap.set(rowId, nextRow.cloneNode(true));
            break;
          }
        }
      } else if (nextRow.hasAttribute('data-row-id')) {
        // Дошли до следующей строки данных
        break;
      }
      nextIndex++;
    }
  });
  
  let allRows = allDataRows;
  let currentSortColumn = 'updated_at';
  let currentSortOrder = 'desc';
  
  // Инициализация: установить текущую сортировку
  sortColumn.value = currentSortColumn;
  sortOrder.value = currentSortOrder;
  updateSortIndicators();
  
  // Обработчики событий
  searchInput.addEventListener('input', applyFilters);
  dateFrom.addEventListener('change', applyFilters);
  dateTo.addEventListener('change', applyFilters);
  sortColumn.addEventListener('change', function() {
    currentSortColumn = this.value;
    applyFilters();
  });
  sortOrder.addEventListener('change', function() {
    currentSortOrder = this.value;
    applyFilters();
  });
  
  resetBtn.addEventListener('click', function() {
    searchInput.value = '';
    dateFrom.value = '';
    dateTo.value = '';
    sortColumn.value = 'updated_at';
    sortOrder.value = 'desc';
    currentSortColumn = 'updated_at';
    currentSortOrder = 'desc';
    applyFilters();
  });
  
  // Клик по заголовку для сортировки
  sortableHeaders.forEach(header => {
    header.style.cursor = 'pointer';
    header.addEventListener('click', function() {
      const column = this.dataset.column;
      if (currentSortColumn === column) {
        // Переключить порядок сортировки
        currentSortOrder = currentSortOrder === 'asc' ? 'desc' : 'asc';
        sortOrder.value = currentSortOrder;
      } else {
        currentSortColumn = column;
        sortColumn.value = column;
      }
      applyFilters();
    });
  });
  
  function applyFilters() {
    const searchTerm = searchInput.value.toLowerCase().trim();
    const fromDate = dateFrom.value;
    const toDate = dateTo.value;
    
    // Фильтрация
    let filtered = allRows.filter(row => {
      // Поиск по ключевым словам
      if (searchTerm) {
        const datasetId = (row.getAttribute('data-dataset-id') || '').toLowerCase();
        const title = (row.getAttribute('data-title') || '').toLowerCase();
        const restUrl = (row.getAttribute('data-rest-url') || '').toLowerCase();
        const rowId = (row.getAttribute('data-row-id') || '').toLowerCase();
        const searchable = (rowId + ' ' + datasetId + ' ' + title + ' ' + restUrl);
        if (!searchable.includes(searchTerm)) {
          return false;
        }
      }
      
      // Фильтрация по дате обновления
      if (fromDate || toDate) {
        let rowDate = null;
        const updatedAt = row.getAttribute('data-updated-at') || '';
        const insertedAt = row.getAttribute('data-inserted-at') || '';
        
        // Приоритет дате обновления, если нет - используем дату добавления
        const dateStr = updatedAt || insertedAt;
        if (dateStr) {
          // Парсим дату (может быть в формате "2024-01-01" или "2024-01-01 12:00:00")
          const datePart = dateStr.split(' ')[0].split('T')[0];
          rowDate = new Date(datePart);
          if (isNaN(rowDate.getTime())) {
            rowDate = null;
          }
        }
        
        if (rowDate) {
          if (fromDate) {
            const from = new Date(fromDate);
            from.setHours(0, 0, 0, 0);
            if (rowDate < from) return false;
          }
          if (toDate) {
            const to = new Date(toDate);
            to.setHours(23, 59, 59, 999);
            if (rowDate > to) return false;
          }
        } else {
          // Если нет валидной даты, исключаем из результатов при активной фильтрации по дате
          if (fromDate || toDate) return false;
        }
      }
      
      return true;
    });
    
    // Сортировка
    if (currentSortColumn) {
      filtered.sort((a, b) => {
        let aVal, bVal;
        
        switch(currentSortColumn) {
          case 'id':
            aVal = parseInt(a.getAttribute('data-row-id')) || 0;
            bVal = parseInt(b.getAttribute('data-row-id')) || 0;
            break;
          case 'dataset_id':
            aVal = (a.getAttribute('data-dataset-id') || '').toLowerCase();
            bVal = (b.getAttribute('data-dataset-id') || '').toLowerCase();
            break;
          case 'title':
            aVal = (a.getAttribute('data-title') || '').toLowerCase();
            bVal = (b.getAttribute('data-title') || '').toLowerCase();
            break;
          case 'updated_at':
            const aUpdated = a.getAttribute('data-updated-at') || '';
            const bUpdated = b.getAttribute('data-updated-at') || '';
            aVal = aUpdated || a.getAttribute('data-inserted-at') || '';
            bVal = bUpdated || b.getAttribute('data-inserted-at') || '';
            break;
          case 'inserted_at':
            aVal = a.getAttribute('data-inserted-at') || '';
            bVal = b.getAttribute('data-inserted-at') || '';
            break;
          default:
            return 0;
        }
        
        // Для дат преобразуем в timestamp
        if (currentSortColumn === 'updated_at' || currentSortColumn === 'inserted_at') {
          const parseDate = (dateStr) => {
            if (!dateStr) return 0;
            const datePart = dateStr.split(' ')[0].split('T')[0];
            const date = new Date(datePart);
            return isNaN(date.getTime()) ? 0 : date.getTime();
          };
          aVal = parseDate(aVal);
          bVal = parseDate(bVal);
        }
        
        // Сравнение
        let comparison = 0;
        if (aVal < bVal) comparison = -1;
        else if (aVal > bVal) comparison = 1;
        
        return currentSortOrder === 'asc' ? comparison : -comparison;
      });
    }
    
    // Обновление таблицы
    // Очищаем tbody
    tableBody.innerHTML = '';
    
    if (filtered.length === 0) {
      tableBody.innerHTML = `
        <tr>
          <td colspan="7" class="text-center text-muted py-5">
            <div class="py-4">
              <i class="bi bi-search" style="font-size: 3rem; opacity: 0.3;"></i>
              <p class="mt-3 mb-0">Ничего не найдено</p>
            </div>
          </td>
        </tr>
      `;
    } else {
      filtered.forEach(row => {
        const rowId = row.getAttribute('data-row-id');
        tableBody.appendChild(row);
        
        // Добавляем связанную collapse-строку, если она есть
        if (collapseRowsMap.has(rowId)) {
          const collapse = collapseRowsMap.get(rowId);
          tableBody.appendChild(collapse);
        }
      });
    }
    
    // Обновление индикаторов сортировки
    updateSortIndicators();
    
    // Обновление счетчика результатов
    if (filtered.length === allRows.length && !searchTerm && !fromDate && !toDate) {
      resultsCount.textContent = `Всего записей: ${filtered.length}`;
      resultsCount.className = 'badge bg-success fs-6';
    } else {
      resultsCount.textContent = `Найдено: ${filtered.length} из ${allRows.length}`;
      resultsCount.className = 'badge bg-primary fs-6';
    }
  }
  
  function updateSortIndicators() {
    sortableHeaders.forEach(header => {
      const indicator = header.querySelector('.sort-indicator');
      const column = header.dataset.column;
      
      if (column === currentSortColumn) {
        indicator.textContent = currentSortOrder === 'asc' ? ' ↑' : ' ↓';
        indicator.style.opacity = '1';
        header.style.backgroundColor = 'rgba(255,255,255,0.1)';
      } else {
        indicator.textContent = '';
        header.style.backgroundColor = '';
      }
    });
  }
  
  // Инициализация: применить фильтры при загрузке
  applyFilters();
  
  // Инициализация счетчика при загрузке
  if (allRows.length > 0) {
    resultsCount.textContent = `Всего записей: ${allRows.length}`;
    resultsCount.className = 'badge bg-success fs-6';
  }
});
</script>

<style>
.bg-gradient-primary {
  background: linear-gradient(135deg, #007bff 0%, #0056b3 100%);
}

.table code {
  background: #f8f9fa;
  padding: 2px 6px;
  border-radius: 4px;
  font-size: 0.875rem;
}

.collapse {
  transition: all 0.3s ease;
}

.sortable {
  user-select: none;
  position: relative;
  transition: background-color 0.2s ease;
}

.sortable:hover {
  background-color: rgba(255,255,255,0.15) !important;
}

.sort-indicator {
  opacity: 0;
  transition: opacity 0.2s ease;
  font-weight: bold;
  margin-left: 5px;
}

.form-label {
  font-size: 0.875rem;
  margin-bottom: 0.5rem;
}

#resultsCount {
  font-weight: 500;
  color: #495057;
}

.card-body .row {
  animation: fadeIn 0.3s ease-out;
}
</style>
@endsection
