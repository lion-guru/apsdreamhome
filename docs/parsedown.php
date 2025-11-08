<?php
class Parsedown
{
    public function text($text)
    {
        // Convert headers
        $text = preg_replace('/^# (.*$)/m', '<h1>$1</h1>', $text);
        $text = preg_replace('/^## (.*$)/m', '<h2>$1</h2>', $text);
        $text = preg_replace('/^### (.*$)/m', '<h3>$1</h3>', $text);
        
        // Convert bold and italic
        $text = preg_replace('/\*\*(.*?)\*\*/s', '<strong>$1</strong>', $text);
        $text = preg_replace('/\*(.*?)\*/s', '<em>$1</em>', $text);
        
        // Convert links
        $text = preg_replace('/\[([^\]]+)\]\(([^)]+)\)/', '<a href="$2">$1</a>', $text);
        
        // Convert lists
        $text = preg_replace('/^\* (.*$)/m', '<li>$1</li>', $text);
        $text = preg_replace('/(<li>.*<\/li>)/s', '<ul>$1</ul>', $text);
        
        // Convert paragraphs
        $text = '<p>' . preg_replace('/\n\n/', '</p><p>', $text) . '</p>';
        $text = str_replace("\n", "<br>\n", $text);
        
        return $text;
    }
}
