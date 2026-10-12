<?php

declare(strict_types=1);

namespace Tests\Feature;

use Illuminate\Pagination\LengthAwarePaginator;
use Tests\TestCase;

// PHPUnit requires the class name to match its *Test.php discovery convention.
class AccountPaginationTest extends TestCase
{
    public function test_directory_pagination_uses_one_tabler_control_set_and_indonesian_labels(): void
    {
        $paginator = new LengthAwarePaginator(range(1, 12), 30, 12, 2, [
            'path' => '/customer-accounts', 'query' => ['search' => 'Surveyor', 'status' => 'active'],
        ]);
        $html = $paginator->onEachSide(1)->links('accounts.partials.pagination')->toHtml();

        $this->assertSame(1, substr_count($html, '<nav '));
        $this->assertSame(1, substr_count($html, '<ul class="pagination mb-0">'));
        $this->assertSame(1, substr_count($html, '>Sebelumnya<'));
        $this->assertSame(1, substr_count($html, '>Berikutnya<'));
        $this->assertStringContainsString('Menampilkan 13 hingga 24 dari 30 data', $html);
        $this->assertStringContainsString('flex-column flex-sm-row', $html);
        $this->assertStringContainsString('aria-current="page"', $html);
        $this->assertStringContainsString('search=Surveyor&amp;status=active&amp;page=1', $html);
        $this->assertStringContainsString('search=Surveyor&amp;status=active&amp;page=3', $html);
        $this->assertStringNotContainsString('Showing', $html);
        $this->assertStringNotContainsString('<svg', $html);
        $this->assertStringNotContainsString('sm:hidden', $html);
    }

    public function test_first_and_last_page_disable_unavailable_navigation(): void
    {
        $first_page = new LengthAwarePaginator(range(1, 12), 13, 12, 1, ['path' => '/employee']);
        $first_html = $first_page->links('accounts.partials.pagination')->toHtml();
        $this->assertStringNotContainsString('rel="prev"', $first_html);
        $this->assertStringContainsString('rel="next"', $first_html);
        $this->assertStringContainsString('aria-disabled="true"', $first_html);

        $last_page = new LengthAwarePaginator([13], 13, 12, 2, ['path' => '/employee']);
        $last_html = $last_page->links('accounts.partials.pagination')->toHtml();
        $this->assertStringContainsString('Menampilkan 13 hingga 13 dari 13 data', $last_html);
        $this->assertStringContainsString('rel="prev"', $last_html);
        $this->assertStringNotContainsString('rel="next"', $last_html);
        $this->assertStringContainsString('aria-disabled="true"', $last_html);
    }

    public function test_empty_and_single_page_directories_do_not_render_pagination(): void
    {
        foreach ([[], [1]] as $items) {
            $paginator = new LengthAwarePaginator($items, count($items), 12, 1);
            $this->assertSame('', trim($paginator->links('accounts.partials.pagination')->toHtml()));
        }
    }
}
