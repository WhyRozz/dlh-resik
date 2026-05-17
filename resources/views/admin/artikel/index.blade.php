@extends('layouts.admin')

{{-- FUNGSI: Menetapkan judul halaman dan header untuk halaman daftar artikel --}}
@section('title', 'Daftar Artikel - RESIK')
@section('page-title', 'Kelola Artikel')
@section('page-title-mobile', 'ARTIKEL')

@push('styles')
    {{-- FUNGSI: Memuat file CSS khusus untuk halaman kelola artikel --}}
    <link rel="stylesheet" href="{{ asset('css/artikel.css') }}">
@endpush

@section('content')
{{-- FUNGSI: Search bar untuk filter artikel berdasarkan judul (client-side) --}}
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

{{-- FUNGSI: Container utama tabel daftar artikel --}}
<div class="table-container">
    {{-- FUNGSI: Header section dengan judul dan tombol tambah artikel --}}
    <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 20px;">
        <h3 style="color: #20A726; margin: 0; font-size: 20px; font-weight: 600;">Daftar Artikel</h3>
        {{-- FUNGSI: Tombol untuk navigasi ke halaman tambah artikel --}}
        <button type="button" 
                onclick="location.href='{{ route('admin.artikel.create') }}'"
                style="background: #20A726; color: white; padding: 10px 20px; border: none; border-radius: 6px; cursor: pointer; font-weight: 600;">
            + Tambah Artikel
        </button>
    </div>

    {{-- FUNGSI: Tabel daftar artikel dengan kolom No, Gambar, Judul, Tanggal, dan Aksi --}}
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
            {{-- FUNGSI: Looping data artikel dari controller untuk ditampilkan dalam tabel --}}
            @forelse($artikelList as $index => $artikel)
            <tr>
                <td>{{ $index + 1 }}</td>
                <td>
                    {{-- FUNGSI: Menampilkan thumbnail gambar artikel atau placeholder jika tidak ada --}}
                    @if($artikel->foto)
                        <img src="{{ asset('storage/' . $artikel->foto) }}" 
                            alt="{{ $artikel->judul }}" 
                            style="width: 80px; height: 60px; object-fit: cover; border-radius: 4px;">
                    @else
                        <span style="color: #999;">-</span>
                    @endif
                </td>
                {{-- FUNGSI: Menampilkan judul artikel dengan limit 80 karakter --}}
                <td>{{ Str::limit($artikel->judul, 80) }}</td>
                {{-- FUNGSI: Menampilkan tanggal publikasi dengan format d-m-Y --}}
                <td>{{ $artikel->tanggal->format('d-m-Y') }}</td>
                <td>
                    <div class="action-btns">
                        {{-- FUNGSI: Tombol Edit untuk navigasi ke halaman edit artikel --}}
                        <a href="{{ route('admin.artikel.edit', $artikel->id_artikel) }}" 
                        style="display: inline-block; margin: 0 0px; padding: 8px 10px; background: #fff3cd; color: #856404; border: none; border-radius: 4px; cursor: pointer; vertical-align: middle;" 
                        title="Edit">
                        <img src="{{ asset('assets/icons/edit.png') }}" 
                        alt="Edit" 
                        style="width: 18px; height: 18px; object-fit: contain; display: block;">
                        </a>
                        
                        {{-- FUNGSI: Tombol Hapus untuk memicu modal konfirmasi penghapusan --}}
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
            {{-- FUNGSI: Pesan empty state ketika tidak ada data artikel --}}
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

{{-- FUNGSI: Modal overlay untuk konfirmasi hapus artikel --}}
<div id="deleteModal" class="modal-overlay">
    <div class="modal-content">
        <div class="modal-header">
            <h3>Konfirmasi Hapus</h3>
        </div>
        <div class="modal-body">
            <p>Apakah Anda yakin ingin menghapus artikel ini?</p>
        </div>
        <div class="modal-footer">
            {{-- FUNGSI: Tombol Batal untuk menutup modal konfirmasi --}}
            <button type="button" class="btn-modal btn-batal" onclick="hideDeleteModal()">Batal</button>
            {{-- FUNGSI: Tombol Hapus untuk mengeksekusi fungsi confirmDelete() --}}
            <button type="button" class="btn-modal btn-hapus" onclick="confirmDelete()">Hapus</button>
        </div>
    </div>
</div>

{{-- FUNGSI: Modal overlay untuk menampilkan notifikasi sukses dengan animasi --}}
<div id="successModal" class="success-modal-overlay">
    <div class="success-modal-content">
        {{-- FUNGSI: Icon centang sukses dengan animasi SVG --}}
        <div class="success-icon">
            <div class="success-icon-circle">
                <svg class="success-icon-check" viewBox="0 0 52 52">
                    <path d="M14 27 L22 35 L38 16"></path>
                </svg>
            </div>
        </div>
        <h2 class="success-modal-title">Berhasil!</h2>
        {{-- FUNGSI: Pesan sukses yang dinamis via JavaScript --}}
        <p class="success-modal-message" id="successModalMessage">Data berhasil disimpan.</p>
        {{-- FUNGSI: Tombol untuk menutup modal sukses --}}
        <button type="button" class="success-modal-btn" onclick="closeSuccessModal()">Tutup</button>
    </div>
</div>

{{-- 🔗 BRIDGE: Pass CSRF token ke file JS eksternal (wajib karena JS file tidak bisa baca {{ }}) --}}
<script>
    window.ArtikelConfig = {
        csrfToken: "{{ csrf_token() }}"
    };
</script>

@push('scripts')
    {{-- FUNGSI: Memuat file JS eksternal yang berisi semua fungsi interaksi halaman --}}
    <script src="{{ asset('js/artikel.js') }}"></script>
@endpush
@endsection