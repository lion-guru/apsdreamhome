<div class="grid grid-cols-12 gap-6">
    <div class="col-span-12 lg:col-span-8">
        <div class="card">
            <div class="card-header">
                <h3 class="text-lg font-semibold">Document Details</h3>
            </div>
            <div class="card-body">
                <div class="space-y-4">
                    <div class="flex items-center justify-between py-2 border-b">
                        <span class="text-sm font-medium text-gray-600">Document ID:</span>
                        <span class="font-mono">{{ $document['id'] }}</span>
                    </div>

                    <div class="flex items-center justify-between py-2 border-b">
                        <span class="text-sm font-medium text-gray-600">Document Type:</span>
                        <span class="badge badge-info">{{ $document['document_type'] }}</span>
                    </div>

                    <div class="flex items-center justify-between py-2 border-b">
                        <span class="text-sm font-medium text-gray-600">Title:</span>
                        <span class="font-medium">{{ $document['title'] }}</span>
                    </div>

                    <div class="flex items-center justify-between py-2 border-b">
                        <span class="text-sm font-medium text-gray-600">Status:</span>
                        @if($document['status'] === 'pending')
                        <span class="badge badge-warning">Pending</span>
                        @elseif($document['status'] === 'signed')
                        <span class="badge badge-success">Signed</span>
                        @elseif($document['status'] === 'cancelled')
                        <span class="badge badge-error">Cancelled</span>
                        @endif
                    </div>

                    <div class="flex items-center justify-between py-2 border-b">
                        <span class="text-sm font-medium text-gray-600">Created By:</span>
                        <span>{{ $document['created_by_name'] ?? 'N/A' }}</span>
                    </div>

                    <div class="flex items-center justify-between py-2 border-b">
                        <span class="text-sm font-medium text-gray-600">Created At:</span>
                        <span>{{ $document['created_at'] }}</span>
                    </div>

                    @if($document['signed_at'])
                    <div class="flex items-center justify-between py-2 border-b">
                        <span class="text-sm font-medium text-gray-600">Signed At:</span>
                        <span>{{ $document['signed_at'] }}</span>
                    </div>
                    @endif

                    @if($document['signed_by'])
                    <div class="flex items-center justify-between py-2 border-b">
                        <span class="text-sm font-medium text-gray-600">Signed By:</span>
                        <span>{{ $document['signed_by_name'] ?? 'N/A' }}</span>
                    </div>
                    @endif
                </div>

                @if($document['status'] === 'pending')
                <div class="mt-6 p-4 bg-yellow-50 border border-yellow-200 rounded-lg">
                    <h4 class="font-medium text-yellow-800 mb-2">Pending Actions</h4>
                    <p class="text-sm text-yellow-700">This document requires a digital signature to proceed.</p>
                    <button onclick="signDocument({{ $document['id'] }})" class="mt-2 btn btn-success">
                        <i class="fas fa-signature"></i> Sign Document
                    </button>
                </div>
                @endif

                @if($document['status'] === 'signed')
                <div class="mt-6 p-4 bg-green-50 border border-green-200 rounded-lg">
                    <h4 class="font-medium text-green-800 mb-2">Document Signed</h4>
                    <p class="text-sm text-green-700">This document has been digitally signed and verified.</p>
                    <div class="mt-2 p-2 bg-green-100 rounded text-xs font-mono">
                        Verification Code: {{ $document['verification_code'] }}
                    </div>
                </div>
                @endif
            </div>
        </div>
    </div>

    <div class="col-span-12 lg:col-span-4">
        <div class="card">
            <div class="card-header">
                <h3 class="text-lg font-semibold">Document Content</h3>
            </div>
            <div class="card-body">
                <div class="prose max-w-none">
                    {!! nl2br(e($document['content'])) !!}
                </div>
            </div>
        </div>

        @if($document['signature_data'])
        <div class="card mt-4">
            <div class="card-header">
                <h3 class="text-lg font-semibold">Signature Preview</h3>
            </div>
            <div class="card-body">
                <div class="bg-gray-50 p-4 rounded border">
                    <img src="data:image/png;base64,{{ $document['signature_data'] }}" alt="Signature" class="max-w-full h-auto" />
                </div>
            </div>
        </div>
        @endif
    </div>
</div>
