@extends('layouts.admin')

<!-- Fungsi: Menetapkan judul halaman untuk halaman data pengguna -->
@section('title', 'Data Pengguna - RESIK')

@push('styles')
<!-- Fungsi: Memuat file CSS khusus untuk halaman data pengguna -->
<link rel="stylesheet" href="{{ asset('css/data-pengguna.css?v=' . time()) }}">
@endpush

@section('content')

<!-- Fungsi: Search box dengan form GET untuk pencarian server-side di pojok kanan atas -->
<div class="search-top-row">
    <div class="search-wrapper">
        <form method="GET" action="{{ route('admin.data-pengguna.index') }}" class="search-form">
            <!-- Fungsi: Input hidden untuk mempertahankan nilai filter saat search -->
            <input type="hidden" name="filter" value="{{ $filter }}">
            <div class="search-input-wrapper">
                <i class="fas fa-search search-icon"></i>
                <!-- Fungsi: Input pencarian nama pengguna -->
                <input 
                    type="text" 
                    name="search" 
                    class="search-input" 
                    placeholder="Cari berdasarkan nama..." 
                    value="{{ request('search') }}"
                >
                <!-- Fungsi: Tombol clear search yang hanya tampil ketika ada keyword -->
                @if(request('search'))
                    <a href="{{ route('admin.data-pengguna.index', ['filter' => $filter]) }}" class="search-clear">
                        <i class="fas fa-times"></i>
                    </a>
                @endif
            </div>
        </form>
    </div>
</div>

<!-- Fungsi: Header halaman dengan judul dan tombol export Excel -->
<div class="page-header">
    <div>
        <h2>Daftar Data Pengguna</h2>
        <p class="text-muted">Kelola data pengguna ASN dan Masyarakat</p>
    </div>
    <div class="header-actions">
    <!-- Fungsi: Tombol export data pengguna ke format Excel -->
    <a href="{{ route('admin.data-pengguna.export', ['filter' => $filter]) }}" class="btn-export">
        <img src="{{ asset('assets/icons/excel.png') }}" alt="Export Excel" class="btn-icon">
        Export Excel
    </a>
    </div>
</div>

<!-- Fungsi: Group tombol filter untuk switching kategori pengguna + Filter Wilayah -->
<div class="filter-group">
    <!-- Fungsi: Filter Semua Pengguna -->
    <a href="{{ route('admin.data-pengguna.index', ['filter' => 'all']) }}"
        class="filter-btn {{ $filter == 'all' ? 'active' : '' }}">
        <i class="fas fa-users"></i> Semua
    </a>
    <!-- Fungsi: Filter Khusus ASN/PNS -->
    <a href="{{ route('admin.data-pengguna.index', ['filter' => 'asn']) }}"
        class="filter-btn {{ $filter == 'asn' ? 'active' : '' }}">
        <i class="fas fa-building"></i> ASN / PNS
    </a>
    <!-- Fungsi: Filter Khusus Masyarakat Umum -->
    <a href="{{ route('admin.data-pengguna.index', ['filter' => 'masyarakat']) }}"
        class="filter-btn {{ $filter == 'masyarakat' ? 'active' : '' }}">
        <i class="fas fa-user-friends"></i> Masyarakat
    </a>
    
    <!-- ✅ FILTER WILAYAH (SEJAJAR DI KANAN) -->
    <div class="filter-wilayah-inline">
        <select name="kecamatan_id" id="filterKecamatan" class="filter-select">
            <option value="">Semua Kecamatan</option>
            @foreach($kecamatans as $kec)
                <option value="{{ $kec->id_kecamatan }}" {{ request('kecamatan_id') == $kec->id_kecamatan ? 'selected' : '' }}>
                    {{ $kec->nama_kecamatan }}
                </option>
            @endforeach
        </select>
        
        <select name="desa_id" id="filterDesa" class="filter-select">
            <option value="">Semua Desa</option>
            @foreach($desas as $desa)
                <option value="{{ $desa->id_desa }}" {{ request('desa_id') == $desa->id_desa ? 'selected' : '' }}>
                    {{ $desa->nama_desa }}
                </option>
            @endforeach
        </select>
        
        <button type="button" id="btnFilterWilayah" class="filter-btn-action">
            <i class="fas fa-filter"></i> Filter
        </button>
        
        <button type="button" id="btnResetWilayah" class="filter-btn-reset">
            <i class="fas fa-redo"></i> Reset
        </button>
    </div>
</div>

<!-- Fungsi: Container tabel dengan pagination untuk menampilkan data pengguna -->
<div class="table-container">
    <table>
        <thead>
            <tr>
                <th>No</th>
                <th>Nama Pengguna</th>
                <th style="text-align: center;">Email</th>
                <th>Jenis Kelamin</th>
                <th style="text-align: center;">No Telp</th>
                <th style="text-align: center;">Pekerjaan</th>
                <th style="text-align: center;">Saldo</th>
                <th>Aksi</th>
            </tr>
        </thead>
        <tbody>
            <!-- Fungsi: Looping data pengguna dengan pagination -->
            @forelse($users as $key => $user)
            <tr>
                <!-- Fungsi: Nomor urut dengan perhitungan pagination -->
                <td>{{ $users->firstItem() + $key }}</td>
                <td>{{ $user->nama }}</td>
                <td>{{ $user->email }}</td>
                <td>
                    <!-- Fungsi: Badge warna berbeda untuk jenis kelamin -->
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
                    <!-- Fungsi: Badge berbeda untuk tipe pekerjaan (ASN vs Masyarakat) -->
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
                <!-- Fungsi: Format saldo dengan rupiah dan pemisah ribuan -->
                <td><strong>Rp {{ number_format($user->saldo, 0, ',', '.') }}</strong></td>
                <td>
                    <!-- Fungsi: Tombol Detail yang memanggil modal dengan ID dan tipe pengguna -->
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
            <!-- Fungsi: Pesan ketika tidak ada data dalam kategori filter yang dipilih -->
            <tr>
                <td colspan="8" class="text-center py-4">
                    Tidak ada data pengguna untuk kategori ini.
                </td>
            </tr>
            @endforelse
        </tbody>
    </table>

    <!-- Fungsi: Pagination links dengan mempertahankan parameter filter -->
    <div class="pagination-wrapper">
        {{ $users->appends([
        'filter' => $filter,
        'search' => $search,
        'kecamatan_id' => $kecamatanId,
        'desa_id' => $desaId
        ])->links('pagination.custom') }}
    </div>
