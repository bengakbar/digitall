<?php

namespace App\Http\Controllers\ReferensiAnggaran;

use App\Libraries\BearerKey;
use App\Http\Controllers\Controller;
use App\Libraries\TarikDataMonsakti;
use App\Models\ReferensiAnggaran\ProgramModel;
use Illuminate\Http\Request;
use Yajra\DataTables\DataTables;

class ProgramController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
    }
    function program(){
        $judul = "List Program";
        return view('ReferensiAnggaran.program',[
            "judul"=>$judul
        ]);
    }

    public function getListProgram(Request $request){
        if ($request->ajax()) {
            $data = ProgramModel::all();
            return Datatables::of($data)
                ->addIndexColumn()
                ->make(true);
        }
    }

    function importprogram(){
        $tahunanggaran = session('tahunanggaran');
        $kodemodul = 'ADM';
        $tipedata = 'refUraian';
        $variable = ['program'];

        $response = new TarikDataMonsakti();
        $response = $response->prosedurlengkap($tahunanggaran, $kodemodul, $tipedata, $variable);

        if ($response != "Gagal" or $response != "Expired"){
            $hasilasli = json_decode($response);
            foreach ($hasilasli as $item => $value) {
                if ($item == "TOKEN") {
                    foreach ($value as $data) {
                        $tokenresponse = $data->TOKEN;
                    }
                    $token = new BearerKey();
                    $token->simpantokenbaru($tahunanggaran, $kodemodul, $tokenresponse);
                }
            }
            foreach ($hasilasli as $item => $value) {
                if ($item != "TOKEN") {
                    foreach ($value as $data) {
                        $THANG = $data->THANG;
                        $KODE = $data->KODE;
                        $DESKRIPSI = $data->DESKRIPSI;

                        $where = array(
                            'tahunanggaran' => $THANG,
                            'kode' => $KODE
                        );

                        $jumlah = ProgramModel::where($where)->get()->count();
                        if ($jumlah == 0) {
                            $data = array(
                                'tahunanggaran' => $THANG,
                                'kode' => $KODE,
                                'uraianprogram' => $DESKRIPSI
                            );
                            ProgramModel::insert($data);
                        }
                    }
                }
            }
            return redirect()->to('program')->with('status','Import Program Berhasil');
        }else if ($response == "Expired"){

                $tokenbaru = new BearerKey();
                $tokenbaru->resetapi($tahunanggaran, $kodemodul, $tipedata);
                return redirect()->to('program')->with(['status' => 'Token Expired']);
        }else{
            return redirect()->to('program')->with(['status' => 'Gagal, Data Terlalu Besar']);
        }
    }
}
