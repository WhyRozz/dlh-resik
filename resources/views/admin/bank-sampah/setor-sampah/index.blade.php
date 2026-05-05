@extends('layouts.admin')

@section('title', 'Data Setor - Bank Sampah')

@push('styles')
<link rel="stylesheet" href="{{ asset('css/setor.css') }}">
@endpush

@section('content')
{{-- Header dengan Search di Pojok Kanan --}}
<div class="top-header">
    <div class="header-title">
        <h2>Data Setor Sampah</h2>
    </div>

    {{-- Filter Box: Bulan & Tahun --}}
<div class="filter-box">
    <form id="filterForm" method="GET" action="{{ route('admin.bank-sampah.setor.index') }}">
        <div class="filter-row">
            <div class="filter-item">
                <select name="bulan" id="filterBulan">
                    <option value="">Semua Bulan</option>
                    @for($i = 1; $i <= 12; $i++)
                        <option value="{{ $i }}" {{ request('bulan') == $i ? 'selected' : '' }}>
                            {{ date('F', mktime(0, 0, 0, $i, 1)) }}
                        </option>
                    @endfor
                </select>
            </div>
            
            <div class="filter-item">
                <select name="tahun" id="filterTahun">
                    <option value="">Semua Tahun</option>
                    @for($y = date('Y'); $y >= date('Y') - 5; $y--)
                        <option value="{{ $y }}" {{ request('tahun') == $y ? 'selected' : '' }}>
                            {{ $y }}
                        </option>
                    @endfor
                </select>
            </div>
            
            <div class="filter-item" style="flex: 0; min-width: auto;">
                <button type="submit" class="filter-btn">
                    <i class="fas fa-filter"></i> Filter
                </button>
            </div>
            
            @if(request('bulan') || request('tahun') || request('search'))
            <div class="filter-item" style="flex: 0; min-width: auto;">
                <a href="{{ route('admin.bank-sampah.setor.index') }}" class="filter-btn reset">
                    <i class="fas fa-undo"></i> Reset
                </a>
            </div>
            @endif
        </div>
    </form>
</div>


    <div class="header-search">
        <div class="search-wrapper">
            <input type="text" id="liveSearchInput" placeholder="Cari nama atau jenis sampah..." value="{{ request('search') }}">
            <button type="button" id="clearSearch" style="display: none;"><i class="fas fa-times"></i></button>
        </div>
    </div> 
</div>

{{-- Data Table Container --}}
<div class="data-table-container">
    <table class="data-table">
        <thead>
            <tr>
                <th width="5%">No</th>
                <th width="22%">Nama</th>
                <th width="15%">Waktu Setor</th>
                <th width="13%">Pekerjaan</th>
                <th width="15%">Jenis</th>
                <th width="10%">Berat</th>
                <th width="15%">Harga</th>
                <th width="10%">Petugas</th>
            </tr>
        </thead>
        <tbody>
            @forelse($setorData as $index => $row)
            <tr>
                <td>{{ $setorData->firstItem() + $index }}</td>
                <td>
                    <div class="user-info">
                        <strong>{{ $row->nama_pengsetor }}</strong>
                    </div>
                </td>
                <td>{{ $row->tanggal_transaksi->format('d/m/Y H:i') }}</td>
                <td><span class="badge badge-info">{{ $row->tipe_pengsetor }}</span></td>
                <td><span class="badge badge-success">{{ $row->jenisSampah->jenis ?? 'N/A' }}</span></td>
                <td><strong>{{ number_format($row->berat, 2) }} Kg</strong></td>
                <td>
                    <div class="text-price">
                        Rp {{ number_format($row->total_rupiah, 0, ',', '.') }}
                        <small>@ Rp {{ number_format($row->harga_per_kg, 0, ',', '.') }}/kg</small>
                    </div>
                </td>
                <td>
                    <div class="user-info">
                        <strong>{{ $row->petugas->nama_lengkap ?? '-' }}</strong>
                        <small><i class="fas fa-user-check"></i> Petugas</small>
                    </div>
                </td>
                <td>
                    <button class="btn-action" onclick="openDetailModal({{ $row->id_transaksi }})">
                        <i class="fas fa-eye"></i> Detail
                    </button>
                </td>
            </tr>
            @empty
            <tr>
                <td colspan="9">
                    <div class="empty-state">
                        <i class="fas fa-inbox"></i>
                        <p>Belum ada data setor sampah</p>
                    </div>
                </td>
            </tr>
            @endforelse
        </tbody>
    </table>
    
    @if($setorData->hasPages())
    <div class="pagination-wrapper">
        {{ $setorData->appends(request()->query())->links('pagination::simple-bootstrap-4') }}
    </div>
    @endif
