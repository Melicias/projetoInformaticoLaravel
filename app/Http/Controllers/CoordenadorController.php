<?php

namespace App\Http\Controllers;

use App\Http\Requests\CoordenadorPostRequest;
use App\Http\Resources\CoordenadorResource;
use App\Models\coordenador;
use Illuminate\Http\Request;

class CoordenadorController extends Controller
{
    public function index()
    {
    	return response(CoordenadorResource::collection(Coordenador::all()),200);
    }

    public function store(CoordenadorPostRequest $request){
        $data = collect($request->validated());
        //dd($data);
        $coordenador = Coordenador::where('idUtilizador',$data->get('idUtilizador'))->where('idCurso',$data->get('idCurso'))->first();
        if(!empty($coordenador)){
            return response("Este utilizador já é administrador da cadeira!",401);
        }
        $coordenador = new Coordenador();
        $coordenador->idUtilizador = $data->get('idUtilizador');
        $coordenador->idCurso = $data->get('idCurso');
        $coordenador->tipo = $data->get('tipo');
        $coordenador->save();

        

        return response(201);
    }

    public function remove(Coordenador $coordenador){
        $coordenador->delete();
        return response(202);
    }
}