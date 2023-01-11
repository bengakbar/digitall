<?php

namespace App\Http\Controllers\BPK\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Yajra\DataTables\DataTables;
use App\Models\ReferensiUnit\DeputiModel;
use App\Models\BPK\Admin\RekomendasiModel;

class TemuanController extends Controller
{
    public function index(Request $request)
    {

        $this->authorize('view',RekomendasiModel::class);

        $judul = 'List temuan';
        $datadeputi = DeputiModel::all();
        $datatahunanggaran = DB::table('tahunanggaran')->get();

        if ($request->ajax()) {
            $data = RekomendasiModel::all();

            return Datatables::of($data)
                ->addIndexColumn()
                ->addColumn('action', function($row){
                    $idtemuan = $row->id;
                    $jumlahtindaklanjutproses = DB::table('tindaklanjutbpk')
                        ->where('idtemuan','=',$idtemuan)
                        ->where('status','=',4)
                        ->count();
                    if ($row->status == 1){
                        $btn = '<div class="btn-group" role="group">
                            <a href="javascript:void(0)" data-toggle="tooltip"  data-id="'.$row->id.'" data-original-title="Edit" class="edit btn btn-primary btn-sm edittemuan">Edit</a>';
                        $btn = $btn.'<a href="javascript:void(0)" data-toggle="tooltip"  data-id="'.$row->id.'" data-original-title="Delete" class="btn btn-danger btn-sm deletetemuan">Delete</a>';
                        $btn = $btn.'<a href="javascript:void(0)" data-toggle="tooltip"  data-id="'.$row->id.'" data-original-title="Kirim" class="btn btn-success btn-sm kirimkeunit">Kirim</a>';

                    }else if($row->status == 2){
                        $btn = '<div class="btn-group" role="group">
                        <a href="javascript:void(0)" data-toggle="tooltip"  data-id="'.$row->id.'" data-original-title="Kirim" class="btn btn-primary btn-sm ingatkanunit">Ingatkan Unit</a>';
                        $btn = $btn.'<a href="javascript:void(0)" data-toggle="tooltip"  data-id="'.$row->id.'" data-original-title="lihattindaklanjut" class="btn btn-info btn-sm lihattindaklanjut">
                                Lihat TL   <span class="badge badge-danger navbar-badge">'.$jumlahtindaklanjutproses.'</span></a>';
                        $btn = $btn.'<a href="javascript:void(0)" data-toggle="tooltip"  data-id="'.$row->id.'" data-original-title="selesai" class="btn btn-success btn-sm selesai">Selesai</a>';
                        $btn = $btn.'<a href="javascript:void(0)" data-toggle="tooltip"  data-id="'.$row->id.'" data-original-title="tddl" class="btn btn-danger btn-sm tddl">TDDL</a>';
                    }else{
                        $btn ="";
                    }
                    return $btn;
                })
                ->addColumn('iddeputi',function ($row){
                    $iddeputi = $row->iddeputi;
                    $uraiandeputi = DB::table('deputi')->where('id','=',$iddeputi)->value('uraiandeputi');
                    return $uraiandeputi;
                })
                ->addColumn('idbiro',function ($row){
                    $idbiro = $row->idbiro;
                    $uraianbiro = DB::table('biro')->where('id','=',$idbiro)->value('uraianbiro');
                    return $uraianbiro;
                })
                ->addColumn('idbagian',function ($row){
                    $idbagian = $row->idbagian;
                    $uraianbiro = DB::table('bagian')->where('id','=',$idbagian)->value('uraianbagian');
                    return $uraianbiro;
                })
                ->addColumn('status',function ($row){
                    $idstatus = $row->status;
                    $uraianstatus = DB::table('statustemuan')->where('id','=',$idstatus)->value('uraianstatus');
                    return $uraianstatus;
                })
                ->addColumn('created_by',function ($row){
                    $iduser = $row->created_by;
                    $namauser = DB::table('users')->where('id','=',$iduser)->value('name');
                    return $namauser;
                })
                ->rawColumns(['action'])
                ->make(true);
        }

        return view('BPK.Admin.temuan',[
            "judul"=>$judul,
            "datadeputi" => $datadeputi,
            "datatahunanggaran" => $datatahunanggaran
        ]);
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        $this->authorize('create',RekomendasiModel::class);

        $userid = auth()->id();
        $saveBtn = $request->get('saveBtn');
        if ($saveBtn == "tambah"){
            $status = 1;
        }else{
            $status = $request->get('statusawal');
        }


        if ($request->file('bukti') != ""){
            $bukti = $request->file('bukti')->store('bukti','public');
        }else{
            $bukti = $request->get('buktiawal');
        }

        $validated = $request->validate([
            'tahunanggaran' => 'required',
            'iddeputi' => 'required',
            'idbiro' => 'required',
            'idbagian' => 'required',
            'kondisi' => 'required',
            'kriteria' => 'required',
            'sebab' => 'required',
            'akibat' => 'required',
            'nilai' => 'required',
            'rekomendasi' => 'required',
            'bukti' => 'required',

        ]);

        RekomendasiModel::create(
            [
                'tahunanggaran' => $request->get('tahunanggaran'),
                'iddeputi' => $request->get('iddeputi'),
                'idbiro' => $request->get('idbiro'),
                'idbagian' => $request->get('idbagian'),
                'kondisi' => $request->get('kondisi'),
                'kriteria' => $request->get('kriteria'),
                'sebab' => $request->get('sebab'),
                'akibat' => $request->get('akibat'),
                'nilai' => $request->get('nilai'),
                'rekomendasi' => $request->get('rekomendasi'),
                'bukti' => $bukti,
                'status' => $status,
                'created_by' => $userid
            ]);

        return response()->json(['status'=>'berhasil']);
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function edit($id)
    {
        $this->authorize('update',RekomendasiModel::class);
        $menu = RekomendasiModel::find($id);
        return response()->json($menu);
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $id)
    {
        $this->authorize('update',RekomendasiModel::class);

        $userid = auth()->id();
        $saveBtn = $request->get('saveBtn');
        if ($saveBtn == "tambah"){
            $status = 1;
        }else{
            $status = $request->get('statusawal');
        }


        if ($request->file('bukti') != ""){
            $bukti = $request->file('bukti')->store('bukti','public');
        }else{
            $bukti = $request->get('buktiawal');
        }

        $validated = $request->validate([
            'tahunanggaran' => 'required',
            'iddeputi' => 'required',
            'idbiro' => 'required',
            'idbagian' => 'required',
            'kondisi' => 'required',
            'kriteria' => 'required',
            'sebab' => 'required',
            'akibat' => 'required',
            'nilai' => 'required',
            'rekomendasi' => 'required',

        ]);

        RekomendasiModel::where('id',$id)->update(
            [
                'tahunanggaran' => $request->get('tahunanggaran'),
                'iddeputi' => $request->get('iddeputi'),
                'idbiro' => $request->get('idbiro'),
                'idbagian' => $request->get('idbagian'),
                'kondisi' => $request->get('kondisi'),
                'kriteria' => $request->get('kriteria'),
                'sebab' => $request->get('sebab'),
                'akibat' => $request->get('akibat'),
                'nilai' => $request->get('nilai'),
                'rekomendasi' => $request->get('rekomendasi'),
                'bukti' => $bukti,
                'status' => $status,
                'created_by' => $userid
            ]);

        return response()->json(['status'=>'berhasil']);
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {

        $this->authorize('delete',RekomendasiModel::class);
        $status = DB::table('rekomendasi')->where('id','=',$id)->value('status');
        if ($status == 1){
            RekomendasiModel::find($id)->delete();
            return response()->json(['status'=>'berhasil']);
        }else{
            return response()->json(['status'=>'gagal']);
        }
    }

    public function kirimtemuankeunit($id){
        $temuan = RekomendasiModel::find($id);
        if ($temuan){
            DB::table('rekomendasi')->where('id','=',$id)->update(['status' => 2]);
            return response()->json(['status'=>'berhasil']);
        }else{
            return response()->json(['status'=>'gagal']);
        }

    }

    public function statustemuanselesai($id){
        $temuan = RekomendasiModel::find($id);
        if ($temuan){
            DB::table('rekomendasi')->where('id','=',$id)->update([
                'status' => 6,
                'updated_by' => Auth::user()->id,
                'updated_at' => now()
            ]);
            return response()->json(['status'=>'berhasil']);
        }else{
            return response()->json(['status'=>'gagal']);
        }

    }

    public function statustemuantddl($id){
        $temuan = RekomendasiModel::find($id);
        if ($temuan){
            DB::table('rekomendasi')->where('id','=',$id)->update([
                'status' => 7,
                'updated_by' => Auth::user()->id,
                'updated_at' => now()
            ]);
            return response()->json(['status'=>'berhasil']);
        }else{
            return response()->json(['status'=>'gagal']);
        }

    }
}
