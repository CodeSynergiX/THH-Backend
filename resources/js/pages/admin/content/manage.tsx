import React, { useState } from 'react';
import { Link, router, useForm } from '@inertiajs/react';
import { ArrowLeft, Plus, Pencil, Trash2 } from 'lucide-react';
import AdminLayout from '../../../components/AdminLayout';
import RichTextField from '../../../components/RichTextField';
import LocaleTabs, { EditorLocale } from '../../../components/LocaleTabs';
import { useTranslation } from '../../../lib/i18n';

interface Item {
    id: number;
    slug: string;
    title_en: string;
    title_gu: string;
    excerpt_en?: string;
    excerpt_gu?: string;
    body_en?: string;
    body_gu?: string;
    is_published: boolean;
    meta?: Record<string, unknown>;
}

interface Paginated {
    data: Item[];
    current_page: number;
    last_page: number;
    total: number;
    per_page: number;
    links: { url: string | null; label: string; active: boolean }[];
}

export default function ContentManage({
    meta,
    items,
    filters,
}: {
    meta: {
        slug: string;
        title: string;
        title_gu?: string;
        description?: string;
        singular: string;
    };
    items?: Paginated | Item[];
    filters?: { search?: string };
}) {
    const rows = Array.isArray(items) ? items : (items?.data ?? []);
    const total = Array.isArray(items)
        ? items.length
        : (items?.total ?? rows.length);
    const { loc } = useTranslation();
    const [search, setSearch] = useState(filters?.search || '');
    const [editing, setEditing] = useState<Item | null>(null);
    const [open, setOpen] = useState(false);
    const [editorLocale, setEditorLocale] = useState<EditorLocale>('en');

    const form = useForm({
        title_en: '',
        title_gu: '',
        excerpt_en: '',
        excerpt_gu: '',
        body_en: '',
        body_gu: '',
        slug: '',
        is_published: true,
    });

    const openCreate = () => {
        form.reset();
        setEditing(null);
        setEditorLocale('en');
        setOpen(true);
    };

    const openEdit = (item: Item) => {
        setEditorLocale('en');
        form.setData({
            title_en: item.title_en,
            title_gu: item.title_gu,
            excerpt_en: item.excerpt_en ?? '',
            excerpt_gu: item.excerpt_gu ?? '',
            body_en: item.body_en ?? '',
            body_gu: item.body_gu ?? '',
            slug: item.slug,
            is_published: item.is_published,
        });
        setEditing(item);
        setOpen(true);
    };

    const submit = (e: React.FormEvent) => {
        e.preventDefault();
        if (editing) {
            form.put(`/admin/content/${meta.slug}/${editing.id}`, {
                onSuccess: () => setOpen(false),
            });
            return;
        }
        form.post(`/admin/content/${meta.slug}`, {
            onSuccess: () => setOpen(false),
        });
    };

    return (
        <AdminLayout title={`Manage ${meta.title}`}>
            <div className="space-y-6">
                <div className="flex flex-col justify-between gap-4 sm:flex-row sm:items-center">
                    <div className="flex items-center gap-3">
                        <Link
                            href="/admin/content"
                            className="border-thh-border rounded-xl border p-2"
                        >
                            <ArrowLeft className="h-4 w-4" />
                        </Link>
                        <div>
                            <h1 className="text-thh-text text-xl font-semibold">
                                {meta.title}
                            </h1>
                            <p className="text-thh-text-muted text-sm">
                                {meta.title_gu} · {total} items
                            </p>
                        </div>
                    </div>
                    <div className="flex gap-2">
                        <form
                            onSubmit={(e) => {
                                e.preventDefault();
                                router.get(
                                    `/admin/content/${meta.slug}`,
                                    { search },
                                    { preserveState: true },
                                );
                            }}
                        >
                            <input
                                value={search}
                                onChange={(e) => setSearch(e.target.value)}
                                placeholder="Search"
                                className="border-thh-border rounded-xl border px-3 py-2 text-base"
                            />
                        </form>
                        <button
                            type="button"
                            onClick={openCreate}
                            className="bg-thh-primary inline-flex items-center gap-1 rounded-xl px-3 py-2 text-sm font-semibold text-white"
                        >
                            <Plus className="h-4 w-4" />
                            Add {meta.singular}
                        </button>
                    </div>
                </div>

                <div className="bg-thh-surface border-thh-border overflow-hidden rounded-2xl border">
                    <table className="w-full text-left text-sm">
                        <thead className="bg-thh-bg text-thh-text-muted">
                            <tr>
                                <th className="px-4 py-3">Title</th>
                                <th className="px-4 py-3">Status</th>
                                <th className="px-4 py-3 text-right">
                                    Actions
                                </th>
                            </tr>
                        </thead>
                        <tbody>
                            {rows.length === 0 && (
                                <tr>
                                    <td
                                        colSpan={3}
                                        className="text-thh-text-muted px-4 py-10 text-center"
                                    >
                                        No items yet.
                                    </td>
                                </tr>
                            )}
                            {rows.map((item) => (
                                <tr
                                    key={item.id}
                                    className="border-thh-border border-t"
                                >
                                    <td className="px-4 py-3">
                                        <p className="text-thh-text font-semibold">
                                            {loc(item.title_en, item.title_gu)}
                                        </p>
                                        <p className="text-thh-text-muted text-sm">
                                            {item.title_en} · {item.title_gu}
                                        </p>
                                    </td>
                                    <td className="px-4 py-3">
                                        {item.is_published
                                            ? 'Published'
                                            : 'Hidden'}
                                    </td>
                                    <td className="px-4 py-3 text-right">
                                        <button
                                            type="button"
                                            onClick={() =>
                                                router.post(
                                                    `/admin/content/${meta.slug}/${item.id}/toggle`,
                                                )
                                            }
                                            className="mr-2 text-sm underline"
                                        >
                                            Toggle
                                        </button>
                                        <button
                                            type="button"
                                            onClick={() => openEdit(item)}
                                            className="mr-2"
                                        >
                                            <Pencil className="inline h-4 w-4" />
                                        </button>
                                        <button
                                            type="button"
                                            onClick={() =>
                                                router.delete(
                                                    `/admin/content/${meta.slug}/${item.id}`,
                                                )
                                            }
                                            className="text-rose-600"
                                        >
                                            <Trash2 className="inline h-4 w-4" />
                                        </button>
                                    </td>
                                </tr>
                            ))}
                        </tbody>
                    </table>
                </div>
            </div>

            {open && (
                <div className="fixed inset-0 z-50 flex items-center justify-center bg-black/50 p-4">
                    <form
                        onSubmit={submit}
                        className="bg-thh-surface max-h-[92vh] w-full max-w-3xl space-y-3 overflow-y-auto rounded-2xl p-6"
                    >
                        <h3 className="text-lg font-semibold">
                            {editing ? 'Edit item' : 'Add item'}
                        </h3>
                        <p className="text-thh-text-muted text-sm">
                            Citizens see English or Gujarati based on the
                            language switcher. Save both.
                        </p>
                        <LocaleTabs
                            value={editorLocale}
                            onChange={setEditorLocale}
                        />
                        {editorLocale === 'en' ? (
                            <>
                                <input
                                    className="border-thh-border w-full rounded-xl border px-3 py-2 text-base"
                                    placeholder="Title (English)"
                                    value={form.data.title_en}
                                    onChange={(e) =>
                                        form.setData('title_en', e.target.value)
                                    }
                                    required
                                />
                                <input
                                    className="border-thh-border w-full rounded-xl border px-3 py-2 text-base"
                                    placeholder="Short summary (English)"
                                    value={form.data.excerpt_en}
                                    onChange={(e) =>
                                        form.setData(
                                            'excerpt_en',
                                            e.target.value,
                                        )
                                    }
                                />
                                <RichTextField
                                    label="Body (English)"
                                    value={form.data.body_en}
                                    onChange={(v) => form.setData('body_en', v)}
                                />
                            </>
                        ) : (
                            <>
                                <input
                                    className="border-thh-border w-full rounded-xl border px-3 py-2 text-base"
                                    placeholder="શીર્ષક (ગુજરાતી)"
                                    value={form.data.title_gu}
                                    onChange={(e) =>
                                        form.setData('title_gu', e.target.value)
                                    }
                                    required
                                />
                                <input
                                    className="border-thh-border w-full rounded-xl border px-3 py-2 text-base"
                                    placeholder="ટૂંકું વર્ણન (ગુજરાતી)"
                                    value={form.data.excerpt_gu}
                                    onChange={(e) =>
                                        form.setData(
                                            'excerpt_gu',
                                            e.target.value,
                                        )
                                    }
                                />
                                <RichTextField
                                    label="વિગત (ગુજરાતી)"
                                    value={form.data.body_gu}
                                    onChange={(v) => form.setData('body_gu', v)}
                                />
                            </>
                        )}
                        <label className="flex items-center gap-2 text-sm">
                            <input
                                type="checkbox"
                                checked={form.data.is_published}
                                onChange={(e) =>
                                    form.setData(
                                        'is_published',
                                        e.target.checked,
                                    )
                                }
                            />
                            Published
                        </label>
                        <div className="flex justify-end gap-2">
                            <button
                                type="button"
                                onClick={() => setOpen(false)}
                                className="px-4 py-2 text-sm"
                            >
                                Cancel
                            </button>
                            <button
                                type="submit"
                                disabled={form.processing}
                                className="bg-thh-primary rounded-xl px-4 py-2 text-sm font-semibold text-white"
                            >
                                Save
                            </button>
                        </div>
                    </form>
                </div>
            )}
        </AdminLayout>
    );
}
