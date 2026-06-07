@extends('layouts.admin')

@section('title', 'Bank Sampah - Data Setor')

@push('styles')
    {{-- FUNGSI: Memuat file CSS khusus untuk halaman data setor --}}
    <link rel="stylesheet" href="{{ asset('css/setor.css?v=' . time()) }}">
@endpush

@section('content')

    {{-- ✅ WRAPPER UTAMA (Fix Space Atas & Compact Layout) --}}
    <div class="page-container">

        {{-- ✅ HEADER WRAPPER: Judul + Filter + Search dalam 1 baris --}}
        <div class="header-wrapper">

            {{-- 1. Judul Halaman --}}
            <h1 class="page-title">Data Setor Sampah</h1>

            {{-- 2. Filter Section (Compact Inline) --}}
            <div class="filter-section">
                <form id="filterForm" method="GET" action="{{ route('admin.bank-sampah.setor.index') }}">
                    <div class="filter-group">
                        <select name="bulan" class="filter-select">
                            <option value="">Semua Bulan</option>
                            @for ($i = 1; $i <= 12; $i++)
                                <option value="{{ $i }}" {{ request('bulan') == $i ? 'selected' : '' }}>
                                    {{ date('F', mktime(0, 0, 0, $i, 1)) }}
                                </option>
                            @endfor
                        </select>

                        {{-- ✅ KODE BARU - PAKAI INI --}}
                        <select name="tahun" class="filter-select">
                            <option value="">Semua Tahun</option>
                            @foreach ($tahunList as $tahun)
                                <option value="{{ $tahun }}" {{ request('tahun') == $tahun ? 'selected' : '' }}>
                                    {{ $tahun }}
                                </option>
                            @endforeach
                        </select>

                        <button type="submit" class="btn-filter">
                            <i class="fas fa-filter"></i> Filter
                        </button>

                        {{-- ✅ Reset Button - Selalu ada di DOM, dikontrol via JS --}}
                        <button type="button" class="btn-filter reset" id="resetButton" style="display: none;"
                            onclick="window.location.href='{{ route('admin.bank-sampah.setor.index') }}'">
                            <i class="fas fa-undo"></i> Reset
                        </button>


                        {{-- ✅ FILTER WILAYAH (KECAMATAN & DESA) --}}
<div class="filter-wilayah-inline">
    <select name="kecamatan_id" id="filterKecamatan" class="filter-select">
        <option value="">Semua Kecamatan</option>
        @foreach($kecamatans as $kec)
            <option value="{{ $kec->id_kecamatan }}" {{ request('kecamatan_id') == $kec->id_kecamatan ? 'selected' : '' }}>
                {{ $kec->nama_kecamatan }}
            </option>
        @endforeach
    </select>
    
    <select name="desa_id" id="filterDesa" class="filter-select" {{ !request('kecamatan_id') ? 'disabled' : '' }}>
        <option value="">Semua Desa</option>
        @if(request('kecamatan_id'))
            @foreach($desas as $desa)
                <option value="{{ $desa->id_desa }}" {{ request('desa_id') == $desa->id_desa ? 'selected' : '' }}>
                    {{ $desa->nama_desa }}
                </option>
            @endforeach
        @endif
    </select>
    
    <button type="button" id="btnFilterWilayah" class="btn-filter">
        <i class="fas fa-filter"></i> Filter
    </button>
    
    <button type="button" id="btnResetWilayah" class="btn-filter reset">
        <i class="fas fa-redo"></i> Reset
    </button>
</div>
                        
                    </div>
                </form>
            </div>

            {{-- 3. Search Box (Compact Inline) --}}
            <div class="top-search">
                <div class="search-wrapper">
                    <i class="fas fa-search search-icon"></i>
                    <input type="text" id="liveSearchInput" class="search-input"
                        placeholder="Cari nama atau jenis sampah..." value="{{ request('search') }}">
                    <button type="button" id="clearSearch"
                        style="display: none; background:none; border:none; color:#888; cursor:pointer; padding:0 5px;">
                        <i class="fas fa-times"></i>
                    </button>
                </div>
            </div>
        </div>

        {{-- ✅ GREEN DIVIDER --}}
        <div class="green-divider"></div>

        {{-- ✅ TABLE CONTAINER --}}
        <div class="table-container">
            <table class="data-table">
                <thead>
                    <tr>
                        <th width="5%">No</th>
                        <th width="22%">Nama Pengguna</th>
                        <th width="15%">Waktu Setor</th>
                        <th width="13%">Pekerjaan</th>
                        <th width="15%">Jenis</th>
                        <th width="10%">Berat</th>
                        <th width="15%">Harga</th>
                        <th width="10%">Petugas</th>
                        <th width="10%">Aksi</th>
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
                                    <i class="fas fa-eye"></i>
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

            {{-- ✅ PAGINATION --}}
            @if ($setorData->hasPages())
                <div class="pagination-container">
                    <div>
                        Menampilkan {{ $setorData->firstItem() }} - {{ $setorData->lastItem() }} dari
                        {{ $setorData->total() }} data
                    </div>
                    {{ $setorData->appends(request()->query())->links() }}
                </div>
            @endif
        </div>

    </div> {{-- ✅ TUTUP .page-container --}}

    {{-- ✅ MODAL DETAIL (Tetap sama, tidak diubah) --}}
    @include('admin.bank-sampah.setor-sampah._detail-modal')

    {{-- 🔗 BRIDGE: Pass dynamic routes ke file JS eksternal --}}
    <script>
        window.SetorConfig = {
            routes: {
                index: "{{ route('admin.bank-sampah.setor.index') }}"
            }
        };
    </script>

@push('scripts')
    {{-- FUNGSI: Memuat file JS eksternal yang berisi semua fungsi interaksi halaman --}}
    <script src="{{ asset('js/setor.js?v=' . time()) }}"></script>

    <script>
// Cascading dropdown: Kecamatan → Desa
document.getElementById('filterKecamatan').addEventListener('change', function() {
    const kecId = this.value;
    const desaSelect = document.getElementById('filterDesa');
    
    // Reset desa dropdown
    desaSelect.innerHTML = '<option value="">Semua Desa</option>';
    desaSelect.disabled = !kecId;
    
    if (kecId) {
        // Fetch desa dari API
        fetch(`/admin/data-pengguna/desa/${kecId}`)
            .then(res => res.json())
            .then(data => {
                data.forEach(d => {
                    const option = document.createElement('option');
                    option.value = d.id_desa;
                    option.textContent = d.nama_desa;
                    desaSelect.appendChild(option);
                });
            })
            .catch(err => console.error('Error fetching desa:', err));
    }
});

// Tombol Filter Wilayah
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

// Tombol Reset Wilayah
document.getElementById('btnResetWilayah').addEventListener('click', function() {
    const url = new URL(window.location.href);
    url.searchParams.delete('kecamatan_id');
    url.searchParams.delete('desa_id');
    window.location.href = url.toString();
});
</script>
@endpush
@endsection