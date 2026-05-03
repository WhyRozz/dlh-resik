@extends('layouts.admin')

@section('title', 'Daftar Artikel - SIMPELSI')
@section('page-title', 'Kelola Artikel')
@section('page-title-mobile', 'ARTIKEL')

@push('styles')
<link rel="stylesheet" href="{{ asset('css/artikel.css') }}">
@endpush

@section('content')
<div class="table-container">
    
    <!-- ✅ SEARCH BOX DIPISAH - Posisi Atas Kanan -->
    <div class="search-container-top">
        <form method="GET" action="{{ route('admin.artikel.index') }}" class="search-form-top">
            <div class="search-wrapper-top">
                <input 
                    type="text" 
                    name="search" 
                    class="search-input-top" 
                    placeholder="Cari berdasarkan judul..."
                    value="{{ request('search') }}"
                    autocomplete="off"
                >
                <button type="submit" class="search-btn-top">
                    <svg width="16" height="16" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" 
                              d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path>
                    </svg>
                </button>
                @if(request('search'))
                <a href="{{ route('admin.artikel.index') }}" class="search-clear-top" title="Hapus">×</a>
                @endif
            </div>
        </form>
    </div>

    <!-- ✅ HEADER: Judul + Tombol Tambah (Tetap Sebelahan) -->
    <div class="table-header-custom">
        <h2 class="table-header-title">Daftar Artikel</h2>
        <a href="{{ route('admin.artikel.create') }}" class="btn-tambah-custom">
            <span>+</span> TAMBAH ARTIKEL
        </a>
    </div>
    
    <hr class="header-divider">

    <table class="table-design">
    <thead>
        <tr>
            <th style="width: 5%; font-weight: bold;">No</th>
            <th style="width: 55%; font-weight: bold;">Judul</th>
            <th style="width: 20%; font-weight: bold;">Tanggal</th>
            <th style="width: 20%; font-weight: bold;">Aksi</th>
        </tr>
    </thead>
        <tbody>
            @forelse($artikelList as $index => $artikel)
            <tr>
                <td>{{ $index + 1 }}</td>
                <td>{{ Str::limit($artikel->judul, 80) }}</td>
                <td>{{ $artikel->tanggal->format('d-m-Y') }}</td>
                <td>
                    <div class="action-btns">
                        <a href="{{ route('admin.artikel.edit', $artikel->id_artikel) }}" 
                           class="btn-action btn-edit" 
                           title="Edit">
                            ✏️
                        </a>
                        <button type="button" 
                                class="btn-action btn-delete" 
                                title="Hapus"
                                onclick="showDeleteModal({{ $artikel->id_artikel }})">
                            🗑️
                        </button>
                    </div>
                </td>
            </tr>
            @empty
            <tr>
                <td colspan="4">
                    <div class="empty-state">
                        <p>Belum ada artikel</p>
                        <a href="{{ route('admin.artikel.create') }}">Tambah artikel sekarang</a>
                    </div>
                </td>
            </tr>
            @endforelse
        </tbody>
    </table>
</div>

<!-- Delete Confirmation Modal -->
<div id="deleteModal" class="modal-overlay">
    <div class="modal-content">
        <div class="modal-header">
            <h3>Konfirmasi Hapus</h3>
        </div>
        <div class="modal-body">
            <p>Apakah Anda yakin ingin menghapus artikel ini?</p>
        </div>
        <div class="modal-footer">
            <button type="button" class="btn-modal btn-batal" onclick="hideDeleteModal()">Batal</button>
            <button type="button" class="btn-modal btn-hapus" onclick="confirmDelete()">Hapus</button>
        </div>
    </div>
</div>

<!-- Success Modal (Elegan) -->
<div id="successModal" class="success-modal-overlay">
    <div class="success-modal-content">
        <div class="success-icon">
            <div class="success-icon-circle">
                <svg class="success-icon-check" viewBox="0 0 52 52">
                    <path d="M14 27 L22 35 L38 16"></path>
                </svg>
            </div>
        </div>
        <h2 class="success-modal-title">Berhasil!</h2>
        <p class="success-modal-message" id="successModalMessage">Data berhasil disimpan.</p>
        <button type="button" class="success-modal-btn" onclick="closeSuccessModal()">Tutup</button>
    </div>
</div>
@endsection

@push('scripts')
<script>
let deleteId = null;

function showDeleteModal(id) {
    deleteId = id;
    document.getElementById('deleteModal').classList.add('show');
}

function hideDeleteModal() {
    document.getElementById('deleteModal').classList.remove('show');
    deleteId = null;
}

function confirmDelete() {
    if (!deleteId) return;

    const deleteBtn = document.querySelector('.btn-hapus');
    const originalText = deleteBtn.textContent;
    deleteBtn.textContent = 'Menghapus...';
    deleteBtn.disabled = true;

    fetch(`/admin/artikel/${deleteId}`, {
        method: 'DELETE',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': '{{ csrf_token() }}'
        }
    })
    .then(response => response.json())
    .then(data => {
        hideDeleteModal();
        if (data.success) {
            showSuccessModal(data.message || 'Artikel berhasil dihapus!');
            setTimeout(() => location.reload(), 1500);
        } else {
            alert(data.message || 'Gagal menghapus artikel');
        }
    })
    .catch(error => {
        console.error('Error:', error);
        hideDeleteModal();
        alert('Terjadi kesalahan saat menghapus artikel');
    })
    .finally(() => {
        deleteBtn.textContent = originalText;
        deleteBtn.disabled = false;
    });
}

// ✅ FUNGSI BARU: Show Success Modal
function showSuccessModal(message) {
    document.getElementById('successModalMessage').textContent = message;
    document.getElementById('successModal').classList.add('show');
}

// ✅ FUNGSI BARU: Close Success Modal
function closeSuccessModal() {
    document.getElementById('successModal').classList.remove('show');
}

// Close modal when clicking outside
document.getElementById('deleteModal').addEventListener('click', function(e) {
    if (e.target === this) hideDeleteModal();
});

document.getElementById('successModal').addEventListener('click', function(e) {
    if (e.target === this) closeSuccessModal();
});

// ✅ Show success from session (store/update) - pakai modal baru
@if(session('success'))
document.addEventListener('DOMContentLoaded', function() {
    showSuccessModal("{{ session('success') }}");
});
@endif

// ✅ LIVE SEARCH - Ubah selector ke class baru
const searchInput = document.querySelector('.search-input-top'); // ✅ Ganti dari .search-input
let debounceTimer = null;

if (searchInput) {
    searchInput.addEventListener('input', function(e) {
        clearTimeout(debounceTimer);
        debounceTimer = setTimeout(() => {
            e.target.closest('form').submit();
        }, 500);
    });
}
</script>
@endpush