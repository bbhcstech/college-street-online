<?php

namespace App\Services;

use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;

class PublicImageStorageService
{
    public function storeBookCover(UploadedFile $file): string
    {
        return $file->store('book-covers', 'public');
    }

    public function storePaymentQr(UploadedFile $file): string
    {
        return $file->store('payment-qr', 'public');
    }

    public function delete(?string $path): void
    {
        if ($path && ! str_starts_with($path, 'http')) {
            Storage::disk('public')->delete($path);
        }
    }
}
