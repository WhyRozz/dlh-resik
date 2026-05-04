@extends('layouts.admin')

@section('title', 'Data Setor - Bank Sampah')

@push('styles')
<link rel="stylesheet" href="{{ asset('css/setor.css') }}">
@endpush

@section('content')
{{-- Header dengan Search di Pojok Kanan --}}
<div class="top-header">
    <div class="header-title">
        <h2>Data Setor Sampah</h2>
    </div>
    <div class="header-search">
        <form action="{{ route('admin.bank-sampah.setor.index') }}" method="GET" class="search-wrapper">
            <input type="text" name="search" placeholder="Cari berdasarkan nama..." value="{{ request('search') }}">
            <button type="submit"><i class="fas fa-search"></i></button>
        </form>
    </div>
</div>

{{-- Data Table Container --}}
<div class="data-table-container">
    <table class="data-table">
        <thead>
            <tr>
                <th width="5%">No</th>
                <th width="22%">Nama</th>
                <th width="15%">Waktu Setor</th>
                <th width="13%">Pekerjaan</th>
                <th width="15%">Jenis</th>
                <th width="10%">Berat</th>
                <th width="15%">Harga</th>
                <th width="10%">Petugas</th>
            </tr>
        </thead>
        {{-- Ganti bagian <tbody> dengan ini --}}
<tbody>
    @forelse($setorData as $index => $row)
    <tr>
        <td>{{ $setorData->firstItem() + $index }}</td>
        <td>
            <div class="user-info">
                {{-- ✅ PERBAIKAN: nama (bukan nama_lengkap) --}}
                <strong>{{ $row->nama_pengsetor }}</strong>
                <small>
                    @if($row->id_masyarakat)
                        <span class="badge badge-info">Masyarakat</span>
                    @elseif($row->id_pns)
                        <span class="badge badge-success">PNS</span>
                    @endif
                </small>
            </div>
        </td>
        <td>{{ $row->tanggal_transaksi->format('d/m/Y H:i') }}</td>
        {{-- ✅ HAPUS kolom Pekerjaan atau ganti dengan data yang ada --}}
        <td><span class="badge badge-info">{{ $row->tipe_pengsetor }}</span></td>
        {{-- ✅ PERBAIKAN: jenis (bukan nama) --}}
        <td><span class="badge badge-success">{{ $row->jenisSampah->jenis ?? 'N/A' }}</span></td>
        <td><strong>{{ number_format($row->berat, 2) }} Kg</strong></td>
        <td>
            <div class="text-price">
                Rp {{ number_format($row->total_rupiah, 0, ',', '.') }}
                <small>@ Rp {{ number_format($row->harga_per_kg, 0, ',', '.') }}/kg</small>
            </div>
        </td>
        {{-- ✅ TAMPILKAN NAMA PETUGAS --}}
        <td>
            <div class="user-info">
                <strong>{{ $row->petugas->nama_lengkap ?? '-' }}</strong>
                <small><i class="fas fa-user-check"></i> Petugas</small>
            </div>
        </td>
        <td>
            <button class="btn-action" onclick="openDetailModal({{ $row->id_transaksi }})">
                <i class="fas fa-eye"></i> Detail
            </button>
        </td>
    </tr>
    @empty
    <tr>
        <td colspan="9">
            <div class="empty-state">
                <i class="fas fa-inbox"></i>
                <p>Belum ada data setor sampah</p>
            </div>
        </td>
    </tr>
    @endforelse
</tbody>
    </table>
    
    @if($setorData->hasPages())
    <div class="pagination-wrapper">
        {{ $setorData->appends(request()->query())->links('pagination::simple-bootstrap-4') }}
    </div>
    @endif
</div>

{{-- Modal Detail (tetap sama) --}}
@include('admin.bank-sampah.setor-sampah._detail-modal')
@endsection

@push('scripts')
<script>
    function openDetailModal(id) {
        const modal = document.getElementById('detailModal');
        const modalBody = document.getElementById('detailModalBody');
        const template = document.getElementById('detailModalTemplate');
        
        modalBody.innerHTML = `<div class="modal-loading"><div class="spinner"></div><p>Memuat data...</p></div>`;
        modal.style.display = 'flex';
        document.body.style.overflow = 'hidden';
        
        fetch(`/admin/bank-sampah/setor/${id}`, {
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
    
    // Nama nasabah (dari masyarakat atau pns)
    const namaPengsetor = data.masyarakat?.nama || data.pns?.nama || '-';
    document.getElementById('d_nama').value = namaPengsetor;
    
    // Pekerjaan / Tipe
    const tipePengsetor = data.masyarakat ? 'Masyarakat' : (data.pns ? 'PNS' : '-');
    document.getElementById('d_pekerjaan').value = tipePengsetor;
    
    // Jenis sampah
    document.getElementById('d_jenis').value = data.jenisSampah?.jenis || '-';
    
    // Berat
    document.getElementById('d_berat').value = data.berat ? data.berat + ' Kg' : '-';
    
    // Harga
    document.getElementById('d_harga').value = data.harga_per_kg ? 'Rp ' + formatRupiah(data.harga_per_kg) : '-';
    
    // Total
    document.getElementById('d_total').value = data.total_rupiah ? 'Rp ' + formatRupiah(data.total_rupiah) : '-';
    
    // Petugas
    document.getElementById('d_petugas').value = data.petugas?.nama_lengkap || '-';
    
    // Waktu
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
@endpush