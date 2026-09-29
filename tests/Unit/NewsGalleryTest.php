<?php

namespace Tests\Unit;

use App\Support\NewsGallery;
use PHPUnit\Framework\TestCase;

class NewsGalleryTest extends TestCase
{
    public function test_four_photos_use_a_pair_then_full_width_rows(): void
    {
        $rows = NewsGallery::rows(['a.jpg', 'b.jpg', 'c.jpg', 'd.jpg']);

        $this->assertSame([
            ['a.jpg', 'b.jpg'],
            ['c.jpg'],
            ['d.jpg'],
        ], $rows);
    }

    public function test_three_photos_match_the_article_layout(): void
    {
        $rows = NewsGallery::rows(['left.jpg', 'right.jpg', 'wide.jpg']);

        $this->assertSame([
            ['left.jpg', 'right.jpg'],
            ['wide.jpg'],
        ], $rows);
    }

    public function test_extra_photos_repeat_the_pair_then_full_width_pattern(): void
    {
        $rows = NewsGallery::rows(['1', '2', '3', '4', '5', '6']);

        $this->assertSame([
            ['1', '2'],
            ['3'],
            ['4', '5'],
            ['6'],
        ], $rows);
    }

    public function test_empty_paths_are_skipped(): void
    {
        $rows = NewsGallery::rows(['a.jpg', '', '  ', 'b.jpg']);

        $this->assertSame([
            ['a.jpg', 'b.jpg'],
        ], $rows);
    }

    public function test_decode_ignores_invalid_json(): void
    {
        $this->assertSame([], NewsGallery::decode(null));
        $this->assertSame(['/uploads/news/a.jpg'], NewsGallery::decode('["/uploads/news/a.jpg", ""]'));
    }
}
