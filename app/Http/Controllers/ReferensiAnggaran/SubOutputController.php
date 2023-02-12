<?php

namespace App\Http\Controllers\ReferensiAnggaran;

use App\Libraries\BearerKey;
use App\Http\Controllers\Controller;
use App\Libraries\TarikDataMonsakti;
use App\Models\ReferensiAnggaran\SubOutputModel;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Yajra\DataTables\DataTables;

class SubOutputController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
    }

    public function ambildatasuboutput(Request $request){
        $data['suboutput'] = DB::table('suboutput')
            ->where('kodekegiatan','=',$request->kodekegiatan)
            ->where('kodeoutput','=',$request->kodeoutput)
            ->get(['kodesuboutput','deskripsi']);

        return response()->json($data);
    }


    function suboutput(){
        $judul = "List Sub Output";
        return view('ReferensiAnggaran.suboutput',[
            "judul"=>$judul
        ]);
    }

    public function getListSubOutput(Request $request){
        if ($request->ajax()) {
            $data = SubOutputModel::all();
            return Datatables::of($data)
                ->addIndexColumn()
                ->make(true);
        }
    }

    function importsuboutput(){
        $tahunanggaran = session('tahunanggaran');
        $kodemodul = 'ADM';
        $tipedata = 'refUraian';
        $variable = ['suboutput'];

        $response = new TarikDataMonsakti();
        $response = $response->prosedurlengkap($tahunanggaran, $kodemodul, $tipedata, $variable);
        if ($response != "Gagal" or $response != "Expired"){
            $hasilasli = json_decode($response);
            //echo json_encode($hasilasli);
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
                        $KODEKEGIATAN = substr($KODE,0,4);
                        $KODEOUTPUT = substr($KODE,5,3);
                        $KODESUBOUTPUT = substr($KODE,9,3);
                        $DESKRIPSI = $data->DESKRIPSI;
                        $SATUAN = $data->SATUAN;
                        $databaru = array(
                            'tahunanggaran' => $THANG,
                            'kode' => $KODE,
                            'kodekegiatan' => $KODEKEGIATAN,
                            'kodeoutput' => $KODEOUTPUT,
                            'kodesuboutput' => $KODESUBOUTPUT,
                            'deskripsi' => $DESKRIPSI,
                            'satuan' => $SATUAN
                        );

                        $where = array(
                            'kode' => $KODE,
                            'tahunanggaran' => $tahunanggaran
                        );
                        $jumlah = DB::table('suboutputall')->where($where)->count();
                        if ($jumlah == 0){
                            DB::table('suboutputall')->insert($databaru);
                        }else{
                            DB::table('suboutputall')->where($where)->update($databaru);
                        }
                    }
                }
            }
            $this->importsuboutputdpr();
            return redirect()->to('suboutput')->with('status',"Import SubOutput Berhasil");
        }else if ($response == "Expired"){

                $tokenbaru = new BearerKey();
                $tokenbaru->resetapi($tahunanggaran, $kodemodul, $tipedata);
                return redirect()->to('suboutput')->with(['status' => 'Token Expired']);
        }else{
            return redirect()->to('suboutput')->with(['status' => 'Gagal, Data Terlalu Besar']);
        }
    }

    function importsuboutputdpr(){
        $tahunanggaran = session('tahunanggaran');
        $datasatker = ['001012','001030'];
        $statusimport = "";

        foreach ($datasatker as $satker){
            //dapatkan data IDREFSTATUS terakhir
            $idrefstatus = DB::table('ref_status')
                ->where([
                    ['tahunanggaran','=',$tahunanggaran],
                    ['kd_sts_history','LIKE','B%'],
                    ['kdsatker','=',$satker]
                ])->orwhere([
                    ['kd_sts_history','LIKE','C%'],
                    ['kdsatker','=',$satker],
                    ['tahunanggaran','=',$tahunanggaran],
                    ['flag_update_coa','=',1]])
                ->max('idrefstatus');

            //dapatkan info kd_sts_history
            //$kd_sts_history = DB::table('ref_status')->where('idrefstatus','=',$idrefstatus)->value('kd_sts_history');

            //dapatkan data anggaran
            $dataanggaran = DB::table('data_ang')
                ->where('idrefstatus','=',$idrefstatus)
                ->get();
            //echo $dataanggaran;

            if (count($dataanggaran) === 0) {
                $statusimport = $statusimport.$satker." Data Ang Terakhir Belum Diimport ";
            }else{
                foreach ($dataanggaran as $item){
                    $tahunanggaran = $item->tahunanggaran;
                    $kodekegiatan = $item->kodekegiatan;
                    $kodeoutput = $item->kodeoutput;
                    $kodesuboutput = $item->kodesuboutput;
                    $kode = $kodekegiatan.".".$kodeoutput.".".$kodesuboutput;
                    $uraiansuboutput = DB::table('suboutputall')
                        ->where('tahunanggaran','=',$tahunanggaran)
                        ->where('kode','=',$kode)
                        ->value('deskripsi');
                    $satuansuboutput = DB::table('suboutputall')
                        ->where('tahunanggaran','=',$tahunanggaran)
                        ->where('kode','=',$kode)
                        ->value('satuan');

                    $data = array(
                        'tahunanggaran' => $tahunanggaran,
                        'kodekegiatan' => $kodekegiatan,
                        'kodeoutput' => $kodeoutput,
                        'kodesuboutput' => $kodesuboutput,
                        'kode' => $kode,
                        'deskripsi' => $uraiansuboutput,
                        'satuan' => $satuansuboutput
                    );

                    SubOutputModel::updateOrCreate([
                        'tahunanggaran' => $tahunanggaran,
                        'kode' => $kode
                    ],$data);
                }
            }
        }
    }
}
