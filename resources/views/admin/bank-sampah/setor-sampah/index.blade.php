@extends('layouts.admin')

@section('title', 'Data Setor - Bank Sampah')

@push('styles')
    <link rel="stylesheet" href="{{ asset('css/setor.css') }}">
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
                        <th width="22%">Nama</th>
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

    {{-- ✅ LIVE SEARCH JAVASCRIPT (Tetap sama, tidak diubah) --}}
    <script>
        (function() {
            const input = document.getElementById('liveSearchInput');
            const clearBtn = document.getElementById('clearSearch');
            const tableContainer = document.querySelector('.table-container'); // ✅ Update selector
            const filterForm = document.getElementById('filterForm');
            let timer = null;

            // Live Search Input
            if (input && clearBtn) {
                if (input.value.trim() !== '') clearBtn.style.display = 'inline-block';

                input.addEventListener('input', function() {
                    const val = this.value.trim();
                    clearBtn.style.display = val ? 'inline-block' : 'none';

                    toggleResetButton();

                    clearTimeout(timer);
                    timer = setTimeout(() => {
                        if (val.length >= 2 || val === '') fetchSearch(val);
                    }, 350);
                });

                clearBtn.addEventListener('click', () => {
                    input.value = '';
                    clearBtn.style.display = 'none';
                    toggleResetButton();
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
                tableContainer.innerHTML =
                    `<div style="text-align:center;padding:40px;"><i class="fas fa-spinner fa-spin fa-2x"></i><p style="margin-top:15px;color:#666;">Mencari...</p></div>`;

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
                        window.location.href =
                            `{{ route('admin.bank-sampah.setor.index') }}?search=${encodeURIComponent(query)}`;
                    });
            }

            function fetchSearchAjax(queryString) {
                if (!tableContainer) return;
                tableContainer.innerHTML =
                    `<div style="text-align:center;padding:40px;"><i class="fas fa-spinner fa-spin fa-2x"></i><p style="margin-top:15px;color:#666;">Memfilter...</p></div>`;

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
                    const newContainer = doc.querySelector('.table-container'); // ✅ Update selector
                    if (newContainer && tableContainer) {
                        tableContainer.outerHTML = newContainer.outerHTML;
                    }
                    toggleResetButton();
                }
            }
        })();


        // ✅ Fungsi untuk tampilkan/sembunyikan tombol Reset berdasarkan filter aktif
        function toggleResetButton() {
            const resetBtn = document.getElementById('resetButton');
            if (!resetBtn) return;

            // Cek nilai filter
            const bulan = document.querySelector('select[name="bulan"]').value;
            const tahun = document.querySelector('select[name="tahun"]').value;
            const search = document.getElementById('liveSearchInput')?.value || '';

            // Tampilkan Reset jika ada filter aktif
            if (bulan || tahun || search.trim()) {
                resetBtn.style.display = 'inline-flex';
            } else {
                resetBtn.style.display = 'none';
            }
        }

        // ✅ Jalankan saat halaman pertama kali load
        document.addEventListener('DOMContentLoaded', toggleResetButton);
    </script>
@endsection
