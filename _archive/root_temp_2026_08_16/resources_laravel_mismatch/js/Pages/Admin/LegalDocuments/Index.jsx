import React, { useState, useEffect } from 'react';
import { Head } from '@inertiajs/react';
import { Link } from '@inertiajs/react';
import { useForm, usePage } from '@inertiajs/react';
import { ChevronDownIcon, ChevronUpIcon, MagnifyingGlassIcon, FunnelIcon, DocumentTextIcon, EyeIcon, PencilIcon, TrashIcon, ArrowPathIcon, DocumentDuplicateIcon, CheckCircleIcon, XCircleIcon, ExclamationTriangleIcon, DocumentArrowDownIcon } from '@heroicons/react/24/outline';
import { CheckCircleIcon as CheckCircleIconSolid, XCircleIcon as XCircleIconSolid } from '@heroicons/react/24/solid';
import { Dialog, Transition } from '@headlessui/react';

export default function Index({ documents, stats, filters, categories, documentTypes, statuses }) {
    const [searchQuery, setSearchQuery] = useState(filters.search || '');
    const [selectedCategory, setSelectedCategory] = useState(filters.category || '');
    const [selectedType, setSelectedType] = useState(filters.document_type || '');
    const [selectedStatus, setSelectedStatus] = useState(filters.status || '');
    const [showFilters, setShowFilters] = useState(false);
    const [selectedIds, setSelectedIds] = useState([]);
    const [selectAll, setSelectAll] = useState(false);
    const [bulkAction, setBulkAction] = useState('');
    const [showBulkConfirm, setShowBulkConfirm] = useState(false);
    const [sortColumn, setSortColumn] = useState('created_at');
    const [sortDirection, setSortDirection] = useState('desc');

    const { data, setData, post, processing } = useForm({
        action: '',
        ids: [],
    });

    const handleSort = (column) => {
        if (sortColumn === column) {
            setSortDirection(sortDirection === 'asc' ? 'desc' : 'asc');
        } else {
            setSortColumn(column);
            setSortDirection('asc');
        }
    };

    const toggleSelectAll = () => {
        if (selectAll) {
            setSelectedIds([]);
        } else {
            setSelectedIds(documents.data.map(d => d.id));
        }
        setSelectAll(!selectAll);
    };

    const toggleSelect = (id) => {
        setSelectedIds(prev => prev.includes(id)
            ? prev.filter(i => i !== id)
            : [...prev, id]
        );
    };

    const handleBulkAction = (action) => {
        if (selectedIds.length === 0) {
            alert('Please select at least one document');
            return;
        }
        setBulkAction(action);
        setShowBulkConfirm(true);
    };

    const confirmBulkAction = () => {
        data.value.action = bulkAction;
        data.value.ids = selectedIds;
        post(route('admin.legal-documents.bulk-action'), {
            onSuccess: () => {
                setSelectedIds([]);
                setSelectAll(false);
                setShowBulkConfirm(false);
            }
        });
    };

    const formatDate = (date) => {
        if (!date) return '-';
        return new Date(date).toLocaleDateString('en-IN', {
            year: 'numeric',
            month: 'short',
            day: 'numeric',
            hour: '2-digit',
            minute: '2-digit',
        });
    };

    const getStatusBadge = (status) => {
        const badges = {
            published: 'bg-green-100 text-green-800',
            draft: 'bg-yellow-100 text-yellow-800',
            archived: 'bg-gray-100 text-gray-800',
        };
        return badges[status] || 'bg-gray-100 text-gray-800';
    };

    const getCategoryBadge = (category) => {
        const badges = {
            company: 'bg-blue-100 text-blue-800',
            associate: 'bg-orange-100 text-orange-800',
            agent: 'bg-blue-100 text-blue-800',
            booking: 'bg-purple-100 text-purple-800',
            general: 'bg-gray-100 text-gray-800',
        };
        return badges[category] || 'bg-gray-100 text-gray-800';
    };

    return (
        <>
            <Head>
                <title>Legal Documents - Admin</title>
            </Head>

            <div className="p-6 space-y-6">
                {/* Header */}
                <div className="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
                    <div>
                        <h1 className="text-2xl font-bold text-gray-900">Legal Documents</h1>
                        <p className="text-gray-600 mt-1">Manage terms, policies, and legal documents</p>
                    </div>
                    <Link
                        href={route('admin.legal-documents.create')}
                        className="inline-flex items-center px-4 py-2 bg-teal-600 text-white rounded-lg hover:bg-teal-700 transition-colors"
                    >
                        <DocumentTextIcon className="w-5 h-5 mr-2" />
                        Create Document
                    </Link>
                </div>

                {/* Stats Cards */}
                <div className="grid grid-cols-2 md:grid-cols-6 gap-4">
                    <StatCard title="Total" value={stats.total} icon={<DocumentTextIcon />} color="blue" />
                    <StatCard title="Published" value={stats.published} icon={<CheckCircleIconSolid />} color="green" />
                    <StatCard title="Drafts" value={stats.draft} icon={<DocumentTextIcon />} color="yellow" />
                    <StatCard title="Archived" value={stats.archived} icon={<XCircleIconSolid />} color="gray" />
                    <StatCard title="Mandatory" value={stats.mandatory} icon={<ExclamationTriangleIcon />} color="red" />
                    <StatCard title="Acceptances" value={stats.total_acceptances} icon={<CheckCircleIconSolid />} color="teal" />
                </div>

                {/* Filters */}
                <div className="bg-white rounded-lg shadow-sm border border-gray-200">
                    <div className="px-4 py-4 border-b border-gray-200 flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
                        <div className="flex flex-wrap items-center gap-3">
                            <label className="text-sm font-medium text-gray-700">Filters:</label>
                            
                            <select
                                value={selectedCategory}
                                onChange={e => setSelectedCategory(e.target.value)}
                                className="px-3 py-2 text-sm border border-gray-300 rounded-lg focus:ring-2 focus:ring-teal-500 focus:border-teal-500"
                            >
                                <option value="">All Categories</option>
                                {categories.map(cat => (
                                    <option key={cat} value={cat}>{cat.charAt(0).toUpperCase() + cat.slice(1)}</option>
                                ))}
                            </select>

                            <select
                                value={selectedType}
                                onChange={e => setSelectedType(e.target.value)}
                                className="px-3 py-2 text-sm border border-gray-300 rounded-lg focus:ring-2 focus:ring-teal-500 focus:border-teal-500"
                            >
                                <option value="">All Types</option>
                                {documentTypes.map(dt => (
                                    <option key={dt} value={dt}>{dt.replace('_', ' ').replace(/\b\w/g, l => l.toUpperCase())}</option>
                                ))}
                            </select>

                            <select
                                value={selectedStatus}
                                onChange={e => setSelectedStatus(e.target.value)}
                                className="px-3 py-2 text-sm border border-gray-300 rounded-lg focus:ring-2 focus:ring-teal-500 focus:border-teal-500"
                            >
                                <option value="">All Statuses</option>
                                {statuses.map(st => (
                                    <option key={st} value={st}>{st.charAt(0).toUpperCase() + st.slice(1)}</option>
                                ))}
                            </select>
                        </div>

                        <div className="flex items-center gap-3">
                            <div className="relative">
                                <MagnifyingGlassIcon className="absolute left-3 top-1/2 -translate-y-1/2 w-5 h-5 text-gray-400" />
                                <input
                                    type="text"
                                    placeholder="Search documents..."
                                    value={searchQuery}
                                    onChange={e => setSearchQuery(e.target.value)}
                                    className="pl-10 pr-4 py-2 text-sm border border-gray-300 rounded-lg focus:ring-2 focus:ring-teal-500 focus:border-teal-500 w-64"
                                />
                            </div>
                            <button
                                onClick={() => setShowFilters(!showFilters)}
                                className="px-4 py-2 text-sm text-gray-700 bg-white border border-gray-300 rounded-lg hover:bg-gray-50 transition-colors flex items-center gap-2"
                            >
                                <FunnelIcon className="w-5 h-5" />
                                Filters
                            </button>
                        </div>
                    </div>
                </div>

                {/* Bulk Actions Bar */}
                {selectedIds.length > 0 && (
                    <div className="bg-teal-50 border border-teal-200 rounded-lg p-4 flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
                        <div className="flex items-center gap-4">
                            <span className="text-sm font-medium text-teal-800">
                                {selectedIds.length} selected
                            </span>
                            <select
                                value={bulkAction}
                                onChange={e => setBulkAction(e.target.value)}
                                className="px-3 py-1.5 text-sm border border-teal-300 rounded-lg bg-white focus:ring-2 focus:ring-teal-500"
                            >
                                <option value="">Bulk Action</option>
                                <option value="publish">Publish</option>
                                <option value="archive">Archive</option>
                                <option value="delete">Move to Trash</option>
                                <option value="force_delete">Delete Permanently</option>
                            </select>
                            <button
                                onClick={handleBulkAction}
                                disabled={!bulkAction || processing}
                                className="px-4 py-1.5 text-sm bg-teal-600 text-white rounded-lg hover:bg-teal-700 transition-colors disabled:opacity-50"
                            >
                                Apply
                            </button>
                        </div>
                        <button
                            onClick={() => { setSelectedIds([]); setSelectAll(false); }}
                            className="text-sm text-teal-700 hover:text-teal-900"
                        >
                            Clear Selection
                        </button>
                    </div>
                )}

                {/* Documents Table */}
                <div className="bg-white rounded-lg shadow-sm border border-gray-200 overflow-hidden">
                    <div className="overflow-x-auto">
                        <table className="min-w-full divide-y divide-gray-200">
                            <thead className="bg-gray-50">
                                <tr>
                                    <th className="px-4 py-3 text-left">
                                        <input
                                            type="checkbox"
                                            checked={selectAll && documents.data.length > 0}
                                            onChange={toggleSelectAll}
                                            className="h-4 w-4 text-teal-600 border-gray-300 rounded focus:ring-teal-500"
                                            aria-label="Select all"
                                        />
                                    </th>
                                    <th 
                                        className="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider cursor-pointer hover:text-gray-700"
                                        onClick={() => handleSort('title')}
                                    >
                                        <div className="flex items-center gap-1">
                                            Document
                                            {sortColumn === 'title' && (
                                                sortDirection === 'asc' ? <ChevronUpIcon className="w-4 h-4" /> : <ChevronDownIcon className="w-4 h-4" />
                                            )}
                                        </div>
                                    </th>
                                    <th className="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Category / Type</th>
                                    <th 
                                        className="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider cursor-pointer hover:text-gray-700"
                                        onClick={() => handleSort('status')}
                                    >
                                        <div className="flex items-center gap-1">
                                            Status
                                            {sortColumn === 'status' && (
                                                sortDirection === 'asc' ? <ChevronUpIcon className="w-4 h-4" /> : <ChevronDownIcon className="w-4 h-4" />
                                            )}
                                        </div>
                                    </th>
                                    <th className="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Mandatory</th>
                                    <th className="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Acceptances</th>
                                    <th 
                                        className="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider cursor-pointer hover:text-gray-700"
                                        onClick={() => handleSort('created_at')}
                                    >
                                        <div className="flex items-center gap-1">
                                            Created
                                            {sortColumn === 'created_at' && (
                                                sortDirection === 'asc' ? <ChevronUpIcon className="w-4 h-4" /> : <ChevronDownIcon className="w-4 h-4" />
                                            )}
                                        </div>
                                    </th>
                                    <th className="px-4 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">Actions</th>
                                </tr>
                            </thead>
                            <tbody className="bg-white divide-y divide-gray-200">
                                {documents.data.length === 0 ? (
                                    <tr>
                                        <td colSpan={8} className="px-4 py-12 text-center text-gray-500">
                                            <DocumentTextIcon className="w-12 h-12 mx-auto text-gray-300 mb-3" />
                                            <p className="text-lg font-medium text-gray-500">No documents found</p>
                                            <p className="text-sm text-gray-400 mt-1">Create your first legal document</p>
                                        </td>
                                    </tr>
                                ) : (
                                    documents.data.map((document) => (
                                        <tr key={document.id} className="hover:bg-gray-50">
                                            <td className="px-4 py-4">
                                                <input
                                                    type="checkbox"
                                                    checked={selectedIds.includes(document.id)}
                                                    onChange={() => toggleSelect(document.id)}
                                                    className="h-4 w-4 text-teal-600 border-gray-300 rounded focus:ring-teal-500"
                                                />
                                            </td>
                                            <td className="px-4 py-4">
                                                <div className="font-medium text-gray-900">{document.title}</div>
                                                <div className="text-sm text-gray-500 truncate max-w-xs">{document.slug}</div>
                                                {document.is_mandatory && (
                                                    <span className="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-medium bg-red-100 text-red-800 mt-1">
                                                        Mandatory
                                                    </span>
                                                )}
                                            </td>
                                            <td className="px-4 py-4">
                                                <span className="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-medium capitalize {getCategoryBadge(document.category)}">
                                                    {document.category}
                                                </span>
                                                <div className="mt-1 text-xs text-gray-500 capitalize">
                                                    {document.document_type.replace('_', ' ')}
                                                </div>
                                            </td>
                                            <td className="px-4 py-4">
                                                <span className="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-medium {getStatusBadge(document.status)}">
                                                    {document.status.charAt(0).toUpperCase() + document.status.slice(1)}
                                                </span>
                                            </td>
                                            <td className="px-4 py-4">
                                                {document.is_mandatory ? (
                                                    <span className="inline-flex items-center text-red-600">
                                                        <XCircleIconSolid className="w-4 h-4 mr-1" /> Yes
                                                    </span>
                                                ) : (
                                                    <span className="inline-flex items-center text-green-600">
                                                        <CheckCircleIconSolid className="w-4 h-4 mr-1" /> No
                                                    </span>
                                                )}
                                            </td>
                                            <td className="px-4 py-4 text-sm text-gray-900">
                                                {document.acceptances_count || 0}
                                            </td>
                                            <td className="px-4 py-4 text-sm text-gray-500">
                                                {formatDate(document.created_at)}
                                            </td>
                                            <td className="px-4 py-4 text-right">
                                                <div className="flex items-center justify-end gap-2">
                                                    <Link
                                                        href={route('admin.legal-documents.show', document.id)}
                                                        className="p-2 text-gray-500 hover:text-teal-600 hover:bg-teal-50 rounded-lg transition-colors"
                                                        title="View"
                                                    >
                                                        <EyeIcon className="w-5 h-5" />
                                                    </Link>
                                                    <Link
                                                        href={route('admin.legal-documents.edit', document.id)}
                                                        className="p-2 text-gray-500 hover:text-teal-600 hover:bg-teal-50 rounded-lg transition-colors"
                                                        title="Edit"
                                                    >
                                                        <PencilIcon className="w-5 h-5" />
                                                    </Link>
                                                    <button
                                                        onClick={() => {
                                                            data.value.action = 'delete';
                                                            data.value.ids = [document.id];
                                                            post(route('admin.legal-documents.bulk-action'));
                                                        }}
                                                        className="p-2 text-gray-500 hover:text-red-600 hover:bg-red-50 rounded-lg transition-colors"
                                                        title="Delete"
                                                    >
                                                        <TrashIcon className="w-5 h-5" />
                                                    </button>
                                                </div>
                                            </td>
                                        </tr>
                                    ))
                                )}
                            </tbody>
                        </table>
                    </div>

                    {/* Pagination */}
                    {documents.last_page > 1 && (
                        <div className="px-4 py-4 border-t border-gray-200">
                            <div className="flex items-center justify-between">
                                <div className="text-sm text-gray-700">
                                    Showing <span className="font-medium">{documents.from}</span> to <span className="font-medium">{documents.to}</span> of <span className="font-medium">{documents.total}</span> results
                                </div>
                                <div className="flex gap-2">
                                    {documents.prev_page_url && (
                                        <a
                                            href={documents.prev_page_url}
                                            className="px-3 py-1.5 text-sm border border-gray-300 rounded-lg hover:bg-gray-50 transition-colors"
                                        >
                                            Previous
                                        </a>
                                    )}
                                    {documents.next_page_url && (
                                        <a
                                            href={documents.next_page_url}
                                            className="px-3 py-1.5 text-sm border border-gray-300 rounded-lg hover:bg-gray-50 transition-colors"
                                        >
                                            Next
                                        </a>
                                    )}
                                </div>
                            </div>
                        </div>
                    )}
                </div>

                {/* Bulk Confirm Modal */}
                <Transition appear show={showBulkConfirm} as={Fragment}>
                    <Dialog as="div" className="relative z-50" onClose={setShowBulkConfirm}>
                        <Transition.Child
                            as={Fragment}
                            enter="ease-out duration-300"
                            enterFrom="opacity-0"
                            enterTo="opacity-100"
                            leave="ease-in duration-200"
                            leaveFrom="opacity-100"
                            leaveTo="opacity-0"
                        >
                            <div className="fixed inset-0 bg-gray-900/25" />
                        </Transition.Child>

                        <div className="fixed inset-0 overflow-y-auto">
                            <div className="flex min-h-full items-center justify-center p-4 text-center">
                                <Transition.Child
                                    as={Fragment}
                                    enter="ease-out duration-300"
                                    enterFrom="opacity-0 scale-95"
                                    enterTo="opacity-100 scale-100"
                                    leave="ease-in duration-200"
                                    leaveFrom="opacity-100 scale-100"
                                    leaveTo="opacity-0 scale-95"
                                >
                                    <Dialog.Panel className="w-full max-w-md transform overflow-hidden rounded-2xl bg-white p-6 text-left align-middle shadow-xl transition-all">
                                        <Dialog.Title as="h3" className="text-lg font-semibold text-gray-900 mb-2">
                                            Confirm Bulk Action
                                        </Dialog.Title>
                                        <Dialog.Description className="text-gray-500 text-sm mb-6">
                                            Are you sure you want to <strong className="capitalize">{bulkAction}</strong> {selectedIds.length} selected document(s)? This action cannot be undone.
                                        </Dialog.Description>
                                        <div className="flex justify-end gap-3">
                                            <button
                                                onClick={() => setShowBulkConfirm(false)}
                                                className="px-4 py-2 text-sm text-gray-700 bg-white border border-gray-300 rounded-lg hover:bg-gray-50 transition-colors"
                                            >
                                                Cancel
                                            </button>
                                            <button
                                                onClick={confirmBulkAction}
                                                disabled={processing}
                                                className="px-4 py-2 text-sm text-white bg-red-600 rounded-lg hover:bg-red-700 transition-colors disabled:opacity-50"
                                            >
                                                {processing ? 'Processing...' : `Confirm ${bulkAction.charAt(0).toUpperCase() + bulkAction.slice(1)}`}
                                            </button>
                                        </div>
                                    </Dialog.Panel>
                                </Transition.Child>
                            </div>
                        </div>
                    </Dialog>
                </Transition>
            </div>
        </>
    );
}

function StatCard({ title, value, icon, color }) {
    const colors = {
        blue: 'bg-blue-50 text-blue-600',
        green: 'bg-green-50 text-green-600',
        yellow: 'bg-yellow-50 text-yellow-600',
        gray: 'bg-gray-50 text-gray-600',
        red: 'bg-red-50 text-red-600',
        teal: 'bg-teal-50 text-teal-600',
    };

    return (
        <div className={`bg-white rounded-lg shadow-sm border border-gray-200 p-4 ${colors[color] || colors.blue}`}>
            <div className="flex items-center justify-between">
                <div>
                    <p className="text-sm font-medium text-gray-500">{title}</p>
                    <p className="text-2xl font-bold mt-1">{value}</p>
                </div>
                <div className="p-3 rounded-full bg-white/50">
                    {icon}
                </div>
            </div>
        </div>
    );
}