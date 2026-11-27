<?php

namespace App\Http\Controllers;

use App\Models\Harvest;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Http\RedirectResponse;

class HarvestController extends Controller
{
    public function index()
    {
        $harvests = Harvest::query()
            ->where('user_id', Auth::id())
            ->latest()
            ->get();

        return view('harvests.index', compact('harvests'));
    }

    public function create()
    {
        return view('harvests.create');
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'commodity' => ['required', 'string', 'max:255'],
            'weight' => ['required', 'numeric', 'gt:0'],
            'location' => ['required', 'string', 'max:255'],
            'harvest_date' => ['required', 'date'],
            'expiry_date' => [
    'required',
    'date',
    'after_or_equal:harvest_date',
],
        ]);

        Harvest::create([
            'user_id' => Auth::id(),
            'commodity' => $validated['commodity'],
            'weight' => $validated['weight'],
            'location' => $validated['location'],
            'harvest_date' => $validated['harvest_date'],
            'expiry_date' => $validated['expiry_date'] ?? null,
        ]);

        return redirect()
            ->route('harvests.index')
            ->with('success', 'Data panen berhasil disimpan.');
    }

    public function destroy(int $id): RedirectResponse
    {
        $harvest = Harvest::query()
            ->where('user_id', Auth::id())
            ->findOrFail($id);

        $harvest->delete();

        return redirect()
            ->route('harvests.index')
            ->with('success', 'Data panen berhasil dihapus.');
    }
}