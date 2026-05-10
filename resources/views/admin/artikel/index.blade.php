@extends('layouts.admin')

<!-- Fungsi: Menetapkan judul halaman dan header untuk halaman daftar artikel -->
@section('title', 'Daftar Artikel - SIMPELSI')
@section('page-title', 'Kelola Artikel')
@section('page-title-mobile', 'ARTIKEL')

@push('styles')
<!-- Fungsi: Memuat file CSS khusus untuk halaman kelola artikel -->
<link rel="stylesheet" href="{{ asset('css/artikel.css') }}">
@endpush

@section('content')
<!-- Fungsi: Search bar untuk filter artikel berdasarkan judul (client-side) -->
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

<!-- Fungsi: Container utama tabel daftar artikel -->
<div class="table-container">
    <!-- Fungsi: Header section dengan judul dan tombol tambah artikel -->
    <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 20px;">
        <h3 style="color: #20A726; margin: 0; font-size: 20px; font-weight: 600;">
            Daftar Artikel
        </h3>
        <!-- Fungsi: Tombol untuk navigasi ke halaman tambah artikel -->
        <button type="button" 
                onclick="location.href='{{ route('admin.artikel.create') }}'"
                style="background: #20A726; color: white; padding: 10px 20px; border: none; border-radius: 6px; cursor: pointer; font-weight: 600;">
            + Tambah Artikel
        </button>
    </div>

    <!-- Fungsi: Tabel daftar artikel dengan kolom No, Gambar, Judul, Tanggal, dan Aksi -->
    <table class="table-design">
    <thead>
        <tr>
            <th style="width: 5%; font-weight: bold;">No</th>
            <th style="width: 15%; font-weight: bold;">Gambar</th>
            <th style="width: 55%; font-weight: bold;">Judul</th>
            <th style="width: 20%; font-weight: bold;">Tanggal</th>
            <th style="width: 20%; font-weight: bold;">Aksi</th>
        </tr>
    </thead>
        <tbody>
            <!-- Fungsi: Looping data artikel dari controller untuk ditampilkan dalam tabel -->
            @forelse($artikelList as $index => $artikel)
            <tr>
                <td>{{ $index + 1 }}</td>
                <td>
                    <!-- Fungsi: Menampilkan thumbnail gambar artikel atau placeholder jika tidak ada -->
                    @if($artikel->foto)
                        <img src="{{ asset('storage/' . $artikel->foto) }}" 
                            alt="{{ $artikel->judul }}" 
                            style="width: 80px; height: 60px; object-fit: cover; border-radius: 4px;">
                    @else
                        <span style="color: #999;">-</span>
                    @endif
                </td>
                <!-- Fungsi: Menampilkan judul artikel dengan limit 80 karakter -->
                <td>{{ Str::limit($artikel->judul, 80) }}</td>
                <!-- Fungsi: Menampilkan tanggal publikasi dengan format d-m-Y -->
                <td>{{ $artikel->tanggal->format('d-m-Y') }}</td>
                <td>
                    <div class="action-btns">
                        <!-- Fungsi: Tombol Edit untuk navigasi ke halaman edit artikel -->
                        <a href="{{ route('admin.artikel.edit', $artikel->id_artikel) }}" 
                        style="display: inline-block; margin: 0 0px; padding: 8px 10px; background: #fff3cd; color: #856404; border: none; border-radius: 4px; cursor: pointer; vertical-align: middle;" 
                        title="Edit">
                        <img src="{{ asset('assets/icons/edit.png') }}" 
                        alt="Edit" 
                        style="width: 18px; height: 18px; object-fit: contain; display: block;">
                        </a>
                        
                        <!-- Fungsi: Tombol Hapus untuk memicu modal konfirmasi penghapusan -->
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
            <!-- Fungsi: Pesan empty state ketika tidak ada data artikel -->
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

