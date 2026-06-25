@extends('layouts.admin')

@section('title', 'Data Pengguna - RESIK')

@push('styles')
    <link rel="stylesheet" href="{{ asset('css/data-pengguna.css?v=' . time()) }}">
@endpush

@section('content')
    {{-- Search Box --}}
    <div class="search-top-row">
        <div class="search-wrapper">
            <form method="GET" action="{{ route('admin.data-pengguna.index') }}" class="search-form">
                <input type="hidden" name="filter" value="{{ $filter }}">
                <div class="search-input-wrapper">
                    <i class="fas fa-search search-icon"></i>
                    <input type="text" name="search" class="search-input" placeholder="Cari berdasarkan nama..."
                        value="{{ request('search') }}">
                    @if (request('search'))
                        <a href="{{ route('admin.data-pengguna.index', ['filter' => $filter]) }}" class="search-clear">
                            <i class="fas fa-times"></i>
                        </a>
                    @endif
                </div>
            </form>
        </div>
    </div>

    {{-- Header --}}
    <div class="page-header">
        <div>
            <h2>Daftar Data Pengguna</h2>
            <p class="text-muted">Kelola data pengguna ASN dan Masyarakat</p>
        </div>
        <div class="header-actions">
            <a href="{{ route('admin.data-pengguna.export', [
                'filter' => $filter,
                'kecamatan_id' => $kecamatanId,
                'desa_id' => $desaId,
                'dinas_id' => request('dinas_id'),
                'search' => $search,
            ]) }}"
                class="btn-export">
                <img src="{{ asset('assets/icons/excel.png') }}" alt="Export Excel" class="btn-icon">
                Export Excel
            </a>
        </div>
    </div>

    {{-- Filter Kategori (Semua/ASN/Masyarakat) --}}
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

        {{-- Filter Wilayah & Dinas (TERPISAH) --}}
        <form method="GET" action="{{ route('admin.data-pengguna.index') }}" id="filterForm">
            <div class="filter-wilayah-container">
                {{-- Tipe Filter --}}
                <select name="tipe_filter" id="tipeFilter" class="filter-select" onchange="toggleFilterType()">
                    <option value="wilayah"
                        {{ request('tipe_filter') === 'wilayah' || !request('tipe_filter') ? 'selected' : '' }}>
                        Berdasarkan Wilayah
                    </option>
                    <option value="dinas" {{ request('tipe_filter') === 'dinas' ? 'selected' : '' }}>
                        Berdasarkan Dinas
                    </option>
                </select>

                {{-- Filter Wilayah --}}
                <div id="filterWilayah" style="{{ request('tipe_filter') === 'dinas' ? 'display:none;' : '' }}">
                    <select name="kecamatan_id" id="filterKecamatan" class="filter-select">
                        <option value="">Semua Kecamatan</option>
                        @foreach ($kecamatans as $kec)
                            <option value="{{ $kec->id_kecamatan }}"
                                {{ request('kecamatan_id') == $kec->id_kecamatan ? 'selected' : '' }}>
                                {{ $kec->nama_kecamatan }}
                            </option>
                        @endforeach
                    </select>

                    <select name="desa_id" id="filterDesa" class="filter-select"
                        {{ !request('kecamatan_id') ? 'disabled' : '' }}>
                        <option value="">Semua Desa</option>
                        @if (request('kecamatan_id'))
                            @foreach ($desas as $desa)
                                <option value="{{ $desa->id_desa }}"
                                    {{ request('desa_id') == $desa->id_desa ? 'selected' : '' }}>
                                    {{ $desa->nama_desa }}
                                </option>
                            @endforeach
                        @endif
                    </select>
                </div>

                {{-- Filter Dinas --}}
                <div id="filterDinas" style="{{ request('tipe_filter') === 'dinas' ? '' : 'display:none;' }}">
                    <select name="dinas_id" id="filterDinasSelect" class="filter-select">
                        <option value="">Semua Dinas</option>
                        @foreach ($dinasList as $dinas)
                            <option value="{{ $dinas->id_dinas }}"
                                {{ request('dinas_id') == $dinas->id_dinas ? 'selected' : '' }}>
                                {{ $dinas->nama_dinas }}
                            </option>
                        @endforeach
                    </select>
                </div>

                {{-- Tombol Filter & Reset --}}
                <button type="button" id="btnFilterWilayah" class="filter-btn-action">
                    <i class="fas fa-filter"></i> Filter
                </button>

                <button type="button" id="btnResetWilayah" class="filter-btn-reset" style="display: none;">
                    <i class="fas fa-redo"></i> Reset
                </button>
            </div>
        </form>
    </div>

    {{-- Tabel Data --}}
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
                @forelse($users as $key => $user)
                    <tr>
                        <td>{{ $users->firstItem() + $key }}</td>
                        <td>{{ $user->nama }}</td>
                        <td>{{ $user->email }}</td>
                        <td>
                            @if ($user->jenis_kelamin)
                                <span
                                    class="badge {{ $user->jenis_kelamin == 'Laki-laki' ? 'badge-blue' : 'badge-pink' }}">
                                    {{ $user->jenis_kelamin }}
                                </span>
                            @else
                                <span class="text-muted">-</span>
                            @endif
                        </td>
                        <td>{{ $user->no_telepon ?? '-' }}</td>
                        <td>
                            @if ($user->jenis_pengguna === 'PNS')
                                <span class="badge badge-asn">
                                    {{ $user->nama_dinas ?? 'ASN/PNS' }}
                                </span>
                            @else
                                <span class="badge badge-masyarakat">
                                    Masyarakat ({{ $user->nama_kecamatan ?? '-' }}, {{ $user->nama_desa ?? '-' }})
                                </span>
                            @endif
                        </td>
                        <td><strong>Rp {{ number_format($user->saldo, 0, ',', '.') }}</strong></td>
                        <td>
                            @if ($user->jenis_pengguna === 'PNS')
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

        {{-- Pagination --}}
        <div class="pagination-wrapper">
            {{ $users->appends([
                    'filter' => $filter,
                    'search' => $search,
                    'kecamatan_id' => $kecamatanId,
                    'desa_id' => $desaId,
                ])->links('pagination.custom') }}
        </div>
    </div>

    {{-- Modal Detail --}}
    <div id="userModal" class="modal-overlay">
        <div class="modal-card">
            <div class="modal-header">
                <h3>Detail Pengguna</h3>
                <button class="close-btn" onclick="closeModal()">&times;</button>
            </div>
            <div class="modal-body">
                <div class="user-grid">
                    <div class="info-item">
                        <label>Nama Lengkap</label>
                        <span id="modalNama">-</span>
                    </div>
                    <div class="info-item">
                        <label>Alamat</label>
                        <span id="modalAlamat">-</span>
                    </div>
                    <div class="info-item">
                        <label>Email</label>
                        <span id="modalEmail">-</span>
                    </div>
                    <div class="info-item">
                        <label>Tanggal Lahir</label>
                        <span id="modalTglLahir">-</span>
                    </div>
                    <div class="info-item">
                        <label>No Telepon</label>
                        <span id="modalTelp">-</span>
                    </div>
                    <div class="info-item">
                        <label>Kode Anggota</label>
                        <span id="modalKodeAnggota">-</span>
                    </div>
                    <div class="info-item">
                        <label>Jenis Kelamin</label>
                        <span id="modalJenisKelamin">-</span>
                    </div>
                    <div class="info-item">
                        <label>Barcode ID</label>
                        <span id="modalBarcodeId">-</span>
                    </div>
                    <div class="info-item">
                        <label>Pekerjaan</label>
                        <span id="modalPekerjaan">-</span>
                    </div>
                    <div class="info-item">
                        <label>Terdaftar Sejak</label>
                        <span id="modalCreated">-</span>
                    </div>
                    <div class="info-item">
                        <label>Kecamatan</label>
                        <span id="modalKecamatan">-</span>
                    </div>
                    <div class="info-item">
                        <label>Saldo Bank Sampah</label>
                        <span id="modalSaldo" style="color: #2e8b57; font-weight: bold;">-</span>
                    </div>
                    <div class="info-item">
                        <label>Desa/Kelurahan</label>
                        <span id="modalDesa">-</span>
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <button class="btn-primary" onclick="closeModal()">Tutup</button>
            </div>
        </div>
    </div>

    {{-- Bridge Config --}}
    <script>
        window.DataPenggunaConfig = {
            routes: {
                index: "{{ route('admin.data-pengguna.index') }}"
            }
        };
    </script>
@endsection

@push('scripts')
    <script src="{{ asset('js/data-pengguna.js?v=' . time()) }}"></script>
@endpush
