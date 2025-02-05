<?php

namespace App\Exports;


use Illuminate\Support\Facades\DB;
use Maatwebsite\Excel\Concerns\Exportable;
use Maatwebsite\Excel\Concerns\FromQuery;
use Maatwebsite\Excel\Concerns\WithHeadings;

class ExportRencanaPenarikan implements FromQuery, WithHeadings
{
    use Exportable;
    /**
     * @return \Illuminate\Support\Collection
     */
    protected $tahunanggaran;

    public function __construct($tahunanggaran)
    {
        $this->tahunanggaran = $tahunanggaran;
    }

    public function query()
    {
        $tahunanggaran = $this->tahunanggaran;


        $data = DB::table('rencanakegiatan as a')
            ->select(['a.tahunanggaran','a.kdsatker','c.uraianbiro','b.uraianbagian','a.pengenal','a.noitempok','a.uraiankegiatanpok',
                'a.uraiankegiatanbagian','a.paguanggaran','a.totalrencana','a.statusrencana','a.pok1','a.pok2','a.pok3','a.pok4','a.pok5',
                'a.pok6','a.pok7','a.pok8','a.pok9','a.pok10','a.pok11','a.pok12'

            ])
            ->leftJoin('biro as c','a.idbiro','=','c.id')
            ->leftJoin('bagian as b', function ($join){
                $join->on('a.idbagian','=','b.id');
                $join->on('a.idbiro','=','b.idbiro');
            })
            ->where('tahunanggaran','=',$tahunanggaran)
            ->orderBy('pengenal');
       //echo json_encode($data);
        return $data;
    }

    public function headings(): array
    {
        return ['TA','Satker','Biro','Bagian','Pengenal','No Item','Uraian Item','Kegiatan Bagian','Pagu Anggaran','Total Rencana','Status', 'Januari',
            'Februari','Maret','April','Mei','Juni','Juli','Agustus','September','Oktober','November','Desember'];
    }
}
