import React, { useState, useEffect } from 'react';
import { Head } from '@inertiajs/react';
import { Link, useForm } from '@inertiajs/react';
import { ArrowLeftIcon, SaveIcon, EyeIcon, TrashIcon, ChevronDownIcon, ChevronUpIcon } from '@heroicons/react/24/outline';
import { DocumentTextIcon, ExclamationTriangleIcon } from '@heroicons/react/24/outline';
import { CheckCircleIcon, XCircleIcon } from '@heroicons/react/24/solid';
import { Dialog, Transition } from '@headlessui/react';

export default function Edit({ document, categories, documentTypes, roles }) {
    const { data, setData, put, processing, errors } = useForm({
        title: document.title,
        slug: document.slug,
        category: document.category,
        document_type: document.document_type,
        content: document.content,
        summary: document.summary,
        version: document.version,
        status: document.status,
        is_mandatory: document.is_mandatory,
        applies_to_roles: document.applies_to_roles || [],
        metadata: document.metadata || {},
        published_at: document.published_at ? new Date(document.published_at).toISOString().slice(0, 16) : '',
        effective_from: document.effective_from ? new Date(document.effective_from).toISOString().slice(0, 16) : '',
        expires_at: document.expires_at ? new Date(document.expires_at).toISOString().slice(0, 16) : '',
    });

    const [showPreview, setShowPreview] = useState(false);

    const handleSubmit = (e) => {
        e.preventDefault();
        put(route('admin.legal-documents.update', document.id), {
            onSuccess: () => {},
        });
    };

    const autoGenerateSlug = () => {
        if (!data.slug && data.title) {
            setData('slug', data.title
                .toLowerCase()
                .replace(/[^a-z0-9]+/g, '-')
                .replace(/(^-|-$)/g, ''));
        }
    };

    const formatDateTime = (date) => {
        if (!date) return '';
        return new Date(date).toISOString().slice(0, 16);
    };

    return (
        <>
            <Head>
                <title>Edit Legal Document - Admin</title>
            </Head>

            <div className="p-6 space-y-6">
                {/* Header */}
                <div className="flex items-center justify-between">
                    <div className="flex items-center gap-4">
                        <Link
                            href={route('admin.legal-documents.index')}
                            className="p-2 text-gray-500 hover:text-teal-600 hover:bg-teal-50 rounded-lg transition-colors"
                            title="Back to list"
                        >
                            <svg className="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path strokeLinecap="round" strokeLinejoin="round" strokeWidth={2} d="M15 19l-7-7 7-7" /></svg>
                        </Link>
                        <div>
                            <h1 className="text-2xl font-bold text-gray-900">Edit Legal Document</h1>
                            <p className="text-gray-600">Editing: {document.title}</p>
                        </div>
                    </div>
                    <div className="flex gap-3">
                        <Link
                            href={route('admin.legal-documents.index')}
                            className="px-4 py-2 text-sm text-gray-700 bg-white border border-gray-300 rounded-lg hover:bg-gray-50 transition-colors"
                        >
                            Cancel
                        </Link>
                        <Link
                            href={route('admin.legal-documents.show', document.id)}
                            className="px-4 py-2 text-sm text-gray-700 bg-white border border-gray-300 rounded-lg hover:bg-gray-50 transition-colors"
                        >
                            <svg className="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path strokeLinecap="round" strokeLinejoin="round" strokeWidth={2} d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" /><path strokeLinecap="round" strokeLinejoin="round" strokeWidth={2} d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" /></svg>
                        </Link>
                        <button
                            type="button"
                            onClick={() => {
                                if (confirm('Are you sure you want to delete this document?')) {
                                    document.getElementById('delete-form')?.submit();
                                }
                            }}
                            className="px-4 py-2 text-sm text-red-600 bg-white border border-red-300 rounded-lg hover:bg-red-50 transition-colors"
                        >
                            <svg className="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path strokeLinecap="round" strokeLinejoin="round" strokeWidth={2} d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" /></svg>
                        </Link>
                    </div>
                </div>

                {/* Main Form */}
                <form id="legal-form" onSubmit={(e) => { e.preventDefault(); put(route('admin.legal-documents.update', document.id)); }} className="space-y-6">
                    {/* Basic Info Section */}
                    <div className="bg-white rounded-lg shadow-sm border border-gray-200 p-6 space-y-6">
                        <h2 className="text-lg font-semibold text-gray-900">Basic Information</h2>
                        <div className="grid grid-cols-1 md:grid-cols-2 gap-6">
                            <div>
                                <label className="block text-sm font-medium text-gray-700 mb-1">Title <span className="text-red-500">*</span></label>
                                <input
                                    type="text"
                                    value={data.title}
                                    onChange={e => { setData('title', e.target.value); setData('slug', e.target.value.toLowerCase().replace(/[^a-z0-9]+/g, '-').replace(/(^-|-$)/g, '')); }}
                                    placeholder="e.g., Terms of Service"
                                    className="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-teal-500 focus:border-teal-500"
                                    required
                                />
                            </div>
                            <div>
                                <label className="block text-sm font-medium text-gray-700 mb-1">Slug <span className="text-red-500">*</span></label>
                                <div className="flex gap-2">
                                    <input
                                        type="text"
                                        value={data.slug}
                                        onChange={e => setData('slug', e.target.value)}
                                        placeholder="Auto-generated from title"
                                        className="flex-1 px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-teal-500 focus:border-teal-500"
                                    />
                                </div>
                                {errors.slug && <p className="mt-1 text-sm text-red-600">{errors.slug}</p>}
                            </div>
                        </div>

                        <div className="grid grid-cols-1 md:grid-cols-3 gap-6">
                            <div>
                                <label className="block text-sm font-medium text-gray-700 mb-1">Category <span className="text-red-500">*</span></label>
                                <select
                                    value={data.category}
                                    onChange={e => setData('category', e.target.value)}
                                    className="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-teal-500 focus:border-teal-500"
                                    required
                                >
                                    {categories.map(cat => (
                                        <option key={cat.value} value={cat.value}>{cat.label}</option>
                                    ))}
                                </select>
                            </div>
                            <div>
                                <label className="block text-sm font-medium text-gray-700 mb-1">Document Type <span className="text-red-500">*</span></label>
                                <select
                                    value={data.document_type}
                                    onChange={e => setData('document_type', e.target.value)}
                                    className="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-teal-500 focus:border-teal-500"
                                    required
                                >
                                    {documentTypes.map(dt => (
                                        <option key={dt.value} value={dt.value}>{dt.label}</option>
                                    ))}
                                </select>
                            </div>
                            <div>
                                <label className="block text-sm font-medium text-gray-700 mb-1">Status <span className="text-red-500">*</span></label>
                                <select
                                    value={data.status}
                                    onChange={e => setData('status', e.target.value)}
                                    className="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-teal-500 focus:border-teal-500"
                                    required
                                >
                                    <option value="draft">Draft</option>
                                    <option value="published">Published</option>
                                    <option value="archived">Archived</option>
                                </select>
                            </div>
                        </div>

                        <div className="grid grid-cols-1 md:grid-cols-3 gap-6">
                            <div>
                                <label className="block text-sm font-medium text-gray-700 mb-1">Version</label>
                                <input
                                    type="text"
                                    value={data.version}
                                    onChange={e => setData('version', e.target.value)}
                                    className="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-teal-500 focus:border-teal-500"
                                />
                            </div>
                            <div>
                                <label className="block text-sm font-medium text-gray-700 mb-1">Published At</label>
                                <input
                                    type="datetime-local"
                                    value={data.published_at}
                                    onChange={e => setData('published_at', e.target.value)}
                                    className="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-teal-500 focus:border-teal-500"
                                />
                            </div>
                            <div>
                                <label className="block text-sm font-medium text-gray-700 mb-1">Effective From</label>
                                <input
                                    type="datetime-local"
                                    value={data.effective_from}
                                    onChange={e => setData('effective_from', e.target.value)}
                                    className="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-teal-500 focus:border-teal-500"
                                />
                            </div>
                        </div>

                        <div className="grid grid-cols-1 md:grid-cols-2 gap-6">
                            <div>
                                <label className="block text-sm font-medium text-gray-700 mb-1">Expires At</label>
                                <input
                                    type="datetime-local"
                                    value={data.expires_at}
                                    onChange={e => setData('expires_at', e.target.value)}
                                    className="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-teal-500 focus:border-teal-500"
                                />
                            </div>
                            <div>
                                <label className="block text-sm font-medium text-gray-700 mb-1">Applies To Roles</label>
                                <div className="flex flex-wrap gap-2">
                                    {roles.map(role => (
                                        <label key={role.value} className="inline-flex items-center gap-2">
                                            <input
                                                type="checkbox"
                                                checked={data.applies_to_roles.includes(role.value)}
                                                onChange={e => setData('applies_to_roles', e.target.checked
                                                    ? [...data.applies_to_roles, role.value]
                                                    : data.applies_to_roles.filter(r => r !== role.value)
                                                )}
                                                className="h-4 w-4 text-teal-600 border-gray-300 rounded focus:ring-teal-500"
                                            />
                                            <span className="text-sm text-gray-700">{role.label}</span>
                                        </label>
                                    )}
                                </div>
                                <p className="mt-1 text-xs text-gray-500">Leave empty to apply to all roles</p>
                            </div>
                        </div>
                    </div>

                    {/* Content Section */}
                    <div className="bg-white rounded-lg shadow-sm border border-gray-200 p-6 space-y-6">
                        <h2 className="text-lg font-semibold text-gray-900">Content</h2>
                        
                        <div className="grid grid-cols-1 md:grid-cols-2 gap-6">
                            <div>
                                <label className="block text-sm font-medium text-gray-700 mb-1">Summary</label>
                                <textarea
                                    value={data.summary}
                                    onChange={e => setData('summary', e.target.value)}
                                    rows={3}
                                    placeholder="Brief summary for listings (max 500 characters)"
                                    className="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-teal-500 focus:border-teal-500"
                                    maxLength={500}
                                />
                                <p className="mt-1 text-xs text-gray-500 text-right">{data.summary.length}/500</p>
                            </div>
                            <div>
                                <label className="block text-sm font-medium text-gray-700 mb-1">Is Mandatory</label>
                                <label className="inline-flex items-center gap-2">
                                    <input
                                        type="checkbox"
                                        checked={data.is_mandatory}
                                        onChange={e => setData('is_mandatory', e.target.checked)}
                                        className="h-4 w-4 text-teal-600 border-gray-300 rounded focus:ring-teal-500"
                                    />
                                    <span className="text-sm text-gray-700">Users must accept this document before proceeding</span>
                                </label>
                            </div>
                        </div>

                        <div>
                            <label className="block text-sm font-medium text-gray-700 mb-2">Content (HTML) <span className="text-red-500">*</span></label>
                            <div className="relative">
                                <div className="flex items-center gap-2 mb-2 text-xs text-gray-500">
                                    <kbd className="px-2 py-0.5 bg-gray-100 rounded">Ctrl+B</kbd> Bold
                                    <kbd className="px-2 py-0.5 bg-gray-100 rounded">Ctrl+I</kbd> Italic
                                    <kbd className="px-2 py-0.5 bg-gray-100 rounded">Ctrl+U</kbd> Underline
                                    <kbd className="px-2 py-0.5 bg-gray-100 rounded">Ctrl+K</kbd> Link
                                </div>
                                <textarea
                                    value={data.content}
                                    onChange={e => setData('content', e.target.value)}
                                    rows={20}
                                    className="w-full font-mono text-sm px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-teal-500 focus:border-teal-500 resize-y"
                                    placeholder="Enter HTML content..."
                                    required
                                />
                            </div>
                        </div>

                        {/* Metadata */}
                        <div>
                            <label className="block text-sm font-medium text-gray-700 mb-1">Metadata (JSON)</label>
                            <textarea
                                value={JSON.stringify(data.metadata, null, 2)}
                                onChange={e => {
                                    try {
                                        setData('metadata', JSON.parse(e.target.value));
                                    } catch (e) {}
                                }}
                                rows={5}
                                className="w-full font-mono text-sm px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-teal-500 focus:border-teal-500 resize-y"
                                placeholder='{"effective_date": "2024-01-01", "jurisdiction": "India", "contact_email": "legal@apsdreamhome.com"}'
                            />
                            <p className="mt-1 text-xs text-gray-500">Optional metadata in JSON format</p>
                        </div>
                    </div>

                    {/* Preview Section */}
                    <div className="bg-white rounded-lg shadow-sm border border-gray-200 p-6">
                        <div className="flex items-center justify-between mb-4">
                            <h2 className="text-lg font-semibold text-gray-900">Live Preview</h2>
                            <button
                                type="button"
                                onClick={() => setShowPreview(!showPreview)}
                                className="px-4 py-2 text-sm text-teal-600 hover:text-teal-800 font-medium flex items-center gap-1"
                            >
                                <ChevronUpIcon className="w-5 h-5" />
                                Hide Preview
                            </button>
                        </div>
                        {showPreview && (
                            <div className="bg-gray-50 rounded-lg p-6 border border-gray-200 max-h-96 overflow-auto">
                                <div className="prose prose-teal max-w-none" dangerouslySetInnerHTML={{ __html: data.content }} />
                            </div>
                        )}
                    </div>

                    {/* Actions */}
                    <div className="flex justify-end gap-4 pt-4 border-t border-gray-200">
                        <Link
                            href={route('admin.legal-documents.index')}
                            className="px-4 py-2 text-sm text-gray-700 bg-white border border-gray-300 rounded-lg hover:bg-gray-50 transition-colors"
                        >
                            Cancel
                        </Link>
                        <button
                            type="submit"
                            disabled={processing}
                            className="px-6 py-2 text-sm text-white bg-teal-600 rounded-lg hover:bg-teal-700 transition-colors disabled:opacity-50 flex items-center gap-2"
                        >
                            <svg className="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path strokeLinecap="round" strokeLinejoin="round" strokeWidth={2} d="M8 7H5a2 2 0 00-2 2v9a2 2 0 002 2h14a2 2 0 002-2V9a2 2 0 00-2-2h-3l-1-1H6a2 2 0 00-2 2v9a2 2 0 002 2h2m-6 0l3 3m0 0l3-3m-3 3V3" /></svg>
                            Save Changes
                        </button>
                    </div>
                </form>
            </div>
        </>
    );
}

export default Edit;