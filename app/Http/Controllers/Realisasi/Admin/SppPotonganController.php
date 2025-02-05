<?php

namespace App\Http\Controllers\Realisasi\Admin;

use App\Http\Controllers\Controller;
use App\Libraries\BearerKey;
use App\Libraries\TarikDataMonsakti;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Yajra\DataTables\DataTables;

class SppPotonganController extends Controller
{
    public function __construct()
    {
        $this->middleware(['auth']);

    }

    function importspppotongan($ID_SPP, $TA){
        //cek apakah sudah ada
        $jumlahdata = DB::table('spppotongan')->where('ID_SPP','=',$ID_SPP)->count();
        if ($jumlahdata > 0){
            DB::table('spppotongan')->where('ID_SPP','=',$ID_SPP)->delete();
        }

        $kodemodul = 'PEM';
        $tipedata = 'sppPotongan';
        $variabel = [$ID_SPP];

        //reset dlu tokennya
        $tokenbaru = new BearerKey();
        $tokenbaru->resetapi($TA, $kodemodul, $tipedata);

        //tarikdata
        $response = new TarikDataMonsakti();
        $response = $response->prosedurlengkap($TA, $kodemodul, $tipedata, $variabel);
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
                    $token->simpantokenbaru($TA, $kodemodul, $tokenresponse);
                }else{
                    foreach ($value as $DATA) {
                        $KODE_KEMENTERIAN = $DATA->KODE_KEMENTERIAN;
                        $KDSATKER = $DATA->KDSATKER;
                        $ID_SPP = $DATA->ID_SPP;
                        $KODE_PROGRAM = $DATA->KODE_PROGRAM;
                        $KODE_KEGIATAN = $DATA->KODE_KEGIATAN;
                        $KODE_OUTPUT = $DATA->KODE_OUTPUT;
                        $KODE_AKUN = $DATA->KODE_AKUN;
                        $KODE_SUBOUTPUT = $DATA->KODE_SUBOUTPUT;
                        $KODE_KOMPONEN = $DATA->KODE_KOMPONEN;
                        $KODE_SUBKOMPONEN = $DATA->KODE_SUBKOMPONEN;
                        $KODE_ITEM = $DATA->KODE_ITEM;
                        $KD_CTARIK = $DATA->KD_CTARIK;
                        $KD_REGISTER = $DATA->KD_REGISTER;
                        $KODE_COA = $DATA->KODE_COA;
                        $KODE_VALAS = $DATA->KODE_VALAS;
                        $NILAI_AKUN_POT = $DATA->NILAI_AKUN_POT;
                        $NILAI_TUKAR = $DATA->NILAI_TUKAR;
                        $NILAI_TUKAR_SP2D = $DATA->NILAI_TUKAR_SP2D;
                        $NILAI_VALAS = $DATA->NILAI_VALAS;
                        $NILAI_PEMBAYARAN_VALAS_SP2D = $DATA->NILAI_PEMBAYARAN_VALAS_SP2D;

                        $datapengeluaran = DB::table('spppengeluaran')->where('ID_SPP','=',$ID_SPP)->get();
                        $ID_BAGIAN = 0;
                        $ID_BIRO = 0;
                        $ID_DEPUTI = 0;
                        foreach ($datapengeluaran as $dp){
                            $ID_BAGIAN = $dp->ID_BAGIAN;
                            $ID_BIRO = $dp->ID_BIRO;
                            $ID_DEPUTI = $dp->ID_DEPUTI;
                        }
                        $data = array(
                            'KODE_KEMENTERIAN' => $KODE_KEMENTERIAN,
                            'KDSATKER' => $KDSATKER,
                            'ID_SPP' => $ID_SPP,
                            'KODE_PROGRAM' => $KODE_PROGRAM,
                            'KODE_KEGIATAN' => $KODE_KEGIATAN,
                            'KODE_OUTPUT' => $KODE_OUTPUT,
                            'KODE_AKUN' => $KODE_AKUN,
                            'KODE_SUBOUTPUT' => $KODE_SUBOUTPUT,
                            'KODE_KOMPONEN' => $KODE_KOMPONEN,
                            'KODE_SUBKOMPONEN' => $KODE_SUBKOMPONEN,
                            'KODE_ITEM' => $KODE_ITEM,
                            'KD_CTARIK' => $KD_CTARIK,
                            'KD_REGISTER' => $KD_REGISTER,
                            'KODE_COA' => $KODE_COA,
                            'KODE_VALAS' => $KODE_VALAS,
                            'NILAI_AKUN_POT' => $NILAI_AKUN_POT,
                            'NILAI_TUKAR' => $NILAI_TUKAR,
                            'NILAI_TUKAR_SP2D' => $NILAI_TUKAR_SP2D,
                            'NILAI_VALAS' => $NILAI_VALAS,
                            'NILAI_PEMBAYARAN_VALAS_SP2D' => $NILAI_PEMBAYARAN_VALAS_SP2D,
                            'ID_BAGIAN' => $ID_BAGIAN,
                            'ID_BIRO' => $ID_BIRO,
                            'ID_DEPUTI' => $ID_DEPUTI
                        );
                        DB::table('spppotongan')->insert($data);
                    }
                }
            }
        }
    }

    public function getlistpotongan(Request $request, $ID_SPP){
        if ($request->ajax()) {
            $data = DB::table('spppotongan')->where('ID_SPP','=',$ID_SPP)->get();
            return Datatables::of($data)
                ->addIndexColumn()
                ->make(true);
        }
    }
}
