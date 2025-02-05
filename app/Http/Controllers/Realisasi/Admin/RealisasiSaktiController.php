<?php

namespace App\Http\Controllers\Realisasi\Admin;

use App\Exports\ExportRealisasiSakti;
use App\Http\Controllers\Controller;
use App\Jobs\ImportRealisasiSakti;
use App\Jobs\RekapRealisasiHarian;
use App\Libraries\BearerKey;
use App\Libraries\TarikDataMonsakti;
use App\Models\Realisasi\Admin\RealisasiSaktiModel;
use App\Models\ReferensiUnit\BagianModel;
use App\Models\ReferensiUnit\BiroModel;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Maatwebsite\Excel\Facades\Excel;
use Yajra\DataTables\DataTables;

class RealisasiSaktiController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
    }

    public function index(Request $request)
    {
        $tahunanggaran = session('tahunanggaran');
        $judul = 'Data Realisasi';
        $datarealisasisetjen = DB::table('laporanrealisasianggaranbac')
            ->select(DB::raw('sum(paguanggaran) as pagu, sum(rsd12) as realisasi, (sum(rsd12)/sum(paguanggaran))*100 as prosentase'))
            ->where('kodesatker','=','001012')
            ->where('tahunanggaran','=',$tahunanggaran)
            ->get();
        foreach ($datarealisasisetjen  as $rs){
            $pagusetjen = $rs->pagu;
            $realisasisetjen = $rs->realisasi;
            $prosentasesetjen = $rs->prosentase;
        }

        $datarealisasidewan = DB::table('laporanrealisasianggaranbac')
            ->select(DB::raw('sum(paguanggaran) as pagu, sum(rsd12) as realisasi, (sum(rsd12)/sum(paguanggaran))*100 as prosentase'))
            ->where('kodesatker','=','001030')
            ->where('tahunanggaran','=',$tahunanggaran)
            ->get();
        foreach ($datarealisasidewan as $drd){
            $pagudewan = $drd->pagu;
            $realisasidewan = $drd->realisasi;
            $prosentasedewan = $drd->prosentase;
        }
        return view('realisasi.admin.realisasisakti',[
            "judul"=>$judul,
            "pagusetjen" => $pagusetjen,
            "realisasisetjen" => $realisasisetjen,
            "prosentasesetjen" => $prosentasesetjen,
            "pagudewan" => $pagudewan,
            "realisasidewan" => $realisasidewan,
            "prosentasedewan" => $prosentasedewan
        ]);
    }

    public function getdetilrealisasi(Request $request){
        $tahunanggaran = session('tahunanggaran');
        if ($request->ajax()) {
            $data = RealisasiSaktiModel::with('bagianrelation')
                ->with('birorelation')
                ->select(['realisasisakti.*'])
                ->where('THNANG','=',$tahunanggaran);
            return Datatables::of($data)
                ->addColumn('bagian', function (RealisasiSaktiModel $id) {
                    return $id->ID_BAGIAN ? $id->bagianrelation->uraianbagian:"";
                })
                ->addColumn('biro', function (RealisasiSaktiModel $id) {
                    return $id->ID_BIRO ? $id->birorelation->uraianbiro:"";
                })
                ->rawColumns(['bagian','biro'])
                ->make(true);
        }
    }

    function exportdetilrealisasi(){
        $tahunanggaran = session('tahunanggaran');
        //Excel::download(new UsersExport, 'users.xlsx');
        return Excel::download(new ExportRealisasiSakti($tahunanggaran),'RealisasiSAKTI.xlsx');
    }

    function importrealisasisakti(){
        $tahunanggaran = session('tahunanggaran');
        $this->dispatch(new ImportRealisasiSakti($tahunanggaran));
        return redirect()->to('realisasisakti')->with('status','Proses Import Realisasi SAKTI Berhasil Dijalankan');
    }

    function rekaprealisasiharian(){
        $tahunanggaran = session('tahunanggaran');
        $this->dispatch(new RekapRealisasiHarian($tahunanggaran));
        return redirect()->to('realisasisakti')->with('status','Proses Rekap Realisasi SAKTI Berhasil Dijalankan, Cek Secara Berkala');
    }

    function aksiimportrealisasisakti($tahunanggaran){
        DB::table('realisasisakti')->where('THNANG','=',$tahunanggaran)->delete();

        $kodemodul = 'PEM';
        $tipedata = 'realisasi';

        //reset api
        $resetapi = new BearerKey();
        $resetapi = $resetapi->resetapi($tahunanggaran, $kodemodul, $tipedata);

        //tarikdata
        $response = new TarikDataMonsakti();
        $response = $response->prosedurlengkap($tahunanggaran, $kodemodul, $tipedata);
        //echo json_encode($response);


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
                }else{
                    foreach ($value as $DATA) {
                        $THNANG = $tahunanggaran;
                        $KDSATKER = $DATA->KDSATKER;
                        $KODE_KEMENTERIAN = $DATA->KODE_KEMENTERIAN;
                        $KD_JNS_SPP = $DATA->KD_JNS_SPP;
                        $NO_SPP = $DATA->NO_SPP;
                        $TGL_SPP = new \DateTime($DATA->TGL_SPP);
                        $TGL_SPP = $TGL_SPP->format('Y-m-d');
                        $NO_SPM = $DATA->NO_SPM;
                        $TGL_SPM = new \DateTime($DATA->TGL_SPM);
                        $TGL_SPM = $TGL_SPM->format('Y-m-d');
                        $NO_SP2D = $DATA->NO_SP2D;
                        $TGL_SP2D = new \DateTime($DATA->TGL_SP2D);
                        $TGL_SP2D = $TGL_SP2D->format('Y-m-d');

                        $bulan = new \DateTime($TGL_SP2D);
                        $bulan = $bulan->format('n');

                        $NO_SP2B = $DATA->NO_SP2B;
                        $TGL_SP2B = new \DateTime($DATA->TGL_SP2B);
                        $TGL_SP2B = $TGL_SP2B->format('Y-m-d');
                        $NO_SP3HL_BJS = $DATA->NO_SP3HL_BJS;
                        $TGL_SP23HL_BJS = new \DateTime($DATA->TGL_SP2B);
                        $TGL_SP23HL_BJS = $TGL_SP23HL_BJS->format('Y-m-d');
                        $URAIAN = $DATA->URAIAN;
                        $KODE_COA = $DATA->KODE_COA;
                        $KODE_PROGRAM = $DATA->KODE_PROGRAM;
                        $KODE_KEGIATAN = $DATA->KODE_KEGIATAN;
                        $KODE_OUTPUT = $DATA->KODE_OUTPUT;
                        $KODE_SUBOUTPUT = $DATA->KODE_SUBOUTPUT;
                        $KODE_KOMPONEN = $DATA->KODE_KOMPONEN;
                        $KODE_SUBKOMPONEN = substr($DATA->KODE_SUBKOMPONEN, 1,1);
                        $KODE_AKUN = $DATA->KODE_AKUN;
                        $KODE_ITEM = $DATA->KODE_ITEM;
                        $PENGENAL = $THNANG.".".$KDSATKER.".".$KODE_PROGRAM.'.'.$KODE_KEGIATAN.'.'.$KODE_OUTPUT.'.'.$KODE_SUBOUTPUT.'.'.$KODE_KOMPONEN.'.'.$KODE_SUBKOMPONEN.'.'.$KODE_AKUN;
                        $MATA_UANG = $DATA->MATA_UANG;
                        $KURS = $DATA->KURS;
                        $NILAI_VALAS = $DATA->NILAI_VALAS;
                        $NILAI_RUPIAH = $DATA->NILAI_RUPIAH;
                        $STATUS_DATA = $DATA->STATUS_DATA;
                        //cek data idbagian dll
                        if ($KDSATKER == '001012'){
                            $indeks = $THNANG.$KDSATKER.$KODE_PROGRAM.$KODE_KEGIATAN.$KODE_OUTPUT.$KODE_SUBOUTPUT.$KODE_KOMPONEN.$KODE_SUBKOMPONEN;
                        }else{
                            $indeks = $THNANG.$KDSATKER.$KODE_PROGRAM.$KODE_KEGIATAN.$KODE_OUTPUT.$KODE_SUBOUTPUT.$KODE_KOMPONEN;
                        }
                        $datapengenal = DB::table('anggaranbagian')->where('indeks','=',$indeks)->get();
                        $ID_DEPUTI = 0;
                        $ID_BIRO = 0;
                        $ID_BAGIAN = 0;
                        foreach ($datapengenal as $D){
                            $ID_BAGIAN = $D->idbagian;
                            $ID_BIRO = $D->idbiro;
                            $ID_DEPUTI = $D->iddeputi;
                        }

                        $datainsert = array(
                            'THNANG' => $THNANG,
                            'KDSATKER' => $KDSATKER,
                            'KODE_KEMENTERIAN' => $KODE_KEMENTERIAN,
                            'KD_JNS_SPP' => $KD_JNS_SPP,
                            'NO_SPP' => $NO_SPP,
                            'TGL_SPP' => $TGL_SPP,
                            'NO_SPM' => $NO_SPM,
                            'TGL_SPM' => $TGL_SPM,
                            'NO_SP2D' => $NO_SP2D,
                            'TGL_SP2D' => $TGL_SP2D,
                            'BULAN_SP2D' => $bulan,
                            'NO_SP2B' => $NO_SP2B,
                            'TGL_SP2B' => $TGL_SP2B,
                            'NO_SP3HL_BJS' => $NO_SP3HL_BJS,
                            'TGL_SP3HL_BJS' => $TGL_SP23HL_BJS,
                            'URAIAN' => $URAIAN,
                            'KODE_COA' => $KODE_COA,
                            'KODE_PROGRAM' => $KODE_PROGRAM,
                            'KODE_KEGIATAN' => $KODE_KEGIATAN,
                            'KODE_OUTPUT' => $KODE_OUTPUT,
                            'KODE_SUBOUTPUT' => $KODE_SUBOUTPUT,
                            'KODE_KOMPONEN' => $KODE_KOMPONEN,
                            'KODE_SUBKOMPONEN' => $KODE_SUBKOMPONEN,
                            'KODE_AKUN' => $KODE_AKUN,
                            'KODE_ITEM' => $KODE_ITEM,
                            'PENGENAL' => $PENGENAL,
                            'MATA_UANG' => $MATA_UANG,
                            'KURS' => $KURS,
                            'NILAI_VALAS' => $NILAI_VALAS,
                            'NILAI_RUPIAH' => $NILAI_RUPIAH,
                            'STATUS_DATA' => $STATUS_DATA,
                            'ID_BAGIAN' => $ID_BAGIAN,
                            'ID_BIRO' => $ID_BIRO,
                            'ID_DEPUTI' => $ID_DEPUTI
                        );
                        DB::table('realisasisakti')->insert($datainsert);
                    }
                }
            }
        }
    }
}
