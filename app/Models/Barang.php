<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Barang extends Model
{
    protected $fillable = [
        'nama_barang',
        'price'
    ];

    public function getBarang($id)
    {
        $this->db->select('*');
        $this->db->from('barangs');
        $this->db->where('id', $id);
    }
}
