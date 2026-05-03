@extends('layouts.admin')

@section('title', 'Bank Sampah - Data Penarikan')
@section('page-title', 'Data Penarikan')

@section('content')
<style>
    :root {
        --green: #10b981;
        --green-dark: #059669;
        --gray-50: #f9fafb;
        --gray-100: #f3f4f6;
        --gray-200: #e5e7eb;
        --gray-300: #d1d5db;
        --gray-400: #9ca3af;
        --gray-500: #6b7280;
        --gray-600: #4b5563;
        --gray-700: #374151;
        --gray-800: #1f2937;
        --radius: 8px;
        --transition: all 0.2s ease;
    }

    .page-wrapper {
        background: white;
        border-radius: var(--radius);
        box-shadow: 0 1px 3px rgba(0,0,0,0.08);
        padding: 1.5rem;
        margin-bottom: 2rem;
        border: 1px solid var(--gray-100);
    }

    /* 1. Search di Atas */
    .search-top {
        margin-bottom: 1.25rem;
    }
    .search-box {
        max-width: 380px;
        position: relative;
    }
    .search-input {
        width: 100%;
        padding: 0.65rem 1rem;
        padding-left: 2.5rem;
        border: 1px solid var(--gray-200);
        border-radius: var(--radius);
        font-size: 0.875rem;
        background: var(--gray-50);
        transition: var(--transition);
    }
    .search-input:focus {
        outline: none;
        border-color: var(--green);
        box-shadow: 0 0 0 3px rgba(16, 185, 129, 0.15);
        background: white;
    }
    .search-icon {
        position: absolute;
        left: 0.85rem;
        top: 50%;
        transform: translateY(-50%);
        color: var(--gray-400);
        pointer-events: none;
    }

    /* 2. Judul Hijau + Tombol Cetak */
    .header-row {
        display: flex;
        justify-content: space-between;
        align-items: center;
        margin-bottom: 0.5rem;
        flex-wrap: wrap;
        gap: 0.75rem;
    }
    .title-green {
        margin: 0;
        font-size: 1.25rem;
        font-weight: 700;
        color: var(--green);
        letter-spacing: 0.01em;
    }
   .btn-print {
    background: #FF8114;
    border: none;
    color: white;
    padding: 0.5rem 0.85rem;
    border-radius: var(--radius);
    display: flex;
    align-items: center;
    gap: 0.5rem;
    cursor: pointer;
    font-size: 0.85rem;
    font-weight: 500;
    transition: var(--transition);
}
.btn-print:hover {
    background: #E67300;
    transform: translateY(-1px);
    box-shadow: 0 2px 8px rgba(255, 129, 20, 0.3);
}

    /* 3. Garis Hijau Memanjang Lurus */
    .green-line {
        width: 100%;
        height: 3px;
        background: var(--green);
        margin-bottom: 1.5rem;
        border-radius: 99px;
        opacity: 0.9;
    }

    /* Table */
    .table-wrapper {
        overflow-x: auto;
    }
    .data-table {
        width: 100%;
        border-collapse: collapse;
        font-size: 0.875rem;
    }
    .data-table thead th {
        padding: 1rem 1rem;
        text-align: left;
        font-weight: 600;
        color: var(--gray-500);
        text-transform: uppercase;
        font-size: 0.7rem;
        letter-spacing: 0.06em;
        border-bottom: 2px solid var(--gray-100);
        white-space: nowrap;
        background: var(--gray-50);
    }
    .data-table tbody tr {
        border-bottom: 1px solid var(--gray-100);
        transition: var(--transition);
    }
    .data-table tbody tr:hover {
        background: var(--gray-50);
    }
    .data-table tbody td {
        padding: 1rem;
        color: var(--gray-700);
        vertical-align: middle;
    }

    /* Status Badge */
    .status-badge {
        padding: 0.3rem 0.75rem;
        border-radius: 99px;
        font-size: 0.72rem;
        font-weight: 600;
        display: inline-block;
        text-transform: capitalize;
    }
    .status-diproses { background: #fef3c7; color: #92400e; }
    .status-diterima { background: #d1fae5; color: #065f46; }
    .status-ditolak   { background: #fee2e2; color: #991b1b; }

    /* Action Buttons */
    .btn-action {
        width: 30px;
        height: 30px;
        padding: 0;
        border-radius: 6px;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        border: none;
        cursor: pointer;
        transition: var(--transition);
    }
    .btn-view { background: var(--green); color: white; }
    .btn-view:hover { background: var(--green-dark); transform: scale(1.05); }
    .btn-delete { background: #ef4444; color: white; }
    .btn-delete:hover { background: #dc2626; transform: scale(1.05); }

    /* Empty State */
    .empty-state {
        text-align: center;
        padding: 3rem 1rem;
        color: var(--gray-400);
    }
    .empty-state svg { width: 48px; height: 48px; margin-bottom: 0.75rem; opacity: 0.5; }
    .empty-state p { margin: 0.25rem 0; }
    .empty-state strong { color: var(--gray-600); font-size: 1rem; }

    /* Modal */
    .modal {
        display: none;
        position: fixed;
        top: 0; left: 0;
        width: 100%; height: 100%;
        background: rgba(0,0,0,0.4);
        backdrop-filter: blur(3px);
        z-index: 1000;
        align-items: center;
        justify-content: center;
    }
    .modal-content {
        background: white;
        border-radius: var(--radius);
        width: 90%;
        max-width: 480px;
        max-height: 90vh;
        overflow-y: auto;
        box-shadow: 0 10px 25px rgba(0,0,0,0.1);
    }
    .modal-header {
        padding: 1.25rem 1.5rem;
        border-bottom: 1px solid var(--gray-100);
        display: flex;
        justify-content: space-between;
        align-items: center;
    }
    .modal-header h3 { margin: 0; font-size: 1.1rem; font-weight: 600; color: var(--gray-800); }
    .modal-close {
        background: none; border: none; font-size: 1.5rem; cursor: pointer;
        color: var(--gray-400); width: 32px; height: 32px; border-radius: 50%;
        display: flex; align-items: center; justify-content: center;
    }
    .modal-close:hover { background: var(--gray-100); color: var(--gray-600); }
    .modal-body { padding: 1.5rem; }
    .form-group { margin-bottom: 1rem; }
    .form-group label {
        display: block; margin-bottom: 0.4rem; font-size: 0.85rem;
        font-weight: 500; color: var(--gray-500);
    }
    .form-group input, .form-group select {
        width: 100%; padding: 0.7rem 0.9rem; border: 1px solid var(--gray-200);
        border-radius: 6px; font-size: 0.9rem; background: var(--gray-50); box-sizing: border-box;
    }
    .form-group input[readonly] { background: var(--gray-100); cursor: not-allowed; }
    .form-actions {
        display: flex; gap: 0.75rem; justify-content: flex-end;
        margin-top: 1.25rem; padding-top: 1rem; border-top: 1px solid var(--gray-100);
    }
    .btn {
        padding: 0.6rem 1rem; border-radius: 6px; font-weight: 500;
        cursor: pointer; border: none; font-size: 0.85rem; transition: var(--transition);
    }
    .btn-secondary { background: var(--gray-100); color: var(--gray-600); }
    .btn-secondary:hover { background: var(--gray-200); }
    .btn-green { background: var(--green); color: white; }
    .btn-green:hover { background: var(--green-dark); }

    @media print {
        .search-top, .btn-print, .btn-action { display: none !important; }
        .page-wrapper { box-shadow: none; border: none; padding: 0; }
        .green-line { background: #333; }
    }
    @media (max-width: 640px) {
        .header-row { flex-direction: column; align-items: flex-start; }
        .btn-print { width: 100%; justify-content: center; }
        .search-box { max-width: 100%; }
    }
</style>

<div class="page-wrapper">
    <!-- 1. Pencarian di Atas -->
    <div class="search-top">
        <div class="search-box">
            <svg class="search-icon" width="18" height="18" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
            </svg>
            <input type="text" id="searchInput" class="search-input" placeholder="Cari nama, status, atau tanggal...">
        </div>
    </div>

    <!-- 2. Judul Hijau + Tombol Cetak -->
    <div class="header-row">
        <h2 class="title-green">Data Penarikan</h2>
        <button class="btn-print" onclick="window.print()">
            <svg width="16" height="16" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z"/>
            </svg>
            Cetak PDF
        </button>
    </div>

    <!-- 3. Garis Hijau Memanjang Lurus -->
    <div class="green-line"></div>

    <!-- 4. Tabel Data -->
    <div class="table-wrapper">
        @if($penarikans->count() > 0)
        <table class="data-table">
            <thead>
                <tr>
                    <th>No</th>
                    <th>Nama</th>
                    <th>Waktu Penarikan</th>
                    <th>Jumlah Penarikan</th>
                    <th>Status</th>
                    <th>Aksi</th>
                </tr>
            </thead>
            <tbody>
                @foreach($penarikans as $index => $penarikan)
                <tr>
                    <td>{{ $index + 1 }}</td>
                    <td style="font-weight: 500; color: var(--gray-800);">{{ $penarikan->nama }}</td>
                    <td>
                        <div>{{ $penarikan->waktu_penarikan->format('d M Y') }}</div>
                        <small style="color: var(--gray-400); font-size: 0.8rem;">{{ $penarikan->waktu_penarikan->format('H:i:s') }}</small>
                    </td>
                    <td style="font-weight: 600;">Rp {{ number_format($penarikan->jumlah, 0, ',', '.') }}</td>
                    <td>
                        <span class="status-badge status-{{ strtolower(str_replace(' ', '-', $penarikan->status)) }}">
                            {{ $penarikan->status }}
                        </span>
                    </td>
                    <td>
                        <div style="display: flex; gap: 0.5rem;">
                            <button class="btn-action btn-view" onclick="showDetail({{ $penarikan->id }})" title="Lihat Detail">
                                <svg width="15" height="15" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/>
                                </svg>
                            </button>
                            <button class="btn-action btn-delete" onclick="deleteData({{ $penarikan->id }})" title="Hapus">
                                <svg width="15" height="15" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
                                </svg>
                            </button>
                        </div>
                    </td>
                </tr>
                @endforeach
            </tbody>
        </table>
        @else
        <div class="empty-state">
            <svg fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/>
            </svg>
            <p><strong>Belum ada data penarikan</strong></p>
            <p style="font-size: 0.85rem;">Data akan muncul ketika ada anggota yang mengajukan penarikan.</p>
        </div>
        @endif
    </div>
</div>

<!-- Modal Detail -->
<div id="detailModal" class="modal">
    <div class="modal-content">
        <div class="modal-header">
            <h3>Detail Penarikan</h3>
            <button class="modal-close" onclick="closeModal()">&times;</button>
        </div>
        <div class="modal-body">
            <form id="detailForm">
                <div class="form-group"><label>ID Penarikan</label><input type="text" id="detail-no" readonly></div>
                <div class="form-group"><label>Nama Anggota</label><input type="text" id="detail-nama" readonly></div>
                <div class="form-group"><label>Waktu Penarikan</label><input type="text" id="detail-waktu" readonly></div>
                <div class="form-group"><label>Jenis E-Wallet</label><input type="text" id="detail-jenis" readonly></div>
                <div class="form-group"><label>Nomor E-Wallet</label><input type="text" id="detail-ewallet" readonly></div>
                <div class="form-group"><label>Jumlah Penarikan</label><input type="text" id="detail-jumlah" readonly></div>
                <div class="form-group">
                    <label>Status</label>
                    <select id="detail-status">
                        <option value="Diproses">Diproses</option>
                        <option value="Diterima">Diterima</option>
                        <option value="Ditolak">Ditolak</option>
                    </select>
                </div>
                <div class="form-actions">
                    <button type="button" class="btn btn-secondary" onclick="closeModal()">Batal</button>
                    <button type="button" class="btn btn-green" onclick="updateStatus()">Simpan</button>
                </div>
            </form>
        </div>
    </div>
</div>

<form id="deleteForm" method="POST" style="display: none;">
    @csrf
    @method('DELETE')
</form>
@endsection

@section('scripts')
<script>
let currentId = null;
const formatRupiah = (num) => new Intl.NumberFormat('id-ID', { style: 'currency', currency: 'IDR', minimumFractionDigits: 0 }).format(num);

function showDetail(id) {
    currentId = id;
    fetch(`/bank-sampah/penarikan/${id}`)
        .then(r => r.json())
        .then(data => {
            document.getElementById('detail-no').value = `#TRX-${String(data.id).padStart(5, '0')}`;
            document.getElementById('detail-nama').value = data.nama;
            document.getElementById('detail-waktu').value = new Date(data.waktu_penarikan).toLocaleString('id-ID');
            document.getElementById('detail-jenis').value = data.jenis?.toUpperCase() || '-';
            document.getElementById('detail-ewallet').value = data.nomor_ewallet || '-';
            document.getElementById('detail-jumlah').value = formatRupiah(data.jumlah);
            document.getElementById('detail-status').value = data.status;
            document.getElementById('detailModal').style.display = 'flex';
        })
        .catch(err => { alert('Gagal mengambil data'); console.error(err); });
}

function closeModal() {
    document.getElementById('detailModal').style.display = 'none';
    currentId = null;
}

function updateStatus() {
    if (!currentId) return;
    const status = document.getElementById('detail-status').value;
    fetch(`/bank-sampah/penarikan/${currentId}/status`, {
        method: 'PUT',
        headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.content },
        body: JSON.stringify({ status })
    }).then(r => r.json()).then(d => { if(d.success) location.reload(); })
      .catch(e => { alert('Gagal update status'); console.error(e); });
}

function deleteData(id) {
    if (confirm('Yakin ingin menghapus data ini?')) {
        const form = document.getElementById('deleteForm');
        form.action = `/bank-sampah/penarikan/${id}`;
        form.submit();
    }
}

window.onclick = e => { if (e.target === document.getElementById('detailModal')) closeModal(); };

let searchTimeout;
document.getElementById('searchInput')?.addEventListener('input', function(e) {
    clearTimeout(searchTimeout);
    searchTimeout = setTimeout(() => {
        const term = e.target.value.toLowerCase().trim();
        document.querySelectorAll('.data-table tbody tr').forEach(row => {
            row.style.display = row.textContent.toLowerCase().includes(term) ? '' : 'none';
        });
    }, 250);
});
</script>
@endsection