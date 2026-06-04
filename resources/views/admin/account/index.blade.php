@extends('layouts.admin')

@section('title', 'Kelola Akun - RESIK')
@section('page-title', 'Kelola Akun')
@section('page-title-mobile', 'AKUN')

@push('styles')
<link rel="stylesheet" href="{{ asset('css/account.css?v=' . time()) }}">
@endpush

@section('content')
{{-- Header dengan Judul + Search Bar Sejajar --}}
<div class="content-header">
    <h2>Kelola Akun Admin</h2>
    
    <div class="search-wrapper-akun">
        <i class="fas fa-search search-icon"></i>
        <input type="text" id="searchAkun" class="search-input-akun" placeholder="Cari akun petugas...">
    </div>
</div>

{{-- Cards Akun Admin --}}
<div class="accounts-grid">
    {{-- Akun Utama --}}
    <div class="account-card">
        <div class="card-header">
            <div class="card-title"> <img src="{{ asset('assets/secure.png') }}" alt="Lock" style="width: 20px; height: 20px; margin-right: 0px; vertical-align: middle;">Akun Utama WEB </div>
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
                    onclick="requestOTPForAction('edit', {{ $akunUtama->id_admin }}, '{{ addslashes($akunUtama->email) }}')">
                    Edit
                </button>
            @else
                <button class="btn btn-primary" onclick="showAdminForm()">Buat Akun Utama</button>
            @endif
        </div>
    </div>

    {{-- Akun Kedua --}}
    <div class="account-card">
        <div class="card-header">
            <div class="card-title"> <img src="{{ asset('assets/user1.png') }}" alt="Lock" style="width: 20px; height: 20px; margin-right: 0px; vertical-align: middle;">Akun Kedua WEB </div>
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
                onclick="requestOTPForAction('edit', {{ $tambahan[0]->id_admin }}, '{{ addslashes($tambahan[0]->email) }}')">
                Edit
            </button>
            @endif
        </div>
    </div>
</div>

