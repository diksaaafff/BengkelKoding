<?php

namespace App\Http\Controllers\Dokter;

use App\Http\Controllers\Controller;
use App\Models\DaftarPoli;
use App\Models\DetailPeriksa;
use App\Models\Obat;
use App\Models\Periksa;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class PeriksaPasienController extends Controller
{
    public function index()
    {
        $dokterId = Auth::id();

        $daftarPasien = DaftarPoli::with(['pasien', 'jadwalPeriksa', 'periksas'])
            ->whereHas('jadwalPeriksa', function ($query) use ($dokterId) {
                $query->where('id_dokter', $dokterId);
            })
            ->orderBy('no_antrian')
            ->get();

        return view('dokter.periksa-pasien.index', compact('daftarPasien'));
    }

    public function create($id)
    {
        $obats = Obat::all();
        return view('dokter.periksa-pasien.create', compact('obats', 'id'));
    }
        //validasii
    public function store(Request $request)
    {
        $request->validate([
            'obat_json' => 'required',
            'catatan' => 'nullable|string',
            'biaya_periksa' => 'required|integer',
        ]);

        $obatIds = json_decode($request->obat_json, true) ?? [];
        
        // Validasi stok di backend sebelum transaksi dimulai
        foreach ($obatIds as $idObat) {
            $obat = Obat::find($idObat);
            if (!$obat) {
                return back()->withErrors(['obat_error' => 'Obat tidak ditemukan.'])->withInput();
            }
            if ($obat->stok <= 0) {
                return back()->withErrors(['obat_error' => "Stok '{$obat->nama_obat}' telah habis! Mohon pilih obat lain."])->withInput();
            }
        }
      //disinii
        DB::beginTransaction();
        try {
            //2. Logika Perhitungan & Simpan ke Tabel Utama
            $periksa = Periksa::create([
                'id_daftar_poli' => $request->id_daftar_poli,
                'tgl_periksa' => now(),
                'catatan' => $request->catatan,
                'biaya_periksa' => $request->biaya_periksa + 150000,
            ]);

            //3. Logika Relasi Tabel / Transaksi Obat & Pengurangan Stok
            foreach ($obatIds as $idObat) {
                DetailPeriksa::create([
                    'id_periksa' => $periksa->id,
                    'id_obat' => $idObat,
                ]);

                // Kurangi stok obat
                $obat = Obat::findOrFail($idObat);
                $obat->decrement('stok');
            }

            DB::commit();
        } catch (\Exception $e) {
            DB::rollBack();
            return back()->withErrors(['db_error' => 'Terjadi kesalahan sistem saat menyimpan pemeriksaan: ' . $e->getMessage()])->withInput();
        }
        //sini
        return redirect()->route('periksa-pasien.index')->with('success', 'Data periksa berhasil disimpan.');
    }
}
