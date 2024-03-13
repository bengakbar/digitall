<?php

namespace App\Models\IKPA\Admin;

use App\Models\ReferensiUnit\BagianModel;
use App\Models\ReferensiUnit\BiroModel;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class RekapIKPABagianlModel extends Model
{
    use HasFactory;

    protected $table = 'ikparekapbagian';

    protected $guarded = [];

    public function bagianrelation(){
        return $this->hasOne(BagianModel::class,'id','idbagian');
    }

    public function birorelation(){
        return $this->hasOne(BiroModel::class,'id','idbiro');
    }
}
