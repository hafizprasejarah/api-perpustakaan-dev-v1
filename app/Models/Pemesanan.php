<?php   
namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Pemesanan extends Model{
    protected $table = "pemesanan";

    // public $timestamps = false;

    protected $fillable = [
        'user_id',
        'book_id',
    ];

    public function user()
    {
        return $this->belongsTo(Users::class, 'user_id');
    }

    public function book()
    {
        return $this->belongsTo(Books::class, 'book_id');
    }
}