@extends('layouts.admin')

@section('title', 'RESIK - Jenis & Harga Sampah')
@push('styles')
<link rel="stylesheet" href="{{ asset('css/jenis-sampah.css') }}">
@endpush

@section('content')
<div class="page-container">
    <!-- Header -->
    <div class="page-header">
        <h2 class="page-title">Daftar Jenis & Harga Sampah</h2>
        <!-- ✅ Tambah: Link ke halaman create, bukan modal -->
        <a href="{{ route('admin.bank-sampah.jenis-sampah.create') }}" class="btn-add">
            <i class="fas fa-plus"></i> Tambah Jenis Sampah
        </a>
    </div>

    <!-- Table Container -->
    <div class="table-container">
        <table>
            <thead>
                <tr>
                    <th style="width: 50px;">No</th>
                    <th style="width: 100px;">Gambar</th>
                    <th>Jenis</th>
                    <th style="width: 100px;">Satuan</th>
                    <th style="width: 150px;">Harga</th>
                    <th style="width: 100px;">Aksi</th>
                </tr>
            </thead>
            <tbody>
                @forelse($jenisSampah as $key => $item)
                <tr>
                    <td>{{ $jenisSampah->firstItem() + $key }}</td>
                    <td>
                        @if($item->gambar)
                            <img src="{{ asset('storage/' . $item->gambar) }}" alt="{{ $item->jenis }}" class="table-img">
                        @else
                            <span class="text-muted">-</span>
                        @endif
                    </td>
                    <td>{{ $item->jenis }}</td>
                    <td>{{ $item->satuan }}</td>
                    <td><strong>Rp {{ number_format($item->harga, 0, ',', '.') }}</strong></td>
                    <td>
                        <div class="action-buttons">
                            <!-- ✅ Edit: Link ke halaman edit -->
                            <a href="{{ route('admin.bank-sampah.jenis-sampah.edit', $item->id_jenis_sampah) }}"
                               class="btn-icon btn-edit" title="Edit">
                                <img src="{{ asset('assets/icons/edit.png') }}" alt="Edit" style="width: 1.2em; height: 1.2em; object-fit: contain; vertical-align: middle;">
                            </a>
                            <!-- ✅ Hapus: Tetap pakai modal konfirmasi -->
                            <button class="btn-icon btn-delete"
                                    onclick="confirmDelete({{ $item->id_jenis_sampah }}, '{{ addslashes($item->jenis) }}')"
                                    title="Hapus">
                                    <img src="{{ asset('assets/icons/delete.png') }}" alt="Hapus" style="width: 1.2em; height: 1.2em; object-fit: contain; vertical-align: middle;">
                            </button>
                        </div>
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="6" class="text-center py-4">
                        <div class="empty-state">
                            <i class="fas fa-recycle"></i>
                            <p>Belum ada data jenis sampah.</p>
                        </div>
                    </td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>

<!--  Modal Confirm Delete-->
<div id="deleteModal" class="modal-overlay">
    <div class="modal-card modal-sm">
        <div class="modal-header">
            <h3>Konfirmasi Hapus</h3>
            <button class="close-btn" onclick="closeDeleteModal()">&times;</button>
        </div>
        <div class="modal-body text-center">
            <i class="fas fa-exclamation-triangle" style="font-size: 48px; color: #f59e0b; margin-bottom: 15px;"></i>
            <p>Apakah Anda yakin ingin menghapus <strong id="deleteName"></strong>?</p>
            <p class="text-muted" style="font-size: 13px;">Data yang dihapus tidak bisa dikembalikan.</p>
        </div>
        <div class="modal-footer" style="justify-content: center; gap: 10px;">
            <button class="btn-secondary" onclick="closeDeleteModal()" style="background: #6c757d;">Batal</button>
            <form id="deleteForm" method="POST" style="display: inline;">
                @csrf
                @method('DELETE')
                <button type="submit" class="btn-danger" style="background: #dc3545;">Hapus</button>
            </form>
        </div>
    </div>
</div>

@if(session('success'))
<div class="alert alert-success">
    {{ session('success') }}
</div>
@endif

@if($errors->any())
<div class="alert alert-danger">
    <ul style="margin: 0; padding-left: 20px;">
        @foreach($errors->all() as $error)
            <li>{{ $error }}</li>
        @endforeach
    </ul>
</div>
@endif
@endsection

@push('scripts')
<script src="{{ asset('js/jenis-sampah.js') }}"></script>
@endpush
