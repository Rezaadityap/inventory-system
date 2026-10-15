<?php

namespace App\Console\Commands;

use App\Models\Customer;
use App\Models\ReturItem;
use App\Models\TransactionHistory;
use App\Models\User;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Storage;

class PruneOrphanPhotos extends Command
{
    protected $signature = 'photos:prune {--force : Hapus filenya, bukan cuma melaporkan}';

    protected $description = 'Cari file foto di storage yang sudah tidak dirujuk baris mana pun.';

    private const DIRECTORIES = [
        'company_photos',
        'driver_images',
        'retur_photos',
        'shipment_photos',
        'user_images',
    ];

    public function handle(): int
    {
        $referenced = collect()
            ->merge(User::whereNotNull('photo')->pluck('photo'))
            ->merge(Customer::whereNotNull('company_photo')->pluck('company_photo'))
            ->merge(TransactionHistory::whereNotNull('photo_path')->pluck('photo_path'))
            ->merge(ReturItem::whereNotNull('photo_path')->pluck('photo_path'))
            ->unique();

        $disk = Storage::disk('public');
        $orphans = collect();

        foreach (self::DIRECTORIES as $directory) {
            $files = collect($disk->files($directory))->diff($referenced);

            if ($files->isEmpty()) {
                continue;
            }

            $this->line("<fg=yellow>{$directory}</> ({$files->count()} yatim)");

            foreach ($files as $file) {
                $size = number_format($disk->size($file) / 1024, 0, ',', '.');
                $this->line("  {$file}  <fg=gray>{$size} KB</>");
            }

            $orphans = $orphans->merge($files);
            $this->newLine();
        }

        if ($orphans->isEmpty()) {
            $this->info('Tidak ada file yatim. Storage bersih.');

            return self::SUCCESS;
        }

        if (! $this->option('force')) {
            $this->warn("{$orphans->count()} file yatim ditemukan. Jalankan ulang dengan --force untuk menghapusnya.");

            return self::SUCCESS;
        }

        $disk->delete($orphans->all());

        $this->info("{$orphans->count()} file yatim dihapus.");

        return self::SUCCESS;
    }
}
