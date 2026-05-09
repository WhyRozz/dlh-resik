@extends('layouts.admin')

@section('title', 'RESIK - Data Pengguna')

@push('styles')
<link rel="stylesheet" href="{{ asset('css/data-pengguna.css') }}">
@endpush

@section('content')

<!-- Search Box - PALING ATAS, POJOK KANAN -->
<div class="search-top-row">
    <div class="search-wrapper">
        <form method="GET" action="{{ route('admin.data-pengguna.index') }}" class="search-form">
            <input type="hidden" name="filter" value="{{ $filter }}">
            <div class="search-input-wrapper">
                <i class="fas fa-search search-icon"></i>
                <input 
                    type="text" 
                    name="search" 
                    class="search-input" 
                    placeholder="Cari berdasarkan nama..." 
                    value="{{ request('search') }}"
                >
                @if(request('search'))
                    <a href="{{ route('admin.data-pengguna.index', ['filter' => $filter]) }}" class="search-clear">
                        <i class="fas fa-times"></i>
                    </a>
                @endif
            </div>
        </form>
    </div>
</div>

<!-- Row 1: Judul & Export Excel -->
<div class="page-header">
    <div>
        <h2>Daftar Data Pengguna</h2>
        <p class="text-muted">Kelola data pengguna ASN dan Masyarakat</p>
    </div>
    <div class="header-actions">
    <a href="{{ route('admin.data-pengguna.export', ['filter' => $filter]) }}" class="btn-export">
        <img src="{{ asset('assets/icons/excel.png') }}" alt="Export Excel" class="btn-icon">
        Export Excel
    </a>
    </div>
</div>

<!-- Row 2: Filter Buttons -->
<div class="filter-group">
    <a href="{{ route('admin.data-pengguna.index', ['filter' => 'all']) }}"
        class="filter-btn {{ $filter == 'all' ? 'active' : '' }}">
        <i class="fas fa-users"></i> Semua
    </a>
    <a href="{{ route('admin.data-pengguna.index', ['filter' => 'asn']) }}"
        class="filter-btn {{ $filter == 'asn' ? 'active' : '' }}">
        <i class="fas fa-building"></i> ASN / PNS
    </a>
    <a href="{{ route('admin.data-pengguna.index', ['filter' => 'masyarakat']) }}"
        class="filter-btn {{ $filter == 'masyarakat' ? 'active' : '' }}">
        <i class="fas fa-user-friends"></i> Masyarakat
    </a>
</div>

<!-- Tabel Data -->
<div class="table-container">
    <table>
        <thead>
            <tr>
                <th>No</th>
                <th>Nama Pengguna</th>
                <th>Email</th>
                <th>Jenis Kelamin</th>
                <th>No Telp</th>
                <th>Pekerjaan</th>
                <th>Saldo</th>
                <th>Aksi</th>
            </tr>
        </thead>
        <tbody>
            @forelse($users as $key => $user)
            <tr>
                <td>{{ $users->firstItem() + $key }}</td>
                <td>{{ $user->nama }}</td>
                <td>{{ $user->email }}</td>
                <td>
                    @if($user->jenis_kelamin)
                    <span class="badge {{ $user->jenis_kelamin == 'Laki-laki' ? 'badge-blue' : 'badge-pink' }}">
                        {{ $user->jenis_kelamin }}
                    </span>
                    @else
                    <span class="text-muted">-</span>
                    @endif
                </td>
                <td>{{ $user->no_telepon ?? '-' }}</td>
                <td>
                    @if($user->jenis_pengguna === 'PNS')
                    <span class="badge badge-asn">
                        {{ $user->nama_dinas ?? 'ASN/PNS' }}
                    </span>
                    @else
                    <span class="badge badge-masyarakat">
                        Masyarakat
                    </span>
                    @endif
                </td>
                <td><strong>Rp {{ number_format($user->saldo, 0, ',', '.') }}</strong></td>
                <td>
                    @if($user->jenis_pengguna === 'PNS')
                    <button class="btn-icon" onclick="openModal({{ $user->id }}, 'pns')">
                        <i class="fas fa-eye"></i> Detail
                    </button>
                    @else
                    <button class="btn-icon" onclick="openModal({{ $user->id }}, 'masyarakat')">
                        <i class="fas fa-eye"></i> Detail
                    </button>
                    @endif
                </td>
            </tr>
            @empty
            <tr>
                <td colspan="8" class="text-center py-4">
                    Tidak ada data pengguna untuk kategori ini.
                </td>
            </tr>
            @endforelse
        </tbody>
    </table>

    <div class="pagination-wrapper">
        {{ $users->appends(['filter' => $filter])->links('pagination.custom') }}
    </div>