{{-- Section Petugas --}}
<div class="petugas-section">
    <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 20px; flex-wrap: wrap; gap: 10px;">
        <h3>Daftar Akun Petugas Mobile</h3>
        <button type="button" class="btn-tambah-akun" onclick="openPetugasModal('add')">
            + Tambah Akun
        </button>
    </div>

    <div class="petugas-table-container">
        @if($petugas->count() > 0)
            {{-- Desktop Table --}}
            <div class="desktop-table">
                <table class="data-table">
                    <thead>
                        <tr>
                            <th style="width: 5%;">No</th>
                            <th style="width: 18%;">Nama Petugas</th>
                            <th style="width: 22%;">Email</th>
                            <th style="width: 12%;">No Telpon</th>
                            <th style="width: 25%; text-align: center;">Wilayah Kerja</th>
                            <th style="width: 10%; text-align: center;">Kata Sandi</th>
                            <th style="width: 8%; text-align: center;">Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        @php
                        $levelNames = [
                            'petugas_dlh' => 'Petugas DLH',
                            'bank_sampah_kelurahan_kauman_kauman_nganjuk' => 'Bank Sampah KELURAHAN KAUMAN (Kauman, Nganjuk)',
                            'bank_sampah_kramat_bersih_kramat_nganjuk' => 'Bank Sampah KRAMAT BERSIH (Kramat, Nganjuk)',
                            'bank_sampah_kelurahan_cangkringan_cangkringan_nganjuk' => 'Bank Sampah KELURAHAN CANGKRINGAN (Cangkringan, Nganjuk)',
                            'bank_sampah_ngudi_sariro_jatirejo_nganjuk' => 'Bank Sampah NGUDI SARIRO (Jatirejo, Nganjuk)',
                            'bank_sampah_margo_utomo_begadung_nganjuk' => 'Bank Sampah MARGO UTOMO (Begadung, Nganjuk)',
                            'bank_sampah_sejahtera_kartoharjo_nganjuk' => 'Bank Sampah SEJAHTERA (Kartoharjo, Nganjuk)',
                            'bank_sampah_melati_kedungdowo_nganjuk' => 'Bank Sampah MELATI (Kedungdowo, Nganjuk)',
                            'bank_sampah_anggrek_werungotok_nganjuk' => 'Bank Sampah ANGGREK (Werungotok, Nganjuk)',
                            'bank_sampah_sumber_rejeki_werungotok_nganjuk' => 'Bank Sampah SUMBER REJEKI (Werungotok, Nganjuk)',
                            'bank_sampah_beringin_hijau_ringinanom_nganjuk' => 'Bank Sampah BERINGIN HIJAU (Ringinanom, Nganjuk)',
                            'bank_sampah_ploso_ploso_nganjuk' => 'Bank Sampah PLOSO (Ploso, Nganjuk)',
                            'bank_sampah_mulyo_agung_kudu_kertosono' => 'Bank Sampah MULYO AGUNG (Kudu, Kertosono)',  
                            'bank_sampah_estu_sae_petak_bagor' => 'Bank Sampah ESTU SAE (Petak, Bagor)',
                            'bank_sampah_desa_ngangkatan_ngangkatan_rejoso' => 'Bank Sampah DESA NGANGKATAN (Ngangkatan, Rejoso)',
                            'bank_sampah_desa_jegreg_jegreg_lengkong' => 'Bank Sampah DESA JEGREG (Jegreg, Lengkong)',
                            'bank_sampah_musirkidul_musirkidul_rejoso' => 'Bank Sampah MUSIRKIDUL (Musirkidul, Rejoso)',
                            'bank_sampah_tanjung_tanjunganom_tanjunganom' => 'Bank Sampah TANJUNG (Tanjunganom, Tanjunganom)',
                            'bank_sampah_flamboyan_loceret_loceret' => 'Bank Sampah FLAMBOYAN (Loceret, Loceret)',
                            'bank_sampah_pelita_bogo_nganjuk' => 'Bank Sampah PELITA (Bogo, Nganjuk)',
                            'bank_sampah_desa_getas_getas_tanjunganom' => 'Bank Sampah DESA GETAS (Getas, Tanjunganom)',
                            'bank_sampah_mbejaji_juwet_ngronggot' => 'Bank Sampah MBEJAJI (Juwet, Ngronggot)',
                            'bank_sampah_kedondong_kedondong_bagor' => 'Bank Sampah KEDONDONG (Kedondong, Bagor)',
                            'bank_sampah_sinar_terang_jampes_pace' => 'Bank Sampah SINAR TERANG (Jampes, Pace)',
                            'bank_sampah_desa_blongko_blongko_ngetos' => 'Bank Sampah DESA BLONGKO (Blongko, Ngetos)',
                            'bank_sampah_bukur_bukur_patianrowo' => 'Bank Sampah BUKUR (Bukur, Patianrowo)',
                            'bank_sampah_bungur_makmur_bungur_sukomoro' => 'Bank Sampah BUNGUR MAKMUR (Bungur, Sukomoro)',
                            'bank_sampah_seger_waras_mabung_baron' => 'Bank Sampah SEGER WARAS (Mabung, Baron)',
                            'bank_sampah_maju_bahagia_gondanglegi_prambon' => 'Bank Sampah MAJU BAHAGIA (Gondanglegi, Prambon)',
                            'bank_sampah_barokah_kemlokolegi_baron' => 'Bank Sampah BAROKAH (Kemlokolegi, Baron)',
                            'bank_sampah_dahlia_senjayan_gondang' => 'Bank Sampah DAHLIA (Senjayan, Gondang)',
                            'bank_sampah_cengkok_cengkok_ngronggot' => 'Bank Sampah CENGKOK (Cengkok, Ngronggot)',
                            'bank_sampah_induk_salepok_omahe_nganjuk_kedondong_bagor' => 'Bank Sampah Induk SALEPOK OMAHE NGANJUK (Kedondong, Bagor)',
                        ];
                        @endphp
                        @foreach($petugas as $index => $p)
                            <tr>
                                <td>{{ $index + 1 }}</td>
                                <td style="font-weight: 500;">{{ htmlspecialchars($p->nama_lengkap) }}</td>
                                <td style="word-break: break-all;">{{ htmlspecialchars($p->email) }}</td>
                                <td>{{ htmlspecialchars($p->no_telepon) }}</td>
                                <td>
                                    <span class="badge-wilayah">
                                        {{ $levelNames[$p->level] ?? $p->level }}
                                    </span>
                                </td>
                                <td style="text-align: center;">••••••••</td>
                                <td style="text-align: center;">
                                    <div class="actions">
                                        <button type="button" 
                                        onclick='openPetugasModal("edit", {{ json_encode([
                                            "id" => $p->id_petugas,
                                            "nama" => $p->nama_lengkap,
                                            "email" => $p->email,
                                            "telpon" => $p->no_telepon,
                                            "level" => $p->level
                                        ], JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT) }})'
                                        class="btn-icon btn-edit" title="Edit">
                                            <img src="{{ asset('assets/icons/edit.png') }}" alt="Edit" style="width: 16px; height: 16px;">
                                        </button>
                                        
                                        <button onclick="confirmDelete({{ $p->id_petugas }})" 
                                        class="btn-icon btn-delete" title="Hapus">
                                            <img src="{{ asset('assets/icons/delete.png') }}" alt="Hapus" style="width: 16px; height: 16px;">
                                        </button>
                                    </div>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>

            {{-- Mobile Card View --}}
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
                            <span class="card-value" style="font-weight: 600;">{{ htmlspecialchars($p->nama_lengkap) }}</span>
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

{{-- Delete Modal --}}
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

{{-- Config JS --}}
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
    <script src="{{ asset('js/account.js?v=' . time()) }}"></script>
@endpush
@endsection