<div class="col-md-6 col-lg-4">
    <a href="{{ $document->url }}" class="card h-100 border border-gray-200 hover:border-teal-300 hover:shadow-lg transition-all duration-200 position-relative">
        @if($document->is_mandatory)
        <span class="position-absolute top-0 end-0 m-2 badge bg-red-500 text-white">
            <i class="fas fa-exclamation-circle me-1"></i> Mandatory
        </span>
        @endif
        <div class="card-body d-flex flex-column">
            <div class="d-flex items-center gap-2 mb-2">
                <span class="badge bg-teal-100 text-teal-800 text-xs capitalize">{{ $document->category }}</span>
                <span class="badge bg-gray-100 text-gray-700 text-xs">{{ ucfirst(str_replace('_', ' ', $document->document_type)) }}</span>
            </div>
            <h5 class="card-title text-gray-900 mb-2">{{ $document->title }}</h5>
            @if($document->summary)
            <p class="card-text text-muted small flex-grow-1">{{ $document->summary }}</p>
            @endif
            <div class="d-flex items-center gap-3 mt-3 pt-3 border-top border-gray-100">
                <span class="badge bg-teal-100 text-teal-800 text-xs">v{{ $document->version }}</span>
                @if($document->is_mandatory)
                <span class="badge bg-red-100 text-red-700 text-xs">
                    <i class="fas fa-exclamation-circle me-1"></i> Mandatory
                </span>
                @endif
            </div>
        </div>
        <div class="card-footer bg-transparent border-top border-gray-100">
            <div class="d-flex items-center justify-between">
                <small class="text-muted">
                    <i class="fas fa-calendar me-1"></i> 
                    {{ $document->published_at ? $document->published_at->format('M d, Y') : 'Draft' }}
                </small>
                <span class="btn btn-teal btn-sm fw-medium">
                    Read <i class="fas fa-arrow-right ms-1"></i>
                </span>
            </div>
        </div>
    </a>
</div>