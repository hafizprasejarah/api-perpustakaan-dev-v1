<?php

namespace App\Http\Controllers;


use App\Models\Users;
use App\Helper\CustomController;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Tymon\JWTAuth\Facades\JWTAuth;
use Illuminate\Support\Facades\Validator;
use Illuminate\Http\Request;

class UsersController extends CustomController
{

    public function store()
    {
        try {
            $body = $this->parseRequestBody();
            // Membuat validator dengan aturan validasi
            $validator = Validator::make($body, [
                'nama' => 'required|string',
                'username' => 'required|string|',
                'email' => 'required|email|unique:users|',
                'telp' => 'required|string',
                'alamat' => 'required|string',
                'password' => 'required|string|min:6',
            ]);

            // Cek jika validasi gagal
            if ($validator->fails()) {

                return $this->jsonNotFoundResponse($validator->errors()->first());
            }
            $data = [
                'nama' => $body['nama'],
                'username' => $body['username'],
                'email' => $body['email'],
                'telp' => $body['telp'],
                'alamat' => $body['alamat'],
                'role' => 'PEMINJAM',
                'password' => Hash::make($body['password']),

            ];


            $occupant = Users::create($data);
            return $this->jsonCreatedResponse('success', $occupant);
        } catch (\Throwable $e) {
            return $this->jsonErrorResponse('internal server error ' . $e->getMessage());
        }
    }

    public function storeAdmin()
    {
        try {
            $body = $this->parseRequestBody();
            // Membuat validator dengan aturan validasi
            $validator = Validator::make($body, [
                'nama' => 'required|string',
                'username' => 'required|string|',
                'email' => 'required|email|unique:users|',
                'telp' => 'required|string',
                'alamat' => 'required|string',
                'password' => 'required|string|min:6',
            ]);

            // Cek jika validasi gagal
            if ($validator->fails()) {

                return $this->jsonNotFoundResponse($validator->errors()->first());
            }
            $data = [
                'nama' => $body['nama'],
                'username' => $body['username'],
                'email' => $body['email'],
                'telp' => $body['telp'],
                'alamat' => $body['alamat'],
                'role' => 'ADMIN',
                'password' => Hash::make($body['password']),

            ];


            $occupant = Users::create($data);
            return $this->jsonCreatedResponse('success', $occupant);
        } catch (\Throwable $e) {
            return $this->jsonErrorResponse('internal server error ' . $e->getMessage());
        }
    }

    public function login()
    {
        try {
            $username = $this->postField('username');
            $password = $this->postField('password');

            $user = Users::with([])
                ->where('username', '=', $username)
                ->where('role', '=', 'PEMINJAM')
                ->first();
            if (!$user) {
                return $this->jsonNotFoundResponse('user not found');
            }

            $isPasswordValid = Hash::check($password, $user->password);
            if (!$isPasswordValid) {
                return $this->jsonUnauthorizedResponse('password did not match');
            }
            // Generate token
            $token = JWTAuth::fromUser($user);

            return $this->jsonSuccessResponse('Login Success', [
                'user' => $user,
                'token' => $token
            ]);
        } catch (\Throwable $e) {
            return $this->jsonErrorResponse('internal server error ' . $e->getMessage());
        }
    }

    public function loginPetugas()
    {
        try {
            $username = $this->postField('username');
            $password = $this->postField('password');

            $user = Users::with([])
                ->where('username', '=', $username)
                ->where('role', '!=', 'PEMINJAM')
                ->first();
            if (!$user) {
                return $this->jsonNotFoundResponse('user not found');
            }

            $isPasswordValid = Hash::check($password, $user->password);
            if (!$isPasswordValid) {
                return $this->jsonUnauthorizedResponse('password did not match');
            }


            // Generate token
            $token = JWTAuth::fromUser($user);

            return $this->jsonSuccessResponse('Login Success', [
                'user' => $user,
                'token' => $token
            ]);
        } catch (\Throwable $e) {
            return $this->jsonErrorResponse('internal server error ' . $e->getMessage());
        }
    }

    public function loginAdmin()
    {
        try {
            $username = $this->postField('username');
            $password = $this->postField('password');

            $user = Users::with([])
                ->where('username', '=', $username)
                ->where('role', '=', 'ADMIN')
                ->first();
            if (!$user) {
                return $this->jsonNotFoundResponse('user not found');
            }

            $isPasswordValid = Hash::check($password, $user->password);
            if (!$isPasswordValid) {
                return $this->jsonUnauthorizedResponse('password did not match');
            }


            // Generate token
            $token = JWTAuth::fromUser($user);

            return $this->jsonSuccessResponse('Login Success', [
                'user' => $user,
                'token' => $token
            ]);
        } catch (\Throwable $e) {
            return $this->jsonErrorResponse('internal server error ' . $e->getMessage());
        }
    }

    public function user()
    {

        try {
            $user = auth()->user();
            return $this->jsonSuccessResponse('Success',$user);
        } catch (\Throwable $e) {
            return $this->jsonErrorResponse('Internal server error ' . $e->getMessage());
        }
    }


  

