{{-- Popup Modal Detail Setor Sampah --}}
<div id="detailModal" class="modal-popup" style="display: none;">
    <div class="modal-backdrop" onclick="closeDetailModal()"></div>
    <div class="modal-box">

        <div class="modal-header">
            <h3> Detail Setor Sampah</h3>
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

        {{-- ✅ SECTION LOKASI (Ditampilkan berdasarkan tipe pengsetor) --}}
        <div id="d_lokasi_section" style="display: none;">
            {{-- Baris 1: Kecamatan & Desa --}}
            <div class="form-row" id="d_wilayah_row" style="display: none;">
                <div class="form-group">
                    <label><i class="fas fa-map-marker-alt"></i> Kecamatan</label>
                    <input type="text" id="d_kecamatan" readonly>
                </div>
                <div class="form-group">
                    <label><i class="fas fa-home"></i> Desa</label>
                    <input type="text" id="d_desa" readonly>
                </div>
            </div>

            {{-- Baris 2: Dinas (Full Width) --}}
            <div class="form-group full" id="d_dinas_group" style="display: none; margin-top: 15px;">
                <label><i class="fas fa-building"></i> Dinas/Instansi</label>
                <input type="text" id="d_dinas" readonly>
            </div>
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
            <div class="form-group full">
                <label><i class="fas fa-user-check"></i> Petugas & Wilayah Kerja</label>
                <input type="text" id="d_petugas" readonly>
            </div>
            <div class="form-group">
                <label><i class="fas fa-clock"></i> Waktu</label>
                <input type="text" id="d_waktu" readonly>
            </div>
        </div>
    </div>
</template>
