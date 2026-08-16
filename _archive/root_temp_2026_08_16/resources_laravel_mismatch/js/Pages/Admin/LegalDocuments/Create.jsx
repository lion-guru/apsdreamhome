import React, { useEffect, useState } from 'react';
import { Head } from '@inertiajs/react';
import { Link, useForm } from '@inertiajs/react';
import { ArrowLeftIcon, SaveIcon, EyeIcon, XMarkIcon, ChevronDownIcon, ChevronUpIcon } from '@heroicons/react/24/outline';
import { Dialog, Transition } from '@headlessui/react';

export default function Create({ categories, documentTypes, roles }) {
    const { data, setData, post, processing, errors, clearErrors } = useForm({
        title: '',
        slug: '',
        category: 'company',
        document_type: 'terms',
        content: '',
        summary: '',
        version: '1.0',
        status: 'draft',
        is_mandatory: false,
        applies_to_roles: [],
        metadata: {},
        published_at: '',
        effective_from: '',
        expires_at: '',
    });

    const [showPreview, setShowPreview] = useState(false);

    const handleSubmit = (e) => {
        e.preventDefault();
        post(route('admin.legal-documents.store'), {
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
                <title>Create Legal Document - Admin</title>
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
                            <h1 className="text-2xl font-bold text-gray-900">Create Legal Document</h1>
                            <p className="text-gray-600">Create a new legal document for the platform</p>
                        </div>
                    </div>
                    <div className="flex gap-3">
                        <Link
                            href={route('admin.legal-documents.index')}
                            className="px-4 py-2 text-sm text-gray-700 bg-white border border-gray-300 rounded-lg hover:bg-gray-50 transition-colors"
                        >
                            Cancel
                        </Link>
                        <button
                            type="submit"
                            form="legal-form"
                            disabled={processing}
                            className="px-4 py-2 text-sm text-white bg-teal-600 rounded-lg hover:bg-teal-700 transition-colors disabled:opacity-50 flex items-center gap-2"
                        >
                            <SaveIcon className="w-5 h-5" />
                            {processing ? 'Saving...' : 'Save Document'}
                        </button>
                    </div>
                </div>

                {/* Main Form */}
                <form id="legal-form" onSubmit={handleSubmit} className="space-y-6">
                    {/* Basic Info Section */}
                    <div className="bg-white rounded-lg shadow-sm border border-gray-200 p-6 space-y-6">
                        <h2 className="text-lg font-semibold text-gray-900">Basic Information</h2>
                        <div className="grid grid-cols-1 md:grid-cols-2 gap-6">
                            <div>
                                <label className="block text-sm font-medium text-gray-700 mb-1">Title <span className="text-red-500">*</span></label>
                                <input
                                    type="text"
                                    value={data.title}
                                    onChange={e => { setData('title', e.target.value); autoGenerateSlug(); }}
                                    placeholder="e.g., Terms of Service"
                                    className="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-teal-500 focus:border-teal-500"
                                    required
                                />
                                {errors.title && <p className="mt-1 text-sm text-red-600">{errors.title}</p>}
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
                                    <button
                                        type="button"
                                        onClick={autoGenerateSlug}
                                        className="px-4 py-2 text-sm text-gray-700 bg-white border border-gray-300 rounded-lg hover:bg-gray-50 transition-colors"
                                    >
                                        Auto-generate
                                    </button>
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
                                {errors.category && <p className="mt-1 text-sm text-red-600">{errors.category}</p>}
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
                                {errors.document_type && <p className="mt-1 text-sm text-red-600">{errors.document_type}</p>}
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
                                {errors.status && <p className="mt-1 text-sm text-red-600">{errors.status}</p>}
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
                                    value={data.version}
                                />
                            </div>
                            <div>
                                <label className="block text-sm font-medium text-gray-700 mb-1">Published At</label>
                                <input
                                    type="datetime-local"
                                    value={formatDateTime(data.published_at)}
                                    onChange={e => setData('published_at', e.target.value)}
                                    className="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-teal-500 focus:border-teal-500"
                                />
                            </div>
                            <div>
                                <label className="block text-sm font-medium text-gray-700 mb-1">Effective From</label>
                                <input
                                    type="datetime-local"
                                    value={formatDateTime(data.effective_from)}
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
                                    value={formatDateTime(data.expires_at)}
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
                                    ))}
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
                                {errors.summary && <p className="mt-1 text-sm text-red-600">{errors.summary}</p>}
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
                                {errors.content && <p className="mt-1 text-sm text-red-600">{errors.content}</p>}
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
                                    } catch (e) {
                                        // Ignore invalid JSON while typing
                                    }
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
                                {showPreview ? <ChevronUpIcon className="w-5 h-5" /> : <ChevronDownIcon className="w-5 h-5" />}
                                {showPreview ? 'Hide Preview' : 'Show Preview'}
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
                            <SaveIcon className="w-5 h-5" />
                            {processing ? 'Saving...' : 'Save Document'}
                        </button>
                    </div>
                </form>
            </div>
        </>
    );
}

export default Create;