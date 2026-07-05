@extends('layouts.admin')

@section('title', 'Bank Sampah - Data Penarikan')

@push('styles')
    <link rel="stylesheet" href="{{ asset('css/penarikan.css?v=' . time()) }}">
@endpush

@section('content')
    <div class="page-container">
        {{-- HEADER --}}
        <div class="header-wrapper">
            <h1 class="page-title">Data Penarikan</h1>

            {{-- ✅ FORM FILTER UTAMA (Bulan, Tahun, Status) --}}
            <form method="GET" action="{{ route('admin.bank-sampah.penarikan.index') }}" id="filterFormUtama"
                class="header-filters">
                <select name="bulan" class="filter-select">
                    <option value="">Semua Bulan</option>
                    @for ($i = 1; $i <= 12; $i++)
                        <option value="{{ $i }}" {{ request('bulan') == $i ? 'selected' : '' }}>
                            {{ date('F', mktime(0, 0, 0, $i, 1)) }}
                        </option>
                    @endfor
                </select>

                <select name="tahun" class="filter-select">
                    <option value="">Semua Tahun</option>
                    @foreach ($tahunList as $tahun)
                        <option value="{{ $tahun }}" {{ request('tahun') == $tahun ? 'selected' : '' }}>
                            {{ $tahun }}
                        </option>
                    @endforeach
                </select>

                <select name="status" class="filter-select">
                    <option value="">Semua Status</option>
                    <option value="diproses" {{ request('status') == 'diproses' ? 'selected' : '' }}>Diproses</option>
                    <option value="berhasil" {{ request('status') == 'berhasil' ? 'selected' : '' }}>Berhasil</option>
                    <option value="ditolak" {{ request('status') == 'ditolak' ? 'selected' : '' }}>Ditolak</option>
                </select>

                <button type="submit" class="btn-filter">
                    <i class="fas fa-filter"></i> Filter
                </button>

                @if (request('bulan') || request('tahun') || request('status'))
                    <button type="button" class="btn-filter reset" onclick="resetFilter()">
                        <i class="fas fa-undo"></i> Reset
                    </button>
                @endif
            </form>

            {{-- Search --}}
            <div class="top-search">
                <div class="search-wrapper">
                    <i class="fas fa-search search-icon"></i>
                    <input type="text" id="searchInput" class="search-input"
                        placeholder="Cari nama, status, atau tanggal...">
                </div>
            </div>
        </div>

        {{-- Filter Wilayah --}}
        <div class="filter-section">
            <form method="GET" action="{{ route('admin.bank-sampah.penarikan.index') }}" id="filterForm">
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
        <form method="GET"
              action="{{ route('admin.bank-sampah.penarikan.export') }}"
              id="exportForm">

            <input type="hidden" name="bulan" value="{{ request('bulan') }}">
            <input type="hidden" name="tahun" value="{{ request('tahun') }}">
            <input type="hidden" name="status" value="{{ request('status') }}">
            <input type="hidden" name="tipe_filter" value="{{ request('tipe_filter') }}">
            <input type="hidden" name="kecamatan_id" value="{{ request('kecamatan_id') }}">
            <input type="hidden" name="desa_id" value="{{ request('desa_id') }}">
            <input type="hidden" name="dinas_id" value="{{ request('dinas_id') }}">
            <input type="hidden" name="tipe_pengguna" value="semua">

            <button type="submit" class="btn-cetak">
                <img src="{{ asset('assets/icons/excel.png') }}" class="icon-excel">
                Export Excel
            </button>

        </form>

    </div>

