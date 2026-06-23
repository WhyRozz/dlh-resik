@extends('layouts.admin')

@section('title', 'Kelola Laporan Aduan - RESIK')
@section('page-title', 'Kelola Laporan')
@section('page-title-mobile', 'LAPORAN')

@push('styles')
    <link rel="stylesheet" href="{{ asset('css/laporan.css?v=' . time()) }}">
@endpush

@section('content')
    {{-- Header + Search dalam satu wrapper --}}
    <div class="page-search-bar">
        <input type="text" class="search-input" id="searchInput" placeholder="Cari data berdasarkan nama atau lokasi...">
    </div>
    <div class="content-card">
        <h2 class="card-title">Kelola Laporan Aduan</h2>
        <div class="table-container">
            <table>
                <thead>
                    <tr>
                        <th>No</th>
                        <th>Nama</th>
                        <th style="text-align: center;">Lokasi</th>
                        <th>Status</th>
                        <th>Tanggal</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($laporanList as $laporan)
                        @php
                            $id = $laporan->id;
                            $nama = $laporan->nama ?? '—';
                            $lokasi = $laporan->lokasi ?? '—';
                            $keterangan = $laporan->keterangan ?? '—';
                            $status = $laporan->status ?? 'Diproses';
                            $balasan = $laporan->balasan ?? '';
                            $foto = $laporan->foto ?? '';
                            $fotoBalasan = $laporan->foto_balasan ?? '';
                            $tanggal = $laporan->tanggal
                                ? \Carbon\Carbon::parse($laporan->tanggal)->format('d-m-Y')
                                : '—';

                            $statusClass = match ($status) {
                                'Diterima' => 'diterima',
                                'Ditolak' => 'ditolak',
                                default => 'diproses',
                            };

                            $isEditable = $status === 'Diproses';
                            $fotoUrl = $foto
                                ? asset('storage/' . str_replace(['storage/', 'uploads/'], '', $foto))
                                : 'https://via.placeholder.com/300x200?text=Tidak+Ada+Foto';
                            $fotoBalasanUrl = $fotoBalasan
                                ? asset('storage/' . str_replace(['storage/', 'uploads/'], '', $fotoBalasan))
                                : '';
                        @endphp

                        {{-- ✅ Row Utama - Kirim data via data attributes --}}
                        <tr onclick="showDetailModal(this)" 
                            data-id="{{ $id }}"
                            data-nama="{{ $nama }}"
                            data-lokasi="{{ $lokasi }}"
                            data-keterangan="{{ $keterangan }}"
                            data-status="{{ $status }}"
                            data-balasan="{{ $balasan }}"
                            data-foto="{{ $fotoUrl }}"
                            data-foto-balasan="{{ $fotoBalasanUrl }}"
                            data-tanggal="{{ $tanggal }}"
                            style="cursor: pointer;">
                            <td data-label="No">{{ $laporanList->firstItem() + $loop->index }}</td>
                            <td data-label="Nama">{{ $nama }}</td>
                            <td data-label="Lokasi">{{ $lokasi }}</td>
                            <td data-label="Status"><span class="status-badge status-{{ $statusClass }}">{{ $status }}</span></td>
                            <td data-label="Tanggal">{{ $tanggal }}</td>
                        </tr>
                    @endforeach

                    @if ($laporanList->isEmpty())
                        <tr>
                            <td colspan="5" style="text-align: center; padding: 30px; color: #666;">
                                Tidak ada laporan untuk ditampilkan.
                            </td>
                        </tr>
                    @endif
                </tbody>
            </table>
        </div>

        {{-- Pagination --}}
        @if ($laporanList->hasPages())
            <div class="pagination-container">
                <div>
                    Menampilkan {{ $laporanList->firstItem() }} - {{ $laporanList->lastItem() }} dari {{ $laporanList->total() }} data
                </div>
                {{ $laporanList->links() }}
            </div>
        @endif
    </div>

    {{-- ✅ MODAL POPUP DETAIL LAPORAN --}}
    <div id="detailModal" class="modal-overlay" style="display: none;">
        <div class="modal-container" style="max-width: 800px;">
            <div class="modal-header" style="background: #2e8b57; color: white; padding: 20px; border-radius: 12px 12px 0 0; display: flex; justify-content: space-between; align-items: center;">
                <h3 style="margin: 0; font-size: 18px;">📋 Detail Laporan Aduan</h3>
                <button onclick="closeDetailModal()" style="background: rgba(255,255,255,0.2); border: none; color: white; width: 32px; height: 32px; border-radius: 50%; cursor: pointer; font-size: 20px;">&times;</button>
            </div>

            <div class="modal-body" style="padding: 25px; max-height: 70vh; overflow-y: auto;">
                {{-- Foto Laporan --}}
                <div style="margin-bottom: 20px; text-align: center;">
                    <img id="modalFoto" src="" alt="Foto Laporan" style="max-width: 100%; max-height: 300px; border-radius: 8px; box-shadow: 0 2px 8px rgba(0,0,0,0.1);" onerror="this.src='https://via.placeholder.com/400x300?text=Gambar+Tidak+Tersedia'">
                </div>

                {{-- Form Detail --}}
                <div style="display: grid; gap: 15px;">
                    <div>
                        <label style="display: block; margin-bottom: 5px; font-weight: 600; color: #555; font-size: 14px;">ID Laporan:</label>
                        <input type="text" id="modalId" class="form-input" readonly style="width: 100%; padding: 10px; border: 1px solid #ddd; border-radius: 6px; background: #f5f5f5;">
                    </div>

                    <div>
                        <label style="display: block; margin-bottom: 5px; font-weight: 600; color: #555; font-size: 14px;">Nama Pelapor:</label>
                        <input type="text" id="modalNama" class="form-input" readonly style="width: 100%; padding: 10px; border: 1px solid #ddd; border-radius: 6px; background: #f5f5f5;">
                    </div>

                    <div>
                        <label style="display: block; margin-bottom: 5px; font-weight: 600; color: #555; font-size: 14px;">Lokasi:</label>
                        <input type="text" id="modalLokasi" class="form-input" readonly style="width: 100%; padding: 10px; border: 1px solid #ddd; border-radius: 6px; background: #f5f5f5;">
                    </div>

                    <div>
                        <label style="display: block; margin-bottom: 5px; font-weight: 600; color: #555; font-size: 14px;">Tanggal:</label>
                        <input type="text" id="modalTanggal" class="form-input" readonly style="width: 100%; padding: 10px; border: 1px solid #ddd; border-radius: 6px; background: #f5f5f5;">
                    </div>

                    <div>
                        <label style="display: block; margin-bottom: 5px; font-weight: 600; color: #555; font-size: 14px;">Keterangan:</label>
                        <textarea id="modalKeterangan" class="form-textarea" readonly style="width: 100%; padding: 10px; border: 1px solid #ddd; border-radius: 6px; min-height: 80px; background: #f5f5f5; resize: vertical;"></textarea>
                    </div>

                    {{-- ✅ Form Edit Status (hanya jika status Diproses) --}}
                    <div id="editSection" style="display: none; border-top: 2px solid #e0e0e0; padding-top: 20px; margin-top: 10px;">
                        <div>
                            <label style="display: block; margin-bottom: 10px; font-weight: 600; color: #555; font-size: 14px;">Status:</label>
                            <div style="display: flex; gap: 20px;">
                                <label style="display: flex; align-items: center; gap: 8px; cursor: pointer;">
                                    <input type="radio" name="status" value="Diproses" style="accent-color: #2e8b57;">
                                    <span>Diproses</span>
                                </label>
                                <label style="display: flex; align-items: center; gap: 8px; cursor: pointer;">
                                    <input type="radio" name="status" value="Diterima" style="accent-color: #2e8b57;">
                                    <span>Diterima</span>
                                </label>
                                <label style="display: flex; align-items: center; gap: 8px; cursor: pointer;">
                                    <input type="radio" name="status" value="Ditolak" style="accent-color: #2e8b57;">
                                    <span>Ditolak</span>
                                </label>
                            </div>
                        </div>

                        <div style="margin-top: 15px;">
                            <label style="display: block; margin-bottom: 5px; font-weight: 600; color: #555; font-size: 14px;">Foto Balasan (Opsional):</label>
                            <input type="file" id="fotoBalasan" accept="image/*" style="width: 100%; padding: 8px; border: 1px solid #ddd; border-radius: 6px;">
                            <small style="color: #666; font-size: 12px;">Format: PNG, JPG, JPEG. Maksimal 10MB</small>
                        </div>

                        <div style="margin-top: 15px;">
                            <label style="display: block; margin-bottom: 5px; font-weight: 600; color: #555; font-size: 14px;">Balasan untuk Masyarakat:</label>
                            <textarea id="modalBalasan" class="form-textarea" placeholder="Tulis alasan perubahan status (opsional)" style="width: 100%; padding: 10px; border: 1px solid #ddd; border-radius: 6px; min-height: 80px; resize: vertical;"></textarea>
                        </div>
                    </div>

                    {{-- ✅ Read Only Status --}}
                    <div id="readOnlySection" style="display: none; border-top: 2px solid #e0e0e0; padding-top: 20px; margin-top: 10px;">
                        <div>
                            <label style="display: block; margin-bottom: 5px; font-weight: 600; color: #555; font-size: 14px;">Status Akhir:</label>
                            <input type="text" id="modalStatusRead" readonly style="width: 100%; padding: 10px; border: 1px solid #ddd; border-radius: 6px; background: #f5f5f5;">
                        </div>
                        
                        {{-- ✅ Foto Balasan (Read Only) --}}
                        <div id="fotoBalasanReadSection" style="margin-top: 15px; display: none;">
                            <label style="display: block; margin-bottom: 5px; font-weight: 600; color: #555; font-size: 14px;">Foto Balasan:</label>
                            <div style="text-align: center; padding: 10px; background: #f9f9f9; border-radius: 6px;">
                                <img id="modalFotoBalasanRead" src="" alt="Foto Balasan" style="max-width: 100%; max-height: 250px; border-radius: 6px; box-shadow: 0 2px 6px rgba(0,0,0,0.1);" onerror="this.src='https://via.placeholder.com/400x300?text=Foto+Tidak+Tersedia'">
                            </div>
                        </div>
                        
                        <div id="balasanReadSection" style="margin-top: 15px; display: none;">
                            <label style="display: block; margin-bottom: 5px; font-weight: 600; color: #555; font-size: 14px;">Balasan:</label>
                            <textarea id="modalBalasanRead" readonly style="width: 100%; padding: 10px; border: 1px solid #ddd; border-radius: 6px; min-height: 60px; background: #f5f5f5; resize: vertical;"></textarea>
                        </div>
                        
                        <div style="margin-top: 15px; padding: 10px; background: #e8f5e9; border-radius: 6px; font-size: 13px; color: #166534;">
                            Laporan ini telah diselesaikan. Status tidak dapat diubah lagi.
                        </div>
                    </div>
                </div>
            </div>

            <div class="modal-footer" style="padding: 15px 25px; background: #f5f5f5; border-radius: 0 0 12px 12px; display: flex; justify-content: flex-end; gap: 10px;">
                <button onclick="closeDetailModal()" class="btn-secondary" style="background: #6c757d; color: white; padding: 10px 20px; border: none; border-radius: 6px; cursor: pointer; font-weight: 600;">
                    Tutup
                </button>
                <button onclick="saveStatus()" id="btnSimpan" class="btn-primary" style="display: none; background: #2e8b57; color: white; padding: 10px 20px; border: none; border-radius: 6px; cursor: pointer; font-weight: 600;">
                    Simpan
                </button>
            </div>
        </div>
    </div>
@endsection

@push('scripts')
    <script src="{{ asset('js/laporan.js?v=' . time()) }}"></script>
@endpush