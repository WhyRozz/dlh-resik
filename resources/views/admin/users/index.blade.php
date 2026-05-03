@extends('layouts.admin')

@section('title', 'Data Pengguna')

@section('content')
<div class="page-header">
    <div>
        <h2>Daftar Data Pengguna</h2>
        <p class="text-muted">Kelola data pengguna ASN dan Masyarakat</p>
    </div>
    <div class="header-actions">
        <a href="{{ route('admin.data-pengguna.export', ['filter' => $filter]) }}" class="btn-export">
            <i class="fas fa-file-excel"></i> Export Excel
        </a>
    </div>
</div>

<!-- Filter Buttons -->
<div class="filter-group">
    <a href="{{ route('admin.data-pengguna.index', ['filter' => 'all']) }}"
       class="filter-btn {{ $filter == 'all' ? 'active' : '' }}">
        <i class="fas fa-users"></i> Semua ({{ \App\Models\Masyarakat::count() }})
    </a>
    <a href="{{ route('admin.data-pengguna.index', ['filter' => 'asn']) }}"
       class="filter-btn {{ $filter == 'asn' ? 'active' : '' }}">
        <i class="fas fa-building"></i> ASN / PNS
    </a>
    <a href="{{ route('admin.data-pengguna.index', ['filter' => 'masyarakat']) }}"
       class="filter-btn {{ $filter == 'masyarakat' ? 'active' : '' }}">
        <i class="fas fa-user-friends"></i> Masyarakat
    </a>
</div>

<!-- Tabel Data -->
<div class="table-container">
    <table>
        <thead>
            <tr>
                <th>No</th>
                <th>Nama Pengguna</th>
                <th>Email</th>
                <th>Jenis Kelamin</th>
                <th>No Telp</th>
                <th>Pekerjaan</th>
                <th>Saldo</th>
                <th>Aksi</th>
            </tr>
        </thead>
        <tbody>
            @forelse($users as $key => $user)
            <tr>
                <td>{{ $users->firstItem() + $key }}</td>
                <td>{{ $user->nama }}</td>
                <td>{{ $user->email }}</td>
                <td>
                    @if($user->jenis_kelamin)
                        <span class="badge {{ $user->jenis_kelamin == 'Laki-laki' ? 'badge-blue' : 'badge-pink' }}">
                            {{ $user->jenis_kelamin }}
                        </span>
                    @else
                        <span class="text-muted">-</span>
                    @endif
                </td>
                <td>{{ $user->no_telp ?? '-' }}</td>
                <td>
                    <span class="badge {{ str_contains(strtolower($user->pekerjaan ?? ''), 'dinas') ? 'badge-asn' : 'badge-masyarakat' }}">
                        {{ $user->pekerjaan ?? 'Masyarakat' }}
                    </span>
                </td>
                <td><strong>Rp {{ number_format($user->saldo_bank_sampah, 0, ',', '.') }}</strong></td>
                <td>
                    <button class="btn-icon" onclick="openModal({{ $user->id_masyarakat }})">
                        <i class="fas fa-eye"></i> Detail
                    </button>
                </td>
            </tr>
            @empty
            <tr>
                <td colspan="8" class="text-center py-4">
                    Tidak ada data pengguna untuk kategori ini.
                </td>
            </tr>
            @endforelse
        </tbody>
    </table>

    <div class="pagination-wrapper">
        {{ $users->links() }}
    </div>
</div>

<!-- Modal Detail -->
<div id="userModal" class="modal-overlay">
    <div class="modal-card">
        <div class="modal-header">
            <h3>Detail Pengguna</h3>
            <button class="close-btn" onclick="closeModal()">&times;</button>
        </div>
        <div class="modal-body">
            <div class="user-grid">
                <div class="info-item">
                    <label>Nama Lengkap</label>
                    <span id="modalNama">-</span>
                </div>
                <div class="info-item">
                    <label>Jenis Kelamin</label>
                    <span id="modalJenisKelamin">-</span>
                </div>
                <div class="info-item">
                    <label>Email</label>
                    <span id="modalEmail">-</span>
                </div>
                <div class="info-item">
                    <label>No Telepon</label>
                    <span id="modalTelp">-</span>
                </div>
                <div class="info-item">
                    <label>Pekerjaan</label>
                    <span id="modalPekerjaan">-</span>
                </div>
                <div class="info-item">
                    <label>Saldo Bank Sampah</label>
                    <span id="modalSaldo" style="color: #2e8b57; font-weight: bold;">-</span>
                </div>
                <div class="info-item full-width">
                    <label>Alamat</label>
                    <span id="modalAlamat">-</span>
                </div>
                <div class="info-item full-width">
                    <label>QR Code</label>
                    <div id="modalQrCode" style="margin-top: 10px;"></div>
                </div>
            </div>
        </div>
        <div class="modal-footer">
            <button class="btn-primary" onclick="closeModal()">Tutup</button>
        </div>
    </div>
