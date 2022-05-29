<?php

namespace App\Http\Controllers;


use App\Models\Turno;
use App\Models\Anoletivo;
use App\Models\Inscricao;
use App\Models\Utilizador;
use App\Services\InscricaoService;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use App\Http\Resources\InscricaoResource;
use App\Http\Requests\InscricaoPostRequest;


class InscricaoController extends Controller
{
    public function store(InscricaoPostRequest $request){
        //fazer inscricao
        $idTurnosAceites = [];
        $turnosRejeitados = [];
        $idCadeiras = [];

        $data = collect($request->validated());
        $canBeCreated = (new InscricaoService)->checkData($data);

        if($canBeCreated['response'] == 0){
            return response($canBeCreated['erro'], 401);
        }

        if ($canBeCreated['response'] == 2) {
            $idTurnosAceites = $canBeCreated['idTurnosAceites'];
            $turnosRejeitados = $canBeCreated['rejeitados'];
        } else if($canBeCreated['response'] == 1){
            $idTurnosAceites = $data->get('turnosIds');
        } 

        $inscricoesAtuais = Inscricao::join('turno', function ($join) {
            $join->on('turno.id', '=', 'idTurno')->where('idUtilizador', '=', Auth::user()->id);
        })->select('inscricao.id', 'turno.id as turnoId','turno.idCadeira','turno.tipo')->get();

        //verificar se houve algum turno retirado, se foi entao apaga
        foreach ($inscricoesAtuais as $inscricaoAtual) {
            if (!in_array($inscricaoAtual->turnoId, $idTurnosAceites)) {
                $mudanca = 0;
                foreach ($turnosRejeitados as $key => $rejeitado) {
                    if($rejeitado->idCadeira == $inscricaoAtual->idCadeira && $rejeitado->tipo == $inscricaoAtual->tipo){
                        $mudanca = 1;
                        break;
                    }
                }
                if($mudanca == 0){
                    $inscricao = (new InscricaoService)->remove($inscricaoAtual->id, $inscricaoAtual->turnoId);
                    unset($idTurnosAceites[$inscricaoAtual->turnoId]);
                    array_push($idCadeiras,$inscricaoAtual->idCadeira);
                }    
            }else{
                unset($idTurnosAceites[$inscricaoAtual->turnoId]);
            }
        }

        $anoletivo = Anoletivo::where("ativo", 1)->first();
        $idsTurnos = DB::table('turno')->select('id','tipo','idCadeira')->whereIn('id', $idTurnosAceites)->get();
        foreach($idsTurnos as $turno){
            $subquery = "select i.*, t.tipo, t.idCadeira as cadeiraId from inscricao i join turno t on t.id = i.idTurno where i.idUtilizador = " . Auth::user()->id . " and t.tipo = '" . $turno->tipo . "' and t.idCadeira = '" . $turno->idCadeira . "' and t.idAnoletivo = " . $anoletivo->id;
            $inscricoes = DB::select(DB::raw($subquery));
            if (sizeof($inscricoes) == 0) {
                $inscricao = (new InscricaoService)->save(Auth::user()->id, $turno->id);
                if($inscricao != null){
                    if(!in_array($turno->id,$idCadeiras)){
                        array_push($idCadeiras,$turno->id);
                    }
                }
            }else{
                $inscricao = Inscricao::find($inscricoes[0]->id);
                if (!empty($inscricao)) {
                    $inscricao = (new InscricaoService)->update($inscricao, $turno->id);
                    if($inscricao != null){
                        if(!in_array($turno->id,$idCadeiras)){
                            array_push($idCadeiras,$turno->id);
                        }
                    }
                }
            }            
        }

        if ($canBeCreated['response'] == 2) {
            return response(["rejeitados" => $canBeCreated['rejeitados'], "idsCadeiras" => $idCadeiras], 201);
        } else if($canBeCreated['response'] == 1){
            return response(["idsCadeiras" => $idCadeiras],201);
        }

    }

    public function store2(InscricaoPostRequest $request){
        //fazer inscricao
        $idTurnosAceites = [];

        $data = collect($request->validated());
        $canBeCreated = (new InscricaoService)->checkData($data);

        if($canBeCreated['response'] == 0){
            return response($canBeCreated['erro'], 401);
        }

        if ($canBeCreated['response'] == 2) {
            $idTurnosAceites = $canBeCreated['idTurnosAceites'];
        } else if($canBeCreated['response'] == 1){
            $idTurnosAceites = $data->get('turnosIds');
        } 
        

    }

    public function delete(Inscricao $inscricao){
        $turnoId = $inscricao->idTurno;
        $del = $inscricao->delete();
        if(!$del){
            return response("Erro ao apagar a inscrição". 400);
        }
        Turno::where('id', $turnoId)->update(['vagasocupadas' => DB::raw('vagasocupadas-1')]);
        return response("Inscrição apagada com sucesso". 200);
    }
}
