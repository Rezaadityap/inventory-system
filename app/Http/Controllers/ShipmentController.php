<?php

namespace App\Http\Controllers;

use App\Models\Driver;
use App\Models\Shipment;
use App\Models\Stock;
use App\Models\StockHistory;
use App\Models\Transaction;
use App\Notifications\ShipmentReceivedNotification;
use App\Services\InvoiceService;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;

class ShipmentController extends Controller
{
    private array $statuses = ['Dikirim', 'Sampai', 'Diterima'];

    public function index()
    {
        // Transaksi yang sudah disetujui dan belum punya pengiriman
        $transactions = Transaction::with('customer')
            ->where('status', 'Disetujui')
            ->whereDoesntHave('shipment')
            ->latest()
            ->get();

        $drivers = Driver::with('user')->get();

        $availableDrivers = $drivers->where('available_status', 'Tersedia')->values();

        $shipments = Shipment::with('transaction.customer', 'driver.user', 'shipper', 'receiver')
            ->latest()
            ->get();

        return view('shipment.index', compact('transactions', 'drivers', 'availableDrivers', 'shipments'));
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'transaction_id' => ['required', 'exists:transactions,id', Rule::unique('shipments', 'transaction_id')],
            'driver_id' => 'required|exists:drivers,id',
            'vehicle_number' => 'required|string|max:20',
            'planned_shipped_at' => 'required|date|after_or_equal:today',
        ]);

        $transaction = Transaction::with('items')->findOrFail($data['transaction_id']);

        if ($transaction->status !== 'Disetujui') {
            throw ValidationException::withMessages([
                'transaction_id' => 'Transaksi belum disetujui atau sudah diproses.',
            ]);
        }

        $this->guardDriverIsAvailable((int) $data['driver_id']);

        DB::transaction(function () use ($data, $transaction) {
            $shipment = Shipment::create([
                'transaction_id' => $transaction->id,
                'driver_id' => $data['driver_id'],
                'shipment_number' => 'TEMP-' . uniqid(),
                'vehicle_number' => $data['vehicle_number'],
                'status' => 'Diproses',
                'shipped_by' => Auth::id(),
                'planned_shipped_at' => $data['planned_shipped_at'],
                'estimated_arrival' => $data['planned_shipped_at'],
            ]);

            $shipment->update(['shipment_number' => $this->generateShipmentNumber($shipment)]);

            $this->setDriverAvailability($shipment->driver_id, 'Sibuk');

            // Stok baru dikurangi
            foreach ($transaction->items as $item) {
                $this->adjustStock($transaction->id, $item->waste_id, $item->qty, "Pengiriman {$shipment->shipment_number}");
            }

            $transaction->update(['status' => 'Diproses']);

            $transaction->histories()->create([
                'status' => 'Diproses',
                'description' => "Limbah sedang diproses untuk pengiriman.",
                'user_id' => Auth::id(),
            ]);
        });

        return redirect()->route('shipments.index')->with('success', 'Pengiriman berhasil dibuat.');
    }

    public function show(Shipment $shipment)
    {
        $shipment->load('transaction.customer', 'transaction.items.waste', 'transaction.histories', 'driver.user', 'shipper', 'receiver');

        $user = Auth::user();
        abort_unless(
            $user->hasRole('admin')
                || $shipment->transaction?->customer?->user_id === $user->id
                || $shipment->driver?->user_id === $user->id,
            403,
        );

        if ($user->hasRole('pengemudi')) {
            abort_unless(
                $shipment->planned_shipped_at === null || !$shipment->planned_shipped_at->isFuture(),
                403,
                'Jadwal pengiriman ini belum bisa diakses.',
            );
        }

        $sequence = ['Diproses', 'Dikirim', 'Sampai', 'Diterima'];
        $stepIndex = array_search($shipment->status, $sequence, true);
        $step = $stepIndex === false ? 0 : $stepIndex + 1;

        $shippedHistory = $shipment->transaction?->histories->firstWhere('status', 'Dikirim');
        $shippedProof = $shippedHistory?->photo_path ? Storage::url($shippedHistory->photo_path) : null;

        $timeline = [
            [
                'label' => 'Diproses',
                'description' => 'Pengiriman dibuat dan limbah disiapkan.',
                'at' => $shipment->created_at?->translatedFormat('d M Y H:i'),
                'by' => $shipment->shipper?->name,
                'done' => $step >= 1,
            ],
            [
                'label' => 'Dikirim',
                'description' => 'Paket diserahkan ke pengemudi dan dalam perjalanan.',
                'at' => $shipment->shipped_at?->translatedFormat('d M Y H:i'),
                'by' => $shipment->driver?->user?->name,
                'proof' => $shippedProof,
                'done' => $step >= 2,
            ],
            [
                'label' => 'Sampai di Tujuan',
                'description' => 'Kendaraan tiba di lokasi pelanggan.',
                'at' => $shipment->arrived_at?->translatedFormat('d M Y H:i'),
                'by' => $shipment->driver?->user?->name,
                'done' => $step >= 3,
            ],
            [
                'label' => 'Diterima',
                'description' => 'Paket diterima dan dikonfirmasi oleh pelanggan.',
                'at' => $shipment->received_at?->translatedFormat('d M Y H:i'),
                'by' => $shipment->receiver?->name,
                'done' => $step >= 4,
            ],
        ];

        return response()->json([
            'id' => $shipment->id,
            'shipment_number' => $shipment->shipment_number,
            'transaction_number' => $shipment->transaction?->transaction_number,
            'customer_name' => $shipment->transaction?->customer?->customer_name,
            'driver_name' => $shipment->driver?->user?->name,
            'vehicle_number' => $shipment->vehicle_number,
            'status' => $shipment->status,
            'shipped_by' => $shipment->shipper?->name,
            'shipped_at' => $shipment->shipped_at?->format('d M Y H:i'),
            'planned_shipped_at' => $shipment->planned_shipped_at?->format('d M Y'),
            'estimated_arrival' => $shipment->estimated_arrival?->format('d M Y'),
            'arrived_at' => $shipment->arrived_at?->format('d M Y H:i'),
            'received_by' => $shipment->receiver?->name,
            'received_at' => $shipment->received_at?->format('d M Y H:i'),
            'timeline' => $timeline,
            'items' => $shipment->transaction?->items->map(fn($item) => [
                'code' => $item->waste?->code,
                'waste_name' => $item->waste?->waste_name,
                'qty' => $item->qty,
                'unit' => $item->waste?->unit,
                'note_item' => $item->note_item,
            ])->values() ?? [],
        ]);
    }

    public function update(Request $request, Shipment $shipment)
    {
        $data = $request->validate([
            'transaction_id' => [
                'required',
                'exists:transactions,id',
                Rule::unique('shipments', 'transaction_id')->ignore($shipment->id),
            ],
            'driver_id' => 'required|exists:drivers,id',
            'vehicle_number' => 'required|string|max:20',
            'status' => ['required', Rule::in($this->statuses)],
        ]);

        $previousStatus = $shipment->status;

        abort_unless(
            $previousStatus !== 'Diterima' || $data['status'] === 'Diterima',
            403,
            'Pengiriman yang sudah diterima tidak bisa dikembalikan ke status sebelumnya karena invoicenya sudah terbit.',
        );

        $previousDriverId = $shipment->driver_id;
        $newDriverId = (int) $data['driver_id'];

        if ($newDriverId !== $previousDriverId) {
            $this->guardDriverIsAvailable($newDriverId);
        }

        DB::transaction(function () use ($data, $shipment, $previousDriverId, $newDriverId) {
            $shipment->update([
                'transaction_id' => $data['transaction_id'],
                'driver_id' => $data['driver_id'],
                'vehicle_number' => $data['vehicle_number'],
                'status' => $data['status'],
                'arrived_at' => in_array($data['status'], ['Sampai', 'Diterima'], true)
                    ? ($shipment->arrived_at ?? now())
                    : null,
                'received_at' => $data['status'] === 'Diterima' ? ($shipment->received_at ?? now()) : null,
                'received_by' => $data['status'] === 'Diterima' ? ($shipment->received_by ?? Auth::id()) : null,
            ]);

            if ($newDriverId !== $previousDriverId) {
                $this->setDriverAvailability($previousDriverId, 'Tersedia');
                $this->setDriverAvailability($newDriverId, 'Sibuk');
            }

            if ($data['status'] === 'Diterima') {
                $shipment->transaction?->update(['status' => 'Selesai']);
                $this->setDriverAvailability($newDriverId, 'Tersedia');
            } else {
                $shipment->transaction?->update(['status' => 'Dikirim']);
            }
        });

        if ($data['status'] === 'Diterima' && $previousStatus !== 'Diterima') {
            $this->afterReceipt($shipment->fresh());
        }

        return redirect()->route('shipments.index')->with('success', 'Pengiriman berhasil diperbarui.');
    }

    public function ship(Request $request, Shipment $shipment)
    {
        $user = Auth::user();

        abort_unless($user->hasRole('pengemudi'), 403);
        abort_unless($shipment->driver?->user_id === $user->id, 403, 'Pengiriman ini bukan jadwal Anda.');
        abort_unless($shipment->status === 'Diproses', 403, 'Status pengiriman tidak bisa diubah.');
        abort_unless(
            $shipment->planned_shipped_at === null || !$shipment->planned_shipped_at->isFuture(),
            403,
            'Jadwal pengiriman ini belum bisa dikirim.',
        );

        $request->validate([
            'photo' => 'required|image|mimes:jpeg,jpg,png,webp|max:2048',
        ]);

        DB::transaction(function () use ($request, $shipment) {
            $photoPath = $request->file('photo')->store('shipment_photos', 'public');

            $shippedNow = now();
            $shippedOnPlan = $shipment->planned_shipped_at?->isSameDay($shippedNow) ?? true;

            $shipment->update([
                'status' => 'Dikirim',
                'shipped_at' => $shippedNow,
                'estimated_arrival' => $shippedOnPlan ? $shipment->estimated_arrival : $shippedNow,
            ]);

            $shipment->transaction?->update(['status' => 'Dikirim']);

            $shipment->transaction?->histories()->create([
                'status' => 'Dikirim',
                'description' => 'Limbah dikirim oleh pengemudi.',
                'photo_path' => $photoPath,
                'user_id' => Auth::id(),
            ]);
        });

        return redirect()->route('shipments.index')->with('success', 'Pengiriman ditandai dikirim.');
    }

    public function arrive(Shipment $shipment)
    {
        $user = Auth::user();

        abort_unless($user->hasRole('pengemudi'), 403);
        abort_unless($shipment->driver?->user_id === $user->id, 403, 'Pengiriman ini bukan jadwal Anda.');
        abort_unless($shipment->status === 'Dikirim', 403, 'Status pengiriman tidak bisa diubah.');

        $shipment->update([
            'status' => 'Sampai',
            'arrived_at' => now(),
        ]);

        return redirect()->route('shipments.index')->with('success', 'Pengiriman ditandai sampai di lokasi.');
    }

    public function receive(Shipment $shipment)
    {
        $user = Auth::user();

        abort_unless($user->hasRole('pelanggan'), 403);
        abort_unless($shipment->transaction?->customer?->user_id === $user->id, 403, 'Pengiriman ini bukan milik Anda.');
        abort_unless($shipment->status === 'Sampai', 403, 'Pengiriman belum sampai di lokasi.');

        DB::transaction(function () use ($shipment, $user) {
            $shipment->update([
                'status' => 'Diterima',
                'received_at' => now(),
                'received_by' => $user->id,
            ]);

            $shipment->transaction?->update(['status' => 'Selesai']);

            $this->setDriverAvailability($shipment->driver_id, 'Tersedia');
        });

        $this->afterReceipt($shipment);

        return redirect()->route('shipments.index')
            ->with('success', 'Paket berhasil dikonfirmasi diterima.')
            ->with('surat_jalan_url', route('shipments.surat-jalan', $shipment));
    }

    public function suratJalan(Shipment $shipment)
    {
        $shipment->load(
            'transaction.customer',
            'transaction.items.waste',
            'transaction.approver',
            'driver.user',
            'shipper',
            'receiver',
        );

        $user = Auth::user();
        abort_unless(
            $user->hasRole('admin')
                || $shipment->transaction?->customer?->user_id === $user->id
                || $shipment->driver?->user_id === $user->id,
            403,
        );

        abort_unless($shipment->status === 'Diterima', 404, 'Surat jalan hanya tersedia setelah paket diterima.');

        $logoPath = public_path('ssg.png');
        $logoBase64 = is_file($logoPath) ? base64_encode(file_get_contents($logoPath)) : null;

        $pdf = Pdf::loadView('pdf.surat-jalan', compact('shipment', 'logoBase64'))->setPaper('a4', 'landscape');

        $safeNumber = str_replace(['/', '\\'], '-', $shipment->shipment_number);

        return $pdf->stream("Surat-Jalan-{$safeNumber}.pdf");
    }

    public function destroy(Shipment $shipment)
    {
        abort_unless(
            $shipment->status === 'Diproses',
            403,
            'Pengiriman yang sudah berjalan tidak bisa dihapus. Status saat ini: ' . $shipment->status . '.',
        );

        DB::transaction(function () use ($shipment) {
            $shipment->load('transaction.items');

            // Kembalikan stok yang sempat dikurangi saat pengiriman dibuat.
            foreach ($shipment->transaction?->items ?? [] as $item) {
                $this->adjustStock(
                    $shipment->transaction_id,
                    $item->waste_id,
                    $item->qty,
                    "Pembatalan pengiriman {$shipment->shipment_number}",
                    'Masuk',
                );
            }

            // Kembalikan transaksi ke status disetujui supaya bisa dibuatkan pengiriman ulang.
            $shipment->transaction?->update(['status' => 'Disetujui']);

            $this->setDriverAvailability($shipment->driver_id, 'Tersedia');

            $shipment->delete();
        });

        return redirect()->route('shipments.index')->with('success', 'Pengiriman berhasil dihapus.');
    }

    // Begitu paket diterima, invoice diterbitkan dengan status "unpaid".
    // Virtual Account BELUM dibuat di sini -- VA baru terbit saat pelanggan
    // memilih bank dan menekan bayar di halaman invoice (InvoiceController::pay).
    private function afterReceipt(Shipment $shipment): void
    {
        if ($shipment->transaction && ! $shipment->transaction->isReplacement()) {
            try {
                app(InvoiceService::class)->generateFromTransaction($shipment->transaction->fresh());
            } catch (\Throwable $e) {
                report($e);
            }
        }

        $shipment->shipper?->notify(new ShipmentReceivedNotification($shipment));
    }

    private function setDriverAvailability(?int $driverId, string $status): void
    {
        if (! $driverId) {
            return;
        }

        Driver::whereKey($driverId)
            ->where('available_status', '!=', 'Libur')
            ->update(['available_status' => $status]);
    }

    private function guardDriverIsAvailable(int $driverId): void
    {
        $driver = Driver::find($driverId);

        if ($driver && $driver->available_status !== 'Tersedia') {
            throw ValidationException::withMessages([
                'driver_id' => "Pengemudi sedang {$driver->available_status} dan belum bisa ditugaskan.",
            ]);
        }
    }

    private function adjustStock(int $transactionId, int $wasteId, int $qty, string $remark, string $type = 'Keluar'): void
    {
        Stock::firstOrCreate(['waste_id' => $wasteId], ['qty' => 0]);

        $stock = Stock::where('waste_id', $wasteId)->lockForUpdate()->first();

        $qtyBefore = $stock->qty;
        $qtyAfter = $type === 'Keluar' ? $qtyBefore - $qty : $qtyBefore + $qty;

        if ($qtyAfter < 0) {
            throw ValidationException::withMessages([
                'transaction_id' => "Stok tidak mencukupi untuk salah satu item. Sisa stok saat ini: {$qtyBefore}",
            ]);
        }

        $stock->update(['qty' => $qtyAfter]);

        StockHistory::create([
            'waste_id' => $wasteId,
            'transaction_id' => $transactionId,
            'type' => $type,
            'source' => 'Pesanan',
            'qty' => $qty,
            'qty_before' => $qtyBefore,
            'qty_after' => $qtyAfter,
            'remark' => $remark,
            'user_id' => Auth::id(),
        ]);
    }

    private function generateShipmentNumber(Shipment $shipment): string
    {
        $sequence = str_pad((string) $shipment->id, 4, '0', STR_PAD_LEFT);

        return $sequence . '/SSG/' . $this->bulanRomawi(now()->month) . '/' . now()->format('Y');
    }

    private function bulanRomawi($bulan)
    {
        $romawi = [
            1 => 'I',
            2 => 'II',
            3 => 'III',
            4 => 'IV',
            5 => 'V',
            6 => 'VI',
            7 => 'VII',
            8 => 'VIII',
            9 => 'IX',
            10 => 'X',
            11 => 'XI',
            12 => 'XII'
        ];

        return $romawi[$bulan] ?? '';
    }
}