</div>
@endsection

@push('styles')
<style>
    .page-header { display: flex; justify-content: space-between; align-items: center; margin-bottom: 20px; flex-wrap: wrap; gap: 10px; }
    .text-muted { color: #666; font-size: 14px; margin-top: 4px; }

    /* Filter Buttons */
    .filter-group { display: flex; gap: 10px; margin-bottom: 20px; flex-wrap: wrap; }
    .filter-btn {
        padding: 10px 20px; border-radius: 25px; background: #f0f0f0; color: #555;
        text-decoration: none; font-size: 14px; font-weight: 600; transition: all 0.2s;
        display: flex; align-items: center; gap: 8px; border: 2px solid transparent;
    }
    .filter-btn:hover { background: #e0e0e0; transform: translateY(-1px); }
    .filter-btn.active {
        background: #2e8b57; color: white; border-color: #2e8b57;
        box-shadow: 0 4px 10px rgba(46,139,87,0.2);
    }

    /* Export Button */
    .btn-export {
        background: #1b5e20; color: white; padding: 10px 20px; border-radius: 25px;
        text-decoration: none; font-size: 14px; font-weight: bold; display: inline-flex;
        align-items: center; gap: 8px; transition: 0.2s;
    }
    .btn-export:hover { background: #144a18; transform: translateY(-1px); }

    /* Badges */
    .badge { padding: 5px 10px; border-radius: 12px; font-size: 12px; font-weight: 600; }
    .badge-blue { background: #e3f2fd; color: #1565c0; }
    .badge-pink { background: #fce4ec; color: #c2185b; }
    .badge-asn { background: #e3f2fd; color: #1565c0; }
    .badge-masyarakat { background: #e8f5e9; color: #2e7d32; }

    /* Table */
    .table-container { background: white; border-radius: 12px; padding: 20px; box-shadow: 0 2px 10px rgba(0,0,0,0.05); overflow-x: auto; }
    table { width: 100%; border-collapse: collapse; }
    th, td { padding: 12px; text-align: left; border-bottom: 1px solid #eee; font-size: 14px; }
    th { background: #f8f9fa; color: #555; font-weight: 600; }
    .btn-icon { background: none; border: none; cursor: pointer; color: #2e8b57; font-size: 14px; font-weight: 600; }
    .py-4 { padding-top: 1rem; padding-bottom: 1rem; }

    /* Modal */
    .modal-overlay { display: none; position: fixed; top: 0; left: 0; width: 100%; height: 100%; background: rgba(0,0,0,0.5); z-index: 2000; justify-content: center; align-items: center; }
    .modal-overlay.active { display: flex; }
    .modal-card { background: white; width: 90%; max-width: 600px; border-radius: 15px; overflow: hidden; animation: slideUp 0.3s ease; }
    .modal-header { padding: 15px 20px; background: #f0f7f4; display: flex; justify-content: space-between; align-items: center; border-bottom: 1px solid #e0e0e0; }
    .modal-header h3 { margin: 0; color: #333; font-size: 16px; }
    .close-btn { background: none; border: none; font-size: 24px; cursor: pointer; }
    .modal-body { padding: 20px; }
    .user-grid { display: grid; grid-template-columns: 1fr 1fr; gap: 15px; }
    .info-item.full-width { grid-column: span 2; }
    .info-item label { display: block; font-size: 12px; color: #888; margin-bottom: 4px; }
    .info-item span { font-size: 14px; color: #333; font-weight: 500; }
    .modal-footer { padding: 15px 20px; border-top: 1px solid #eee; display: flex; justify-content: flex-end; }
    @keyframes slideUp { from { transform: translateY(20px); opacity: 0; } to { transform: translateY(0); opacity: 1; } }

    @media (max-width: 768px) {
        .page-header { flex-direction: column; align-items: flex-start; }
        .filter-group { width: 100%; }
        .filter-btn { flex: 1; justify-content: center; }
        .user-grid { grid-template-columns: 1fr; }
        .info-item.full-width { grid-column: span 1; }
    }
</style>
@endpush

@push('scripts')
<script>
    function openModal(userId) {
        // Fetch data user via AJAX (contoh statis, nanti ganti dengan fetch)
        document.getElementById('userModal').classList.add('active');

        // Contoh: nanti pakai fetch(`/api/admin/users/${userId}`)
        // Untuk sekarang hardcoded
        document.getElementById('modalNama').textContent = 'Loading...';
    }

    function closeModal() {
        document.getElementById('userModal').classList.remove('active');
    }
</script>
@endpush
