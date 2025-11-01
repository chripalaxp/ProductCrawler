<?php

namespace MiniCrawler;

class Parser
{
    public function parseProduct(string $html): array
    {
        $dom = str_get_html($html);
        if (!$dom) {
            return ['title' => null, 'price' => null, 'availability' => null];
        }

        $title = null;
        $price = null;
        $availability = null;

        // Title
        $title_array = [
            'title',
            'h1',
        ];


        foreach ($title_array as $sel) {
            $el = $dom->find($sel, 0);
            $title = $el ? trim($el->plaintext) : $title;
            if ($title) {
                break;
            }
        }

        // Price
        $price_array = [
            '.price',
            '.product-price',
            '.price-current',
            '[itemprop="price"]',
            'meta[itemprop="price"]',
            'span[itemprop="price"]',
            'meta[property="product:price:amount"]',
            '[data-price]'
        ];

        foreach ($price_array as $sel) {
            $el = $dom->find($sel, 0);
            if ($el) {
                $content = (string)$el->getAttribute('content');
                $text = trim(preg_replace('/\s+/', ' ', $el->plaintext));
                $value = $content !== '' ? $content : $text;
                if ($value !== '') {
                    $price = $value;
                    break;
                }
            }
        }


        // Availability
        $availability_array = [
            '.availability',
            '.availability-label',
            '.stock-status',
            '.availability-label .label',
            '[itemprop=availability]',
            '.product-availability'
        ];
        
        foreach ($availability_array as $sel) {
            $el = $dom->find($sel, 0);
            if ($el) {
                $content = (string)$el->getAttribute('content');
                $text = trim($el->plaintext);
                $value = $content !== '' ? $content : $text;
                if ($value !== '') {
                    $availability = $value;
                    break;
                }
            }
        }

        $dom->clear();
        unset($dom);

        return [
            'title' => $title ?: null,
            'price' => $price ?: null,
            'availability' => $availability ?: null,
        ];
    }

}