</div>

<!-- Fungsi: Modal overlay untuk menampilkan detail lengkap data pengguna -->
<div id="userModal" class="modal-overlay">
    <div class="modal-card">
        <div class="modal-header">
            <h3>Detail Pengguna</h3>
            <!-- Fungsi: Tombol close modal -->
            <button class="close-btn" onclick="closeModal()">&times;</button>
        </div>
        <div class="modal-body">
            <!-- Fungsi: Grid layout untuk menampilkan field-field detail pengguna -->
            <div class="user-grid">
                <!-- Baris 1 -->
                <div class="info-item">
                    <label>Nama Lengkap</label>
                    <span id="modalNama">-</span>
                </div>
                <div class="info-item">
                    <label>Alamat</label>
                    <span id="modalAlamat">-</span>
                </div>

                <!-- Baris 2 -->
                <div class="info-item">
                    <label>Email</label>
                    <span id="modalEmail">-</span>
                </div>
                <div class="info-item">
                    <label>Tanggal Lahir</label>
                    <span id="modalTglLahir">-</span>
                </div>

                <!-- Baris 3 -->
                <div class="info-item">
                    <label>No Telepon</label>
                    <span id="modalTelp">-</span>
                </div>
                <div class="info-item">
                    <label>Kode Anggota</label>
                    <span id="modalKodeAnggota">-</span>
                </div>

                <!-- Baris 4 -->
                <div class="info-item">
                    <label>Jenis Kelamin</label>
                    <span id="modalJenisKelamin">-</span>
                </div>
                <div class="info-item">
                    <label>Barcode ID</label>
                    <span id="modalBarcodeId">-</span>
                </div>

                <!-- Baris 5 -->
                <div class="info-item">
                    <label>Pekerjaan</label>
                    <span id="modalPekerjaan">-</span>
                </div>
                <div class="info-item">
                    <label>Terdaftar Sejak</label>
                    <span id="modalCreated">-</span>
                </div>

                <!-- Baris 6 -->
                <div class="info-item">
                    <label>Kecamatan</label>
                    <span id="modalKecamatan">-</span>
                </div>
                <div class="info-item">
                    <label>Saldo Bank Sampah</label>
                    <span id="modalSaldo" style="color: #2e8b57; font-weight: bold;">-</span>
                </div>

                <!-- Baris 7 -->
                <div class="info-item">
                    <label>Desa/Kelurahan</label>
                    <span id="modalDesa">-</span>
                </div>
            </div>
        <div class="modal-footer">
            <button class="btn-primary" onclick="closeModal()">Tutup</button>
        </div>
    </div>
</div>

{{-- 🔗 BRIDGE: Pass dynamic route ke file JS eksternal --}}
<script>
    window.DataPenggunaConfig = {
        routes: {
            index: "{{ route('admin.data-pengguna.index') }}"
        }
    };
</script>

@push('scripts')
<!-- Fungsi: Memuat file JavaScript eksternal untuk handle modal dan interaksi data pengguna -->
<script src="{{ asset('js/data-pengguna.js?v=' . time()) }}"></script>

<script>
document.getElementById('filterKecamatan').addEventListener('change', function() {
    const kecId = this.value;
    const desaSelect = document.getElementById('filterDesa');
    
    desaSelect.innerHTML = '<option value="">Semua Desa</option>';
    
    if (kecId) {
        fetch(`{{ route('admin.data-pengguna.desa-by-kecamatan', ['kecamatan_id' => 'KECAMATAN_ID_PLACEHOLDER']) }}`.replace('KECAMATAN_ID_PLACEHOLDER', kecId))
            .then(res => res.json())
            .then(data => {
                desaSelect.innerHTML = '<option value="">Semua Desa</option>';
                data.forEach(d => {
                    desaSelect.innerHTML += `<option value="${d.id_desa}">${d.nama_desa}</option>`;
                });
            });
    } else {
        desaSelect.innerHTML = '<option value="">Semua Desa</option>';
    }
});

document.getElementById('btnFilterWilayah').addEventListener('click', function() {
    const kecId = document.getElementById('filterKecamatan').value;
    const desaId = document.getElementById('filterDesa').value;
    const url = new URL(window.location.href);
    
    if (kecId) url.searchParams.set('kecamatan_id', kecId);
    else url.searchParams.delete('kecamatan_id');
    
    if (desaId) url.searchParams.set('desa_id', desaId);
    else url.searchParams.delete('desa_id');
    
    window.location.href = url.toString();
});

// TAMBAHKAN INI setelah script btnFilterWilayah
document.getElementById('btnResetWilayah').addEventListener('click', function() {
    const url = new URL(window.location.href);
    url.searchParams.delete('kecamatan_id');
    url.searchParams.delete('desa_id');
    window.location.href = url.toString();
});
</script>
@endpush
@endsection