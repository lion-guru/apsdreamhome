@extends('layouts.base')

@section('title', 'Legal Documents - APS Dream Home')

@section('content')
<div class="container py-5">
    <div class="row">
        <div class="col-lg-10 mx-auto">
            <!-- Header -->
            <div class="text-center mb-6">
                <h1 class="display-4 fw-bold text-gray-900 mb-3">Legal Documents</h1>
                <p class="lead text-muted">Browse our legal documents, policies, and terms</p>
            </div>

            <!-- Category Tabs -->
            @if(count($documents) > 0)
            <ul class="nav nav-tabs nav-fill mb-5 border-bottom border-gray-300" role="tablist">
                <li class="nav-item" role="presentation">
                    <button class="nav-link {{ !$category ? 'active' : '' }}" 
                            data-bs-toggle="tab" data-bs-target="#all" type="button">
                        All Documents
                        <span class="badge bg-gray-200 text-gray-700 ms-2">{{ $documents->count() }}</span>
                    </button>
                </li>
                @foreach($categories as $key => $label)
                    @if($documents->has($key))
                    <li class="nav-item" role="presentation">
                        <button class="nav-link {{ $category === $key ? 'active' : '' }}" 
                                data-bs-toggle="tab" data-bs-target="#{{ $key }}" type="button">
                            {{ $label }}
                            <span class="badge bg-gray-200 text-gray-700 ms-2">{{ $documents[$key]->count() }}</span>
                        </button>
                    </li>
                    @endif
                @endforeach
            </ul>

            <!-- Tab Content -->
            <div class="tab-content">
                <div class="tab-pane fade {{ !$category ? 'show active' : '' }}" id="all" role="tabpanel">
                    @if($documents->count() > 0)
                    <div class="row g-4">
                        @foreach($documents as $cat => $docs)
                            @foreach($docs as $doc)
                                @include('legal.partials.document-card', ['document' => $doc])
                            @endforeach
                        @endforeach
                    </div>
                    @else
                        <div class="text-center py-12">
                            <i class="fas fa-file-contract fa-4x text-gray-300 mb-4"></i>
                            <h4 class="text-gray-600">No documents found</h4>
                        </div>
                    @endif
                </div>

                @foreach($categories as $key => $label)
                    @if($documents->has($key))
                    <div class="tab-pane fade {{ $category === $key ? 'show active' : '' }}" id="{{ $key }}" role="tabpanel">
                        <div class="row g-4">
                            @foreach($documents[$key] as $doc)
                                @include('legal.partials.document-card', ['document' => $doc])
                            @endforeach
                        </div>
                    </div>
                    @endif
                @endforeach
            </div>

            <!-- Quick Links -->
            <div class="mt-6 p-4 bg-teal-50 rounded-lg border border-teal-200">
                <h5 class="fw-bold text-teal-800 mb-3">
                    <i class="fas fa-lightbulb me-2"></i> Need Help?
                </h5>
                <p class="text-teal-700 mb-3">Can't find what you're looking for? Our legal team is here to help.</p>
                <div class="d-flex flex-wrap gap-3">
                    <a href="{{ url('/contact') }}" class="btn btn-teal">
                        <i class="fas fa-envelope me-2"></i> Contact Legal Team
                    </a>
                    <a href="{{ url('/faq') }}" class="btn btn-outline-teal">
                        <i class="fas fa-question-circle me-2"></i> View FAQs
                    </a>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection