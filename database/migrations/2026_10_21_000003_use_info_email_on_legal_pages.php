<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    private const OLD_EMAIL = 'office@svoyhod.ru';

    private const NEW_EMAIL = 'info@svoyhod.ru';

    /**
     * Legal pages were seeded with office@, while the rest of the site shows info@.
     * A plain string replace keeps any other edits admins made to the texts;
     * no-op on a fresh install (pages are seeded later with info@ already).
     */
    public function up(): void
    {
        $this->replaceInBodies(self::OLD_EMAIL, self::NEW_EMAIL);
    }

    public function down(): void
    {
        $this->replaceInBodies(self::NEW_EMAIL, self::OLD_EMAIL, ['privacy', 'consent', 'requisites']);
    }

    /**
     * @param  list<string>|null  $slugs
     */
    private function replaceInBodies(string $from, string $to, ?array $slugs = null): void
    {
        DB::table('pages')
            ->where('body', 'like', '%'.$from.'%')
            ->when($slugs, fn ($query) => $query->whereIn('slug', $slugs))
            ->pluck('body', 'id')
            ->each(fn (string $body, int $id) => DB::table('pages')->where('id', $id)->update([
                'body' => str_replace($from, $to, $body),
                'updated_at' => now(),
            ]));
    }
};
