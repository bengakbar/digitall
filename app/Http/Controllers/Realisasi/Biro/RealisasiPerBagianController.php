<?php

namespace App\Http\Controllers\Realisasi\Biro;

use App\Exports\ExportRealisasiBagianPerPengenal;
use App\Exports\ExportRealisasiPengenal;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Maatwebsite\Excel\Facades\Excel;
use Yajra\DataTables\DataTables;

class RealisasiPerBagianController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
    }

    public function index(Request $request)
    {
        $judul = 'Realisasi Per Bagian';
        $idbiro = Auth::user()->idbiro;
        $tahunanggaran = session('tahunanggaran');
        $datarealisasisetjen = DB::table('laporanrealisasianggaranbac')
            ->select(DB::raw('sum(paguanggaran) as pagu, sum(rsd12) as realisasi, (sum(rsd12)/sum(paguanggaran))*100 as prosentase'))
            ->where('kodesatker','=','001012')
            ->where('tahunanggaran','=',$tahunanggaran)
            ->where('idbiro','=',$idbiro)
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
            ->where('idbiro','=',$idbiro)
            ->get();
        foreach ($datarealisasidewan as $drd){
            $pagudewan = $drd->pagu;
            $realisasidewan = $drd->realisasi;
            $prosentasedewan = $drd->prosentase;
        }
        return view('realisasi.biro.realisasiperbagian',[
            "judul"=>$judul,
            "pagusetjen" => $pagusetjen,
            "realisasisetjen" => $realisasisetjen,
            "prosentasesetjen" => $prosentasesetjen,
            "pagudewan" => $pagudewan,
            "realisasidewan" => $realisasidewan,
            "prosentasedewan" => $prosentasedewan
        ]);
    }

    public function getrealisasiperbagian(Request $request){
        $tahunanggaran = session('tahunanggaran');
        $idbiro = Auth::user()->idbiro;
        if ($request->ajax()) {
            $data = DB::table('bagian as a')
                ->select(['a.uraianbagian','a.id as idbagian',
                    DB::raw('sum(b.paguanggaran) as paguanggaran, sum(b.rsd12) as realisasi, (sum(b.rsd12)/sum(paguanggaran))*100 as prosentase')])
                ->leftJoin('laporanrealisasianggaranbac as b',function($join) use($tahunanggaran){
                   $join->on('a.id','=','b.idbagian');
                   $join->on('b.kodesatker','=',DB::raw('001012'));
                   $join->on('b.tahunanggaran','=',DB::raw($tahunanggaran));
                })
                ->where('a.idbiro','=',$idbiro)
                ->groupBy('a.id')
                ->get();
            return Datatables::of($data)
                ->addIndexColumn()
                ->addColumn('action', function($row){
                    $btn = '<a href="javascript:void(0)" data-toggle="tooltip"  data-id="'.$row->idbagian.'" data-original-title="Edit" class="edit btn btn-primary btn-sm realisasiperpengenal">Lihat</a>';
                    return $btn;
                })
                ->rawColumns(['action'])
                ->make(true);
        }
    }

    public function realisasibagianperpengenal(Request $request, $idbagian)
    {
        $tahunanggaran = session('tahunanggaran');
        $uraianbagian = DB::table('bagian')->where('id','=',$idbagian)->value('uraianbagian');
        $judul = 'Realisasi Bagian Per Pengenal';
        $datarealisasisetjen = DB::table('laporanrealisasianggaranbac')
            ->select(DB::raw('sum(paguanggaran) as pagu, sum(rsd12) as realisasi, (sum(rsd12)/sum(paguanggaran))*100 as prosentase'))
            ->where('kodesatker','=','001012')
            ->where('tahunanggaran','=',$tahunanggaran)
            ->where('idbagian','=',$idbagian)
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
            ->where('idbagian','=',$idbagian)
            ->get();
        foreach ($datarealisasidewan as $drd){
            $pagudewan = $drd->pagu;
            $realisasidewan = $drd->realisasi;
            $prosentasedewan = $drd->prosentase;
        }
        return view('realisasi.biro.realisasibagianperpengenal',[
            "judul"=>$judul,
            "pagusetjen" => $pagusetjen,
            "realisasisetjen" => $realisasisetjen,
            "prosentasesetjen" => $prosentasesetjen,
            "pagudewan" => $pagudewan,
            "realisasidewan" => $realisasidewan,
            "prosentasedewan" => $prosentasedewan,
            "uraianbagian" => $uraianbagian,
            "idbagian" => $idbagian
        ]);
    }

    public function getrealisasibagianperpengenal(Request $request, $idbagian){
        $tahunanggaran = session('tahunanggaran');
        if ($request->ajax()) {
            $data = DB::table('laporanrealisasianggaranbac as a')
                ->select(['a.kodesatker as kodesatker','a.pengenal as pengenal','a.paguanggaran as pagu','a.rsd12 as realisasi',
                    DB::raw('(a.rsd12/a.paguanggaran)*100 as prosentase')
                ])
                ->where('tahunanggaran','=',$tahunanggaran)
                ->where('idbagian','=',$idbagian)
                ->get();
            return Datatables::of($data)
                ->addIndexColumn()
                ->make(true);
        }
    }

    function exportrealisasibagianperpengenal($idbagian){
        $tahunanggaran = session('tahunanggaran');
        //Excel::download(new UsersExport, 'users.xlsx');
        return Excel::download(new ExportRealisasiBagianPerPengenal($tahunanggaran, $idbagian),'RealisasiBagianPerPengenal.xlsx');
    }
}
