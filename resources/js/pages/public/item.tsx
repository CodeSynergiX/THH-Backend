import React from 'react';
import { Link } from '@inertiajs/react';
import PublicLayout from '../../components/PublicLayout';
import ApplyHelpForm from '../../components/ApplyHelpForm';
import { useTranslation } from '../../lib/i18n';

export default function PublicItem({
    module,
    item,
    districts = [],
}: {
    module: {
        slug: string;
        title_en: string;
        title_gu: string;
        show_apply_form?: boolean;
        accent_color?: string;
    };
    item: {
        slug: string;
        title_en: string;
        title_gu: string;
        body_en?: string;
        body_gu?: string;
        excerpt_en?: string;
        excerpt_gu?: string;
    };
    districts?: unknown[];
}) {
    const { t, loc } = useTranslation();
    const title = loc(item.title_en, item.title_gu);
    const body = loc(item.body_en, item.body_gu);
    const excerpt = loc(item.excerpt_en, item.excerpt_gu);

    return (
        <PublicLayout title={title}>
            <article className="mx-auto max-w-3xl px-4 py-12">
                <Link
                    href={`/community/${module.slug}`}
                    className="text-thh-primary text-sm font-semibold"
                >
                    ← {loc(module.title_en, module.title_gu)}
                </Link>
                <p
                    className="mt-6 mb-3 inline-block rounded-full px-3 py-1 text-xs font-bold text-white"
                    style={{
                        backgroundColor:
                            module.accent_color || 'var(--color-primary)',
                    }}
                >
                    {t('home.guidance_note', 'Guidance note')}
                </p>
                <h1 className="font-serif text-4xl leading-tight">{title}</h1>
                {excerpt && (
                    <p className="text-thh-text-muted mt-4 text-lg leading-8">
                        {excerpt}
                    </p>
                )}
                <div
                    className="cms-body mt-8 text-base leading-8"
                    dangerouslySetInnerHTML={{ __html: body || '' }}
                />

                {module.show_apply_form && (
                    <div className="mt-12">
                        <ApplyHelpForm
                            action={`/community/${module.slug}/apply`}
                            defaultTitle={title}
                            defaultDescription={excerpt}
                            districts={districts as any}
                        />
                    </div>
                )}
            </article>
        </PublicLayout>
    );
}
