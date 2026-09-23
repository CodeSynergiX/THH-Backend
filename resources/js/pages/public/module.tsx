import React, { useState } from 'react';
import { Link, usePage } from '@inertiajs/react';
import PublicLayout from '../../components/PublicLayout';
import ApplyHelpForm from '../../components/ApplyHelpForm';
import { useTranslation } from '../../lib/i18n';
import { visualFor } from '../../lib/moduleVisual';

interface Item {
    id: number;
    slug: string;
    title_en: string;
    title_gu: string;
    excerpt_en?: string;
    excerpt_gu?: string;
}

interface ModuleRecord {
    slug: string;
    title_en: string;
    title_gu: string;
    description_en?: string;
    description_gu?: string;
    show_apply_form?: boolean;
    accent_color?: string;
}

export default function PublicModule({
    module,
    items,
    districts = [],
}: {
    module: ModuleRecord;
    items: { data?: Item[] } | Item[];
    districts?: Array<{
        id: number;
        name_en: string;
        name_gu?: string;
        talukas?: Array<{
            id: number;
            name_en: string;
            name_gu?: string;
            villages?: Array<{
                id: number;
                name_en: string;
                name_gu?: string;
                lat?: number | null;
                lng?: number | null;
            }>;
        }>;
    }>;
}) {
    const { t, loc } = useTranslation();
    const flash = usePage<{ flash?: { success?: string } }>().props.flash;
    const [search, setSearch] = useState('');
    const [chip, setChip] = useState<'all' | 'apply'>('all');
    const title = loc(module.title_en, module.title_gu);
    const look = visualFor(module.slug);
    const list = Array.isArray(items) ? items : (items.data ?? []);
    const filtered = list.filter((item) => {
        const hay =
            `${item.title_en} ${item.title_gu} ${item.excerpt_en} ${item.excerpt_gu}`.toLowerCase();
        return hay.includes(search.toLowerCase());
    });

    return (
        <PublicLayout title={title}>
            <section
                className="relative overflow-hidden"
                style={{
                    background: `linear-gradient(135deg, ${module.accent_color || 'var(--color-primary)'}22, var(--color-bg))`,
                }}
            >
                <span className="shape-blob bg-thh-primary/15 -top-10 -right-8 h-40 w-40" />
                <div className="relative mx-auto max-w-6xl px-4 py-12">
                    <Link
                        href="/"
                        className="text-thh-primary text-sm font-semibold"
                    >
                        ← {t('nav.home', 'Home')}
                    </Link>
                    <div className="mt-5 flex flex-wrap items-start gap-5">
                        <span
                            className="flex h-16 w-16 items-center justify-center rounded-[1.3rem] text-3xl shadow-sm"
                            style={{
                                backgroundColor: `${module.accent_color || '#B45309'}33`,
                            }}
                        >
                            {look.icon}
                        </span>
                        <div className="max-w-3xl">
                            <p className="text-thh-primary text-xs font-bold tracking-[0.18em] uppercase">
                                {look.chip}
                            </p>
                            <h1 className="mt-1 font-serif text-4xl">
                                {title}
                            </h1>
                            <p className="text-thh-text-muted mt-3 text-lg leading-8">
                                {loc(
                                    module.description_en,
                                    module.description_gu,
                                )}
                            </p>
                        </div>
                    </div>
                </div>
            </section>

            <section className="mx-auto max-w-6xl px-4 py-10">
                {flash?.success && (
                    <p className="mb-6 rounded-2xl bg-emerald-50 p-4 text-emerald-800">
                        {flash.success}
                    </p>
                )}
                <div className="mb-6 flex flex-wrap gap-2">
                    <button
                        type="button"
                        onClick={() => setChip('all')}
                        className={`rounded-full px-4 py-1.5 text-sm font-semibold ${
                            chip === 'all'
                                ? 'bg-thh-primary text-white'
                                : 'bg-thh-surface border-thh-border border'
                        }`}
                    >
                        {t('home.listings', 'Listings')} ({list.length})
                    </button>
                    {module.show_apply_form && (
                        <button
                            type="button"
                            onClick={() => setChip('apply')}
                            className={`rounded-full px-4 py-1.5 text-sm font-semibold ${
                                chip === 'apply'
                                    ? 'bg-thh-primary text-white'
                                    : 'bg-thh-surface border-thh-border border'
                            }`}
                        >
                            {t('apply.heading', 'Apply for help')}
                        </button>
                    )}
                </div>
                {chip === 'all' && (
                    <>
                        <input
                            className="border-thh-border focus:ring-thh-primary w-full max-w-md rounded-2xl border px-4 py-3 text-base focus:ring-2 focus:outline-hidden"
                            placeholder={t('app.common.search', 'Search')}
                            value={search}
                            onChange={(e) => setSearch(e.target.value)}
                        />
                        <div className="mt-6 grid gap-4 sm:grid-cols-2">
                            {filtered.map((item) => (
                                <Link
                                    key={item.id}
                                    href={`/community/${module.slug}/${item.slug}`}
                                    className="service-card bg-thh-surface border-thh-border rounded-[1.6rem] border p-6 shadow-sm"
                                >
                                    <h2 className="font-serif text-2xl">
                                        {loc(item.title_en, item.title_gu)}
                                    </h2>
                                    <p className="text-thh-text-muted mt-3 line-clamp-5 text-base leading-7">
                                        {loc(item.excerpt_en, item.excerpt_gu)}
                                    </p>
                                    <p className="text-thh-primary mt-4 text-sm font-semibold">
                                        {t('home.read_more', 'Read guidance')} →
                                    </p>
                                </Link>
                            ))}
                        </div>
                    </>
                )}
                {chip === 'apply' && module.show_apply_form && (
                    <div className="mt-2 max-w-xl">
                        <ApplyHelpForm
                            action={`/community/${module.slug}/apply`}
                            districts={districts}
                        />
                    </div>
                )}
                {chip === 'all' && module.show_apply_form && (
                    <div className="mt-12 max-w-xl">
                        <ApplyHelpForm
                            action={`/community/${module.slug}/apply`}
                            districts={districts}
                        />
                    </div>
                )}
            </section>
        </PublicLayout>
    );
}