<!-- Fungsi: Modal overlay untuk konfirmasi hapus artikel -->
<div id="deleteModal" class="modal-overlay">
    <div class="modal-content">
        <div class="modal-header">
            <h3>Konfirmasi Hapus</h3>
        </div>
        <div class="modal-body">
            <p>Apakah Anda yakin ingin menghapus artikel ini?</p>
        </div>
        <div class="modal-footer">
            <!-- Fungsi: Tombol Batal untuk menutup modal konfirmasi -->
            <button type="button" class="btn-modal btn-batal" onclick="hideDeleteModal()">Batal</button>
            <!-- Fungsi: Tombol Hapus untuk mengeksekusi fungsi confirmDelete() -->
            <button type="button" class="btn-modal btn-hapus" onclick="confirmDelete()">Hapus</button>
        </div>
    </div>
</div>

<!-- Fungsi: Modal overlay untuk menampilkan notifikasi sukses dengan animasi -->
<div id="successModal" class="success-modal-overlay">
    <div class="success-modal-content">
        <!-- Fungsi: Icon centang sukses dengan animasi SVG -->
        <div class="success-icon">
            <div class="success-icon-circle">
                <svg class="success-icon-check" viewBox="0 0 52 52">
                    <path d="M14 27 L22 35 L38 16"></path>
                </svg>
            </div>
        </div>
        <h2 class="success-modal-title">Berhasil!</h2>
        <!-- Fungsi: Pesan sukses yang dinamis via JavaScript -->
        <p class="success-modal-message" id="successModalMessage">Data berhasil disimpan.</p>
        <!-- Fungsi: Tombol untuk menutup modal sukses -->
        <button type="button" class="success-modal-btn" onclick="closeSuccessModal()">Tutup</button>
    </div>
</div>
@endsection

@push('scripts')
<script>
<!-- Fungsi: Variabel global untuk menyimpan ID artikel yang akan dihapus -->
let deleteId = null;

<!-- Fungsi: Menampilkan modal konfirmasi hapus dan menyimpan ID artikel -->
function showDeleteModal(id) {
    deleteId = id;
    document.getElementById('deleteModal').classList.add('show');
}

<!-- Fungsi: Menyembunyikan modal konfirmasi hapus dan reset deleteId -->
function hideDeleteModal() {
    document.getElementById('deleteModal').classList.remove('show');
    deleteId = null;
}

<!-- Fungsi: Mengeksekusi penghapusan artikel via AJAX DELETE request -->
function confirmDelete() {
    if (!deleteId) return;

    <!-- Fungsi: Disable tombol dan ubah teks saat proses hapus berjalan -->
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
            <!-- Fungsi: Tampilkan modal sukses dan reload halaman setelah delay -->
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
        <!-- Fungsi: Restore state tombol setelah request selesai -->
        deleteBtn.textContent = originalText;
        deleteBtn.disabled = false;
    });
}

<!-- Fungsi: Menampilkan modal sukses dengan pesan yang diberikan -->
function showSuccessModal(message) {
    document.getElementById('successModalMessage').textContent = message;
    document.getElementById('successModal').classList.add('show');
}

<!-- Fungsi: Menyembunyikan modal sukses -->
function closeSuccessModal() {
    document.getElementById('successModal').classList.remove('show');
}

<!-- Fungsi: Close modal delete ketika user klik di luar area modal content -->
document.getElementById('deleteModal').addEventListener('click', function(e) {
    if (e.target === this) hideDeleteModal();
});

<!-- Fungsi: Close modal success ketika user klik di luar area modal content -->
document.getElementById('successModal').addEventListener('click', function(e) {
    if (e.target === this) closeSuccessModal();
});

<!-- Fungsi: Tampilkan modal sukses dari session setelah store/update berhasil -->
@if(session('success'))
document.addEventListener('DOMContentLoaded', function() {
    showSuccessModal("{{ session('success') }}");
});
@endif

<!-- Fungsi: Filter artikel client-side berdasarkan input judul -->
function filterArtikel() {
    const input = document.getElementById('searchArtikel');
    const filter = input.value.toLowerCase();
    const table = document.querySelector('.table-design');
    const tr = table.getElementsByTagName('tr');
    
    for (let i = 1; i < tr.length; i++) {
        <!-- Fungsi: Ambil kolom judul (index 1) untuk dicocokkan dengan keyword -->
        const td = tr[i].getElementsByTagName('td')[1];
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