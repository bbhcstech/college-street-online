<?php

namespace App\Services;

use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;

class PublicImageStorageService
{
    public function storeBookCover(UploadedFile $file): string
    {
        return $file->store('book-covers', config('filesystems.default', 'public'));
    }

    public function storePaymentQr(UploadedFile $file): string
    {
        return $file->store('payment-qr', config('filesystems.default', 'public'));
    }

    public function delete(?string $path): void
    {
        if ($path && ! str_starts_with($path, 'http')) {
            Storage::disk(config('filesystems.default', 'public'))->delete($path);
        }
    }
}
