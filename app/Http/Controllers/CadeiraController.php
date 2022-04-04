<?php

namespace App\Http\Controllers;

use App\Http\Resources\CadeiraResource;
use App\Http\Resources\InscricaoucsResource;
use App\Models\Cadeira;
use App\Models\Inscricaoucs;
use App\Models\Utilizador;

class CadeiraController extends Controller
{
    public function getCadeirasUtilizador(Utilizador $utilizador){
        if($utilizador->tipo == 0){ //estudante
            InscricaoucsResource::$format = 'cadeiras';
            return response(InscricaoucsResource::collection($utilizador->inscricaoucs),200);
        }
    }

    public function getCadeirasNaoAprovadasUtilizador(Utilizador $utilizador){
        if($utilizador->tipo == 0){ //estudante
            InscricaoucsResource::$format = 'cadeiras';

            $dados = Cadeira::where('idCurso', $utilizador->idCurso)->leftJoin('inscricaoucs', function ($join) use(&$utilizador) {
                $join->on('cadeira.id', '=', 'inscricaoucs.idCadeira')
                     ->where('inscricaoucs.idUtilizador','=',$utilizador->id);
            })->select('inscricaoucs.*', 'cadeira.*' )->get();
            CadeiraResource::$format = 'inscricaoucs';
            return response(CadeiraResource::collection($dados),200);
        }
    }
}
