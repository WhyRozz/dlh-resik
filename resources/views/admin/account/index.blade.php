@extends('layouts.admin')

@section('title', 'Kelola Akun - RESIK')
@section('page-title', 'Kelola Akun')
@section('page-title-mobile', 'AKUN')

@push('styles')
    <link rel="stylesheet" href="{{ asset('css/account.css') }}">
@endpush

@if(session('success'))
    <div class="alert alert-success">{{ session('success') }}</div>
@endif
@if(session('error'))
    <div class="alert alert-error">{{ session('error') }}</div>
@endif
@if($errors->any())
    <div class="alert alert-error">{{ $errors->first() }}</div>
@endif

@section('content')
<div class="content-header">
    <h2>Kelola Akun Admin</h2>
</div>

<div class="search-wrapper-akun">
    <i class="fas fa-search search-icon"></i>
    <input type="text" id="searchAkun" class="search-input-akun" placeholder="Cari akun petugas berdasarkan nama atau email">
</div>

<div class="accounts-grid">
    <!-- Akun Utama -->
    <div class="account-card">
        <div class="card-header">
            <div class="card-title"><span>🔒</span> Akun Utama WEB</div>
            <span class="badge-default">DEFAULT</span>
        </div>
        <div class="account-info">
            <label>Email:</label>
            <span>{{ $akunUtama ? htmlspecialchars($akunUtama->email) : 'Belum dibuat' }}</span>
        </div>
        <div class="account-info">
            <label>Password:</label>
            <span>••••••••</span>
        </div>
        <div class="btn-group">
            @if($akunUtama)
                <button class="btn btn-outline" 
                    onclick="requestOTPForAction('edit_admin', {{ $akunUtama->id_admin }}, '{{ addslashes($akunUtama->email) }}')">
                    Edit
                </button>
            @else
                <button class="btn btn-primary" onclick="showAdminForm()">Buat Akun Utama</button>
            @endif
        </div>
    </div>

    <!-- Akun Kedua -->
    <div class="account-card">
        <div class="card-header">
            <div class="card-title"><span>👤</span> Akun Kedua WEB</div>
            <span class="badge-default">DEFAULT</span>
        </div>
        <div class="account-info">
            <label>Email:</label>
            <span>{{ isset($tambahan[0]) ? htmlspecialchars($tambahan[0]->email) : 'Belum dibuat' }}</span>
        </div>
        <div class="account-info">
            <label>Password:</label>
            <span>••••••••</span>
        </div>
        <div class="btn-group">
            @if(isset($tambahan[0]))
                <button class="btn btn-outline" 
                    onclick="requestOTPForAction('edit_admin', {{ $tambahan[0]->id_admin }}, '{{ addslashes($tambahan[0]->email) }}')">
                    Edit
                </button>
            @else
                <button class="btn btn-primary" onclick="showAdminForm()">Tambah Akun</button>
            @endif
        </div>
    </div>
</div>

<hr style="margin: 40px 0; border: none; border-top: 2px solid #e0e0e0;">

