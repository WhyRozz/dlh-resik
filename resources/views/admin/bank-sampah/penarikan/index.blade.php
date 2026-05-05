@extends('layouts.admin')

@section('title', 'Data Penarikan')
@section('page-title', 'Data Penarikan')

@push('styles')
    <link rel="stylesheet" href="{{ asset('css/penarikan.css') }}">
@endpush

@section('content')

<div class="page-container">
    
    {{-- Header: Judul & Tombol Cetak --}}
    <div class="page-header">
        <h1 class="page-title">Data Penarikan</h1>
        <button class="btn-cetak" onclick="window.print()">
            <svg width="18" height="18" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z"/>
            </svg>
            Cetak PDF
        </button>
    </div>

    {{-- Search Box --}}
    <div class="search-container">
        <div class="search-wrapper">
            <svg class="search-icon" width="20" height="20" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
            </svg>
            <input type="text" id="searchInput" class="search-input" placeholder="Cari nama, status, atau tanggal...">
        </div>
    </div>

    {{-- Green Divider Line --}}
    <div class="green-divider"></div>

    {{-- Table Container --}}
    <div class="table-container">
        <table class="data-table" id="penarikanTable">
            <thead>
                <tr>
                    <th width="5%">No</th>
                    <th width="20%">Nama Anggota</th>
                    <th width="18%">Tanggal Penarikan</th>
                    <th width="15%">Jumlah Uang</th>
                    <th width="17%">E-Wallet</th>
                    <th width="12%">Status</th>
                    <th width="13%">Aksi</th>
                </tr>
            </thead>
            <tbody>
                @forelse($penarikans as $index => $penarikan)
                <tr>
                    <td>{{ $penarikans->firstItem() + $index }}</td>
                    <td>
                        <span class="member-name">{{ $penarikan->nama_user ?? 'Unknown' }}</span>
                    </td>
                    <td>
                        <span class="date-main">{{ $penarikan->tanggal_penarikan->format('d M Y') }}</span>
                        <span class="date-time">{{ $penarikan->tanggal_penarikan->format('H:i') }}</span>
                    </td>
                    <td>
                        <span class="amount">Rp {{ number_format($penarikan->jumlah_uang, 0, ',', '.') }}</span>
                    </td>
                    <td>
                        <span class="wallet-type">{{ $penarikan->jenis_ewallet ?? '-' }}</span>
                        <span class="wallet-number">{{ $penarikan->nomor_ewallet ?? '' }}</span>
                    </td>
                    <td>
                        <span class="status-badge status-{{ strtolower($penarikan->status) }}">
                            {{ ucfirst($penarikan->status) }}
                        </span>
                    </td>
                    <td>
                        <div class="action-buttons">
                            <button class="btn-action btn-view" 
                                    onclick="showDetail({{ $penarikan->id_penarikan }})" 
                                    title="Lihat Detail">
                                <svg width="16" height="16" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/>
                                </svg>
                            </button>
                            
                            @if($penarikan->status !== 'berhasil')
                            <button class="btn-action btn-delete" 
                                    onclick="deleteData({{ $penarikan->id_penarikan }})" 
                                    title="Hapus">
                                <svg width="16" height="16" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
                                </svg>
                            </button>
                            @else
                            <button class="btn-action btn-disabled" disabled title="Sudah Disetujui">
                                <svg width="16" height="16" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                                </svg>
                            </button>
                            @endif
                        </div>
                    </td>
                </tr>
                @empty
                {{-- Empty State Row --}}
                <tr class="empty-row">
                    <td colspan="7">
                        <svg width="48" height="48" fill="none" stroke="currentColor" viewBox="0 0 24 24" style="margin-bottom: 0.5rem; opacity: 0.5;">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/>
                        </svg>
                        <div>Belum ada data penarikan</div>
                    </td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    {{-- Pagination --}}
    @if($penarikans->hasPages())
    <div class="pagination-container">
        <div>
            Menampilkan {{ $penarikans->firstItem() }} - {{ $penarikans->lastItem() }} 
            dari {{ $penarikans->total() }} data
        </div>
        {{ $penarikans->links() }}
    </div>
    @endif

</div>

{{-- Modal Detail --}}
<div id="detailModal" class="modal-overlay">
    <div class="modal-container">
        <div class="modal-header">
            <h3 class="modal-title">Detail Penarikan</h3>
            <button class="modal-close" onclick="closeModal()">&times;</button>
        </div>
        <div class="modal-body">
            <form id="detailForm">
                <div class="form-group">
                    <label class="form-label">ID Penarikan</label>
                    <input type="text" id="detail-id" class="form-input" readonly>
                </div>
                
                <div class="form-group">
                    <label class="form-label">Nama Anggota</label>
                    <input type="text" id="detail-nama" class="form-input" readonly>
                </div>
                
                <div class="form-group">
                    <label class="form-label">Tanggal Pengajuan</label>
                    <input type="text" id="detail-tanggal" class="form-input" readonly>
                </div>
                
                <div class="form-group">
                    <label class="form-label">Jumlah Penarikan</label>
                    <input type="text" id="detail-jumlah" class="form-input" readonly style="font-weight: bold; color: var(--primary-green);">
                </div>
                
                <div class="form-group">
                    <label class="form-label">E-Wallet</label>
                    <input type="text" id="detail-jenis" class="form-input" readonly>
                    <input type="text" id="detail-ewallet" class="form-input" readonly style="margin-top: 0.5rem;" placeholder="Nomor E-Wallet">
                </div>
                
                <div class="form-group">
                    <label class="form-label">Status Saat Ini</label>
                    <input type="text" id="detail-status-text" class="form-input" readonly style="background: var(--gray-100); font-weight: 600;">
                </div>
                
                {{-- Update Status Section --}}
                <div style="border-top: 2px solid var(--gray-200); padding-top: 1.25rem; margin-top: 1.25rem;">
                    <label class="form-label" style="color: var(--primary-green); font-weight: 700;">Update Status</label>
                    <select id="detail-status" class="form-select" onchange="toggleStatusInfo()">
                        <option value="diproses">🔄 Diproses</option>
                        <option value="berhasil" style="color: green; font-weight: bold;">✅ Berhasil (Setujui)</option>
                        <option value="ditolak" style="color: red; font-weight: bold;">❌ Ditolak</option>
                    </select>
                </div>
                
                <div id="statusInfo" class="status-info"></div>
            </form>
        </div>
        <div class="modal-footer">
            <button class="btn btn-secondary" onclick="closeModal()">Batal</button>
            <button class="btn btn-primary" onclick="updateStatus()">Simpan Perubahan</button>
        </div>
    </div>