    public function Alluser(Request $request)
    {
        $role = $request->input('role');
        try {
            // Deklarasi variabel data
            $data = null;
    
            // Cek apakah parameter role diberikan
            if ($role) {
                // Jika parameter role diberikan, filter data berdasarkan role
                $data = Users::where('role', $role)
                    ->orderBy('created_at', 'DESC')
                    ->get();
            } else {
                // Jika parameter role tidak diberikan, ambil semua data pengguna
                $data = Users::orderBy('created_at', 'DESC')->get();
            }
    
            // Periksa apakah data kosong
            if ($data->isEmpty()) {
                return $this->jsonNotFoundResponse('Data not found!');
            }
    
            // Kembalikan data pengguna yang ditemukan
            return $this->jsonSuccessResponse('Success', $data);
        } catch (\Throwable $e) {
            // Tangkap pengecualian dan kembalikan pesan kesalahan
            return $this->jsonErrorResponse('Internal server error: ' . $e->getMessage());
        }
    }

    public function update(Request $request, $id)
    {
        try {
            // Temukan pengguna berdasarkan ID
            $user = Users::find($id);
    
            // Periksa apakah pengguna ditemukan
            if (!$user) {
                return  response()->json([
                    'message' => 'User not found'
                ], 404);
            }
    
            // Periksa apakah pengguna memiliki izin untuk memperbarui data
            if ($user->id != auth()->user()->id) {
                return  response()->json([
                    'message' => 'Permission denied'
                ], 403);
            }
    
           
            $validator = Validator::make($request->all(), [
                'nama' => 'required|string',
                'telp' => 'required|string',
                'alamat' => 'required|string',
                'image' => 'image|mimes:jpeg,png,jpg,gif|max:2048',
            ]);
    
            if ($validator->fails()) {
                return response()->json([
                    'message' => $validator->errors()->first(),
                ], 400);
            }
            if ($request->hasFile('image')) {
                
                // Dapatkan instance dari gambar yang diunggah
                $image = $request->file('image');
    
                // Buat nama unik untuk file gambar
                $imageName = time().'.'.$image->getClientOriginalExtension();
    
                // Direktori penyimpanan gambar
                $storagePath = 'images';
    
                // Simpan gambar ke dalam direktori penyimpanan
                $image->move($storagePath, $imageName);
    
                // Hapus gambar lama jika ada
                if ($user->image) {
                    // Hapus gambar lama dari direktori penyimpanan
                    unlink($user->image);
                }
    
                // Simpan path atau URL gambar baru ke dalam database
                $user->image = $storagePath.'/'.$imageName;
                $user->save();
            }
    
            // Perbarui data pengguna
            $user->update([
                'nama' => $request->input('nama'),
                'telp' => $request->input('telp'),
                'alamat' => $request->input('alamat'),
            ]);
        
            return response()->json([
                'message' => 'User updated successfully',
                'user' => $user
            ]);
        } catch (\Throwable $e) {
            return response()->json([
                'message' => 'Internal server error: ' . $e->getMessage(),
            ], 500);
        }
    }

    public function logout()
    {
        try {
            // Hapus token dari user saat logout
            JWTAuth::parseToken()->invalidate();
    
            return $this->jsonSuccessResponse('Logout Success');
        } catch (\Throwable $e) {
            return $this->jsonErrorResponse('Internal server error ' . $e->getMessage());
        }
    }

    public function updateAdmin(Request $request, $id)
    {
        try {
            // Temukan pengguna berdasarkan ID
            $user = Users::find($id);
    
            // Periksa apakah pengguna ditemukan
            if (!$user) {
                return response()->json([
                    'message' => 'User not found'
                ], 404);
            }
    
    
            // Validasi input
            $validator = Validator::make($request->all(), [
                'nama' => 'required|string',
                'username' => 'required|string',
                'email' => ['required', 'email', 'unique:users,email,' . $id],
                'telp' => 'required|string',
                'alamat' => 'required|string',
                'role' => 'required|string',
                'image' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
            ]);
    
            if ($validator->fails()) {
                return response()->json([
                    'message' => $validator->errors()->first(),
                    'id' => $id
                ], 400);
            }
    
            // Jika ada gambar yang diunggah, tangani unggahannya
            if ($request->hasFile('image')) {
                // Dapatkan instance dari gambar yang diunggah
                $image = $request->file('image');
    
                // Buat nama unik untuk file gambar
                $imageName = time() . '.' . $image->getClientOriginalExtension();
    
                // Direktori penyimpanan gambar
                $storagePath = 'images';
    
                // Simpan gambar ke dalam direktori penyimpanan
                $image->move($storagePath, $imageName);
    
                // Hapus gambar lama jika ada
                if ($user->image) {
                    // Hapus gambar lama dari direktori penyimpanan
                    unlink($user->image);
                }
    
                // Simpan path atau URL gambar baru ke dalam database
                $user->image = $storagePath . '/' . $imageName;
            }
    
            // Perbarui data pengguna
            $user->update([
                'nama' => $request->input('nama'),
                'username' => $request->input('username'),
                'email' => $request->input('email'),
                'telp' => $request->input('telp'),
                'role' => $request->input('role'),
                'alamat' => $request->input('alamat'),
            ]);
    
            return response()->json([
                'message' => 'User updated successfully',
                'user' => $user
            ]);
        } catch (\Throwable $e) {
            return response()->json([
                'message' => 'Internal server error: ' . $e->getMessage(),
               
            ], 500);
        }
    }
    

    public function search(Request $request)
    {
        $query = $request->input('nama');

        // Lakukan pencarian buku berdasarkan query
        $books = Users::where('nama', 'like', '%' . $query . '%')->get();

        return $this->jsonSuccessResponse('data', $books);
    }

}
