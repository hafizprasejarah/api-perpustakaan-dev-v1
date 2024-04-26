<?php
namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Peminjaman;
use App\Helper\CustomController;
use Illuminate\Support\Facades\Validator;

class PeminjamanController extends CustomController{

    public function index(Request $request)
    {
        $status = $request->input('status');
        try {
            // Bangun kueri dasar untuk mengambil data peminjaman
            $query = Peminjaman::with(['book', 'user']);
    
            // Jika status diberikan, tambahkan kondisi where berdasarkan status
            if ($status) {
                $query->where('status', '=', $status);
            }
    
            // Eksekusi kueri dan ambil hasilnya
            $data = $query->orderBy('created_at', 'DESC')->get();
    
            // Jika tidak ada data yang ditemukan, kembalikan respons kesalahan
            if (!$data) {
                return $this->jsonNotFoundResponse('Data not found');
            }
    
            // Kembalikan data dalam respons sukses
            return $this->jsonSuccessResponse('Success', $data);
        } catch (\Throwable $e) {
            // Tangani kesalahan server internal
            return $this->jsonErrorResponse('Internal server error: ' . $e->getMessage());
        }
    }

    public function getByUser($id, Request $request)
    {
        $status = $request->input('status');
        try {
            // Bangun kueri dasar untuk mengambil data peminjaman
            $query = Peminjaman::with(['book', 'user'])->where('user_id', '=', $id);
    
            // Jika status diberikan, tambahkan kondisi where berdasarkan status
            if ($status) {
                $query->where('status', '=', $status);
            }
    
            // Eksekusi kueri dan ambil hasilnya
            $data = $query->orderBy('created_at', 'DESC')->get();
    
            // Jika tidak ada data yang ditemukan, kembalikan respons kesalahan
            if (!$data) {
                return $this->jsonNotFoundResponse('Data not found');
            }
    
            // Kembalikan data dalam respons sukses
            return $this->jsonSuccessResponse('Success', $data);
        } catch (\Throwable $e) {
            // Tangani kesalahan server internal
            return $this->jsonErrorResponse('Internal server error: ' . $e->getMessage());
        }
    }
    

    public function store()
    {
        try {
            $body = $this->parseRequestBody();
            $data = [
                'user_id' => $body['user_id'],
                'book_id' => $body['book_id'],
                'tanggal_pinjam' => $body['tanggal_pinjam'],
                'tanggal_kembali' => $body['tanggal_kembali'],
                'status' => "DIPROSES",
            ];
            $add = Peminjaman::create($data);

            return $this->jsonCreatedResponse('success', $add);
        } catch (\Throwable $e) {
            return $this->jsonErrorResponse('internal server error ' . $e->getMessage());
        }
    }

    public function update(Request $request, $id)
    {
        try {
            $status =  $request->input('status');
            // Temukan pengguna berdasarkan ID
            $pinjam = Peminjaman::find($id);
    
            // Periksa apakah pengguna ditemukan
            if (!$pinjam) {
                return  response()->json([
                    'message' => 'pinjam not found'
                ], 404);
            }
   
            // Perbarui data pengguna
            if ($status == null) {
                return $this->jsonNotFoundResponse('status not found');
            }else{
            $pinjam->update([
                'status' =>$status,
            ]);
            }

        
            return $this->jsonCreatedResponse('success', $pinjam);
        } catch (\Throwable $e) {
            return response()->json([
                'message' => 'Internal server error: ' . $e->getMessage(),
            ], 500);
        }
    }
}