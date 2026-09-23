import React, { useState } from 'react';
import { useForm } from '@inertiajs/react';
import { FileText } from 'lucide-react';
import AdminLayout from '../../../components/AdminLayout';
import RichTextField from '../../../components/RichTextField';
import LocaleTabs, { EditorLocale } from '../../../components/LocaleTabs';

interface PageRow {
    id: number;
    slug: string;
    title_en?: string;
    title_gu?: string;
    body_en?: string;
    body_gu?: string;
    is_active: boolean;
}

export default function StaticPagesIndex({
    pages = [],
}: {
    pages?: PageRow[];
}) {
    const [editorLocale, setEditorLocale] = useState<EditorLocale>('en');
    const [selected, setSelected] = useState<PageRow | null>(pages[0] ?? null);
    const form = useForm({
        title_en: pages[0]?.title_en ?? '',
        title_gu: pages[0]?.title_gu ?? '',
        body_en: pages[0]?.body_en ?? '',
        body_gu: pages[0]?.body_gu ?? '',
        is_active: pages[0]?.is_active ?? true,
    });

    const select = (page: PageRow) => {
        setSelected(page);
        form.setData({
            title_en: page.title_en ?? '',
            title_gu: page.title_gu ?? '',
            body_en: page.body_en ?? '',
            body_gu: page.body_gu ?? '',
            is_active: page.is_active,
        });
    };

    return (
        <AdminLayout title="Public pages">
            <div className="grid gap-6 lg:grid-cols-[240px_1fr]">
                <aside className="bg-thh-surface border-thh-border space-y-2 rounded-2xl border p-3">
                    {pages.map((page) => (
                        <button
                            key={page.id}
                            type="button"
                            onClick={() => select(page)}
                            className={`w-full rounded-xl px-3 py-2 text-left text-sm ${
                                selected?.id === page.id
                                    ? 'bg-thh-primary text-white'
                                    : 'hover:bg-thh-bg'
                            }`}
                        >
                            {page.title_en || page.title_gu || page.slug}
                        </button>
                    ))}
                </aside>

                {selected && (
                    <form
                        onSubmit={(e) => {
                            e.preventDefault();
                            form.put(`/admin/pages/${selected.id}`);
                        }}
                        className="bg-thh-surface border-thh-border space-y-4 rounded-2xl border p-6"
                    >
                        <div className="flex items-center gap-2">
                            <FileText className="text-thh-primary h-5 w-5" />
                            <h2 className="text-lg font-semibold">
                                {selected.slug}
                            </h2>
                        </div>
                        <p className="text-thh-text-muted text-sm">
                            Privacy, Terms and About are shown in the language
                            the visitor selects.
                        </p>
                        <LocaleTabs
                            value={editorLocale}
                            onChange={setEditorLocale}
                        />
                        {editorLocale === 'en' ? (
                            <>
                                <input
                                    className="border-thh-border w-full rounded-xl border px-3 py-2 text-base"
                                    value={form.data.title_en}
                                    onChange={(e) =>
                                        form.setData('title_en', e.target.value)
                                    }
                                    placeholder="Title (English)"
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
                                    value={form.data.title_gu}
                                    onChange={(e) =>
                                        form.setData('title_gu', e.target.value)
                                    }
                                    placeholder="શીર્ષક (ગુજરાતી)"
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
                                checked={form.data.is_active}
                                onChange={(e) =>
                                    form.setData('is_active', e.target.checked)
                                }
                            />
                            Active
                        </label>
                        <button
                            type="submit"
                            disabled={form.processing}
                            className="bg-thh-primary rounded-xl px-4 py-2 text-sm font-semibold text-white"
                        >
                            Save page
                        </button>
                    </form>
                )}
            </div>
        </AdminLayout>
    );
}
