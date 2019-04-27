<?php
namespace App\Media;

use Parsedown;

/**
 * Class MarkdownParser
 */
class MarkdownParser extends Parsedown
{
    /**
     * {@inheritDoc}
     */
    protected function inlineImage($Excerpt)
    {
        return;
    }

    /**
     * {@inheritDoc}
     */
    protected function inlineLink($Excerpt)
    {
        return;
    }
}