</div>

    <div class="green-divider"></div>


    {{-- Table --}}
    <div class="table-container">
        <table class="data-table" id="penarikanTable">
            <thead>
                <tr>
                    <th width="5%">No</th>
                    <th width="12%">Nama Pengguna</th>
                    <th style="width: 15%; text-align: center; padding-right: 0px;">Pekerjaan</th>
                    <th width="15%">Tanggal Penarikan</th>
                    <th width="12%">Jumlah Uang</th>
                    <th style="width: 10%;">E-Wallet / Bank</th>
                    <th style="width: 10%; text-align: center; padding-right: 30px;">Status</th>
                    <th width="10%">Aksi</th>
                </tr>
            </thead>
            <tbody>
                @forelse($penarikans as $index => $penarikan)
                    <tr>
                        {{-- 1. Nomor Urut --}}
                        <td>{{ $penarikans->firstItem() + $index }}</td>

                        {{-- 2. Nama Anggota --}}
                        <td><span class="member-name">{{ $penarikan->nama_user ?? 'Unknown' }}</span></td>

                        {{-- 3. Pekerjaan --}}
                        <td>
                            @if ($penarikan->id_masyarakat)
                                {{-- Masyarakat: Tampilkan "Masyarakat (Kecamatan, Desa)" --}}
                                <span class="badge badge-masyarakat">
                                    Masyarakat ({{ $penarikan->kecamatan ?? '-' }}, {{ $penarikan->desa ?? '-' }})
                                </span>
                            @else
                                {{-- PNS: Tampilkan nama dinas --}}
                                <span class="badge badge-pns">
                                    {{ $penarikan->dinas ?? 'ASN/PNS' }}
                                </span>
                            @endif
                        </td>

                        {{-- 4. Tanggal --}}
                        <td>
                            <span class="date-main">{{ $penarikan->tanggal_penarikan->format('d M Y') }}</span>
                            <span class="date-time">{{ $penarikan->tanggal_penarikan->format('H:i') }}</span>
                        </td>

                        {{-- 5. Jumlah Uang --}}
                        <td><span class="amount">Rp {{ number_format($penarikan->jumlah_uang, 0, ',', '.') }}</span></td>

                        {{-- 6. E-Wallet --}}
                        <td>
                            <span class="wallet-type">
                                @if ($penarikan->jenis_layanan === 'bank')
                                    {{ $penarikan->nama_bank ?? '-' }}
                                @else
                                    {{ $penarikan->jenis_ewallet ?? '-' }}
                                @endif
                            </span>
                            <span class="wallet-number">{{ $penarikan->nomor_ewallet ?? '' }}</span>
                        </td>

                        {{-- 7. Status --}}
                        <td>
                            @php
                                $statusClass = match ($penarikan->status) {
                                    'berhasil' => 'status-berhasil',
                                    'ditolak' => 'status-ditolak',
                                    default => 'status-diproses',
                                };
                                $statusText = ucfirst($penarikan->status);
                            @endphp
                            <span class="status-badge {{ $statusClass }}">{{ $statusText }}</span>
                        </td>

                        {{-- 8. Aksi --}}
                        <td>
                            <div class="action-buttons">
                                <button class="btn-action btn-view" onclick="showDetail({{ $penarikan->id_penarikan }})"
                                    title="Lihat Detail">
                                    <i class="fas fa-eye"></i>
                                </button>
                            </div>
                        </td>
                    </tr>
                @empty
                    <tr class="empty-row">
                        <td colspan="7">
                            <i class="fas fa-inbox"
                                style="font-size: 48px; opacity: 0.3; display: block; margin-bottom: 8px;"></i>
                            Belum ada data penarikan
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    {{-- Pagination --}}
    @if ($penarikans->hasPages())
        <div class="pagination-container">
            <div>Menampilkan {{ $penarikans->firstItem() }} - {{ $penarikans->lastItem() }} dari
                {{ $penarikans->total() }} data</div>
            {{ $penarikans->links() }}
        </div>
    @endif
    </div>

    {{-- Modal Detail --}}
    <div id="detailModal" class="modal-overlay">
        <div class="modal-container">
            <div class="modal-header">
                <h3 class="modal-title">Detail Penarikan</h3>
                <button class="modal-close" onclick="closeModal()">&times;</button>
            </div>

            <div class="modal-body">
                <div class="form-grid">
                    <div class="form-group">
                        <label class="form-label">ID Penarikan</label>
                        <input type="text" id="detail-id" class="form-input" readonly>
                    </div>

                    <div class="form-group">
                        <label class="form-label">Nama Anggota</label>
                        <input type="text" id="detail-nama" class="form-input" readonly>
                    </div>

                    {{-- Pekerjaan --}}
                    <div class="form-group">
                        <label class="form-label">Pekerjaan</label>
                        <input type="text" id="detail-tipe" class="form-input" readonly>
                    </div>

                    {{-- Untuk Masyarakat: Kecamatan & Desa --}}
                    <div id="detail-wilayah-group" class="form-row" style="display: none;">
                        <div class="form-group">
                            <label class="form-label">Kecamatan</label>
                            <input type="text" id="detail-kecamatan" class="form-input" readonly>
                        </div>
                        <div class="form-group">
                            <label class="form-label">Desa</label>
                            <input type="text" id="detail-desa" class="form-input" readonly>
                        </div>
                    </div>

                    {{-- Untuk PNS: Dinas --}}
                    <div id="detail-dinas-group" class="form-group" style="display: none;">
                        <label class="form-label">Dinas</label>
                        <input type="text" id="detail-dinas" class="form-input" readonly>
                    </div>

                    <div class="form-group">
                        <label class="form-label">Tanggal Pengajuan</label>
                        <input type="text" id="detail-tanggal" class="form-input" readonly>
                    </div>

                    <div class="form-group">
                        <label class="form-label">E-Wallet / Bank</label>
                        <input type="text" id="detail-jenis" class="form-input" readonly>
                    </div>

                    <div class="form-group">
                        <label class="form-label">Nomor Rekening / E-Wallet</label>
                        <input type="text" id="detail-ewallet" class="form-input" readonly>
                    </div>

                    <div class="form-group">
                        <label class="form-label">Jumlah Penarikan</label>
                        <input type="text" id="detail-jumlah" class="form-input amount-highlight" readonly>
                    </div>

                    <div class="form-group">
                        <label class="form-label">Status Saat Ini</label>
                        <input type="text" id="detail-status-text" class="form-input" readonly>
                    </div>

                    <div class="form-section">
                        <label class="form-label label-primary">Update Status</label>
                        <select id="detail-status" class="form-select" onchange="toggleStatusInfo()">
                            <option value="diproses">🔄 Diproses</option>
                            <option value="berhasil">✅ Berhasil (Setujui)</option>
                            <option value="ditolak">❌ Ditolak</option>
                        </select>
                    </div>

                    <div id="alasanPenolakanGroup" class="form-group" style="display: none;">
                        <label class="form-label label-danger">Alasan Penolakan <span class="required">*</span></label>
                        <textarea id="detail-alasan" class="form-textarea" rows="3" placeholder="Masukkan alasan penolakan..."></textarea>
                    </div>

                    <div id="statusFinalInfo" class="info-box info-final" style="display: none;">
                        <strong>ℹ️ Status Sudah Final</strong><br>
                        Status penarikan ini sudah tidak dapat diubah lagi.
                    </div>

                    <div id="statusInfo" class="status-info"></div>
                </div>
            </div>

            <div class="modal-footer">
                <button class="btn btn-secondary" onclick="closeModal()">Tutup</button>
                <button id="btnSimpan" class="btn btn-primary" onclick="updateStatus()" style="display: none;">Simpan
                    Perubahan</button>
            </div>
        </div>
    </div>

    {{-- Success Popup --}}
    <div id="successPopup" class="success-popup">
        <div class="success-content">
            <div class="success-icon">
                <i class="fas fa-check-circle"></i>
            </div>
            <h3 class="success-title">Berhasil!</h3>
            <p class="success-message" id="successMessage">Data berhasil diperbarui.</p>
        </div>
    </div>

    {{-- Confirmation Modal --}}
    <div id="confirmModal" class="confirm-modal">
        <div class="confirm-content">
            <div class="confirm-icon">
                <i class="fas fa-question-circle"></i>
            </div>
            <h3 class="confirm-title" id="confirmTitle">Konfirmasi</h3>
            <p class="confirm-message" id="confirmMessage">Apakah Anda yakin?</p>
            <div class="confirm-buttons">
                <button class="btn-confirm btn-cancel" onclick="closeConfirmModal()">Batal</button>
                <button class="btn-confirm btn-ok" id="btnConfirmOk" onclick="executeConfirm()">Ya, Lanjutkan</button>
            </div>
        </div>
    </div>
@endsection

@push('scripts')
    <script src="{{ asset('js/penarikan.js?v=' . time()) }}"></script>

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
            url.searchParams.delete('tipe_filter');
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

        // Toggle tombol Reset Wilayah
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
