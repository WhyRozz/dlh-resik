{{-- 
    FILE: resources/views/bank-sampah/penarikan/index.blade.php
    FUNGSI: Menampilkan halaman daftar penarikan dana bank sampah
    DATA: $penarikans (Collection dari model Penarikan)
--}}

{{-- 1. EXTEND LAYOUT ADMIN --}}
{{-- Menggunakan layout utama admin yang sudah ada di project --}}
@extends('layouts.admin')

{{-- 2. SET JUDUL HALAMAN --}}
{{-- Judul yang muncul di browser tab --}}
@section('title', 'Bank Sampah - Data Penarikan')

{{-- Judul yang muncul di header halaman --}}
@section('page-title', 'Data Penarikan')

{{-- 3. KONTEN UTAMA HALAMAN --}}
@section('content')

{{-- ============================================
     BAGIAN CSS / STYLING
     ============================================ --}}
<style>
    /* Variabel CSS untuk konsistensi warna & ukuran */
    :root {
        --green: #10b981;        /* Warna utama hijau */
        --green-dark: #059669;   /* Hijau gelap untuk hover */
        --orange: #FF8114;       /* Warna tombol cetak */
        --orange-dark: #E67300;  /* Orange gelap untuk hover */
        --gray-50: #f9fafb;
        --gray-100: #f3f4f6;
        --gray-200: #e5e7eb;
        --gray-400: #9ca3af;
        --gray-500: #6b7280;
        --gray-600: #4b5563;
        --gray-700: #374151;
        --gray-800: #1f2937;
        --radius: 8px;           /* Sudut melengkung */
        --transition: all 0.2s ease; /* Animasi halus */
    }

    /* Container utama halaman */
    .page-wrapper {
        background: white;
        border-radius: var(--radius);
        box-shadow: 0 1px 3px rgba(0,0,0,0.08);
        padding: 1.5rem;
        margin-bottom: 2rem;
        border: 1px solid var(--gray-100);
    }

    /* === SEARCH BOX === */
    .search-top { margin-bottom: 1.25rem; }
    .search-box { max-width: 380px; position: relative; }
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

    /* === HEADER: JUDUL + TOMBOL CETAK === */
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
    }
    .btn-print {
        background: var(--orange);
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
        background: var(--orange-dark);
        transform: translateY(-1px);
        box-shadow: 0 2px 8px rgba(255, 129, 20, 0.3);
    }

    /* === GARIS PEMISAH HIJAU === */
    .green-line {
        width: 100%;
        height: 3px;
        background: var(--green);
        margin-bottom: 1.5rem;
        border-radius: 99px;
        opacity: 0.9;
    }

    /* === TABEL === */
    .table-wrapper { overflow-x: auto; }
    .data-table {
        width: 100%;
        border-collapse: collapse;
        font-size: 0.875rem;
    }
    .data-table thead th {
        padding: 1rem;
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
    .data-table tbody tr:hover { background: var(--gray-50); }
    .data-table tbody td {
        padding: 1rem;
        color: var(--gray-700);
        vertical-align: middle;
    }

    /* === BADGE STATUS === */
    .status-badge {
        padding: 0.3rem 0.75rem;
        border-radius: 99px;
        font-size: 0.72rem;
        font-weight: 600;
        display: inline-block;
        text-transform: capitalize;
    }
    .status-diproses { background: #fef3c7; color: #92400e; }  /* Kuning */
    .status-berhasil { background: #d1fae5; color: #065f46; }  /* Hijau */
    .status-ditolak   { background: #fee2e2; color: #991b1b; }  /* Merah */

    /* === TOMBOL AKSI (View/Delete) === */
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

    /* === EMPTY STATE (Saat tidak ada data) === */
    .empty-state {
        text-align: center;
        padding: 3rem 1rem;
        color: var(--gray-400);
    }
    .empty-state svg { width: 48px; height: 48px; margin-bottom: 0.75rem; opacity: 0.5; }
    .empty-state p { margin: 0.25rem 0; }
    .empty-state strong { color: var(--gray-600); font-size: 1rem; }

    /* === MODAL POPUP === */
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

    /* === PRINT MODE === */
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

{{-- ============================================
     BAGIAN HTML / TAMPILAN
     ============================================ --}}
<div class="page-wrapper">
    
    {{-- 1. SEARCH BOX: Untuk filter data client-side --}}
    <div class="search-top">
        <div class="search-box">
            <svg class="search-icon" width="18" height="18" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
            </svg>
            <input type="text" id="searchInput" class="search-input" placeholder="Cari nama, status, atau tanggal...">
        </div>
    </div>

    {{-- 2. HEADER: Judul + Tombol Cetak --}}
    <div class="header-row">
        <h2 class="title-green">Data Penarikan</h2>
        <button class="btn-print" onclick="window.print()">
            <svg width="16" height="16" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z"/>
            </svg>
            Cetak PDF
        </button>
    </div>

    {{-- 3. GARIS PEMISAH HIJAU --}}
    <div class="green-line"></div>

    {{-- 4. TABEL DATA PENARIKAN --}}
    <div class="table-wrapper">
        @if($penarikans->count() > 0)
        <table class="data-table">
            <thead>
                <tr>
                    <th>No</th>
                    <th>Nama Anggota</th>
                    <th>Tanggal Penarikan</th>
                    <th>Jumlah Uang</th>
                    <th>E-Wallet</th>
                    <th>Status</th>
                    <th>Aksi</th>
                </tr>
            </thead>
            <tbody>
                {{-- Loop setiap data penarikan --}}
                @foreach($penarikans as $index => $penarikan)
                <tr>
                    {{-- Nomor urut --}}
                    <td>{{ $index + 1 }}</td>
                    
                    {{-- Nama: Ambil dari relasi masyarakat, fallback ke ID jika null --}}
                    <td style="font-weight: 500; color: var(--gray-800);">
                        {{ $penarikan->masyarakat->nama ?? 'User #'.$penarikan->id_masyarakat }}
                    </td>
                    
                    {{-- Tanggal: Format tanggal_penarikan --}}
                    <td>
                        <div>{{ \Carbon\Carbon::parse($penarikan->tanggal_penarikan)->format('d M Y') }}</div>
                        <small style="color: var(--gray-400); font-size: 0.8rem;">
                            {{ \Carbon\Carbon::parse($penarikan->tanggal_penarikan)->format('H:i:s') }}
                        </small>
                    </td>
                    
                    {{-- Jumlah: Format Rupiah dari kolom jumlah_uang --}}
                    <td style="font-weight: 600;">
                        Rp {{ number_format($penarikan->jumlah_uang ?? 0, 0, ',', '.') }}
                    </td>
                    
                    {{-- E-Wallet: Jenis + Nomor --}}
                    <td>
                        <div>{{ $penarikan->jenis_ewallet ?? '-' }}</div>
                        <small style="color: var(--gray-400);">{{ $penarikan->nomor_ewallet ?? '' }}</small>
                    </td>
                    
                    {{-- Status: Badge dengan warna sesuai status --}}
                    <td>
                        <span class="status-badge status-{{ strtolower($penarikan->status) }}">
                            {{ ucfirst($penarikan->status) }}
                        </span>
                    </td>
                    
                    {{-- Tombol Aksi: View & Delete --}}
                    <td>
                        <div style="display: flex; gap: 0.5rem;">
                            {{-- Tombol Detail: Buka modal --}}
                            <button class="btn-action btn-view" 
                                    onclick="showDetail({{ $penarikan->id_penarikan }})" 
                                    title="Lihat Detail">
                                <svg width="15" height="15" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/>
                                </svg>
                            </button>
                            
                            {{-- Tombol Hapus: Konfirmasi dulu --}}
                            <button class="btn-action btn-delete" 
                                    onclick="deleteData({{ $penarikan->id_penarikan }})" 
                                    title="Hapus">
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
        {{-- Tampilan jika tidak ada data --}}
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

{{-- ============================================
     MODAL DETAIL PENARIKAN
     ============================================ --}}
<div id="detailModal" class="modal">
    <div class="modal-content">
        <div class="modal-header">
            <h3>Detail Penarikan</h3>
            <button class="modal-close" onclick="closeModal()">&times;</button>
        </div>
        <div class="modal-body">
            <form id="detailForm">
                {{-- Field-field readonly untuk display data --}}
                <div class="form-group">
                    <label>ID Penarikan</label>
                    <input type="text" id="detail-id" readonly>
                </div>
                <div class="form-group">
                    <label>Nama Anggota</label>
                    <input type="text" id="detail-nama" readonly>
                </div>
                <div class="form-group">
                    <label>Tanggal Penarikan</label>
                    <input type="text" id="detail-tanggal" readonly>
                </div>
                <div class="form-group">
                    <label>Jenis E-Wallet</label>
                    <input type="text" id="detail-jenis" readonly>
                </div>
                <div class="form-group">
                    <label>Nomor E-Wallet</label>
                    <input type="text" id="detail-ewallet" readonly>
                </div>
                <div class="form-group">
                    <label>Jumlah Penarikan</label>
                    <input type="text" id="detail-jumlah" readonly>
                </div>
                
                {{-- Dropdown untuk update status --}}
                <div class="form-group">
                    <label>Status</label>
                    <select id="detail-status">
                        <option value="diproses">Diproses</option>
                        <option value="berhasil">Berhasil</option>
                        <option value="ditolak">Ditolak</option>
                    </select>
                </div>
                
                {{-- Tombol simpan & batal --}}
                <div class="form-actions">
                    <button type="button" class="btn btn-secondary" onclick="closeModal()">Batal</button>
                    <button type="button" class="btn btn-green" onclick="updateStatus()">Simpan Perubahan</button>
                </div>
            </form>
        </div>
    </div>
</div>

{{-- Form tersembunyi untuk proses delete --}}
<form id="deleteForm" method="POST" style="display: none;">
    @csrf
    @method('DELETE')
</form>

@endsection

{{-- ============================================
     BAGIAN JAVASCRIPT / INTERAKTIVITAS
     ============================================ --}}
@section('scripts')
<script>
    // Variabel global untuk menyimpan ID penarikan yang sedang dipilih
    let currentId = null;

    // Fungsi format Rupiah otomatis
    const formatRupiah = (num) => new Intl.NumberFormat('id-ID', { 
        style: 'currency', 
        currency: 'IDR', 
        minimumFractionDigits: 0 
    }).format(num);

    /**
     * FUNGSI: showDetail(id)
     * TUJUAN: Fetch data detail dari API dan tampilkan di modal
     */
    function showDetail(id) {
        currentId = id;
        
        // ✅ Route harus sesuai dengan yang ada di web.php
        fetch(`/admin/bank-sampah/tarik/${id}`)
            .then(response => response.json())
            .then(data => {
                // Isi field modal dengan data dari API
                document.getElementById('detail-id').value = `#TRX-${String(data.id_penarikan).padStart(5, '0')}`;
                document.getElementById('detail-nama').value = data.masyarakat?.nama || 'User #'+data.id_masyarakat;
                document.getElementById('detail-tanggal').value = new Date(data.tanggal_penarikan).toLocaleString('id-ID');
                document.getElementById('detail-jenis').value = data.jenis_ewallet?.toUpperCase() || '-';
                document.getElementById('detail-ewallet').value = data.nomor_ewallet || '-';
                document.getElementById('detail-jumlah').value = formatRupiah(data.jumlah_uang);
                document.getElementById('detail-status').value = data.status;
                
                // Tampilkan modal
                document.getElementById('detailModal').style.display = 'flex';
            })
            .catch(err => { 
                alert('Gagal mengambil data detail'); 
                console.error(err); 
            });
    }

    /**
     * FUNGSI: closeModal()
     * TUJUAN: Sembunyikan modal dan reset variabel
     */
    function closeModal() {
        document.getElementById('detailModal').style.display = 'none';
        currentId = null;
    }

    /**
     * FUNGSI: updateStatus()
     * TUJUAN: Kirim request PUT untuk update status penarikan
     */
    function updateStatus() {
        if (!currentId) return;
        
        const status = document.getElementById('detail-status').value;
        
        // ✅ Route harus sesuai dengan yang ada di web.php
        fetch(`/admin/bank-sampah/tarik/${currentId}/status`, {
            method: 'PUT',
            headers: { 
                'Content-Type': 'application/json', 
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.content 
            },
            body: JSON.stringify({ status: status })
        })
        .then(response => response.json())
        .then(data => { 
            if (data.message || data.success) {
                location.reload(); // Refresh halaman jika sukses
            } else {
                alert('Gagal update status');
            }
        })
        .catch(error => { 
            alert('Terjadi kesalahan saat update status'); 
            console.error(error); 
        });
    }

    /**
     * FUNGSI: deleteData(id)
     * TUJUAN: Konfirmasi lalu submit form delete
     */
    function deleteData(id) {
        if (confirm('⚠️ Yakin ingin menghapus data penarikan ini?')) {
            const form = document.getElementById('deleteForm');
            // ✅ Route harus sesuai dengan yang ada di web.php
            form.action = `/admin/bank-sampah/tarik/${id}`;
            form.submit();
        }
    }

    // Tutup modal jika klik di luar area modal
    window.onclick = function(event) {
        const modal = document.getElementById('detailModal');
        if (event.target === modal) {
            closeModal();
        }
    }

    /**
     * FITUR: Live Search
     * TUJUAN: Filter tabel secara real-time tanpa reload
     */
    let searchTimeout;
    document.getElementById('searchInput')?.addEventListener('input', function(e) {
        clearTimeout(searchTimeout);
        searchTimeout = setTimeout(() => {
            const term = e.target.value.toLowerCase().trim();
            document.querySelectorAll('.data-table tbody tr').forEach(row => {
                // Tampilkan baris jika teks mengandung keyword search
                row.style.display = row.textContent.toLowerCase().includes(term) ? '' : 'none';
            });
        }, 250); // Delay 250ms agar tidak berat saat mengetik cepat
    });
</script>
@endsection