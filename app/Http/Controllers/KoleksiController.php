<?php
namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Koleksi;
use App\Helper\CustomController;

class KoleksiController extends CustomController{

    public function index()
    {
        $data = Koleksi::with([])->orderBy('created_at', 'DESC')->get();
        if (!$data) {
            return $this->jsonNotFoundResponse('not found!');
        }
        return $this->jsonSuccessResponse('success', $data);
    }

    public function getByUser($id)
    {
        try {
            $data = Koleksi::with(['book:id,judul,description,penulis,penerbit,tahun_terbit,kategori_id,image','book.kategori:id,nama'])->where('user_id', '=', $id)->orderBy('created_at', 'DESC')->get();
            if (!$data) {
                return $this->jsonNotFoundResponse('data not found');
            }
            return $this->jsonSuccessResponse('success', $data);
        } catch (\Throwable $e) {
            return $this->jsonErrorResponse('internal server error ' . $e->getMessage());
        }
    }

    public function store()
    {
        try {
            $body = $this->parseRequestBody();
            $data = [
                'user_id' => $body['user_id'],
                'book_id' => $body['book_id'],
            ];
            $add = Koleksi::create($data);
            return $this->jsonCreatedResponse('success', $add);
        } catch (\Throwable $e) {
            return $this->jsonErrorResponse('internal server error ' . $e->getMessage());
        }
    }

    public function delete($id)
    {
       
        $koleksi = Koleksi::find($id);

        if (!$koleksi) {
            return $this->jsonNotFoundResponse('not found!');
        }
        
        $koleksi->delete();

        return $this->jsonSuccessResponse('success', $koleksi);
    }
}