<!-- Section Petugas -->
<div class="petugas-section" style="background: white; border-radius: 10px; padding: 20px; box-shadow: 0 2px 8px rgba(0,0,0,0.08); margin-top: 20px;">
    <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 20px; flex-wrap: wrap; gap: 10px;">
        <h3 style="color: #20A726; margin: 0; font-size: 18px; font-weight: 600;">Daftar Akun Petugas Mobile</h3>
        <button type="button" class="btn-tambah-akun" 
                onclick="openPetugasModal('add')"
                style="background: #20A726; color: white; padding: 10px 18px; border: none; border-radius: 6px; cursor: pointer; font-weight: 600; font-size: 14px; white-space: nowrap;">
            + Tambah Akun
        </button>
    </div>

    <div class="petugas-table-container">
        @if($petugas->count() > 0)
            <!-- Desktop Table -->
            <div class="desktop-table" style="overflow-x: auto;">
                <table style="width: 100%; border-collapse: collapse; min-width: 800px;">
                    <thead style="background: #e6f2e6;">
                        <tr>
                            <th style="padding: 12px 10px; text-align: left; border-bottom: 2px solid #ddd; font-weight: 600; color: #333; width: 5%;">No</th>
                            <th style="padding: 12px 10px; text-align: left; border-bottom: 2px solid #ddd; font-weight: 600; color: #333; width: 18%;">Nama Petugas</th>
                            <th style="padding: 12px 10px; text-align: left; border-bottom: 2px solid #ddd; font-weight: 600; color: #333; width: 22%;">Email</th>
                            <th style="padding: 12px 10px; text-align: left; border-bottom: 2px solid #ddd; font-weight: 600; color: #333; width: 12%;">No Telpon</th>
                            <th style="padding: 12px 10px; text-align: left; border-bottom: 2px solid #ddd; font-weight: 600; color: #333; width: 25%;">Wilayah Kerja</th>
                            <th style="padding: 12px 10px; text-align: center; border-bottom: 2px solid #ddd; font-weight: 600; color: #333; width: 10%;">Kata Sandi</th>
                            <th style="padding: 12px 10px; text-align: center; border-bottom: 2px solid #ddd; font-weight: 600; color: #333; width: 8%;">Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        @php
                        $levelNames = [
                            'petugas_dlh' => 'Petugas DLH',
                            'bank_sampah_kelurahan_kauman_kauman_nganjuk' => 'BS Kel. Kauman',
                            'bank_sampah_kramat_bersih_kramat_nganjuk' => 'BS Kramat Bersih',
                            'bank_sampah_kelurahan_cangkringan_cangkringan_nganjuk' => 'BS Kel. Cangkringan',
                            'bank_sampah_ngudi_sariro_jatirejo_nganjuk' => 'BS Ngudi Sariro',
                            'bank_sampah_margo_utomo_begadung_nganjuk' => 'BS Margo Utomo',
                            'bank_sampah_sejahtera_kartoharjo_nganjuk' => 'BS Sejahtera',
                            'bank_sampah_melati_kedungdowo_nganjuk' => 'BS Melati',
                            'bank_sampah_anggrek_werungotok_nganjuk' => 'BS Anggrek',
                            'bank_sampah_sumber_rejeki_werungotok_nganjuk' => 'BS Sumber Rejeki',
                            'bank_sampah_beringin_hijau_ringinanom_nganjuk' => 'BS Beringin Hijau',
                            'bank_sampah_ploso_ploso_nganjuk' => 'BS Ploso',
                            'bank_sampah_mulyo_agung_kudu_kertosono' => 'BS Mulyo Agung',
                            'bank_sampah_estu_sae_petak_bagor' => 'BS Estu Sae',
                            'bank_sampah_desa_ngangkatan_ngangkatan_rejoso' => 'BS Desa Ngangkatan',
                            'bank_sampah_desa_jegreg_jegreg_lengkong' => 'BS Desa Jegreg',
                            'bank_sampah_musirkidul_musirkidul_rejoso' => 'BS Musirkidul',
                            'bank_sampah_tanjung_tanjunganom_tanjunganom' => 'BS Tanjung',
                            'bank_sampah_flamboyan_loceret_loceret' => 'BS Flamboyan',
                            'bank_sampah_pelita_bogo_nganjuk' => 'BS Pelita',
                            'bank_sampah_desa_getas_getas_tanjunganom' => 'BS Desa Getas',
                            'bank_sampah_mbejaji_juwet_ngronggot' => 'BS Mbejaji',
                            'bank_sampah_kedondong_kedondong_bagor' => 'BS Kedondong',
                            'bank_sampah_sinar_terang_jampes_pace' => 'BS Sinar Terang',
                            'bank_sampah_desa_blongko_blongko_ngetos' => 'BS Desa Blongko',
                            'bank_sampah_bukur_bukur_patianrowo' => 'BS Bukur',
                            'bank_sampah_bungur_makmur_bungur_sukomoro' => 'BS Bungur Makmur',
                            'bank_sampah_seger_waras_mabung_baron' => 'BS Seger Waras',
                            'bank_sampah_maju_bahagia_gondanglegi_prambon' => 'BS Maju Bahagia',
                            'bank_sampah_barokah_kemlokolegi_baron' => 'BS Barokah',
                            'bank_sampah_dahlia_senjayan_gondang' => 'BS Dahlia',
                            'bank_sampah_cengkok_cengkok_ngronggot' => 'BS Cengkok',
                            'bank_sampah_induk_salepok_omahe_nganjuk_kedondong_bagor' => 'BS Induk Salepok Omahe',
                        ];
                        @endphp
                        @foreach($petugas as $index => $p)
                            <tr style="border-bottom: 1px solid #eee;">
                                <td style="padding: 12px 10px;">{{ $index + 1 }}</td>
                                <td style="padding: 12px 10px; font-weight: 500;">{{ htmlspecialchars($p->nama_lengkap) }}</td>
                                <td style="padding: 12px 10px; word-break: break-all;">{{ htmlspecialchars($p->email) }}</td>
                                <td style="padding: 12px 10px;">{{ htmlspecialchars($p->no_telepon) }}</td>
                                <td style="padding: 12px 10px;">
                                    <span class="badge-wilayah" style="display: inline-block; padding: 4px 10px; background: #e8f5e9; color: #2e7d32; border-radius: 4px; font-size: 11px; font-weight: 500; white-space: nowrap;">
                                        {{ $levelNames[$p->level] ?? $p->level }}
                                    </span>
                                </td>
                                <td style="padding: 12px 10px; text-align: center;">••••••••</td>
                                <td style="padding: 12px 10px; text-align: center;">
                                    <div style="display: flex; gap: 5px; justify-content: center;">
                                        <button type="button" 
                                        onclick='openPetugasModal("edit", {{ json_encode([
                                            "id" => $p->id_petugas,
                                            "nama" => $p->nama_lengkap,
                                            "email" => $p->email,
                                            "telpon" => $p->no_telepon,
                                            "level" => $p->level
                                        ], JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT) }})'
                                        style="padding: 6px 10px; background: #fff3cd; color: #856404; border: none; border-radius: 4px; cursor: pointer;" 
                                        title="Edit">
                                            <img src="{{ asset('assets/icons/edit.png') }}" alt="Edit" style="width: 16px; height: 16px;">
                                        </button>
                                        
                                        <button onclick="confirmDelete({{ $p->id_petugas }})" 
                                        style="padding: 6px 10px; background: #f8d7da; color: #721c24; border: none; border-radius: 4px; cursor: pointer;"
                                        title="Hapus">
                                            <img src="{{ asset('assets/icons/delete.png') }}" alt="Hapus" style="width: 16px; height: 16px;">
                                        </button>
                                    </div>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>

            <!-- Mobile Card View -->
            <div class="mobile-cards">
                @foreach($petugas as $index => $p)
                <div class="petugas-card" data-search="{{ strtolower($p->nama_lengkap . ' ' . $p->email . ' ' . $p->no_telepon) }}">
                    <div class="card-header-mobile">
                        <span class="card-number">#{{ $index + 1 }}</span>
                        <div class="card-actions">
                            <button type="button" 
                            onclick='openPetugasModal("edit", {{ json_encode([
                                "id" => $p->id_petugas,
                                "nama" => $p->nama_lengkap,
                                "email" => $p->email,
                                "telpon" => $p->no_telepon,
                                "level" => $p->level
                            ], JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT) }})'
                            class="btn-action-edit" title="Edit">
                                <img src="{{ asset('assets/icons/edit.png') }}" alt="Edit" style="width: 18px; height: 18px;">
                            </button>
                            <button onclick="confirmDelete({{ $p->id_petugas }})" 
                            class="btn-action-delete" title="Hapus">
                                <img src="{{ asset('assets/icons/delete.png') }}" alt="Hapus" style="width: 18px; height: 18px;">
                            </button>
                        </div>
                    </div>
                    
                    <div class="card-body-mobile">
                        <div class="card-row">
                            <span class="card-label">Nama Petugas:</span>
                            <span class="card-value" style="font-weight: 600; color: #333;">{{ htmlspecialchars($p->nama_lengkap) }}</span>
                        </div>
                        
                        <div class="card-row">
                            <span class="card-label">Email:</span>
                            <span class="card-value" style="word-break: break-all;">{{ htmlspecialchars($p->email) }}</span>
                        </div>
                        
                        <div class="card-row">
                            <span class="card-label">No. Telepon:</span>
                            <span class="card-value">{{ htmlspecialchars($p->no_telepon) }}</span>
                        </div>
                        
                        <div class="card-row">
                            <span class="card-label">Wilayah Kerja:</span>
                            <span class="badge-wilayah-mobile">
                                {{ $levelNames[$p->level] ?? $p->level }}
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
                <p style="margin: 0; font-size: 16px;">📭 Belum ada akun petugas lapangan</p>
                <p style="margin: 5px 0 0 0; font-size: 14px;">Silakan tambahkan petugas menggunakan tombol di atas</p>
            </div>
        @endif
    </div>
