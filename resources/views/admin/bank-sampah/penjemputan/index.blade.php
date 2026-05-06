@extends('layouts.admin') {{-- ✅ PAKAI LAYOUT ADMIN (Font Konsisten) --}}
@section('title', 'Daftar Penjemputan')

@push('styles')
    <link rel="stylesheet" href="{{ asset('css/penjemputan.css') }}">
    
    {{-- ✅ FIX CSS: Mapping class penjemputan ke struktur admin --}}
    <style>
        /* Wrapper penjemputan: reset margin/padding biar nggak dobel */
        .main-wrapper {
            margin-left: 0 !important;
            padding: 0 !important;
            background: transparent !important;
        }
        
        /* Main content: kasih padding yang sesuai */
        .main-content {
            padding: 0 !important;
        }
        
        /* Pastikan content-card tampil rapi */
        .content-card {
            margin: 0 !important;
        }
        
        /* Fix font sidebar biar nggak tebal berlebih saat active */
        .sidebar .nav-item.active > a {
            font-weight: 500 !important;
        }

 /* ============================================
   ✅ FIX: Header Layout - Judul & Search
   ============================================ */
.card-header-wrapper {
    display: flex !important;
    justify-content: flex-start !important; /* ✅ Ubah dari space-between ke flex-start */
    align-items: center !important;
    width: 100% !important;
    gap: 20px !important; /* ✅ Jarak antara judul dan search box */
    margin-bottom: 0 !important;
    padding-bottom: 0 !important;
}

.card-header-wrapper .page-title {
    margin: 0 !important;
    padding: 0 !important;
    border-bottom: none !important;
    font-size: 20px !important;
    font-weight: 700 !important;
    color: #2e7d32 !important;
    white-space: nowrap; /* ✅ Judul tidak turun ke bawah */
}

/* ✅ Wrapper Search Box - Posisi ke Kiri */
.search-box-wrapper {
    margin: 0 !important;
    padding: 0 !important;
    display: flex;
    justify-content: flex-start !important; /* ✅ Pastikan align ke kiri */
}

.search-box-wrapper .search-box {
    position: relative;
    width: 260px;
    margin-left: 260px !important; /* ✅ Pastikan tidak ada margin kiri */
}

.search-box-wrapper .search-box input {
    width: 100%;
    padding: 8px 40px 8px 16px; /* ✅ Padding kanan diperbesar untuk tombol */
    border: 2px solid #ddd;
    border-radius: 25px;
    font-size: 14px;
    outline: none;
    background: white;
    box-sizing: border-box; /* ✅ Agar padding tidak melebarkan input */
}

.search-box-wrapper .search-box input:focus {
    border-color: #4CAF50;
    box-shadow: 0 0 8px rgba(76, 175, 80, 0.3);
}

/* ✅ Tombol Search Hijau - Posisi Mentok Kanan dalam Input */
.search-box-wrapper .search-box button {
    position: absolute;
    right: 3px !important; /* ✅ Lebih mentok ke kanan */
    top: 50%;
    transform: translateY(-50%) !important; /* ✅ Vertikal benar-benar tengah */
    background: #4CAF50;
    border: none;
    width: 32px;
    height: 32px;
    border-radius: 50%;
    color: white;
    cursor: pointer;
    display: flex;
    align-items: center;
    justify-content: center;
    transition: background 0.2s;
    font-size: 14px;
    padding: 0 !important; /* ✅ Reset padding */
    line-height: 1 !important; /* ✅ Reset line-height */
}

.search-box-wrapper .search-box button:hover {
    background: #45a049;
}

.search-box-wrapper .search-box button i {
    display: flex;
    align-items: center;
    justify-content: center;
    line-height: 1; /* ✅ Icon kaca pembesar benar-benar tengah */
}

        /* Garis hijau pindah ke elemen terpisah */
        .green-line {
            height: 2px;
            background: #4caf50;
            margin: 16px 0 24px 0 !important;
            width: 100%;
            border-radius: 2px;
            display: block;
        }

        /* Responsive Mobile */
        @media (max-width: 768px) {
            .card-header-wrapper {
                flex-direction: column !important;
                align-items: flex-start !important;
                gap: 12px !important;
            }
            .search-box-wrapper .search-box {
                width: 100% !important;
            }
        }
    </style>
@endpush

