<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\User;
use App\Models\Vehicle; // Exemplo para futuras pesquisas
use Illuminate\Database\Eloquent\Collection;

class SearchController extends Controller
{
    public function search(Request $request)
    {
        $query = $request->input('query');
        $model = $request->input('model');
        $results = new Collection();


        if (empty($query) || empty($model)) {
            return view('partials.search-results', [
                'results' => new Collection(), // Passa uma coleção vazia
                'model' => $model
            ]);
        }


        $query = $request->input('query');
        $model = $request->input('model');

        if (empty($query) || empty($model)) {
            return response()->json([]);
        }

        $results = [];

        switch ($model) {
            case 'user':
                $userQuery = User::query();

                if (!empty($query)) {
                    $userQuery->where(function ($q) use ($query) {
                        $q->where('name', 'LIKE', "%{$query}%")
                            ->orWhere('email', 'LIKE', "%{$query}%")
                            ->orWhere('cpf', 'LIKE', "%{$query}%");
                    });
                }

                // Agora usamos paginate() em vez de limit() e get()
                $results = $userQuery->with('role')->latest()->paginate(15);

                // Adiciona o termo de pesquisa aos links de paginação
                $results->appends(['query' => $query, 'model' => $model]);

                break;

            // Exemplo de como adicionar pesquisa para veículos no futuro
            /*
            case 'vehicle':
                $results = Vehicle::where('plate', 'LIKE', "%{$query}%")
                                   ->orWhere('model', 'LIKE', "%{$query}%")
                                   ->limit(10)
                                   ->get();
                break;
            */
        }

        // Retorna a view parcial com os resultados
        return view('partials.users-table-rows', ['users' => $results]);
    }
}
