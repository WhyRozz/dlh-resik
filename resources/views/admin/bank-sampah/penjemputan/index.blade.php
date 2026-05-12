@extends('layouts.admin')

@section('title', 'Daftar Penjemputan')

@push('styles')
    <link rel="stylesheet" href="{{ asset('css/penjemputan.css') }}">

    {{-- ✅ CSS KHUSUS: Layout Header ala Data Setor --}}
    <style>
        /* Wrapper Utama */
        .main-wrapper {
            margin-left: 0 !important;
            padding: 0 !important;
            background: transparent !important;
        }

        .main-content {
            padding: 0 !important;
        }

        /* Card Container */
        .page-container {
            background: white;
            border-radius: 12px;
            box-shadow: 0 2px 8px rgba(0, 0, 0, 0.08);
            margin-top: -50px;
            /* Tarik ke atas agar sejajar logo */
            padding: 5px 20px 20px 20px;
            margin-bottom: 0;
        }

        /* Header Wrapper: Judul + Filter + Search dalam 1 baris */
        .header-wrapper {
            display: flex !important;
            align-items: center !important;
            justify-content: space-between !important;
            gap: 1rem !important;
            margin-bottom: 1rem !important;
            flex-wrap: nowrap !important;
        }

        .page-title {
            margin: 0 !important;
            color: #2e8b57 !important;
            font-size: 1.4rem !important;
            font-weight: 700 !important;
            white-space: nowrap !important;
        }

        /* Filter Section */
        .filter-section {
            background: transparent !important;
            padding: 0 !important;
            border: none !important;
            margin: 0 !important;
            flex-shrink: 0 !important;
        }

        .filter-group {
            display: flex !important;
            gap: 0.5rem !important;
            align-items: center !important;
            flex-wrap: nowrap !important;
        }

        .filter-select {
            padding: 0.6rem 0.9rem !important;
            border: 1px solid #e0e0e0 !important;
            border-radius: 8px !important;
            font-size: 0.9rem !important;
            background: #f8f9fa !important;
            min-width: 130px !important;
            cursor: pointer !important;
            height: 38px !important;
            transition: all 0.3s ease !important;
        }

        .filter-select:focus {
            outline: none !important;
            border-color: #2e8b57 !important;
            background: white !important;
            box-shadow: 0 0 0 3px rgba(46, 139, 87, 0.1) !important;
        }

        /* Tombol Filter & Reset */
        .btn-filter {
            background: #2e8b57 !important;
            color: white !important;
            border: none !important;
            padding: 0.6rem 1.2rem !important;
            border-radius: 8px !important;
            cursor: pointer !important;
            font-weight: 500 !important;
            font-size: 0.85rem !important;
            display: inline-flex !important;
            align-items: center !important;
            gap: 0.4rem !important;
            height: 38px !important;
            transition: all 0.3s ease !important;
        }

        .btn-filter:hover {
            background: #257046 !important;
        }

        .btn-filter.reset {
            background: #6c757d !important;
        }

        .btn-filter.reset:hover {
            background: #5a6268 !important;
        }

        /* Search Box */
        .top-search {
            display: flex !important;
            justify-content: flex-end !important;
            margin: 0 !important;
            padding: 0 !important;
            flex-shrink: 0 !important;
        }

        .search-wrapper {
            position: relative !important;
            width: 280px !important;
            background: #f8f9fa !important;
            border: 1px solid #e0e0e0 !important;
            border-radius: 25px !important;
            padding: 0 15px !important;
            height: 38px !important;
            display: flex !important;
            align-items: center !important;
            transition: all 0.3s ease !important;
        }

        .search-wrapper:focus-within {
            border-color: #2e8b57 !important;
            background: white !important;
            box-shadow: 0 0 0 3px rgba(46, 139, 87, 0.1) !important;
        }

        .search-icon {
            color: #2e8b57 !important;
            margin-right: 8px !important;
            flex-shrink: 0 !important;
        }

        .search-input {
            border: none !important;
            background: transparent !important;
            outline: none !important;
            flex: 1 !important;
            padding: 6px 0 !important;
            font-size: 0.9rem !important;
            color: #333 !important;
            width: 100% !important;
        }

        /* Green Divider */
        .green-divider {
            height: 2px !important;
            background: #2e8b57 !important;
            border-radius: 99px !important;
            margin: 0.5rem 0 1.5rem 0 !important;
        }

        /* Responsive Mobile */
        @media (max-width: 1024px) {
            .header-wrapper {
                flex-wrap: wrap !important;
                gap: 1rem !important;
            }

            .filter-section {
                order: 3 !important;
                width: 100% !important;
                margin-top: 0.5rem !important;
            }

            .filter-group {
                flex-wrap: wrap !important;
                justify-content: center !important;
            }
        }

        @media (max-width: 768px) {
            .header-wrapper {
                flex-direction: column !important;
                align-items: stretch !important;
            }

            .page-title,
            .filter-section,
            .top-search {
                width: 100% !important;
                justify-content: center !important;
            }

            .filter-group {
                flex-direction: column !important;
                align-items: stretch !important;
            }

            .filter-select,
            .btn-filter {
                width: 100% !important;
                justify-content: center !important;
            }

            .search-wrapper {
                width: 100% !important;
            }
        }
    </style>
