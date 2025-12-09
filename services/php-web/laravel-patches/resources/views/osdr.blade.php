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

  <div class="card shadow-sm">
    <div class="card-header bg-gradient-primary text-white">
      <h5 class="card-title m-0">📋 Список наборов данных</h5>
    </div>
    <div class="card-body p-0">
      <div class="table-responsive">
        <table class="table table-hover align-middle mb-0">
          <thead>
            <tr>
              <th>#</th>
              <th>Dataset ID</th>
              <th>Название</th>
              <th>REST URL</th>
              <th>Обновлено</th>
              <th>Добавлено</th>
              <th>Действия</th>
            </tr>
          </thead>
          <tbody>
          @forelse($items as $row)
            <tr>
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
              <td class="small">{{ $row['updated_at'] ?? '—' }}</td>
              <td class="small">{{ $row['inserted_at'] ?? '—' }}</td>
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
</style>
@endsection
