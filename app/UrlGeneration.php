<?php

namespace App;

use Illuminate\Support\Str;

trait UrlGeneration
{
    protected function generateSlug(string $value, ?int $id = null): string
    {
        $slug = mb_strtolower(Str::slug(Str::ascii($value)));

        $slug_already_exists = self::where('slug', $slug)
            ->when(!empty($id), fn ($query) => $query->where('id', '!=', $id))
            ->exists();

        if ($slug_already_exists) {
            $time = '-' . time();

            $slug = strlen($slug . $time) > 255
                ? substr($slug, 0, 255 - strlen($time)) . $time
                : $slug. $time;
        }

        return $slug;
    }
}
