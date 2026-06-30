@extends('layouts.admin')

@section('title', 'Kelola Sub Admin Desa')

@push('styles')
    <link rel="stylesheet" href="{{ asset('css/account.css?v=' . time()) }}">
    <link rel="stylesheet" href="{{ asset('css/sub-admin.css?v=' . time()) }}">
@endpush

@section('content')
    {{-- Session Flash Messages (untuk JavaScript) --}}
    @if (session('success'))
        <meta name="session-success" content="{{ session('success') }}">
    @endif
    @if (session('error'))
        <meta name="session-error" content="{{ session('error') }}">
    @endif

    {{-- Search Box - Pojok Kanan Atas (LIVE SEARCH CLIENT-SIDE) --}}
    <div style="display: flex; justify-content: flex-end; margin-bottom: 20px;">
        <div class="search-wrapper-akun" style="position: relative;">
            <i class="fas fa-search search-icon"></i>
            <input type="text" id="searchSubAdmin" class="search-input-akun"
                placeholder="Cari sub admin desa..." onkeyup="filterSubAdmin()">
            <button type="button" id="clearSearchSubAdmin"
                style="display: none; position: absolute; right: 15px; top: 50%; transform: translateY(-50%); background: none; border: none; cursor: pointer; color: #94a3b8; font-size: 14px;"
                onclick="clearSearchSubAdmin()">
                <i class="fas fa-times"></i>
            </button>
        </div>
    </div>

    {{-- Header: Judul (Kiri) + Filter (Tengah) + Tombol (Kanan) --}}
    <div class="content-header"
        style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 20px; margin-top: -25px;">

        {{-- KIRI: Judul --}}
        <div style="flex: 1; text-align: left;">
            <h2 style="margin: 0;">Kelola Sub Admin Desa</h2>
        </div>

        {{-- TENGAH: Filter Kecamatan & Desa --}}
        <div
            style="flex: 0 0 auto; display: flex; gap: 10px; align-items: center; flex-wrap: wrap; justify-content: center;">
            {{-- Filter Kecamatan --}}
            <select id="filterKecamatanSubAdmin"
                style="padding: 10px 35px 10px 15px; border: 1px solid #e2e8f0; border-radius: 20px; font-size: 14px; background: white; cursor: pointer; min-width: 180px; outline: none;">
                <option value="">Semua Kecamatan</option>
                @foreach ($kecamatans as $kec)
                    <option value="{{ $kec->id_kecamatan }}"
                        {{ request('kecamatan_id') == $kec->id_kecamatan ? 'selected' : '' }}>
                        {{ $kec->nama_kecamatan }}
                    </option>
                @endforeach
            </select>

            {{-- Filter Desa --}}
            <select id="filterDesaSubAdmin" disabled
                style="padding: 10px 35px 10px 15px; border: 1px solid #e2e8f0; border-radius: 20px; font-size: 14px; background: white; cursor: pointer; min-width: 180px; outline: none; opacity: 0.6;">
                <option value="">Semua Desa</option>
            </select>

            {{-- Tombol Filter --}}
            <button onclick="applyFilterSubAdmin()" id="btnFilterSubAdmin"
                style="padding: 10px 24px; background: #2e8b57; color: white; border: none; border-radius: 20px; font-weight: 600; cursor: pointer; min-width: 100px; display: inline-flex; align-items: center; gap: 8px;">
                <i class="fas fa-filter"></i> Filter
            </button>

            {{-- Tombol Reset --}}
            <button onclick="resetFilterSubAdmin()" id="btnResetSubAdmin"
                style="padding: 10px 24px; background: white; color: #666; border: 1px solid #e2e8f0; border-radius: 20px; font-weight: 600; cursor: pointer; min-width: 100px; display: none; align-items: center; gap: 8px;">
                <i class="fas fa-redo"></i> Reset
            </button>
        </div>

        {{-- KANAN: Tombol Tambah --}}
        <div style="flex: 1; text-align: right;">
            <button type="button" class="btn-tambah-akun" onclick="openSubAdminModal('create')">
                <i class="fas fa-plus"></i> Tambah Sub Admin
            </button>
        </div>
    </div>

    <div class="green-divider"></div>

    <div class="petugas-section">
        <div class="petugas-table-container">
            @if ($subAdmins->count() > 0)
                <div class="desktop-table">
                    <table class="data-table">
                        <thead>
                            <tr>
                                <th style="width: 5%;">No</th>
                                <th style="width: 15%;">Nama Admin</th>
                                <th style="width: 15%;">Email</th>
                                <th style="width: 12%;">No Telepon</th>
                                <th style="width: 20%;">Wilayah Kerja</th>
                                <th style="width: 13%;">Kata Sandi</th>
                                <th style="width: 10%; text-align: center;">Aksi</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($subAdmins as $index => $admin)
                                <tr>
                                    <td>{{ $index + 1 }}</td>
                                    <td style="font-weight: 500;">{{ $admin->nama ?? '-' }}</td>
                                    <td style="word-break: break-all;">{{ $admin->email }}</td>
                                    <td>{{ $admin->no_telepon ?? '-' }}</td>
                                    <td>
                                        <span class="badge-wilayah">
                                            @if ($admin->desa && $admin->kecamatan)
                                                Desa {{ strtoupper($admin->desa->nama_desa) }}
                                                ({{ $admin->desa->nama_desa }},
                                                {{ $admin->kecamatan->nama_kecamatan }})
                                            @else
                                                -
                                            @endif
                                        </span>
                                    </td>
                                    <td style="text-align: center;">••••••••</td>
                                    <td style="text-align: center;">
                                        <div class="actions">
                                            <button type="button" onclick="openModalEdit({{ $admin->id_admin }})"
                                                class="btn-icon btn-edit" title="Edit">
                                                <img src="{{ asset('assets/icons/edit.png') }}" alt="Edit"
                                                    style="width: 16px; height: 16px;">
                                            </button>
                                            <button type="button" onclick="confirmDelete({{ $admin->id_admin }})"
                                                class="btn-icon btn-delete" title="Hapus">
                                                <img src="{{ asset('assets/icons/delete.png') }}" alt="Hapus"
                                                    style="width: 16px; height: 16px;">
                                            </button>
                                        </div>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>

                {{-- Mobile View --}}
                <div class="mobile-cards">
                    @foreach ($subAdmins as $index => $admin)
                        <div class="petugas-card">
                            <div class="card-header-mobile">
                                <span class="card-number">#{{ $index + 1 }}</span>
                                <div class="card-actions">
                                    <button type="button" onclick="openModalEdit({{ $admin->id_admin }})"
                                        class="btn-action-edit" title="Edit">
                                        <img src="{{ asset('assets/icons/edit.png') }}" alt="Edit"
                                            style="width: 18px; height: 18px;">
                                    </button>
                                    <button type="button" onclick="confirmDelete({{ $admin->id_admin }})"
                                        class="btn-action-delete" title="Hapus">
                                        <img src="{{ asset('assets/icons/delete.png') }}" alt="Hapus"
                                            style="width: 18px; height: 18px;">
                                    </button>
                                </div>
                            </div>
                            <div class="card-body-mobile">
                                <div class="card-row">
                                    <span class="card-label">Nama Admin:</span>
                                    <span class="card-value" style="font-weight: 600;">{{ $admin->nama ?? '-' }}</span>
                                </div>
                                <div class="card-row">
                                    <span class="card-label">Email:</span>
                                    <span class="card-value" style="word-break: break-all;">{{ $admin->email }}</span>
                                </div>
                                <div class="card-row">
                                    <span class="card-label">No Telepon:</span>
                                    <span class="card-value">{{ $admin->no_telepon ?? '-' }}</span>
                                </div>
                                <div class="card-row">
                                    <span class="card-label">Wilayah Kerja:</span>
                                    <span class="badge-wilayah-mobile">
                                        @if ($admin->desa && $admin->kecamatan)
                                            Desa {{ strtoupper($admin->desa->nama_desa) }}
                                        @else
                                            -
                                        @endif
                                    </span>
                                </div>
                                <div class="card-row">
                                    <span class="card-label">Kata Sandi:</span>
                                    <span class="card-value">••••••••</span>
                                </div>
                            </div>
                        </div>
                    @endforeach
                </div>
            @else
                <div style="text-align: center; padding: 40px 20px; background: #f9f9f9; border-radius: 8px; color: #666;">
                    <p style="margin: 0; font-size: 16px;">📭 Belum ada Sub Admin Desa</p>
                    <p style="margin: 5px 0 0 0; font-size: 14px;">Silakan tambahkan Sub Admin menggunakan tombol di atas
                    </p>
                </div>
            @endif
        </div>
    </div>

    {{-- MODAL CREATE/EDIT --}}
    <div id="subAdminModal" class="modal-overlay" style="display: none;">
        <div class="modal-container" style="max-width: 600px; max-height: 90vh; display: flex; flex-direction: column;">
            <div class="modal-header" style="flex-shrink: 0;">
                <h3 id="subAdminModalTitle">Tambah Sub Admin Desa</h3>
                <button type="button" class="modal-close" onclick="closeSubAdminModal()" style="cursor: pointer;">
                    <i class="fas fa-times"></i>
                </button>
            </div>

            <form id="subAdminForm" method="POST" action="" data-store-url="{{ route('admin.sub-admin.store') }}"
                data-update-url="{{ url('admin/sub-admin') }}" style="flex: 1; overflow-y: auto;">
                @csrf
                <input type="hidden" id="formMethod" name="_method" value="POST">
                <input type="hidden" id="formId" name="id_admin" value="">

                <div class="modal-body" style="padding: 20px 25px;">
                    {{-- Nama Admin --}}
                    <div class="form-group" style="margin-bottom: 20px;">
                        <label for="nama" style="display: block; margin-bottom: 8px; font-weight: 600; color: #333;">
                            Nama Admin <span style="color: #dc3545;">*</span>
                        </label>
                        <input type="text" name="nama" id="nama" class="form-control"
                            style="width: 100%; padding: 12px; border: 2px solid #e2e8f0; border-radius: 8px; font-size: 14px;"
                            placeholder="Masukkan nama admin" required autocomplete="off">
                        <span id="namaError"
                            style="color: #dc3545; font-size: 13px; margin-top: 5px; display: none;"></span>
                    </div>

                    {{-- Email --}}
                    <div class="form-group" style="margin-bottom: 20px;">
                        <label for="email" style="display: block; margin-bottom: 8px; font-weight: 600; color: #333;">
                            Email <span style="color: #dc3545;">*</span>
                        </label>
                        <input type="email" name="email" id="email" class="form-control"
                            style="width: 100%; padding: 12px; border: 2px solid #e2e8f0; border-radius: 8px; font-size: 14px;"
                            placeholder="contoh@email.com" required autocomplete="off">
                        <span id="emailError"
                            style="color: #dc3545; font-size: 13px; margin-top: 5px; display: none;"></span>
                    </div>

                    {{-- No Telepon --}}
                    <div class="form-group" style="margin-bottom: 20px;">
                        <label for="no_telepon"
                            style="display: block; margin-bottom: 8px; font-weight: 600; color: #333;">
                            No Telepon <span style="color: #dc3545;">*</span>
                        </label>
                        <input type="tel" name="no_telepon" id="no_telepon" class="form-control"
                            style="width: 100%; padding: 12px; border: 2px solid #e2e8f0; border-radius: 8px; font-size: 14px;"
                            placeholder="08xxxxxxxxxx" required autocomplete="off">
                        <span id="no_teleponError"
                            style="color: #dc3545; font-size: 13px; margin-top: 5px; display: none;"></span>
                    </div>

                    {{-- Kecamatan --}}
                    <div class="form-group" style="margin-bottom: 20px;">
                        <label for="id_kecamatan"
                            style="display: block; margin-bottom: 8px; font-weight: 600; color: #333;">
                            Kecamatan <span style="color: #dc3545;">*</span>
                        </label>
                        <select name="id_kecamatan" id="id_kecamatan" class="form-control"
                            style="width: 100%; padding: 12px; border: 2px solid #e2e8f0; border-radius: 8px; font-size: 14px;"
                            required onchange="loadDesa(this.value)" autocomplete="off">
                            <option value="">-- Pilih Kecamatan --</option>
                            @foreach ($kecamatans as $kec)
                                <option value="{{ $kec->id_kecamatan }}">{{ $kec->nama_kecamatan }}</option>
                            @endforeach
                        </select>
                        <span id="id_kecamatanError"
                            style="color: #dc3545; font-size: 13px; margin-top: 5px; display: none;"></span>
                    </div>

                    {{-- Desa --}}
                    <div class="form-group" style="margin-bottom: 20px;">
                        <label for="id_desa" style="display: block; margin-bottom: 8px; font-weight: 600; color: #333;">
                            Desa <span style="color: #dc3545;">*</span>
                        </label>
                        <select name="id_desa" id="id_desa" class="form-control"
                            style="width: 100%; padding: 12px; border: 2px solid #e2e8f0; border-radius: 8px; font-size: 14px;"
                            required disabled autocomplete="off">
                            <option value="">-- Pilih Desa --</option>
                        </select>
                        <span id="id_desaError"
                            style="color: #dc3545; font-size: 13px; margin-top: 5px; display: none;"></span>
                    </div>

                    {{-- Password dengan Show/Hide --}}
                    <div class="form-group" style="margin-bottom: 20px;" id="passwordGroup">
                        <label for="password" style="display: block; margin-bottom: 8px; font-weight: 600; color: #333;">
                            Password <span style="color: #dc3545;">*</span>
                            <span id="passwordHint" style="font-weight: 400; color: #6c757d; font-size: 12px;"></span>
                        </label>
                        <div style="position: relative;">
                            <input type="password" name="password" id="password" class="form-control"
                                style="width: 100%; padding: 12px 45px 12px 12px; border: 2px solid #e2e8f0; border-radius: 8px; font-size: 14px;"
                                placeholder="Minimal 8 karakter" autocomplete="new-password" {{-- ✅ PENTING: Mencegah autofill password --}}
                                value=""> {{-- ✅ PASTIKAN KOSONG --}}
                            <button type="button" id="togglePassword"
                                style="position: absolute; right: 12px; top: 50%; transform: translateY(-50%); background: none; border: none; cursor: pointer; color: #6c757d; padding: 5px;">
                                <i class="fas fa-eye" id="eyeIcon"></i>
                            </button>
                        </div>
                        <span id="passwordError"
                            style="color: #dc3545; font-size: 13px; margin-top: 5px; display: none;"></span>
                    </div>
                </div>

                <div class="modal-footer" style="flex-shrink: 0;">
                    <button type="button" class="btn-secondary" onclick="closeSubAdminModal()">Batal</button>
                    <button type="submit" class="btn-primary-modal" id="btnSubmit">
                        Simpan
                    </button>
                </div>
            </form>
        </div>
    </div>

    {{-- Delete Form --}}
    <form id="deleteForm" method="POST" style="display: none;" data-delete-url="{{ url('admin/sub-admin') }}">
        @csrf
        @method('DELETE')
    </form>
@endsection

@push('scripts')
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script>
        // Pass data dari Laravel ke JavaScript
        window.kecamatansData = @json($kecamatans);
        window.subAdminsData = @json($subAdmins); // ← TAMBAHKAN INI
    </script>
    <script src="{{ asset('js/sub-admin.js?v=' . time()) }}"></script>
@endpush
