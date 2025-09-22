<?php
namespace App\Http\Controllers;

use App\Models\TireLayout;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;

class TireLayoutController extends Controller
{
    public function index()
    {
        if (!\Illuminate\Support\Facades\Schema::hasTable('tire_layouts')) {
            return view('tire-layouts.index', [
                'layouts' => collect([]),
                'missingTable' => true,
            ]);
        }
        $layouts = TireLayout::orderBy('name')->paginate(15);
        return view('tire-layouts.index', compact('layouts'));
    }

    public function create()
    {
        return view('tire-layouts.create');
    }

    public function store(Request $request)
    {
        $data = $this->validateData($request);
        TireLayout::create($data);
        return redirect()->route('tire-layouts.index')->with('success','Layout criado.');
    }

    public function edit(TireLayout $tireLayout)
    {
        return view('tire-layouts.edit', ['layout'=>$tireLayout]);
    }

    public function update(Request $request, TireLayout $tireLayout)
    {
        $data = $this->validateData($request, $tireLayout->id);
        $tireLayout->update($data);
        return redirect()->route('tire-layouts.index')->with('success','Layout atualizado.');
    }

    public function destroy(TireLayout $tireLayout)
    {
        if ($tireLayout->categories()->exists()) {
            return redirect()->route('tire-layouts.index')->with('error','Layout em uso por categorias; remova a associação antes de excluir.');
        }
        $tireLayout->delete();
        return redirect()->route('tire-layouts.index')->with('success','Layout removido.');
    }

    private function validateData(Request $request, ?int $id = null): array
    {
        $validated = $request->validate([
            'name' => 'required|string|max:100',
            'code' => 'required|string|max:60|unique:tire_layouts,code'.($id?','.$id:''),
            'description' => 'nullable|string|max:1000',
            'positions' => 'required|string',
        ]);
        $positionsRaw = json_decode($validated['positions'], true);
        if (!is_array($positionsRaw) || empty($positionsRaw)) {
            throw ValidationException::withMessages(['positions'=>'JSON inválido ou vazio']);
        }
        $normalized = [];
        $codes = [];
        foreach ($positionsRaw as $p) {
            if (!isset($p['code'])) {
                throw ValidationException::withMessages(['positions'=>'Cada posição precisa de "code"']);
            }
            $c = strtoupper(trim($p['code']));
            if (in_array($c,$codes)) {
                throw ValidationException::withMessages(['positions'=>'Código duplicado: '.$c]);
            }
            $codes[] = $c;
            $label = $p['label'] ?? $c;
            $entry = ['code'=>$c,'label'=>$label];
            foreach(['x','y'] as $coord){
                if(isset($p[$coord])) {
                    $val = $p[$coord];
                    if(!is_numeric($val) || $val < 0 || $val > 100) {
                        throw ValidationException::withMessages(['positions'=>'Coordenada '.$coord.' inválida para '.$c.' (0-100)']);
                    }
                    $entry[$coord] = (float)$val;
                }
            }
            $normalized[] = $entry;
        }
        $validated['positions'] = json_encode($normalized);
        return $validated;
    }
}
