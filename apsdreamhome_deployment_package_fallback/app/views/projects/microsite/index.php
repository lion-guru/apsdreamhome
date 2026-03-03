<?php
/**
 * Project Microsite Layout
 */

/** @var \App\DTO\ProjectMicrositeDTO $microsite */

$template = template('default');
$template->setTitle($microsite->meta['title'] ?? 'Project Microsite')
    ->addCSS('/public/assets/projects/microsite.css')
    ->addJS('/public/assets/projects/microsite.js', true, false)
    ->outputHeader();

$project = $microsite->project;
$location = $microsite->location;
$developer = $microsite->developer;
$media = $microsite->media;
$highlights = $microsite->highlights;
$cta = $microsite->cta;
$meta = $microsite->meta;
$related = $microsite->related;
$theme = $microsite->theme;

include __DIR__ . '/partials/hero.php';
include __DIR__ . '/partials/highlights.php';
include __DIR__ . '/partials/media.php';
include __DIR__ . '/partials/location.php';
include __DIR__ . '/partials/contact.php';
include __DIR__ . '/partials/related-projects.php';

$template->outputFooter();
