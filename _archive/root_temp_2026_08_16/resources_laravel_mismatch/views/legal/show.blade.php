@extends('layouts.base')

@section('title', $document->title)

@section('content')
<div class="container py-5">
    <div class="row">
        <div class="col-lg-9 mx-auto">
            <!-- Header -->
            <div class="text-center mb-5">
                <div class="inline-flex items-center gap-2 px-4 py-2 rounded-full bg-teal-100 text-teal-800 text-sm font-medium mb-3 capitalize">
                    {{ ucfirst($document->category) }} &bull; {{ ucfirst(str_replace('_', ' ', $document->document_type)) }}
                </div>
                <h1 class="display-4 fw-bold text-gray-900 mb-3">{{ $document->title }}</h1>
                <div class="flex flex-wrap justify-center gap-4 text-sm text-gray-500">
                    <span class="d-flex align-items-center gap-1">
                        <i class="fas fa-tag"></i> v{{ $document->version }}
                    </span>
                    @if($document->is_mandatory)
                    <span class="d-flex align-items-center gap-1 text-red-600">
                        <i class="fas fa-exclamation-circle"></i> Mandatory
                    </span>
                    @endif
                    @if($document->effective_from)
                    <span class="d-flex align-items-center gap-1">
                        <i class="fas fa-calendar"></i> Effective {{ $document->effective_from->format('M d, Y') }}
                    </span>
                    @endif
                    @if($document->published_at)
                    <span class="d-flex align-items-center gap-1">
                        <i class="fas fa-globe"></i> Published {{ $document->published_at->format('M d, Y') }}
                    </span>
                    @endif
                </div>
                
                @if($document->summary)
                <div class="mt-4 p-4 bg-teal-50 rounded-lg border border-teal-200">
                    <p class="text-teal-800 mb-0">{{ $document->summary }}</p>
                </div>
                @endif
            </div>

            <!-- Acceptance Button for Mandatory Docs -->
            @if($document->is_mandatory && auth()->check() && !$accepted)
            <div class="alert alert-warning border-0 shadow-sm mb-5" role="alert">
                <div class="d-flex align-items-center justify-content-between flex-wrap gap-3">
                    <div class="d-flex align-items-center gap-3">
                        <div class="bg-warning bg-opacity-10 rounded-circle p-3">
                            <i class="fas fa-file-contract text-warning fa-2x"></i>
                        </div>
                        <div>
                            <h5 class="mb-1 fw-bold">Action Required</h5>
                            <p class="mb-0 text-muted small">You must accept this {{ strtolower($document->document_type) }} to continue using our services.</p>
                        </div>
                    </div>
                    <form action="{{ route('legal.accept') }}" method="POST" class="ms-3">
                        @csrf
                        <input type="hidden" name="document_id" value="{{ $document->id }}">
                        <input type="hidden" name="redirect" value="{{ request()->url() }}">
                        <button type="submit" class="btn btn-warning fw-bold px-4 py-2">
                            <i class="fas fa-check me-2"></i> Accept & Continue
                        </button>
                    </form>
                </div>
            @endif

            <!-- Document Content -->
            <article class="legal-content prose prose-teal max-w-none bg-white rounded-lg shadow-sm border border-gray-200 p-6 p-md-8">
                {!! $document->content !!}
            </article>

            <!-- Document Meta -->
            <div class="mt-6 pt-6 border-t border-gray-200">
                <div class="row text-sm text-gray-500">
                    <div class="col-md-6">
                        <p class="mb-1"><strong>Category:</strong> {{ ucfirst($document->category) }}</p>
                        <p class="mb-1"><strong>Type:</strong> {{ ucfirst(str_replace('_', ' ', $document->document_type)) }}</p>
                        @if($document->effective_from)
                        <p class="mb-1"><strong>Effective From:</strong> {{ $document->effective_from->format('M d, Y') }}</p>
                        @endif
                    </div>
                    <div class="col-md-6 text-md-end">
                        <p class="mb-1"><strong>Version:</strong> v{{ $document->version }}</p>
                        @if($document->published_at)
                        <p class="mb-1"><strong>Published:</strong> {{ $document->published_at->format('M d, Y') }}</p>
                        @endif
                        @if($document->expires_at)
                        <p class="mb-1"><strong>Expires:</strong> {{ $document->expires_at->format('M d, Y') }}</p>
                        @endif
                    </div>
                </div>
            </div>

            <!-- Related Documents -->
            @if($related->count())
            <div class="mt-6">
                <h3 class="h5 fw-bold text-gray-900 mb-4">Related Documents</h3>
                <div class="row g-3">
                    @foreach($related as $relatedDoc)
                    <div class="col-md-6 col-lg-4">
                        <a href="{{ $relatedDoc->url }}" class="card h-100 border border-gray-200 hover:border-teal-300 hover:shadow-md transition-all">
                            <div class="card-body">
                                <div class="d-flex items-center gap-2 mb-2">
                                    <span class="badge bg-teal-100 text-teal-800 text-xs capitalize">{{ $relatedDoc->category }}</span>
                                    <span class="badge bg-gray-100 text-gray-700 text-xs">{{ ucfirst(str_replace('_', ' ', $relatedDoc->document_type)) }}</span>
                                </div>
                                <h5 class="card-title text-gray-900">{{ $relatedDoc->title }}</h5>
                                @if($relatedDoc->summary)
                                <p class="card-text text-muted small mb-0">{{ $relatedDoc->summary }}</p>
                                @endif
                            </div>
                        </a>
                    </div>
                    @endforeach
                </div>
            </div>
            @endif

            <!-- Back Link -->
            <div class="mt-6 text-center">
                <a href="{{ url()->previous() }}" class="btn btn-outline-teal">
                    <i class="fas fa-arrow-left me-2"></i> Back
                </a>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    // Smooth scroll for anchor links
    document.querySelectorAll('a[href^="#"]').forEach(anchor => {
        anchor.addEventListener('click', function(e) {
            e.preventDefault();
            const target = document.querySelector(this.getAttribute('href'));
            if (target) {
                target.scrollIntoView({ behavior: 'smooth', block: 'start' });
            }
        });
    });
});
</script>
@endpush