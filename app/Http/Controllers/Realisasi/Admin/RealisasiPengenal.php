<?php

namespace App\Http\Controllers\Realisasi\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Yajra\DataTables\DataTables;

class RealisasiPengenal extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
    }

    public function index(Request $request)
    {
        $tahunanggaran = session('tahunanggaran');
        $judul = 'Realisasi Per Pengenal';
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
        return view('realisasi.admin.realisasiperpengenal',[
            "judul"=>$judul,
            "pagusetjen" => $pagusetjen,
            "realisasisetjen" => $realisasisetjen,
            "prosentasesetjen" => $prosentasesetjen,
            "pagudewan" => $pagudewan,
            "realisasidewan" => $realisasidewan,
            "prosentasedewan" => $prosentasedewan
        ]);
    }

    public function getrealisasiperpengenal(Request $request){
        $tahunanggaran = session('tahunanggaran');
        if ($request->ajax()) {
            $data = DB::table('laporanrealisasianggaranbac as a')
                ->select(['a.kodesatker as kodesatker','a.pengenal as pengenal','a.paguanggaran as pagu','a.rsd12 as realisasi',
                    'b.uraianbiro as biro','c.uraianbagian as bagian'])
                ->leftJoin('biro as b','a.idbiro','=','b.id')
                ->leftJoin('bagian as c','a.idbagian','=','c.id')
                ->where('tahunanggaran','=',$tahunanggaran);
            return Datatables::of($data)
                ->addIndexColumn()
                ->make(true);
        }
    }
}
