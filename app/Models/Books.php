<?php   
namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Books extends Model{
    protected $table = "books";

    // public $timestamps = false;

    protected $fillable = [
        'kategori_id',
        'judul',
        'image',
        'description',
        'penulis',
        'penerbit',
        'tahun_terbit',
    ];

    public function kategori()
    {
        return $this->belongsTo(Kategori::class, 'kategori_id');
    }
}