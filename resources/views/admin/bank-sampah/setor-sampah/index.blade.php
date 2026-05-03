@extends('layouts.admin')

@section('title', 'Data Setor - Bank Sampah')

@push('styles')
<link rel="stylesheet" href="{{ asset('css/setor.css') }}">
@endpush

@section('content')
{{-- Page Header --}}
<div class="page-header">
    <div>
        <h2><i class="fas fa-recycle"></i> Data Setor Sampah</h2>
        <nav class="breadcrumb">
            <a href="{{ route('admin.dashboard') }}">Beranda</a> / 
            <a href="#">Bank Sampah</a> / 
            <span>Data Setor</span>
        </nav>
    </div>
    <button class="btn-action" onclick="window.print()">
        <i class="fas fa-print"></i> Cetak
    </button>
</div>

{{-- Stats Cards --}}
<div class="stats-grid">
    <div class="stat-card">
        <h3>{{ $totalSetor ?? 0 }}</h3>
        <p>Total Transaksi</p>
    </div>
    <div class="stat-card blue">
        <h3>{{ number_format($totalBerat ?? 0, 2) }} Kg</h3>
        <p>Total Berat</p>
    </div>
    <div class="stat-card yellow">
        <h3>Rp {{ number_format($totalNilai ?? 0, 0, ',', '.') }}</h3>
        <p>Total Nilai</p>
    </div>
    <div class="stat-card red">
        <h3>{{ $totalNasabah ?? 0 }}</h3>
        <p>Total Nasabah</p>
    </div>
</div>

{{-- Filter Box --}}
<div class="filter-box">
    <form action="{{ route('admin.bank-sampah.setor') }}" method="GET">
        <div class="filter-row">
            <div class="filter-item">
                <label>Cari Nama / Lokasi</label>
                <input type="text" name="search" placeholder="Ketik untuk mencari..." value="{{ request('search') }}">
            </div>
            <div class="filter-item">
                <label>Jenis Sampah</label>
                <select name="jenis_sampah">
                    <option value="">Semua Jenis</option>
                    @foreach($jenisSampah as $js)
                    <option value="{{ $js->id }}" {{ request('jenis_sampah') == $js->id ? 'selected' : '' }}>
                        {{ $js->nama }}
                    </option>
                    @endforeach
                </select>
            </div>
            <div class="filter-item">
                <label>Dari Tanggal</label>
                <input type="date" name="tanggal_from" value="{{ request('tanggal_from') }}">
            </div>
            <div class="filter-item">
                <label>Sampai Tanggal</label>
                <input type="date" name="tanggal_to" value="{{ request('tanggal_to') }}">
            </div>
            <div class="filter-item">
                <button type="submit" class="btn-search">
                    <i class="fas fa-search"></i> Cari
                </button>
            </div>
        </div>
    </form>
</div>

{{-- Data Table --}}
<div class="data-table-container">
    <div class="table-header">
        <h3><i class="fas fa-list"></i> Daftar Setor</h3>
        <div class="table-actions">
            <button onclick="exportCSV()"><i class="fas fa-file-csv"></i> Export</button>
        </div>
    </div>
    
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
                <th width="10%">Aksi</th>
            </tr>
        </thead>
        <tbody>
            @forelse($setorData as $index => $row)
            <tr>
                <td>{{ $setorData->firstItem() + $index }}</td>
                <td>
                    <div class="user-info">
                        <strong>{{ $row->masyarakat->nama_lengkap ?? 'N/A' }}</strong>
                        <small>{{ $row->masyarakat->email ?? '-' }}</small>
                    </div>
                </td>
                <td>{{ $row->tanggal_transaksi->format('d/m/Y H:i') }}</td>
                <td><span class="badge badge-info">{{ $row->masyarakat->pekerjaan ?? '-' }}</span></td>
                <td><span class="badge badge-success">{{ $row->jenisSampah->nama ?? 'N/A' }}</span></td>
                <td><strong>{{ number_format($row->berat, 2) }} Kg</strong></td>
                <td>
                    <div class="text-price">
                        Rp {{ number_format($row->total_rupiah, 0, ',', '.') }}
                        <small>@ Rp {{ number_format($row->harga_per_kg, 0, ',', '.') }}/kg</small>
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
                <td colspan="8">
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

{{-- Include Popup Modal Detail --}}
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
            document.getElementById('d_nama').value = data.masyarakat?.nama_lengkap || '-';
            document.getElementById('d_email').value = data.masyarakat?.email || '-';
            document.getElementById('d_pekerjaan').value = data.masyarakat?.pekerjaan || '-';
            document.getElementById('d_alamat').value = data.masyarakat?.alamat || '-';
            document.getElementById('d_jenis').value = data.jenis_sampah?.nama || '-';
            document.getElementById('d_kategori').value = data.jenis_sampah?.kategori || '-';
            document.getElementById('d_berat').value = data.berat ? data.berat + ' Kg' : '-';
            document.getElementById('d_harga').value = data.harga_per_kg ? 'Rp ' + formatRupiah(data.harga_per_kg) : '-';
            document.getElementById('d_total').value = data.total_rupiah ? 'Rp ' + formatRupiah(data.total_rupiah) : '-';
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
    
    function exportCSV() { alert('Fitur export akan segera tersedia!'); }
    
    document.addEventListener('keydown', function(e) {
        if (e.key === 'Escape') {
            const modal = document.getElementById('detailModal');
            if (modal.style.display === 'flex') closeDetailModal();
        }
    });
    
    document.querySelector('.modal-box')?.addEventListener('click', function(e) { e.stopPropagation(); });
</script>
@endpush