{{-- Load CSS Khusus Modal --}}
<link rel="stylesheet" href="{{ asset('css/modal-detail.css') }}">

{{-- Popup Modal Detail Setor Sampah --}}
<div id="detailModal" class="modal-popup" style="display: none;">
    <div class="modal-backdrop" onclick="closeDetailModal()"></div>
    <div class="modal-box">
        
        <div class="modal-header">
            <h3><i class="fas fa-info-circle"></i> Detail Setor Sampah</h3>
            <button class="modal-close" onclick="closeDetailModal()" title="Tutup">
                <i class="fas fa-times"></i>
            </button>
        </div>
        
        <div class="modal-body" id="detailModalBody">
            <div class="modal-loading">
                <div class="spinner"></div>
                <p>Memuat data...</p>
            </div>
        </div>
        
        <div class="modal-footer">
            <button class="btn btn-secondary" onclick="closeDetailModal()">
                <i class="fas fa-times"></i> Tutup
            </button>
        </div>
        
    </div>
</div>

{{-- Template Konten (diisi via JS) --}}
<template id="detailModalTemplate">
    <div class="form-grid">
        <div class="form-group full">
            <label><i class="fas fa-hashtag"></i> No. Transaksi</label>
            <input type="text" id="d_no" readonly>
        </div>
        
        <div class="form-group full">
            <label><i class="fas fa-user"></i> Nama Nasabah</label>
            <input type="text" id="d_nama" readonly>
        </div>
        
        <div class="form-group full">
            <label><i class="fas fa-briefcase"></i> Pekerjaan</label>
            <input type="text" id="d_pekerjaan" readonly>
        </div>
        
        <div class="form-row">
            <div class="form-group">
                <label><i class="fas fa-recycle"></i> Jenis Sampah</label>
                <input type="text" id="d_jenis" readonly>
            </div>
            <div class="form-group">
                <label><i class="fas fa-weight"></i> Berat</label>
                <input type="text" id="d_berat" readonly>
            </div>
        </div>
        
        <div class="form-row">
            <div class="form-group">
                <label><i class="fas fa-coins"></i> Harga / Kg</label>
                <input type="text" id="d_harga" readonly>
            </div>
            <div class="form-group">
                <label><i class="fas fa-calculator"></i> Total Nilai</label>
                <input type="text" id="d_total" readonly class="highlight">
            </div>
        </div>
        
        <div class="form-row">
            <div class="form-group">
                <label><i class="fas fa-user-check"></i> Dicatat Oleh</label>
                <input type="text" id="d_petugas" readonly>
            </div>
            <div class="form-group">
                <label><i class="fas fa-clock"></i> Waktu</label>
                <input type="text" id="d_waktu" readonly>
            </div>
        </div>
    </div>
</template>

{{-- JavaScript Modal --}}
<script>
    function openDetailModal(id) {
        const modal = document.getElementById('detailModal');
        const modalBody = document.getElementById('detailModalBody');
        const template = document.getElementById('detailModalTemplate');
        
        modalBody.innerHTML = `<div class="modal-loading"><div class="spinner"></div><p>Memuat data...</p></div>`;
        modal.style.display = 'flex';
        document.body.style.overflow = 'hidden';
        
        fetch(`/admin/bank-sampah/setor-sampah/${id}`, {
            headers: {
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                'Accept': 'application/json'
            }
        })
        .then(res => res.json())
        .then(data => {
    const content = template.content.cloneNode(true);
    modalBody.innerHTML = '';
    modalBody.appendChild(content);
    
    document.getElementById('d_no').value = data.id_transaksi || '-';
    
    // ✅ PERBAIKAN: Ambil nama dari masyarakat atau pns
    const namaPengsetor = data.masyarakat?.nama || data.pns?.nama || '-';
    document.getElementById('d_nama').value = namaPengsetor;
    
    document.getElementById('d_email').value = data.masyarakat?.email || data.pns?.email || '-';
    
    // ✅ HAPUS/COMMENT pekerjaan jika tidak ada di DB
    // document.getElementById('d_pekerjaan').value = data.masyarakat?.pekerjaan || '-';
    document.getElementById('d_pekerjaan').value = data.tipe_pengsetor || '-';
    
    document.getElementById('d_alamat').value = data.masyarakat?.alamat || data.pns?.alamat || '-';
    
    // ✅ PERBAIKAN: jenisSampah (bukan jenis_sampah) dan kolom 'jenis' (bukan 'nama')
    document.getElementById('d_jenis').value = data.jenisSampah?.jenisdocument.getElementById('d_jenis').value = data.jenisSampah?.jenis || '-'; || '-';
    document.getElementById('d_kategori').value = data.jenisSampah?.satuan || '-';
    
    document.getElementById('d_berat').value = data.berat ? data.berat + ' Kg' : '-';
    document.getElementById('d_harga').value = data.harga_per_kg ? 'Rp ' + formatRupiah(data.harga_per_kg) : '-';
    document.getElementById('d_total').value = data.total_rupiah ? 'Rp ' + formatRupiah(data.total_rupiah) : '-';
    
    // ✅ Petugas
    document.getElementById('d_petugas').value = data.petugas?.nama_lengkap || '-';
    document.getElementById('d_waktu').value = data.tanggal_transaksi ? new Date(data.tanggal_transaksi).toLocaleString('id-ID') : '-';
})
        .catch(err => {
            modalBody.innerHTML = `<div style="text-align:center;color:#e74c3c;padding:30px 20px;"><i class="fas fa-exclamation-triangle" style="font-size:2.5rem;margin-bottom:15px;opacity:0.7;"></i><p style="margin:0;font-weight:500;">Gagal memuat data</p><small style="color:#888;">Silakan coba lagi</small></div>`;
            console.error('Error:', err);
        });
    }
    
    function closeDetailModal() {
        document.getElementById('detailModal').style.display = 'none';
        document.body.style.overflow = '';
    }
    
    function formatRupiah(angka) {
        return new Intl.NumberFormat('id-ID').format(angka);
    }
    
    document.addEventListener('keydown', function(e) {
        if (e.key === 'Escape') {
            const modal = document.getElementById('detailModal');
            if (modal.style.display === 'flex') closeDetailModal();
        }
    });
    
    document.querySelector('.modal-box')?.addEventListener('click', function(e) { e.stopPropagation(); });
</script>