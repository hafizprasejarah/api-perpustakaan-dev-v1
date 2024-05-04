<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Books;
use App\Helper\CustomController;
use Illuminate\Support\Facades\Validator;

class BooksController extends CustomController
{


    public function index(Request $request)
    {
        $kategori = $request->input('kategori');
        if ($kategori) {
            $data = Books::with(['kategori:id,nama'])
                ->whereHas('kategori', function ($query) use ($kategori) {
                    $query->where('nama', $kategori);
                })
                ->orderBy('created_at', 'DESC')
                ->get();
        } else {
            $data = Books::with(['kategori:id,nama'])->orderBy('created_at', 'DESC')->get();
        }
    
        if ($data->isEmpty()) {
            return $this->jsonNotFoundResponse('not found!');
        }
    
        return $this->jsonSuccessResponse('success', $data);
    }

    public function store(Request $request)
    {
        try {
            $body = $this->parseRequestBody();

            $validator = Validator::make($body, [
                'kategori_id' => 'required',
                'judul' => 'required|string',
                'description' => 'required|string|max:600',
                'penulis' => 'required|string',
                'penerbit' => 'required|string',
                'tahun_terbit' => 'required|integer',
                'image' => 'required|image|mimes:jpeg,png,jpg,gif|max:2048', // Validasi gambar
            ]);

            if ($validator->fails()) {
                return $this->jsonNotFoundResponse($validator->errors()->first());
            }

            $imageName = null; // Inisialisasi nama file gambar

            if ($request->hasFile('image')) {
                // Dapatkan instance dari gambar yang diunggah
                $image = $request->file('image');

                // Buat nama unik untuk file gambar
                $imageName = time() . '.' . $image->getClientOriginalExtension();

                // Direktori penyimpanan gambar
                $storagePath = 'post_images';

                // Simpan gambar ke dalam direktori penyimpanan
                $image->move($storagePath, $imageName);
            }

            $data = [
                'kategori_id' => $body['kategori_id'],
                'judul' => $body['judul'],
                'description' => $body['description'],
                'penulis' => $body['penulis'],
                'penerbit' => $body['penerbit'],
                'tahun_terbit' => $body['tahun_terbit'],
                'image' => "post_images/" . $imageName, // Simpan nama file gambar
            ];

            // Tambahkan data buku ke dalam database
            $add = Books::create($data);

            return $this->jsonCreatedResponse('success', $add);
        } catch (\Throwable $e) {
            return $this->jsonErrorResponse('internal server error ' . $e->getMessage());
        }
    }


    public function getByID($id)
    {
        try {
            $data = Books::with([])->where('id', '=', $id)->first();
            if (!$data) {
                return $this->jsonNotFoundResponse(' not found');
            }
            if ($this->request->method() === 'POST') {
                return $this->patch($data);
            }
            return $this->jsonSuccessResponse('success', $data);
        } catch (\Throwable $e) {
            return $this->jsonErrorResponse('internal server error ' . $e->getMessage());
        }
    }

    private function patch($data)
    {
        $body = $this->parseRequestBody();
        $data_request = [
            'kategori_id' => $body['kategori_id'],
            'judul' => $body['judul'],
            'penulis' => $body['penulis'],
            'penerbit' => $body['penerbit'],
            'tahun_terbit' => $body['tahun_terbit'],
        ];
        $data->update($data_request);
        return $this->jsonCreatedResponse('success');
    }

    public function delete($id)
{
    try {
        // Cari buku berdasarkan ID
        $book = Books::find($id);

        // Jika buku tidak ditemukan, kembalikan respons dengan pesan tidak ditemukan
        if (!$book) {
            return $this->jsonNotFoundResponse('Book not found');
        }

        // Hapus gambar terkait jika ada
        if ($book->image) {
            // Hapus gambar dari penyimpanan
            unlink($book->image);
        }

        // Hapus entri yang terkait dengan buku dari tabel koleksi

        // Hapus entri yang terkait dengan buku dari tabel peminjaman
        $book->peminjaman()->delete();
        
        $book->ulasan()->delete();

        $book->koleksi()->delete();

        $book->pemesanan()->delete();

        // Hapus buku dari database
        $book->delete();

        // Kembalikan respons berhasil
        return $this->jsonSuccessResponse('Book and related entries deleted successfully');
    } catch (\Throwable $e) {
        // Tangani kesalahan internal server
        return $this->jsonErrorResponse('Internal server error: ' . $e->getMessage());
    }
}


    public function update(Request $request, $id)
    {
        try {
            $body = $this->parseRequestBody();

            $validator = Validator::make($body, [
                'kategori_id' => 'required',
                'judul' => 'required|string',
                'description' => 'required|string|max:600',
                'penulis' => 'required|string',
                'penerbit' => 'required|string',
                'tahun_terbit' => 'required|integer',
                'image' => 'image|mimes:jpeg,png,jpg,gif|max:2048', // Validasi gambar
            ]);

            if ($validator->fails()) {
                return $this->jsonNotFoundResponse($validator->errors()->first());
            }

            $book = Books::findOrFail($id); // Cari buku berdasarkan ID

            if ($request->hasFile('image')) {
                if ($book->image) {
                    unlink($book->image);
                }
            }

            // Update data buku dengan data baru
            $book->kategori_id = $body['kategori_id'];
            $book->judul = $body['judul'];
            $book->description = $body['description'];
            $book->penulis = $body['penulis'];
            $book->penerbit = $body['penerbit'];
            $book->tahun_terbit = $body['tahun_terbit'];

            // Jika ada file gambar yang diunggah, simpan gambar baru
            if ($request->hasFile('image')) {
                $image = $request->file('image');
                $imageName = time() . '.' . $image->getClientOriginalExtension();
                $storagePath = 'post_images';
                $image->move($storagePath, $imageName);
                $book->image = "post_images/" . $imageName;
            }

            // Simpan perubahan
            $book->save();

            return $this->jsonSuccessResponse('Data berhasil diperbarui', $book);
        } catch (\Throwable $e) {
            return $this->jsonErrorResponse('Internal server error: ' . $e->getMessage());
        }
    }

    public function search(Request $request)
    {
        $query = $request->input('judul');

        // Lakukan pencarian buku berdasarkan query
        $books = Books::where('judul', 'like', '%' . $query . '%')->with(['kategori:id,nama'])
                     ->get();

        return $this->jsonSuccessResponse('data', $books);
    }
}

