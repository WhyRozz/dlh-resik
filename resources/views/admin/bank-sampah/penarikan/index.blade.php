@extends('layouts.admin')

@section('title', 'Data Penarikan')
@section('page-title', 'Data Penarikan')

@push('styles')
<link rel="stylesheet" href="{{ asset('css/penarikan.css') }}">
@endpush

@section('content')

<div class="page-container">

    {{-- ✅ HEADER WRAPPER: Judul + Filter + Search dalam 1 baris --}}
    <div class="header-wrapper">
        
        {{-- 1. Judul --}}
        <div class="page-title-wrapper">
            <h1 class="page-title">Data Penarikan</h1>
        </div>

        {{-- 2. Filter --}}
        <div class="filter-section">
            <form method="GET" action="{{ route('admin.bank-sampah.penarikan.index') }}" id="filterForm">
                <div class="filter-group">
                    <select name="bulan" class="filter-select">
                        <option value="">Semua Bulan</option>
                        <option value="1" {{ request('bulan')=='1'?'selected':'' }}>Januari</option>
                        <option value="2" {{ request('bulan')=='2'?'selected':'' }}>Februari</option>
                        <option value="3" {{ request('bulan')=='3'?'selected':'' }}>Maret</option>
                        <option value="4" {{ request('bulan')=='4'?'selected':'' }}>April</option>
                        <option value="5" {{ request('bulan')=='5'?'selected':'' }}>Mei</option>
                        <option value="6" {{ request('bulan')=='6'?'selected':'' }}>Juni</option>
                        <option value="7" {{ request('bulan')=='7'?'selected':'' }}>Juli</option>
                        <option value="8" {{ request('bulan')=='8'?'selected':'' }}>Agustus</option>
                        <option value="9" {{ request('bulan')=='9'?'selected':'' }}>September</option>
                        <option value="10" {{ request('bulan')=='10'?'selected':'' }}>Oktober</option>
                        <option value="11" {{ request('bulan')=='11'?'selected':'' }}>November</option>
                        <option value="12" {{ request('bulan')=='12'?'selected':'' }}>Desember</option>
                    </select>

                    <select name="tahun" class="filter-select">
                        <option value="">Semua Tahun</option>
                        @foreach($tahunList as $tahun)
                            <option value="{{ $tahun }}" {{ request('tahun')==$tahun?'selected':'' }}>{{ $tahun }}</option>
                        @endforeach
                    </select>

                    <select name="status" class="filter-select">
                        <option value="">Semua Status</option>
                        <option value="diproses" {{ request('status')=='diproses'?'selected':'' }}>Diproses</option>
                        <option value="berhasil" {{ request('status')=='berhasil'?'selected':'' }}>Berhasil</option>
                        <option value="ditolak" {{ request('status')=='ditolak'?'selected':'' }}>Ditolak</option>
                    </select>

                    <button type="submit" class="btn-filter">
                        <svg width="16" height="16" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 4a1 1 0 011-1h16a1 1 0 011 1v2.586a1 1 0 01-.293.707l-6.414 6.414a1 1 0 00-.293.707V17l-4 4v-6.586a1 1 0 00-.293-.707L3.293 7.293A1 1 0 013 6.586V4z" />
                        </svg>
                        Filter
                    </button>
                </div>
            </form>
        </div>

        {{-- 3. Search --}}
        <div class="top-search">
            <div class="search-wrapper">
                <svg class="search-icon" width="18" height="18" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
                </svg>
                <input type="text" id="searchInput" class="search-input" placeholder="Cari nama, status, atau tanggal...">
            </div>
        </div>
    </div>

    {{-- Tombol Export Excel (dengan filter) --}}
    <div class="page-header" style="display: flex; justify-content: flex-end; margin-bottom: 1rem;">
        <form method="GET" action="{{ route('admin.bank-sampah.penarikan.export') }}" id="exportForm">
            {{-- Input hidden untuk mengirim filter --}}
            <input type="hidden" name="bulan" value="{{ request('bulan') }}">
            <input type="hidden" name="tahun" value="{{ request('tahun') }}">
            <input type="hidden" name="status" value="{{ request('status') }}">
            
            <button type="submit" class="btn-cetak">
                <svg width="18" height="18" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                </svg>
                Export Excel
            </button>
        </form>
    </div>

    {{-- Green Divider --}}
    <div class="green-divider"></div>

    {{-- Table Container --}}
    <div class="table-container">
        <table class="data-table" id="penarikanTable">
            <thead>
                <tr>
                    <th width="5%">No</th>
                    <th width="20%">Nama Anggota</th>
                    <th width="18%">Tanggal Penarikan</th>
                    <th width="15%">Jumlah Uang</th>
                    <th width="17%">E-Wallet</th>
                    <th width="12%">Status</th>
                    <th width="13%">Aksi</th>
                </tr>
            </thead>
            <tbody>
                @forelse($penarikans as $index => $penarikan)
                <tr>
                    <td>{{ $penarikans->firstItem() + $index }}</td>
                    <td>
                        <span class="member-name">{{ $penarikan->nama_user ?? 'Unknown' }}</span>
                    </td>
                    <td>
                        <span class="date-main">{{ $penarikan->tanggal_penarikan->format('d M Y') }}</span>
                        <span class="date-time">{{ $penarikan->tanggal_penarikan->format('H:i') }}</span>
                    </td>
                    <td>
                        <span class="amount">Rp {{ number_format($penarikan->jumlah_uang, 0, ',', '.') }}</span>
                    </td>
                    <td>
                        <span class="wallet-type">{{ $penarikan->jenis_ewallet ?? '-' }}</span>
                        <span class="wallet-number">{{ $penarikan->nomor_ewallet ?? '' }}</span>
                    </td>
                    <td>
                        <span class="status-badge status-{{ strtolower($penarikan->status) }}">
                            {{ ucfirst($penarikan->status) }}
                        </span>
                    </td>
                    <td>
                        <div class="action-buttons">
                            <button
                                class="btn-action btn-view"
                                onclick="showDetail({{ $penarikan->id_penarikan }})"
                                title="Lihat Detail">
                                <svg width="16" height="16" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />
                                </svg>
                            </button>
                        </div>
                    </td>
                </tr>
                @empty
                {{-- Empty State Row --}}
                <tr class="empty-row">
                    <td colspan="7">
                        <svg width="48" height="48" fill="none" stroke="currentColor" viewBox="0 0 24 24" style="margin-bottom: 0.5rem; opacity: 0.5;">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2" />
                        </svg>
                        <div>Belum ada data penarikan</div>
                    </td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    {{-- Pagination --}}
    @if($penarikans->hasPages())
    <div class="pagination-container">
        <div>
            Menampilkan {{ $penarikans->firstItem() }} - {{ $penarikans->lastItem() }}
            dari {{ $penarikans->total() }} data
        </div>
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
            <form id="detailForm">
                <div class="form-group">
                    <label class="form-label">ID Penarikan</label>
                    <input type="text" id="detail-id" class="form-input" readonly>
                </div>

                <div class="form-group">
                    <label class="form-label">Nama Anggota</label>
                    <input type="text" id="detail-nama" class="form-input" readonly>
                </div>

                <div class="form-group">
                    <label class="form-label">Tanggal Pengajuan</label>
                    <input type="text" id="detail-tanggal" class="form-input" readonly>
                </div>

                <div class="form-group">
                    <label class="form-label">Jumlah Penarikan</label>
                    <input type="text" id="detail-jumlah" class="form-input" readonly style="font-weight: bold; color: var(--primary-green);">
                </div>

                <div class="form-group">
                    <label class="form-label">E-Wallet</label>
                    <input type="text" id="detail-jenis" class="form-input" readonly>
                    <input type="text" id="detail-ewallet" class="form-input" readonly style="margin-top: 0.5rem;" placeholder="Nomor E-Wallet">
                </div>

                <div class="form-group">
                    <label class="form-label">Status Saat Ini</label>
                    <input type="text" id="detail-status-text" class="form-input" readonly style="background: var(--gray-100); font-weight: 600;">
                </div>

                {{-- Update Status Section --}}
                <div style="border-top: 2px solid var(--gray-200); padding-top: 1.25rem; margin-top: 1.25rem;">
                    <label class="form-label" style="color: var(--primary-green); font-weight: 700;">Update Status</label>
                    <select id="detail-status" class="form-select" onchange="toggleStatusInfo()">
                        <option value="diproses">🔄 Diproses</option>
                        <option value="berhasil" style="color: green; font-weight: bold;">✅ Berhasil (Setujui)</option>
                        <option value="ditolak" style="color: red; font-weight: bold;">❌ Ditolak</option>
                    </select>
                </div>

                {{-- 2. ✅ TEMPEL INI TEPAT DI BAWAHNYA (KODE BARU) --}}
                <div id="alasanPenolakanGroup" class="form-group" style="display:none; margin-top:1rem;">
                    <label class="form-label" style="color:var(--red);">Alasan Penolakan <span style="color:var(--red);">*</span></label>
                    <textarea id="detail-alasan" class="form-input" rows="3" placeholder="Masukkan alasan mengapa penarikan ditolak..."></textarea>
                </div>

                <div id="statusFinalInfo" class="status-info success" style="display:none;">
                    <strong>ℹ️ Status Sudah Final</strong><br>
                    Status penarikan ini sudah tidak dapat diubah lagi.
                </div>

                <div id="statusInfo" class="status-info"></div>
            </form>
        </div>
        <div class="modal-footer">
        <button class="btn btn-secondary" onclick="closeModal()">Tutup</button>
        <button id="btnSimpan" class="btn btn-primary" onclick="updateStatus()" style="display:none;">Simpan Perubahan</button>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
    let currentId = null;
    let currentStatus = null;

    // Format Rupiah
    const formatRupiah = (angka) => {
        return new Intl.NumberFormat('id-ID', {
            style: 'currency',
            currency: 'IDR',
            minimumFractionDigits: 0
        }).format(angka);
    };

    // ✅ TAMBAH FUNGSI INI
    function resetFilter() {
        document.getElementById('filterBulan').value = '';
        document.getElementById('filterTahun').value = '';
        document.getElementById('filterStatus').value = '';
        document.getElementById('filterForm').submit();
    }

    // Show Detail Modal
    function showDetail(id) {
        currentId = id;

        fetch('/admin/bank-sampah/penarikan/' + id)
            .then(function(response) {
                return response.json();
            })
            .then(function(data) {
                // ✅ Tambah null check untuk setiap elemen
                var elId = document.getElementById('detail-id');
                var elNama = document.getElementById('detail-nama');
                var elTanggal = document.getElementById('detail-tanggal');
                var elJumlah = document.getElementById('detail-jumlah');
                var elJenis = document.getElementById('detail-jenis');
                var elEwallet = document.getElementById('detail-ewallet');
                var elStatusText = document.getElementById('detail-status-text');
                var elStatus = document.getElementById('detail-status');

                if (elId) elId.value = '#TRX-' + String(data.id_penarikan).padStart(5, '0');
                if (elNama) elNama.value = data.nama_user || 'Unknown';
                if (elTanggal) elTanggal.value = new Date(data.tanggal_penarikan).toLocaleString('id-ID');
                if (elJumlah) elJumlah.value = formatRupiah(data.jumlah_uang);
                if (elJenis) elJenis.value = (data.jenis_ewallet || '-').toUpperCase();
                if (elEwallet) elEwallet.value = data.nomor_ewallet || '-';
                if (elStatusText) elStatusText.value = data.status.toUpperCase();
                if (elStatus) elStatus.value = data.status;

                // ✅✅✅ TAMBAH KODE BARU DI SINI (SETELAH set value, SEBELUM toggleStatusInfo) ✅✅✅
                
                // 1. Simpan status saat ini ke variable global
                currentStatus = data.status;
                
                // 2. Ambil elemen-elemen yang perlu di-show/hide
                const statusFinalInfo = document.getElementById('statusFinalInfo');
                const btnSimpan = document.getElementById('btnSimpan');
                const alasanGroup = document.getElementById('alasanPenolakanGroup');
                
                // 3. Jika status sudah final (bukan diproses), sembunyikan form update
                if (data.status !== 'diproses') {
                    if (statusFinalInfo) statusFinalInfo.style.display = 'block';
                    if (btnSimpan) btnSimpan.style.display = 'none';
                } else {
                    // Jika masih diproses, tampilkan tombol simpan
                    if (statusFinalInfo) statusFinalInfo.style.display = 'none';
                    if (btnSimpan) btnSimpan.style.display = 'inline-block';
                }
                
                // 4. Isi field alasan jika ada datanya
                if (alasanGroup && data.alasan_penolakan) {
                    document.getElementById('detail-alasan').value = data.alasan_penolakan;
                }

                toggleStatusInfo();

                var modal = document.getElementById('detailModal');
                if (modal) {
                    modal.classList.add('active');
                }
            })['catch'](function(error) {
                console.error('Error:', error);
                alert('Gagal mengambil data detail');
            });
    }

    // Close Modal
    function closeModal() {
        document.getElementById('detailModal').classList.remove('active');
        currentId = null;
    }

    // Toggle Status Info Message
    function toggleStatusInfo() {
        const status = document.getElementById('detail-status').value;
        const infoBox = document.getElementById('statusInfo');

        // ✅✅✅ TAMBAH 4 BARIS INI ✅✅✅
        const alasanGroup = document.getElementById('alasanPenolakanGroup');
        if (alasanGroup) {
            alasanGroup.style.display = (status === 'ditolak') ? 'block' : 'none';
        }

        const messages = {
            'diproses': {
                text: '🔄 Status masih dalam proses verifikasi',
                class: 'info'
            },
            'berhasil': {
                text: '✅ Penarikan disetujui. Saldo sudah dipotong dan admin akan melakukan transfer manual.',
                class: 'success'
            },
            'ditolak': {
                text: '❌ Penarikan ditolak. Saldo akan dikembalikan otomatis ke anggota.',
                class: 'danger'
            }
        };

        infoBox.textContent = messages[status].text;
        infoBox.className = 'status-info active ' + messages[status].class;
    }

    // Update Status
    function updateStatus() {
        if (!currentId) {
            alert('ID penarikan tidak ditemukan');
            return;
        }

        var status = document.getElementById('detail-status').value;

        // ✅ TAMBAH INI
        if (status === 'ditolak' && !document.getElementById('detail-alasan').value.trim()) {
            alert('❌ Alasan penolakan wajib diisi!');
            document.getElementById('detail-alasan').focus();
            return;
        }

        if (!confirm('Yakin ingin mengubah status menjadi ' + status.toUpperCase() + '?')) {
            return;
        }

        // ✅ Cara yang lebih aman mengambil CSRF token
        var csrfToken = document.querySelector('meta[name="csrf-token"]').getAttribute('content');

        if (!csrfToken || csrfToken === '') {
            alert('CSRF token tidak ditemukan. Silakan refresh halaman.');
            location.reload();
            return;
        }

        fetch('/admin/bank-sampah/penarikan/' + currentId + '/status', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': csrfToken,
                    'Accept': 'application/json',
                    'X-Requested-With': 'XMLHttpRequest'
                },
                body: JSON.stringify({
                    status: status,
                    alasan_penolakan: document.getElementById('detail-alasan').value, // ✅ TAMBAH BARIS INI
                    _method: 'PUT'
                })
            })
            .then(function(response) {
                return response.json();
            })
            .then(function(data) {
                if (data.success) {
                    alert('✅ ' + data.message);
                    location.reload();
                } else {
                    alert('❌ ' + data.message);
                }
            })['catch'](function(error) {
                console.error('Error:', error);
                alert('❌ Gagal: ' + error.message);
            });
    }


    // Close modal when clicking outside
    document.getElementById('detailModal').addEventListener('click', function(e) {
        if (e.target === this) {
            closeModal();
        }
    });

    // Live Search
    let searchTimeout;
    document.getElementById('searchInput').addEventListener('input', function(e) {
        clearTimeout(searchTimeout);
        searchTimeout = setTimeout(() => {
            const term = e.target.value.toLowerCase().trim();
            const rows = document.querySelectorAll('#penarikanTable tbody tr');

            rows.forEach(row => {
                if (!row.classList.contains('empty-row')) {
                    const text = row.textContent.toLowerCase();
                    row.style.display = text.includes(term) ? '' : 'none';
                }
            });
        }, 300);
    });

    // Escape key to close modal
    document.addEventListener('keydown', function(e) {
        if (e.key === 'Escape') {
            closeModal();
        }
    });
</script>
@endpush
