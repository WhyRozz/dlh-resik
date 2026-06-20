@extends('layouts.admin')

@section('title', 'Bank Sampah - Data Setor')

@push('styles')
    {{-- FUNGSI: Memuat file CSS khusus untuk halaman data setor --}}
    <link rel="stylesheet" href="{{ asset('css/setor.css?v=' . time()) }}">
@endpush

@section('content')

    {{-- ✅ WRAPPER UTAMA (Fix Space Atas & Compact Layout) --}}
    <div class="page-container">

        {{-- ✅ ROW 1: Judul + Filter (Bulan, Tahun, Reset) + Search --}}
        <div class="header-wrapper">
            {{-- 1. Judul Halaman --}}
            <h1 class="page-title">Data Setor Sampah</h1>

            {{-- 2. Filter Bulan, Tahun, Tombol Filter & Reset --}}
            <div class="header-filters">
                <select name="bulan" class="filter-select" onchange="this.form.submit()" form="filterForm">
                    <option value="">Semua Bulan</option>
                    @for ($i = 1; $i <= 12; $i++)
                        <option value="{{ $i }}" {{ request('bulan') == $i ? 'selected' : '' }}>
                            {{ date('F', mktime(0, 0, 0, $i, 1)) }}
                        </option>
                    @endfor
                </select>

                <select name="tahun" class="filter-select" onchange="this.form.submit()" form="filterForm">
                    <option value="">Semua Tahun</option>
                    @foreach ($tahunList as $tahun)
                        <option value="{{ $tahun }}" {{ request('tahun') == $tahun ? 'selected' : '' }}>
                            {{ $tahun }}
                        </option>
                    @endforeach
                </select>

                <button type="submit" class="btn-filter" form="filterForm">
                    <i class="fas fa-filter"></i> Filter
                </button>

                <button type="button" class="btn-filter reset" id="resetButton" style="display: none;"
                    onclick="window.location.href='{{ route('admin.bank-sampah.setor.index') }}'">
                    <i class="fas fa-undo"></i> Reset
                </button>
            </div>

            {{-- 3. Search Box --}}
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

        {{-- ✅ ROW 2: Semua Filter (Bulan, Tahun, Kecamatan, Desa) --}}
        <div class="filter-section">
            <form id="filterForm" method="GET" action="{{ route('admin.bank-sampah.setor.index') }}">
                <div class="filter-group">
                    {{-- ✅ TIPE FILTER (Wilayah atau Dinas) --}}
                    <select name="tipe_filter" id="tipeFilter" class="filter-select" onchange="toggleFilterType()">
                        <option value="wilayah"
                            {{ request('tipe_filter') === 'wilayah' || !request('tipe_filter') ? 'selected' : '' }}>
                            Berdasarkan Wilayah
                        </option>
                        <option value="dinas" {{ request('tipe_filter') === 'dinas' ? 'selected' : '' }}>
                            Berdasarkan Dinas
                        </option>
                    </select>

                    {{-- ✅ FILTER WILAYAH (Kecamatan & Desa) --}}
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

                    {{-- ✅ FILTER DINAS --}}
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

                    {{-- Tombol Filter Wilayah --}}
                    <button type="button" id="btnFilterWilayah" class="btn-filter">
                        <i class="fas fa-filter"></i> Filter
                    </button>

                    {{-- Tombol Reset Wilayah --}}
                    <button type="button" id="btnResetWilayah" class="btn-filter reset" style="display: none;">
                        <i class="fas fa-undo"></i> Reset
                    </button>
                </div>
            </form>
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

            // Tombol Filter Wilayah (handle kedua tipe filter)
            document.getElementById('btnFilterWilayah').addEventListener('click', function() {
                const tipeFilter = document.getElementById('tipeFilter').value;
                const url = new URL(window.location.href);

                url.searchParams.set('tipe_filter', tipeFilter);

                if (tipeFilter === 'wilayah') {
                    // Filter berdasarkan wilayah (kecamatan & desa)
                    const kecId = document.getElementById('filterKecamatan').value;
                    const desaId = document.getElementById('filterDesa').value;

                    if (kecId) url.searchParams.set('kecamatan_id', kecId);
                    else url.searchParams.delete('kecamatan_id');

                    if (desaId) url.searchParams.set('desa_id', desaId);
                    else url.searchParams.delete('desa_id');

                    // Hapus parameter dinas
                    url.searchParams.delete('dinas_id');
                } else if (tipeFilter === 'dinas') {
                    // Filter berdasarkan dinas
                    const dinasId = document.getElementById('filterDinasSelect').value;

                    if (dinasId) url.searchParams.set('dinas_id', dinasId);
                    else url.searchParams.delete('dinas_id');

                    // Hapus parameter wilayah
                    url.searchParams.delete('kecamatan_id');
                    url.searchParams.delete('desa_id');
                }

                window.location.href = url.toString();
            });

            // Tombol Reset Wilayah
            document.getElementById('btnResetWilayah').addEventListener('click', function() {
                const url = new URL(window.location.href);
                url.searchParams.delete('kecamatan_id');
                url.searchParams.delete('desa_id');
                url.searchParams.delete('dinas_id');
                window.location.href = url.toString();
            });

            // Toggle Filter Type (Wilayah atau Dinas)
            function toggleFilterType() {
                const tipeFilter = document.getElementById('tipeFilter').value;
                const filterWilayah = document.getElementById('filterWilayah');
                const filterDinas = document.getElementById('filterDinas');
                const btnFilterWilayah = document.getElementById('btnFilterWilayah');

                if (tipeFilter === 'dinas') {
                    // Sembunyikan filter wilayah, tampilkan filter dinas
                    filterWilayah.style.display = 'none';
                    filterDinas.style.display = 'inline-flex';

                    // Reset nilai filter wilayah
                    document.getElementById('filterKecamatan').value = '';
                    document.getElementById('filterDesa').value = '';
                    document.getElementById('filterDesa').disabled = true;
                } else {
                    // Sembunyikan filter dinas, tampilkan filter wilayah
                    filterDinas.style.display = 'none';
                    filterWilayah.style.display = 'inline-flex';
                    btnFilterWilayah.style.display = 'inline-flex';

                    // Reset nilai filter dinas
                    document.getElementById('filterDinasSelect').value = '';
                }

                toggleResetWilayah();
            }

            // Update toggleResetWilayah untuk cek dinas juga
            function toggleResetWilayah() {
                const btnResetWilayah = document.getElementById('btnResetWilayah');
                const kecId = document.getElementById('filterKecamatan')?.value || '';
                const desaId = document.getElementById('filterDesa')?.value || '';
                const dinasId = document.getElementById('filterDinasSelect')?.value || '';

                if (kecId || desaId || dinasId) {
                    btnResetWilayah.style.display = 'inline-flex';
                } else {
                    btnResetWilayah.style.display = 'none';
                }
            }

            // Panggil saat halaman load
            toggleFilterType();
            toggleResetWilayah();
        </script>
    @endpush
@endsection
