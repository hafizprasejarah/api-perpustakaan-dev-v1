<?php
namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Ulasan;
use App\Helper\CustomController;

class UlasanController extends CustomController{

    public function index()
    {
        $data = Ulasan::with([])->orderBy('created_at', 'DESC')->get();
        if (!$data) {
            return $this->jsonNotFoundResponse('not found!');
        }
        return $this->jsonSuccessResponse('success', $data);
    }

    public function getByUser($id)
    {
        try {
            $data = Ulasan::with(['user','book'])->where('book_id', '=', $id)->get();
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
    
            // Memeriksa apakah rating dan ulasan ada dalam permintaan
            if (!isset($body['rating']) || !isset($body['ulasan'])) {
                return $this->jsonErrorResponse('Rating dan ulasan dibutuhkan.');
            }
    
            // Memeriksa apakah rating dan ulasan tidak kosong
            if (empty($body['rating']) || empty($body['ulasan'])) {
                return $this->jsonErrorResponse('Rating dan ulasan tidak boleh kosong.');
            }
    
            $data = [
                'user_id' => $body['user_id'],
                'book_id' => $body['book_id'],
                'ulasan' => $body['ulasan'],
                'rating' => $body['rating'],
            ];
            
            $add = Ulasan::create($data);
            return $this->jsonCreatedResponse('success', $add);
        } catch (\Throwable $e) {
            return $this->jsonErrorResponse('Internal server error: ' . $e->getMessage());
        }
    }

    public function delete($id)
    {
        try {
            
            $ulasan = Ulasan::find($id);

           
            if (!$ulasan) {
                return $this->jsonNotFoundResponse('tidak di temukan');
            }

            $ulasan->delete();

            return $this->jsonSuccessResponse('Berhasil di hapus', $ulasan);
        } catch (\Throwable $e) {
            return $this->jsonErrorResponse('Internal server error: ' . $e->getMessage());
        }
    }
    

}