@section('content')
{{-- ✅ WRAPPER KHUSUS: Bungkus konten dengan class yang dicari penjemputan.css --}}
<div class="main-wrapper" id="mainWrapper">
    <main class="main-content">
        
       <div class="content-card">
            {{-- ✅ Judul dan Search dalam satu baris --}}
            <div class="card-header-wrapper">
                <h2 class="page-title">Daftar Penjemputan</h2>
                
                <div class="search-box-wrapper">
                    <div class="search-box">
                        <input type="text" id="searchInput" placeholder="Cari penjemputan..." onkeyup="searchTable()">
                        <button type="button" onclick="searchTable()">
                            <i class="fas fa-search"></i>
                        </button>
                    </div>
                </div>
            </div>
            
            {{-- ✅ GARIS HIJAU PINDAH KE SINI (di bawah header) --}}
            <div class="green-line"></div>
            
            <table id="penjemputanTable">
                <thead>
                    <tr>
                        <th>No</th>
                        <th>Gambar</th>
                        <th>Nama Admin</th>
                        <th>Waktu</th>
                        <th>Berat</th>
                        <th>Status</th>
                        <th>Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($penjemputans as $index => $item)
                    <tr onclick="showDetail({{ $item->id }})" style="cursor: pointer;">
                        <td>{{ $index + 1 }}</td>
                        <td>
                            @if($item->foto)
                                <img src="{{ asset('storage/' . $item->foto) }}" alt="Foto Penjemputan">
                            @else
                                <img src="{{ asset('images/no-image.png') }}" alt="No Image" class="no-img">
                            @endif
                        </td>
                        <td>{{ $item->nama_admin }}</td>
                        <td>{{ \Carbon\Carbon::parse($item->waktu)->format('d-m-Y, H:i') }}</td>
                        <td>{{ number_format($item->berat, 2) }} Kg</td>
                        <td>
                            @php
                                $badgeClass = match($item->status) {
                                    'diproses' => 'status-diproses',
                                    'berhasil' => 'status-berhasil',
                                    'ditolak'  => 'status-ditolak',
                                    default    => 'status-diproses'
                                };
                            @endphp
                            <span class="status-badge {{ $badgeClass }}">{{ ucfirst($item->status) }}</span>
                        </td>
                        <td onclick="event.stopPropagation()">
                        <div class="aksi-wrapper">
                            @if($item->status === 'diproses')
                                <form id="form-approve-{{ $item->id }}" action="{{ route('admin.bank-sampah.penjemputan.approve', $item->id) }}" method="POST" style="display: inline;">
                                    @csrf @method('PATCH')
                                    <button type="button" class="btn-approve" title="Setujui" onclick="showConfirm('approve', {{ $item->id }})">
                                        <i class="fas fa-check"></i>
                                    </button>
                                </form>

                                <form id="form-reject-{{ $item->id }}" action="{{ route('admin.bank-sampah.penjemputan.reject', $item->id) }}" method="POST" style="display: inline;">
                                    @csrf @method('DELETE')
                                    <button type="button" class="btn-reject" title="Tolak" onclick="showConfirm('reject', {{ $item->id }})">
                                        <i class="fas fa-times"></i>
                                    </button>
                                </form>
                            @else
                                <span class="aksi-selesai">✓ Selesai</span>
                            @endif
                        </div>
                    </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="7">
                            <div class="empty-state">
                                <i class="fas fa-inbox" style="font-size:28px; margin-bottom:8px; display:block;"></i>
                                Tidak ada data penjemputan.
                            </div>
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

    </main>
</div>

<!-- Modal Detail -->
<div id="detailModal" class="modal-overlay" style="display: none;">
    <div class="modal-container">
        <div class="modal-header">
            <h3>Detail Penjemputan</h3>
            <button class="modal-close" onclick="closeModal()">&times;</button>
        </div>
        <div class="modal-body">
            <div class="detail-content">
                <div class="detail-image">
                    <img id="modalFoto" src="" alt="Foto Penjemputan">
                </div>
                <div class="detail-form">
                    <div class="form-group"><label>No</label><input type="text" id="modalNo" readonly></div>
                    <div class="form-group"><label>Nama Admin</label><input type="text" id="modalNamaAdmin" readonly></div>
                    <div class="form-group"><label>Waktu</label><input type="text" id="modalWaktu" readonly></div>
                    <div class="form-group"><label>Berat</label><input type="text" id="modalBerat" readonly></div>
                    <div class="form-group"><label>Lokasi</label><input type="text" id="modalLokasi" readonly></div>
                    <div class="form-group"><label>Keterangan</label><textarea id="modalKeterangan" rows="3" readonly></textarea></div>
                </div>
            </div>
        </div>
        <div class="modal-footer">
            <button class="btn-tutup" onclick="closeModal()">Tutup</button>
        </div>
    </div>
</div>

