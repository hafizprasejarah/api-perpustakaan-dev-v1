<?php
namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Pemesanan;
use App\Helper\CustomController;
use Illuminate\Support\Facades\Validator;

class PemesananController extends CustomController{

    public function index(Request $request)
    {
        $status = $request->input('status');
        try {
            // Bangun kueri dasar untuk mengambil data peminjaman
            $query = Pemesanan::with(['book', 'user']);
    
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
        try {
            // Bangun kueri dasar untuk mengambil data peminjaman
            $query = Pemesanan::with(['book', 'user'])->where('user_id', '=', $id);
    
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
            ];
            $add = Pemesanan::create($data);

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
            $pinjam = Pemesanan::find($id);
    
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

    public function delete($id)
    {
        try {
            // Cari buku berdasarkan ID
            $book = Pemesanan::find($id);

            // Jika buku tidak ditemukan, kembalikan respons dengan pesan tidak ditemukan
            if (!$book) {
                return $this->jsonNotFoundResponse('Book not found');
            }

            // Hapus gambar terkait jika ada
            if ($book->image) {
                // Hapus gambar dari penyimpanan
                unlink($book->image);
            }

            // Hapus buku dari database
            $book->delete();

            // Kembalikan respons berhasil
            return $this->jsonSuccessResponse('Book deleted successfully');
        } catch (\Throwable $e) {
            // Tangani kesalahan internal server
            return $this->jsonErrorResponse('Internal server error: ' . $e->getMessage());
        }
    }
    
}