@endpush

@section('content')

    <div class="page-container">

        {{-- ✅ HEADER WRAPPER: Judul + Filter + Search dalam 1 baris --}}
        <div class="header-wrapper">

            {{-- 1. Judul Halaman --}}
            <h1 class="page-title">Daftar Penjemputan</h1>

            {{-- 2. Filter Section --}}
            <div class="filter-section">
                <form method="GET" action="{{ route('admin.bank-sampah.penjemputan.index') }}" id="filterForm">
                    <div class="filter-group">

                        {{-- Filter Bulan --}}
                        <select name="bulan" class="filter-select">
                            <option value="">Semua Bulan</option>
                            @for ($i = 1; $i <= 12; $i++)
                                <option value="{{ $i }}" {{ request('bulan') == $i ? 'selected' : '' }}>
                                    {{ date('F', mktime(0, 0, 0, $i, 1)) }}
                                </option>
                            @endfor
                        </select>

                        {{-- Filter Tahun --}}
                        <select name="tahun" class="filter-select">
                            <option value="">Semua Tahun</option>
                            @foreach ($tahunList as $tahun)
                                <option value="{{ $tahun }}" {{ request('tahun') == $tahun ? 'selected' : '' }}>
                                    {{ $tahun }}
                                </option>
                            @endforeach
                        </select>

                        {{-- Filter Status --}}
                        <select name="status" class="filter-select">
                            <option value="">Semua Status</option>
                            <option value="diproses" {{ request('status') == 'diproses' ? 'selected' : '' }}>Diproses
                            </option>
                            <option value="disetujui" {{ request('status') == 'disetujui' ? 'selected' : '' }}>Disetujui
                            </option>
                            <option value="ditolak" {{ request('status') == 'ditolak' ? 'selected' : '' }}>Ditolak</option>
                        </select>

                        {{-- Tombol Filter --}}
                        <button type="submit" class="btn-filter">
                            <i class="fas fa-filter"></i> Filter
                        </button>

                        {{-- Tombol Reset --}}
                        @if (request('bulan') || request('tahun') || request('status'))
                            <button type="button" class="btn-filter reset" onclick="resetFilter()">
                                <i class="fas fa-undo"></i> Reset
                            </button>
                        @endif
                    </div>
                </form>
            </div>

            {{-- 3. Search Box --}}
            <div class="top-search">
                <div class="search-wrapper">
                    <i class="fas fa-search search-icon"></i>
                    <input type="text" id="liveSearchInput" class="search-input" placeholder="Cari penjemputan..."
                        value="{{ request('search') }}">
                    <button type="button" id="clearSearch"
                        style="display: none; background:none; border:none; color:#888; cursor:pointer; padding:0 5px;">
                        <i class="fas fa-times"></i>
                    </button>
                </div>
            </div>
        </div>

        {{-- ✅ GREEN DIVIDER --}}
        <div class="green-divider"></div>

        {{-- ✅ TABLE CONTAINER --}}
        <table id="penjemputanTable" class="data-table" style="width: 100%; border-collapse: collapse;">
            <thead>
                <tr style="background: #f8f9fa; border-bottom: 1px solid #e0e0e0;">
                    <th
                        style="padding: 14px 20px 14px 35px; text-align: left; font-weight: 600; color: #666; font-size: 0.85rem;">
                        No</th>
                    <th style="padding: 14px 20px; text-align: left; font-weight: 600; color: #666; font-size: 0.85rem;">
                        Gambar</th>
                    <th style="padding: 14px 20px; text-align: left; font-weight: 600; color: #666; font-size: 0.85rem;">
                        Nama Admin</th>
                    <th style="padding: 14px 20px; text-align: left; font-weight: 600; color: #666; font-size: 0.85rem;">
                        Waktu</th>
                    <th style="padding: 14px 20px; text-align: left; font-weight: 600; color: #666; font-size: 0.85rem;">
                        Berat</th>
                    <th style="padding: 14px 20px; text-align: left; font-weight: 600; color: #666; font-size: 0.85rem;">
                        Status</th>
                    <th style="padding: 14px 20px; text-align: left; font-weight: 600; color: #666; font-size: 0.85rem;">
                        Aksi</th>
                </tr>
            </thead>
            <tbody>
                @forelse($penjemputans as $index => $item)
                    <tr onclick="showDetail({{ $item->id }})"
                        style="cursor: pointer; border-bottom: 1px solid #f2f2f2;">
                
                        <td style="padding: 14px 20px; color: #333;">
                            {{ $penjemputans->firstItem() + $index }}
                        </td>
                        <td style="padding: 14px 20px;">
                            @if ($item->foto)
                                <img src="{{ asset('storage/' . $item->foto) }}" alt="Foto Penjemputan"
                                    style="width: 64px; height: 52px; object-fit: cover; border-radius: 7px;">
                            @else
                                <img src="{{ asset('images/no-image.png') }}" alt="No Image"
                                    style="width: 64px; height: 52px; object-fit: cover; border-radius: 7px; background: #e9ecef;">
                            @endif
                        </td>
                        <td style="padding: 14px 20px; color: #333; font-weight: 500;">{{ $item->nama_admin }}</td>
                        <td style="padding: 14px 20px; color: #333;">
                            {{ \Carbon\Carbon::parse($item->waktu)->format('d-m-Y, H:i') }}</td>
                        <td style="padding: 14px 20px; color: #333; font-weight: 600;">{{ number_format($item->berat, 2) }}
                            Kg</td>
                        <td style="padding: 14px 20px;">
                            @php
                                $badgeClass = match ($item->status) {
                                    'diproses' => 'status-diproses',
                                    'disetujui' => 'status-berhasil',
                                    'ditolak' => 'status-ditolak',
                                    default => 'status-diproses',
                                };
                            @endphp
                            <span class="status-badge {{ $badgeClass }}"
                                style="padding: 4px 10px; border-radius: 20px; font-size: 0.8rem; font-weight: 500;">
                                {{ ucfirst($item->status) }}
                            </span>
                        </td>
                        <td style="padding: 14px 20px;" onclick="event.stopPropagation()">
                            <div class="aksi-wrapper" style="display: flex; gap: 8px;">
                                @if ($item->status === 'diproses')
                                    <form id="form-approve-{{ $item->id }}"
                                        action="{{ route('admin.bank-sampah.penjemputan.approve', $item->id) }}"
                                        method="POST" style="display: inline;">
                                        @csrf @method('PATCH')
                                        <button type="button" class="btn-approve" title="Setujui"
                                            onclick="showConfirm('approve', {{ $item->id }})"
                                            style="width: 32px; height: 32px; border: none; border-radius: 50%; background: #43a047; color: white; cursor: pointer; display: flex; align-items: center; justify-content: center;">
                                            <i class="fas fa-check" style="font-size: 14px;"></i>
                                        </button>
                                    </form>
                                    <form id="form-reject-{{ $item->id }}"
                                        action="{{ route('admin.bank-sampah.penjemputan.reject', $item->id) }}"
                                        method="POST" style="display: inline;">
                                        @csrf @method('DELETE')
                                        <button type="button" class="btn-reject" title="Tolak"
                                            onclick="showConfirm('reject', {{ $item->id }})"
                                            style="width: 32px; height: 32px; border: none; border-radius: 50%; background: #e53935; color: white; cursor: pointer; display: flex; align-items: center; justify-content: center;">
                                            <i class="fas fa-times" style="font-size: 14px;"></i>
                                        </button>
                                    </form>
                                @else
                                    <span style="color: #999; font-size: 12px; font-style: italic;">✓ Selesai</span>
                                @endif
                            </div>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="7" style="text-align: center; padding: 40px 20px; color: #aaa;">
                            <i class="fas fa-inbox" style="font-size: 28px; margin-bottom: 8px; display: block;"></i>
                            Tidak ada data penjemputan.
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>

        {{-- Pagination (Jika ada) --}}
        @if (method_exists($penjemputans, 'links'))
            <div
                style="margin-top: 1.5rem; display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 1rem; padding: 15px 20px 0 20px; border-top: 1px solid #e0e0e0;">
                <div style="font-size: 0.9rem; color: #666;">
                    Menampilkan {{ $penjemputans->firstItem() ?? 0 }} - {{ $penjemputans->lastItem() ?? 0 }} dari
                    {{ $penjemputans->total() ?? 0 }} data
                </div>
                {{ $penjemputans->links() }}
            </div>
        @endif

    </div> {{-- Tutup .page-container --}}

    <!-- Modal Detail -->
    <div id="detailModal" class="modal-overlay" style="display: none;">
        <div class="modal-container">
            <div class="modal-header">
                <h3>Detail Penjemputan</h3>
                <button class="modal-close" onclick="closeModal()">&times;</button>
            </div>
            <div class="modal-body">
                <div class="detail-content">
                    <div class="detail-image">
                        <img id="modalFoto" src="" alt="Foto Penjemputan"
                            style="width: 100%; border-radius: 10px; object-fit: cover; max-height: 280px;">
                    </div>
                    <div class="detail-form">
                        <div class="form-group"><label>No</label><input type="text" id="modalNo" readonly
                                style="width: 100%; padding: 10px; border: 1px solid #ddd; border-radius: 6px; background: #f9f9f9;">
                        </div>
                        <div class="form-group"><label>Nama Admin</label><input type="text" id="modalNamaAdmin"
                                readonly
                                style="width: 100%; padding: 10px; border: 1px solid #ddd; border-radius: 6px; background: #f9f9f9;">
                        </div>
                        <div class="form-group"><label>Waktu</label><input type="text" id="modalWaktu" readonly
                                style="width: 100%; padding: 10px; border: 1px solid #ddd; border-radius: 6px; background: #f9f9f9;">
                        </div>
                        <div class="form-group"><label>Berat</label><input type="text" id="modalBerat" readonly
                                style="width: 100%; padding: 10px; border: 1px solid #ddd; border-radius: 6px; background: #f9f9f9;">
                        </div>
                        <div class="form-group"><label>Lokasi</label><input type="text" id="modalLokasi" readonly
                                style="width: 100%; padding: 10px; border: 1px solid #ddd; border-radius: 6px; background: #f9f9f9;">
                        </div>
                        <div class="form-group"><label>Keterangan</label>
                            <textarea id="modalKeterangan" rows="3" readonly
                                style="width: 100%; padding: 10px; border: 1px solid #ddd; border-radius: 6px; background: #f9f9f9;"></textarea>
                        </div>
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <button class="btn-tutup" onclick="closeModal()"
                    style="background: #6c757d; color: white; border: none; padding: 10px 24px; border-radius: 6px; cursor: pointer;">Tutup</button>
            </div>
        </div>
    </div>

    <!-- Modal Konfirmasi -->
    <div id="confirmModal" class="modal-overlay" style="display: none;">
        <div class="modal-container" style="max-width: 400px;">
            <div class="modal-header">
                <h3 id="confirmTitle">Konfirmasi</h3>
                <button class="modal-close" onclick="closeConfirmModal()">&times;</button>
            </div>
            <div class="modal-body" style="text-align: center; padding: 30px 24px;">
                <p id="confirmMessage" style="font-size: 15px; color: #555; margin-bottom: 0;"></p>
            </div>
            <div class="modal-footer" style="display: flex; gap: 12px; justify-content: center; padding: 16px 24px 24px;">
                <button class="btn-tutup" onclick="closeConfirmModal()"
                    style="width: 100%; background: #6c757d; color: white; border: none; padding: 10px; border-radius: 6px; cursor: pointer;">Batal</button>
                <button id="confirmYesBtn"
                    style="width: 100%; padding: 10px; border-radius: 6px; border: none; cursor: pointer; font-weight: 600; color: white; transition: background 0.2s;">
                    Ya, Lanjutkan
                </button>
            </div>
        </div>
    </div>

    <!-- Success Popup -->
    <div id="successPopup" class="success-popup-overlay" style="display: none;">
        <div class="success-popup-container">
            <div class="success-icon">
                <svg width="64" height="64" viewBox="0 0 24 24" fill="none" stroke="#4CAF50"
                    stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                    <path d="M22 11.08V12a10 10 0 1 1-5.93-9.14"></path>
                    <polyline points="22 4 12 14.01 9 11.01"></polyline>
                </svg>
            </div>
            <h3 class="success-title">Berhasil!</h3>
            <p class="success-message" id="successMessage">Data penjemputan telah diperbarui.</p>
        </div>
    </div>

    <script>
        // ================= LIVE SEARCH FUNCTION =================
        (function() {
            const input = document.getElementById('liveSearchInput');
            const clearBtn = document.getElementById('clearSearch');
            const table = document.getElementById('penjemputanTable');
            let timer = null;

            if (input && clearBtn) {
                if (input.value.trim() !== '') clearBtn.style.display = 'inline-block';

                input.addEventListener('input', function() {
                    const val = this.value.trim();
                    clearBtn.style.display = val ? 'inline-block' : 'none';
                    clearTimeout(timer);
                    timer = setTimeout(() => filterTable(val), 300);
                });

                clearBtn.addEventListener('click', () => {
                    input.value = '';
                    clearBtn.style.display = 'none';
                    filterTable('');
                    input.focus();
                });
            }

            function filterTable(query) {
                if (!table) return;
                const filter = query.toLowerCase();
                const rows = table.getElementsByTagName('tr');
                for (let i = 1; i < rows.length; i++) {
                    const tdNama = rows[i].getElementsByTagName('td')[2];
                    const tdWaktu = rows[i].getElementsByTagName('td')[3];
                    const tdStatus = rows[i].getElementsByTagName('td')[5];
                    const namaVal = tdNama?.textContent.toLowerCase() || '';
                    const waktuVal = tdWaktu?.textContent.toLowerCase() || '';
                    const statusVal = tdStatus?.textContent.toLowerCase() || '';
                    const match = namaVal.includes(filter) || waktuVal.includes(filter) || statusVal.includes(filter);
                    rows[i].style.display = match ? '' : 'none';
                }
            }
        })();

        // ================= MODAL DETAIL =================
        function showDetail(id) {
            fetch(`/admin/bank-sampah/penjemputan/${id}/detail`)
                .then(response => response.json())
                .then(data => {
                    document.getElementById('modalNo').value = data.id;
                    document.getElementById('modalNamaAdmin').value = data.nama_admin;
                    document.getElementById('modalWaktu').value = new Date(data.waktu).toLocaleString('id-ID');
                    document.getElementById('modalBerat').value = parseFloat(data.berat).toFixed(2) + ' Kg';
                    document.getElementById('modalLokasi').value = data.lokasi || '-';
                    document.getElementById('modalKeterangan').value = data.keterangan || '-';
                    const imgUrl = data.foto ? `/storage/${data.foto}` : '/images/no-image.png';
                    document.getElementById('modalFoto').src = imgUrl;
                    document.getElementById('detailModal').style.display = 'flex';
                })
                .catch(error => {
                    console.error('Error:', error);
                    alert('Gagal mengambil data detail.');
                });
        }

        function closeModal() {
            document.getElementById('detailModal').style.display = 'none';
        }

        // ================= MODAL KONFIRMASI =================
        let pendingForm = null;

        function showConfirm(type, id) {
            const modal = document.getElementById('confirmModal');
            const title = document.getElementById('confirmTitle');
            const msg = document.getElementById('confirmMessage');
            const btn = document.getElementById('confirmYesBtn');
            const formId = type === 'approve' ? `form-approve-${id}` : `form-reject-${id}`;
            pendingForm = document.getElementById(formId);
            if (!pendingForm) return;
            if (type === 'approve') {
                title.innerText = 'Konfirmasi Penjemputan';
                msg.innerHTML =
                    'Apakah Anda yakin ingin <b>menyetujui</b> penjemputan ini?<br>Data akan ditandai sebagai Disetujui.';
                btn.innerText = 'Ya, Setujui';
                btn.style.background = '#43a047';
            } else {
                title.innerText = 'Konfirmasi Penolakan';
                msg.innerHTML =
                    'Apakah Anda yakin ingin <b>menolak</b> penjemputan ini?<br>Data akan ditandai sebagai Ditolak.';
                btn.innerText = 'Ya, Tolak';
                btn.style.background = '#e53935';
            }
            modal.style.display = 'flex';
        }

        function closeConfirmModal() {
            document.getElementById('confirmModal').style.display = 'none';
            pendingForm = null;
        }
        document.getElementById('confirmYesBtn').addEventListener('click', function() {
            if (pendingForm) pendingForm.submit();
        });

        // ================= GLOBAL CLICK & KEYBOARD =================
        window.onclick = function(event) {
            if (event.target === document.getElementById('detailModal')) closeModal();
            if (event.target === document.getElementById('confirmModal')) closeConfirmModal();
        }
        document.addEventListener('keydown', function(event) {
            if (event.key === 'Escape') {
                closeModal();
                closeConfirmModal();
            }
        });

        // ✅ Fungsi Reset Filter
        function resetFilter() {
            const url = new URL(window.location);
            url.searchParams.delete('bulan');
            url.searchParams.delete('tahun');
            url.searchParams.delete('status');
            window.location.href = url.pathname;
        }
    </script>

    <script>
        // ================= SUCCESS POPUP =================
        @if (session('success'))
            document.addEventListener('DOMContentLoaded', function() {
                const popup = document.getElementById('successPopup');
                const message = document.getElementById('successMessage');
                message.textContent = "{{ session('success') }}";
                popup.style.display = 'flex';

                // Auto close after 3 seconds
                setTimeout(function() {
                    popup.style.display = 'none';
                }, 3000);
            });
        @endif

        @if (session('error'))
            document.addEventListener('DOMContentLoaded', function() {
                alert("{{ session('error') }}");
            });
        @endif
    </script>
@endsection
