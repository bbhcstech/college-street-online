<?php
namespace App\Services;

/**
 * Bengali (Bangla) -> English phonetic transliteration for search indexing
 * (FR-4). This is a simple character-map transliterator suitable for
 * search-index purposes; it is intentionally not a full linguistic
 * transliteration engine.
 */
class TransliterationService
{
    protected array $map = [
        'অ'=>'a','আ'=>'aa','ই'=>'i','ঈ'=>'ii','উ'=>'u','ঊ'=>'uu','এ'=>'e','ঐ'=>'oi','ও'=>'o','ঔ'=>'ou',
        'ক'=>'k','খ'=>'kh','গ'=>'g','ঘ'=>'gh','ঙ'=>'ng','চ'=>'ch','ছ'=>'chh','জ'=>'j','ঝ'=>'jh','ঞ'=>'n',
        'ট'=>'t','ঠ'=>'th','ড'=>'d','ঢ'=>'dh','ণ'=>'n','ত'=>'t','থ'=>'th','দ'=>'d','ধ'=>'dh','ন'=>'n',
        'প'=>'p','ফ'=>'ph','ব'=>'b','ভ'=>'bh','ম'=>'m','য'=>'j','র'=>'r','ল'=>'l','শ'=>'sh','ষ'=>'sh',
        'স'=>'s','হ'=>'h','ড়'=>'r','ঢ়'=>'rh','য়'=>'y','ৎ'=>'t',
        'া'=>'a','ি'=>'i','ী'=>'ii','ু'=>'u','ূ'=>'uu','ে'=>'e','ৈ'=>'oi','ো'=>'o','ৌ'=>'ou','ং'=>'ng','ঃ'=>'h','ঁ'=>'n',
        '০'=>'0','১'=>'1','২'=>'2','৩'=>'3','৪'=>'4','৫'=>'5','৬'=>'6','৭'=>'7','৮'=>'8','৯'=>'9',
    ];

    public function transliterate(string $text): string
    {
        $out = '';
        foreach (mb_str_split($text) as $ch) {
            $out .= $this->map[$ch] ?? $ch;
        }
        return $out;
    }
}
