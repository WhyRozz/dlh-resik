@extends('layouts.admin')

@section('title', 'RESIK - Data Pengguna')

@push('styles')
<link rel="stylesheet" href="{{ asset('css/data-pengguna.css') }}">
@endpush

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
        <i class="fas fa-users"></i> Semua
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
                    @if($user->jenis_pengguna === 'PNS')
                    <span class="badge badge-asn">
                        {{ $user->nama_dinas ?? 'ASN/PNS' }}
                    </span>
                    @else
                    <span class="badge badge-masyarakat">
                        Masyarakat
                    </span>
                    @endif
                </td>
                <td><strong>Rp {{ number_format($user->saldo, 0, ',', '.') }}</strong></td>
                <td>
                    @if($user->jenis_pengguna === 'PNS')
                    <button class="btn-icon" onclick="openModal({{ $user->id }}, 'pns')">
                        <i class="fas fa-eye"></i> Detail
                    </button>
                    @else
                    <button class="btn-icon" onclick="openModal({{ $user->id }}, 'masyarakat')">
                        <i class="fas fa-eye"></i> Detail
                    </button>
                    @endif
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
        {{ $users->appends(['filter' => $filter])->links('pagination.custom') }}
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
                <div class="info-item full-width">
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
                    <label>Tanggal Lahir</label>
                    <span id="modalTglLahir">-</span>
                </div>
                <div class="info-item">
                    <label>Pekerjaan/Dinas</label>
                    <span id="modalPekerjaan">-</span>
                </div>
                <div class="info-item">
                    <label>Kode Anggota</label>
                    <span id="modalKodeAnggota">-</span>
                </div>
                <div class="info-item">
                    <label>Saldo Bank Sampah</label>
                    <span id="modalSaldo" style="color: #2e8b57; font-weight: bold;">-</span>
                </div>
                <div class="info-item full-width">
                    <label>Terdaftar Sejak</label>
                    <span id="modalCreated">-</span>
                </div>
            </div>
        </div>
        <div class="modal-footer">
            <button class="btn-primary" onclick="closeModal()">Tutup</button>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script src="{{ asset('js/data-pengguna.js') }}"></script>
@endpush
