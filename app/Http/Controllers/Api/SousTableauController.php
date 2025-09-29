<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\SousTableau;
use App\Models\Variable;
use Illuminate\Http\Request;

class SousTableauController extends Controller
{
    
    public function index()
    {
        return SousTableau::with('variables')->get();
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'tableau_id' => 'required|exists:tableaux,id',
            'nom' => 'required|string',
            'budget_prevu' => 'required|numeric',
        ]);
        return SousTableau::create($data);
    }

    public function show($id)
    {
        return SousTableau::with('variables')->findOrFail($id);
    }

    public function update(Request $request, $id)
    {
        $sousTableau = SousTableau::findOrFail($id);
        $sousTableau->update($request->only([
            'nom', 'budget_prevu'
        ]));
        return $sousTableau;
    }

    public function destroy($id)
    {
        SousTableau::destroy($id);
        
        return response()->json(['message' => 'Supprimé']);
    }
}
