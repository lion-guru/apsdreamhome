<?php

namespace App\Http\Controllers\Property;

use App\DTO\ProjectMicrositeDTO;
use App\Http\Controllers\BaseController;
use App\Services\MicrositeAssembler;

class ProjectMicrositeController extends BaseController
{
    protected MicrositeAssembler $assembler;

    public function __construct(?MicrositeAssembler $assembler = null)
    {
        parent::__construct();
        $this->assembler = $assembler ?? new MicrositeAssembler();
    }

    /**
     * Show project microsite
     */
    public function show(?string $projectCode = null): string
    {
        if (empty($projectCode)) {
            $this->redirect(BASE_URL . 'projects');
            return '';
        }

        $microsite = $this->assembler->assembleByCode($projectCode);

        if (!$microsite instanceof ProjectMicrositeDTO) {
            return $this->render('errors/404', [
                'message' => 'Project microsite not found'
            ]);
        }

        return $this->render('projects/microsite/index', [
            'microsite' => $microsite,
            'page_title' => $microsite->meta['title'] ?? ($microsite->project['name'] ?? 'Project Microsite'),
        ]);
    }
}