</div>

{{-- Form Delete (Hidden) --}}
<form id="deleteForm" method="POST" style="display: none;">
    @csrf
    @method('DELETE')
</form>

@endsection

@push('scripts')
<script>
    let currentId = null;

    // Format Rupiah
    const formatRupiah = (angka) => {
        return new Intl.NumberFormat('id-ID', {
            style: 'currency',
            currency: 'IDR',
            minimumFractionDigits: 0
        }).format(angka);
    };

    // Show Detail Modal
    function showDetail(id) {
        currentId = id;
        
        fetch(`/admin/bank-sampah/penarikan/${id}`)
            .then(response => response.json())
            .then(data => {
                document.getElementById('detail-id').value = `#TRX-${String(data.id_penarikan).padStart(5, '0')}`;
                document.getElementById('detail-nama').value = data.nama_user || 'Unknown';
                document.getElementById('detail-tanggal').value = new Date(data.tanggal_penarikan).toLocaleString('id-ID');
                document.getElementById('detail-jumlah').value = formatRupiah(data.jumlah_uang);
                document.getElementById('detail-jenis').value = (data.jenis_ewallet || '-').toUpperCase();
                document.getElementById('detail-ewallet').value = data.nomor_ewallet || '-';
                document.getElementById('detail-status-text').value = data.status.toUpperCase();
                document.getElementById('detail-status').value = data.status;
                
                toggleStatusInfo();
                document.getElementById('detailModal').classList.add('active');
            })
            .catch(error => {
                console.error('Error:', error);
                alert('Gagal mengambil data detail');
            });
    }

    // Close Modal
    function closeModal() {
        document.getElementById('detailModal').classList.remove('active');
        currentId = null;
    }

    // Toggle Status Info Message
    function toggleStatusInfo() {
        const status = document.getElementById('detail-status').value;
        const infoBox = document.getElementById('statusInfo');
        
        const messages = {
            'diproses': {
                text: '🔄 Status masih dalam proses verifikasi',
                class: 'info'
            },
            'berhasil': {
                text: '✅ Penarikan disetujui. Saldo sudah dipotong dan admin akan melakukan transfer manual.',
                class: 'success'
            },
            'ditolak': {
                text: '❌ Penarikan ditolak. Saldo akan dikembalikan otomatis ke anggota.',
                class: 'danger'
            }
        };
        
        infoBox.textContent = messages[status].text;
        infoBox.className = `status-info active ${messages[status].class}`;
    }

    // Update Status
    function updateStatus() {
        if (!currentId) return;
        
        const status = document.getElementById('detail-status').value;
        
        if (!confirm(`Yakin ingin mengubah status menjadi ${status.toUpperCase()}?`)) {
            return;
        }
        
        fetch(`/admin/bank-sampah/penarikan/${currentId}/status`, {
            method: 'PUT',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.content
            },
            body: JSON.stringify({ status: status })
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                alert('✅ ' + data.message);
                location.reload();
            } else {
                alert('❌ ' + data.message);
            }
        })
        .catch(error => {
            console.error('Error:', error);
            alert('❌ Terjadi kesalahan saat update status');
        });
    }

    // Delete Data
    function deleteData(id) {
        if (confirm('⚠️ Yakin ingin menghapus data penarikan ini?\n\nSaldo akan dikembalikan ke anggota.')) {
            const form = document.getElementById('deleteForm');
            form.action = `/admin/bank-sampah/penarikan/${id}`;
            form.submit();
        }
    }

    // Close modal when clicking outside
    document.getElementById('detailModal').addEventListener('click', function(e) {
        if (e.target === this) {
            closeModal();
        }
    });

    // Live Search
    let searchTimeout;
    document.getElementById('searchInput')?.addEventListener('input', function(e) {
        clearTimeout(searchTimeout);
        searchTimeout = setTimeout(() => {
            const term = e.target.value.toLowerCase().trim();
            const rows = document.querySelectorAll('#penarikanTable tbody tr');
            
            rows.forEach(row => {
                if (!row.classList.contains('empty-row')) {
                    const text = row.textContent.toLowerCase();
                    row.style.display = text.includes(term) ? '' : 'none';
                }
            });
        }, 300);
    });

    // Escape key to close modal
    document.addEventListener('keydown', function(e) {
        if (e.key === 'Escape') {
            closeModal();
        }
    });
</script>
@endpush