</div>

<!-- Modal Detail -->
<div id="userModal" class="modal-overlay">
    <div class="modal-card">
        <div class="modal-header">
            <h3>Detail Pengguna</h3>
            <button class="close-btn" onclick="closeModal()">&times;</button>
        </div>
        <div class="modal-body">
            <div class="user-grid">
                <div class="info-item full-width">
                    <label>Nama Lengkap</label>
                    <span id="modalNama">-</span>
                </div>
                <div class="info-item">
                    <label>Jenis Kelamin</label>
                    <span id="modalJenisKelamin">-</span>
                </div>
                <div class="info-item">
                    <label>Email</label>
                    <span id="modalEmail">-</span>
                </div>
                <div class="info-item">
                    <label>No Telepon</label>
                    <span id="modalTelp">-</span>
                </div>
                <div class="info-item">
                    <label>Tanggal Lahir</label>
                    <span id="modalTglLahir">-</span>
                </div>
                <div class="info-item">
                    <label>Pekerjaan/Dinas</label>
                    <span id="modalPekerjaan">-</span>
                </div>
                <div class="info-item">
                    <label>Kode Anggota</label>
                    <span id="modalKodeAnggota">-</span>
                </div>
                <div class="info-item">
                <label>Barcode ID</label>
                <span id="modalBarcodeId">-</span>
                </div>
                <div class="info-item">
                    <label>Saldo Bank Sampah</label>
                    <span id="modalSaldo" style="color: #2e8b57; font-weight: bold;">-</span>
                </div>
                <div class="info-item full-width">
                    <label>Terdaftar Sejak</label>
                    <span id="modalCreated">-</span>
                </div>
            </div>
        </div>
        <div class="modal-footer">
            <button class="btn-primary" onclick="closeModal()">Tutup</button>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script src="{{ asset('js/data-pengguna.js') }}"></script>
@push('scripts')
<script src="{{ asset('js/data-pengguna.js') }}"></script>

{{-- ✅ LIVE SEARCH JAVASCRIPT --}}
<script>
(function() {
    const searchInput = document.querySelector('.search-input');
    const tableContainer = document.querySelector('.table-container');
    let timeout = null;

    if (!searchInput) return;

    searchInput.addEventListener('input', function(e) {
        const searchValue = e.target.value.trim();
        const currentFilter = document.querySelector('input[name="filter"]')?.value || 'all';
        
        // Clear timeout sebelumnya
        clearTimeout(timeout);
        
        // Tunggu 500ms setelah user berhenti mengetik
        timeout = setTimeout(function() {
            performSearch(searchValue, currentFilter);
        }, 500);
    });

    function performSearch(search, filter) {
        // Tampilkan loading
        const originalContent = tableContainer.innerHTML;
        tableContainer.innerHTML = `
            <div style="text-align: center; padding: 40px;">
                <i class="fas fa-spinner fa-spin" style="font-size: 32px; color: #2e8b57;"></i>
                <p style="margin-top: 15px; color: #666;">Mencari...</p>
            </div>
        `;

        // Fetch data via AJAX
        const url = `{{ route('admin.data-pengguna.index') }}?search=${encodeURIComponent(search)}&filter=${filter}`;
        
        fetch(url, {
            headers: {
                'X-Requested-With': 'XMLHttpRequest',
                'Accept': 'text/html'
            }
        })
        .then(response => response.text())
        .then(html => {
            // Parse HTML response
            const parser = new DOMParser();
            const doc = parser.parseFromString(html, 'text/html');
            const newTableContainer = doc.querySelector('.table-container');
            
            if (newTableContainer) {
                tableContainer.innerHTML = newTableContainer.innerHTML;
            } else {
                tableContainer.innerHTML = originalContent;
            }
        })
        .catch(error => {
            console.error('Search error:', error);
            // Kembalikan konten asli jika error
            tableContainer.innerHTML = originalContent;
        });
    }
})();
</script>
@endpush
