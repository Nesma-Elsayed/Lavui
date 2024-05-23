<?php

namespace App\Http\Traits;
trait ImageTrait
{
    private function uploadImage(object $file, string $path, string $oldImage = null, string $extra_name = null): string
    {
        $imageName = time() . "$extra_name._$path." . $file->extension();
        if ($oldImage) {
            unlink(public_path($oldImage));
        }
        $file->move(public_path("uploaded/$path"), $imageName);
        return $imageName;
    }
}
