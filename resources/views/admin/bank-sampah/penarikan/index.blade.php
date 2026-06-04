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

            {{-- Filter --}}
            <div class="filter-section">
                <form method="GET" action="{{ route('admin.bank-sampah.penarikan.index') }}" id="filterForm">
                    <div class="filter-group">
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
                            <option value="diproses" {{ request('status') == 'diproses' ? 'selected' : '' }}>Diproses
                            </option>
                            <option value="berhasil" {{ request('status') == 'berhasil' ? 'selected' : '' }}>Berhasil
                            </option>
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
                    </div>
                </form>
            </div>


            {{-- Export Button --}}
            <div class="page-header">
                <form method="GET" action="{{ route('admin.bank-sampah.penarikan.export') }}" id="exportForm">
                    <input type="hidden" name="bulan" value="{{ request('bulan') }}">
                    <input type="hidden" name="tahun" value="{{ request('tahun') }}">
                    <input type="hidden" name="status" value="{{ request('status') }}">
                    <button type="submit" class="btn-cetak">
                        <img src="{{ asset('assets/icons/excel.png') }}" alt="Excel" class="icon-excel">
                        Export Excel
                    </button>
                </form>
            </div>


            {{-- Search --}}
            <div class="top-search">
                <div class="search-wrapper">
                    <i class="fas fa-search search-icon"></i>
                    <input type="text" id="searchInput" class="search-input"
                        placeholder="Cari nama, status, atau tanggal...">
                </div>
            </div>
        </div>

        <div class="green-divider"></div>


        {{-- Table --}}
        <div class="table-container">
            <table class="data-table" id="penarikanTable">
                <thead>
                    <tr>
                        <th width="5%">No</th>
                        <th width="20%">Nama Pengguna</th>
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
                            {{-- 1. Nomor Urut --}}
                            <td>{{ $penarikans->firstItem() + $index }}</td>

                            {{-- 2. Nama Anggota --}}
                            <td><span class="member-name">{{ $penarikan->nama_user ?? 'Unknown' }}</span></td>

                            {{-- 3. Tanggal --}}
                            <td>
                                <span class="date-main">{{ $penarikan->tanggal_penarikan->format('d M Y') }}</span>
                                <span class="date-time">{{ $penarikan->tanggal_penarikan->format('H:i') }}</span>
                            </td>

                            {{-- 4. Jumlah Uang --}}
                            <td><span class="amount">Rp {{ number_format($penarikan->jumlah_uang, 0, ',', '.') }}</span>
                            </td>

                            {{-- 5. E-Wallet --}}
                            <td>
                                <span class="wallet-type">{{ $penarikan->jenis_ewallet ?? '-' }}</span>
                                <span class="wallet-number">{{ $penarikan->nomor_ewallet ?? '' }}</span>
                            </td>

                            {{-- 6. ✅ STATUS (Badge: Diproses/Disetujui/Ditolak) --}}
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

                            {{-- 7. ✅ AKSI (Icon Mata 👁️) --}}
                            <td>
                                <div class="action-buttons">
                                    <button class="btn-action btn-view"
                                        onclick="showDetail({{ $penarikan->id_penarikan }})" title="Lihat Detail">
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

                    <div class="form-group">
                        <label class="form-label">Tanggal Pengajuan</label>
                        <input type="text" id="detail-tanggal" class="form-input" readonly>
                    </div>

                    <div class="form-group">
                        <label class="form-label">Jumlah Penarikan</label>
                        <input type="text" id="detail-jumlah" class="form-input amount-highlight" readonly>
                    </div>

                    <div class="form-group">
                        <label class="form-label">E-Wallet</label>
                        <input type="text" id="detail-jenis" class="form-input" readonly
                            placeholder="Jenis E-Wallet">
                        <input type="text" id="detail-ewallet" class="form-input" readonly
                            placeholder="Nomor E-Wallet" style="margin-top: 8px;">
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
@endpush
