<?php

namespace App\Services;

use Cloudinary\Cloudinary;
use Illuminate\Http\UploadedFile;
use Illuminate\Validation\ValidationException;

class CloudinaryImageService
{
    public function uploadBookCover(UploadedFile $file): array
    {
        $cloudinary = $this->client();
        $result = $cloudinary->uploadApi()->upload($file->getRealPath(), [
            'folder' => config('services.cloudinary.folder'),
            'resource_type' => 'image',
            'use_filename' => false,
            'unique_filename' => true,
        ]);

        return [
            'url' => (string) $result['secure_url'],
            'public_id' => (string) $result['public_id'],
        ];
    }

    public function delete(?string $publicId): void
    {
        if ($publicId) {
            $this->client()->uploadApi()->destroy($publicId, ['resource_type' => 'image']);
        }
    }

    private function client(): Cloudinary
    {
        $url = config('services.cloudinary.url');
        if (! $url) {
            throw ValidationException::withMessages([
                'cover_image' => 'Cloudinary is not configured. Add CLOUDINARY_URL to the environment file.',
            ]);
        }

        return new Cloudinary($url);
    }
}
