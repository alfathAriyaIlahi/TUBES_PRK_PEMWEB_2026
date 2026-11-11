<?php

// KOMPONEN KARTU BERITA (KATALOG) - ORANG 2
function article_card(array $article): array
{
    return [
        'href' => e(url('detail.html?slug=' . rawurlencode($article['slug']))),
        'image' => e(article_image($article['featured_image'] ?? null)),
        'faculty' => e(faculty_badge($article['faculty_name'] ?? null)),
        'title' => e($article['title']),
        'date' => e(date_id($article['published_at'] ?? $article['created_at'] ?? null)),
        'category' => e($article['category_name'] ?? 'Berita'),
        'summary' => e(excerpt($article['excerpt'] ?: $article['content'], 115)),
        'author' => e($article['author_name'] ?? 'Redaksi'),
    ];
}
