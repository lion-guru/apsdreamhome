{!! theme()->render('admin/@parts/breadcrumbs', [
    'Home' => '/admin',
    'Document E-Sign' => '/admin/document-esign',
]) !!}

<div class="grid grid-cols-12 gap-6">
    <div class="col-span-12">
        <div class="card">
            <div class="card-header flex justify-between items-center">
                <h3 class="text-lg font-semibold">Document E-Sign Management</h3>
                <div class="flex gap-2">
                    <button onclick="openCreateModal()" class="btn btn-primary">
                        <i class="fas fa-plus"></i> Create Document
                    </button>
                </div>
            </div>

            <div class="card-body">
                <div class="overflow-x-auto">
                    <table class="table table-zebra w-full">
                        <thead>
                            <tr>
                                <th>ID</th>
                                <th>Document Type</th>
                                <th>Title</th>
                                <th>Status</th>
                                <th>Created By</th>
                                <th>Created At</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($documents as $doc)
                            <tr>
                                <td>#{{ $doc['id'] }}</td>
                                <td>
                                    <span class="badge badge-info">{{ $doc['document_type'] }}</span>
                                </td>
                                <td class="font-medium">{{ $doc['title'] }}</td>
                                <td>
                                    @if($doc['status'] === 'pending')
                                    <span class="badge badge-warning">Pending</span>
                                    @elseif($doc['status'] === 'signed')
                                    <span class="badge badge-success">Signed</span>
                                    @elseif($doc['status'] === 'cancelled')
                                    <span class="badge badge-error">Cancelled</span>
                                    @endif
                                </td>
                                <td>{{ $doc['created_by_name'] ?? 'N/A' }}</td>
                                <td>{{ $doc['created_at'] }}</td>
                                <td>
                                    <div class="flex gap-1">
                                        <button onclick="viewDocument({{ $doc['id'] }})" class="btn btn-sm btn-info" title="View">
                                            <i class="fas fa-eye"></i>
                                        </button>
                                        @if($doc['status'] === 'pending')
                                        <button onclick="signDocument({{ $doc['id'] }})" class="btn btn-sm btn-success" title="Sign">
                                            <i class="fas fa-signature"></i>
                                        </button>
                                        @endif
                                        @if($doc['status'] === 'pending')
                                        <button onclick="cancelDocument({{ $doc['id'] }})" class="btn btn-sm btn-error" title="Cancel">
                                            <i class="fas fa-times"></i>
                                        </button>
                                        @endif
                                    </div>
                                </td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>
