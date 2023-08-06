<?php

namespace App\Http\Controllers\AdminAnggaran;

use App\Libraries\BearerKey;
use App\Http\Controllers\Controller;
use App\Libraries\TarikDataMonsakti;
use App\Models\AdminAnggaran\AnggaranBagianModel;
use App\Models\AdminAnggaran\DataAngModel;
use App\Models\AdminAnggaran\SummaryDipaModel;
use App\Models\ReferensiUnit\BagianModel;
use Illuminate\Support\Facades\DB;
use Illuminate\Http\Request;

class DataAngController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
    }

    function checkdata(Request $request){
        $kdsatker = $request->get('kdsatker');
        $kd_sts_history = $request->get('kd_sts_history');
        $jumlahdata = DB::table('data_ang')
            ->where($kdsatker,'=',$kdsatker)
            ->where('kodestshistory','=',$kd_sts_history)
            ->count();

        if ($jumlahdata > 0){
            return response()->json("Ada");
        }else{
            return response()->json("Tidak Ada");
        }
    }

    function importdataang($kdsatker, $kd_sts_history){
        $tahunanggaran = session('tahunanggaran');
        $this->aksiimportdataang($kdsatker, $kd_sts_history, $tahunanggaran);
    }


    function aksiimportdataang($kdsatker, $kd_sts_history, $tahunanggaran){
        $kodemodul = 'ANG';
        $tipedata = 'dataAng';
        $variable = [$kdsatker, $kd_sts_history];

        //idrefstatus
        $idrefstatus = DB::table('ref_status')
            ->where('kd_sts_history','=',$kd_sts_history)
            ->where('kdsatker','=',$kdsatker)
            ->value('idrefstatus');

        //hapus idrefstatus
        DB::table('data_ang')->where('idrefstatus','='.$idrefstatus)->delete();

        //tarikdata
        $response = new TarikDataMonsakti();
        $response = $response->prosedurlengkap($tahunanggaran, $kodemodul, $tipedata, $variable);
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
                }
            }
            foreach ($hasilasli as $item => $value) {
                if ($item != "TOKEN") {
                    foreach ($value as $DATA) {
                        $KDSATKER = $DATA->KDSATKER;
                        $KODE_PROGRAM = $DATA->KODE_PROGRAM;
                        $KODE_KEGIATAN = $DATA->KODE_KEGIATAN;
                        $KODE_OUTPUT = $DATA->KODE_OUTPUT;
                        $KDIB = $DATA->KDIB;
                        $VOLUME_OUTPUT = $DATA->VOLUME_OUTPUT;
                        $KODE_SUBOUTPUT = $DATA->KODE_SUBOUTPUT;
                        $VOLUME_SUBOUTPUT = $DATA->VOLUME_SUBOUTPUT;
                        $KODE_KOMPONEN = $DATA->KODE_KOMPONEN;
                        $KODE_SUBKOMPONEN = $DATA->KODE_SUBKOMPONEN;
                        $URAIAN_SUBKOMPONEN = $DATA->URAIAN_SUBKOMPONEN;
                        $KODE_AKUN = $DATA->KODE_AKUN;
                        $KODE_JENIS_BEBAN = $DATA->KODE_JENIS_BEBAN;
                        $KODE_CARA_TARIK = $DATA->KODE_CARA_TARIK;
                        $HEADER1 = $DATA->HEADER1;
                        $HEADER2 = $DATA->HEADER2;
                        $KODE_ITEM = $DATA->KODE_ITEM;
                        $NOMOR_ITEM = $DATA->NOMOR_ITEM;
                        $URAIAN_ITEM = $DATA->URAIAN_ITEM;
                        $CONS_ITEM = $DATA->CONS_ITEM;
                        $SUMBER_DANA = $DATA->SUMBER_DANA;
                        $VOL_KEG_1 = $DATA->VOL_KEG_1;
                        $VOL_KEG_1 = (int)$VOL_KEG_1;
                        $SAT_KEG_1 = $DATA->SAT_KEG_1;
                        $VOL_KEG_2 = $DATA->VOL_KEG_2;
                        $VOL_KEG_2 = (int)$VOL_KEG_2;
                        $SAT_KEG_2 = $DATA->SAT_KEG_2;
                        $VOL_KEG_3 = $DATA->VOL_KEG_3;
                        $VOL_KEG_3 = (int)$VOL_KEG_3;
                        $SAT_KEG_3 = $DATA->SAT_KEG_3;
                        $VOL_KEG_4 = (int)$DATA->VOL_KEG_4;
                        $SAT_KEG_4 = $DATA->SAT_KEG_4;
                        $VOLKEG = (int)$DATA->VOLKEG;
                        $SATKEG = $DATA->SATKEG;
                        $HARGASAT = (int)$DATA->HARGASAT;
                        $TOTAL = (int)$DATA->TOTAL;
                        $KODE_BLOKIR = $DATA->KODE_BLOKIR;
                        $NILAI_BLOKIR = (int)$DATA->NILAI_BLOKIR;
                        $KODE_STS_HISTORY = $DATA->KODE_STS_HISTORY;
                        $POK_NILAI_1 = (int)$DATA->POK_NILAI_1;
                        $POK_NILAI_2 = (int)$DATA->POK_NILAI_2;
                        $POK_NILAI_3 = (int)$DATA->POK_NILAI_3;
                        $POK_NILAI_4 = (int)$DATA->POK_NILAI_4;
                        $POK_NILAI_5 = (int)$DATA->POK_NILAI_5;
                        $POK_NILAI_6 = (int)$DATA->POK_NILAI_6;
                        $POK_NILAI_7 = (int)$DATA->POK_NILAI_7;
                        $POK_NILAI_8 = (int)$DATA->POK_NILAI_8;
                        $POK_NILAI_9 = (int)$DATA->POK_NILAI_9;
                        $POK_NILAI_10 = (int)$DATA->POK_NILAI_10;
                        $POK_NILAI_11 = (int)$DATA->POK_NILAI_11;
                        $POK_NILAI_12 = (int)$DATA->POK_NILAI_12;

                        $data = array(
                            'tahunanggaran' => $tahunanggaran,
                            'idrefstatus' => $idrefstatus,
                            'kdsatker' => $KDSATKER,
                            'kodeprogram' => $KODE_PROGRAM,
                            'kodekegiatan' => $KODE_KEGIATAN,
                            'kodeoutput' => $KODE_OUTPUT,
                            'kdib' => $KDIB,
                            'volumeoutput' => $VOLUME_OUTPUT,
                            'kodesuboutput' => $KODE_SUBOUTPUT,
                            'volumesuboutput' => $VOLUME_SUBOUTPUT,
                            'kodekomponen' => $KODE_KOMPONEN,
                            'kodesubkomponen' => $KODE_SUBKOMPONEN,
                            'uraiansubkomponen' => $URAIAN_SUBKOMPONEN,
                            'kodeakun' => $KODE_AKUN,
                            'pengenal' => $tahunanggaran.".".$kdsatker.".".$KODE_PROGRAM.'.'.$KODE_KEGIATAN.'.'.$KODE_OUTPUT.'.'.$KODE_SUBOUTPUT.'.'.$KODE_KOMPONEN.'.'.$KODE_SUBKOMPONEN.'.'.$KODE_AKUN,
                            'kodejenisbeban' => $KODE_JENIS_BEBAN,
                            'kodecaratarik' => $KODE_CARA_TARIK,
                            'header1' => $HEADER1,
                            'header2' => $HEADER2,
                            'kodeitem' => $KODE_ITEM,
                            'nomoritem' => $NOMOR_ITEM,
                            'cons_item' => $CONS_ITEM,
                            'uraianitem' => $URAIAN_ITEM,
                            'sumberdana' => $SUMBER_DANA,
                            'volkeg1' => $VOL_KEG_1,
                            'satkeg1' => $SAT_KEG_1,
                            'volkeg2' => $VOL_KEG_2,
                            'satkeg2' => $SAT_KEG_2,
                            'volkeg3' => $VOL_KEG_3,
                            'satkeg3' => $SAT_KEG_3,
                            'volkeg4' => $VOL_KEG_4,
                            'satkeg4' => $SAT_KEG_4,
                            'volkeg' => $VOLKEG,
                            'satkeg' => $SATKEG,
                            'hargasat' => $HARGASAT,
                            'total' => $TOTAL,
                            'kodeblokir' => $KODE_BLOKIR,
                            'nilaiblokir' => $NILAI_BLOKIR,
                            'kodestshistory' => $KODE_STS_HISTORY,
                            'poknilai1' => $POK_NILAI_1,
                            'poknilai2' => $POK_NILAI_2,
                            'poknilai3' => $POK_NILAI_3,
                            'poknilai4' => $POK_NILAI_4,
                            'poknilai5' => $POK_NILAI_5,
                            'poknilai6' => $POK_NILAI_6,
                            'poknilai7' => $POK_NILAI_7,
                            'poknilai8' => $POK_NILAI_8,
                            'poknilai9' => $POK_NILAI_9,
                            'poknilai10' => $POK_NILAI_10,
                            'poknilai11' => $POK_NILAI_11,
                            'poknilai12' => $POK_NILAI_12
                        );
                        DataAngModel::Create($data);
                    }
                }
            }
        }else if ($response == "Expired"){
            $tokenbaru = new BearerKey();
            $tokenbaru->resetapi($tahunanggaran, $kodemodul, $tipedata);
        }else{
            $tokenbaru = new BearerKey();
            $tokenbaru->resetapi($tahunanggaran, $kodemodul, $tipedata);
        }
    }

    public function rekapanggarannoredirect($idrefstatus, $tahunanggaran){
        $datapagu = DataAngModel::where([
            ['header1','=',0],
            ['header2','=',0],
            ['idrefstatus','=',$idrefstatus]
        ])->get();

        foreach ($datapagu as $item){
            $kdsatker = $item->kdsatker;
            $kodeprogram = $item->kodeprogram;
            $kodekegiatan = $item->kodekegiatan;
            $kodeoutput = $item->kodeoutput;
            $kodesubout = $item->kodesuboutput;
            $kodekomponen = $item->kodekomponen;
            $kodesubkomponen = $item->kodesubkomponen;
            $kodeakun = $item->kodeakun;
            $jenisbelanja = substr($kodeakun,0,2);
            $pengenalanggaranbagian = $kodeprogram.'.'.$kodekegiatan.'.'.$kodeoutput.'.'.$kodesubout.'.'.$kodekomponen;
            $pengenalrealisasianggaran = $tahunanggaran.".".$kdsatker.".".$kodeprogram.'.'.$kodekegiatan.'.'.$kodeoutput.'.'.$kodesubout.'.'.$kodekomponen.".".$kodesubkomponen.".".$kodeakun;

            $indeks = $tahunanggaran.$kdsatker.$kodeprogram.$kodekegiatan.$kodeoutput.$kodesubout.$kodekomponen;

            if ($kdsatker == '001012'){
                $pengenalanggaranbagian = $pengenalanggaranbagian.".".$kodesubkomponen;
                $indeks = $indeks.$kodesubkomponen;
            }else{
                $pengenalanggaranbagian = $pengenalanggaranbagian;
                $indeks = $indeks;
            }

            $where = array(
                'indeks' => $indeks
            );
            $adadata = AnggaranBagianModel::where($where)->count();
            if ($adadata == 0){
                $data = array(
                    'tahunanggaran' => $tahunanggaran,
                    'kdsatker' => $kdsatker,
                    'kodeprogram' => $kodeprogram,
                    'kodekegiatan' => $kodekegiatan,
                    'kodeoutput' => $kodeoutput,
                    'kodesuboutput' => $kodesubout,
                    'kodekomponen' => $kodekomponen,
                    'kodesubkomponen' => $kodesubkomponen,
                    'pengenal' => $pengenalanggaranbagian,
                    'indeks' => $indeks,
                    'idbagian' => null
                );
                AnggaranBagianModel::insert($data);
            }

            //insert ke laporan realisasi anggaran
            $adadatarealisasi = DB::table('laporanrealisasianggaranbac')->where('pengenal','=',$pengenalrealisasianggaran)->count();
            if ($adadatarealisasi == 0){
                $datarealisasi = DB::table('anggaranbagian')->where('pengenal','=',$pengenalanggaranbagian)->get();
                $idbagian = 0;
                $idbiro = 0;
                $iddeputi = 0;
                foreach ($datarealisasi as $dr){
                    $idbagian = $dr->idbagian;
                    $idbiro = $dr->idbiro;
                    $iddeputi = $dr->iddeputi;
                    $idindikatorro = $dr->idindikatorro;
                    $idro = $dr->idro;
                    $idkro = $dr->idkro;
                }
                $datarealisasianggaran = array(
                    'tahunanggaran' => $tahunanggaran,
                    'kodesatker' => $kdsatker,
                    'kodeprogram' => $kodeprogram,
                    'kodekegiatan' => $kodekegiatan,
                    'kodeoutput' => $kodeoutput,
                    'kodesuboutput' => $kodesubout,
                    'kodekomponen' => $kodekomponen,
                    'kodesubkomponen' => $kodesubkomponen,
                    'kodeakun' => $kodeakun,
                    'jenisbelanja' => $jenisbelanja,
                    'pengenal' => $pengenalrealisasianggaran,
                    'idbagian' => $idbagian,
                    'idbiro' => $idbiro,
                    'iddeputi' => $iddeputi,
                    'idindikatorro' => $idindikatorro,
                    'idro' => $idro,
                    'idkro' => $idkro
                );
                DB::table('laporanrealisasianggaranbac')->insert($datarealisasianggaran);
            }
        }
    }

    public function rekapanggaran($idrefstatus){
        $tahunanggaran = session('tahunanggaran');
        $this->rekapanggarannoredirect($idrefstatus, $tahunanggaran);
        return redirect()->to('refstatus')->with('rekapberhasil','Rekap Anggaran Bagian Berhasil');
    }


}
