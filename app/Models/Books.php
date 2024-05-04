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

    public function peminjaman()
    {
        return $this->hasMany(Peminjaman::class, 'book_id');
    }

    public function ulasan()
    {
        return $this->hasMany(Ulasan::class, 'book_id');
    }

    public function pemesanan()
    {
        return $this->hasMany(Pemesanan::class, 'book_id');
    }

    public function koleksi()
    {
        return $this->hasMany(Koleksi::class, 'book_id');
    }
}