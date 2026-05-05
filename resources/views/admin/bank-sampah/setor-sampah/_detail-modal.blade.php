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
        
        // Tampilkan loading
        modalBody.innerHTML = `<div class="modal-loading"><div class="spinner"></div><p>Memuat data...</p></div>`;
        modal.style.display = 'flex';
        document.body.style.overflow = 'hidden';
        
        // ✅ ENDPOINT YANG BENAR (Sesuai Route)
        fetch(`/admin/bank-sampah/setor/${id}`, {
            headers: {
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.content || '',
                'Accept': 'application/json'
            }
        })
        .then(res => {
            if (!res.ok) throw new Error('Data tidak ditemukan');
            return res.json();
        })
        .then(data => {
            // Debug Console (Bisa dihapus nanti jika sudah stabil)
            console.log('✅ Data dari server:', data);
            console.log('🗑️ Jenis sampah object:', data.jenisSampah);
            
            const content = template.content.cloneNode(true);
            modalBody.innerHTML = '';
            modalBody.appendChild(content);
            
            // Helper function aman
            const setValue = (id, value) => {
                const el = document.getElementById(id);
                if (el) el.value = value ?? '-';
            };
            
            setValue('d_no', data.id_transaksi);
            
            // Nama
            const namaPengsetor = data.masyarakat?.nama || data.pns?.nama || '-';
            setValue('d_nama', namaPengsetor);
            
            // Tipe
            const tipePengsetor = data.masyarakat ? 'Masyarakat' : (data.pns ? 'PNS' : '-');
            setValue('d_pekerjaan', tipePengsetor);
            
            // ✅ JENIS SAMPAH (Fallback lengkap)
            const jenisObj = data.jenisSampah || data.jenis_sampah || {};
            const jenisNama = jenisObj.jenis || jenisObj.nama || '-';
            console.log('🏷️ Jenis yang ditampilkan:', jenisNama);
            setValue('d_jenis', jenisNama);
            
            // Berat
            const berat = data.berat ? parseFloat(data.berat).toFixed(2) + ' Kg' : '-';
            setValue('d_berat', berat);
            
            // Harga
            const harga = data.harga_per_kg ? 'Rp ' + formatRupiah(data.harga_per_kg) : '-';
            setValue('d_harga', harga);
            
            // Total
            const total = data.total_rupiah ? 'Rp ' + formatRupiah(data.total_rupiah) : '-';
            setValue('d_total', total);
            
            // Petugas
            setValue('d_petugas', data.petugas?.nama_lengkap);
            
            // Waktu
            const waktu = data.tanggal_transaksi ? new Date(data.tanggal_transaksi).toLocaleString('id-ID') : '-';
            setValue('d_waktu', waktu);
        })
        .catch(err => {
            console.error('❌ Error:', err);
            modalBody.innerHTML = `
                <div style="text-align:center;color:#e74c3c;padding:30px 20px;">
                    <i class="fas fa-exclamation-triangle" style="font-size:2.5rem;margin-bottom:15px;opacity:0.7;"></i>
                    <p style="margin:0;font-weight:500;">Gagal memuat data</p>
                    <small style="color:#888;">${err.message}</small>
                </div>`;
        });
    }
    
    function closeDetailModal() {
        const modal = document.getElementById('detailModal');
        if (modal) {
            modal.style.display = 'none';
            document.body.style.overflow = '';
        }
    }
    
    function formatRupiah(angka) {
        if (!angka && angka !== 0) return '-';
        return new Intl.NumberFormat('id-ID').format(angka);
    }
    
    // Keyboard & Click outside
    document.addEventListener('keydown', function(e) {
        if (e.key === 'Escape') {
            const modal = document.getElementById('detailModal');
            if (modal && modal.style.display === 'flex') closeDetailModal();
        }
    });
    
    document.addEventListener('click', function(e) {
        const modalBox = document.querySelector('.modal-box');
        const modal = document.getElementById('detailModal');
        if (modalBox && modal && modal.style.display === 'flex') {
            if (!modalBox.contains(e.target) && e.target.classList.contains('modal-backdrop')) {
                closeDetailModal();
            }
        }
    });
</script>