import React from 'react';
import PublicLayout from '../../components/PublicLayout';
import { useTranslation } from '../../lib/i18n';

export default function StaticPageView({
    page,
}: {
    page: {
        slug: string;
        title_en?: string;
        title_gu?: string;
        body_en?: string;
        body_gu?: string;
    };
}) {
    const { loc } = useTranslation();
    const title = loc(page.title_en, page.title_gu);
    const body = loc(page.body_en, page.body_gu);

    return (
        <PublicLayout title={title || page.slug}>
            <article className="mx-auto max-w-3xl px-4 py-12">
                <h1 className="mb-6 font-serif text-3xl">{title}</h1>
                <div
                    className="cms-body text-base leading-7"
                    dangerouslySetInnerHTML={{ __html: body || '' }}
                />
            </article>
        </PublicLayout>
    );
}
