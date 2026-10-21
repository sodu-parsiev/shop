<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Unlabelled spot => labelled spot, per page slug. Privacy section 1 and
     * the requisites already say "юридический адрес" and are not matched.
     *
     * @var array<string, array{string, string}>
     */
    private const REPLACEMENTS = [
        'privacy' => [
            'ИНН 3700025101, 153025, г. Иваново',
            'ИНН 3700025101, юридический адрес: 153025, г. Иваново',
        ],
        'consent' => [
            'ОГРН 1243700009915, 153025, г. Иваново',
            'ОГРН 1243700009915, юридический адрес: 153025, г. Иваново',
        ],
    ];

    /**
     * Privacy section 10 and the consent text list the Ivanovo address
     * without saying it is the legal one, so it reads like the office.
     * A plain string replace keeps any other edits admins made to the texts.
     */
    public function up(): void
    {
        foreach (self::REPLACEMENTS as $slug => [$unlabelled, $labelled]) {
            $this->replaceInBody($slug, $unlabelled, $labelled);
        }
    }

    public function down(): void
    {
        foreach (self::REPLACEMENTS as $slug => [$unlabelled, $labelled]) {
            $this->replaceInBody($slug, $labelled, $unlabelled);
        }
    }

    private function replaceInBody(string $slug, string $from, string $to): void
    {
        DB::table('pages')
            ->where('slug', $slug)
            ->where('body', 'like', '%'.$from.'%')
            ->pluck('body', 'id')
            ->each(fn (string $body, int $id) => DB::table('pages')->where('id', $id)->update([
                'body' => str_replace($from, $to, $body),
                'updated_at' => now(),
            ]));
    }
};
