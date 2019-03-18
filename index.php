<?php

/**
 * Kirby3 Highlight - Themeable server-side syntax highlighting for Kirby v3
 *
 * @package   Kirby CMS
 * @author    S1SYPHOS <hello@twobrain.io>
 * @link      http://twobrain.io
 * @version   1.0.0
 * @license   MIT
 */

use Highlight\Highlighter;

@include_once __DIR__ . '/vendor/autoload.php';

Kirby::plugin('s1syphos/highlight', [
    'options' => [
        'class' => 'hljs',
        'languages' => ['html', 'php'],
        'escaping' => false
    ],
    'hooks' => [
        'kirbytext:after' => function ($text) {
            /*
             * I. Adding `hljs` class to all `pre` elements
             */

            // Converting kirbytext to an HTML document
            // See https://secure.php.net/manual/en/class.domdocument.php
            $html = new DOMDocument();
            $html->loadHTML($text, LIBXML_HTML_NOIMPLIED | LIBXML_HTML_NODEFDTD);

            // Retrieving all `pre` elements inside our newly created HTML document
            // See https://secure.php.net/manual/en/class.domxpath.php & https://en.wikipedia.org/wiki/XPath
            $query = new DOMXPath($html);
            $elements = $query->evaluate('//pre');

            // Looping through all `pre` elements, adding the class name
            foreach ($elements as $element) {
                $element->setAttribute('class', option('s1syphos.highlight.class'));
            }

            // Saving all changes
            $text = $html->saveHTML();


            /*
             * II. Highlighting everything between <code> and </code>
             */

            // Pattern to be matched when parsing kirbytext()
            $pattern = '~<code[^>]*>\K.*(?=</code>)~Uis';

            // Applying syntax highlighting by
            return preg_replace_callback($pattern, function ($match) {
                // Instantiating Highlighter & passing array of languages to be auto-detected
                $highlighter = new Highlighter();
                $highlighter->setAutodetectLanguages(option('s1syphos.highlight.languages'));
                // Optionally escaping each match ..
                $input = option('s1syphos.highlight.escaping') ? $match[0] : htmlspecialchars_decode($match[0]);
                // .. but always highlighting & outputting it
                $highlightedMatch = $highlighter->highlightAuto($input);
                $highlightedMatch = $highlightedMatch->value;
                return $highlightedMatch;
            }, $text);
        }
    ]
]);