<!-- Modal Konfirmasi -->
<div id="confirmModal" class="modal-overlay" style="display: none;">
    <div class="modal-container" style="max-width: 400px;">
        <div class="modal-header">
            <h3 id="confirmTitle">Konfirmasi</h3>
            <button class="modal-close" onclick="closeConfirmModal()">&times;</button>
        </div>
        <div class="modal-body" style="text-align: center; padding: 30px 24px;">
            <p id="confirmMessage" style="font-size: 15px; color: #555; margin-bottom: 0;"></p>
        </div>
        <div class="modal-footer" style="display: flex; gap: 12px; justify-content: center; padding: 16px 24px 24px;">
            <button class="btn-tutup" onclick="closeConfirmModal()" style="width: 100%;">Batal</button>
            <button id="confirmYesBtn" style="width: 100%; padding: 10px; border-radius: 8px; border: none; cursor: pointer; font-weight: 600; color: white; transition: background 0.2s;">
                Ya, Lanjutkan
            </button>
        </div>
    </div>
</div>

<script>
// ================= SEARCH FUNCTION =================
function searchTable() {
    const input = document.getElementById('searchInput');
    const filter = input.value.toLowerCase();
    const table = document.getElementById('penjemputanTable');
    const tr = table.getElementsByTagName('tr');
    for (let i = 1; i < tr.length; i++) {
        const tdNamaAdmin = tr[i].getElementsByTagName('td')[2];
        const tdWaktu = tr[i].getElementsByTagName('td')[3];
        const tdStatus = tr[i].getElementsByTagName('td')[5];
        if (tdNamaAdmin || tdWaktu || tdStatus) {
            const namaValue = tdNamaAdmin.textContent || tdNamaAdmin.innerText;
            const waktuValue = tdWaktu.textContent || tdWaktu.innerText;
            const statusValue = tdStatus.textContent || tdStatus.innerText;
            if (namaValue.toLowerCase().indexOf(filter) > -1 || waktuValue.toLowerCase().indexOf(filter) > -1 || statusValue.toLowerCase().indexOf(filter) > -1) {
                tr[i].style.display = '';
            } else { tr[i].style.display = 'none'; }
        }
    }
}
// ================= MODAL DETAIL =================
function showDetail(id) {
    fetch(`/admin/bank-sampah/penjemputan/${id}/detail`)
        .then(response => response.json())
        .then(data => {
            document.getElementById('modalNo').value = data.id;
            document.getElementById('modalNamaAdmin').value = data.nama_admin;
            document.getElementById('modalWaktu').value = new Date(data.waktu).toLocaleString('id-ID', { day:'2-digit', month:'2-digit', year:'numeric', hour:'2-digit', minute:'2-digit', second:'2-digit' });
            document.getElementById('modalBerat').value = parseFloat(data.berat).toFixed(2) + ' Kg';
            document.getElementById('modalLokasi').value = data.lokasi || '-';
            document.getElementById('modalKeterangan').value = data.keterangan || '-';
            const imgUrl = data.foto ? `/storage/${data.foto}` : '/images/no-image.png';
            document.getElementById('modalFoto').src = imgUrl;
            document.getElementById('detailModal').style.display = 'flex';
        })
        .catch(error => { console.error('Error:', error); alert('Gagal mengambil data detail.'); });
}
function closeModal() { document.getElementById('detailModal').style.display = 'none'; }
// ================= MODAL KONFIRMASI =================
let pendingForm = null;
function showConfirm(type, id) {
    const modal = document.getElementById('confirmModal');
    const title = document.getElementById('confirmTitle');
    const msg = document.getElementById('confirmMessage');
    const btn = document.getElementById('confirmYesBtn');
    const formId = type === 'approve' ? `form-approve-${id}` : `form-reject-${id}`;
    pendingForm = document.getElementById(formId);
    if (!pendingForm) return;
    if (type === 'approve') {
        title.innerText = 'Konfirmasi Penjemputan';
        msg.innerHTML = 'Apakah Anda yakin ingin <b>menyetujui</b> penjemputan ini?<br>Data akan ditandai sebagai Berhasil.';
        btn.innerText = 'Ya, Setujui'; btn.style.background = '#43a047';
    } else {
        title.innerText = 'Konfirmasi Penolakan';
        msg.innerHTML = 'Apakah Anda yakin ingin <b>menolak</b> penjemputan ini?<br>Data akan ditandai sebagai Ditolak.';
        btn.innerText = 'Ya, Tolak'; btn.style.background = '#e53935';
    }
    modal.style.display = 'flex';
}
function closeConfirmModal() { document.getElementById('confirmModal').style.display = 'none'; pendingForm = null; }
document.getElementById('confirmYesBtn').addEventListener('click', function() { if (pendingForm) pendingForm.submit(); });
// ================= GLOBAL CLICK & KEYBOARD =================
window.onclick = function(event) {
    if (event.target === document.getElementById('detailModal')) closeModal();
    if (event.target === document.getElementById('confirmModal')) closeConfirmModal();
}
document.addEventListener('keydown', function(event) {
    if (event.key === 'Escape') { closeModal(); closeConfirmModal(); }
});
</script>
@endsection