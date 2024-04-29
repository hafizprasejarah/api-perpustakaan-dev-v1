<?php

namespace App\Console;

use Illuminate\Console\Scheduling\Schedule;
use Laravel\Lumen\Console\Kernel as ConsoleKernel;
use App\Models\Peminjaman;
use Illuminate\Support\Carbon; // Import kelas Carbon

class Kernel extends ConsoleKernel
{
    /**
     * The Artisan commands provided by your application.
     *
     * @var array
     */
    protected $commands = [
        //
    ];

    /**
     * Define the application's command schedule.
     *
     * @param  \Illuminate\Console\Scheduling\Schedule  $schedule
     * @return void
     */
    protected function schedule(Schedule $schedule)
    {
        $schedule->call(function () {
            // Ambil semua peminjaman yang masih dalam status "DIPROSES"
            $peminjaman = Peminjaman::all();
    
            foreach ($peminjaman as $p) {
                // Periksa selisih waktu antara tanggal pinjam dan tanggal sekarang
                $selisihHari = Carbon::now()->diffInDays($p->tanggal_pinjam);
    
                // Jika sudah lebih dari 2 hari dan status belum dikonfirmasi, ubah status menjadi "GAGAL"
                if ($selisihHari > 2 && $p->status !== 'DIKONFIRMASI') {
                    $p->status = 'GAGAL';
                    $p->save();
                }
    
                // Jika sudah dikonfirmasi dan belum dipinjam lebih dari 4 hari, ubah status menjadi "GAGAL"
                if ($p->status === 'DIKONFIRMASI' && $selisihHari > 4) {
                    $p->status = 'GAGAL';
                    $p->save();
                }
    
                // Jika status "DIPINJAM" dan sudah lewat tanggal pengembalian, ubah status menjadi "DENDA"
                if ($p->status === 'DIPINJAM' && $p->tanggal_kembali < Carbon::now()) {
                    $p->status = 'DENDA';
                    $p->save();
                }
    
                // Jika status "DIKEMBALIKAN" dan sudah lebih dari 24 jam, hapus data peminjaman
                if ($p->status === 'KEMBALI' && $p->updated_at->diffInHours(Carbon::now()) > 24) {
                    $p->delete();
                }

                if ($p->status === 'GAGAL' && $p->updated_at->diffInHours(Carbon::now()) > 48) {
                    $p->delete();
                }
            }
            \Illuminate\Support\Facades\Log::info('Scheduled task completed...');
        })->everyMinute(); // Jalankan setiap hari
    }
}
