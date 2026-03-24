<?php

declare(strict_types=1);

namespace App\Support;

final class PresentationCatalog
{
    private const SECTION_MAP = [
        'construction' => [
            'title' => 'Строительство и благоустройство БГТУ им. В.Г. Шухова',
            'eyebrow' => 'Раздел презентации',
            'route' => '/stroitelstvo_i_blagoustroistvo/',
            'file' => 'page111186596.html',
            'hero' => 'tild3330-3534-4330-b630-313334626262__2024-08-02_bgtu-shuk.jpg',
            'lead' => 'Ознакомьтесь с материалами о развитии кампуса, благоустройстве территории и современных пространствах университета.',
        ],
        'education' => [
            'title' => 'Образовательная и научная деятельность',
            'eyebrow' => 'Раздел презентации',
            'route' => '/obrazovatelnaya_i_nauchnnaya_deyatelnost/',
            'file' => 'page111191956.html',
            'hero' => 'tild3330-3534-4330-b630-313334626262__2024-08-02_bgtu-shuk.jpg',
            'lead' => 'Ознакомьтесь с материалами об образовательных программах, научной деятельности и возможностях, которые университет открывает для студентов и исследователей.',
        ],
    ];

    public static function overviewCards(): array
    {
        return [
            [
                'badge' => 'Презентация',
                'title' => 'История создания и развития',
                'description' => 'Откройте обзор основных разделов презентации и выберите интересующее направление знакомства с университетом.',
                'route' => '/istoriya_sozdaniya/',
                'meta' => 'Перейти к обзору',
            ],
            [
                'badge' => 'Презентация',
                'title' => self::SECTION_MAP['construction']['title'],
                'description' => 'Познакомьтесь с объектами кампуса, благоустройством территории и развитием университетской среды.',
                'route' => self::SECTION_MAP['construction']['route'],
                'meta' => self::slideCountLabel('construction'),
            ],
            [
                'badge' => 'Презентация',
                'title' => self::SECTION_MAP['education']['title'],
                'description' => 'Узнайте больше об образовательных программах, научной работе и возможностях, которые университет предлагает студентам.',
                'route' => self::SECTION_MAP['education']['route'],
                'meta' => self::slideCountLabel('education'),
            ],
            [
                'badge' => 'Раздел сайта',
                'title' => 'Почётные доктора',
                'description' => 'Посмотрите сведения о почётных докторах университета и откройте подробные персональные карточки.',
                'route' => '/honors.php?type=doctor',
                'meta' => 'Перейти к разделу',
            ],
            [
                'badge' => 'Раздел сайта',
                'title' => 'Почётные профессора',
                'description' => 'Ознакомьтесь с почётными профессорами БГТУ им. В.Г. Шухова и их вкладом в развитие университета.',
                'route' => '/honors.php?type=professor',
                'meta' => 'Перейти к разделу',
            ],
        ];
    }

    public static function section(string $key): array
    {
        $config = self::SECTION_MAP[$key] ?? null;

        if ($config === null) {
            return [];
        }

        $slides = self::slides($key);

        return $config + [
            'slides' => $slides,
            'slide_count' => count($slides),
        ];
    }

    public static function slides(string $key): array
    {
        static $cache = [];

        if (isset($cache[$key])) {
            return $cache[$key];
        }

        if (function_exists('presentation_repository')) {
            $slides = presentation_repository()->publishedSlides($key);

            if ($slides !== []) {
                $cache[$key] = $slides;

                return $slides;
            }
        }

        $cache[$key] = self::exportSlides($key);

        return $cache[$key];
    }

    public static function sections(): array
    {
        return self::SECTION_MAP;
    }

    public static function defaultSlides(string $key): array
    {
        return self::exportSlides($key);
    }

    private static function exportSlides(string $key): array
    {
        static $exportCache = [];

        if (isset($exportCache[$key])) {
            return $exportCache[$key];
        }

        $file = self::SECTION_MAP[$key]['file'] ?? null;

        if ($file === null) {
            return [];
        }

        $html = file_get_contents(base_path('bstupresent/' . $file));

        if ($html === false) {
            return [];
        }

        preg_match_all('/data-slide-index="[^"]+".*?data-original="images\/([^"]+)"/su', $html, $matches);
        $slides = [];

        foreach ($matches[1] ?? [] as $index => $filename) {
            $slides[] = [
                'index' => $index + 1,
                'filename' => $filename,
                'url' => '/presentation_asset.php?file=' . rawurlencode($filename),
                'alt' => sprintf('%s. Слайд %d', self::SECTION_MAP[$key]['title'], $index + 1),
            ];
        }

        $exportCache[$key] = $slides;

        return $exportCache[$key];
    }

    public static function heroUrl(string $filename): string
    {
        return '/presentation_asset.php?file=' . rawurlencode($filename);
    }

    private static function slideCountLabel(string $key): string
    {
        $count = count(self::slides($key));

        return $count . ' слайдов';
    }
}
