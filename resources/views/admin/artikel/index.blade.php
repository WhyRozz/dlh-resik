@extends('layouts.admin')

@section('title', 'Daftar Artikel - SIMPELSI')
@section('page-title', 'Kelola Artikel')
@section('page-title-mobile', 'ARTIKEL')

@push('styles')
<link rel="stylesheet" href="{{ asset('css/artikel.css') }}">
@endpush

@section('content')
<!-- 🔍 Search Bar - DIPINDAH KE LUAR -->
<div class="search-wrapper-akun" style="margin-bottom: 20px; margin-top: -50px;">
    <i class="fas fa-search search-icon"></i>
    <input 
        type="text" 
        id="searchArtikel" 
        class="search-input-akun" 
        placeholder="Cari artikel berdasarkan judul..."
        value="{{ request('search') }}"
        onkeyup="filterArtikel()"
    >
</div>

<div class="table-container">
    <!-- Header Section -->
    <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 20px;">
        <h3 style="color: #20A726; margin: 0; font-size: 20px; font-weight: 600;">
            Daftar Artikel
        </h3>
        <button type="button" 
                onclick="location.href='{{ route('admin.artikel.create') }}'"
                style="background: #20A726; color: white; padding: 10px 20px; border: none; border-radius: 6px; cursor: pointer; font-weight: 600;">
            + Tambah Artikel
        </button>
    </div>

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
                        style="display: inline-block; margin: 0 0px; padding: 8px 10px; background: #fff3cd; color: #856404; border: none; border-radius: 4px; cursor: pointer; vertical-align: middle;" 
                        title="Edit">
                        <img src="{{ asset('assets/icons/edit.png') }}" 
                        alt="Edit" 
                        style="width: 18px; height: 18px; object-fit: contain; display: block;">
                        </a>
                        
                        <button type="button" 
                        class="btn-action btn-delete" 
                        title="Hapus"
                        onclick="showDeleteModal({{ $artikel->id_artikel }})">
                        <img src="{{ asset('assets/icons/delete.png') }}" 
                        alt="Hapus" 
                        style="width: 18px; height: 18px; vertical-align: middle; display: inline-block;">
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

// 🔍 Search Artikel (Client-side seperti di Akun)
function filterArtikel() {
    const input = document.getElementById('searchArtikel');
    const filter = input.value.toLowerCase();
    const table = document.querySelector('.table-design');
    const tr = table.getElementsByTagName('tr');
    
    for (let i = 1; i < tr.length; i++) {
        const td = tr[i].getElementsByTagName('td')[1]; // Kolom Judul
        if (td) {
            const txtValue = td.textContent || td.innerText;
            if (txtValue.toLowerCase().indexOf(filter) > -1) {
                tr[i].style.display = '';
            } else {
                tr[i].style.display = 'none';
            }
        }
    }
}
</script>
@endpush