</div>

{{-- Modal Detail (Include dari file terpisah) --}}
@include('admin.bank-sampah.setor-sampah._detail-modal')

{{-- ✅ LIVE SEARCH JAVASCRIPT (AJAX) --}}
<script>
(function() {
    const input = document.getElementById('liveSearchInput');
    const clearBtn = document.getElementById('clearSearch');
    const tableContainer = document.querySelector('.data-table-container');
    const filterForm = document.getElementById('filterForm');
    let timer = null;

    // Live Search Input
    if (input && clearBtn) {
        if (input.value.trim() !== '') clearBtn.style.display = 'inline-block';

        input.addEventListener('input', function() {
            const val = this.value.trim();
            clearBtn.style.display = val ? 'inline-block' : 'none';
            
            clearTimeout(timer);
            timer = setTimeout(() => {
                if (val.length >= 2 || val === '') fetchSearch(val);
            }, 350);
        });

        clearBtn.addEventListener('click', () => {
            input.value = '';
            clearBtn.style.display = 'none';
            fetchSearch('');
            input.focus();
        });
    }

    // Filter Form Submit (AJAX)
    if (filterForm) {
        filterForm.addEventListener('submit', function(e) {
            e.preventDefault();
            const formData = new FormData(this);
            const searchQuery = input?.value.trim() || '';
            if (searchQuery) formData.set('search', searchQuery);
            
            const queryString = new URLSearchParams(formData).toString();
            fetchSearchAjax(queryString);
        });
    }

    function fetchSearch(query) {
        if (!tableContainer) return;
        tableContainer.innerHTML = `<div style="text-align:center;padding:40px;"><i class="fas fa-spinner fa-spin fa-2x"></i><p style="margin-top:15px;color:#666;">Mencari...</p></div>`;

        fetch(`{{ route('admin.bank-sampah.setor.index') }}?search=${encodeURIComponent(query)}`, {
            headers: {
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.content || '',
                'X-Requested-With': 'XMLHttpRequest',
                'Accept': 'application/json'
            }
        })
        .then(res => res.json())
        .then(data => updateTable(data.table))
        .catch(err => {
            console.error('Search error:', err);
            window.location.href = `{{ route('admin.bank-sampah.setor.index') }}?search=${encodeURIComponent(query)}`;
        });
    }

    function fetchSearchAjax(queryString) {
        if (!tableContainer) return;
        tableContainer.innerHTML = `<div style="text-align:center;padding:40px;"><i class="fas fa-spinner fa-spin fa-2x"></i><p style="margin-top:15px;color:#666;">Memfilter...</p></div>`;

        fetch(`{{ route('admin.bank-sampah.setor.index') }}?${queryString}`, {
            headers: {
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.content || '',
                'X-Requested-With': 'XMLHttpRequest',
                'Accept': 'application/json'
            }
        })
        .then(res => res.json())
        .then(data => updateTable(data.table))
        .catch(err => {
            console.error('Filter error:', err);
            window.location.href = `{{ route('admin.bank-sampah.setor.index') }}?${queryString}`;
        });
    }

    function updateTable(html) {
        if (html) {
            const parser = new DOMParser();
            const doc = parser.parseFromString(html, 'text/html');
            const newContainer = doc.querySelector('.data-table-container');
            if (newContainer && tableContainer) {
                tableContainer.outerHTML = newContainer.outerHTML;
            }
        }
    }
})();
</script>
@endsection