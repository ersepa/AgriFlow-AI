<?php

namespace App\Http\Controllers;

use App\Models\Harvest;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class HarvestController extends Controller
{
    public function index()
    {
        $harvests = Harvest::latest()->get();

        return view('harvests.index', compact('harvests'));
    }

    public function create()
    {
        return view('harvests.create');
    }

    public function store(Request $request)
    {
        Harvest::create([
            'user_id' => Auth::id(),
            'commodity' => $request->commodity,
            'weight' => $request->weight,
            'location' => $request->location,
            'harvest_date' => $request->harvest_date,
            'expiry_date' => $request->expiry_date,
        ]);

        return redirect()->route('harvests.index');
    }
    public function destroy($id)
{
    // Cari data berdasarkan ID
    $harvest = \App\Models\Harvest::findOrFail($id);
    
    // Hapus data
    $harvest->delete();

    // Redirect dengan pesan sukses
    return redirect()->route('harvests.index')->with('success', 'Data panen berhasil dihapus!');
}
}