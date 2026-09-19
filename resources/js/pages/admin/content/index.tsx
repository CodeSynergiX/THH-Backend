import React from 'react';
import { Link } from '@inertiajs/react';
import {
    Layers,
    Landmark,
    GraduationCap,
    Briefcase,
    Activity,
    Droplet,
    BookOpen,
    HeartHandshake,
    MapPin,
    ArrowRight,
} from 'lucide-react';
import AdminLayout from '../../../components/AdminLayout';

interface ModuleItem {
    slug: string;
    title: string;
    title_gu: string;
    count: number;
    active_count: number;
    description: string;
}

interface ContentIndexProps {
    modules: ModuleItem[];
}

export default function ContentIndex({ modules }: ContentIndexProps) {
    const getModuleIcon = (slug: string) => {
        switch (slug) {
            case 'schemes':
                return <Landmark className="text-thh-primary h-6 w-6" />;
            case 'scholarships':
                return <GraduationCap className="text-thh-secondary h-6 w-6" />;
            case 'jobs':
                return <Briefcase className="text-thh-accent h-6 w-6" />;
            case 'health':
                return <Activity className="h-6 w-6 text-rose-600" />;
            case 'blood':
                return <Droplet className="h-6 w-6 text-red-600" />;
            case 'mock_tests':
                return <BookOpen className="h-6 w-6 text-indigo-600" />;
            case 'sakhi':
                return <HeartHandshake className="h-6 w-6 text-teal-600" />;
            case 'village_reports':
                return <MapPin className="h-6 w-6 text-amber-600" />;
            default:
                return <Layers className="text-thh-primary h-6 w-6" />;
        }
    };

    return (
        <AdminLayout title="Content & Community Modules">
            <div className="space-y-6">
                {/* Header */}
                <div>
                    <h2 className="text-thh-text flex items-center gap-2.5 text-2xl font-bold tracking-tight">
                        <Layers className="text-thh-primary h-6 w-6" />
                        <span>Content & Community Modules</span>
                    </h2>
                    <p className="text-thh-text-muted mt-1 text-xs">
                        Central registry of welfare schemes, student
                        scholarships, employment listings, health camps, and
                        ground village reports.
                    </p>
                </div>

                {/* Modules Grid */}
                <div className="grid grid-cols-1 gap-5 sm:grid-cols-2 lg:grid-cols-4">
                    {modules.map((m) => (
                        <div
                            key={m.slug}
                            className="bg-thh-surface border-thh-border hover:border-thh-primary flex flex-col justify-between space-y-4 rounded-2xl border p-5 shadow-2xs transition-colors"
                        >
                            <div className="space-y-3">
                                <div className="flex items-center justify-between">
                                    <div className="bg-thh-bg border-thh-border rounded-xl border p-2">
                                        {getModuleIcon(m.slug)}
                                    </div>
                                    <span className="text-thh-primary font-mono text-sm font-black">
                                        {m.count}
                                    </span>
                                </div>

                                <div>
                                    <h3 className="text-thh-text text-base font-bold">
                                        {m.title}
                                    </h3>
                                    <span className="text-thh-text-muted block text-[11px] font-medium">
                                        {m.title_gu}
                                    </span>
                                </div>

                                <p className="text-thh-text-muted text-xs leading-relaxed">
                                    {m.description}
                                </p>
                            </div>

                            <div className="border-thh-border flex items-center justify-between border-t pt-3 text-xs">
                                <span className="text-thh-text-muted text-[11px] font-semibold">
                                    {m.active_count} Active
                                </span>
                                <Link
                                    href={`/admin/content/${m.slug}`}
                                    className="bg-thh-primary/10 text-thh-primary hover:bg-thh-primary inline-flex items-center gap-1.5 rounded-lg px-3 py-1.5 text-xs font-bold transition-all duration-150 hover:text-white"
                                >
                                    <span>Manage</span>
                                    <ArrowRight className="h-3.5 w-3.5" />
                                </Link>
                            </div>
                        </div>
                    ))}
                </div>
            </div>
        </AdminLayout>
    );
}