</div>

<!-- Delete Modal -->
<div id="deleteModal" class="modal" style="display: none;">
    <div style="background: white; padding: 30px; border-radius: 10px; max-width: 400px; text-align: center; box-shadow: 0 10px 30px rgba(0,0,0,0.3); margin: 20px;">
        <h4 style="margin-top: 0; color: #333; font-size: 18px; margin-bottom: 15px;">Konfirmasi Hapus</h4>
        <p style="color: #666; margin-bottom: 25px;">Apakah Anda yakin ingin menghapus Akun ini?</p>
        <div style="display: flex; gap: 15px; justify-content: center; flex-wrap: wrap;">
            <button onclick="closeDeleteModal()" 
                    style="background: #6c757d; color: white; padding: 10px 25px; border: none; border-radius: 6px; cursor: pointer; font-weight: 600; flex: 1; min-width: 100px;">
                Batal
            </button>
            <form id="deleteForm" method="POST" style="display: inline; flex: 1; min-width: 100px;">
                @csrf
                @method('DELETE')
                <button type="submit" 
                        style="background: #dc3545; color: white; padding: 10px 25px; border: none; border-radius: 6px; cursor: pointer; font-weight: 600; width: 100%;">
                    Hapus
                </button>
            </form>
        </div>
    </div>
</div>

@include('admin.account.partials.modals')

{{-- 🔗 BRIDGE: Pass dynamic Laravel data ke JS eksternal --}}
<script>
    window.AccountConfig = {
        csrfToken: "{{ csrf_token() }}",
        routes: {
            requestOtp: "{{ route('admin.akun.request-otp') }}",
            verifyOtp: "{{ route('admin.akun.verify-otp') }}",
            petugasStore: "{{ url('admin/petugas') }}"
        }
    };
</script>

@push('scripts')
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script src="{{ asset('js/account.js') }}"></script>
@endpush
@endsection