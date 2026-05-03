@extends('layouts.penjemputan')
@section('title', 'Daftar Penjemputan')

@section('content')
<div class="content-card">
    <h2>Daftar Penjemputan</h2>
    
    <table>
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
                        {{-- Tombol Setujui --}}
                        <form id="form-approve-{{ $item->id }}" action="{{ route('admin.bank-sampah.penjemputan.approve', $item->id) }}" method="POST" style="display: inline;">
                            @csrf @method('PATCH')
                            <button type="button" class="btn-approve" title="Setujui" onclick="showConfirm('approve', {{ $item->id }})">
                                <i class="fas fa-check"></i>
                            </button>
                        </form>

                        {{-- Tombol Tolak --}}
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
                    <div class="form-group">
                        <label>No</label>
                        <input type="text" id="modalNo" readonly>
                    </div>
                    
                    <div class="form-group">
                        <label>Nama Admin</label>
                        <input type="text" id="modalNamaAdmin" readonly>
                    </div>
                    
                    <div class="form-group">
                        <label>Waktu</label>
                        <input type="text" id="modalWaktu" readonly>
                    </div>
                    
                    <div class="form-group">
                        <label>Berat</label>
                        <input type="text" id="modalBerat" readonly>
                    </div>
                    
                    <div class="form-group">
                        <label>Lokasi</label>
                        <input type="text" id="modalLokasi" readonly>
                    </div>
                    
                    <div class="form-group">
                        <label>Keterangan</label>
                        <textarea id="modalKeterangan" rows="3" readonly></textarea>
                    </div>
                </div>
            </div>
        </div>
        
        <div class="modal-footer">
            <button class="btn-tutup" onclick="closeModal()">Tutup</button>
        </div>
    </div>
</div>


<!-- Modal Konfirmasi Approve/Reject -->
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
// ================= MODAL DETAIL =================
function showDetail(id) {
    fetch(`/admin/bank-sampah/penjemputan/${id}/detail`)
        .then(response => response.json())
        .then(data => {
            document.getElementById('modalNo').value = data.id;
            document.getElementById('modalNamaAdmin').value = data.nama_admin;
            document.getElementById('modalWaktu').value = new Date(data.waktu).toLocaleString('id-ID', {
                day: '2-digit', month: '2-digit', year: 'numeric',
                hour: '2-digit', minute: '2-digit', second: '2-digit'
            });
            document.getElementById('modalBerat').value = parseFloat(data.berat).toFixed(2) + ' Kg';
            document.getElementById('modalLokasi').value = data.lokasi || '-';
            document.getElementById('modalKeterangan').value = data.keterangan || '-';
            
            const imgUrl = data.foto ? `/storage/${data.foto}` : '/images/no-image.png';
            document.getElementById('modalFoto').src = imgUrl;
            
            document.getElementById('detailModal').style.display = 'flex';
        })
        .catch(error => {
            console.error('Error:', error);
            alert('Gagal mengambil data detail.');
        });
}

function closeModal() {
    document.getElementById('detailModal').style.display = 'none';
}

// ================= MODAL KONFIRMASI =================
let pendingForm = null;

function showConfirm(type, id) {
    const modal = document.getElementById('confirmModal');
    const title = document.getElementById('confirmTitle');
    const msg = document.getElementById('confirmMessage');
    const btn = document.getElementById('confirmYesBtn');
    
    // Cari form yang sesuai dengan id
    const formId = type === 'approve' ? `form-approve-${id}` : `form-reject-${id}`;
    pendingForm = document.getElementById(formId);
    
    if (!pendingForm) return;

    if (type === 'approve') {
        title.innerText = 'Konfirmasi Penjemputan';
        msg.innerHTML = 'Apakah Anda yakin ingin <b>menyetujui</b> penjemputan ini?<br>Data akan ditandai sebagai Berhasil.';
        btn.innerText = 'Ya, Setujui';
        btn.style.background = '#43a047'; // Hijau
        btn.onmouseover = () => btn.style.background = '#2e7d32';
        btn.onmouseout = () => btn.style.background = '#43a047';
    } else {
        title.innerText = 'Konfirmasi Penolakan';
        msg.innerHTML = 'Apakah Anda yakin ingin <b>menolak</b> penjemputan ini?<br>Data akan ditandai sebagai Ditolak.';
        btn.innerText = 'Ya, Tolak';
        btn.style.background = '#e53935'; // Merah
        btn.onmouseover = () => btn.style.background = '#b71c1c';
        btn.onmouseout = () => btn.style.background = '#e53935';
    }
    
    modal.style.display = 'flex';
}

function closeConfirmModal() {
    document.getElementById('confirmModal').style.display = 'none';
    pendingForm = null;
}

document.getElementById('confirmYesBtn').addEventListener('click', function() {
    if (pendingForm) {
        pendingForm.submit(); // Submit form asli Laravel
    }
});

// ================= GLOBAL CLICK & KEYBOARD =================
window.onclick = function(event) {
    const detailModal = document.getElementById('detailModal');
    const confirmModal = document.getElementById('confirmModal');
    
    if (event.target === detailModal) closeModal();
    if (event.target === confirmModal) closeConfirmModal();
}

document.addEventListener('keydown', function(event) {
    if (event.key === 'Escape') {
        closeModal();
        closeConfirmModal();
    }
});
</script>
@endsection