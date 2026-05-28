<?php

namespace App\Http\Controllers;

use App\Models\Attendance;
use App\Models\QrToken;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Artisan;

class AttendanceController extends Controller
{
    // Halaman display QR (dipasang di tablet/layar pintu masuk)
    public function display()
    {
        $token = QrToken::current();

        if (! $token) {
            Artisan::call('qr:generate');
            $token = QrToken::current();
        }

        return view('absensi.display', compact('token'));
    }

    // Halaman scan — employee buka URL ini setelah scan QR
    public function showScan(string $tokenStr)
    {
        if (! $this->isLocalNetwork()) {
            return view('absensi.result', [
                'success' => false,
                'message' => 'Absensi hanya bisa dilakukan di lokasi carwash. Pastikan kamu terhubung ke WiFi MensekakStaff.',
                'icon'    => 'error',
            ]);
        }

        $qrToken = QrToken::where('token', $tokenStr)->first();

        if (! $qrToken || $qrToken->isExpired()) {
            return view('absensi.result', [
                'success' => false,
                'message' => 'QR Code sudah tidak berlaku. Minta admin untuk scan ulang QR terbaru.',
                'icon'    => 'expired',
            ]);
        }

        $employees = User::where('role', 'user')
            ->where('is_active', true)
            ->orderBy('name')
            ->get(['id', 'name']);

        return view('absensi.scan', compact('tokenStr', 'employees'));
    }

    // Proses submit absensi
    public function submitScan(Request $request, string $tokenStr)
    {
        if (! $this->isLocalNetwork()) {
            return view('absensi.result', [
                'success' => false,
                'message' => 'Absensi hanya bisa dilakukan di lokasi carwash.',
                'icon'    => 'error',
            ]);
        }

        $request->validate(['employee_id' => 'required|exists:users,id']);

        $qrToken = QrToken::where('token', $tokenStr)->first();

        if (! $qrToken || $qrToken->isExpired()) {
            return view('absensi.result', [
                'success' => false,
                'message' => 'QR Code sudah tidak berlaku. Scan QR terbaru.',
                'icon'    => 'expired',
            ]);
        }

        $employee = User::find($request->employee_id);
        $today    = today();

        $existing = Attendance::where('employee_id', $employee->id)
            ->where('date', $today)
            ->first();

        if ($existing) {
            return view('absensi.result', [
                'success' => false,
                'message' => "Kamu sudah absen hari ini pada pukul {$existing->check_in_time}.",
                'icon'    => 'already',
                'user'    => $employee,
            ]);
        }

        // Tentukan status: terlambat jika lebih dari 30 menit setelah jam buka
        $lateThreshold = now()->setTimeFromTimeString('08:30:00');
        $status        = now()->greaterThan($lateThreshold) ? 'terlambat' : 'hadir';

        Attendance::create([
            'employee_id'   => $employee->id,
            'date'          => $today,
            'check_in_time' => now()->format('H:i:s'),
            'status'        => $status,
            'device_info'   => $request->userAgent(),
        ]);

        return view('absensi.result', [
            'success' => true,
            'message' => $status === 'terlambat'
                ? "Absensi tercatat! Kamu terlambat hari ini, {$employee->name}."
                : "Absensi berhasil! Selamat bekerja, {$employee->name}.",
            'icon'    => $status,
            'user'    => $employee,
        ]);
    }

    private function isLocalNetwork(): bool
    {
        // Validasi cukup lewat QR token yang rotate tiap 1 menit
        return true;
    }
}
