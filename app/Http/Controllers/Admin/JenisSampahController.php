<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\JenisSampah;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class JenisSampahController extends Controller
{
    public function index()
    {
        // Ganti order by created_at dengan id
        $jenisSampah = JenisSampah::orderBy('id_jenis_sampah', 'desc')->paginate(10);
        return view('admin.bank-sampah.jenis-sampah.index', compact('jenisSampah'));
    }

    public function create()
    {
        return view('admin.bank-sampah.jenis-sampah.create');
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'gambar' => 'nullable|image|mimes:jpg,jpeg,png|max:2048',
            'jenis' => 'required|string|max:100',
            'satuan' => 'required|in:Kg,Lt,Pcs,Pack,Lusin',
            'harga' => 'required|numeric|min:0',
        ]);

        if ($request->hasFile('gambar')) {
            $validated['gambar'] = $request->file('gambar')->store('jenis-sampah', 'public');
        }

        JenisSampah::create($validated);

        return redirect()->route('admin.bank-sampah.jenis-sampah.index')
            ->with('success', 'Jenis sampah berhasil ditambahkan!');
    }

    public function edit($id)
    {
        $jenisSampah = JenisSampah::findOrFail($id);
        return view('admin.bank-sampah.jenis-sampah.edit', compact('jenisSampah'));
    }

    public function update(Request $request, $id)
    {
        $jenisSampah = JenisSampah::findOrFail($id);

        $validated = $request->validate([
            'gambar' => 'nullable|image|mimes:jpg,jpeg,png|max:2048',
            'jenis' => 'required|string|max:100',
            'satuan' => 'required|in:Kg,Lt,Pcs,Pack,Lusin',
            'harga' => 'required|numeric|min:0',
        ]);

        if ($request->hasFile('gambar')) {
            if ($jenisSampah->gambar && Storage::disk('public')->exists($jenisSampah->gambar)) {
                Storage::disk('public')->delete($jenisSampah->gambar);
            }
            $validated['gambar'] = $request->file('gambar')->store('jenis-sampah', 'public');
        }

        $jenisSampah->update($validated);

        return redirect()->route('admin.bank-sampah.jenis-sampah.index')
            ->with('success', 'Jenis sampah berhasil diperbarui!');
    }

    public function destroy($id)
    {
        $jenisSampah = JenisSampah::findOrFail($id);

        if ($jenisSampah->gambar && Storage::disk('public')->exists($jenisSampah->gambar)) {
            Storage::disk('public')->delete($jenisSampah->gambar);
        }

        $jenisSampah->delete();

        return redirect()->back()->with('success', 'Jenis sampah berhasil dihapus!');
    }
}
