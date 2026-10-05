<?php

namespace App\Cms;

/**
 * Registry of page-section types. Adding a type = one entry here + a public
 * partial at resources/views/cms/sections/{type}.blade.php. The admin form is
 * generated from the field definitions.
 *
 * Field types: text, textarea, code, url, image, events, products, repeater.
 */
class SectionTypes
{
    public static function all(): array
    {
        return [
            'html' => [
                'label' => 'Raw HTML / CSS / JS',
                'icon' => 'mdi-code-tags',
                'fields' => [
                    'html' => ['type' => 'code', 'label' => 'HTML (rendered as-is: style and script tags allowed)'],
                ],
            ],
            'hero' => [
                'label' => 'Hero',
                'icon' => 'mdi-image-area',
                'fields' => [
                    'heading' => ['type' => 'text', 'label' => 'Heading'],
                    'subheading' => ['type' => 'textarea', 'label' => 'Sub-heading'],
                    'image' => ['type' => 'image', 'label' => 'Background image'],
                    'button_label' => ['type' => 'text', 'label' => 'Button label'],
                    'button_url' => ['type' => 'url', 'label' => 'Button URL'],
                ],
            ],
            'text' => [
                'label' => 'Text block',
                'icon' => 'mdi-format-text',
                'fields' => [
                    'heading' => ['type' => 'text', 'label' => 'Heading'],
                    'body' => ['type' => 'code', 'label' => 'Body (HTML allowed)'],
                ],
            ],
            'image' => [
                'label' => 'Image',
                'icon' => 'mdi-image',
                'fields' => [
                    'image' => ['type' => 'image', 'label' => 'Image'],
                    'alt' => ['type' => 'text', 'label' => 'Alt text'],
                    'link' => ['type' => 'url', 'label' => 'Link (optional)'],
                ],
            ],
            'events' => [
                'label' => 'Events list',
                'icon' => 'mdi-calendar-star',
                'fields' => [
                    'heading' => ['type' => 'text', 'label' => 'Heading'],
                    'event_ids' => ['type' => 'events', 'label' => 'Events to show (none selected = all upcoming events of this country)'],
                ],
            ],
            'products' => [
                'label' => 'Products grid (store)',
                'icon' => 'mdi-shopping',
                'fields' => [
                    'heading' => ['type' => 'text', 'label' => 'Heading'],
                    'product_ids' => ['type' => 'products', 'label' => 'Products to show (none selected = featured first, then newest; only products priced for this country appear)'],
                    'limit' => ['type' => 'text', 'label' => 'Max products when none are picked (default 8)'],
                ],
            ],
            'product_categories' => [
                'label' => 'Product categories (store)',
                'icon' => 'mdi-shape-outline',
                'fields' => [
                    'heading' => ['type' => 'text', 'label' => 'Heading'],
                ],
            ],
            'faq' => [
                'label' => 'FAQ',
                'icon' => 'mdi-help-circle-outline',
                'fields' => [
                    'heading' => ['type' => 'text', 'label' => 'Heading'],
                    'items' => ['type' => 'repeater', 'label' => 'Questions', 'fields' => [
                        'question' => ['type' => 'text', 'label' => 'Question'],
                        'answer' => ['type' => 'textarea', 'label' => 'Answer'],
                    ]],
                ],
            ],
            'testimonials' => [
                'label' => 'Testimonials',
                'icon' => 'mdi-comment-quote-outline',
                'fields' => [
                    'heading' => ['type' => 'text', 'label' => 'Heading'],
                    'items' => ['type' => 'repeater', 'label' => 'Testimonials', 'fields' => [
                        'quote' => ['type' => 'textarea', 'label' => 'Quote'],
                        'author' => ['type' => 'text', 'label' => 'Author'],
                    ]],
                ],
            ],
            'cta' => [
                'label' => 'Call to action',
                'icon' => 'mdi-bullhorn-outline',
                'fields' => [
                    'heading' => ['type' => 'text', 'label' => 'Heading'],
                    'text' => ['type' => 'textarea', 'label' => 'Text'],
                    'button_label' => ['type' => 'text', 'label' => 'Button label'],
                    'button_url' => ['type' => 'url', 'label' => 'Button URL'],
                ],
            ],
        ];
    }

    public static function get(string $type): ?array
    {
        return static::all()[$type] ?? null;
    }

    public static function keys(): array
    {
        return array_keys(static::all());
    }

    /**
     * Keep only the fields the type defines (repeaters get reindexed, empty
     * rows dropped). Values are stored raw: admin-only content, no sanitising.
     */
    public static function clean(string $type, array $input): array
    {
        $def = static::get($type);
        $out = [];
        foreach ($def['fields'] ?? [] as $name => $field) {
            $value = $input[$name] ?? null;
            if ($field['type'] === 'repeater') {
                $rows = [];
                foreach ((array) $value as $row) {
                    if (! is_array($row) || ! array_filter($row, fn ($v) => trim((string) $v) !== '')) {
                        continue;
                    }
                    $rows[] = array_intersect_key($row, $field['fields']);
                }
                $out[$name] = $rows;
            } elseif (in_array($field['type'], ['events', 'products'], true)) {
                $out[$name] = array_values(array_map('intval', (array) $value));
            } else {
                $out[$name] = is_scalar($value) ? (string) $value : '';
            }
        }

        return $out;
    }
}
